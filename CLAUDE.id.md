# CLAUDE.md (Versi Bahasa Indonesia)

Terjemahan dari `CLAUDE.md` — file panduan untuk Claude Code (claude.ai/code) saat bekerja
dengan kode di repository ini. File ini murni referensi bacaan (mis. untuk persiapan sidang);
`CLAUDE.md` versi Inggris tetap yang dipakai sebagai instruksi aktif oleh Claude Code.

## Proyek

Sistem e-tourism untuk **CV Kopi Danau Diatas** (wisata perkebunan kopi): katalog paket wisata,
booking dengan kuota harian, pembayaran Midtrans Snap, e-tiket QR + invoice PDF, ulasan, dan
panel admin Filament. Laravel 11 / PHP 8.3. Ini adalah proyek skripsi (tugas akhir) — folder
`docs/` menyimpan dokumentasi UML (use case, diagram activity/sequence, ERD, sprint planning)
dan **di-gitignore**, jadi ada secara lokal tapi `git log`/`git show` tidak punya riwayatnya.

## Perintah

```bash
composer dev                    # server + queue:listen + pail (log) + vite, sekaligus (dev)
php artisan serve               # hanya web server
php artisan queue:work          # queue worker — WAJIB, email di-antre bukan dikirim langsung
npm run dev                     # vite (hot reload)

php artisan test                                    # seluruh test suite
php artisan test --filter=Midtrans                  # filter berdasarkan nama
php artisan test tests/Feature/BookingQuotaTest.php  # satu file saja

vendor/bin/pint                 # code style (Laravel Pint)
vendor/bin/pint --dirty         # hanya file yang berubah

php artisan content:translate --locale=en   # isi terjemahan EN yang kosong lewat Gemini (idempotent)
```

Dua perintah terjadwal (`routes/console.php`, perlu `schedule:run` lewat cron di produksi, atau
dijalankan manual saat dev) menjaga status booking tetap benar: `bookings:expire-pending`
(mengadaluarsakan booking belum bayar yang sudah >1 jam, tiap 15 menit) dan
`bookings:auto-complete` (paid/confirmed → completed setelah `visit_date` lewat, tiap hari jam
23:55). Tanpa queue worker + scheduler yang berjalan, email pembayaran tidak akan pernah
terkirim dan booking pending yang kadaluarsa akan terus menahan kuota selamanya.

Database untuk test pakai SQLite in-memory (`phpunit.xml`), sepenuhnya terpisah dari
`database/database.sqlite` yang dipakai saat development.

## Arsitektur

**Dua pintu depan, satu basis kode**: situs publik (`routes/web.php`,
`resources/views/pages/*`, Blade + Tailwind biasa, tanpa framework JS) dan panel admin Filament
di `/admin` (`app/Filament/Resources`, `app/Providers/Filament/AdminPanelProvider.php`).
Resource Filament sebagian besar hanya membungkus model Eloquent yang sama dipakai situs
publik — tidak ada data layer terpisah untuk admin.

**Booking adalah mesin status (state machine), bukan kolom status bebas isi.** Transisi yang
diperbolehkan didefinisikan di `Booking::ALLOWED_TRANSITIONS` (`app/Models/Booking.php`) dan
dipaksakan oleh `BookingObserver` di setiap penyimpanan (didaftarkan di
`AppServiceProvider::boot`), sehingga loncatan ilegal (mis. `completed` → `pending`) akan
melempar `ValidationException` — tidak peduli apakah penulisan datang dari controller,
Filament, atau service. Status: `pending → paid|cancelled|expired`,
`paid → confirmed|completed|cancelled`, `confirmed → completed|cancelled`; `completed`/
`cancelled`/`expired` bersifat terminal (tidak bisa berubah lagi). Saat menambah cara baru untuk
mengubah status booking, selalu lewat `$booking->update(['status' => ...])` — jangan
melewatinya dengan `saveQuietly()`/query mentah, karena itu satu-satunya jalur yang membuat
guard milik observer benar-benar jalan.

**Kuota dihitung, bukan disimpan.** `TourPackage::getAvailableQuota($date)` menjumlahkan
`guest_count` dari booking berstatus paid/confirmed/completed *ditambah* booking pending yang
dibuat dalam satu jam terakhir (jendela waktu yang sama dengan batas
`bookings:expire-pending`, supaya pengguna yang sedang di tengah proses checkout tidak
kebobolan double-booking, sementara baris pending yang sudah basi tetap dikecualikan).
`BookingController::store()` mengecek ulang kuota ini di dalam `DB::transaction()` dengan
`TourPackage::lockForUpdate()` untuk menutup celah race condition antara dua pengguna yang
memesan kursi terakhir secara bersamaan — lihat `tests/Feature/BookingQuotaTest.php`. Jalur
pembuatan booking baru apa pun wajib memakai ulang pola lock-lalu-cek ini, bukan sekadar
memanggil `getAvailableQuota` di luar transaksi.

