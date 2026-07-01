# DEV_DOCS-082: Audit Ulang Repo Bersih Setelah Pull Besar

- **Tanggal audit:** 2026-07-01
- **Auditor:** Arena.ai Agent Mode
- **Branch:** `main`
- **Commit audit:** `6c5d8ab` — `chore(ci3-to-ci4-migration): remove skill migration files and scripts`
- **Remote:** `origin/main` = `6c5d8ab`
- **Kondisi awal audit:** working tree bersih setelah `git pull --ff-only origin main`
- **Catatan stash lokal:**
  - `stash@{0}: arena-local-before-pull-20260701003127`
  - `stash@{1}: arena-local-tahap2a-before-pull-20260625160139`
- **Batasan audit:** environment sandbox tidak memiliki `php` dan `composer`; audit ini berbasis verifikasi statis file fisik, grep, route/controller/view reference scan, migration scan, dan pembacaan source. `php artisan test`, `route:list`, dan `migrate:fresh --seed` belum bisa dijalankan di sandbox ini.

---

## 1. Executive Summary

Repo terbaru sudah jauh berubah dibanding audit sebelumnya. Banyak gap lama sudah mulai ditutup oleh upstream:

1. **Konsolidasi model data sudah mulai berjalan.** `Student`, `Classroom`, `Subject`, dan `AcademicYear` kini diarahkan ke tabel canonical Indonesia (`siswa`, `kelas`, `mapel`, `tahun_ajaran`). Ini mengurangi masalah “parallel universe” besar.
2. **Academic module jauh lebih lengkap.** Controller dan view akademik tambahan sudah masuk (`GuruController`, `KelasController`, `MapelController`, `JadwalController`, dll).
3. **Presence Absensi jauh lebih matang.** Ada bulk absensi, view fisik, dan feature test `AbsensiBulkStoreTest`.
4. **CRUDLFIX dan Livewire hybrid mulai masuk.** Ada `app/Support/Crudlfix/*`, `app/Livewire/Crudlfix/*`, `resources/views/livewire/crudlfix/*`, dan config Livewire.
5. **RBAC sidebar platform leak sudah mulai ditangani.** Ada migration `add_is_platform_to_menus_table.php` dan `MenuRenderer` memfilter menu platform untuk tenant users.

Namun, audit statis masih menemukan beberapa **gap kritis** yang perlu ditangani sebelum repo dapat dianggap stabil:

1. **`AuthServiceProvider` masih tidak terdaftar di `bootstrap/providers.php`.** Policy mapping Finance/Kurikulum di file itu tidak akan aktif jika provider tidak diload.
2. **`PluginActivationService` masih mencoba insert kolom `module` ke tabel `permissions`, padahal migration permission tidak memiliki kolom tersebut.** Ini berpotensi crash saat aktivasi plugin.
3. **Lifecycle menu plugin belum lengkap.** Tidak ada kode yang menyinkronkan `manifest->menu()` ke tabel `menus`, dan `MenuRenderer` belum memfilter menu plugin berdasarkan tenant activation.
4. **Event hook Kurikulum masih belum terhubung ke flow real Evaluation.** `EvaluationFrameworkResolver` ada, tetapi `GradeEntryController` belum memanggilnya. `RaporGeneratorService` belum dispatch `RaportRenderSection`.
5. **Kurikulum CRUDLFIX masih memiliki mismatch schema/controller/view.** `deskripsi` dipakai controller/view tapi tidak ada di migration/model; `jenis_kegiatan` mismatch `kokurikuler_p5` vs `kokurikuler`; `pendekatan_pedagogis` enum DB tapi controller/view masih free-text.
6. **Beberapa permission CRUDLFIX tidak disemai.** Controller permission-mode memakai prefix `kelas-siswa`, `orang-tua`, `semester`, `tahun-ajaran`, tetapi permission tersebut tidak ditemukan di `RolePermissionSeeder`.
7. **Masih ada missing static views di controller lama/core.** Tidak sebanyak sebelumnya, tetapi beberapa route/controller masih bisa error bila diakses.

Kesimpulan: kondisi repo terbaru adalah **lebih baik dan progresif**, tetapi masih **PARTIAL/STABILISASI LANJUTAN DIPERLUKAN**. Fokus berikutnya seharusnya bukan menambah fitur, melainkan menutup gap provider, plugin lifecycle, event hook, permission CRUDLFIX, dan schema mismatch.

---

## 2. Pull & Kondisi Git

Pull terbaru membawa repo ke:

