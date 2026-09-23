# 🚀 Implementation Plan — SIKG (Sistem Informasi Keuangan Gereja)

> Dibuat berdasarkan: [Audit Report](./audit_report.md)
> Terakhir diperbarui: 2026-09-23 (berdasarkan git history `main`)

---

## Status Proyek: 🟢 LIVE IN PRODUCTION

```
Phase 1 — Gap Closure            ██████████  (100% — 30 Jul 2026)
Auth Fix — Redirect Bug           ██████████  (100% — 31 Jul 2026)
Phase 2 — Pelaporan PDF           ██████████  (100% — 31 Jul 2026)
Phase 3 — Buku Besar & Jurnal     ██████████  (100% — 31 Jul 2026)
Infra — Docker, CI/CD & Deploy    ██████████  (100% — Agu 2026)
Phase 4 — RBAC + Audit Log        ██████████  (100% — 24 Agu 2026)
Portal — Landing, Dashboard, etc  ██████████  (100% — Agu–Sep 2026)
Auth Hardening — Phase A–D        ██████████  (100% — 19 Sep 2026)
Phase 5 — Multi-Tenant            ░░░░░░░░░░  (Fase Lanjutan / Opsional — belum dikerjakan)
```

**MVP + ekstensi inti dianggap selesai.** Sistem berjalan di produksi dengan pipeline deploy otomatis. Sisa pekerjaan bersifat opsional (multi-tenancy) dan maintenance rutin.

---

## 🏗️ Arsitektur Produksi (Aktif)

| Lapisan | Teknologi |
|---|---|
| Runtime | Docker (multi-stage image PHP 8.4 + Node 22) |
| Registry | GitHub Container Registry (`ghcr.io/.../portal-aplikasi-gereja`) |
| CI/CD | GitHub Actions → test → build & push → SSH deploy (`.github/workflows/deploy.yml`) |
| Database | Supabase PostgreSQL (managed, SSL required) |
| Object Storage | Cloudflare R2 (S3-compatible) / local public disk untuk logo |
| Edge & SSL | Cloudflare proxy + Cloudflare Tunnel (`cloudflared` sidecar) |
| Server | VPS Ubuntu/Debian + Docker Compose (`docker-compose.prod.yml`) di `/opt/stacks/keuangan-gereja` |

Panduan lengkap: [`deploy/README.md`](../deploy/README.md)

---

## ✅ Riwayat Penyelesaian (Git History)

### Fase Development (Jul 2026)

#### Phase 1 — Penutupan Gap Kritis MVP (SELESAI — 30 Jul 2026)
- **Restrict on Delete (DB)**: FK `kode_akun` → `chart_of_accounts` dengan `restrictOnDelete()` eksplisit.
- **Restrict on Delete (App)**: Guard `before()` pada `DeleteAction` CoA (cek riwayat transaksi & akun anak).
- **DB Transaction**: `DB::transaction()` membungkus `handleRecordCreation` / `handleRecordUpdate` pada Create & Edit Voucher.

#### Bug Fix — Redirect Inertia ke Filament (SELESAI — 31 Jul 2026)
- `Inertia::location()` pada login, registrasi, dan konfirmasi password agar redirect penuh ke `/admin`.

#### Phase 2 — Cetak Bukti Voucher PDF (SELESAI — 31 Jul 2026)
- `barryvdh/laravel-dompdf`, Blade `resources/views/pdf/voucher.blade.php`, route `/vouchers/{voucher}/pdf`, tombol aksi di tabel & halaman Edit.

#### Phase 3 — Laporan Buku Besar & Jurnal (SELESAI — 31 Jul 2026)
- Halaman Filament `LaporanBukuBesar` & `LaporanJurnal` + ekspor PDF landscape (`LaporanPdfController`).

---

### Fase Infrastruktur & Rilis (Agu 2026)

