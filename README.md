# E-Tourism Information System — CV Kopi Danau Diatas

Sistem informasi e-tourism untuk CV Kopi Danau Diatas: katalog paket wisata kebun
kopi, pemesanan (booking) dengan kuota harian, pembayaran via **Midtrans Snap**,
e-ticket QR + invoice PDF, ulasan, dan panel admin **Filament**.

> Tugas Akhir — Fadhil Dzaky Arhab (2301092010).
> Dokumentasi UML & perencanaan ada di folder [`docs/`](docs/)
> (use case, activity diagram, sprint planning).

---

## Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 11 (PHP 8.3) |
| Admin panel | Filament 3.3 |
| Pembayaran | Midtrans Snap (`midtrans/midtrans-php`) |
| Login sosial | Google OAuth (Laravel Socialite) |
| PDF / QR | `barryvdh/laravel-dompdf`, `simplesoftwareio/simple-qrcode` |
| Antrian / cache / sesi | database (default) |

---

## Prasyarat

- PHP **8.3+** dengan ekstensi umum Laravel (`pdo`, `mbstring`, `openssl`, `gd`/`imagick` untuk QR PNG).
- Composer 2.
- Node.js 18+ & npm (build aset Vite).
- Database: **SQLite** (default, paling cepat untuk dev) atau MySQL/MariaDB.
- Akun **Midtrans Sandbox** dan kredensial **Google OAuth** (lihat di bawah).

---

## Setup cepat

```bash
# 1. Dependensi
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
#  -> isi MIDTRANS_* dan GOOGLE_* di .env (lihat bagian "Konfigurasi Integrasi")

# 3. Database (SQLite)
#  Windows PowerShell:  New-Item -ItemType File database/database.sqlite
#  Linux/macOS:         touch database/database.sqlite
php artisan migrate --seed   # --seed opsional bila ada seeder

# 4. Storage symlink (agar QR & gambar tampil)
php artisan storage:link

# 5. Build aset
npm run build
```

---

## Menjalankan (development)

Sistem memerlukan **beberapa proses** agar berfungsi penuh. Cara termudah memakai
skrip gabungan:

```bash
composer dev
```

Skrip ini menyalakan sekaligus lima proses: **web server**, **queue worker**
(`queue:listen`), **log viewer (pail)**, **Vite**, dan **Reverb** (websocket
server untuk notifikasi realtime di panel admin).

Bila ingin menjalankan manual, butuh minimal empat terminal:

```bash
php artisan serve            # 1) Web server
php artisan queue:work       # 2) Queue worker  (WAJIB — lihat catatan di bawah)
php artisan reverb:start     # 3) Websocket server (WAJIB untuk notifikasi realtime admin)
npm run dev                  # 4) Vite (hot reload aset)
```

### ⚠️ Dependensi runtime yang WAJIB ada

| Proses | Bila tidak dijalankan |
|--------|-----------------------|
| **`php artisan queue:work`** | Email konfirmasi + e-ticket + invoice **tidak akan terkirim** (email di-`queue`, bukan dikirim sinkron), dan notifikasi WhatsApp/push ke admin juga tertunda (dikirim via job antrian, lihat `app/Jobs/NotifyAdminsOfBookingPaid.php`). |
| **`php artisan reverb:start`** | Lonceng notifikasi **tidak update realtime** — hanya fallback polling tiap beberapa detik yang jalan (`databaseNotificationsPolling()` di `AdminPanelProvider`). Tabel booking & widget "Latest Bookings" tetap auto-refresh tanpa Reverb karena keduanya pakai polling Livewire (`->poll()`), bukan websocket. |
| **Scheduler (`php artisan schedule:run` tiap menit via cron)** | Booking `pending` **tidak pernah kedaluwarsa** (kuota tidak dibebaskan) dan booking lampau **tidak auto-complete**. |

Scheduler menjalankan dua perintah terjadwal (`routes/console.php`):

- `bookings:expire-pending` — meng-expire booking `pending` > 1 jam (tiap 15 menit).
- `bookings:auto-complete` — `paid`/`confirmed` → `completed` setelah `visit_date` lewat (harian 23:55).

