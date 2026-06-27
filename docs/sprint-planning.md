# Sprint Planning
# E-Tourism Information System — CV Kopi Danau Atas

> **Version:** 1.0 | **Date:** 27 Juni 2026
> **Author:** Fadhil Dzaky Arhab — 2301092010
> **Sumber:** Gap Analysis (audit keamanan + fitur + kesiapan produksi terhadap kode nyata)

---

## 1. Konteks & Asumsi

- **Tim:** 1 developer (solo, tugas akhir).
- **Cadence:** sprint 2 minggu.
- **Kapasitas:** ±20–25 *story point* (SP) / sprint (≈ 25–35 jam kerja efektif/sprint).
- **Skala SP:** 1 = ±1–2 jam, 2 = ±setengah hari, 3 = ±1 hari, 5 = ±2 hari, 8 = ±3–4 hari.
- **Prinsip urutan:** kelayakan **sidang** dulu (Sprint 1), lalu **fitur produksi inti** (Sprint 2–3), lalu **hardening** (Sprint 4), terakhir **growth opsional** (Sprint 5).

### 1.1 Status fondasi (sudah benar — TIDAK perlu dikerjakan ulang)
Tanda tangan webhook Midtrans terverifikasi (`hash_equals`), harga dihitung server-side dalam transaksi `lockForUpdate` (anti double-booking & anti-tamper), tidak ada IDOR, transisi status idempoten, uang `decimal(12,2)`, indeks & cache benar. Sprint di bawah **menambah**, bukan memperbaiki, fondasi ini.

### 1.2 Definition of Ready (DoR)
Item siap dikerjakan bila: tujuan jelas, file terdampak diketahui, acceptance criteria terdefinisi, dependensi tidak memblokir.

### 1.3 Definition of Done (DoD)
Item selesai bila: kode jalan & ter-commit di branch, **acceptance criteria terpenuhi**, ada test (untuk item berlogika), `php artisan test` hijau, `vendor/bin/pint` bersih, dan dokumentasi/README diperbarui bila perlu.

---

## 2. Roadmap (Ringkasan)

| Sprint | Tema | Tujuan | SP | Prioritas Gap |
|--------|------|--------|----|----|
| **1** | Pengerasan Kesiapan Sidang | Jalur uang terbukti via test + sistem reproducible | 16 | P0 |
| **2** | Tiket & Kontrol Booking | E-ticket benar-benar berfungsi (check-in) + user kontrol booking | 21 | P1a |
| **3** | Penemuan & Keamanan Data | Katalog dapat dicari + data finansial aman + deploy reproducible | 22 | P1b |
| **4** | Hardening Keamanan & Ops | Tutup celah defense-in-depth + observability | 12 (+buffer) | P2 |
| **5** *(opsional)* | UX & Growth | Reminder, laporan, reschedule, dll. | ~20 | P2/P3 |

> **Catatan TA:** menyelesaikan **Sprint 1** sudah cukup membuat sistem layak & dapat dipertahankan di sidang. Sprint 2+ adalah jalan menuju production / pilot nyata di CV Kopi Danau Atas.

---

## 3. Sprint 1 — Pengerasan Kesiapan Sidang (P0)

> **Sprint Goal:** Membuktikan secara otomatis bahwa jalur uang (webhook, kuota) benar, dan memastikan sistem dapat dijalankan ulang oleh penguji/dosen.

| ID | Item | Est | Dep |
|----|------|-----|-----|
| S1-1 | Test webhook pembayaran + idempotensi | 5 | — |
| S1-2 | Test race-kondisi kuota (anti double-booking) | 3 | S1-1 (factories) |
| S1-3 | Test end-to-end alur booking | 3 | S1-1 |
| S1-4 | Lengkapi `.env.example` | 1 | — |
| S1-5 | README runbook (queue worker + cron + setup) | 2 | — |
| S1-6 | Rate limiting endpoint autentikasi | 2 | — |

