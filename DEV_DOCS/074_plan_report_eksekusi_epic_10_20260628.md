# DEV_DOCS-074: Plan Report — Eksekusi EPIC 10 (8 Plugin Scaffold) — Pilot First

- **Tanggal:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Tujuan:** Rencana eksekusi EPIC 10 (8 Plugin Scaffold) dengan pendekatan **pilot 1-2 plugin dulu** untuk validasi pattern, baru lanjut sisanya.
- **Dasar:** Plan asli `DEV_DOCS/superpowers/plans/2026-06-20-epic-10-plugin-scaffold.md` (1427 baris, 9 tasks) + baseline 144/144 PASS (report 073).
- **Keputusan user:** Plan report dulu → eksekusi pilot 1-2 plugin → validasi → lanjut sisanya.

---

## 1. Goal & Definition of Done

**Goal:** Scaffold 8 plugin lengkap (manifest, provider, models, migrations, controllers, policies, routes, views, permissions, menu, activation test) + register ke PluginRegistry. Plugin Kurikulum (EPIC 9) = referensi pattern.

**Definition of Done (DoD) per plugin:**
- [ ] Direktori `app/Plugins/<Nama>/` lengkap (Providers, Models, Controllers, Policies, Database/Migrations)
- [ ] `<Nama>Plugin.php` implement `PluginContract` (9 methods)
- [ ] Migrations created & jalan (`php artisan migrate`)
- [ ] Models pakai `BelongsToTenant` + `TracksAuditColumns` + `SoftDeletes`
- [ ] Controller + Policy + Routes (dibungkus `plugin:<kode>` middleware)
- [ ] Views placeholder di `resources/views/plugins/<kode>/` (index/create/edit)
- [ ] `permissions.php` + `menu.php`
- [ ] `tests/Feature/Plugin/<Nama>PluginActivationTest.php` PASS

**DoD keseluruhan EPIC 10:**
- [ ] 8 plugin ter-discover PluginRegistry (`syncToDatabase` → 9 baris di tabel `plugins`)
- [ ] Semua activation tests PASS
- [ ] Full project suite tetap 144+/144+ PASS (no regression)
- [ ] Total 17 tabel baru ter-create (65 tabel total project)
- [ ] Tag `epic-10-plugin-scaffold`

---

## 2. 8 Plugin yang Akan Di-Scaffold

| # | Kode | Nama | Tables | Fitur Utama | Plan Task |
|---|------|------|:------:|-------------|:---------:|
| 1 | `absensi_guru` | Absensi Guru | 2 | Presensi guru via QR/manual, rekap bulanan | Task 1 |
| 2 | `rapor` | Rapor Builder | 1 | Template rapor custom per tenant, cetak PDF batch | Task 2 |
| 3 | `spp` | SPP Manager | 2 | Auto-generate tagihan SPP bulanan, reminder tunggakan | Task 3 |
| 4 | `ppdb` | PPDB Online | 3 | Formulir pendaftaran siswa baru online, seleksi | Task 4 |
| 5 | `ekstrakurikuler` | Ekstrakurikuler | 2 | Pendaftaran ekskul, absensi ekskul | Task 5 |
| 6 | `bk` | Bimbingan Konseling | 2 | Catatan BK per siswa, agenda konseling | Task 6 |
| 7 | `perpustakaan` | Perpustakaan | 3 | Koleksi buku, peminjaman, pengembalian | Task 7 |
| 8 | `inventaris` | Inventaris | 2 | Aset sekolah, kondisi, mutasi | Task 8 |
| 9 | (register) | — | — | Sync semua plugin ke registry + tag | Task 9 |

**Total tabel baru:** 17 tabel (2+1+2+3+2+2+3+2).
**Total file baru (estimasi):** ~80+ file (8 plugin × ~10 file/plugin).

---

## 3. Pendekatan Eksekusi: Pilot First

### Fase A — Pilot (1-2 plugin) — VALIDASI PATTERN

Tujuan: Validasi pattern scaffold (manifest, provider, migration, model, controller, policy, route, view, test) jalan end-to-end dengan **1-2 plugin** sebelum produksi massal 8 plugin. Jika pattern bermasalah, ketahuan cepat sebelum invest 8 plugin.

**Pilot pilihan:** Plugin #1 `absensi_guru` + Plugin #2 `rapor`.
- `absensi_guru` = plugin paling lengkap di plan (2 tabel, QR/manual, rekap) — uji pola dasar.
- `rapor` = plugin dengan dependency (`kurikulum`) + fitur cetak PDF — uji pola integrasi.

### Fase B — Batch Sisa (6 plugin) — SETELAH PILOT OK

Setelah pilot validasi pattern jalan & test PASS, eksekusi 6 plugin sisanya dalam batch:
- `spp`, `ppdb`, `ekstrakurikuler`, `bk`, `perpustakaan`, `inventaris`

### Fase C — Register + Final Verification

- Task 9: Sync semua plugin ke registry, run full test suite, tag.

---

## 4. Detail Fase A — Pilot (Eksekusi Sekarang)