**Alur pembayaran**: `MidtransService::createSnapToken()` membuat/memakai ulang baris
`Payment` (memakai ulang `midtrans_order_id` saat "bayar lagi" supaya korelasi webhook tetap
jalan), lalu `MidtransWebhookController` (`routes/api.php`, tanpa CSRF) →
`MidtransService::handleNotification()` memverifikasi signature dan menerapkan perubahan
status. Handler ini idempotent by design (mengecek `midtrans_transaction_id` + status yang
sudah dipetakan sebelum bertindak) karena Midtrans mengirim ulang notifikasi — lihat
`tests/Feature/MidtransWebhookTest.php` dan `tests/Unit/MidtransSignatureTest.php`. Efek
samping pasca-pembayaran (generate QR, email konfirmasi) hanya dijalankan sekali, khusus pada
transisi pending→paid, dan dibungkus try/catch supaya kegagalan email/QR yang tidak stabil
tidak pernah membuat response webhook gagal (yang akan membuat Midtrans melakukan retry dan
menerapkan perubahan status dua kali).

**Refund dicatat admin, bukan dieksekusi lewat gateway**: `RefundService` memindahkan
`paid|confirmed` → `cancelled` dan menandai Payment sebagai `refund` secara lokal;
pengembalian uang yang sesungguhnya dilakukan manual lewat dashboard Midtrans (sandbox tidak
punya API refund yang bisa diandalkan). Jangan menyambungkan ini ke pemanggilan refund
Midtrans yang live tanpa mengecek dulu alasan yang sudah didokumentasikan di class service-nya.

**Check-in adalah klaim atomik yang aman dari race condition**: `TicketCheckInService::checkIn()`
melakukan `UPDATE ... WHERE checked_in_at IS NULL` bersyarat, bukan baca-lalu-tulis, sehingga
dua petugas yang memindai QR yang sama secara bersamaan tidak mungkin dua-duanya berhasil.
Payload QR meng-encode `Booking::ticket_token` (string acak 64 karakter, bukan `booking_code`
yang bisa ditebak/dienumerasi) sebagai deep link ke halaman check-in admin.

**i18n / konten yang bisa diterjemahkan**: Model dengan `HasTranslations` (spatie) yang juga
memakai `HasAutoTranslation` (`app/Models/Concerns/HasAutoTranslation.php`) otomatis mengisi
terjemahan Inggris yang hilang dari Bahasa Indonesia lewat `GeminiTranslator` di setiap
penyimpanan (hook `static::saving`). Bahasa Indonesia selalu jadi sumber kebenaran; kalau
`GEMINI_API_KEY` tidak diset, fitur ini jadi tidak aktif dan teks Inggris fallback ke teks
Indonesia (`Translatable::fallback` di `AppServiceProvider::boot`). Ini keputusan yang
disengaja — user tidak mau ada kerja admin menerjemahkan secara manual sama sekali; jangan
diganti dengan alur tombol "terjemahkan" manual. `php artisan content:translate` mengisi baris
yang sudah ada dan aman dijalankan berulang kali (hanya mengisi target yang masih kosong).
String UI (bukan konten CMS) memakai `lang/{id,en}.json` + `lang/{id,en}/mailer.php`; locale
dibaca dari `session('locale')` oleh middleware `SetLocale`, diganti lewat `/lang/{locale}`.

**Penghapusan paket wisata diblokir, bukan di-cascade**: `TourPackage::booted()` melempar
`PackageHasBookingsException` kalau paket yang masih punya booking dihapus (juga dipaksakan di
level database lewat FK `RESTRICT` — lihat migrasi `restrict_tour_package_booking_fk`), supaya
riwayat keuangan tidak pernah terhapus diam-diam. Admin menonaktifkan paket
(`is_active=false`) sebagai gantinya.

## Konvensi

- String yang tampil ke publik (pesan validasi, pesan flash) berbahasa Indonesia secara
  default, dibungkus `__()`; audiens utama situs ini adalah pengunjung Indonesia meski bahasa
  Inggris juga didukung.
- Slug (`TourPackage`) di-generate sekali dari `name` dan tidak pernah di-generate ulang saat
  update (`doNotGenerateSlugsOnUpdate()`), karena `name` bisa diterjemahkan dan slug harus tetap
  stabil walau locale berpindah.
- Observer (`app/Observers`) menangani hal-hal lintas-model (penegakan state machine, invalidasi
  cache untuk `SiteSetting`/`HomepageSection`/`HomepageImage`) — cek dulu di situ sebelum
  menambahkan logika langsung di controller.
