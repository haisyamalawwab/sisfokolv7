# DEV_DOCS-083: Deep Critical Codebase Audit — No Overclaim, Evidence-Based

- **Tanggal:** 2026-07-01
- **Auditor:** Arena.ai Agent Mode
- **Scope:** repo bersih terbaru setelah pull besar, audit berbasis codebase fisik.
- **Commit:** `6c5d8ab5f987585bf772c51b04a61b15ca4505b5`
- **Branch:** `main`
- **Status kerja saat audit:** tidak ada perubahan kode aplikasi; hanya file audit baru yang dibuat.
- **Batasan penting:** sandbox tidak memiliki `php` dan `composer`; audit ini **tidak mengklaim hasil runtime test**. Tidak ada `php artisan test`, `route:list`, atau `migrate:fresh` yang dijalankan. Semua temuan di bawah berasal dari pembacaan file fisik dan static scan.

---

## 0. Metodologi Verifikasi

Audit ini menggunakan pendekatan berikut:

1. `git status`, `git log`, dan verifikasi commit aktif.
2. Static scan route controller references:
   - `routes/web.php`
   - `routes/api.php`
   - `app/Modules/*/routes.php`
   - `app/Plugins/*/routes.php`
3. Static scan `view('...')` / `View::make('...')` terhadap file Blade fisik.
4. Static scan migration `Schema::create()` untuk duplicate table creates dan canonical table mapping.
5. Static scan permission seed vs CRUDLFIX controller config.
6. Manual pembacaan file kritis dengan line-number evidence:
   - provider registration
   - policy mapping
   - plugin activation
   - menu renderer
   - evaluation hook
   - rapor generation
   - kurikulum migrations/controllers/views
   - presence absensi

Audit ini sengaja **tidak menyimpulkan “tests green”** atau “fitur berjalan” karena environment tidak dapat menjalankan PHP.

---

## 1. Ground Truth Snapshot

### 1.1 Git

Commit aktif:

```text
6c5d8ab chore(ci3-to-ci4-migration): remove skill migration files and scripts
```

Working tree sebelum membuat audit ini bersih kecuali file audit sebelumnya yang belum di-track:

```text
?? DEV_DOCS/082_audit_ulang_repo_bersih_setelah_pull_20260701_ARENA.md
```

### 1.2 Tool runtime

Di sandbox:

```text
php: not found
composer: not found
node: /usr/bin/node
npm: /usr/bin/npm
```

Maka hasil audit ini tidak mencakup eksekusi test PHP.

---

## 2. Static Scan Results

### 2.1 Route controller references

Hasil scan:

```text
MISSING_ROUTE_CONTROLLERS: none
```

Kesimpulan terbatas: semua controller yang direferensikan secara langsung oleh route file ada secara fisik. Ini **bukan** bukti bahwa semua route berhasil boot, karena route model binding, provider, middleware, dan dependency injection belum diuji runtime.

### 2.2 Static view references

Ditemukan 14 static view refs yang tidak ada file Blade fisiknya:

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

Catatan:

- `CrudlfixConfig.php -> academic.siswa` tampaknya contoh string dalam config/documentation code, kemungkinan false-positive.
- Sisanya berada di controller lama/core dan perlu keputusan: dibuatkan view, route dinonaktifkan, atau controller direfactor/dihapus.

---

## 3. Provider & Policy Registration — Temuan Kritis

### 3.1 `AuthServiceProvider` tidak terdaftar

File `bootstrap/providers.php` saat audit:

```php
3 return [
4     App\Providers\AppServiceProvider::class,
5     App\Providers\ModuleServiceProvider::class,
6     App\Providers\PluginRegistryServiceProvider::class,
7     Lab404\Impersonate\ImpersonateServiceProvider::class,
8 ];
```

Tidak ada:

```php
App\Providers\AuthServiceProvider::class
```

Namun file `app/Providers/AuthServiceProvider.php` berisi mapping policy penting:

```php
46 \App\Modules\Finance\Models\ItemPembayaran::class => \App\Modules\Finance\Policies\ItemPembayaranPolicy::class,
47 \App\Modules\Finance\Models\Pembayaran::class => \App\Modules\Finance\Policies\PembayaranPolicy::class,
48 \App\Modules\Finance\Models\TabunganSiswa::class => \App\Modules\Finance\Policies\TabunganPolicy::class,
49 \App\Plugins\Kurikulum\Models\Kurikulum::class => \App\Plugins\Kurikulum\Policies\KurikulumPolicy::class,
57 Gate::before(...)
```

`AppServiceProvider` memang mendaftarkan beberapa policy manual:

```php
56 Gate::policy(AuditLog::class, AuditLogPolicy::class);
57 Gate::policy(Siswa::class, SiswaPolicy::class);
58 Gate::policy(Guru::class, GuruPolicy::class);
59 Gate::policy(Kelas::class, KelasPolicy::class);
60 Gate::policy(Jadwal::class, JadwalPolicy::class);
61 Gate::policy(Attendance::class, PresensiPolicy::class);
62 Gate::policy(Permit::class, IzinPolicy::class);
63 Gate::policy(StudentSemesterScore::class, GradePolicy::class);
```

Tetapi `AppServiceProvider` **tidak** mendaftarkan policy Finance modular dan policy Kurikulum.

### Kesimpulan

**Ini bukan asumsi.** Provider aktif di `bootstrap/providers.php` tidak mencantumkan `AuthServiceProvider`, sementara policy mapping penting ada di provider tersebut. Akibat paling mungkin: `Gate::authorize()` untuk model Finance/Kurikulum dapat gagal karena policy tidak registered, kecuali Laravel auto-discovery kebetulan menangani namespace tersebut. Untuk `App\Modules\...` dan `App\Plugins\...`, auto-discovery tidak boleh diasumsikan.

**Severity:** High.

---

## 4. Plugin Activation — Bug Fisik Potensial

File: `app/Modules/Auth/Services/PluginActivationService.php`

Kode:

```php
41 foreach ($manifest->permissions() as $perm) {
42     Permission::firstOrCreate([
43         'name' => $perm['name'],
44         'guard_name' => $perm['guard_name'] ?? 'web',
45     ], [
46         'module' => $perm['module'] ?? $manifest->nama(),
47     ]);
48 }
```

Migration permission table:

```php
21 Schema::create($tableNames['permissions'], function (Blueprint $table) {
22     $table->bigIncrements('id');
23     $table->string('name');
24     $table->string('guard_name');
25     $table->timestamps();
26
27     $table->unique(['name', 'guard_name']);
28 });
```

Tidak ada kolom `module`.

### Kesimpulan

Code activation mencoba membuat `Permission` dengan atribut `module`, sementara tabel `permissions` tidak mendefinisikan kolom itu. Tanpa menjalankan runtime kita tidak bisa memastikan bagaimana Spatie model mem-filter attribute tersebut, tetapi secara codebase ini adalah mismatch nyata antara service dan migration. Risiko SQL error `Unknown column 'module'` saat activation sangat konkret.

**Severity:** High.

### Tambahan

Seeder role permission juga masih memakai:

```php
105 foreach ($permissions as $permission) {
106     Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
107 }
```

Ini lebih aman karena tidak menyertakan kolom non-existent, tetapi tetap lebih konsisten jika menggunakan `Permission::findOrCreate($permission, 'web')` sesuai API Spatie.

---

## 5. Plugin Menu Lifecycle — Belum Lengkap

Evidence:

- `menus` table punya kolom `plugin_kode`.
- `KurikulumPlugin::menu()` ada.
- `app/Plugins/Kurikulum/menu.php` ada.
- Static grep tidak menemukan pemanggilan `manifest->menu()` atau `->menu()` untuk sync ke tabel `menus` dalam activation flow.

`PluginActivationService` hanya seed permission dan update `tenant_plugins`. Tidak ada `Menu::updateOrCreate()` untuk menu plugin.

`MenuRenderer` saat audit:

- sudah filter `is_platform` untuk tenant user.
- belum filter `plugin_kode` dengan `PluginRegistry::isActiveForTenant()`.

### Kesimpulan

Plugin activation belum lengkap secara UI lifecycle:

1. Aktivasi plugin belum memastikan menu plugin masuk ke registry menu database.
2. Jika menu plugin dimasukkan secara manual/global, visibility per tenant belum dikontrol oleh `MenuRenderer` berdasarkan active tenant plugin.

**Severity:** Medium/High.

---

## 6. Evaluation ↔ Kurikulum Hook — Masih Kode Mati di Real Flow

### 6.1 Yang ada

File fisik ada:

- `app/Modules/Evaluation/Events/EvaluationResolveFramework.php`
- `app/Modules/Evaluation/Events/RaportRenderSection.php`
- `app/Modules/Evaluation/Services/EvaluationFrameworkResolver.php`
- `app/Plugins/Kurikulum/Subscribers/EvaluationFrameworkSubscriber.php`
- `app/Plugins/Kurikulum/Subscribers/RaporSectionSubscriber.php`

### 6.2 Yang belum ada di flow real

`GradeEntryController` tidak meng-import atau meng-inject `EvaluationFrameworkResolver`:

```php
16 use App\Modules\Academic\Models\Semester;
17 use App\Modules\Evaluation\Requests\BatchGradeRequest;
18 use App\Modules\Evaluation\Services\GradeCalculatorService;
...
24 protected GradeCalculatorService $calculator;
26 public function __construct(GradeCalculatorService $calculator)
27 {
28     $this->calculator = $calculator;
29 }
```

Tidak ada pemanggilan `resolve()` di method `form()`.

`RaporGeneratorService` juga tidak dispatch `RaportRenderSection`. Method `getReportData()` hanya mengumpulkan classroom, score, attendance, note, school profile, lalu return data:

```php
99 return [
100     'student' => $student,
...
104     'attendance' => [...],
...
112     'schoolProfile' => $schoolProfile,
113 ];
```

Subscriber sendiri masih punya komentar:

```php
25 // NOTE: Event RaportRenderSection belum di-dispatch oleh RaporGeneratorService
```

### Kesimpulan

Klaim “Plugin Kurikulum mengalir ke Grade Entry/Rapor” belum didukung code path real. Test yang memanggil `EvaluationFrameworkResolver` langsung hanya membuktikan resolver/subscriber dapat bekerja bila dipanggil, bukan bahwa UI/rapor memanggilnya.

**Severity:** High untuk Epic 9 integration claim.

---

## 7. Kurikulum Plugin CRUDLFIX — Schema/Controller/View Mismatch

### 7.1 `deskripsi` dipakai tetapi tidak ada di migration/model

Migration `kurikulum`:

```php
13 $table->string('kurikulum_id', 30);
14 $table->string('nama_kurikulum', 100);
15 $table->boolean('status_aktif')->default(true);
```

Model:

```php
16 protected $fillable = ['kurikulum_id', 'nama_kurikulum', 'status_aktif'];
```

Controller/view memakai `deskripsi`:

- `KurikulumController` search/rules mencantumkan `deskripsi`.
- Views `kurikulum/create/edit/index` memakai `deskripsi`.

### Kesimpulan

Field `deskripsi` akan bermasalah: tidak ada kolom DB dan tidak fillable. Ini bukan asumsi; mismatch terlihat langsung dari migration/model/controller/view.

**Severity:** High.

### 7.2 `jenis_kegiatan` mismatch

Controller `StrukturKurikulumController`:

```php
40 'jenis_kegiatan' => 'required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler',
47 'jenis_kegiatan' => 'required|in:intrakurikuler,kokurikuler_p5,ekstrakurikuler',
```

Migration `struktur_kurikulum`:

```php
18 $table->enum('jenis_kegiatan', ['intrakurikuler', 'kokurikuler', 'ekstrakurikuler'])->default('intrakurikuler');
```

Views saat audit memakai `kokurikuler_p5`.