Di server produksi, daftarkan satu cron:

```cron
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

dan jalankan worker + Reverb sebagai layanan (mis. `supervisor` / `systemd`,
atau lihat `docker-compose.yml` yang sudah menyediakan container terpisah
untuk `queue`, `scheduler`, dan `reverb`):

```bash
php artisan queue:work --tries=3 --timeout=90
php artisan reverb:start --host=0.0.0.0 --port=8080
```

---

## Konfigurasi Integrasi

### Midtrans (Sandbox)
1. Daftar di <https://dashboard.sandbox.midtrans.com>.
2. **Settings → Access Keys**, salin `Server Key`, `Client Key`, `Merchant ID`.
3. Isi di `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
   MIDTRANS_MERCHANT_ID=Gxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
4. **Settings → Configuration**, set *Payment Notification URL* ke
   `https://<domain-anda>/api/midtrans/notification` (gunakan tunneling seperti
   `ngrok` saat development agar webhook bisa mencapai mesin lokal).

### Google OAuth
1. <https://console.cloud.google.com> → **APIs & Services → Credentials → OAuth client ID** (Web application).
2. Tambah *Authorized redirect URI*: `http://localhost:8000/auth/google/callback`.
3. Isi di `.env`:
   ```env
   GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxx
   GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
   ```

### Notifikasi realtime admin (Reverb)
Panel admin (`/admin`) menampilkan lonceng notifikasi & tabel booking yang
update otomatis tanpa refresh, memakai **Laravel Reverb** (websocket
self-hosted) + polling singkat sebagai fallback.

1. Jalankan `php artisan reverb:start` (sudah termasuk di `composer dev`).
2. Isi `REVERB_APP_ID`/`REVERB_APP_KEY`/`REVERB_APP_SECRET` di `.env` (bebas
   diisi string acak apa saja, asal konsisten) dan pastikan `VITE_REVERB_*`
   ikut ter-set (nilainya mengikuti `REVERB_*` lewat `${...}` di
   `.env.example`).
3. **Di produksi**: `REVERB_*` (server-ke-server) dan `VITE_REVERB_*`
   (di-bundle ke browser, harus alamat publik `wss://`) **tidak boleh sama**
   — lihat komentar di `.env.example` dan `docs/deployment.md`. Setup yang
   sudah disiapkan di `docker-compose.yml` memakai Cloudflare Tunnel supaya
   tidak perlu urus sertifikat TLS manual.

### WhatsApp admin (Fonnte) — opsional
Mengirim WhatsApp ke nomor HP admin (kolom `phone` di profil user) setiap
ada booking yang dibayar.

1. Daftar di <https://fonnte.com>, hubungkan nomor WhatsApp sebagai device,
   salin token-nya.
2. Isi `FONNTE_TOKEN` di `.env`. Kosongkan untuk mematikan fitur ini
   (fail-soft, tidak error).

### Push notification browser (Web Push) — opsional
Tombol "Aktifkan Notifikasi" di topbar admin mengirim notifikasi OS-level
(via Chrome/Firefox) walau tab panel tidak terbuka.

1. Generate key pair sekali: `php artisan webpush:vapid` — otomatis
   menuliskan `VAPID_PUBLIC_KEY`/`VAPID_PRIVATE_KEY` ke `.env`.
