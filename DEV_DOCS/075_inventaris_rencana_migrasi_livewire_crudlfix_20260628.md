# Inventaris & Rencana Migrasi Bulk: Livewire Crudlfix

**Tanggal:** 28 Juni 2026
**Branch:** `main`
**Status:** ✅ Inventaris selesai — rencana siap eksekusi
**Task:** Task 10 (Bulk migration — inventory controllers & plan)

---

## ⚠️ KOREKSI PENTING: "18 controller" vs Ground Truth

Dev report `074` dan implementation plan `2026-06-26-hybrid-crudlfix-livewire.md` menyebut
**"19 CRUD controller total / 18 remaining"**. Setelah verifikasi fisik codebase
(`grep "use Crudlfix;"` + baca setiap controller + cek routes + cek views), angka itu **TIDAK AKURAT**.

### Akar penyebab perbedaan

Plan Task 10 Step 5 mencantumkan daftar "Admin controllers" dengan nama generik:

> UserController, ClassroomController, AcademicYearController, SubjectController,
> ExtracurricularController, AttendanceTimeController, ScheduleController

**Nama-nama ini TIDAK ADA di codebase.** Project memakai arsitektur domain-modular
(`App\Modules\Academic`, `App\Modules\Finance`, dst.) — bukan naming generik.
Daftar itu adalah **template placeholder** dari fase penulisan plan yang tidak dipetakan
ke controller nyata.

### Angka sebenarnya (ground truth)

| Metrik | Klaim Plan/Report | Ground Truth | Sumber |
|--------|-------------------|--------------|--------|
| Controller pakai Crudlfix trait | 19 | **12** | `grep "use Crudlfix;"` → 12 file (excl. 2 varian `*Crudlfix.php` tak terpakai) |
| Sudah migrasi (pilot) | 1 | 1 | `academic/kelas` (KelasController) |
| Remaining | 18 | **11** | 12 − 1 pilot |
| Migratable dgn komponen saat ini | — | **8** | 11 − 3 blocked (lihat Tier 3) |
| Blocked | — | **3** | Jadwal, TabunganSiswa, Absensi |

**Implikasi:** Bulk migration realistis = **8 controller**, bukan 18.
3 controller lain butuh pekerjaan tambahan (fitur Livewire baru atau custom component).

---

## 📋 MASTER INVENTORY — 12 Controller Crudlfix

| # | Controller | Module | Auth | Search | With | viewData | Hooks/Override | Views ada? | Tier |
|---|-----------|--------|------|--------|------|----------|----------------|-----------|------|
| 1 | **KelasController** ✈️ | Academic | permission | nama | waliKelas, branch | gurus, branches | — | index+create+edit (+livewire) | PILOT |
| 2 | SiswaController | Academic | **policy** | nama,nis,nisn | orangTuas | — | — | index+create+edit+show | T2 |
| 3 | GuruController | Academic | **policy** | nama,nip,email | — | — | — | index only | T2 |
| 4 | MapelController | Academic | permission | kode,nama | jenis | jenisList | — | index+create+edit+show | T1 |
| 5 | MapelJenisController | Academic | permission | kode,nama | — | — | — | ❌ none | T1 |
| 6 | SemesterController | Academic | permission | — | tahunAjaran | tahunAjarans | — | ❌ none | T2 |
| 7 | TahunAjaranController | Academic | permission | nama | — | — | beforeStore (aktif) | ❌ none | T1 |
| 8 | OrangTuaController | Academic | permission | nama,telepon,email | — | — | — | ❌ none | T1 |
| 9 | KelasSiswaController | Academic | permission | — | siswa,kelas,tahunAjaran | 3 lists | — | ❌ none | T2 |
| 10 | JadwalController | Academic | permission | — | 5 rel | tahunAjarans,kelasList,mapels | beforeStore/Update (conflict) | index+create | **T3 BLOCKED** |
| 11 | TabunganSiswaController | Finance | permission | no_rekening | siswa | siswaWithoutTabungan | **override store** + setor/tarik | index+create+show | **T3 BLOCKED** |
| 12 | AbsensiController | Presence | null | — | absentable | — | **override index/create/store** (bulk) | index+create | **T3 BLOCKED** |

✈️ = pilot (sudah ada `index-livewire.blade.php`)

### Varian `*Crudlfix.php` yang BUKAN controller aktif

