# DEV_DOCS-025: Rencana Implementasi — Epic 5: Academic Module (Akademik Sekolah)

- **Tanggal:** 2026-06-21 09:00
- **Status:** 🚀 DISETUJUI & AKTIF
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** Konversi SISFOKOL v7 (PHP native) → Laravel 11 modular monolith

---

## 🛡️ KEPUTUSAN ARSITEKTUR & DESAIN

1. **Multi-Tenancy & Audit Trail**:
   - Seluruh model domain akademik wajib menggunakan trait `BelongsToTenant` dan `TracksAuditColumns` untuk menjamin isolasi data per sekolah dan pencatatan audit log otomatis.
   - Kolom `nis` (siswa) dan `nip` (guru) bersifat unik dalam ruang lingkup tenant masing-masing (menggunakan constraint unik gabungan `['tenant_id', 'nis']`/`['tenant_id', 'nip']`).

2. **Sejarah Siswa (History-Preserving Pivot)**:
   - Hubungan siswa dengan kelas dikelola via tabel `kelas_siswa` yang menyertakan `tahun_ajaran_id`. Ini memastikan riwayat kelas siswa di masa lalu tidak tertimpa ketika siswa naik kelas di tahun ajaran baru (berbeda dari pola overwriting legacy v7).

3. **Mesin Validasi Bentrok Jadwal**:
   - Jadwal memiliki constraint unik gabungan di database untuk mencegah bentrok ruangan/kelas (`uniq_jadwal_kelas_slot`) dan guru (`uniq_jadwal_guru_slot`).
   - Validasi bentrok interaktif akan ditangani oleh service `JadwalConflictChecker` sebelum penyimpanan data untuk memberikan pesan kesalahan yang bersahabat kepada pengguna.

4. **UI Premium Tailwind CSS**:
   - Tampilan antarmuka akademik dirancang dengan keselarasan desain gelap premium di `layouts/app.blade.php`, memanfaatkan komponen Tailwind CSS, grid layout, dan efek glassmorphism.
   - Mengintegrasikan direktif Blade kustom `@field` dan `@fieldAttr` dari Epic 3 untuk menyembunyikan atau membuat field sensitif (seperti nomor telepon siswa) menjadi readonly bagi peran non-authorized.

---

## 📁 STRUKTUR FILE YANG AKAN DIBUAT/DIUBAH

```
app/
├── Modules/Academic/
│   ├── Database/Migrations/
│   │   ├── 2026_06_20_000100_create_mapel_jenis_table.php
│   │   ├── 2026_06_20_000101_create_tahun_ajaran_table.php
│   │   ├── 2026_06_20_000102_create_semester_table.php
│   │   ├── 2026_06_20_000103_create_orang_tua_table.php
│   │   ├── 2026_06_20_000104_create_siswa_table.php
│   │   ├── 2026_06_20_000105_create_siswa_orang_tua_table.php
│   │   ├── 2026_06_20_000106_create_guru_table.php
│   │   ├── 2026_06_20_000107_create_kelas_table.php
│   │   ├── 2026_06_20_000108_create_kelas_siswa_table.php
│   │   ├── 2026_06_20_000109_create_mapel_table.php
│   │   └── 2026_06_20_000110_create_jadwal_table.php
│   │
│   ├── Models/
│   │   ├── Siswa.php, OrangTua.php, SiswaOrangTua.php, Guru.php,
│   │   ├── TahunAjaran.php, Semester.php, Kelas.php, KelasSiswa.php,
│   │   └── Mapel.php, MapelJenis.php, Jadwal.php
│   │
│   ├── Controllers/
│   │   ├── SiswaController.php, GuruController.php, OrangTuaController.php,
│   │   ├── TahunAjaranController.php, SemesterController.php, KelasController.php,
│   │   ├── KelasSiswaController.php, MapelController.php, MapelJenisController.php,
│   │   └── JadwalController.php
│   │
│   ├── Policies/
│   │   ├── SiswaPolicy.php, GuruPolicy.php, KelasPolicy.php,
│   │   └── JadwalPolicy.php
│   │
│   ├── Requests/
│   │   ├── StoreSiswaRequest.php, UpdateSiswaRequest.php,
│   │   ├── StoreGuruRequest.php, StoreJadwalRequest.php, ...
│   │
│   ├── Services/
│   │   ├── JadwalConflictChecker.php       (Validasi bentrok jam mengajar)
│   │   └── KelasSiswaPromotionService.php  (Logika kenaikan kelas massal)
│   │
│   ├── Observers/
│   │   ├── SiswaObserver.php, GuruObserver.php, ...
│   │
│   └── routes.php                          (Registrasi rute domain /academic)
│
├── Providers/
│   └── EventServiceProvider.php            (Registrasi observer akademik)

database/factories/
└── SiswaFactory.php, GuruFactory.php, KelasFactory.php, ...

resources/views/academic/
├── siswa/ (index, create, edit, show)
├── guru/
├── kelas/
├── jadwal/
└── ...

tests/Feature/Academic/
├── SiswaCrudTest.php
├── KelasSiswaPromotionTest.php
├── JadwalConflictTest.php
└── TenantIsolationTest.php
```