2. Isi `VAPID_SUBJECT` (opsional; bila kosong jatuh ke `APP_URL`).
3. **Wajib HTTPS** — Web Push API diblokir browser di koneksi non-HTTPS
   kecuali `localhost`. Di produksi, `APP_URL` harus `https://...` (lihat
   [Checklist produksi](#checklist-produksi-ringkas)).
4. Izin notifikasi & subscription **terikat per-origin browser** — pindah
   dari `localhost:8000` ke domain produksi berarti setiap admin harus
   klik izinkan sekali lagi di domain yang sebenarnya (bukan bug).

### Auto-translate konten (Gemini) — opsional
Konten admin (nama/deskripsi paket, dll.) yang punya versi Bahasa Indonesia
otomatis diterjemahkan ke Inggris saat disimpan, lewat Google Gemini.

1. Ambil API key gratis di <https://aistudio.google.com/apikey>.
2. Isi `GEMINI_API_KEY` di `.env`. Kosongkan untuk mematikan (fail-soft —
   versi Inggris jatuh ke teks Indonesia sebagai fallback, bukan kosong).
3. `php artisan content:translate` — backfill terjemahan yang belum ada,
   aman dijalankan berulang.

### Captcha pendaftaran (Cloudflare Turnstile) — opsional
1. Daftar site key gratis di <https://dash.cloudflare.com> → **Turnstile**
   (tambahkan `localhost` untuk dev).
2. Isi **keduanya** `TURNSTILE_SITE_KEY` & `TURNSTILE_SECRET_KEY` — isi
   salah satu saja membuat form registrasi tidak bisa disubmit. Kosongkan
   keduanya untuk mematikan captcha.

---

## Testing

Test memakai SQLite in-memory (lihat `phpunit.xml`) — tidak menyentuh database dev.

```bash
php artisan test                       # seluruh suite
php artisan test --filter=Midtrans     # contoh: hanya test webhook
```

Cakupan jalur kritis (uang & konkurensi):

| Test | Yang dijamin |
|------|--------------|
| `tests/Feature/MidtransWebhookTest.php` | Verifikasi tanda tangan webhook, pemetaan status, **idempotensi** (efek samping bayar hanya sekali). |
| `tests/Feature/BookingQuotaTest.php` | Proteksi overbooking (kuota dicek ulang dalam transaksi ber-lock). |
| `tests/Feature/BookingFlowTest.php` | Gate verifikasi email, pre-check kuota, persetujuan S&K, pembuatan booking. |
| `tests/Feature/AuthRateLimitTest.php` | Rate limiting login (anti brute-force). |
| `tests/Unit/BookingStateMachineTest.php` | Transisi status yang sah. |
| `tests/Unit/MidtransSignatureTest.php` | Hashing tanda tangan Midtrans. |

Lint gaya kode:

```bash
vendor/bin/pint
```

---

## Checklist produksi (ringkas)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` ter-generate.
- [ ] `APP_URL` diisi domain produksi dengan **`https://`** (dipakai juga sebagai
      fallback `VAPID_SUBJECT` untuk push notification — lihat di bawah).
- [ ] `MIDTRANS_IS_PRODUCTION=true` + kunci produksi; webhook URL produksi terdaftar.
- [ ] Queue worker (`queue:work`) berjalan sebagai layanan; cron `schedule:run` aktif.
- [ ] **Reverb** (`reverb:start`) berjalan sebagai layanan, dan bisa diakses lewat
      `wss://` publik (mis. via Cloudflare Tunnel di `docker-compose.yml`) —
      tanpa ini, lonceng notifikasi admin jatuh ke polling saja (tabel booking
      tidak terpengaruh, itu polling Livewire biasa, bukan websocket).
- [ ] Situs benar-benar diakses lewat HTTPS (bukan cuma `APP_URL` yang diisi
      https) — Web Push API diblokir browser di koneksi non-HTTPS.
- [ ] `VAPID_PUBLIC_KEY`/`VAPID_PRIVATE_KEY` terisi (boleh reuse dari dev);
      admin perlu klik "Aktifkan Notifikasi" ulang di domain produksi karena
      izin browser terikat per-origin.
- [ ] `FONNTE_TOKEN` terisi & valid bila notifikasi WhatsApp admin dipakai
      (token yang expired gagal **senyap** kalau tidak dicek manual).
- [ ] `php artisan storage:link` dijalankan; aset dibangun (`npm run build`).
- [ ] `php artisan config:cache route:cache view:cache` untuk performa.
- [ ] Backup database & folder `storage/app`.

> Untuk daftar gap menuju production lengkap (keamanan, fitur, ops), lihat
> [`docs/sprint-planning.md`](docs/sprint-planning.md).