| File | Status |
|------|--------|
| `SiswaControllerCrudlfix.php` | Contoh refactor lama — controller aktif adalah `SiswaController.php` |
| `ItemPembayaranControllerCrudlfix.php` | Varian Crudlfix — **TAPI controller aktif `ItemPembayaranController.php` adalah tradisional (tanpa Crudlfix)** |

> Catatan: `ItemPembayaranController.php` nyata menulis ulang index/create/store/edit/update/destroy
> manual dengan `Gate::authorize('viewAny', ...)` (policy). Tidak masuk scope migrasi Crudlfix
> kecuali jika ingin direfactor ke trait dulu (pekerjaan terpisah).

---

## 🏗️ STATUS PILOT (KelasController)

Pilot TIDAK belum "live" di route produksi:

- `academic.kelas.index` (produksi) → masih serve `index.blade.php` tradisional (Blade SSR, form GET reload)
- `academic.kelas-livewire` (route test terpisah) → serve `index-livewire.blade.php` (Livewire)

Route test ada di `app/Modules/Academic/routes.php:28-33`. Untuk menjadikan pilot live,
ganti isi `index.blade.php` menjadi wrapper `@livewire('crudlfix.crudlfix-page', [...])`
(sama seperti `index-livewire.blade.php`), atau hapus route terpisah dan rename file.

**Show mode gap:** `CrudlfixPage` view hanya placeholder untuk mode `show`
("`Detail view belum diimplementasikan`"). Controller yang punya `show.blade.php`
(siswa, mapel) akan kehilangan fungsi show bila dialihkan penuh ke CrudlfixPage.

---

## 🎯 RENCANA MIGRASI BERTIER

### Tier 1 — Simple (4 controller) — siap migrate sekarang

Pola: copy `index-livewire.blade.php` pilot, sesuaikan `columns`/`formFields`/`search`/`rules`/`viewData`.
Tidak ada hook, tidak ada override, field standar (text/number/select/date/checkbox).

| Controller | View target | Catatan |
|-----------|-------------|---------|
| MapelJenisController | `academic.mapel-jenis.index` (buat baru) | Tidak ada view sama sekali → Livewire jadi view pertama. Paling mudah. |
| TahunAjaranController | `academic.tahun-ajaran.index` (buat baru) | Hook `beforeStore` only sets `aktif=false` default — checkbox Livewire cukup. |
| OrangTuaController | `academic.orang-tua.index` (buat baru) | Field password nullable — form mode create saja. |
| MapelController | `academic.mapel.index` (ganti existing) | viewData `jenisList` untuk select. Punya show view — pertahankan traditional show sementara. |

### Tier 2 — Medium (4 controller) — siap migrate, butuh perhatian

| Controller | View target | Catatan |
|-----------|-------------|---------|
| SiswaController | `academic.siswa.index` (ganti existing) | **policy auth** → `authType: 'permission'` TIDAK berlaku; CrudlfixPage perlu dukung policy mode (cek di bawah). varName=`siswa`. |
| GuruController | `academic.guru.index` (ganti; buat create/edit via Page) | **policy auth**. Rule unique tenant-scoped (`unique:guru,nip,...,tenant_id,X`). Hanya index view yg ada — create/edit baru via CrudlfixPage. |
| SemesterController | `academic.semester.index` (buat baru) | viewData `tahunAjarans`, with `tahunAjaran`. Field `nama` = integer 1/2 (select). |
| KelasSiswaController | `academic.kelas-siswa.index` (buat baru) | with 3 relasi, viewData 3 lists. Hanya rule `store` (no update) —CrudlfixForm perlu handle tanpa update rules. |

### Tier 3 — Blocked (3 controller) — butuh pekerjaan tambahan

| Controller | Blocker | Solusi |
|-----------|---------|--------|
| **JadwalController** | Pakai `cascades` (tahun_ajaran→semester) + `searchSelects` (guru). Dev report `074` konfirmasi: cascade select & search select **belum diimplementasi** di komponen Livewire. Juga `beforeStore`/`beforeUpdate` hook conflict-checker. | Implementasi cascade + searchSelect di `CrudlfixForm` dulu (est. 1-2 hari), baru migrate. ATAU pertahankan traditional Blade (sudah pakai Alpine `cascade-select.blade.php`). |
| **TabunganSiswaController** | **Override `store()`** dengan `TabunganMutasiService::getOrCreateAccount()`. Punya custom `setor()`/`tarik()`. `CrudlfixForm::save()` tidak memanggil service. | `CrudlfixForm::save()` hanya `model::create($validated)` — tidak jalankan service/hook `beforeStore`. Butuh: (a) dukung hook `beforeStore` di Livewire form, atau (b) custom form Livewire khusus. |
| **AbsensiController** | **Override total** index/create/store. Bukan CRUD standar — bulk absensi per-kelas (multi-siswa, status array). Tidak cocok `CrudlfixForm` sama sekali. | Tidak migrate via CrudlfixPage. Buat Livewire custom component khusus bulk-attendance, atau biarkan traditional. |