---

## 📝 TAHAPAN IMPLEMENTASI

### Task 1: Migrasi Basis Data akademik (11 Tabel)
1. Membuat seluruh 11 berkas migrasi akademik di folder `app/Modules/Academic/Database/Migrations/`.
2. Menghubungkan foreign keys relasional antar tabel secara ketat dengan aksi `cascadeOnDelete()` atau `nullOnDelete()`.
3. Menjalankan migrasi basis data ke MySQL.

### Task 2: Pembangunan Model & Model Factories
1. Membuat berkas Model PHP bagi seluruh entitas akademik.
2. Mengintegrasikan trait `BelongsToTenant` dan `TracksAuditColumns`.
3. Mendefinisikan relasi Eloquent (`belongsTo`, `hasMany`, `belongsToMany`).
4. Membuat model factories untuk penulisan test case.

### Task 3: Fitur Deteksi Bentrok Jadwal (JadwalConflictChecker)
1. Membuat unit test `JadwalConflictTest.php`.
2. Membangun service `JadwalConflictChecker` untuk memvalidasi apakah kelas atau guru yang ditargetkan sudah terisi di slot hari & jam yang sama.
3. Mengintegrasikannya ke proses penyimpanan `JadwalController`.

### Task 4: Kenaikan Kelas Berkelanjutan (KelasSiswaPromotionService)
1. Membuat unit test `KelasSiswaPromotionTest.php`.
2. Membangun service `KelasSiswaPromotionService` untuk memetakan dan menyalan data kelas siswa dari tapel sebelumnya ke tapel baru secara transaksional & idempotent (menghindari duplikasi).

### Task 5: Pembuatan CRUD Controller, Request, Policy & Tailwind Views
1. Menulis Form Requests untuk sanitasi data akademik (validasi NIS/NIP unik per-tenant).
2. Menulis Policies untuk mengamankan data per-tenant.
3. Menulis Observers untuk merekam jejak audit (contoh: `siswa.created`, `siswa.deleted`).
4. Mendesain halaman daftar, tambah, dan edit dengan grid layout, field masking, dan integrasi Field ACL Blade Directives.

---

## 📈 RENCANA VERIFIKASI

### Pengujian Otomatis (Test Targets)
```powershell
php83 artisan test tests/Feature/Academic/SiswaCrudTest.php
php83 artisan test tests/Feature/Academic/KelasSiswaPromotionTest.php
php83 artisan test tests/Feature/Academic/JadwalConflictTest.php
php83 artisan test tests/Feature/Academic/TenantIsolationTest.php
```
