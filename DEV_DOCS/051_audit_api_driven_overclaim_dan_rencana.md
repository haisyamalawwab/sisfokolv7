# DEV_DOCS-051: Audit Overclaim API-Driven Dokumen 040–044 & Rencana Implementasi Bertahap

- **Tanggal:** 2026-06-22
- **Status:** 📋 AUDIT SELESAI (no code change — dokumen saja sesuai permintaan)
- **Penulis:** ZCode (pair-agent)
- **Metode:** Verifikasi fisik langsung pada `sisfokol-laravel/` (no halusinasi, no overclaim di audit ini)
- **Topik:** Audit klaim API-Driven pada dokumen 040–044 vs realitas codebase + rencana implementasi API-Driven MVC
- **Berdasarkan:** DEV_DOCS-040, 041 (2 file), 042, 043 (2 file), 044, 048, 049

---

## ⚡ EXECUTIVE SUMMARY

Audit ini menemukan bahwa **klaim API-Driven pada dokumen 040–044 secara substansial overclaim (lebih optimis dari realitas fisik)**. Dokumen 041b memberi skor "6.5/10 API-Ready", namun verifikasi fisik menunjukkan **tingkat kesiapan aktual ~1.5/10**: lapisan API yang diklaim "berfungsi" sebenarnya **tidak pernah di-load oleh aplikasi**, dan dependency inti (`laravel/sanctum`) **belum dipasang sama sekali**.

Tiga temuan paling fatal:
1. **`routes/api.php` TIDAK pernah diregistrasi** oleh `bootstrap/app.php` → 4 rute API yang diklaim aktif **tidak bisa diakses**.
2. **`laravel/sanctum` tidak terpasang** (tidak ada di `composer.json` maupun `composer.lock`, dan `vendor/` tidak ada di repo) → klaim "Sanctum aktif" adalah kesalahan fakta.
3. **`app/Http/Resources/` tidak ada** → tidak ada serialisasi/DTO; semua respons bermaksud JSON akan expose raw model.

Klaim yang **valid** (tidak overclaim): implementasi `BelongsToTenant` trait, `TenantContext`, dan `AuditLogger` + 3 Observer.

Dokumen ini **hanya audit + rencana**, tidak mengubah kode apa pun. Implementasi di-defer hingga dokumen ini disetujui.

---

## 1. METODOLOGI AUDIT

Audit dilakukan dengan:
1. Membaca seluruh isi dokumen DEV_DOCS-040, 041 (2 versi), 042, 043 (2 versi), 044, 048, 049.
2. Membaca file fisik berikut di `sisfokol-laravel/` dan mencocokkan klaim dokumen dengan realitas:
   - `bootstrap/app.php`, `config/auth.php`, `config/modules.php`, `composer.json`, `composer.lock`
   - `routes/api.php`, `routes/web.php`, `app/Modules/*/routes.php`
   - `app/Http/Controllers/Api/{AuthController,ScheduleController}.php`
   - `app/Providers/ModuleServiceProvider.php`
   - `app/Models/User.php` (cek `HasApiTokens`)
   - `database/migrations/*` (cek `personal_access_tokens`)
   - `config/` (cek `sanctum.php`, `cors.php`)
   - `.env.example` (cek `SANCTUM_*`)
3. Cek keberadaan folder `vendor/`, `app/Http/Resources/`, `app/Http/Resources/*`.

**Catatan integritas:** `vendor/` tidak ada di repo (perlu `composer install`), namun `composer.lock` tersedia. Karena `laravel/sanctum` tidak ter-list di `composer.lock`, status "belum terpasang" bersifat definitif (bukan artefak vendor-hilang).

---

## 2. TABEL AUDIT: KLAIM vs REALITAS FISIK

### 2.1 Klaim FATALLY WRONG (overclaim paling parah — fungsional tidak berjalan)