---

## 🔍 GAP ANALISIS KOMPONEN LIVEWIRE (vs kebutuhan controller)

Dari baca `CrudlfixPage.php` + `CrudlfixConfig.php` + dev report `074`:

| Fitur | Status Komponen | Dibutuhkan oleh |
|-------|----------------|----------------|
| Index (search/sort/paginate) | ✅ jalan | semua |
| Create/Edit form + real-time validation | ✅ jalan | T1, T2 |
| Delete + bulk delete modal | ✅ jalan | semua |
| **Policy-based auth** (`authType: 'policy'`) | ⚠️ CrudlfixPage terima `authType` param, tapi `HasCrudlfixActions`/Table belum implement Gate policy check | Siswa, Guru |
| **beforeStore / beforeUpdate hooks** | ❌ `CrudlfixForm::save()` langsung `create()`/`fill+save`, tidak panggil hook | TahunAjaran, Jadwal, TabunganSiswa |
| **Cascade select** | ❌ belum | Jadwal |
| **Search select (AJAX)** | ❌ belum | Jadwal |
| **Show mode** | ❌ placeholder | Siswa, Mapel |
| **varName custom** | n/a (Livewire handle sendiri) | Siswa |
| **Rules hanya `store` (no update)** | ⚠️ CrudlfixForm pakai `config->rules` flat; controller pakai `rules.store`/`rules.update` nested | KelasSiswa |

**Implikasi untuk Tier 2:**
- Siswa & Guru pakai **policy auth**. Sebelum migrate, verifikasi `CrudlfixPage`/Table/Form
  benar-benar menghormati `authType='policy'` (Gate::authorize ability). Jika belum, tambahkan.
- TahunAjaran `beforeStore` hanya set default boolean — bisa diakali di sisi view (checkbox
  default unchecked) tanpa hook. Tapi untuk compliance, dukungan hook tetap disarankan.

---

## 📦 SPESIFIKASI MIGRASI PER-CONTROLLER (Tier 1 & 2)

Setiap spec = blok `@livewire('crudlfix.crudlfix-page', [...])` siap tempel ke `index.blade.php`.
Ambil `columns`/`formFields` dari field tabel + rules controller. `viewData` di-pass dari
controller `index()` atau di-resolve di route closure (seperti pilot `kelas-livewire`).

### T1.1 — MapelJenisController
```blade
@extends('layouts.app')
@section('title', 'Akademik — Jenis Mapel')
@section('page-title', 'Jenis Mata Pelajaran')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'model' => \App\Modules\Academic\Models\MapelJenis::class,
        'view' => 'academic.mapel-jenis',
        'route' => 'academic.mapel-jenis',
        'columns' => ['kode' => 'Kode', 'nama' => 'Nama'],
        'formFields' => [
            'kode' => ['label' => 'Kode', 'type' => 'text', 'placeholder' => 'Contoh: Wajib'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama jenis mapel'],
        ],
        'search' => ['kode', 'nama'],
        'rules' => [
            'kode' => 'required|string|max:30|unique:mapel_jenis,kode',
            'nama' => 'required|string|max:50',
        ],
        'authorize' => 'mapel',
        'authType' => 'permission',
    ])
</div>
@endsection
```
**File:** buat `resources/views/academic/mapel-jenis/index.blade.php` (tidak ada).

### T1.2 — TahunAjaranController
```blade
'columns' => ['nama' => 'Tahun Ajaran', 'tanggal_mulai' => 'Mulai', 'tanggal_selesai' => 'Selesai', 'aktif' => 'Status'],
'formFields' => [
    'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Contoh: 2026/2027'],
    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
    'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
],
'search' => ['nama'],
'rules' => [
    'nama' => 'required|string|max:20|unique:tahun_ajaran,nama',
    'tanggal_mulai' => 'required|date',
    'tanggal_selesai' => 'required|date|after:tanggal_mulai',
    'aktif' => 'boolean',
],
'authorize' => 'tahun-ajaran', 'authType' => 'permission',
```
**File:** buat `resources/views/academic/tahun-ajaran/index.blade.php`.
**Hook note:** `beforeStore` set `aktif` default false — checkbox Livewire default unchecked sudah setara.

