# DEV_DOCS-033: Analisis & Status Proyek — Review ADR + DEV_DOCS

- **Tanggal:** 2026-06-23
- **Topik:** Ringkasan hasil review menyeluruh seluruh ADR (001–010) dan DEV_DOCS (001–032)
- **Penulis:** Zed Agent (Claude Sonnet 4.6)
- **Supersedes:** DEV_DOCS-012 (sebagai status update terkini)

---

## ⚡ EXECUTIVE SUMMARY

Proyek migrasi **SISFOKOL v7.00** (PHP native + MySQL MyISAM, ~75 tabel) ke **Laravel 11 Domain-Modular Monolith** sedang berjalan. Design phase 100% selesai (10 ADR final). Implementasi sudah menyelesaikan **Sprint 1–3** (Epic 1–5 & 8) dengan **82 tests passing (192 assertions)**. **Sprint 4** (Epic 6: Evaluation + Epic 9: Kurikulum Plugin) adalah langkah berikutnya.

```
Project root : D:\laragon\www\sisfokolv7\
Laravel app  : D:\laragon\www\sisfokolv7\sisfokol-laravel\
DB target    : sisfokol_laravel (MySQL, InnoDB, utf8mb4_unicode_ci)
DB legacy    : sisfokol_v7 (MySQL, MyISAM — READ-ONLY, ETL source)
PHP          : php83 (PHP 8.3.x) — WAJIB, bukan `php` default
Composer     : php83 D:\composer\composer.phar <command>
```

---

## 📋 RINGKASAN 10 ADR (Architecture Decision Records)

### ADR-001 — Record Architecture Decisions
- **Keputusan:** Semua keputusan arsitektur penting wajib dicatat sebagai file `.md` di folder `ADR/` mengikuti template Michael Nygard (Status / Konteks / Keputusan / Konsekuensi).
- **Status:** Accepted

### ADR-002 — Rebuild Total ke Laravel 11 Modular Monolith
- **Keputusan:** Tidak menambal kode lama. Bangun ulang sepenuhnya di Laravel 11. SISFOKOL v7 hanya menjadi referensi domain & business logic.
- **Alasan:** Kode lama kritis: MD5 tanpa salt, SQL injection, tanpa CSRF, MyISAM, PK varchar MD5, denormalisasi tinggi.
- **Status:** Accepted

### ADR-003 — Multi-Tenant SaaS: Shared Database + tenant_id
- **Keputusan:** Satu database bersama dengan kolom `tenant_id` di semua tabel domain. Difilter otomatis via trait `BelongsToTenant` (Eloquent global scope). Identifikasi tenant dari user yang login (bukan subdomain).
- **Hierarki:** `SuperAdmin (tenant_id=NULL)` → `Admin Sekolah` → `User Fungsional`
- **Status:** Accepted

### ADR-004 — Scope MVP Fase 1
- **Keputusan:** 6 core modules (Tenancy, Auth, Academic, Evaluation, Finance, Presence) + plugin infrastructure + 1 plugin referensi penuh (Kurikulum) + 8 plugin scaffold.
- **Status:** Accepted

### ADR-005 — Impersonation "Login As"
- **Keputusan:** Hierarkis (hanya ke bawah), env-gated (`IMPERSONATION_ENABLED`), banner merah persistent, audit log immutable, aksi sensitif diblokir via `BlockWhileImpersonating`.
- **Status:** Accepted

### ADR-006 — Granular Database-Driven RBAC
- **Keputusan:** Spatie laravel-permission (teams mode, `team_id` = `tenant_id`). Seluruh mapping role↔permission dan user↔role di database, diatur via UI admin. Konvensi permission: `resource.aksi` (`siswa.create`, `tagihan.view`, dll.).
- **Status:** Accepted

### ADR-007 — Prinsip Skema Database
- **Keputusan:** InnoDB, utf8mb4, BIGINT PK, FK + ON DELETE/UPDATE, soft delete, audit kolom (`created_by`, `updated_by`), `tenant_id` di semua tabel domain, tipe data sesuai domain (decimal untuk uang, tinyint untuk nilai/status).
- **Status:** Accepted

