# DEV_DOCS-072: Review Jujur EPIC 7, 8, 9 — Ground Truth Codebase

- **Tanggal verifikasi:** 2026-06-28
- **Penulis:** ZCode Agent
- **Proyek:** SISFOKOL v7 → Laravel 11 (`sisfokol-laravel/`)
- **Tujuan:** Review jujur, no overclaim, no hallucination — status REAL EPIC 7 (Finance), 8 (Presence), 9 (Kurikulum) berdasarkan codebase fisik + eksekusi test suite.
- **Metode:** Verifikasi file fisik (count + list) + eksekusi `php artisan test` sungguhan + baca kode sumber.
- **Prinsip:** Setiap klaim disertai bukti. Yang tidak diverifikasi dinyatakan jelas sebagai "tidak diketahui".

---

## 1. Ringkasan Eksekutif

Dokumen audit sebelumnya (`061` 25-Jun, `070` 28-Jun) **salah melaporkan status test** karena masalah environment — bukan karena kode rusak. Setelah test suite dijalankan sungguhan dengan MySQL aktif:

```
Tests:    45 passed (106 assertions)
Duration: 85.21s
```

**SEMUA TEST LULUS. Nol gagal.** Ini koreksi besar terhadap klaim 061 & 070.

| EPIC | Klaim 061 (25-Jun) | Klaim 070 (28-Jun) | **Realita diverifikasi 28-Jun** |
|:----:|:---|:---|:---|
| 7 Finance | 11/14 pass (3 fail) | "belum bisa verifikasi, MySQL mati" | ✅ **14/14 PASS** |
| 8 Presence | 0 test files | 2 test files, unverifiable | ✅ **11/11 PASS** (3 file, bukan 2) |
| 9 Kurikulum | 0/3 pass | unverifiable | ✅ **9/9 PASS** EPIC-9 (2 file, bukan 1) |

**Akar masalah kegagalan sebelumnya:**
- `061`: `phpunit.xml` memakai MySQL produksi → `RefreshDatabase` drop tabel produksi → test berikutnya gagal + data produksi hilang.
- `070`: MySQL/Laragon tidak aktif saat sesi itu → tidak bisa koneksi sama sekali.
- **Sekarang:** `phpunit.xml` sudah pakai DB terpisah `sisfokol_laravel_test` (MySQL, bukan SQLite). DB tersebut ADA dan MySQL AKTIF → semua test aman dan lulus.

---

## 2. Bukti Environment (yang membuat verifikasi mungkin)

| Pemeriksaan | Hasil |
|---|---|
| Port 3306 LISTENING | ✅ (netstat: `0.0.0.0:3306 LISTENING`) |
| Koneksi MySQL dengan kredensial `.env` | ✅ (`root` / `password`) |
| DB `sisfokol_laravel_test` ada | ✅ (terlihat di `SHOW DATABASES`) |
| `phpunit.xml` DB config | `mysql` + `sisfokol_laravel_test` (DB terpisah dari produksi `sisfokol_laravel`) |

**Implikasi:** Verifikasi test suite kini memungkinkan — hal yang gagal dilakukan 061 & 070.

---

## 3. Koreksi Discrepancy: Dokumen 061/070 vs Codebase Nyata

Dokumen sebelumnya tidak hanya salah soal status test, tapi juga **inakurat untuk angka detail**. Berikut koreksi per item:

| Item | Klaim 061/070 | Realita Fisik | Sifat |
|---|---|---|---|
| Finance PHP files | 26 | **28** | 061 undercount |
| Finance policy nama | `StudentPaymentPolicy` | `TabunganPolicy` | 061 salah sebut |
| Finance migrations | 4 | **5** (ada `create_tabungan_siswa_table`) | 061 lewat |
| Presence PHP files (module) | 14 | **17** | 061 undercount |
| Presence services (daftar) | `AttendanceService` + `PresensiRuleEngine` | `IzinApprovalService`, `PresensiRuleEngine`, `QrScannerService` | 061 salah daftar |
| Presence migrations | 1 | **5** | 061 undercount drastis |
| Presence views | 11 | **8** | 061 overcount |
| Presence test files | 0 (061) / 2 (070) | **3** (`AbsensiBulkStoreTest` terlewat) | 070 undercount |
| Kurikulum files | 28 | **27** | 061 overcount (math 4+3+3+2+1+1+4+9=27, bukan 28) |
| Kurikulum test files | 1 | **2** (`KurikulumCrudTest` 6 test terlewat) | 070 undercount |