### T1.3 — OrangTuaController
```blade
'columns' => ['nama' => 'Nama', 'hubungan' => 'Hubungan', 'telepon' => 'Telepon', 'email' => 'Email'],
'formFields' => [
    'nama' => ['label' => 'Nama', 'type' => 'text'],
    'hubungan' => ['label' => 'Hubungan', 'type' => 'select', 'options' => ['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali']],
    'telepon' => ['label' => 'Telepon', 'type' => 'text'],
    'email' => ['label' => 'Email', 'type' => 'text'],
    'pekerjaan' => ['label' => 'Pekerjaan', 'type' => 'text'],
    'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'rows' => 3],
    'username' => ['label' => 'Username', 'type' => 'text'],
    'password' => ['label' => 'Password', 'type' => 'text', 'placeholder' => 'Min. 6 karakter (kosongkan jika tidak ubah)'],
],
'search' => ['nama', 'telepon', 'email'],
'rules' => [ /* lihat controller: store rules */ ],
'authorize' => 'orang-tua', 'authType' => 'permission',
```
**File:** buat `resources/views/academic/orang-tua/index.blade.php`.
**Catatan:** password field — di mode edit, rule `nullable` (tidak wajib isi ulang).

### T1.4 — MapelController
```blade
'columns' => ['kode' => 'Kode', 'nama' => 'Nama', 'jenis.nama' => 'Jenis', 'kkm' => 'KKM', 'jenjang' => 'Jenjang'],
'formFields' => [
    'kode' => ['label' => 'Kode', 'type' => 'text'],
    'nama' => ['label' => 'Nama', 'type' => 'text'],
    'mapel_jenis_id' => ['label' => 'Jenis Mapel', 'type' => 'select', 'options' => $jenisList->pluck('nama', 'id')->toArray()],
    'kkm' => ['label' => 'KKM', 'type' => 'number', 'placeholder' => '0-100'],
    'jenjang' => ['label' => 'Jenjang', 'type' => 'text'],
],
'search' => ['kode', 'nama'],
'with' => ['jenis'],
'viewData' => ['jenisList' => $jenisList],
'rules' => [ /* store rules dari controller */ ],
'authorize' => 'mapel', 'authType' => 'permission',
```
**File:** ganti `resources/views/academic/mapel/index.blade.php`. `show.blade.php` traditional dipertahankan.

### T2.1 — SiswaController
```blade
'columns' => ['nis' => 'NIS', 'nama' => 'Nama', 'jenis_kelamin' => 'L/P', 'status' => 'Status'],
'formFields' => [
    'nis' => ['label' => 'NIS', 'type' => 'text'],
    'nisn' => ['label' => 'NISN', 'type' => 'text'],
    'nama' => ['label' => 'Nama', 'type' => 'text'],
    'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
    'tempat_lahir' => ['label' => 'Tempat Lahir', 'type' => 'text'],
    'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
    'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'rows' => 2],
    'telepon' => ['label' => 'Telepon', 'type' => 'text'],
    'agama' => ['label' => 'Agama', 'type' => 'text'],
    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar']],
],
'search' => ['nama', 'nis', 'nisn'],
'with' => ['orangTuas'],
'rules' => [ /* store rules; NIS unique per tenant pakai Rule::unique */ ],
'authorize' => 'siswa', 'authType' => 'policy',
```
**File:** ganti `resources/views/academic/siswa/index.blade.php`.
**⚠️ Blocker:** `authType: 'policy'` — verifikasi CrudlfixPage/Form mendukung Gate policy.
Rule unique tenant-scoped (closure-based `Rule::unique`) mungkin perlu di-flatten ke string
atau pass sebagai array rule.