### Fase A.1 — Plugin #1: Absensi Guru (`absensi_guru`)

**Files (Plan Task 1):**
- `app/Plugins/AbsensiGuru/{AbsensiGuruPlugin.php, permissions.php, menu.php, routes.php}`
- `app/Plugins/AbsensiGuru/Providers/AbsensiGuruServiceProvider.php`
- `app/Plugins/AbsensiGuru/Models/PresensiGuru.php, RekapKehadiranGuru.php`
- `app/Plugins/AbsensiGuru/Controllers/PresensiGuruController.php`
- `app/Plugins/AbsensiGuru/Policies/PresensiGuruPolicy.php`
- `app/Plugins/AbsensiGuru/Database/Migrations/2026_06_20_000600_*.php` + `000601_*.php`
- `resources/views/plugins/absensi_guru/{scan, rekap}.blade.php`
- `tests/Feature/Plugin/AbsensiGuruPluginTest.php`

**Steps:**
1. Create directory structure
2. Create 2 migrations (`presensi_guru`, `rekap_kehadiran_guru`) + run `php artisan migrate`
3. Implement `AbsensiGuruPlugin` manifest (PluginContract)
4. Create `AbsensiGuruServiceProvider`
5. Create 2 Models (PresensiGuru, RekapKehadiranGuru)
6. Create `PresensiGuruController` (scan + rekap)
7. Create `PresensiGuruPolicy`
8. Create `routes.php` (dibungkus `plugin:absensi_guru` middleware)
9. Create views placeholder (scan, rekap)
10. Create `permissions.php` + `menu.php`
11. Write `AbsensiGuruPluginTest` (activation + permission seed)
12. Run test → must PASS

**Checkpoint:** Setelah Fase A.1 selesai & test PASS → review pattern sebelum lanjut A.2.

### Fase A.2 — Plugin #2: Rapor Builder (`rapor`)

**Files (Plan Task 2):**
- `app/Plugins/Rapor/{RaporPlugin.php, permissions.php, menu.php, routes.php}`
- `app/Plugins/Rapor/Providers/RaporServiceProvider.php`
- `app/Plugins/Rapor/Models/RaporTemplate.php`
- `app/Plugins/Rapor/Controllers/RaporController.php`
- `app/Plugins/Rapor/Policies/RaporPolicy.php`
- `app/Plugins/Rapor/Database/Migrations/2026_06_20_000610_*.php`
- `resources/views/plugins/rapor/{index, template, cetak}.blade.php`
- `tests/Feature/Plugin/RaporPluginTest.php`

**Khusus:** Plugin Rapor punya **dependency `kurikulum`** (inject section via event `Rapor.ResolveSections`). Ini menguji pola plugin-to-plugin integration.

**Checkpoint:** Setelah Fase A.2 → evaluasi pattern. Jika keduanya PASS tanpa hambatan signifikan → lanjut Fase B. Jika ada masalah pattern → fix dulu sebelum produksi massal.

---

## 5. Detail Fase B — Batch Sisa (Setelah Pilot OK)

Eksekusi 6 plugin sisanya mengikuti pattern yang sudah divalidasi di pilot. Urutan sesuai plan:

| Urutan | Plugin | Tables | Catatan Khusus |
|:------:|--------|:------:|----------------|
| B.1 | `spp` | 2 | Punya Artisan command `spp:generate` + scheduled bulanan |
| B.2 | `ppdb` | 3 | Punya konversi pendaftar → siswa (`registerAsSiswa`) |
| B.3 | `ekstrakurikuler` | 2 | Pendaftaran ekskul + absensi |
| B.4 | `bk` | 2 | Catatan BK per siswa |
| B.5 | `perpustakaan` | 3 | Koleksi + peminjaman + pengembalian |
| B.6 | `inventaris` | 2 | Aset + mutasi |

**Per plugin:** Ikuti 12-step pattern yang sama dengan pilot. Checkpoint review setiap 2 plugin.

---

## 6. Detail Fase C — Register + Final Verification

### Task 9: Register semua plugin
- [ ] Verifikasi `PluginRegistryServiceProvider` auto-discover (sudah dari EPIC 4 — tidak perlu ubah)
- [ ] Sync ke DB: `php artisan tinker --execute="app(\App\Support\PluginRegistry::class)->syncToDatabase();"`
- [ ] Expected: 9 baris di tabel `plugins` (kurikulum + 8 baru)

### Final verification
- [ ] Run semua activation tests: `php artisan test tests/Feature/Plugin/` → all pass
- [ ] Run full project suite: `php artisan test` → 144+ pass, 0 fail (no regression)
- [ ] Verifikasi tabel count: 65 tabel total (48 existing + 17 baru)
- [ ] Verifikasi DB produksi tidak tersentuh (test pakai `sisfokol_laravel_test`)
- [ ] Tag: `git tag epic-10-plugin-scaffold`
- [ ] Final dev report `075_dev_report_epic_10_scaffold_<tanggal>.md`

---

## 7. Estimasi & Checkpoint