**Catatan:** `AttendanceService` memang ADA, tapi di `app/Services/` (terpisah dari module Presence), bukan di `app/Modules/Presence/Services/`. Ini konfirmasi debt arsitektur "inconsistent location" yang 061 sebut benar.

**Implikasi:** Angka detail di 061/070 **tidak boleh dipakai sebagai sumber kebenaran** tanpa verifikasi ulang. Hanya hasil test suite yang baru saja dijalankan yang valid.

---

## 4. Status per EPIC (Berbasis Bukti)

### 4.1 EPIC 7 — Finance ✅

| Aspek | Bukti Verifikasi |
|---|---|
| File PHP fisik | 28 file di `app/Modules/Finance/` (Controllers 6, Models 5, Migrations 5, Services 4, Policies 3, Requests 3, Events 1, routes 1) |
| Views | 20 blade di `resources/views/finance/` (cocok klaim 061) |
| Routes terdaftar | 22 (via `php artisan route:list`) |
| Test files | 3 (`PembayaranServiceTest`, `TabunganMutasiTest`, `TagihanGeneratorTest`) |
| **Hasil test** | **14/14 PASS** (6 + 5 + 3) |
| Kode nyata | Dibaca `PembayaranService::bayar()` — production-grade: `DB::transaction`, `lockForUpdate()` (pessimistic locking anti-race), tenant isolation via `withoutGlobalScope('tenant')` + filter `tenant_id`, audit log via `AuditLogger`, event dispatch `PaymentReceived`. **Bukan stub.** |

### 4.2 EPIC 8 — Presence ✅

| Aspek | Bukti Verifikasi |
|---|---|
| File PHP fisik | 17 di `app/Modules/Presence/` (Controllers 4, Services 3, Migrations 5, Policies 2, Events 1, Observers 1, routes 1) |
| Lokasi terpencar | 3 model di `app/Models/` (`Attendance`, `AttendanceTime`, `SubjectAttendance`) + `AttendanceService` di `app/Services/` — **debt arsitektur nyata** |
| Views | 8 blade di `resources/views/presence/` (061 salah klaim 11) |
| Routes terdaftar | 24 |
| Test files | 3 (`AbsensiBulkStoreTest`, `IzinApprovalTest`, `QrScanTest`) |
| **Hasil test** | **11/11 PASS** (3 + 4 + 4) |
| Catatan coverage | 4 controller, hanya 3 test file. `LaporanPresensiController` dan `AbsensiController` **belum tentu ter-cover** — path tanpa test = status tidak diketahui. |

### 4.3 EPIC 9 — Kurikulum ✅

| Aspek | Bukti Verifikasi |
|---|---|
| File fisik | 27 di `app/Plugins/Kurikulum/` (plugin, bukan module — lokasi benar per arsitektur plugin) |
| Struktur | Controllers 3, Models 3, Migrations 4, Subscribers 2, Policy 1, Provider 1, Views 9, manifest 4 (KurikulumPlugin, menu, permissions, routes) |
| Routes terdaftar | 21 |
| Test files | 2 EPIC-9-specific (`KurikulumPluginTest`, `KurikulumCrudTest`) + 3 plugin-infra (`EnsurePluginEnabled`, `PluginActivation`, `PluginRegistry`) |
| **Hasil test** | **9/9 PASS** EPIC-9 (3 + 6) + 11/11 PASS plugin-infra |
| Kode nyata | Plugin architecture + CRUD terverifikasi via test. `KurikulumCrudTest` mencakup index/store/update/destroy + tenant isolation. |

---

## 5. Rekapitulasi Hasil Test (Bukti Eksekusi)

Command dijalankan:
```bash
php artisan test tests/Feature/Finance tests/Feature/Presence tests/Feature/Plugin
```

Hasil (45 tests, 106 assertions, 0 fail):

| Test Class | EPIC | Methods | Status |
|---|:---:|:---:|:---:|
| `PembayaranServiceTest` | 7 | 6 | ✅ 6/6 |
| `TabunganMutasiTest` | 7 | 5 | ✅ 5/5 |
| `TagihanGeneratorTest` | 7 | 3 | ✅ 3/3 |
| `AbsensiBulkStoreTest` | 8 | 3 | ✅ 3/3 |
| `IzinApprovalTest` | 8 | 4 | ✅ 4/4 |
| `QrScanTest` | 8 | 4 | ✅ 4/4 |
| `KurikulumPluginTest` | 9 | 3 | ✅ 3/3 |
| `KurikulumCrudTest` | 9 | 6 | ✅ 6/6 |
| `EnsurePluginEnabledTest` | 4* | 3 | ✅ 3/3 |
| `PluginActivationTest` | 4* | 4 | ✅ 4/4 |
| `PluginRegistryTest` | 4* | 4 | ✅ 4/4 |