### T2.2 — GuruController
```blade
'columns' => ['nip' => 'NIP', 'nama' => 'Nama', 'jenis_kelamin' => 'L/P', 'email' => 'Email', 'jabatan' => 'Jabatan', 'aktif' => 'Status'],
'formFields' => [
    'nip' => ['label' => 'NIP', 'type' => 'text'],
    'nama' => ['label' => 'Nama', 'type' => 'text'],
    'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
    'telepon' => ['label' => 'Telepon', 'type' => 'text'],
    'email' => ['label' => 'Email', 'type' => 'text'],
    'jabatan' => ['label' => 'Jabatan', 'type' => 'text'],
    'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
],
'search' => ['nama', 'nip', 'email'],
'rules' => [ /* store; nip unique: 'unique:guru,nip,NULL,id,tenant_id,' . tenantId */ ],
'authorize' => 'guru', 'authType' => 'policy',
```
**File:** ganti `resources/views/academic/guru/index.blade.php`. Tidak ada create/edit traditional → CrudlfixPage handle.
**⚠️ Blocker:** sama dgn Siswa — policy auth + tenant-scoped unique rule.

### T2.3 — SemesterController
```blade
'columns' => ['tahunAjaran.nama' => 'Tahun Ajaran', 'nama' => 'Semester', 'tanggal_mulai' => 'Mulai', 'tanggal_selesai' => 'Selesai', 'aktif' => 'Status'],
'formFields' => [
    'tahun_ajaran_id' => ['label' => 'Tahun Ajaran', 'type' => 'select', 'options' => $tahunAjarans->pluck('nama', 'id')->toArray()],
    'nama' => ['label' => 'Semester', 'type' => 'select', 'options' => [1 => 'Semester 1', 2 => 'Semester 2']],
    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
    'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
],
'with' => ['tahunAjaran'],
'viewData' => ['tahunAjarans' => $tahunAjarans],
'rules' => [ /* store rules */ ],
'authorize' => 'semester', 'authType' => 'permission',
```
**File:** buat `resources/views/academic/semester/index.blade.php`.

### T2.4 — KelasSiswaController
```blade
'columns' => ['siswa.nama' => 'Siswa', 'kelas.nama' => 'Kelas', 'tahunAjaran.nama' => 'Tahun Ajaran', 'no_urut' => 'No. Urut'],
'formFields' => [
    'kelas_id' => ['label' => 'Kelas', 'type' => 'select', 'options' => $kelasList->pluck('nama', 'id')->toArray()],
    'siswa_id' => ['label' => 'Siswa', 'type' => 'select', 'options' => $siswaList->pluck('nama', 'id')->toArray()],
    'tahun_ajaran_id' => ['label' => 'Tahun Ajaran', 'type' => 'select', 'options' => $tahunAjaranList->pluck('nama', 'id')->toArray()],
    'no_urut' => ['label' => 'No. Urut', 'type' => 'number', 'placeholder' => 'Opsional'],
],
'with' => ['siswa', 'kelas', 'tahunAjaran'],
'viewData' => ['kelasList' => $kelasList, 'siswaList' => $siswaList, 'tahunAjaranList' => $tahunAjaranList],
'rules' => [ /* hanya store rules — no update */ ],
'authorize' => 'kelas-siswa', 'authType' => 'permission',
```
**File:** buat `resources/views/academic/kelas-siswa/index.blade.php`.
**Catatan:** Hanya `rules.store` (no update). CrudlfixForm perlu handle: di mode edit,
fallback ke store rules atau disable edit. Verifikasi sebelum migrate.

---

## 🗺️ URUTAN EKSEKUSI REKOMENDASI

### Phase A — Validasi fondasi (prasyarat)
1. **Verifikasi policy auth di CrudlfixPage** — test dengan Siswa/Guru (authType=policy).
   Bila belum jalan, tambahkan Gate::authorize di `HasCrudlfixActions`/Form.
2. **Putuskan strategi viewData** — pilot pakai route closure passing viewData.
   Untuk produksi (`index.blade.php` diganti), controller `index()` perlu pass viewData
   ke view, ATAU CrudlfixPage resolve viewData sendiri via `getCrudlfixConfig()`.
3. **Switch pilot ke produksi** — ganti `academic/kelas/index.blade.php` jadi Livewire,
   hapus route `kelas-livewire` terpisah. Validasi end-to-end sebagai "golden pattern".

### Phase B — Tier 1 (4 controller, est. 2-3 hari)
Urutan dari paling sederhana:
1. MapelJenis (no view → paling bersih, jadi referensi kedua)
2. TahunAjaran
3. OrangTua
4. Mapel (ganti existing, pertahankan show traditional)