```text
6c5d8ab (HEAD -> main, origin/main, origin/HEAD)
```

Sebelum pull, remote unggul 91 commit:

```text
HEAD...origin/main = 0 91
```

Perubahan lokal lama distash, pull fast-forward berhasil, dan working tree sempat bersih sebelum audit ulang ini.

---

## 3. Verifikasi Statis: Route Controller & Views

### 3.1 Missing route controllers

Script static scan terhadap route controller references menghasilkan:

```text
missing route controllers: none
```

Artinya semua controller yang direferensikan langsung oleh route file saat ini memiliki class fisik.

### 3.2 Missing static views

Masih ditemukan 14 static view refs yang belum punya file fisik:

```text
app/Support/Crudlfix/CrudlfixConfig.php -> academic.siswa
app/Http/Controllers/Counselor/AchievementController.php -> counselor.achievements.create
app/Http/Controllers/Counselor/AchievementController.php -> counselor.achievements.edit
app/Http/Controllers/Counselor/CounselingController.php -> counselor.counselings.create
app/Http/Controllers/Counselor/CounselingController.php -> counselor.counselings.edit
app/Http/Controllers/Counselor/ViolationController.php -> counselor.violations.edit
app/Http/Controllers/Finance/PaymentItemController.php -> finance.payment-items.edit
app/Http/Controllers/Finance/StudentPaymentController.php -> finance.student-payments.show
app/Http/Controllers/Homeroom/ProjectScoreController.php -> homeroom.project-scores.index
app/Http/Controllers/Homeroom/ProjectScoreController.php -> homeroom.project-scores.show
app/Http/Controllers/Teacher/FormativeAssessmentController.php -> teacher.formative-assessments.show
app/Http/Controllers/Teacher/SummativeAssessmentController.php -> teacher.summative-assessments.index
app/Http/Controllers/Teacher/SummativeAssessmentController.php -> teacher.summative-assessments.create
app/Http/Controllers/Teacher/SummativeAssessmentController.php -> teacher.summative-assessments.show
```

Catatan: `CrudlfixConfig.php -> academic.siswa` kemungkinan false-positive dari contoh dokumentasi/config string, bukan route real. Namun view controller lama/core sisanya perlu diputuskan: dilengkapi, dinonaktifkan, atau dihapus jika sudah digantikan CRUDLFIX/modular views.

---

## 4. Konsolidasi Data Model: Status Terbaru

### 4.1 Yang sudah membaik

Compatibility model kini sudah diarahkan ke tabel canonical:

| Compatibility model | Tabel sekarang |
|---|---|
| `App\Models\Student` | `siswa` |
| `App\Models\Classroom` | `kelas` |
| `App\Models\Subject` | `mapel` |
| `App\Models\AcademicYear` | `tahun_ajaran` |
| `App\Models\SubjectType` | `mapel_jenis` |

Migration lama juga sudah berubah:

- `0001_01_01_200022_create_students_table.php` → no-op, komentar bahwa `students` digabung ke `siswa`.
- `0001_01_01_200014_create_classrooms_table.php` → no-op, komentar bahwa `classrooms` digabung ke `kelas`.
- `0001_01_01_200006_create_subjects_table.php` kini membuat `mapel`.
- `0001_01_01_200001_create_academic_years_table.php` kini membuat `tahun_ajaran`.

Ini adalah perbaikan besar atas gap parallel universe yang sebelumnya sangat kritis.

### 4.2 Risiko baru: dual-column compatibility dalam tabel canonical

Tabel canonical kini menyimpan kolom Indonesia dan Inggris sekaligus, contoh:

- `siswa`: `nama` + `name`, `jenis_kelamin` + `gender`, `telepon` + `phone`, dll.
- `kelas`: `nama` + `name`, `kapasitas` + `capacity`.
- `mapel`: `kode` + `code`, `nama` + `name`, `mapel_jenis_id` + `subject_type_id`.
- `tahun_ajaran`: `nama` + `name`, `aktif` + `is_active`.

Model melakukan sync di event `saving()`. Ini workable sebagai bridge transisi, tetapi tetap punya risiko:

1. Data dapat diverge jika update dilakukan via query builder langsung, raw SQL, seeder tertentu, atau migration data yang bypass Eloquent events.
2. Validasi unique masih dominan pada kolom Indonesia (`tenant_id, nis`, `tenant_id, kode`, dll), sedangkan kode lama bisa memakai kolom Inggris.
3. Arah canonical final belum terdokumentasi eksplisit di source sebagai aturan jangka panjang.