### S1-1 — Test webhook pembayaran + idempotensi `[5 SP]`
- **Kerjakan:** Buat `database/factories/TourPackageFactory.php`, `BookingFactory.php`, `PaymentFactory.php`. Tulis `tests/Feature/MidtransWebhookTest.php`.
- **Kasus uji:**
  - Notifikasi `settlement` valid → booking jadi `paid`, payment tercatat, e-ticket dibuat, email konfirmasi ter-dispatch (`Queue::fake()` / `Mail::fake()`).
  - **Idempotensi:** kirim notifikasi sama dua kali → efek samping (email/QR) hanya sekali.
  - Status `expire` → `expired`; `cancel`/`deny` → `cancelled`.
  - Tanda tangan salah → HTTP 403, status tidak berubah.
- **AC:** semua kasus hijau; assertion idempotensi membuktikan tidak ada double-charge/double-email.
- **File:** `app/Services/MidtransService.php`, `app/Http/Controllers/MidtransWebhookController.php`.

### S1-2 — Test race-kondisi kuota `[3 SP]`
- **Kerjakan:** `tests/Feature/BookingQuotaTest.php`.
- **Kasus uji:** paket `daily_quota = 1`, sudah ada 1 booking aktif → percobaan `store()` berikutnya ditolak dengan pesan "Kuota habis"; verifikasi pengecekan ulang kuota terjadi **di dalam** transaksi `lockForUpdate`.
- **AC:** booking ke-2 gagal; kuota tidak pernah minus.
- **File:** `app/Http/Controllers/BookingController.php`, `app/Models/TourPackage.php`.

### S1-3 — Test end-to-end alur booking `[3 SP]`
- **Kerjakan:** `tests/Feature/BookingFlowTest.php` — `create` (review order) → `store`.
- **AC:** validasi data tamu & T&C diuji; booking sukses berstatus `pending` dengan `booking_code` ter-generate; user belum verifikasi email ditolak.

### S1-4 — Lengkapi `.env.example` `[1 SP]`
- **Kerjakan:** tambah `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`, `MIDTRANS_MERCHANT_ID`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (placeholder). Set `APP_DEBUG=false` + komentar checklist produksi.
- **AC:** clone baru bisa dikonfigurasi penuh hanya dari `.env.example`.

### S1-5 — README runbook `[2 SP]`
- **Kerjakan:** ganti README bawaan Laravel. Wajib memuat: langkah setup, **kewajiban menjalankan `php artisan queue:work`** (kalau tidak, email konfirmasi tak terkirim) & **cron `schedule:run`** (kalau tidak, kuota tak pernah bebas & auto-complete mati), cara konfigurasi Midtrans sandbox + Google OAuth, cara menjalankan test.
- **AC:** orang lain bisa menjalankan sistem dari nol mengikuti README.

### S1-6 — Rate limiting autentikasi `[2 SP]`
- **Kerjakan:** tambahkan `->middleware('throttle:5,1')` pada route `login`, `register`, `password.email`, `password.update` (`routes/web.php:43,45,53,55`). Tulis test: percobaan ke-6 → HTTP 429.
- **AC:** brute-force/flood diblokir; test hijau.

**Risiko sprint:** factory untuk model finansial perlu konsisten dengan constraint DB — kerjakan S1-1 lebih dulu agar S1-2/S1-3 memakai factory yang sama.

---

## 4. Sprint 2 — Tiket & Kontrol Booking (P1a)

> **Sprint Goal:** E-ticket berhenti menjadi dekoratif (bisa di-scan & divalidasi di lokasi), dan user/admin punya kontrol pembatalan & refund.

| ID | Item | Est | Dep |
|----|------|-----|-----|
| S2-1 | QR check-in / validasi tiket di lokasi | 8 | — |
| S2-2 | Pembatalan booking oleh user | 5 | — |
| S2-3 | Inisiasi refund oleh admin | 5 | S2-2 |
| S2-4 | Test check-in & pembatalan | 3 | S2-1, S2-2 |