### Kesimpulan

Submit value `kokurikuler_p5` lolos controller, tetapi tidak sesuai enum DB. Ini potensi DB error yang sangat nyata.

**Severity:** High.

### 7.3 `pendekatan_pedagogis` mismatch

Controller:

```php
34 'pendekatan_pedagogis' => 'nullable|string|max:50',
40 'pendekatan_pedagogis' => 'nullable|string|max:50',
```

Migration:

```php
17 $table->enum('pendekatan_pedagogis', ['konvensional', 'deep_learning'])->default('konvensional');
```

Views create/edit masih input text.

### Kesimpulan

User dapat memasukkan string bebas yang tidak diterima enum DB. Ini bukan overclaim; controller validasi memang tidak membatasi sesuai enum.

**Severity:** Medium/High.

---

## 8. Presence Absensi — Functionality Membaik, Authorization Lemah

`AbsensiController` sudah lebih baik secara relation dan data shape:

- memakai `with('absentable')`.
- bulk store menyimpan `type`.
- `typeMap` memetakan `ijin` → `permission`, `sakit` → `sick`.
- view fisik ada.
- test `AbsensiBulkStoreTest` ada.

Namun authorization masih lemah.

Route group:

```php
9 Route::middleware(['web', 'auth'])
```

Absensi routes:

```php
23 Route::prefix('absensi')->name('absensi.')->group(function () {
24     Route::get('/', [AbsensiController::class, 'index'])->name('index');
25     Route::get('/create', [AbsensiController::class, 'create'])->name('create');
26     Route::post('/', [AbsensiController::class, 'store'])->name('store');
27     Route::delete('/{absence}', [AbsensiController::class, 'destroy'])->name('destroy');
28 });
```

Controller config:

```php
22 'model' => Absence::class,
23 'view' => 'presence.absensi',
24 'route' => 'presence.absensi',
25 'authorize' => null,
```

Override methods `index`, `create`, `store` tidak memanggil `Gate::authorize()`.

Test membuat user tanpa role/permission:

```php
60 $this->piket = User::create([...]);
```

Lalu post absensi berhasil diharapkan:

```php
78 $response = $this->actingAs($this->piket)->post('/presence/absensi', [...]);
87 $response->assertRedirect(route('presence.absensi.index'));
```

### Kesimpulan

Saat ini absensi bulk tampaknya dapat digunakan oleh semua authenticated user dalam tenant, bukan hanya guru piket/admin/BK. Ini security gap nyata dari kombinasi route middleware, controller, dan test expectation.

**Severity:** Medium/High.

---

## 9. CRUDLFIX Permission Gaps

Static scan controller `authType => permission` vs permissions seeded menemukan prefix yang tidak ada di `RolePermissionSeeder`:

```text
MISS kelas-siswa        app/Modules/Academic/Controllers/KelasSiswaController.php
MISS orang-tua          app/Modules/Academic/Controllers/OrangTuaController.php
MISS semester           app/Modules/Academic/Controllers/SemesterController.php
MISS tahun-ajaran       app/Modules/Academic/Controllers/TahunAjaranController.php
```

Contoh `KelasSiswaController`:

```php
22 'authorize' => 'kelas-siswa',
23 'authType' => 'permission',
```

Contoh `OrangTuaController`:

```php
19 'authorize' => 'orang-tua',
20 'authType' => 'permission',
```

Seeder permission sekitar baris 79–103 tidak memuat prefix tersebut.

### Kesimpulan

Untuk user selain admin wildcard/SuperAdmin, fitur-fitur tersebut berisiko 403 karena permission granular tidak ada/ tidak diberikan. Ini gap konfigurasi RBAC nyata.

**Severity:** Medium.

---

## 10. Static View Gaps di Controller Lama/Core

Missing views yang tersisa berada terutama pada controller lama:

- Counselor achievements/counselings/violations create/edit.
- Finance core `PaymentItemController` edit dan `StudentPaymentController` show.
- Homeroom `ProjectScoreController` index/show.
- Teacher formative/summative assessment views.