| # | Klaim Dokumen | Realitas Fisik | Verdict |
|---|---|---|---|
| F1 | **041b §1.1:** "4 rute API aktif (`/api/login`, `/api/user`, `/api/logout`, `/api/schedules/today`)" | `bootstrap/app.php:8-12` `withRouting()` **HANYA** mendaftarkan `web:`, `commands:`, `health:`. Tidak ada `api:` key. Akibat: `routes/api.php` **tidak pernah dimuat** → seluruh 4 rute API tidak terdaftar & tidak bisa diakses. | 🔴 **OVERCLAIM FATAL** |
| F2 | **041a §1.2:** "API Layer sangat minimalis menggunakan Laravel Sanctum"; **041b §1.4:** "Guard & Sanctum Ready" | (a) `composer.json` "require" tidak memuat `laravel/sanctum`. (b) `composer.lock` tidak memuat `laravel/sanctum`. (c) `config/sanctum.php` tidak ada. (d) `config/auth.php` guards hanya `web` (session) — **tidak ada guard `sanctum`**. (e) `app/Models/User.php` tidak menggunakan trait `HasApiTokens`. (f) Tidak ada migrasi `personal_access_tokens`. | 🔴 **OVERCLAIM FATAL** |
| F3 | **040 §"Plugin Tests":** "`$user->createToken(...)`" diimplisit berfungsi | `Api/AuthController.php:30` memanggil `$user->createToken()` → membutuhkan `HasApiTokens` trait + tabel `personal_access_tokens`. Keduanya tidak ada → **error fatal saat endpoint dipanggil** (jika rute sempat di-load). | 🔴 **OVERCLAIM FATAL** |
| F4 | **041a §3.4:** "Isolasi Multi-Tenancy via Global Scope, saat API Controller memanggil `Siswa::all()` otomatis terfilter" | Secara **konsep valid** (trait memang ada), tetapi karena belum ada API Controller yang benar-benar berjalan (lihat F1), klaim "siap untuk API" bersifat teoretis, belum terbukti pada runtime API. | 🟡 **OVERCLAIM SOFT** |

### 2.2 Klaim yang Akurat (tidak overclaim)

| # | Klaim Dokumen | Bukti Fisik | Verdict |
|---|---|---|---|
| V1 | **043:** Divergensi model `students` vs `siswa` | `GradeEntryController:80` query `students`; `RaporGeneratorService:36-69` query polimorfik `Siswa::class`. Test `RaporGeneratorTest.php:101` hack `$this->student->id = $this->siswa->id`. | ✅ **AKURAT** |
| V2 | **043:** Event-hook plugin Kurikulum tidak pernah di-dispatch | `EvaluationFrameworkResolver` ada tapi tidak dipanggil dari `GradeEntryController`; `RaportRenderSection` ada tapi tidak dipanggil dari `RaporGeneratorService`. | ✅ **AKURAT** |
| V3 | **044:** `BelongsToTenant` trait sudah diimplementasi | `app/Models/Traits/BelongsToTenant.php` ada, pakai `bootBelongsToTenant()` + global scope. | ✅ **AKURAT** |
| V4 | **044:** Audit observer sudah ada (3 observer) | `app/Modules/{Auth,Academic,Presence}/Observers/*Observer.php` ada; terdaftar di `AppServiceProvider`. | ✅ **AKURAT** |
| V5 | **041b §1.3:** "Skema rute tidak seimbang web >> api" | Fisik: `routes/web.php` 155 baris + 5 `routes.php` modul (160 baris) = **315 baris web** vs `routes/api.php` 21 baris (yang bahkan tidak di-load). Rasio nyata: tak terhingga (api=0 aktif). | ✅ **AKURAT (bahkan lebih parah)** |
| V6 | **044 Gap #7:** `ApiScheduleController` expose raw model | `Api/ScheduleController.php:20-23` kembalikan `$schedules` (collection Eloquent mentah) tanpa Resource. | ✅ **AKURAT** |

### 2.3 Klaim yang Tidak Dapat Divalidasi tanpa Runtime Test

