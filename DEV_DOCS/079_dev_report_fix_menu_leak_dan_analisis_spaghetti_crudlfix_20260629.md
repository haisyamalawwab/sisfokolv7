# Dev Report: Fix Menu Leak (is_platform) + Analisis Spaghetti Crudlfix (terkoreksi)

**Tanggal:** 29 Juni 2026
**Oleh:** ZCode (sesi fix menu leak + analisis spaghetti + browser test)
**Branch:** `main`
**Status:** Fix COMPLIANT [CMD-VERIFY]; analisis spaghetti TERKOREKSI (ada kesalahan awal)

---

## ⚠️ Catatan untuk agent lain — BACA DULU SEBELUM ACT

Dokumen ini memuat **dua hal**, salah satunya berisi **koreksi kesalahan analisis sendiri**:

1. **Fix menu leak `admin.sekolah`** — work nyata, terverifikasi 3 lapis (test + DB + browser). Aman dipakai.
2. **Analisis spaghetti Crudlfix** — versi awal saya **salah menyimpulkan** layer Livewire = dead code. Sudah dikoreksi di §3. **JANGAN hapus layer Livewire Crudlfix** berdasarkan dokumen ini — layer itu AKTIF dipakai 11 view.

---

## 1. Fix: Kebocoran Menu Platform ke Tenant Admin (`admin.sekolah`)

### 1.1 Masalah (dari Dev Report 076)

`admin.sekolah` (Tenant Admin, role `admin` = wildcard `*`) melihat menu platform global
(`Tenants`, `Branches`, `RBAC Builder`, `Audit Log`, `Plugin`) padahal seharusnya SuperAdmin-only.
Akar masalah: `MenuRenderer::forUser()` menyertakan menu `tenant_id=NULL` untuk semua user
non-superadmin via `whereNull('tenant_id')`, LALU filter permission gagal memblokir karena
wildcard `*` membuat `$user->can('tenant.view'|'rbac.manage'|...)` selalu `true`.

### 1.2 Perbaikan (Rekomendasi #2 laporan audit — database-driven, sesuai ADR-010)

| File | Perubahan |
|------|-----------|
| `app/Modules/Auth/Database/Migrations/2026_06_28_000000_add_is_platform_to_menus_table.php` | **NEW** — kolom boolean `is_platform` + backfill idempoten 5 menu platform |
| `app/Modules/Auth/Models/Menu.php` | `is_platform` ditambah ke `$fillable` & `casts()` |
| `database/seeders/MenuSeeder.php` | 5 menu platform ditandai `is_platform => true` |
| `app/Support/MenuRenderer.php` | query `where('is_platform', false)` untuk user non-superadmin **sebelum** filter permission; ganti blacklist permission hardcode jadi filter `is_platform` bersih |
| `tests/Feature/Rbac/MenuRendererTest.php` | 2 test baru: tenant-admin-wildcard + superadmin-still-sees |

5 menu yang di-flag: `tenancy.tenants`, `tenancy.branches`, `auth.rbac`, `auth.audit`, `auth.plugins`.
`auth.users` (Pengguna) sengaja **TIDAK** di-flag — tetap terlihat admin.sekolah sesuai ekspektasi audit.

### 1.3 Verifikasi — 3 lapis [CMD-VERIFY]

**Lapis 1 — Feature test:**
```
$ php artisan test --filter="Rbac"
Tests: 33 passed (60 assertions)   Duration: 107.73s
```
Termasuk test baru `test_tenant_admin_with_wildcard_does_not_see_platform_menus` ✓ dan
`test_superadmin_still_sees_platform_menus` ✓ (no regression).

**Lapis 2 — DB live check:**
```
dashboard => tenant          tenancy.tenants  => PLATFORM
auth.users  => tenant        tenancy.branches => PLATFORM
                            auth.rbac  => PLATFORM
                            auth.audit => PLATFORM
                            auth.plugins => PLATFORM
col exists: YES | platform menus: 5
```

**Lapis 3 — Browser/HTTP test (live server localhost:8000):**

Login `admin.sekolah`/`demo1234` → GET `/dashboard` (final `/admin/dashboard`, HTTP 200, 28KB):
```
Menu PLATFORM (HARUS ABSEN):  Tenants:0  Branches:0  RBAC Builder:0  Audit Log:0  Plugin:0
Menu TENANT (HARUS TAMPIL):   Dashboard:4  Pengguna:2  Siswa:5  Guru:3
Verdict: ✅ COMPLIANT — tidak ada menu platform bocor ke admin.sekolah
```
Kontras login `superadmin`/`SuperAdmin#2026` → GET `/dashboard` (HTTP 200, 32KB):
```
Menu PLATFORM pada SuperAdmin:  Tenants:2  Branches:2  RBAC Builder:2  Audit Log:2  Plugin:2
Verdict: ✅ SuperAdmin tetap melihat menu platform (tidak over-restrict)
```

**Kapabilitas browser test di sesi ini:** HTTP integration via `curl` melawan server live
(route→middleware→Auth→session→MenuRenderer→Blade, full stack nyata). Bukan headed-browser
(Playwright/Puppeteer) — tidak bisa render JS/klik/screenshot. Dusk belum terinstall.
Cukup untuk kasus ini karena kebocoran murni server-side rendering.

---

## 2. Analisis Spaghetti Crudlfix — VER SI AWAL SALAH, INI KOREKSI

### 2.1 ❌ Kesalahan versi awal (HARUS DIABAIKAN)

Saya awalnya menyimpulkan: *"Layer Livewire Crudlfix adalah dead code ~1306 baris"* dengan
bukti grep `<livewire:crudlfix` dan `Livewire::component('crudlfix...` kosong.