### S2-1 — QR check-in / validasi tiket `[8 SP]` 🔴 *lubang fungsional terbesar*
- **Masalah:** QR saat ini tidak dikonsumsi apa pun; view menjanjikan "Petugas kami akan memindai" tapi tak ada mekanismenya.
- **Kerjakan:**
  - Migration: tambah `ticket_token` (random/HMAC), `checked_in_at`, `checked_in_by` pada `bookings`.
  - Ubah payload QR agar memuat token bertanda-tangan (bukan hanya `booking_code` berurutan) — sekaligus menutup temuan keamanan "QR bisa ditebak".
  - Halaman/aksi petugas (Filament page atau route ber-otorisasi admin) untuk scan/validasi: input token → tampilkan detail booking → tombol "Check-in".
  - Aturan: hanya `paid`/`confirmed`/`completed` yang valid; check-in **idempoten** (scan kedua menampilkan "sudah check-in"); token palsu/kedaluwarsa ditolak.
- **AC:** tiket valid → sekali check-in sukses; scan ulang memberi peringatan; tiket palsu/belum bayar ditolak.

### S2-2 — Pembatalan booking oleh user `[5 SP]`
- **Kerjakan:** route `POST /booking/{booking}/cancel` (owner-scoped, cek `user_id`), tombol di halaman detail booking. Hanya status `pending` (kebijakan T&C) yang boleh dibatalkan user; gunakan transisi state-machine (`Booking::ALLOWED_TRANSITIONS`); kuota otomatis bebas.
- **AC:** pemilik dapat membatalkan `pending`; tidak bisa membatalkan `completed`; non-pemilik → 403; transisi ilegal ditolak observer.
- **File:** `app/Http/Controllers/BookingController.php`, `app/Models/Booking.php`, `app/Observers/BookingObserver.php`.

### S2-3 — Inisiasi refund oleh admin `[5 SP]`
- **Masalah:** webhook *menerima* status refund, tapi tak ada cara memicunya dari aplikasi → state bisa drift dari Midtrans.
- **Kerjakan:** Filament action "Refund" di `BookingResource` → panggil API refund Midtrans (atau, bila sandbox terbatas, tandai `refunded` + catat di payment + audit note) → set status sesuai state-machine.
- **AC:** admin dapat memicu; state booking & payment konsisten; aksi tercatat di log.
- **Catatan:** verifikasi ketersediaan API refund di sandbox; bila tidak ada, implementasi "mark refunded + instruksi dashboard manual".

### S2-4 — Test check-in & pembatalan `[3 SP]`
- **AC:** test menutup: check-in idempoten, tiket palsu ditolak, pembatalan owner-only + transisi valid.

---

## 5. Sprint 3 — Penemuan & Keamanan Data (P1b)

> **Sprint Goal:** Katalog paket dapat dicari/difilter, riwayat finansial tak bisa terhapus permanen, dan ada jalur deploy yang reproducible dengan CI.

| ID | Item | Est | Dep |
|----|------|-----|-----|
| S3-1 | Search & filter paket wisata (+ kategori) | 8 | — |
| S3-2 | Proteksi data finansial dari cascade-delete | 5 | — |
| S3-3 | Unique constraint 1-review-per-booking (DB) | 1 | — |
| S3-4 | Deployment artifact + CI gate | 8 | — |

### S3-1 — Search & filter paket `[8 SP]`
- **Masalah:** `TourPackageController::index` hanya `latest()->paginate(9)`, mengabaikan query; tak ada kolom kategori. (Blog sudah punya search — jadikan acuan.)
- **Kerjakan:** migration kolom/relasi kategori paket; `index()` baca query (`q`, `category`, rentang harga, sort); form search/filter di view; pagination mempertahankan query string.
- **AC:** pencarian mengembalikan hasil cocok; filter bisa dikombinasikan; pindah halaman tidak menghilangkan filter.

### S3-2 — Proteksi cascade-delete `[5 SP]` 🟠 *risiko kehilangan data*
- **Masalah:** hapus 1 paket di Filament = hapus permanen booking + payment + review (lihat migrasi `cascadeOnDelete`).
- **Kerjakan:** pilih strategi — (a) `SoftDeletes` pada `Booking`/`Payment`, **atau** (b) ubah FK ke `restrictOnDelete` + larang menghapus paket yang punya booking (override di `TourPackageResource`).
- **AC:** menghapus paket yang memiliki booking diblokir atau di-soft-delete; riwayat finansial tetap utuh.