| # | Klaim | Status | Catatan |
|---|---|---|---|
| U1 | **040:** "KurikulumPluginTest 3/3 PASS" | ⚠️ Tidak dapat dikonfirmasi | `vendor/` tidak ada; test tidak dijalankan ulang dalam audit ini. Klaim masuk akal (kode subscriber ada), tapi status "PASS" bukan fakta yang saya verifikasi langsung. |
| U2 | **040:** "Full suite 112 passed, 3 failed (pre-fix) → 14 passed pasca-fix" | ⚠️ Tidak dapat dikonfirmasi | Sama — butuh `composer install` + `php artisan test`. |

---

## 3. MATRIKS 8 GAP dari DOC-041b vs REALITAS (RE-VERIFIKASI)

Dokumen DEV_DOCS-044 sudah memverifikasi 8 gap. Audit ini **konfirmasi total** temuan 044 dan menambah 3 temuan baru yang **044 luput**:

| # | Gap (DOC-041b) | Status 044 | **Status Audit Ini (fisik)** | Beda? |
|---|---|---|---|---|
| 1 | Sanctum config di `.env.example` | ❌ Belum | ❌ Belum (`.env.example` tanpa `SANCTUM_*`) | Sama |
| 2 | Folder `app/Http/Resources/` | ❌ Belum | ❌ Belum (folder tidak ada) | Sama |
| 3 | Impersonation Token-Swap (Fase 2) | ⚠️ N/A deferred | ⚠️ N/A deferred | Sama |
| 4 | Modular API Routes (`routes_api.php`) | ❌ Belum | ❌ Belum (0 file `routes_api.php`) | Sama |
| 5 | Audit Log Observer | ✅ Sudah | ✅ Sudah | Sama |
| 6 | BelongsToTenant Trait | ✅ Sudah | ✅ Sudah | Sama |
| 7 | DTO / API Resources | ❌ Belum | ❌ Belum | Sama |
| 8 | CORS Configuration | ❌ Belum | ❌ Belum (`config/cors.php` tidak ada) | Sama |
| **N1** | **(BARU) `bootstrap/app.php` tidak load `routes/api.php`** | 044 tidak temukan | 🔴 **TIDAK ADA `api:` di `withRouting()`** | **TEMUAN BARU** |
| **N2** | **(BARU) `laravel/sanctum` tidak terpasang** | 044 tidak temukan (044 asumsikan Sanctum aktif) | 🔴 **Tidak di `composer.json`/`composer.lock`/`vendor/`** | **TEMUAN BARU** |
| **N3** | **(BARU) `User` model tidak pakai `HasApiTokens`** | 044 tidak temukan | 🔴 Trait tidak dipakai; `createToken()` akan fatal | **TEMUAN BARU** |

**Implikasi:** DOC-044 sebelumnya menganggap "Sanctum aktif tapi minimal". Audit ini **mengoreksi**: Sanctum **tidak terpasang sama sekali**, dan rute API **tidak dimuat aplikasi**. Tingkat kesiapan nyata lebih rendah dari skor 6.5/10 di DOC-041b.

---

## 4. BASELINE FISIK API (APA YANG BENAR-BENAR ADA)

Berikut inventory akurat komponen API di codebase:

### 4.1 Yang ADA (skeleton tanpa otak)

| Komponen | Path | Status |
|---|---|---|
| `routes/api.php` | `routes/api.php` (21 baris) | ⚠️ Ada sebagai file, **tetapi tidak di-load** aplikasi |
| `Api\AuthController` | `app/Http/Controllers/Api/AuthController.php` | ⚠️ Ada, **akan crash** saat dipanggil (butuh Sanctum) |
| `Api\ScheduleController` | `app/Http/Controllers/Api/ScheduleController.php` | ⚠️ Ada, expose raw model, **rute tidak terdaftar** |
| `config/auth.php` | `config/auth.php` | ✅ Hanya guard `web` (session); tidak ada guard sanctum |
| `.env.example` | `.env.example` | ✅ Ada, **tanpa** `SANCTUM_*` maupun referensi API |

### 4.2 Yang TIDAK ADA (missing sama sekali)