| Tanggal | Komit | Ringkasan |
|---|---|---|
| 05 Agu | `bf1a25f` | `init` repository |
| 18 Agu | `ca33921`, `755d73e`, … | Sistem pelaporan + seeding CoA, CI/CD pipeline, Dockerization, PHP 8.4, perbaikan migration CoA |
| 18 Agu | `1d68674`, `2de09dd` | Konfigurasi Nginx (cache/statik), adapter S3 Cloudflare R2 |
| 19 Agu | `ee70641`, `e9bf61b` | Optimasi query CoA (eager loading rekursif + index `parent_code`), kolom anggaran |
| 20 Agu | `7cb2b04`, `4ee9125`, `bd48b96` | Arsitektur aman via Cloudflare Tunnel, paksa HTTPS, trust reverse proxy |
| 20 Agu | `c9bf011`…`9ad41ff` | Perbaikan bug voucher 500/Livewire, dukungan jenis voucher BKM/BKK/BBM/BBK |
| 21 Agu | `fc1144c` | Landing page, grid launcher dashboard, routing panel keuangan |

---

### Fase Ekstensi Fitur (Agu–Sep 2026)

#### Phase 4 — RBAC, Audit Log & Settings Portal (SELESAI — 24 Agu 2026)
Commit: `20b44b6`

- **Package**: `spatie/laravel-permission` v8.3 + trait `HasRoles` di `User`.
- **Roles** (berbeda dari nama draft awal — lihat `database/seeders/RbacSeeder.php`):

  | Role | Akses |
  |---|---|
  | `Super Admin` | Full access semua modul, RBAC, audit log, pengaturan master |
  | `Bendahara Keuangan` | CRUD penuh modul keuangan (voucher, CoA, laporan, cetak/ekspor) |
  | `Operator Kasir` | View + create & print voucher saja; tanpa edit/hapus; tanpa Pengaturan Portal |
  | `Majelis Peninjau` | Read-only transaksi + cetak/ekspor laporan |

- **Policies**: `VoucherPolicy`, `ChartOfAccountPolicy`, `UserPolicy`, `RolePolicy`, `ActivityLogPolicy`.
- **Panel Settings** (`/settings`, `SettingsPanelProvider`): `UserResource`, `RoleResource`, `ActivityLogResource`, pengaturan gereja.
- **Audit logging**: trait `LogsActivity` + model `ActivityLog` (kolom `subject_id` string — commit `75c3e2e`).
- **Tests**: `RbacAndActivityLogTest.php`.

#### Ekspor Excel Semua Laporan (SELESAI — 24 Agu 2026)
Commit: `5f026b2` — opsi ekspor `.xlsx` untuk seluruh laporan keuangan.

#### Perbaikan Auth & Navigasi (SELESAI — 1 Sep 2026)
- Redirect logo/navbar ke `/dashboard`, logout modul ke landing page, `Inertia::location` pada login.
- Restorasi konfigurasi Pest untuk Feature tests di CI (`d32a642`).

#### Peningkatan Voucher (SELESAI — 15–17 Sep 2026)
- Input CoA ganda: **Kas/Bank** + **Mata Anggaran** (`f656c4d`, `0adee7d`).
- Penomoran otomatis nomor bukti voucher (`4010998`).
- Menu **Laporan Jurnal Umum** (`4010998`).
- Rename image Docker → `portal-aplikasi-gereja` (`db13712`).

#### Laporan Realisasi Mingguan (SELESAI — 18 Sep 2026)
- Kolom realisasi diganti **saldo awal & saldo akhir** di Excel (`f0eaede`); perbaikan output buffer.
- Test: `LaporanRealisasiExcelTest.php`.

#### Auth Hardening — Phase A–D (SELESAI — 19 Sep 2026)
Commit: `eb8f637`

- **A**: Hapus registrasi publik & alur reset password via email.
- **B**: Overhaul UI auth/profile dengan design system token GPIB Hosiana.
- **C**: Halaman error server-rendered kustom (403, 404, 419, 429, 500, 503).
- **D**: Reset password manual oleh admin via `UserResource` (modal interaktif, token aman, copy-to-clipboard, activity logging tersanitasi).
- Test suite Pest komprehensif — **90 passing tests**.

