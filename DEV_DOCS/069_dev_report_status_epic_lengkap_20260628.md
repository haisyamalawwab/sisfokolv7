# DEV_DOCS-069: Dev Report — Status EPIC Lengkap (Yang Belum Selesai / Belum Dijalankan)

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 Modular Monolith (`sisfokol-laravel/`)
- **Tujuan:** Inventarisasi status seluruh EPIC (1–12), identifikasi EPIC yang belum dijalankan / belum selesai, serta rekomendasi tindak lanjut.

---

## 1. Ringkasan Eksekutif

Project SISFOKOL v7 didekomposisi menjadi **12 EPIC** (rencana lengkap di `DEV_DOCS/superpowers/plans/`). Berdasarkan verifikasi dokumentasi walkthrough/audit/report **dan** struktur file fisik di `sisfokol-laravel/`:

| Kategori | Jumlah | EPIC |
|----------|:------:|------|
| ✅ Selesai & terverifikasi | 6 | EPIC 1, 2, 3, 4, 5, 6 |
| ⚠️ Selesai dengan catatan / gap | 3 | EPIC 7, 8, 9 |
| ❌ Belum dijalankan | 3 | EPIC 10, 11, 12 |

**Kesimpulan utama:** Implementasi inti (fondasi, auth, RBAC, plugin infra, akademik, evaluasi, finance, presence, kurikulum) sudah berjalan. Yang **belum dijalankan sama sekali** adalah **EPIC 10 (8 Plugin Scaffold)**, **EPIC 11 (ETL Pipeline)**, dan **EPIC 12 (Testing + Deployment)**. Selain itu, EPIC 7/8/9 memiliki gap teknis (konfigurasi test, coverage) yang perlu ditutup sebelum produksi.

---

## 2. Tabel Status Detail per EPIC

| EPIC | Nama | Status | Bukti Verifikasi | Catatan / Gap |
|:----:|------|:------:|------------------|---------------|
| 1 | Setup & Fondasi | ✅ Selesai | `013_walkthrough_epic_1` | 19 tests PASS, seeding 100% green. Fix Spatie teams + middleware alias. |
| 2 | Auth Module | ✅ Selesai | `015_walkthrough_epic_2` | 40 tests / 75 assertions PASS. Login, impersonation, audit log, force-reset. |
| 3 | RBAC Builder | ✅ Selesai | `018_walkthrough_epic_3` | Menu/Field ACL, Blade directives, 4-tab RBAC UI. |
| 4 | Plugin Infra | ✅ Selesai | `023_walkthrough_epic_4`, `023d_real_verification` | Plugin registry + aktivasi per-tenant terverifikasi real via tinker. |
| 5 | Academic | ✅ Selesai | `026_walkthrough_epic_5` | 11 tabel akademik, JadwalConflictChecker, promotion service, CRUD siswa. |
| 6 | Evaluation | ✅ Selesai | `050_verifikasi_epic_6`, `056_audit_epic_6` | Verifikasi API-driven MVC; ada audit `057` untuk fix issues. |
| 7 | Finance | ⚠️ Selesai + catatan | `060_audit_epic_7`, `061_audit_epic_7_8_9` | 26/26 file ada, kode real. **11/14 test pass** — 3 fail karena `phpunit.xml` (SQLite dikomentari → RefreshDatabase drop tabel MySQL produksi). Bukan bug kode. |
| 8 | Presence | ⚠️ Selesai + catatan | `029_walkthrough_epic_8`, `062_epic_8_presence_PHYSICAL_VERIFICATION` | Walkthrough klaim 82 tests pass, namun audit `061` menemukan **0 tests** pada saat verifikasi ground-truth (kemungkinan test ditulis ulang/dihapus). 14/14 file fisik ada. |
| 9 | Kurikulum Plugin | ⚠️ Selesai + refactoring | `040_dev_report_epic9`, `063_audit_crudlfix_epic9`, `064_epic9_crudlfix_refactoring` | Plugin referensi lengkap. 0/3 test pass di audit awal (konfigurasi phpunit). Refactoring Crudlfix dilakukan (064). Hybrid Crudlfix + Livewire (lihat commit `0efdbbc`). |
| 10 | 8 Plugin Scaffold | ❌ Belum dijalankan | — | `app/Plugins/` hanya berisi `Infrastructure` + `Kurikulum`. Tidak ada 8 plugin scaffold. |
| 11 | ETL Pipeline | ❌ Belum dijalankan | — | Tidak ada folder/command ETL (`migrate:legacy-sisfokol`, `etl:verify`). |
| 12 | Testing + Deployment | ❌ Belum dijalankan | — | Tidak ada setup PHPStan/Larastan, CI/CD, `.env.production`, security checklist. |

