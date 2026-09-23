# AGENT.MD — Panduan Utama AI Agent & Pengembang
**Sistem Informasi Keuangan Gereja (SIKG)**  
*Dokumen ini adalah acuan resmi arsitektur, aturan keamanan sakral, dan standar teknis pengembangan untuk AI Agent (Codex, Cursor, Claude Code, Antigravity) serta pengembang manusia.*

---

## 🛑 1. CRITICAL SAFETY DIRECTIVES (ATURAN SAKRAL ANTI-DISASTER)

> [!CAUTION]
> **PELANGGARAN TERHADAP ATURAN INI DAPAT MENGHAPUS DATA KEUANGAN JEMAAT YANG TIDAK DAPAT DIPULIHKAN!**

1. **DILARANG KERAS MENJALANKAN PERINTAH DESTRUKTIF DATABASE**:
   - **JANGAN PERNAH** menjalankan:
     - `php artisan migrate:fresh`
     - `php artisan migrate:reset`
     - `php artisan migrate:refresh`
     - `php artisan db:wipe`
     - Query `DROP TABLE`, `TRUNCATE`, atau `DELETE` massal tanpa `WHERE` clause.
   - Perintah di atas **hanya boleh** dieksekusi jika ada perintah eksplisit tertulis dari pemilik proyek dengan konfirmasi dua langkah.

2. **PERIKSA TARGET DATABASE DI `.env` SEBELUM MENJALANKAN PERINTAH DB**:
   - Periksa `DB_HOST` dan `DB_DATABASE` di `.env`.
   - Jika `DB_HOST` mengarah ke domain pooler (misal: `*.supabase.com`), **ANDA SEDANG TERHUBUNG KE DATABASE LIVE SUPABASE!** Perlakukan seperti database production langsung.

3. **STANDAR MIGRASI PRODUCTION**:
   - Gunakan **hanya**: `php artisan migrate --force`
   - Selalu periksa status migrasi terlebih dahulu: `php artisan migrate:status`
   - Buat migrasi yang bersifat **aditif** (menambah kolom nullable, membuat index secara aman, tidak langsung menghapus atau me-rename kolom yang sudah berisi data).

4. **PENGAMAN KODE (`DB::prohibitDestructiveCommands`)**:
   - Method `DB::prohibitDestructiveCommands($this->app->isProduction())` di `App\Providers\AppServiceProvider` **wajib selalu aktif**. Dilarang mengubah, menonaktifkan, atau mem-bypass pengaman ini.

5. **VERIFIKASI BACKUP SEBELUM PERUBAHAN SKEMA BESAR**:
   - Sebelum menjalankan migrasi yang mengubah struktur tabel krusial (`vouchers`, `transactions`, `chart_of_accounts`), pastikan backup database terbaru telah tersimpan di Cloudflare R2 atau storage lokal.

---

## 🏗️ 2. SPESIFIKASI ARSITEKTUR & TECH STACK

- **Backend Framework**: Laravel 13 (PHP 8.4)
- **Admin Panel**: Filament v5.7 (Panel `/keuangan`, `/settings`)
- **Frontend / Portal Jemaat**: Inertia.js + Vue 3 (Vite, Tailwind CSS, Ziggy)
- **Database**: PostgreSQL di Supabase (AWS Singapore Pooler, Schema: `portal`)
  - *Catatan Penting*: Skema database yang digunakan adalah `portal` (`search_path=portal`), bukan `public`. Jangan gunakan hardcoded query yang mengasumsikan skema `public`.
- **Object Storage**: Cloudflare R2 (S3-compatible)
  - Disk: `r2` / `s3`
  - Bucket: `keuangan-gereja-storage`
  - Endpoint: `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`
- **Autentikasi & Otorisasi**: `spatie/laravel-permission` (RBAC) + Activity Log kustom
  - Role bawaan: `Super Admin`, `Bendahara Keuangan`, `Operator Kasir`, `Majelis Peninjau`
  - `Gate::before` aktif: Pengguna dengan role `Super Admin` otomatis memiliki seluruh izin.
- **Infrastruktur & Deployment**:
  - Containerization: Multi-stage Dockerfile (PHP 8.4-FPM Alpine + Nginx + Supervisord + postgresql-client).
  - Reverse Proxy & SSL: Cloudflare Tunnel (`cloudflared` container sidecar).
  - CI/CD: GitHub Actions deploy via SSH ke VPS.

