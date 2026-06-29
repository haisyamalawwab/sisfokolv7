# Dev Report: Bulk Migrasi Livewire Crudlfix (Phase A–E)

**Tanggal:** 29 Juni 2026
**Branch:** `main`
**Task:** Task 10b–10e (Phase A–E bulk migration)
**Status:** ✅ Selesai & terverifikasi
**Test:** 158 passed / 401 assertions (full suite green)

---

## 1. Ringkasan

Ekesekusi penuh rencana migrasi bulk dari `DEV_DOCS/075_inventaris_rencana_migrasi_livewire_crudlfix_20260628.md`.
Dari 12 controller Crudlfix (ground truth, bukan klaim "18" di plan asli):

- **9 controller** dimigrate full `CrudlfixPage` (index + create + edit inline via Livewire)
- **2 controller** dimigrate index-only (Livewire table + traditional create/edit)
- **1 controller** dibiarkan traditional (Absensi — terlalu custom untuk generic table)
- **1 security gap ditutup:** AJAX Livewire sekarang enforce policy/permission auth
- **0 regresi** — full test suite green

---

## 2. Yang Dikerjakan

### Phase A — Fondasi komponen (prasyarat)

**A.1 — `HasCrudlfixAuth` trait (new)**
`app/Livewire/Crudlfix/Traits/HasCrudlfixAuth.php`

Mirror backend `Crudlfix::authorizeCrudlfix()`:
- `'policy'` mode → `Gate::authorize('ability', $model)`
- `'permission'` mode → `Gate::authorize('permission.key')` + ADR-006 team context
- `null` → no in-component auth (rely on route middleware)

Ditambahkan ke `CrudlfixTable` + `CrudlfixForm`. Dipanggil di:
- Table `render()` → `viewAny`
- Form `save()` → `create` / `update`
- `executeDelete()` → `delete`

> **Gap keamanan ditutup:** sebelumnya, operasi AJAX Livewire (save/delete/search)
> bypass auth controller — user tanpa permission bisa save/delete via Livewire
> selama bisa load index page. Sekarang setiap AJAX operation enforce auth.

**A.2 — Controller-resolution architecture**
`CrudlfixForm` + `CrudlfixPage` terima param `controller` (FQCN string, Livewire-safe).

Saat `controller` set, komponen resolve rules + auth + search + with dari
`app($controller)->getCrudlfixConfig()` **tiap render** (AJAX re-hydrasi).

Mengapa ini penting:
- **Solve `Rule::unique` (closure/Rule object)** — Siswa & Guru pakai
  `Rule::unique('siswa')->where('tenant_id', X)` yang tidak bisa di-serialize
  sebagai Livewire public property. Dengan controller-resolution, rules di-build
  fresh tiap request dan langsung dipakai `Validator::make()`. Tidak ada serialisasi.
- **Eliminasi duplikasi** — rules/search/with/auth tidak perlu ditulis ulang di view.
  View hanya pass `controller` + `columns` + `formFields` (layer tampilan).

**A.3 — `resolveRules()` di `HasCrudlfixForm`**
`app/Livewire/Crudlfix/Traits/HasCrudlfixForm.php`

Handle dua format rules:
- Flat array (sama untuk create+edit) → pakai langsung
- Nested `['store' => [...], 'update' => [...]]` → pick by mode

Plus `{{id}}` placeholder replacement untuk unique-on-update
(e.g. `unique:guru,nip,{{id}},id,tenant_id,X` → `{{id}}` jadi editId).

Dipakai oleh `updated()` (real-time validation) dan `saveForm()`.

**A.4 — Inline edit (no URL navigation)**
`CrudlfixTable::editRecord(int $id)` dispatch event `crudlfix-edit` →
`CrudlfixPage::handleEditRequest()` → `setMode('edit', $id)`.

Sebelumnya edit di table pakai `<a href="route.edit">` → 404 untuk controller
tanpa `edit.blade.php` (MapelJenis, TahunAjaran, dll). Sekarang inline Livewire.