---

## 3. EPIC yang Belum Dijalankan (Detail)

### 3.1 EPIC 10 — 8 Plugin Scaffold

**Goal:** Scaffold 8 plugin sisanya dengan struktur skeleton penuh (PluginContract, ServiceProvider, routes, placeholder controllers/views, permission + menu seed). Plugin Kurikulum (EPIC 9) = referensi.

**Bukti belum dijalankan:** `app/Plugins/` hanya berisi 2 entri:
```
app/Plugins/
├── Infrastructure   (EPIC 4)
└── Kurikulum        (EPIC 9 — referensi)
```

**8 Plugin yang harus di-scaffold:**

| # | Kode | Nama | Tables | Fitur Utama |
|---|------|------|:------:|-------------|
| 1 | `absensi_guru` | Absensi Guru | 2 | Presensi guru via QR/manual, rekap bulanan |
| 2 | `rapor` | Rapor Builder | 1 | Template rapor custom per tenant, cetak PDF batch |
| 3 | `spp` | SPP Manager | 2 | Auto-generate tagihan SPP bulanan, reminder tunggakan |
| 4 | `ppdb` | PPDB Online | 3 | Formulir pendaftaran siswa baru online, seleksi |
| 5 | `ekstrakurikuler` | Ekstrakurikuler | 2 | Pendaftaran ekskul, absensi ekskul |
| 6 | `bk` | Bimbingan Konseling | 2 | Catatan BK per siswa, agenda konseling |
| 7 | `perpustakaan` | Perpustakaan | 3 | Koleksi buku, peminjaman, pengembalian |
| 8 | `inventaris` | Inventaris | 2 | Aset sekolah, kondisi, mutasi |

**Rencana:** `DEV_DOCS/superpowers/plans/2026-06-20-epic-10-plugin-scaffold.md`

---

### 3.2 EPIC 11 — ETL Pipeline (20 Steps + Verify)

**Goal:** Implement `migrate:legacy-sisfokol {tenant_id}` — pindahkan data dari legacy `sisfokol_v7` (MyISAM, MD5 PK, no FK) → `sisfokol_laravel` (InnoDB, BIGINT PK, full FK) dalam urutan topologis 20 langkah. Idempotent, transaksional, + command verifikasi `etl:verify {tenant_id}`.

**Bukti belum dijalankan:** Tidak ada folder ETL, tidak ada `app/Console/Commands/MigrateLegacyCommand` atau `Et/VerifyCommand`, tidak ada tabel helper `legacy_id_mappings`. Helper cleansing (`clean_money`, `clean_date`, `clean_phone`) **sudah ada** di `app/Support/helpers.php` (EPIC 1) — siap dipakai.

**Rencana:** `DEV_DOCS/superpowers/plans/2026-06-20-epic-11-etl-pipeline.md`

---

### 3.3 EPIC 12 — Testing + Deployment

**Goal:** Production-readiness:
1. Test coverage ≥ 70% (unit + feature untuk semua modul/plugin EPIC 1–11).
2. Static analysis — PHPStan/Larastan level 5 zero-error.
3. Deployment config — `.env.production`, Artisan optimization, supervisor untuk queue.
4. CI/CD — optional GitHub Actions (PHP 8.3 + MySQL 8 matrix).
5. Security hardening checklist pra-production.

**Bukti belum dijalankan:** Belum ada `phpstan.neon`, tidak ada workflow CI, tidak ada `.env.production` template, tidak ada dokumentasi deployment.

**Rencana:** `DEV_DOCS/superpowers/plans/2026-06-20-epic-12-testing-deployment.md`

---

## 4. EPIC yang Sudah Berjalan tapi Punya Gap (Perlu Tindak Lanjut)

### 4.1 EPIC 7 (Finance) — Test Failure karena Konfigurasi

- **Masalah:** 3 dari 14 test gagal (`DeadlockException`, `QueryException: Table not found`).
- **Root cause:** `phpunit.xml` mengomentari koneksi SQLite `:memory:`, sehingga `RefreshDatabase` menjalankan `migrate:fresh` di **MySQL produksi** → drop tabel → test berikutnya gagal.
- **Bukan bug kode** — semua service (`PembayaranService`, `TagihanGeneratorService`, `TabunganMutasiService`) terverifikasi real.
- **Fix yang dibutuhkan:** Aktifkan kembali SQLite `:memory:` di `phpunit.xml` (atau gunakan MySQL test DB terisolasi).