### Kesimpulan

Tidak semua controller lama aman diakses. Karena repo kini memiliki CRUDLFIX dan beberapa modul modular, perlu keputusan arsitektur: apakah controller lama ini masih dipakai atau harus dimatikan/dilengkapi.

**Severity:** Medium, tergantung route exposure dan menu exposure.

---

## 11. Migration & Data Model

### 11.1 Duplicate create tables

Static scan:

```text
duplicate create tables: 0
```

### 11.2 Canonical table consolidation

Important tables:

```text
siswa: created
students: no create
kelas: created
classrooms: no create
mapel: created
subjects: no create
tahun_ajaran: created
academic_years: no create
mapel_jenis: created
kurikulum: created
menus: created
```

### 11.3 Kesimpulan

Konsolidasi model data jauh lebih baik dibanding versi lama. Namun pendekatan compatibility columns (`nama/name`, `kode/code`, dst.) masih perlu diaudit runtime karena raw query/seeder bisa bypass model `saving()` sync.

**Severity:** Low/Medium sebagai risiko jangka menengah, bukan blocker langsung.

---

## 12. Livewire / CRUDLFIX Hybrid

Repo terbaru menambahkan:

```text
composer.json: livewire/livewire ^4.3
app/Livewire/Crudlfix/*
resources/views/livewire/crudlfix/*
config/livewire.php
```

Karena PHP/composer tidak tersedia, audit tidak mengklaim Livewire berhasil boot. Perlu verifikasi lokal:

```bash
composer install
php artisan route:list
php artisan test
```

---

## 13. Prioritas Fix Berbasis Evidence

### P0 — runtime/security critical

1. **Register `AuthServiceProvider`** di `bootstrap/providers.php`.
2. **Fix `PluginActivationService` permission seed** agar tidak memakai kolom `module`.
3. **Fix Kurikulum schema/controller/view mismatch**:
   - tambah `deskripsi` ke migration/model atau hapus dari UI/controller.
   - samakan `jenis_kegiatan` (`kokurikuler` vs `kokurikuler_p5`).
   - validasi `pendekatan_pedagogis` sesuai enum dan ubah view jadi select.
4. **Hubungkan Evaluation hook**:
   - `GradeEntryController` memanggil `EvaluationFrameworkResolver`.
   - `RaporGeneratorService` dispatch `RaportRenderSection` dan render `customSections`.
5. **Tambahkan authorization Absensi bulk** minimal role/permission untuk `picket-officer`, admin, atau policy `AbsencePolicy`.

### P1 — consistency & RBAC

6. Sync menu plugin pada activation.
7. Filter menu plugin by tenant activation di `MenuRenderer`.
8. Ubah `MenuSeeder` ke `updateOrCreate()`.
9. Tambahkan permission CRUDLFIX prefix yang hilang atau ubah ke policy mode.
10. Putuskan nasib controller lama dengan missing views.

### P2 — cleanup/refactor

11. Dokumentasikan canonical columns dan deprecation plan dual-column compatibility.
12. Audit Livewire CRUDLFIX runtime.
13. Browser smoke test per role.

---

## 14. Kesimpulan Final

Repo terbaru **tidak sama dengan kondisi audit lama**: banyak perbaikan nyata sudah masuk, terutama konsolidasi tabel canonical, Academic CRUD expansion, Absensi bulk, RBAC sidebar platform flag, dan Livewire/CRUDLFIX.

Namun masih ada gap yang jelas dari codebase:

- Provider policy penting belum aktif.
- Plugin activation masih mismatch dengan migration permissions.
- Plugin menu lifecycle belum lengkap.
- Event hook Kurikulum belum dipakai real flow.
- Kurikulum CRUDLFIX mismatch dengan DB enum/columns.
- Absensi bulk belum punya authorization kuat.
- Permission CRUDLFIX beberapa prefix belum disemai.
- Beberapa controller lama masih punya missing views.

Maka status jujur: **progress besar, tetapi belum stabil untuk klaim production-ready/full implementation**.
