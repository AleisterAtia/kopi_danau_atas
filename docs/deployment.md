# Deployment & CI
# E-Tourism Information System — CV Kopi Danau Atas

> **Sumber:** Sprint 3 (S3-4) pada `docs/sprint-planning.md`.

Dokumen ini menjelaskan **jalur deploy yang reproducible** menggunakan Docker
serta **gerbang kualitas (CI)** menggunakan GitHub Actions.

---

## 1. Arsitektur container

`docker-compose.yml` menjalankan service-service yang mencerminkan kebutuhan
produksi sebenarnya:

| Service | Perintah | Kenapa wajib |
|---------|----------|--------------|
| `app` | `php artisan serve` (:8000) | Aplikasi web. Juga menjalankan migrasi saat start (`RUN_MIGRATIONS=true`). |
| `queue` | `php artisan queue:work` | **Tanpa ini email konfirmasi & e-tiket tidak pernah terkirim** (job antri selamanya). |
| `scheduler` | `php artisan schedule:work` | **Tanpa ini kuota tidak pernah dibebaskan** (booking pending tak kedaluwarsa) & auto-complete mati. |
| `reverb` | `php artisan reverb:start` | Websocket server untuk notifikasi realtime admin. **Tidak wajib** — WA (Fonnte) & push notification tetap jalan tanpa ini; yang mati cuma toast realtime di panel admin. |
| `db` | MySQL 8 | Basis data. Punya healthcheck agar service lain menunggu DB siap. |

`app` dan `reverb` publish ke `127.0.0.1` saja (bukan `0.0.0.0`) — HTTPS di-terminate
oleh reverse proxy di **host** (Nginx + Let's Encrypt, lihat 1b), bukan di dalam
compose. **Wajib untuk push notification** (browser menolak Push API di luar
HTTPS/localhost). Kalau tidak mau buka port 80/443 sendiri, service `cloudflared`
(Cloudflare Tunnel) bisa dipakai sebagai gantinya — tapi sejak Cloudflare
mewajibkan kartu untuk aktivasi Zero Trust (termasuk tier gratis), proyek ini
default ke Nginx + Let's Encrypt yang tidak butuh akun pihak ketiga sama sekali.

`app`, `queue`, `scheduler`, dan `reverb` memakai **image yang sama**
(`kopi-danau-atas`) dengan perintah berbeda — persis pola yang dianjurkan
untuk produksi (worker & cron sebagai proses terpisah, bukan dijalankan di
dalam request web).

> **Catatan produksi.** `php artisan serve` cukup untuk demo/pilot. Untuk
> produksi sungguhan, ganti service `app` dengan Nginx + PHP-FPM. Struktur
> compose (worker & scheduler terpisah) tetap sama.

### 1a. Dua pasang host/port Reverb yang gampang tertukar

- **`REVERB_HOST`/`REVERB_PORT`/`REVERB_SCHEME`** (tanpa prefix `VITE_`) dipakai
  Laravel **di dalam container** untuk mendorong event ke Reverb. Di
  docker-compose nilainya `reverb` / `8080` / `http` (nama service, bukan
  `localhost` — tiap container punya `localhost` sendiri-sendiri). Ini tidak
  pernah keluar dari jaringan Docker, jadi tidak butuh HTTPS.
- **`VITE_REVERB_HOST`/`VITE_REVERB_PORT`/`VITE_REVERB_SCHEME`** di-*bake* ke
  bundle JS browser saat `npm run build`, jadi harus alamat **publik** yang
  bisa diakses browser pengunjung — hostname Cloudflare Tunnel di atas port
  443 dengan scheme `https`.

Salah taruh (mis. `VITE_REVERB_HOST=reverb`) membuat browser mencoba connect
ke `reverb` yang tidak resolve di internet — toast realtime diam-diam tidak
pernah muncul, tanpa error yang jelas di console.

### 1b. Kenapa Cloudflare Tunnel, bukan Nginx + certbot

Push notification browser **mewajibkan HTTPS** (kecuali di `localhost`) —
tanpa ini tombol "Aktifkan Notifikasi" gagal total. Cloudflare Tunnel dipilih
karena tidak perlu buka port 80/443 di VPS, tidak perlu urus renewal
sertifikat (certbot cron), dan jalan di belakang NAT/firewall apa pun — cocok
untuk VPS murah yang dikelola sendiri tanpa tim ops. Setup di dashboard:

1. `dash.cloudflare.com` → domain Anda → **Zero Trust** → **Networks** →
   **Tunnels** → buat tunnel baru, tipe **Docker**.
2. Copy token yang diberikan ke `TUNNEL_TOKEN` di `.env`.
3. Di tunnel yang sama, tambah 2 **Public Hostname**:
   - `kopidanauatas.com` (atau domain Anda) → Service `HTTP` → `app:8000`
   - `ws.kopidanauatas.com` → Service `HTTP` → `reverb:8080`
4. Set `VITE_REVERB_HOST=ws.kopidanauatas.com`, `VITE_REVERB_PORT=443`,
   `VITE_REVERB_SCHEME=https`, lalu `npm run build` ulang (nilai ini di-bake
   ke JS, ganti .env saja tidak cukup tanpa rebuild).
5. `APP_URL=https://kopidanauatas.com` di `.env`.

Kalau lebih nyaman pakai Nginx + Let's Encrypt manual (lebih familiar tapi
butuh buka port 80/443 + cron renewal certbot sendiri), hapus service
`cloudflared` dari compose dan pakai itu sebagai gantinya — struktur service
lain tidak berubah.

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

---

## 5. Continuous Deployment (CD) — auto-deploy ke VPS

Job `deploy` di `ci.yml` jalan **hanya setelah job `test` lolos, dan hanya untuk
push ke `main`** (bukan pull request) — SSH ke VPS lalu `git pull` + rebuild.
Tanpa ini, push ke GitHub tidak menyentuh VPS sama sekali; harus manual.

### Setup sekali di VPS

```bash
# Bikin key pair khusus untuk deploy (jangan pakai key pribadi Anda)
ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N ""
cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys

# Clone repo ke path yang akan dipakai terus-menerus
git clone <url-repo-anda> ~/kopi-danau-atas
cd ~/kopi-danau-atas
cp .env.example .env   # lalu isi semua kredensial produksi
docker compose build
docker compose run --rm app php artisan key:generate
docker compose up -d
```

### Setup sekali di GitHub

Repo → **Settings → Secrets and variables → Actions** → tambah 4 secret:

| Secret | Isi |
|--------|-----|
| `VPS_HOST` | IP atau domain VPS |
| `VPS_USERNAME` | User SSH di VPS (mis. `root` atau user non-root dengan akses `docker`) |
| `VPS_SSH_KEY` | Isi **private key** `~/.ssh/deploy_key` (bukan `.pub`) dari langkah di atas |
| `VPS_DEPLOY_PATH` | Path absolut hasil clone di atas, mis. `/root/kopi-danau-atas` |

Setelah ini terisi, tiap push ke `main` yang lolos test otomatis `git pull` +
`docker compose build && up -d` di VPS — tidak perlu SSH manual lagi.

> **Catatan RAM.** `docker compose build` di VPS 1GB bisa spike memori saat
> `npm run build` jalan (lihat bagian 1b) — kalau auto-deploy sering gagal
> karena OOM, tambah swap file atau naik ke VPS 2GB.