*plugin-infra (EPIC 4) mendukung nature plugin EPIC 9.

**EPIC 7+8+9 specific: 34/34 PASS.** Dengan plugin-infra: 45/45 PASS.

---

## 6. ⚠️ Batas Verifikasi — Honest Disclosure

Agar tidak overclaim, berikut yang **TIDAK** diverifikasi dalam sesi ini:

1. **Tidak ada browser/E2E test.** Test yang lulus adalah PHPUnit feature test (level service/controller), bukan verifikasi UI di browser. Tampilan bisa saja rusak meski test service lulus.
2. **Coverage tidak 100%.** Beberapa controller/view tidak punya test (mis. `LaporanKeuanganController`, `TabunganSiswaController`, `LaporanPresensiController`). Path tanpa test = **status tidak diketahui**, bukan "berfungsi".
3. **Tidak verifikasi runtime production.** Tidak menjalankan app via browser, tidak cek error log, tidak uji interaksi multi-tenant nyata dengan data live.
4. **Inconsistent file location adalah debt nyata** — model Presence di `app/Models/`, `AttendanceService` di `app/Services/` (terpisah dari module). Refactoring belum tuntas. Ini bukan klaim kosong; lokasi fisik diverifikasi.
5. **Test PASS = path yang di-test berfungsi.** Tidak ada jaminan untuk path yang tidak di-test.

---

## 7. Kesimpulan Jujur

1. **Status kode EPIC 7, 8, 9: NYATA dan BERFUNGSI untuk path yang di-test.** Bukan stub, bukan mockup. Test suite terverifikasi 45/45 PASS dengan MySQL aktif.

2. **Dokumen 061 & 070 UNDERCLAIM** karena masalah environment (bukan bug kode). Mereka melaporkan "test gagal/belum terverifikasi" padahal setelah environment diperbaiki, semua lulus.

3. **Dokumen 061/070 juga INAKURAT untuk angka detail** (hitung file, nama service, jumlah migrasi, jumlah test file). Tidak boleh dipakai sebagai sumber kebenaran tanpa verifikasi ulang.

4. **Yang masih perlu tindak lanjut (jujur, bukan overclaim):**
   - Tambah test untuk controller yang belum ter-cover (EPIC 8: `LaporanPresensiController`, `AbsensiController`).
   - Browser test end-to-end untuk memvalidasi UI.
   - Konsolidasi lokasi file yang terpencar (model/service ke dalam module masing-masing).
   - Migrasi `phpunit.xml` ke SQLite `:memory:` untuk kecepatan + isolasi test (opsional; saat ini MySQL test DB sudah aman).

---

## 8. Verdict

| EPIC | Kode Ada | Test PASS | Coverage | Verdict Jujur |
|:----:|:--------:|:---------:|:--------:|:--------------|
| 7 Finance | ✅ 28 file | ✅ 14/14 | Parsial (service terkover, sebagian controller tidak) | **Selesai + terverifikasi**, coverage bisa ditingkatkan |
| 8 Presence | ✅ 17+4 file | ✅ 11/11 | Parsial (3/4 controller terkover) | **Selesai + terverifikasi**, coverage gap di Laporan/Absensi |
| 9 Kurikulum | ✅ 27 file | ✅ 9/9 | Baik (CRUD + plugin + tenant isolation) | **Selesai + terverifikasi** |

**Tidak ada overclaim:** kode terverifikasi berfungsi via test, tapi UI dan path tak-tertest TIDAK diklaim berfungsi.

---

## 9. Referensi

- **Dokumen yang dikoreksi:** `061_audit_epic_7_8_9_ground_truth_20260625.md`, `070_dev_report_status_real_epic_7_8_9_20260628.md`
- **Bukti kode:** `app/Modules/Finance/Services/PembayaranService.php` (dibaca, production-grade)
- **Bukti test:** eksekusi `php artisan test` 28-Jun-2026 → 45 passed
- **Config:** `phpunit.xml` (DB `sisfokol_laravel_test`), `.env` (MySQL `root`/`password`)

---

*Dokumen ini adalah verifikasi mandiri berbasis bukti fisik + eksekusi test suite. Setiap angka dapat direproduksi dengan command di bagian 5.*