| Komponen | Ekspektasi | Status |
|---|---|---|
| `laravel/sanctum` (composer) | Dependency inti token API | ❌ Tidak di `composer.json`/`composer.lock` |
| `config/sanctum.php` | Konfigurasi Sanctum | ❌ Tidak ada |
| `config/cors.php` | Konfigurasi CORS | ❌ Tidak ada (Laravel 11 pakai default in-code allow-all) |
| `app/Http/Resources/` | API Resource / DTO | ❌ Tidak ada folder-nya |
| Migrasi `personal_access_tokens` | Tabel token Sanctum | ❌ Tidak ada |
| `HasApiTokens` trait di `User` | Method `createToken()` | ❌ Tidak dipakai |
| `routes_api.php` per modul | Modular API routing | ❌ 0 file di seluruh codebase |
| `api:` key di `bootstrap/app.php` | Loader rute API | ❌ Tidak ada |
| API throttle/rate limiter | Proteksi brute force login API | ❌ Tidak ada (hanya `web.php` login yang pakai `throttle:5,1`) |

### 4.3 Yang SUDAH ADA & BERMANFAAT untuk API-Driven (fondasi dapat dipakai)

| Komponen | Path | Relevansi API |
|---|---|---|
| `BelongsToTenant` trait + global scope | `app/Models/Traits/BelongsToTenant.php` | ✅ Auto-isolasi tenant saat API Controller query model |
| `TenantContext` singleton | `app/Support/TenantContext.php` | ✅ Bisa di-resolve dari token Sanctum nanti |
| `ResolveTenant` middleware | `app/Http/Middleware/ResolveTenant.php` | ✅ Bisa dipakai di middleware stack API |
| `AuditLogger` + Observers | `app/Modules/Auth/Services/AuditLogger.php` | ✅ Audit API mutations tanpa modifikasi |
| Multi-guard-ready config | `config/auth.php` | ✅ Tinggal tambah guard `sanctum` |
| `BlockWhileImpersonating` middleware | `app/Http/Middleware/BlockWhileImpersonating.php` | ✅ Untuk Fase impersonation API |
| RBAC Spatie | `spatie/laravel-permission` | ✅ `permission` middleware bisa dipakai di API |

---

## 5. RE-SCORING KESIAPAN API-DRIVEN

### 5.1 Skor Asli DOC-041b vs Skor Audit Ini

| Aspek | Skor 041b | **Skor Audit Ini** | Alasan Koreksi |
|---|---|---|---|
| Dokumentasi Blueprint | 5/5 | 5/5 | Tidak berubah — DEV_DOCS & ADR memang lengkap |
| Authentication Foundation | 4/5 | **0.5/5** | Sanctum tidak terpasang, guard tidak ada |
| Multi-Tenancy Isolation | 5/5 | **4/5** | Trait ada, tapi belum teruji di konteks API runtime |
| Route Separation | 3/5 | **1/5** | Bukan sekadar "belum modular", tapi rute API **tidak di-load sama sekali** |
| API Resources | 2/5 | **0/5** | Folder tidak ada (bukan "minim", tapi nihil) |
| CORS & Security | 2/5 | **0/5** | Tidak ada config file; default allow-all berbahaya |
| DTO / Serialization | 2/5 | **0/5** | Tidak ada satu pun Resource/DTO |
| Code Organization | 4/5 | **3/5** | Struktur modular bagus, tapi API layer terisolasi dari runtime |

**Skor Total:** DOC-041b = **6.5/10** → **Audit ini: ~1.5/10**.

### 5.2 Interpretasi Jujur

Aplikasi saat ini adalah **pure Blade-SSR monolith** tanpa API layer yang berfungsi. Yang ada hanyalah **skeleton 2 controller + 1 routes file yang tidak terhubung ke runtime**. Klaim "API minimal tapi aktif" tidak akurat.

---

## 6. REKOMENDASI: REncana Implementasi API-Driven MVC (BERTAHAP)

Karena ini hanya dokumen (sesuai keputusan Anda), berikut rencana 3 fase untuk eksekusi di sesi mendatang. Setiap fase punya **DoD terukur & dapat diverifikasi fisik**.