### S3-3 — Unique constraint review `[1 SP]`
- **Kerjakan:** migration unique index `reviews.booking_id` (saat ini hanya dijaga di kode, rawan duplikat dari admin/konkuren).
- **AC:** percobaan review kedua untuk booking sama gagal di level DB.

### S3-4 — Deployment + CI `[8 SP]`
- **Kerjakan:** Dockerfile + `docker-compose` (app, db, **queue worker**, **scheduler**) atau Procfile PaaS; GitHub Actions yang menjalankan `php artisan test` + Pint pada push/PR (lengkapi `.github/workflows/` yang sekarang hanya tag rilis manual).
- **AC:** CI gagal bila ada test merah; ada jalur deploy terdokumentasi yang menyertakan worker & cron.

---

## 6. Sprint 4 — Hardening Keamanan & Ops (P2)

> **Sprint Goal:** Menutup celah defense-in-depth dan menambah observability dasar.

| ID | Item | Est | Dep |
|----|------|-----|-----|
| S4-1 | Sanitasi XSS konten rich (blog/paket/homepage) | 3 | — |
| S4-2 | Perketat mass-assignment (`role`/`total_price`/`status`/`user_id`) | 2 | — |
| S4-3 | Profil: wajib `current_password` + re-verifikasi email saat ganti | 3 | — |
| S4-4 | Error tracking + monitoring failed-jobs + dok. backup | 3 | — |
| S4-5 | Throttle API kuota | 1 | — |

- **S4-1:** pasang sanitizer (mis. `mews/purifier`) pada output `{!! !!}` (`blog/show.blade.php:77`, `packages/show.blade.php:95`, embed maps `home.blade.php`).
- **S4-2:** keluarkan `role` dari `$fillable` `User`; `user_id`/`total_price`/`status` dari `$fillable` `Booking`; set eksplisit.
- **S4-3:** rule `current_password` untuk ganti password/email; null-kan `email_verified_at` + kirim ulang verifikasi saat email berubah (`ProfileController.php:20-31`).
- **S4-4:** integrasi Sentry/Flare; alert `failed_jobs`; tulis strategi backup DB + storage.
- **S4-5:** pindahkan `/api/kuota/...` ke grup `throttle`.

---

## 7. Sprint 5 — UX & Growth *(opsional / future work)*

Item ini cocok sebagai **"Pengembangan Selanjutnya"** di laporan TA:

| Item | Nilai | Est |
|------|-------|-----|
| Reminder H-1 sebelum kunjungan (scheduled notification) | Retensi | 5 |
| Notifikasi admin saat ada booking baru | Operasional | 3 |
| Upload foto review (aktifkan kolom `reviews.photos` yang kini mati) | UX | 3 |
| Reschedule / ubah tanggal booking | Fleksibilitas | 5 |
| Export laporan revenue/okupansi (CSV/Excel) | Bisnis | 3 |
| Tabel audit log perubahan status (kini hanya ke file log) | Akuntabilitas | 3 |
| Kalender ketersediaan bulanan | UX | 5 |
| Hapus akun sendiri (privasi/UU PDP) | Kepatuhan | 2 |

---

## 8. Cara Memakai Dokumen Ini

1. Kerjakan **per sprint berurutan**; jangan lompat sebelum DoD sprint terpenuhi.
2. Untuk tiap item: buat branch, penuhi **acceptance criteria**, pastikan test hijau, lalu PR/commit.
3. Update kolom status (tambah kolom "Status: ⬜/🟦/✅" bila ingin melacak progres harian).
4. Setelah **Sprint 1** selesai → sistem layak sidang. Setelah **Sprint 3** → layak pilot/production terbatas.

> Setiap item dapat ditelusuri ke temuan Gap Analysis dan ke file kode nyata, sehingga eksekusi tidak bergantung pada interpretasi.