Flag `inlineEdit` (default `true`) + `showEdit` (default `true`) untuk mode
index-only (Tier 3) di mana edit tetap URL ke traditional view.

**A.5 — Initial search dari URL**
`CrudlfixTable::mount()` set `$searchQuery = request('search', '')` agar deep-link
`?search=X` tetap filter pada load pertama (sebelumnya table mulai dengan search
kosong → regression vs traditional view yang pakai paginator controller).

**A.6 — Switch pilot ke production**
- `academic/kelas/index.blade.php` → Livewire (param `controller`)
- Route `kelas-livewire` (test) dihapus dari `app/Modules/Academic/routes.php`
- File `academic/kelas/index-livewire.blade.php` dihapus

### Phase B — Tier 1: full CrudlfixPage (4 controller)

| Controller | Aksi | Catatan |
|-----------|------|---------|
| MapelJenis | buat view baru | Tidak ada view sama sekali sebelumnya → Livewire jadi view pertama |
| TahunAjaran | buat view baru | Hook `beforeStore` (aktif default) redundant — checkbox Livewire setara |
| OrangTua | buat view baru | Password field nullable; store+update rules berbeda (unique username) |
| Mapel | ganti view existing | viewData `jenisList` untuk select; `with: jenis` |

Pola view (sama untuk semua Tier 1 & 2):
```blade
@livewire('crudlfix.crudlfix-page', [
    'controller' => \App\Modules\...\XxxController::class,
    'columns' => [...],
    'formFields' => [...],
])
```

### Phase C — Tier 2: full CrudlfixPage (4 controller)

| Controller | Aksi | Catatan |
|-----------|------|---------|
| Semester | buat view baru | viewData `tahunAjarans`; field `nama` = select 1/2 |
| KelasSiswa | buat view baru | 3 viewData lists; hanya `rules.store` (no update) — `resolveRules` fallback ke store |
| Siswa | ganti view existing | **policy auth** via controller; `Rule::unique` (closure) — solve dengan controller-resolution |
| Guru | ganti view existing | **policy auth** via controller; tenant-scoped unique `{{id}}` — solve dengan `resolveRules` |

### Phase D — Tier 3: index-only Livewire table (2+1 controller)

Pendekatan: Livewire table untuk search/sort/pagination (UX win),
create/edit/show tetap traditional (logika kompleks utuh).

| Controller | Aksi | Alasan keep traditional |
|-----------|------|------------------------|
| Jadwal | index → Livewire table | create/edit pakai Alpine cascade-select + controller hooks (conflict checker) |
| TabunganSiswa | index → Livewire table (`showEdit=false`) | create pakai `TabunganMutasiService`; show handles setor/tarik |
| Absensi | **dibiarkan traditional** | index punya custom kelas/date filter (subquery `KelasSiswa`); bulk-create per-kelas — tidak cocok generic table |

Index-only view pakai `crudlfix-table` langsung + `inlineEdit: false`:
```blade
@livewire('crudlfix.crudlfix-table', [
    'model' => $config->model, 'route' => $config->route,
    'with' => $config->with ?? [], 'search' => $config->search ?? [],
    'columns' => [...],
    'inlineEdit' => false, 'showEdit' => true,
])
```

### Phase E — Verifikasi

| Check | Hasil |
|-------|-------|
| `php -l` (6 file PHP) | ✅ semua OK |
| `php artisan view:clear` | ✅ OK |
| `php artisan route:list` | ✅ semua route terdaftar; `kelas-livewire` dihapus |
| `php artisan test` (full suite) | ✅ **158 passed / 401 assertions** |

Test relevan yang pass:
- `CrudlfixRbacTest` (20 tests): search+policy auth, tenant isolation, gate authorization
- `ScheduleTest`: admin can create schedule (Jadwal create flow utuh)
- `RbacBuilderTest`, `MenuRendererTest`, dll: no regression

---

## 3. Perubahan Teknis Kunci