### ADR-008 — DEV_DOCS sebagai Memory & Handoff Antar Agent
- **Keputusan:** Dua kanal: `ADR/` (keputusan final binding) + `DEV_DOCS/` (diskusi, progress, handoff). Agent berikutnya wajib baca keduanya sebelum bertindak.
- **Status:** Accepted

### ADR-009 — Plugin Contract Plug-and-Play
- **Keputusan:** Setiap plugin implement `PluginContract` (9 method). Auto-discovery via `ModuleServiceProvider`. Aktivasi per-tenant via `tenant_plugins`. Data tidak dihapus saat nonaktif.
- **Status:** Accepted

### ADR-010 — RBAC Menjangkau Menu dan Field-Level ACL
- **Keputusan:** RBAC 5 lapis: Resource.Action → Menu Visibility (`menus`, `menu_role_overrides`) → Field/Atribut (`fields`, `field_role_overrides`) → UI Element → Route. Semuanya database-driven, diatur via "RBAC Builder" UI 4-tab.
- **Status:** Accepted

---

## 🚀 STATUS IMPLEMENTASI PER SPRINT

### ✅ Sprint 1 — Foundation & Auth (SELESAI)

**Epic 1: Setup & Fondasi**
- Laravel scaffold (`sisfokol-laravel/`)
- Trait `BelongsToTenant` + `TracksAuditColumns`
- `TenantContext` singleton
- Middleware `ResolveTenant`
- Spatie teams mode + override `User::assignRole()` dengan `runInTeamContext`
- Seeder: `RolePermissionSeeder`, `SchoolProfileSeeder`, `AcademicYearSeeder`, dll.
- **19 tests passed**

**Epic 2: Auth Module**
- `AuthController` — login/logout, throttle (5/menit), bcrypt, `last_login_at`, audit
- `AuditLogger` service (singleton) + `UserObserver`
- Middleware `ForcePasswordReset` (post-ETL)
- `ImpersonationService` + `ImpersonationController` + `BlockWhileImpersonating`
- Dashboard role-aware + `AuditLogController` (filter, pagination)
- Halaman login premium: Bootstrap 5, glassmorphism, Google Fonts
- **40 tests passed (75 assertions)**

---

### ✅ Sprint 2 — RBAC Builder & Plugin Infra (SELESAI)

**Epic 3: RBAC Builder + Field ACL + Menu Renderer**
- Models: `Menu`, `MenuRoleOverride`, `Field`, `FieldRoleOverride`
- `FieldAcl` class — resolusi visibilitas dengan cache per user/tenant
- Blade directives: `@field('resource.field')...@endfield` + `@fieldAttr('resource.field')`
- `MenuRenderer` — sidebar dinamis berdasarkan permission + override, dengan cache
- `RbacBuilderService` — orchestrator + blockIfImpersonating + audit log
- RBAC Builder UI 4 tab: Role↔Permission Matrix, Menu Overrides, Field Overrides, User→Role
- Seeder: `MenuSeeder` (17 menu + ikon FA), `FieldSeeder` (10 field sensitif)
- **51 tests passed (93 assertions)**

**Epic 4: Plugin System Infrastructure**
- `PluginContract` interface (9 method), `PluginContext` carrier
- `Plugin` + `TenantPlugin` models (dengan `BelongsToTenant`)
- `PluginRegistry` — auto-discovery `app/Plugins/*/`, sync ke DB, cache per tenant
- `PluginRegistryServiceProvider` (singleton)
- Middleware `EnsurePluginEnabled` (alias `plugin:kode`)
- `PluginActivationService` — transaksional, Spatie permission seeding, cache flush, audit log, event
- UI dashboard plugin: glassmorphism card, modal konfirmasi
- **62 tests passed (110 assertions)**

---

### ✅ Sprint 3 — Academic & Presence (SELESAI)

**Epic 5: Academic Module**
- 11 tabel akademik: `mapel_jenis`, `tahun_ajaran`, `semester`, `orang_tua`, `siswa`, `siswa_orang_tua`, `guru`, `kelas`, `kelas_siswa`, `mapel`, `jadwal`
- 11 Eloquent model dengan `BelongsToTenant` + `TracksAuditColumns`
- `JadwalConflictChecker` — validasi tabrakan jadwal guru/ruangan
- `KelasSiswaPromotionService` — kenaikan kelas transaksional, idempotent
- `SiswaController` CRUD lengkap + `SiswaPolicy` + `SiswaObserver`
- Validasi NIS unik per tenant, field sensitif dilindungi `@field`
- 4 Blade views premium dark theme (index, create, edit, show)

