# Deployment & CI
# E-Tourism Information System — CV Kopi Danau Atas

> **Sumber:** Sprint 3 (S3-4) pada `docs/sprint-planning.md`.

Dokumen ini menjelaskan **jalur deploy yang reproducible** menggunakan Docker
serta **gerbang kualitas (CI)** menggunakan GitHub Actions.

---

## 1. Arsitektur container

`docker-compose.yml` menjalankan **empat** service yang mencerminkan kebutuhan
produksi sebenarnya:

| Service | Perintah | Kenapa wajib |
|---------|----------|--------------|
| `app` | `php artisan serve` (:8000) | Aplikasi web. Juga menjalankan migrasi saat start (`RUN_MIGRATIONS=true`). |
| `queue` | `php artisan queue:work` | **Tanpa ini email konfirmasi & e-tiket tidak pernah terkirim** (job antri selamanya). |
| `scheduler` | `php artisan schedule:work` | **Tanpa ini kuota tidak pernah dibebaskan** (booking pending tak kedaluwarsa) & auto-complete mati. |
| `db` | MySQL 8 | Basis data. Punya healthcheck agar service lain menunggu DB siap. |

`app`, `queue`, dan `scheduler` memakai **image yang sama** (`kopi-danau-atas`)
dengan perintah berbeda — persis pola yang dianjurkan untuk produksi (worker &
cron sebagai proses terpisah, bukan dijalankan di dalam request web).

> **Catatan produksi.** `php artisan serve` cukup untuk demo/pilot. Untuk
> produksi sungguhan, ganti service `app` dengan Nginx + PHP-FPM. Struktur
> compose (worker & scheduler terpisah) tetap sama.

---

## 2. Build image

`Dockerfile` memakai **multi-stage build**:

1. **Stage `assets`** (`node:20-alpine`) — `npm ci` lalu `npm run build`
   menghasilkan aset Vite (`public/build`).
2. **Stage `app`** (`php:8.3-cli`) — memasang ekstensi PHP (`pdo_mysql`,
   `mbstring`, `gd`, `zip`, `bcmath`, `intl`, `exif`, `pcntl`), `composer
   install --no-dev`, menyalin kode + aset hasil stage 1, lalu meng-optimize
   autoloader.

---

## 3. Langkah menjalankan

```bash
# 1. Siapkan environment
cp .env.example .env
#    Edit .env — minimal:
#      APP_ENV=production
#      APP_DEBUG=false
#      DB_CONNECTION=mysql
#      DB_HOST=db                 # nama service, BUKAN 127.0.0.1
#      DB_DATABASE=kopi_danau_atas
#      DB_USERNAME=kopi
#      DB_PASSWORD=secret         # samakan dengan docker-compose.yml
#      QUEUE_CONNECTION=database
#    plus kredensial Midtrans & Google OAuth (lihat README).

# 2. Build semua image
docker compose build

# 3. Generate APP_KEY (sekali saja)
docker compose run --rm app php artisan key:generate

# 4. Jalankan semua service (migrasi otomatis jalan di service app)
docker compose up -d

# 5. (opsional) seed data contoh
docker compose exec app php artisan db:seed
```

Aplikasi tersedia di `http://localhost:8000`, panel admin di
`http://localhost:8000/admin`.

Hentikan dengan `docker compose down` (tambahkan `-v` untuk menghapus volume DB).

---

## 4. Continuous Integration (CI)

`.github/workflows/ci.yml` berjalan otomatis pada **setiap push ke `main` dan
setiap pull request**. Langkahnya:

1. Setup PHP 8.3 + ekstensi (termasuk `pdo_sqlite` untuk test).
2. Setup Node 20 + `npm ci && npm run build` (agar view ber-`@vite` ter-render).
3. `composer install` (dengan cache).
4. `php artisan key:generate`.
5. **`vendor/bin/pint --test`** — gagal bila ada pelanggaran gaya kode.
6. **`php artisan test`** — gagal bila ada test merah.

Test memakai SQLite in-memory (lihat `phpunit.xml`), jadi CI tidak butuh service
database — cepat dan deterministik.

> **Acceptance criteria S3-4 terpenuhi:** CI gagal bila ada test merah atau gaya
> kode melanggar; jalur deploy terdokumentasi dan menyertakan **queue worker**
> serta **scheduler/cron**.

Workflow rilis manual (`.github/workflows/auto-tag-release.yml`) tetap terpisah
dan tidak terpengaruh.