---

## 💼 3. ATURAN BISNIS & KONVENSI KEUANGAN (DOMAIN RULES)

### A. Chart of Accounts (CoA)
- **Primary Key**: `kode_akun` bertindak sebagai Primary Key bertipe `String` (misal: `111.01`).
- **Immutability**: `kode_akun` bersifat *read-only* setelah disimpan. Tidak boleh diubah sembarangan karena menjadi relasi foreign key pada transaksi historis.
- **Hierarki & Akun Induk**:
  - Akun memiliki struktur bersarang (`parent_code`).
  - Flag `is_postable = true` **hanya** untuk akun anak (level terbawah) yang boleh dipilih dalam transaksi kas.
- **Integritas Hapus**: Tabel `transactions` memiliki foreign key dengan aturan `RESTRICT ON DELETE` terhadap `chart_of_accounts.kode_akun`. Akun yang sudah pernah dipakai transaksi dilarang dihapus.

### B. Transaksi & Voucher Kas
- **Hubungan Master-Detail**:
  - Header: `vouchers` (Nomor Voucher, Tanggal, Jenis Voucher: Masuk/Keluar, Kode Akun Kas/Bank, dsb).
  - Detail: `transactions` (Rincian akun lawan, uraian, debit/kredit, nominal).
- **Integritas Transaksi**:
  - Semua operasi simpan, ubah, atau hapus yang melibatkan `vouchers` dan `transactions` **wajib** dibungkus dalam `DB::transaction(function () { ... })`.
- **Kalkulasi Nominal**:
  - Total voucher harus selalu sinkron dengan kalkulasi baris transaksi. Hindari selisih pembulatan desimal.

### C. Waktu & Lokalisasi
- **Timezone**: Standar sistem adalah `Asia/Jakarta` (WIB). Semua timestamp pencatatan transaksi dan audit log harus menggunakan waktu lokal WIB.
- **Format Uang**: Rupiah (IDR), dipresentasikan tanpa simbol membingungkan pada laporan cetak.

---

## 💾 4. PROTOKOL BACKUP & DISASTER RECOVERY

1. **Pre-Deployment Backup**:
   - Script deployment (`deploy/deploy.sh` dan GitHub Actions) wajib menjalankan dump PostgreSQL sebelum perintah `migrate --force`.
   - File dump tersimpan terkompresi di `./backups/db_backup_<timestamp>.sql.gz`.

2. **Scheduled Automated Backup**:
   - Dijalankan via `spatie/laravel-backup` setiap hari pada pukul 02:00 WIB.
   - Hasil backup otomatis diunggah ke **Cloudflare R2** (`keuangan-gereja-storage`) dan salinan lokal di VPS.
   - Retensi otomatis: Backup lama (> 14 hari) akan dibersihkan secara otomatis.

3. **Perintah Manual Backup**:
   ```bash
   # Backup database saja
   php artisan backup:run --only-db

   # Backup database + file uploads
   php artisan backup:run

   # Bersihkan backup yang kadaluarsa
   php artisan backup:clean
   ```

4. **Prosedur Pemulihan Cepat (Emergency Restore)**:
   - Ambil file backup terbaru dari Cloudflare R2 atau folder `./backups`.
   - Restore ke Supabase menggunakan:
     ```bash
     gunzip -c backup_file.sql.gz | psql "postgresql://<USER>:<PASS>@<HOST>:<PORT>/<DB>?sslmode=require"
     ```

---

## 🛠️ 5. PEDOMAN PENGEMBANGAN FITUR BARU

- **Filament Resources**:
  - Tempatkan resource keuangan di `app/Filament/Keuangan/Resources/`.
  - Tempatkan resource sistem/master di `app/Filament/Settings/Resources/`.
- **Inertia / Vue 3 Pages**:
  - Halaman antarmuka publik/jemaat disimpan di `resources/js/Pages/`.
  - Gunakan TypeScript/JS bersih dan Tailwind CSS untuk styling.
- **Pengujian (Automated Tests)**:
  - Setiap perubahan pada RBAC, logic transaksi kas, atau migrasi **wajib** lulus uji:
    ```bash
    php artisan test
    ```
