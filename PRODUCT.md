# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary users are the internal people of GPIB Jemaat Hosiana Jakarta who steward the church's money and administration: the bendahara (treasurer) recording and reviewing cash movement, the operator kasir entering vouchers day to day, church administrators managing the institution's settings and user accounts, and majelis peninjau (board reviewers) who read and audit the numbers. They are volunteers and staff, not professional accountants, and they are often doing this work around full-time church duties.

The welcome page at `/` is the doorway for this audience. It must get them into the portal with dignity and confidence, and it must convey that this church takes stewardship of funds seriously.

## Product Purpose

SIKG (Sistem Informasi Keuangan Gereja) replaces the church's manual spreadsheet-based cash recording with a structured relational system, so that every rupiah of income and expenditure is captured, accountable, and reportable.

The existing system already delivers the core: chart of accounts with nested budget structure, voucher and transaction entry with atomic persistence, and financial reporting (buku besar, jurnal umum, jurnal, realisasi mingguan) exportable to PDF and Excel.

The product's direction is a single integrated platform covering administration and congregation data, finance and assets, worship scheduling, correspondence, ministry coordination, communications, and pastoral care.

## Positioning

Built by and for this congregation, around this church's actual Chart of Accounts and its real reporting obligations. It is a stewardship instrument first and a piece of software second — a system the church's own volunteers can audit, correct, and hand to the next treasurer without a handover from an outside agency.

## Operating Context

- Indonesian church administration: GPIB (Gereja Protestan di Indonesia bagian Barat), a denomination with a Majelis (board), departmental ministry (pelkat: PA, PT, GP, PKP, PKB, PKLU), and formal roles for church officers.
- Accounting and reporting are done in Indonesian rupiah; dates and reporting are in WIB (Asia/Jakarta).
- The treasurer closes periods and produces reports for the majelis and for physical, wet-signature archival. PDF output is used as an official document, not a convenience export.
- Documented in Indonesian. All product copy is Indonesian.
- The church is at Jl. Rajawali Selatan V No. 7, Jakarta Pusat 10772.

## Capabilities and Constraints

- Laravel 13 / PHP 8.4, Filament v5.7 admin panel at `/keuangan` and `/settings`, Inertia + Vue 3 for the portal, Tailwind CSS.
- PostgreSQL on Supabase, schema `portal`. Object storage on Cloudflare R2. Deployed via Docker behind Cloudflare Tunnel.
- RBAC via spatie/laravel-permission. Roles: Super Admin, Bendahara Keuangan, Operator Kasir, Majelis Peninjau. `Gate::before` grants Super Admin everything.
- An activity log records actions for audit.
- `kode_akun` is a string primary key and is read-only after creation; accounts used by transactions cannot be deleted (`RESTRICT`).
- Voucher and transaction writes are wrapped in a single database transaction.
- All financial reporting is behind authentication. There is no public financial report.
- Of the twelve planned modules, **Keuangan** and **Pengaturan Portal** are live today; the remainder are under development. The platform's framing is deliberately the full integrated vision rather than a feature list of what ships today.
- Stated as non-negotiable by the project: destructive database commands must never be run, and `DB::prohibitDestructiveCommands` must stay enabled. This constrains any future work that touches data.

## Brand Commitments

- Name: GPIB Jemaat Hosiana Jakarta. Portal is presented as "Portal Terintegrasi".
- The GPIB logo is a fixed, official asset: a blue circular seal depicting a cross, an open Bible (Lukas 13:29), a chalice, bread, a congregation, mountains, and a city skyline, with circular lettering. It is the institution's mark, not decoration, and appears in the Filament brand, favicons, and the portal.
- Church identity is configurable by an admin (name, address, logo) via Pengaturan Gereja.

## Evidence on Hand

- `public/favicon.png` and the favicon set: the official GPIB seal, blue line art on white.
- `docs/PRD.md`: product requirements, functional requirements for CoA, voucher entry, and reporting.
- `docs/audit_report.md`: codebase audit dated 2026-07-30.
- `AGENT.md`: architecture, security directives, and financial domain rules.
- `resources/js/Pages/Dashboard.vue`: the authenticated app launcher, which states the module roster and each module's live/coming-soon status, its own descriptions, and the four RBAC roles.
- Real, live financial report views: voucher PDF, buku besar, jurnal, jurnal umum, realisasi mingguan, in both PDF and Excel.

**No** testimonials, user counts, benchmark figures, accreditation, or performance claims exist for this product. Future work must not invent them.

## Product Principles

1. **Accountability is the product.** The system's value to this church is that a number can be traced and defended. Every surface should make provenance and audit feel native, not bolted on.
2. **Honesty over polish.** Because this is a stewardship tool run by volunteers, claims must stay provable. Do not promise the public transparency or capabilities the system does not deliver.
3. **Volunteer-grade clarity.** The audience is not professional accountants. Terminology follows Indonesian church and accounting convention, and no interface should require accounting expertise to operate.
4. **Modular growth, stable core.** The twelve-module platform grows outward from a financial core that must never regress. New surfaces should read as one institution, not twelve products.
5. **The institution outranks the interface.** The GPIB seal and the church's identity come first; the software is a servant of the congregation, not a competing brand.

## Accessibility & Inclusion

No product-specific accessibility requirement has been established yet. Indonesian-language copy must remain legible at small sizes; the church's operators work on varied and sometimes older devices, so clarity and performance are treated as practical concerns.