**Epic 8: Presence Module**
- 5 migrasi ALTER: polymorphic `userable` ke `users`, tambah `tenant_id`/audit ke `attendances`, `absences`, `permits`, kolom `note`
- Models: `Attendance` (polymorphic), `Absence`, `Permit`
- `PresensiRuleEngine` — status: `present`, `late`, `early` berdasar `AttendanceTime`
- `QrScannerService` — scan QR, validasi per-tenant, anti-duplikasi dalam `DB::transaction`
- `IzinApprovalService` — state machine: `submit` → `approve`/`reject`
- `PresensiController`, `AbsensiController`, `IzinController`, `LaporanPresensiController`
- Views: QR scanner real-time (html5-qrcode), rekap, laporan dengan grafik batang, izin approve/reject
- **82 tests passed (192 assertions)**

---

### ⏳ Sprint 4 — Evaluation & Kurikulum Plugin (PENDING)

**Epic 6: Evaluation Module** ← NEXT
- Rencana: DEV_DOCS-031 / superpowers plan `epic-6-evaluation.md`
- 11 tabel sudah ada di DB (dari migrasi legacy), perlu ALTER: tambah `tenant_id` + audit
- `GradeCalculatorService`: `NA = (rata_formatif × 0.40) + (rata_sumatif × 0.60)` (bobot bisa dikonfigurasi)
- Predikat: A (90–100), B (80–89), C (70–79), D (<70)
- `RaporGeneratorService` — PDF via `barryvdh/laravel-dompdf` (sudah di composer.json ✅)
- `GradeEntryController` + view Alpine.js realtime AJAX auto-save
- Target: 90+ tests green

**Epic 9: Plugin Kurikulum** (plugin referensi penuh)
- Framework K13/Kurmer/Muatan Lokal/Deep Learning
- Event `Evaluation.ResolveFramework` → Kurikulum plugin inject metadata (KI/KD atau CP)
- Tabel: `kurikulum`, `struktur_kurikulum`, `komponen_kompetensi`
- `mapel.kurikulum_id` FK → `kurikulum.id`

---

### ⏳ Sprint 5 — Finance & Plugin Scaffold (PENDING)

**Epic 7: Finance Module** ← KRITIS
- `PembayaranService` wajib: `DB::transaction()` + `lockForUpdate()` — race condition = keuangan rusak
- 5 tabel: `item_pembayaran`, `tagihan_siswa`, `pembayaran`, `pembayaran_rincian`, `tabungan_siswa`
- `GenerateTagihanCommand` sudah ada di `app/Console/Commands/`

**Epic 10: 8 Plugin Scaffold**
- Discipline, Inventory, Tahfidz, HafalanHadist, BimbinganKonseling, PendidikanKarakter, PelaporanOrtu, PWA
- Scaffold: folder + manifest + ServiceProvider + migration placeholder + permissions.php
- Tanpa UI (Fase 2+)

---

### ⏳ Sprint 6 — ETL & Deployment (PENDING)

**Epic 11: ETL Pipeline**
- `MigrateLegacyDataCommand` sudah ada di `app/Console/Commands/`
- Source: DB `sisfokol_v7` (MyISAM, read-only)
- Target: DB `sisfokol_laravel`
- Mapping: `tp01`→`guru`, `tp02`→`siswa`, dll. (20 tahap)
- Semua user hasil ETL: `must_reset_password = true`

**Epic 12: Testing & Deployment**
- `php83 artisan test` — full suite
- Setup Laragon virtual host / production config

---

## 🏗️ STRUKTUR PROYEK SAAT INI

### `app/` — Ringkasan