---

## 📦 Status Definition of Done

### MVP (Selesai)
- [x] CoA Management (hierarki, postable, read-only kode)
- [x] Pencatatan Voucher dengan Repeater
- [x] Validasi no bukti & kalkulasi real-time
- [x] Restrict on Delete CoA (Phase 1)
- [x] DB Transaction pada save Voucher (Phase 1)
- [x] Fix Redirect Inertia ke Filament Admin Panel
- [x] Cetak bukti Voucher ke PDF (Phase 2)
- [x] Laporan Buku Besar (Phase 3)
- [x] Laporan Jurnal (Phase 3)

### Rilis Produksi (Selesai)
- [x] Docker image multi-stage + Nginx-ready
- [x] CI/CD GitHub Actions → GHCR → deploy SSH ke VPS
- [x] Database produksi Supabase PostgreSQL
- [x] Storage Cloudflare R2 + tunnel Cloudflare (HTTPS)
- [x] Landing page & dashboard launcher
- [x] RBAC + Audit Log + Settings Portal (Phase 4)
- [x] Ekspor Excel semua laporan
- [x] Laporan Jurnal Umum & Realisasi Mingguan
- [x] Voucher dual CoA (Kas/Bank + Mata Anggaran) + auto nomor bukti
- [x] Auth hardening Phase A–D + custom error pages + manual password recovery
- [x] 90 Pest feature tests passing

### Sisa / Opsional
- [ ] **Phase 5 — Multi-Tenancy** (belum dikerjakan; butuh perubahan skema `entity_id` — lihat seksi di bawah)
- [ ] Monitoring & backup rutin produksi (log rotation sudah diset di compose; backup DB/storage manual via Supabase/R2)
- [ ] Evaluasi kebutuhan RBAC halus (permission per-resource sudah ada; disesuaikan operasional gereja)

---

## 🔵 PHASE 5 — Multi-Tenancy (OPSIONAL, BELUM DIMULAI)
**Estimasi: 5–7 hari kerja | Prioritas: Rendah (PRD Seksi 5 — Roadmap)**

> [!WARNING]
> Membutuhkan **perubahan skema database yang signifikan**. Lakukan hanya setelah evaluasi kebutuhan bisnis yang jelas (berapa entitas/pos pelayanan yang memakai sistem?).

### Task 5.1 — Evaluasi & Desain Arsitektur Multi-Tenant
Pilih pendekatan:
- **Single DB + kolom `entity_id`** (sederhana, cocok skala kecil)
- **Separate schema per tenant** (mis. `stancl/tenancy`)

### Task 5.2 — Tambahkan Kolom `entity_id` (Jika Single DB)
Migration ke tabel utama: `chart_of_accounts`, `vouchers`, `transactions`.

### Task 5.3 — Global Scope untuk Filtering Otomatis
```php
protected static function booted(): void
{
    static::addGlobalScope('entity', function (Builder $builder) {
        $builder->where('entity_id', auth()->user()->entity_id);
    });
}
```

### Verifikasi Phase 5
- [ ] User Entity A tidak melihat data Entity B
- [ ] CoA per entitas terpisah
- [ ] Laporan hanya menampilkan data entitas yang login

---

## 📅 Timeline Aktual (Ringkas)

```
Jul 2026     │ Phase 1–3: gap closure, PDF voucher, Buku Besar & Jurnal  ██████████
Agu 2026     │ Init repo, CI/CD, Docker, Nginx, R2, tunnel, landing      ██████████
Agu 2026     │ Phase 4: RBAC + audit log + /settings                     ██████████
Sep 2026     │ Dual CoA voucher, jurnal umum, realisasi mingguan (xlsx)   ██████████
19 Sep 2026  │ Auth hardening Phase A–D + design system + error pages    ██████████
Selanjutnya  │ Phase 5 multi-tenant (opsional) / maintenance produksi    ░░░░░░░░░░
```