### Architecture: controller-resolution vs flat-array

| Aspek | Flat-array (pilot lama) | Controller-resolution (baru) |
|-------|------------------------|------------------------------|
| Rules | pass dari view (string only) | resolve dari controller (Rule objects OK) |
| Auth | hardcode di view (sering salah) | dari controller `authType` |
| Duplikasi | rules/search/with diduplikasi view | single source of truth |
| `Rule::unique` | ❌ tidak bisa (closure) | ✅ fresh tiap request |
| Backward compat | — | ✅ flat-array masih didukung (controller=null) |

> Keputusan: controller-resolution untuk Tier 1 & 2 (full page).
> Flat-array hanya untuk Tier 3 index-only table (string-only, aman).

### Inline edit event flow
```
CrudlfixTable.editRecord(id)
  → dispatch('crudlfix-edit', ['id' => id])
  → CrudlfixPage.handleEditRequest(['id' => id])
  → setMode('edit', id)
  → page renders CrudlfixForm (isEdit=true, editId=id, controller=...)
  → form.initForm → model::findOrFail(editId) [tenant-scoped]
```

### Auth enforcement points
```
Table.render()      → authorizeCrudlfixAction('viewAny')     [tiap AJAX]
Form.save()         → authorizeCrudlfixAction('create'|'update', $record)
Actions.executeDelete → authorizeCrudlfixAction('delete', $record)
```

---

## 4. File yang Dibuat / Diubah

### Baru (8 file)
| File | Deskripsi |
|------|-----------|
| `app/Livewire/Crudlfix/Traits/HasCrudlfixAuth.php` | Auth trait (policy + permission) |
| `resources/views/academic/mapel-jenis/index.blade.php` | Tier 1 view |
| `resources/views/academic/tahun-ajaran/index.blade.php` | Tier 1 view |
| `resources/views/academic/orang-tua/index.blade.php` | Tier 1 view |
| `resources/views/academic/semester/index.blade.php` | Tier 2 view |
| `resources/views/academic/kelas-siswa/index.blade.php` | Tier 2 view |
| `DEV_DOCS/075_inventaris_rencana_migrasi_livewire_crudlfix_20260628.md` | Inventory + plan |
| `DEV_DOCS/076_dev_report_bulk_migrasi_livewire_crudlfix_20260629.md` | This report |

### Diubah (12 file)
| File | Perubahan |
|------|-----------|
| `app/Livewire/Crudlfix/CrudlfixPage.php` | `controller` param, resolve at mount, `handleEditRequest` listener |
| `app/Livewire/Crudlfix/CrudlfixForm.php` | `controller` param, `getConfigProperty` resolve, auth in `save()` |
| `app/Livewire/Crudlfix/CrudlfixTable.php` | `HasCrudlfixAuth`, auth in `render()`, `editRecord()`, `inlineEdit`/`showEdit` flags, initial search |
| `app/Livewire/Crudlfix/Traits/HasCrudlfixForm.php` | `resolveRules()` (nested + `{{id}}`), use in `updated()` + `saveForm()` |
| `app/Livewire/Crudlfix/Traits/HasCrudlfixActions.php` | auth in `executeDelete()` |
| `resources/views/livewire/crudlfix/page.blade.php` | pass `controller` to form |
| `resources/views/livewire/crudlfix/table.blade.php` | conditional edit (inline vs URL) + `showEdit` |
| `resources/views/academic/kelas/index.blade.php` | → Livewire (controller param) |
| `resources/views/academic/mapel/index.blade.php` | → Livewire |
| `resources/views/academic/siswa/index.blade.php` | → Livewire (policy auth) |
| `resources/views/academic/guru/index.blade.php` | → Livewire (policy auth) |
| `resources/views/academic/jadwal/index.blade.php` | → Livewire table (index-only) |
| `resources/views/finance/tabungan/index.blade.php` | → Livewire table (index-only) |
| `app/Modules/Academic/routes.php` | hapus route `kelas-livewire` |
| `docs/livewire-crudlfix-guide.md` | tambah section "controller param pattern" |