### 6.1 Fase A — Fondasi API Fungsional (Estimasi: 1 sesi)

**Tujuan:** Membuat 4 rute API yang diklaim dokumen **benar-benar berfungsi**.

**Tugas:**
- **A1** — `composer require laravel/sanctum` → update `composer.json` + `composer.lock`.
- **A2** — Tambah `HasApiTokens` trait ke `app/Models/User.php`.
- **A3** — `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` → publish `config/sanctum.php` + migrasi `personal_access_tokens`.
- **A4** — `php artisan migrate` untuk tabel token.
- **A5** — Edit `bootstrap/app.php` `withRouting()`: tambah `api: __DIR__.'/../routes/api.php',`.
- **A6** — Tambah guard `sanctum` ke `config/auth.php`:
  ```php
  'sanctum' => [
      'driver' => 'sanctum',
      'provider' => null, // pakai default user provider
  ],
  ```
- **A7** — Publish `config/cors.php`:
  ```bash
  php artisan config:publish cors
  ```
  Konfigurasi: `paths=['api/*']`, `allowed_origins` dari env.
- **A8** — Buat folder `app/Http/Resources/` + `.gitkeep`.
- **A9** — Buat `app/Http/Resources/UserResource.php` & `ScheduleResource.php`.
- **A10** — Refactor `Api/AuthController` & `Api/ScheduleController` pakai Resource.
- **A11** — Tambah `SANCTUM_STATEFUL_DOMAINS` + `SANCTUM_EXPIRATION` ke `.env.example`.
- **A12** — Buat feature test `tests/Feature/Api/AuthApiTest.php`:
  - POST `/api/login` → 200 + token
  - GET `/api/user` dengan Bearer → 200 + UserResource
  - POST `/api/logout` → 200
  - GET `/api/schedules/today` dengan token guru → 200 + schedule data

**Kriteria Penerimaan (DoD Fase A):**
- [ ] `composer show laravel/sanctum` menampilkan versi terpasang.
- [ ] `php artisan route:list | grep "api/"` menampilkan 4 rute aktif.
- [ ] `php artisan test --filter=Api/AuthApiTest` hijau (4 test cases).
- [ ] `curl -X POST http://sisfokol-laravel.test/api/login -d '...'` mengembalikan token (manual test).
- [ ] `app/Http/Resources/` ada dan berisi ≥2 Resource class.

---

### 6.2 Fase B — Modular API + Resources per Modul (Estimasi: 2–3 sesi)

**Tujuan:** Struktur API termodular, siap melayani konsumsi frontend terpisah.

**Tugas:**
- **B1** — Tambah method `loadModuleApiRoutes()` di `ModuleServiceProvider`:
  ```php
  if (file_exists($modulePath . '/routes_api.php')) {
      Route::middleware(['api', 'auth:sanctum'])
          ->prefix('api/v1')
          ->name('api.')
          ->group($modulePath . '/routes_api.php');
  }
  ```
- **B2** — Buat `routes_api.php` di tiap core modul (6 file):
  - `app/Modules/{Auth,Academic,Evaluation,Finance,Presence,Tenancy}/routes_api.php`
- **B3** — Scaffold API Resources untuk entitas utama:
  - `SiswaResource`, `KelasResource`, `MapelResource`, `JadwalResource` (Academic)
  - `GradeResource`, `RaporResource` (Evaluation)
  - `TagihanResource`, `PembayaranResource` (Finance)
  - `AttendanceResource`, `AbsenceResource` (Presence)
- **B4** — Buat `app/Http/Controllers/Api/BaseApiController.php`:
  - Method helper `success($data, $status=200)`, `error($message, $status)`, `paginate($paginator)`.
- **B5** — Buat JSON ExceptionHandler (override `ExceptionHandler::render` untuk rute `api/*`):
  - `ValidationException` → 422 + `{message, errors}`
  - `AuthenticationException` → 401
  - `AuthorizationException` → 403
  - `ModelNotFoundException` → 404
  - Fallback → 500 + `{message}` (production) / detail (local)