### Phase C — Tier 2 (4 controller, est. 3-4 hari)
Setelah policy auth terverifikasi di Phase A:
1. Semester (buat baru, no policy issue)
2. KelasSiswa (buat baru, verifikasi rules-store-only)
3. Siswa (policy, ganti existing)
4. Guru (policy, buat create/edit baru)

### Phase D — Tier 3 (3 controller, butuh R&D)
- Jadwal: implementasi cascade + searchSelect di CrudlfixForm (est. 2-3 hari) ATAU biarkan traditional.
- TabunganSiswa: tambah dukungan `beforeStore` hook di CrudlfixForm ATAU custom form.
- Absensi: custom Livewire bulk-attendance component (bukan CrudlfixPage).

### Phase E — Cleanup (Task 11)
- Hapus `index-livewire.blade.php` pilot setelah produksi switch.
- Hapus varian `*Crudlfix.php` tak terpakai (SiswaControllerCrudlfix, ItemPembayaranControllerCrudlfix)
  atau jadikan referensi terdokumentasi.
- Update guide `livewire-crudlfix-guide.md` dgn temuan real (policy auth, rules-store-only).

---

## ✅ CHECKLIST EKSEKUSI

- [ ] Phase A.1 — Verifikasi/fix policy auth di komponen Livewire
- [ ] Phase A.2 — Putuskan strategi viewData produksi
- [ ] Phase A.3 — Switch pilot KelasController ke route produksi
- [ ] Phase B.1 — Migrate MapelJenis (buat view)
- [ ] Phase B.2 — Migrate TahunAjaran (buat view)
- [ ] Phase B.3 — Migrate OrangTua (buat view)
- [ ] Phase B.4 — Migrate Mapel (ganti view)
- [ ] Phase C.1 — Migrate Semester (buat view)
- [ ] Phase C.2 — Migrate KelasSiswa (buat view, verify rules-store-only)
- [ ] Phase C.3 — Migrate Siswa (policy, ganti view)
- [ ] Phase C.4 — Migrate Guru (policy, buat create/edit)
- [ ] Phase D — R&D Tier 3 (terpisah, optional)
- [ ] Phase E — Cleanup (Task 11)

---

## 📎 REFERENSI

- **Golden pattern (pilot):** `resources/views/academic/kelas/index-livewire.blade.php`
- **Plan asli:** `DOCS/superpowers/plans/2026-06-26-hybrid-crudlfix-livewire.md` (Task 10)
- **Dev report:** `DEV_DOCS/074_dev_report_hybrid_crudlfix_livewire_20260626.md`
- **Analisis adopsi:** `DEV_DOCS/071_analisis_crudlfix_vs_tall_stack_20260628.md`
- **Guide:** `sisfokol-laravel/docs/livewire-crudlfix-guide.md`
- **Komponen:** `app/Livewire/Crudlfix/` (Page, Table, Form + 3 traits)
- **Crudlfix trait:** `app/Support/Crudlfix/Crudlfix.php`
- **Routes:** `app/Modules/Academic/routes.php`, `app/Modules/Finance/routes.php`, `app/Modules/Presence/routes.php`

---

*Inventaris generated: 28 Juni 2026 — berdasarkan verifikasi fisik codebase (ground truth).*

---

## 📋 LAPORAN EKSEKUSI (Phase A–E) — 28 Juni 2026

**Status:** ✅ Selesai. 20/20 CrudlfixRbacTest pass.

### Yang dikerjakan

#### Phase A — Fondasi komponen
- **`HasCrudlfixAuth` trait** (new): mirror backend `authorizeCrudlfix()` — policy & permission modes.
  Ditambahkan ke `CrudlfixTable` + `CrudlfixForm`. Dipanggil di table `render()` (viewAny),
  form `save()` (create/update), dan `executeDelete()` (delete). **Menutup gap keamanan**
  di mana AJAX Livewire sebelumnya bypass auth controller.
- **Controller-resolution**: `CrudlfixForm` + `CrudlfixPage` terima param `controller` (FQCN).
  Saat set, rules + auth di-resolve dari `getCrudlfixConfig()` tiap render. Ini:
  - Eliminasi duplikasi rules di view
  - **Solve masalah `Rule::unique` (closure/object)** yang tidak bisa di-serialize sebagai
    Livewire property (Siswa, Guru) — rules di-build fresh tiap request