### Dihapus (1 file)
| File | Alasan |
|------|--------|
| `resources/views/academic/kelas/index-livewire.blade.php` | Pilot file — produksi `index.blade.php` sudah Livewire |

---

## 5. Known Limitations

1. **Double query pada initial load** — controller `index()` (Crudlfix trait) masih
   build paginator, lalu Livewire table jalankan query sendiri. Tidak ada bug,
   hanya query berlebang. Optimasi: override `index()` di migrated controller untuk
   skip pagination saat Livewire. Future task.

2. **Show mode placeholder** — `CrudlfixPage` mode `show` masih placeholder
   ("Detail view belum diimplementasikan"). Controller dengan show view
   (Siswa, Mapel, Kelas) tetap bisa akses via URL `.show` traditional. Table
   masih menampilkan tombol Detail (URL ke `.show` route). Implementasi show
   mode Livewire = future task.

3. **Absensi tidak dimigrate** — index punya custom kelas/date filtering
   (subquery `KelasSiswa`) + bulk-create per-kelas. Butuh custom Livewire
   component, bukan generic `CrudlfixPage`/`CrudlfixTable`.

4. **Varian `*Crudlfix.php` tak terpakai** — `SiswaControllerCrudlfix.php` dan
   `ItemPembayaranControllerCrudlfix.php` masih ada sebagai contoh refactor lama.
   Cleanup di Task 11.

5. **`ItemPembayaranController`** (real) adalah traditional hand-written (tanpa
   Crudlfix trait). Tidak masuk scope migrasi ini. Varian `*Crudlfix.php`-nya
   adalah alternatif tak terpakai.

---

## 6. Metrik Akhir

| Metric | Value |
|--------|-------|
| Controller dimigrate full CrudlfixPage | 9 (Kelas pilot + Tier1×4 + Tier2×4) |
| Controller dimigrate index-only | 2 (Jadwal, TabunganSiswa) |
| Controller dibiarkan traditional | 1 (Absensi) |
| Security gap ditutup | ✅ auth enforcement di Livewire AJAX |
| File PHP baru | 1 (HasCrudlfixAuth trait) |
| File PHP diubah | 6 |
| File Blade baru | 6 (5 view + 1 dev doc... sebenarnya 6 view baru) |
| File Blade diubah | 7 (6 view + page/table component views + guide) |
| File dihapus | 1 (pilot index-livewire) |
| Route dihapus | 1 (kelas-livewire) |
| Tests | 158 passed / 401 assertions |
| Regresi | 0 |

---

## 7. Langkah Berikutnya (Task 11 / future)

- [ ] Hapus varian `*Crudlfix.php` tak terpakai (SiswaControllerCrudlfix, ItemPembayaranControllerCrudlfix)
- [ ] Optimasi double-query: override `index()` di migrated controller
- [ ] Implementasi show mode di `CrudlfixPage` (saat ini placeholder)
- [ ] Custom Livewire component untuk Absensi (kelas/date filter + bulk)
- [ ] Browser smoke test manual tiap route migrated (test suite cakupan policy/tenant,
      tapi bukan UX interaktif Livewire seperti mode-switch, real-time validation)

---

## 8. Referensi

- **Inventory & plan:** `DEV_DOCS/075_inventaris_rencana_migrasi_livewire_crudlfix_20260628.md`
- **Dev report sebelumnya:** `DEV_DOCS/074_dev_report_hybrid_crudlfix_livewire_20260626.md`
- **Guide:** `sisfokol-laravel/docs/livewire-crudlfix-guide.md`
- **Komponen:** `app/Livewire/Crudlfix/` (Page, Table, Form + 4 traits: Table, Form, Actions, Auth)
- **Crudlfix trait:** `app/Support/Crudlfix/Crudlfix.php`

---

*Dev report generated: 29 Juni 2026.*