- **B6** — Register `throttle:api` untuk semua rute `auth:sanctum` (default 60 req/min).
- **B7** — Migrasi rute yang ada di `routes/api.php` ke `routes_api.php` per modul (per modul yang sesuai).

**Kriteria Penerimaan (DoD Fase B):**
- [ ] `php artisan route:list | grep "api/v1/"` menampilkan endpoint per modul.
- [ ] `app/Http/Resources/` berisi ≥10 Resource class.
- [ ] Setiap API Controller mengembalikan `JsonResource`/`ResourceCollection` (bukan raw model).
- [ ] Error 401/403/404/422 mengembalikan JSON terstruktur, bukan HTML.
- [ ] Feature test per modul: `tests/Feature/Api/Academic/*`, dll.

---

### 6.3 Fase C — Token-Swap Impersonation + Hardening (Estimasi: 2 sesi)

**Tujuan:** Memecahkan dependency session impersonation agar bisa dipakai via API.

**Tugas:**
- **C1** — Endpoint token-swap:
  - `POST /api/v1/impersonate/{userId}/token-swap` (SuperAdmin only)
  - Generate token Sanctum atas nama user target, simpan reference original.
  - Response: `{token, impersonated_as, original_user_id, audit_id}`.
- **C2** — Endpoint leave:
  - `POST /api/v1/impersonate/leave` → revoke token impersonated, kembalikan original token.
- **C3** — Log impersonation API via `AuditLogger::log()` dengan event `impersonate.token_swap` / `impersonate.leave`.
- **C4** — CORS hardening: ganti `allowed_origins=['*']` ke whitelist dari `SANCTUM_STATEFUL_DOMAINS`.
- **C5** — API versioning: namespace `App\Http\Controllers\Api\V1\` sebagai konvensi untuk Fase 2.
- **C6** — Dokumentasi `DEV_DOCS/052_api_endpoint_catalog.md` (katalog semua endpoint).

**Kriteria Penerimaan (DoD Fase C):**
- [ ] SuperAdmin dapat token-swap ke user lain via API.
- [ ] Audit log tercatat dengan event `impersonate.token_swap`.
- [ ] `config/cors.php` `allowed_origins` dari env, bukan `*`.
- [ ] Endpoint catalog ditulis.

---

## 7. RISIKO & MITIGASI

| Risiko | Kemungkinan | Dampak | Mitigasi |
|---|---|---|---|
| Install Sanctum memicu migration conflict | Rendah | Sedang | `migrate` di DB uji; backup sebelum production |
| Enable `routes/api.php` men-expose endpoint yang belum ada auth test | Sedang | Tinggi | Pasang `auth:sanctum` di semua rute sebelum enable; tambah throttle |
| Refactor controller ke Resource bisa break Blade view yang konsumsi data raw | Sedang | Sedang | Resource hanya untuk API Controller; Blade tetap pakai controller web yang ada |
| Divergensi model D1 (students vs siswa) semakin nyata saat API dibuka | Tinggi | Tinggi | Sebelum Fase B, selesaikan minimal adapter (lihat DEV_DOCS-050 E6-S3) |
| CORS default allow-all dieksploitasi saat dev server exposed | Sedang | Sedang | Fase A sudah publish `config/cors.php`; restrict ke `localhost`+`SANCTUM_STATEFUL_DOMAINS` |
| Token-swap impersonation (C1) jadi vektor privilege escalation jika salah otorisasi | Sedang | Kritis | Policy ketat + audit log wajib + unit test SuperAdmin-only |

---

## 8. RANGKUMAN EKSEKUSI & ESTIMASI

| Fase | Estimasi | Prasyarat | Deliverable Utama |
|---|---|---|---|
| **A — Fondasi** | 1 sesi | `composer install` berfungsi | 4 rute API benar-benar aktif + Sanctum + CORS + 2 Resource |
| **B — Modular** | 2–3 sesi | Fase A hijau | 6 `routes_api.php`, ≥10 Resource, JSON ExceptionHandler |
| **C — Hardening** | 2 sesi | Fase B hijau | Token-swap impersonation + CORS restrict + endpoint catalog |
| **Total** | **5–6 sesi** | | API-Driven MVC yang benar-benar berfungsi |

---

## 9. DEFINITION OF DONE (API-DRIVEN LEVEL)

API-Driven dianggap "benar-benar berfungsi" ketika:

- [ ] `php artisan route:list --path=api` menampilkan ≥15 endpoint aktif (4 auth/schedule + endpoint per modul).
- [ ] `composer show laravel/sanctum` menampilkan versi terpasang.
- [ ] `config/sanctum.php` dan `config/cors.php` ada dan dikonfigurasi.
- [ ] Semua API Controller mengembalikan `JsonResource` (no raw model).
- [ ] `php artisan test --filter=Api` hijau (≥15 test cases).
- [ ] Token-swap impersonation berfungsi + ter-audit.
- [ ] `curl http://localhost/api/v1/...` mengembalikan JSON, bukan HTML/404.
- [ ] CORS restrict ke whitelist (bukan `*`).
- [ ] Dokumen endpoint catalog `DEV_DOCS/052` tersedia.