- **`resolveRules()`** di `HasCrudlfixForm`: handle nested `rules.store`/`rules.update` +
  placeholder `{{id}}` replacement untuk unique-on-update.
- **Inline edit**: table dispatch `crudlfix-edit` event → page `setMode('edit')` (no URL nav).
  Flag `inlineEdit`/`showEdit` untuk mode index-only (Tier 3).
- **Initial search from URL**: table baca `request('search')` saat mount agar deep-link
  `?search=X` tetap berfungsi pada load pertama.
- **Pilot switch**: `academic/kelas/index.blade.php` → Livewire (param `controller`).
  Route `kelas-livewire` + file `index-livewire.blade.php` dihapus.

#### Phase B — Tier 1 (4 controller, full CrudlfixPage)
MapelJenis (buat view), TahunAjaran (buat), OrangTua (buat), Mapel (ganti).

#### Phase C — Tier 2 (4 controller, full CrudlfixPage)
Semester (buat), KelasSiswa (buat), Siswa (ganti, policy auth via controller),
Guru (ganti, policy auth via controller).

#### Phase D — Tier 3 (index-only Livewire table + traditional create/edit)
- **Jadwal**: index → Livewire table (URL edit ke traditional cascade form).
- **TabunganSiswa**: index → Livewire table (`showEdit=false`; create via service, show handles setor/tarik).
- **Absensi**: **dibiarkan traditional** — index punya custom kelas/date filtering
  (subquery `KelasSiswa`) yang tidak didukung generic table. Bulk-create per-kelas
  tidak cocok `CrudlfixForm`. Butuh custom Livewire component bila ingin dimigrate.

#### Phase E — Verifikasi
- `php -l`: 6 file PHP OK
- `view:clear`: OK
- `route:list`: semua route terdaftar, `kelas-livewire` dihapus
- `php artisan test --filter=Crudlfix`: **20 passed (30 assertions)**
  - Termasuk: search+policy auth, tenant isolation, gate authorization guru

### Pola view hasil migrasi

**Full CrudlfixPage (Tier 1 & 2)** — view hanya pass `controller` + `columns` + `formFields`:
```blade
@livewire('crudlfix.crudlfix-page', [
    'controller' => \App\Modules\Academic\Controllers\MapelController::class,
    'columns' => ['kode' => 'Kode', 'nama' => 'Nama', 'jenis.nama' => 'Jenis'],
    'formFields' => [
        'kode' => ['label' => 'Kode', 'type' => 'text'],
        'mapel_jenis_id' => ['label' => 'Jenis', 'type' => 'select', 'options' => $jenisList->pluck('nama','id')->toArray()],
    ],
])
```

**Index-only (Tier 3)** — view pass `crudlfix-table` langsung + `inlineEdit: false`:
```blade
@livewire('crudlfix.crudlfix-table', [
    'model' => $config->model, 'route' => $config->route,
    'with' => $config->with ?? [], 'search' => $config->search ?? [],
    'columns' => [...],
    'inlineEdit' => false, 'showEdit' => true,
])
```

### Known limitations
1. **Double query pada initial load** — controller `index()` (Crudlfix trait) masih build
   paginator, lalu Livewire table jalankan query sendiri. Optimasi: override `index()`
   di migrated controller untuk skip pagination. Future task.
2. **Show mode placeholder** — `CrudlfixPage` mode `show` masih placeholder.
   Controller dengan show view (Siswa, Mapel, Kelas) masih bisa akses via URL `.show`
   tradisional. Implementasi show mode Livewire = future task.
3. **Absensi tidak dimigrate** — butuh custom component untuk kelas/date filter + bulk.
4. **`*Crudlfix.php` variant** (SiswaControllerCrudlfix, ItemPembayaranControllerCrudlfix)
   masih ada sebagai contoh tak terpakai. Cleanup di Task 11.

### Metrik akhir

| Metric | Value |
|--------|-------|
| Controller dimigrate full CrudlfixPage | 9 (Kelas + Tier1×4 + Tier2×4) |
| Controller dimigrate index-only | 2 (Jadwal, TabunganSiswa) |
| Controller dibiarkan traditional | 1 (Absensi) |
| File PHP dibuat/diubah | 7 (1 new trait + 6 modified) |
| File Blade dibuat/diubah | 10 (8 new/replace views + 2 component views + 1 deleted) |
| Tests | 20 passed |
| Auth gap closed | ✅ (Livewire AJAX now enforces policy/permission) |

