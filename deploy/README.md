# 📖 Panduan Deployment & CI/CD Keuangan Gereja

Dokumentasi ini menjelaskan langkah-langkah setup infrastruktur produksi untuk **Keuangan Gereja** menggunakan:
- **CI/CD:** GitHub Actions + GitHub Container Registry (`ghcr.io`) + SSH Deploy (`appleboy/ssh-action`)
- **Database:** Supabase (PostgreSQL Managed Database)
- **Object Storage:** Cloudflare R2 (S3-Compatible Storage)
- **Edge Network & SSL:** Cloudflare Proxy (Awan Oranye)
- **Server:** VPS Ubuntu/Debian dengan Docker & Portainer

---

## 📑 Daftar Isi
1. [Langkah 1: Setup Supabase Database](#1-setup-supabase-database)
2. [Langkah 2: Setup Cloudflare R2 Storage](#2-setup-cloudflare-r2-storage)
3. [Langkah 3: Setup SSH Key & GitHub Secrets](#3-setup-ssh-key--github-secrets)
4. [Langkah 4: Setup Server VPS](#4-setup-server-vps)
5. [Langkah 5: Uji Coba Deployment CI/CD](#5-uji-coba-deployment-cicd)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Setup Supabase Database
1. Buka [Supabase Dashboard](https://supabase.com/dashboard) dan buat project baru (atau pilih project yang sudah ada).
2. Masuk ke **Project Settings -> Database**.
3. Di bagian **Connection string**, pilih mode **Connection pooling (Session/Transaction mode)** atau **Direct connection**.
   - Host: `aws-0-ap-southeast-1.pooler.supabase.com` (atau sesuai region project Anda)
   - Port: `6543` (Pooler) atau `5432`
   - Database: `postgres`
   - User: `postgres.your-project-ref`
   - Password: Password database yang Anda tentukan saat membuat project Supabase.

---

## 2. Setup Cloudflare R2 Storage
1. Buka [Cloudflare Dashboard](https://dash.cloudflare.com/) -> **R2 Object Storage**.
2. Klik **Create Bucket**, beri nama misalnya `keuangan-gereja-bucket`.
3. Di menu **Manage R2 API Tokens**, klik **Create API token**:
   - Permission: **Object Read & Write**
   - Apply to bucket: Pilih bucket `keuangan-gereja-bucket`
   - Salin nilai **Access Key ID** dan **Secret Access Key**.
4. Endpoint R2 adalah: `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`.
5. Di pengaturan bucket, aktifkan **Public Development URL** (atau Custom Domain seperti `media.domain-gereja.org`) agar file/lampiran dapat diakses publik melalui browser.

---

## 3. Setup SSH Key & GitHub Secrets

### A. Buat SSH Key Khusus Deployment (di komputer lokal atau VPS):
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key
```

- Salin isi `github_deploy_key.pub` (Public Key) dan tambahkan ke file `~/.ssh/authorized_keys` di VPS:
  ```bash
  cat ~/.ssh/github_deploy_key.pub >> ~/.ssh/authorized_keys
  chmod 600 ~/.ssh/authorized_keys
  ```
- Salin isi `github_deploy_key` (Private Key) untuk didaftarkan ke GitHub Secrets.

### B. Daftarkan GitHub Secrets:
Buka repository Anda di GitHub -> **Settings -> Secrets and variables -> Actions -> New repository secret**:

| Secret Name | Deskripsi | Contoh Nilai |
| :--- | :--- | :--- |
| `VPS_HOST` | IP Publik atau domain VPS | `103.175.xxx.xxx` |
| `VPS_USERNAME` | Username SSH di VPS | `root` atau `ubuntu` |
| `VPS_SSH_KEY` | Private SSH Key lengkap | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `VPS_SSH_PORT` | Port SSH VPS (default 22) | `22` |
| `VPS_DEPLOY_PATH` | Path direktori stack di VPS | `/opt/stacks/keuangan-gereja` |

> Catatan `VPS_SSH_KEY`: simpan isi private key lengkap tanpa tambahan tanda kutip. Format multi-line maupun format satu baris dengan `\n` keduanya didukung oleh workflow deploy.

---

## 4. Setup Server VPS

### A. Jalankan Script Setup Awal di VPS:
Login ke VPS Anda via terminal, kemudian jalankan:
```bash
# Buat direktori deployment
sudo mkdir -p /opt/stacks/keuangan-gereja/storage/logs
sudo chown -R $USER:$USER /opt/stacks/keuangan-gereja
```

### B. Siapkan File `.env` Produksi di VPS:
Buat file di `/opt/stacks/keuangan-gereja/.env`:
```bash
nano /opt/stacks/keuangan-gereja/.env
```
Isi dengan konfigurasi produksi:
```env
APP_NAME="Keuangan Gereja"
APP_ENV=production
APP_KEY=base64:GENERATE_PRODUCTION_KEY_DENGAN_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://keuangan.domain-gereja.org
APP_TIMEZONE=Asia/Jakarta

# Supabase PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.your-project-ref
DB_PASSWORD=your-supabase-db-password
DB_SSLMODE=require

# Storage (Penyimpanan Logo Lokal di VPS)
FILESYSTEM_DISK=local

# Cloudflare R2 Storage (Opsional / Fitur Masa Depan)
AWS_ACCESS_KEY_ID=your_r2_access_key_id
AWS_SECRET_ACCESS_KEY=your_r2_secret_access_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=keuangan-gereja-bucket
AWS_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_URL=https://pub-xxxxxx.r2.dev

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### C. Salin `docker-compose.prod.yml` ke VPS:
Simpan file `docker-compose.prod.yml` ke `/opt/stacks/keuangan-gereja/docker-compose.yml`.

Pastikan nama image di dalamnya sesuai dengan repo Anda (misal: `ghcr.io/reubenjanuardi/keuangan-gereja:latest`).

---

## 5. Uji Coba Deployment CI/CD

1. Lakukan commit dan push perubahan ke branch `main`:
   ```bash
   git add .
   git commit -m "feat: setup CI/CD GitHub Actions with Docker GHCR and SSH deploy"
   git push origin main
   ```
2. Buka tab **Actions** di GitHub repository Anda.
3. Pantau jalannya pipeline:
   - ✅ **test**: Melakukan syntax & automated testing.
   - ✅ **build-and-push**: Membuild Docker image multi-stage dan mem-push ke GitHub Packages (`ghcr.io`).
   - ✅ **deploy**: Melakukan SSH ke VPS, menjalankan migrasi database Supabase, me-restart container, dan mengoptimasi cache Laravel.

---

## 6. Troubleshooting

- **Gagal Pull Image dari GHCR di VPS:**
  Jika repository GitHub bersifat Private, jalankan perintah ini satu kali di terminal VPS untuk login ke GHCR:
  ```bash
  echo "YOUR_GITHUB_PERSONAL_ACCESS_TOKEN" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin
  ```
  *(Buat Personal Access Token di GitHub dengan scope `read:packages`).*

- **Cek Log Container di VPS:**
  ```bash
  cd /opt/stacks/keuangan-gereja
  docker compose logs -f app
  ```

- **Jalankan Perintah Artisan Manual di Container:**
  ```bash
  docker compose exec app php artisan tinker
  ```