**Kesimpulan itu SALAH.** Penyebab: saya grep pola **tag syntax** `<livewire:>` padahal
codebase pakai **blade directive syntax** `@livewire('crudlfix.crudlfix-page')`.
Keduanya valid di Laravel Livewire, tapi grep saya hanya cari satu.

Verifikasi ulang dengan pola benar:
```
$ grep -rln "@livewire(" resources/views/ app/Plugins/
resources/views/academic/guru/index.blade.php
resources/views/academic/jadwal/index.blade.php
resources/views/academic/kelas/index.blade.php
resources/views/academic/kelas-siswa/index.blade.php
resources/views/academic/mapel/index.blade.php
resources/views/academic/mapel-jenis/index.blade.php
resources/views/academic/orang-tua/index.blade.php
resources/views/academic/semester/index.blade.php
resources/views/academic/siswa/index.blade.php
resources/views/academic/tahun-ajaran/index.blade.php
resources/views/finance/tabungan/index.blade.php
```
**Layer Livewire Crudlfix AKTIF dipakai 11 view.** Bukan dead code. JANGAN dihapus.
Klaim migrasi di Dev Report 075/076 [CMD-VERIFY via grep pola-benar] — konsisten dengan codebase.

### 2.2 ✅ Temuan spaghetti yang TERVERIFIKASI real [CMD-VERIFY]

Setelah koreksi, sisa temuan yang benar:

#### 🔴 HIGH — Orphan controller + class collision risk (2 file)
```
app/Modules/Academic/Controllers/SiswaControllerCrudlfix.php          → class SiswaController
app/Modules/Academic/Controllers/SiswaController.php                  → class SiswaController  ⚠ nama sama, 1 namespace
app/Modules/Finance/Controllers/ItemPembayaranControllerCrudlfix.php  → class ItemPembayaranController
app/Modules/Finance/Controllers/ItemPembayaranController.php          → class ItemPembayaranController  ⚠ nama sama
```
Bukti:
```
$ grep -rn "SiswaControllerCrudlfix|ItemPembayaranControllerCrudlfix" app/ routes/ tests/ database/
(empty — tidak ada referensi FQCN, confirmed orphaned)
$ grep -h "^class " ...SiswaController.php ...SiswaControllerCrudlfix.php
class SiswaController extends Controller   (dua kali — collision)
```
Dibuat di commit `3667cd8`, tidak ter-wire ke route. Risiko: PSR-4 dua file definisi class
identik → collision saat `composer dump-autoload --optimize` (classmap bisa ambil file salah).
**Sudah diakui** doc 075 §"Varian `*Crudlfix.php`" & doc 076 Known Limitation #4 — dijadwalkan
cleanup Task 11, **belum dieksekusi**. Status: `[BACA-DOK terjadwal] [CMD-VERIFY masih ada]`.

#### 🟡 MEDIUM — Double-query pada initial load (known limitation, not yet fixed)
HTTP `Crudlfix` trait `index()` masih `paginate()` (line 165) LALU Livewire table jalankan
query sendiri. Bukan bug, hanya query berlebih.
```
$ grep -n "paginate" app/Support/Crudlfix/Crudlfix.php
165: $paginator = $query->paginate($cfg->perPage ?? 15)->withQueryString();
```
**Sudah diakui** doc 076 Known Limitation #1 — optimasi future. Status: belum dieksekusi.

#### 🟢 LOW — Smell pada kode aktif (perlu diwaspadai, bukan mendesak)
1. `CrudlfixConfig::nameColumn()` (line 163) — `$this->model::first()?->getAttributes()`
   query DB setiap render flash message hanya untuk menebak kolom nama. Sebaiknya properti
   config eksplisit / cache.
2. Manual FormRequest resolution `Crudlfix.php:345-353` — `setRedirector`/`validateResolved`
   bypass flow normal Laravel. Berfungsi (ada test), fragile vs upgrade framework.

### 2.3 Catatan: duplikasi logika HTTP vs Livewire = by design, bukan spaghetti
`Crudlfix` (HTTP trait) dan trait Livewire (`HasCrudlfixTable`/`Form`/`Auth`) menduplikasi
logika search/filter/auth. Tapi ini **arsitektur hybrid by design** (HTTP fallback +
Livewire interactive), diakui doc 076. Bukan anti-pattern — hanya maintenance burden yang
harus dijaga sinkron. Tidak masuk rekomendasi pembersihan.

---

## 3. Rekomendasi pembersihan (low-risk, berurutan)

1. **Hapus 2 orphan `*Crudlfix.php`** — verifikasi no ref (sudah dipastikan kosong), hapus,
   `composer dump-autoload`. Eliminasi class-collision risk. (Sudah dijadwalkan Task 11.)
2. **Optimasi double-query** — override `index()` di migrated controller skip pagination saat
   Livewire. (Future, doc 076.)
3. (Opsional LOW) Refactor `nameColumn()` jadi properti config, bukan query runtime.

**TIDAK dianjurkan:** hapus layer Livewire Crudlfix (AKTIF, 11 view pakai).

---

## 4. Referensi silang

- Dev Report 075 (inventory migrasi Livewire) — `075_inventaris_rencana_migrasi_livewire_crudlfix_20260628.md`
- Dev Report 076 (eksekusi migrasi + Known Limitations) — `076_dev_report_bulk_migrasi_livewire_crudlfix_20260629.md`
- Dev Report 076 sidebar audit — `076_dev_report_sidebar_rbac_tenant_compliance_20260628.md` (§6 fix is_platform)
- Konvensi anti-overclaim — `077_konvensi_verifikasi_agentic_20260628.md`

---

*Generated: 29 Juni 2026. Analisis spaghetti dikoreksi setelah verifikasi ulang —
versi awal keliru menyimpulkan layer Livewire = dead code karena grep pola syntax salah.*