Rekomendasi: tetapkan kolom Indonesia sebagai canonical, jadikan kolom Inggris read/write compatibility sementara, lalu rencanakan deprecation.

---

## 5. Provider & Policy: Gap Kritis

### 5.1 AuthServiceProvider tidak diload

`bootstrap/providers.php` saat audit:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    App\Providers\PluginRegistryServiceProvider::class,
    Lab404\Impersonate\ImpersonateServiceProvider::class,
];
```

`App\Providers\AuthServiceProvider::class` **tidak ada**.

Dampak:

- Mapping policy di `app/Providers/AuthServiceProvider.php` tidak dijamin aktif.
- Mapping policy Finance modular dan Plugin Kurikulum berada di provider ini.
- `Gate::before()` SuperAdmin yang ditulis di `AuthServiceProvider` juga tidak aktif.

Memang sebagian policy didaftarkan manual di `AppServiceProvider`, tetapi tidak semuanya:

- Finance modular (`ItemPembayaran`, `Pembayaran`, `TabunganSiswa`) hanya ada di `AuthServiceProvider`.
- Kurikulum plugin policy hanya ada di `AuthServiceProvider`.
- `TagihanSiswa` tidak ada di mapping policy provider.

**Severity:** High.

**Rekomendasi:** Tambahkan `App\Providers\AuthServiceProvider::class` ke `bootstrap/providers.php` dan/atau pindahkan mapping penting ke `AppServiceProvider`.

---

## 6. Plugin Activation & Menu Lifecycle

### 6.1 Crash potensial saat aktivasi plugin

File:

```text
app/Modules/Auth/Services/PluginActivationService.php
```

Masih ada:

```php
Permission::firstOrCreate([
    'name' => $perm['name'],
    'guard_name' => $perm['guard_name'] ?? 'web',
], [
    'module' => $perm['module'] ?? $manifest->nama(),
]);
```

Migration `permissions` tidak memiliki kolom `module`; tabel permissions hanya memiliki `id`, `name`, `guard_name`, timestamps.

**Dampak:** Aktivasi plugin dapat gagal SQL unknown column `module`.

**Severity:** High.

**Rekomendasi:** Gunakan `Permission::findOrCreate($name, $guard)` atau tambahkan migration kolom `module`. Opsi paling aman: `findOrCreate`.

### 6.2 Menu plugin belum disinkronkan

Grep tidak menemukan pemanggilan `manifest->menu()` atau `->menu()` untuk disimpan ke tabel `menus`. `PluginActivationService` hanya seed permission.

Dampak:

- Aktivasi plugin belum otomatis membuat menu plugin muncul.
- `KurikulumPlugin::menu()` dan `app/Plugins/Kurikulum/menu.php` menjadi metadata yang tidak dipakai oleh sidebar.

**Severity:** Medium/High.

**Rekomendasi:** Saat aktivasi plugin, sync `manifest->menu()` ke tabel `menus` dengan `plugin_kode`, lalu `MenuRenderer` filter berdasarkan `isActiveForTenant`.

### 6.3 MenuRenderer belum memfilter plugin menu berdasarkan tenant activation

`MenuRenderer` sudah memfilter `is_platform` untuk tenant user, tetapi belum memfilter `plugin_kode` terhadap `PluginRegistry::isActiveForTenant()`.

Dampak: jika menu plugin pernah masuk tabel `menus`, ada risiko tampil untuk tenant yang belum mengaktifkan plugin, tergantung permission.

---

## 7. RBAC/Menu/Seeder

### 7.1 Menu platform leak sudah mulai ditangani

Ada migration:

```text
app/Modules/Auth/Database/Migrations/2026_06_28_000000_add_is_platform_to_menus_table.php
```

Dan `MenuRenderer` memfilter:

```php
$query->where('is_platform', false)
```

untuk tenant user. Ini memperbaiki gap sebelumnya dimana tenant admin dengan wildcard bisa melihat menu platform.

### 7.2 MenuSeeder masih `firstOrCreate`

File:

```text
database/seeders/MenuSeeder.php
```

Masih memakai:

```php
Menu::firstOrCreate(['kode' => $m['kode']], array_merge($m, ['aktif' => true]));
```

Dampak:

- Jika route/permission/is_platform berubah, seed ulang tidak memperbarui record lama.
- Migration backfill `is_platform` membantu sebagian, tetapi perubahan lain tetap bisa stale.

**Rekomendasi:** Gunakan `updateOrCreate()` agar menu registry bisa di-maintain lewat seeder.

### 7.3 RolePermissionSeeder cukup membaik tapi masih ada gap permission CRUDLFIX

Seeder sudah memakai `Permission::firstOrCreate()` dan `Role::findOrCreate()`, sudah lebih idempotent.

Namun static check terhadap controller CRUDLFIX mode `permission` menemukan prefix yang belum jelas disemai:

```text
MISS kelas-siswa  app/Modules/Academic/Controllers/KelasSiswaController.php
MISS orang-tua    app/Modules/Academic/Controllers/OrangTuaController.php
MISS semester     app/Modules/Academic/Controllers/SemesterController.php
MISS tahun-ajaran app/Modules/Academic/Controllers/TahunAjaranController.php
```

`Crudlfix` permission mode membentuk ability seperti:

```php
{$authorize}.{$action}
```

misalnya `kelas-siswa.viewAny`, `kelas-siswa.create`, dll.

Jika permission tidak ada dan user bukan SuperAdmin/admin wildcard, fitur akan 403.

**Rekomendasi:** Tambah permission granular atau ubah controller tersebut ke policy mode dengan policy yang jelas.

---

## 8. Evaluation ↔ Kurikulum Hook

### 8.1 Event/service ada

Ada:

- `app/Modules/Evaluation/Events/EvaluationResolveFramework.php`
- `app/Modules/Evaluation/Events/RaportRenderSection.php`
- `app/Modules/Evaluation/Services/EvaluationFrameworkResolver.php`
- Subscriber Kurikulum untuk dua event.

### 8.2 Namun flow real belum memanggil hook

Grep menunjukkan `EvaluationFrameworkResolver` hanya digunakan di service/test, bukan oleh `GradeEntryController`.

`RaporGeneratorService` juga belum dispatch `RaportRenderSection`.

Subscriber bahkan masih memiliki komentar:

```php
// NOTE: Event RaportRenderSection belum di-dispatch oleh RaporGeneratorService
```

Dampak:

- Plugin Kurikulum belum benar-benar mengalir ke Grade Entry dan Rapor real flow.
- Test plugin yang memanggil resolver langsung tidak membuktikan integrasi UI/rapor.

**Severity:** High untuk Epic 9 claim.

**Rekomendasi:**

- Inject `EvaluationFrameworkResolver` ke `GradeEntryController` dan resolve framework berdasarkan `Mapel/Kelas`.
- Dispatch `RaportRenderSection` dari `RaporGeneratorService::getReportData()`.
- Render `customSections` di `rapor.show` dan `rapor.pdf`.

---

## 9. Presence Module

### 9.1 Absensi jauh lebih baik

`AbsensiController` sekarang:

- memakai `with('absentable')`, benar untuk `Absence`.
- memiliki bulk store per kelas.
- menghapus record lama per siswa/tanggal untuk idempotency.
- menyimpan `type`, bukan `status`.
- punya feature test `AbsensiBulkStoreTest`.

### 9.2 Security concern: route absensi hanya `auth`, controller override tidak melakukan Gate explicit

Route presence group hanya menggunakan middleware:

```php
['web', 'auth']
```

`AbsensiController` override `index`, `create`, dan `store` tidak terlihat memanggil `Gate::authorize()` eksplisit. Test `AbsensiBulkStoreTest` memakai user biasa tanpa role/permission dan mengharapkan bisa post absensi.

Dampak: subfitur absensi bisa terlalu terbuka untuk semua authenticated user bila tidak ada proteksi di layer lain.

**Severity:** Medium/High.

**Rekomendasi:** Tambahkan policy/gate check untuk `viewAny/create/delete` Absence atau route middleware permission/role sesuai requirement (`picket-officer`, `counselor`, admin).

---

## 10. Plugin Kurikulum CRUDLFIX

### 10.1 Mismatch `deskripsi`

Controller/view memakai `deskripsi`:

- `KurikulumController` search/rules memasukkan `deskripsi`.
- Views `kurikulum/create/edit/index` memakai `deskripsi`.

Tetapi migration `create_kurikulum_table.php` tidak membuat kolom `deskripsi`, dan model `Kurikulum` tidak memasukkan `deskripsi` ke fillable.

**Severity:** High — form dapat gagal atau field diabaikan.

### 10.2 Mismatch `jenis_kegiatan`

Migration:

```php
['intrakurikuler', 'kokurikuler', 'ekstrakurikuler']
```

Controller CRUDLFIX:

```php
required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler
```

Views kini memakai `kokurikuler_p5` juga, tetapi DB migration tidak menerima nilai itu.

**Severity:** High — submit `kokurikuler_p5` bisa gagal DB enum.

**Rekomendasi:** Pilih satu canonical value. Karena migration saat ini menerima `kokurikuler`, controller/view sebaiknya kembali ke `kokurikuler`, atau migration diubah ke `kokurikuler_p5` dan data lama dimigrasikan.

### 10.3 Mismatch `pendekatan_pedagogis`

Migration enum:

```php
['konvensional', 'deep_learning']
```

Controller validasi:

```php
nullable|string|max:50
```

View masih input text.

**Severity:** Medium/High — user dapat input nilai di luar enum, lalu DB error.

**Rekomendasi:** Validasi `in:konvensional,deep_learning` dan view select.

---

## 11. Livewire / CRUDLFIX Hybrid

Repo terbaru membawa Livewire:

- `composer.json`: `livewire/livewire: ^4.3`
- `config/livewire.php`
- `app/Livewire/Crudlfix/*`
- `resources/views/livewire/crudlfix/*`

Karena PHP/composer tidak tersedia, belum bisa memverifikasi installability. Namun secara fisik komponen sudah ada.

Catatan audit:

- Perlu `composer install` untuk memastikan versi Livewire tersedia dan kompatibel dengan Laravel 11.
- Perlu route/browser smoke test untuk memastikan hybrid Livewire tidak konflik dengan SSR Blade CRUDLFIX.

---

## 12. Migration Status

Static scan:

```text
files: 105
create tables: 96
duplicate creates: none
```

Tabel canonical penting:

| Tabel | Status |
|---|---|
| `siswa` | created |
| `kelas` | created |
| `mapel` | created |
| `tahun_ajaran` | created |
| `students` | no create (merged) |
| `classrooms` | no create (merged) |
| `subjects` | no create (merged; migration creates `mapel`) |
| `academic_years` | no create (merged; migration creates `tahun_ajaran`) |

Migration duplicate create sudah tidak terlihat. Namun tetap wajib `migrate:fresh --seed` karena static scan tidak membuktikan foreign key order dan enum compatibility di semua driver DB.

---

## 13. Prioritas Fix Berikutnya

### P0 — wajib sebelum klaim stabil

1. Daftarkan `AuthServiceProvider` di `bootstrap/providers.php`.
2. Fix `PluginActivationService` agar tidak insert kolom `module` ke `permissions`.
3. Fix Kurikulum mismatch:
   - tambah `deskripsi` ke migration/model atau hapus dari UI/controller.
   - samakan `jenis_kegiatan` controller/view/migration.
   - validasi `pendekatan_pedagogis` sesuai enum dan ubah view ke select.
4. Hubungkan `EvaluationFrameworkResolver` ke `GradeEntryController`.
5. Dispatch `RaportRenderSection` di `RaporGeneratorService` dan render `customSections`.

### P1

6. Sync menu plugin saat activation dan filter plugin menu by active tenant.
7. Ubah `MenuSeeder` ke `updateOrCreate()`.
8. Tambah permission CRUDLFIX yang hilang (`kelas-siswa`, `orang-tua`, `semester`, `tahun-ajaran`) atau ubah ke policy mode.
9. Proteksi `AbsensiController` override dengan Gate/policy/role.
10. Putuskan nasib controller lama dengan missing views.

### P2

11. Audit dual-column compatibility (`nama/name`, `kode/code`, dst.) dan dokumentasikan canonical source of truth.
12. Jalankan browser test untuk Livewire hybrid.
13. Jalankan full test suite setelah dependency tersedia.

---

## 14. Command Verifikasi Wajib di Local/Laragon

```bash
cd sisfokol-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
```

Target test prioritas:

```bash
php artisan test tests/Feature/Rbac
php artisan test tests/Feature/Plugin
php artisan test tests/Feature/Presence
php artisan test tests/Feature/Evaluation
php artisan test tests/Feature/Academic
php artisan test tests/Feature/Finance
```

---

## 15. Status Akhir Audit

Repo terbaru **bersih dari missing route controller** dan sudah memperlihatkan kemajuan besar, terutama konsolidasi model data dan penambahan CRUDLFIX/Livewire. Tetapi masih ada beberapa bug/gap statis yang cukup jelas dan sebaiknya difix sebelum lanjut fitur baru.

Rekomendasi: lanjutkan dengan **Fix P0 kecil-terarah**, bukan apply stash lama secara utuh. Stash lama kemungkinan sudah obsolete sebagian karena remote terbaru banyak mengubah file yang sama.
