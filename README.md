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

Sistem memerlukan **tiga proses** agar berfungsi penuh. Cara termudah memakai
skrip gabungan:

```bash
composer dev
```

Skrip ini menyalakan sekaligus: **web server**, **queue worker**, **log viewer (pail)**, dan **Vite**.

Bila ingin menjalankan manual, butuh minimal tiga terminal:

```bash
php artisan serve            # 1) Web server
php artisan queue:work       # 2) Queue worker  (WAJIB — lihat catatan di bawah)
npm run dev                  # 3) Vite (hot reload aset)
```

### ⚠️ Dua dependensi runtime yang WAJIB ada

| Proses | Bila tidak dijalankan |
|--------|-----------------------|
| **`php artisan queue:work`** | Email konfirmasi + e-ticket + invoice **tidak akan terkirim** (email di-`queue`, bukan dikirim sinkron). |
| **Scheduler (`php artisan schedule:run` tiap menit via cron)** | Booking `pending` **tidak pernah kedaluwarsa** (kuota tidak dibebaskan) dan booking lampau **tidak auto-complete**. |

Scheduler menjalankan dua perintah terjadwal (`routes/console.php`):

- `bookings:expire-pending` — meng-expire booking `pending` > 1 jam (tiap 15 menit).
- `bookings:auto-complete` — `paid`/`confirmed` → `completed` setelah `visit_date` lewat (harian 23:55).

Di server produksi, daftarkan satu cron:

```cron
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

dan jalankan worker sebagai layanan (mis. `supervisor` / `systemd`):

```bash
php artisan queue:work --tries=3 --timeout=90
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
- [ ] `MIDTRANS_IS_PRODUCTION=true` + kunci produksi; webhook URL produksi terdaftar.
- [ ] Queue worker berjalan sebagai layanan; cron `schedule:run` aktif.
- [ ] `php artisan storage:link` dijalankan; aset dibangun (`npm run build`).
- [ ] `php artisan config:cache route:cache view:cache` untuk performa.
- [ ] Backup database & folder `storage/app`.

> Untuk daftar gap menuju production lengkap (keamanan, fitur, ops), lihat
> [`docs/sprint-planning.md`](docs/sprint-planning.md).