```
app/
├── Modules/              ← Domain-Modular Monolith (5 domain)
│   ├── Academic/         ✅ Models, Controllers, Services, Migrations, routes.php
│   ├── Auth/             ✅ Login, RBAC, Impersonate, AuditLog, Plugin Mgmt
│   ├── Evaluation/       ⏳ Models ada, perlu Service+Controller+Views
│   ├── Presence/         ✅ QR scan, Absensi, Izin, Laporan
│   └── Tenancy/          ✅ Tenant, Branch, Subscription, TenantSetting
│
├── Plugins/
│   └── Infrastructure/   ✅ Plugin, TenantPlugin models
│
├── Support/              ✅ FieldAcl, MenuRenderer, PluginRegistry, TenantContext
├── Http/Middleware/      ✅ ResolveTenant, ForcePasswordReset,
│                            BlockWhileImpersonating, EnsurePluginEnabled
├── Models/               63+ Eloquent models + Traits/
└── Providers/            AppServiceProvider, ModuleServiceProvider,
                          PluginRegistryServiceProvider
```

### `database/` — Ringkasan

- **70+ migrasi** (termasuk modul migrations di `app/Modules/*/Database/Migrations/`)
- **15 Seeder** termasuk `DemoSeeder`
- **13 Factory** (campuran English/Indonesia naming)

### `routes/`

| File | Konten |
|------|--------|
| `web.php` | Role-based routing: admin, teacher, student, homeroom, finance, counselor, picket, inventory, principal |
| `api.php` | Sanctum API: login, user, logout, schedules/today |
| `console.php` | Scheduled: backup:run (02:00 daily), queue:work (per menit) |
| Module routes | Di-load `ModuleServiceProvider` dari `app/Modules/*/routes.php` |

---

## 📦 STACK TEKNOLOGI

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11, PHP 8.3 |
| Database | MySQL 8 / MariaDB, InnoDB, utf8mb4_unicode_ci |
| RBAC | `spatie/laravel-permission` ^6.4 (teams mode) |
| Impersonation | `lab404/laravel-impersonate` ^1.7 |
| PDF | `barryvdh/laravel-dompdf` ^3.1 |
| Excel | `maatwebsite/excel` ^3.1 |
| QR Code | `simplesoftwareio/simple-qrcode` ^4.0 |
| Frontend | Bootstrap 5 + Alpine.js + Vite |
| API Auth | Laravel Sanctum (Fase 2) |

---

## 👤 AKUN DEMO

| Role | Username | Password |
|------|----------|----------|
| SuperAdmin | `superadmin` | `SuperAdmin#2026` |
| Admin Sekolah | `admin.sekolah` | `demo1234` |
| Guru Piket | `piket.demo` | `demo1234` |
| Guru BK | `bk.demo` | `demo1234` |
| Guru Mapel | `guru.demo` | `demo1234` |
| Wali Kelas | `walikelas.demo` | `demo1234` |
| Siswa | `siswa.2024001` | `demo1234` |

Reset DB: `php83 artisan migrate:fresh --seed`

---

## ⚠️ ATURAN WAJIB (jangan dilanggar)

1. **`php83`** untuk SEMUA artisan + composer command — bukan `php`
2. **`BelongsToTenant` wajib** di semua Eloquent model domain — tanpa ini ada risiko data leak antar tenant
3. **`DB::transaction()` + `lockForUpdate()` wajib** di `PembayaranService` — race condition = keuangan rusak
4. **`must_reset_password = true`** untuk semua user hasil ETL
5. Dokumen `DOCS/ARENA_...` sebagian besar **overstated** — klaim adanya codebase di `/home/user/sisfokol-laravel-mvp/` adalah sandbox AI yang tidak pernah disimpan ke disk. **Jangan dijadikan referensi implementasi.**
6. Setiap keputusan penting → tulis ADR baru; setiap sesi panjang → tulis DEV_DOCS baru

---

## 🎯 NEXT ACTION: Sprint 4 — Epic 6 (Evaluation Module)

1. `composer require barryvdh/laravel-dompdf` (jika belum — cek composer.json, sudah ada ✅)
2. Buat 4 migrasi ALTER untuk 8 tabel evaluasi: tambah `tenant_id` + audit columns
3. Implementasi `GradeCalculatorService` dengan rumus & predikat
4. Implementasi `RaporGeneratorService` (PDF DomPDF)
5. `GradeEntryController` + view (Alpine.js realtime + AJAX auto-save)
6. `RaporController` + view PDF
7. Test: target 90+ tests green
8. Lanjut Epic 9: Plugin Kurikulum (framework K13/Kurmer)

---

*Dokumen ini dibuat oleh Zed Agent pada 2026-06-23 berdasarkan review menyeluruh ADR/001–010 dan DEV_DOCS/001–032.*