### 4.2 EPIC 8 (Presence) — Inkonsistensi Test

- **Walkthrough `029`** klaim **82 tests / 192 assertions PASS**.
- **Audit `061`** menemukan **0 tests** pada saat ground-truth verification (test mungkin ditulis ulang/dihapus di sesi berikutnya).
- **14/14 file fisik ada**, kode real (`PresensiRuleEngine`, `QrScannerService`, `IzinApprovalService`).
- **Tindak lanjut:** Reconcile — pastikan test suite Presence benar-benar ada & pass.

### 4.3 EPIC 9 (Kurikulum) — 0/3 Test Pass + Refactoring

- Audit awal `061`: 0/3 test pass (konfigurasi phpunit, sama dengan EPIC 7).
- Dilakukan refactoring Crudlfix (`063`, `064`) + hybrid Crudlfix + Livewire (commit `0efdbbc`, design spec `069`-style).
- **Tindak lanjut:** Fix konfigurasi test + verifikasi ulang test suite Kurikulum pass.

---

## 5. Urutan Eksekusi yang Disarankan

| Prioritas | EPIC | Alasan |
|:---------:|:----:|-------|
| 1 | **Fix gap EPIC 7, 8, 9** | Tutup masalah konfigurasi `phpunit.xml` + verifikasi test sebelum menambah beban baru. Blok EPIC 12. |
| 2 | **EPIC 10** (8 Plugin Scaffold) | Bergantung pada EPIC 4 (plugin infra) ✅. Memperkaya ekosistem plugin; relatif mekanis (ulang pola Kurikulum). |
| 3 | **EPIC 11** (ETL Pipeline) | Bergantung pada semua modul domain (EPIC 5–9) ✅. Diperlukan sebelum bisa migrasi data produksi. |
| 4 | **EPIC 12** (Testing + Deployment) | Harus terakhir — coverage & CI mencakup semua EPIC 1–11. |

---

## 6. Verifikasi Fisik Cepat (Snapshot)

```bash
# Struktur modul & plugin yang sudah ada
sisfokol-laravel/app/Modules/   → Academic, Auth, Evaluation, Finance, Presence, Tenancy  (6 core — EPIC 1-9)
sisfokol-laravel/app/Plugins/   → Infrastructure, Kurikulum  (2 — EPIC 4 + 9)

# Yang BELUM ada
# app/Plugins/{AbsensiGuru,Rapor,Spp,Ppdb,Ekstrakurikuler,Bk,Perpustakaan,Inventaris}  → EPIC 10
# app/Console/Commands/MigrateLegacy*.php, Et/Verify*.php                              → EPIC 11
# phpstan.neon, .github/workflows/*.yml, .env.production                               → EPIC 12
```

---

## 7. Referensi Dokumen Pendukung

- Rencana lengkap semua EPIC: `DEV_DOCS/superpowers/plans/2026-06-20-epic-*.md`
- Audit ground-truth gabungan: `DEV_DOCS/061_audit_epic_7_8_9_ground_truth_20260625.md`
- Master plan konsolidasi: `DEV_DOCS/053_master_implementation_plan_konsolidasi_20260622.md`
- Walkthrough per EPIC: `DEV_DOCS/013, 015, 018, 023, 026, 029`
- Design spec hybrid Crudlfix + Livewire: commit `657f8f8`

---

## 8. Definition of Done — Project Lengkap

Project dianggap siap produksi ketika:
- [ ] EPIC 10 selesai — 8 plugin scaffold aktif & bisa di-toggle per-tenant
- [ ] EPIC 11 selesai — `migrate:legacy-sisfokol {tenant_id}` + `etl:verify` jalan idempotent
- [ ] EPIC 12 selesai — coverage ≥ 70%, PHPStan level 5 zero-error, CI/CD + security checklist
- [ ] Gap EPIC 7/8/9 ditutup — semua test suite pass di environment terisolasi (SQLite `:memory:` atau MySQL test DB)
- [ ] Migrasi data legacy real (dari `sisfokol_v7`) berhasil via ETL pipeline

---

*Dokumen ini merupakan snapshot status per 2026-06-28. Update ketika ada progres EPIC baru.*