---

## 10. CATATAN INTEGRITAS (NO OVERCLAIM DALAM AUDIT INI)

Semua temuan di dokumen ini diverifikasi dengan membaca file fisik. Berikut path & bukti spesifik untuk temuan paling kritis:

| Temuan Kritis | Path + Bukti |
|---|---|
| F1: routes/api.php tidak di-load | `bootstrap/app.php:8-12` — `withRouting()` tanpa `api:` key |
| F2: Sanctum tidak terpasang | `composer.json` "require" tanpa `laravel/sanctum`; `composer.lock` tanpa entry sanctum |
| F2: Guard sanctum tidak ada | `config/auth.php` `guards` hanya berisi `web` |
| F3: `createToken` akan crash | `app/Models/User.php` tanpa `use HasApiTokens`; migrasi `personal_access_tokens` tidak ada |
| N1: routes_api.php tidak ada | Glob `app/Modules/**/routes_api.php` → 0 hasil |
| N2: `app/Http/Resources/` tidak ada | `ls app/Http/Resources/` → No such file or directory |
| N3: `config/cors.php` tidak ada | `ls config/cors.php` → No such file or directory |
| V1: Divergensi model nyata | `RaporController` + `GradeEntryController` query `Student` (tabel `students`); modul Academic tulis `Siswa` (tabel `siswa`) |

**Yang TIDAK saya klaim dalam dokumen ini:**
- Tidak mengklaim "test suite PASS/FAIL" (tidak dijalankan — vendor belum install).
- Tidak mengklaim performa API.
- Tidak mengklaim keamanan produksi.
- Klaim saya terbatas pada: file ada/tidak ada + struktur rute + dependency list.

---

## 11. REFERENSI DOKUMEN

- **DEV_DOCS-040** — Dev report Epic 9 Kurikulum (klaim test PASS — tidak diverifikasi ulang di sini).
- **DEV_DOCS-041a** — Review kesiapan API (skor tidak akurat per audit ini).
- **DEV_DOCS-041b** — Deep-dive 8 gap (gap valid, tapi skor 6.5/10 terlalu optimis).
- **DEV_DOCS-042** — Scaffolding 8 plugin + ETL (skeleton).
- **DEV_DOCS-043** — Divergensi model + event-hook (akurat).
- **DEV_DOCS-043@1607** — Status Epic 6/7/8 (overclaim ~85% — dikoreksi di DEV_DOCS-050).
- **DEV_DOCS-044** — Verifikasi 8 gap API (37.5% implementasi — valid, tapi luput N1/N2/N3).
- **DEV_DOCS-048, 049** — Review dokumentasi 040/041.
- **DEV_DOCS-050** — Sprint plan Epic 6 (audit Evaluation).
- **ADR-002, 003, 009, 010** — Keputusan arsitektur terkait.