| Fase | Plugin | Estimasi | Checkpoint |
|:----:|:------:|:--------:|:-----------|
| A.1 | absensi_guru | 30–45 menit | Test PASS → review pattern |
| A.2 | rapor | 30–45 menit | Test PASS → evaluasi pilot |
| B.1–B.6 | 6 plugin | 3–4 jam | Review setiap 2 plugin |
| C | register + verify | 20–30 menit | Full suite PASS + tag |
| **Total** | **8 plugin** | **4.5–6 jam** | — |

**Pilot (Fase A) saja:** ~1–1.5 jam untuk 2 plugin.

---

## 8. Risk & Mitigasi

| Risk | Probabilitas | Dampak | Mitigasi |
|------|:------------:|:------:|----------|
| Pattern scaffold tidak match implementasi EPIC 4 saat ini | Low | High | Pilot validasi dulu (Fase A) sebelum massal |
| Migration FK ke tabel yang belum ada (urutan) | Medium | Medium | Pakai timestamp `0006xx` setelah EPIC 1-9 |
| Plugin Rapor dependency `kurikulum` event tidak ter-resolve | Medium | Medium | Ikuti pola `KurikulumPluginTest` (register + subscribe eksplisit) |
| Plugin SPP command `spp:generate` butuh scheduler config | Low | Low | Daftar di `routes.php` / provider, skip scheduler di scaffold |
| Test activation butuh permission seed timing | Medium | Low | Ikuti pola `PluginActivationTest` yang sudah PASS |
| Regression test existing (144/144) | Low | High | Run full suite setelah Fase C |

---

## 9. Skill Pendukung untuk Eksekusi

- **`nwidart-module-management`** — JIKA pakai nwidart modules (cek dulu; project ini pakai custom `app/Plugins/` bukan nwidart, jadi mungkin tidak relevan)
- **`verification-before-completion`** — sebelum claim plugin selesai, run test & tunjukkan output
- **`smart-debugging`** — saat test fail & investigasi root cause
- **`test-driven-development`** — saat tulis activation test per plugin

**Prinsip:** Setiap plugin selesai = test PASS dengan evidence output command. Tidak klaim selesai tanpa bukti.

---

## 10. Pre-flight Check (Sebelum Eksekusi)

- [x] Baseline 144/144 PASS (report 073)
- [x] `app/Plugins/` hanya berisi `Infrastructure` + `Kurikulum` (konfirmasi 8 plugin belum ada)
- [x] `PluginContract` + `PluginContext` interface terverifikasi match dengan plan
- [x] Plugin Kurikulum (EPIC 9) = referensi pattern yang sudah jalan & teruji
- [x] MySQL aktif, DB test `sisfokol_laravel_test` tersedia
- [x] Plan asli EPIC 10 detail & konkret (kode lengkap, bukan placeholder)

---

## 11. Output yang Diharapkan

Setelah Fase A (pilot) selesai:
1. 2 plugin (`absensi_guru`, `rapor`) lengkap & test PASS
2. Checkpoint review pattern — putuskan lanjut Fase B atau fix pattern

Setelah Fase B + C selesai (EPIC 10 complete):
1. 8 plugin scaffold lengkap & semua activation test PASS
2. `075_dev_report_epic_10_scaffold_<tanggal>.md` — final report
3. Update `069_dev_report_status_epic_lengkap` — EPIC 10 → ✅ Selesai
4. Tag `epic-10-plugin-scaffold`
5. Baseline project: 144+ → 152+ PASS (8 activation test baru)
6. Sisa EPIC yang belum dijalankan: EPIC 11 (ETL) + EPIC 12 (Testing/Deploy)

---

## 12. Urutan Eksekusi Checklist

```
□ FASE A — PILOT
  □ A.1 Plugin absensi_guru
    □ Step 1-2: Dir structure + 2 migrations + migrate
    □ Step 3-4: Manifest + ServiceProvider
    □ Step 5: 2 Models
    □ Step 6-7: Controller + Policy
    □ Step 8: routes.php
    □ Step 9: Views placeholder
    □ Step 10: permissions.php + menu.php
    □ Step 11: AbsensiGuruPluginTest
    □ Step 12: Run test → PASS (evidence)
    □ CHECKPOINT: review pattern
  □ A.2 Plugin rapor
    □ (12 steps sama + dependency kurikulum event)
    □ Run test → PASS (evidence)
    □ CHECKPOINT: evaluasi pilot → go/no-go Fase B

□ FASE B — BATCH SISA (setelah pilot OK)
  □ B.1 spp | B.2 ppdb | B.3 ekstrakurikuler
  □ B.4 bk | B.5 perpustakaan | B.6 inventaris
  □ Review setiap 2 plugin

□ FASE C — REGISTER + VERIFY
  □ Task 9: Sync registry → 9 baris di tabel plugins
  □ Full test suite → 152+ PASS, 0 fail
  □ Verifikasi DB produksi aman
  □ Tag epic-10-plugin-scaffold
  □ Final dev report 075
```

---

*Dokumen ini adalah plan report. Eksekusi dimulai dari Fase A.1 (Plugin absensi_guru). Setiap checkpoint, hentikan & review sebelum lanjut.*
