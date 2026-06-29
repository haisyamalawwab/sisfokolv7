# Livewire Crudlfix — Dokumentasi Penggunaan

## Ringkasan

Livewire Crudlfix adalah hybrid approach yang menggabungkan Crudlfix trait (backend) dengan Livewire components (frontend) untuk memberikan:
- **Real-time validation** — Error muncul saat user mengetik
- **No page reload** — Search, sort, filter, pagination tanpa reload
- **Better UX** — Loading states, smooth transitions

## Arsitektur

```
Controller (Crudlfix trait)
    ↓
CrudlfixConfig (single source of truth)
    ↓
Livewire Components
    ├── CrudlfixPage (orchestrator)
    ├── CrudlfixTable (data table)
    └── CrudlfixForm (form with validation)
```

**Key principle:** Backend logic tetap di Crudlfix trait. Livewire hanya handle view layer.

---

## Pola yang Direkomendasikan: `controller` param (single source of truth)

Cara tercepat & terbersih: pass **FQCN controller** ke `crudlfix-page`. Komponen akan
resolve rules, search, with, auth, dan viewData dari `getCrudlfixConfig()` controller.
View hanya perlu define **columns** dan **formFields** (layer tampilan).

```blade
@livewire('crudlfix.crudlfix-page', [
    'controller' => \App\Modules\Academic\Controllers\MapelController::class,
    'columns' => ['kode' => 'Kode', 'nama' => 'Nama', 'jenis.nama' => 'Jenis'],
    'formFields' => [
        'kode' => ['label' => 'Kode', 'type' => 'text'],
        'nama' => ['label' => 'Nama', 'type' => 'text'],
        'mapel_jenis_id' => ['label' => 'Jenis', 'type' => 'select', 'options' => $jenisList->pluck('nama','id')->toArray()],
    ],
])
```

**Keuntungan:**
- Rules (termasuk `Rule::unique` closure/object) di-resolve dari controller — tidak perlu
  diduplikasi di view, dan aman untuk closure yang tidak bisa di-serialize Livewire.
- Auth (`authType` policy/permission) otomatis dari controller config.
- `viewData` (untuk select options) di-pass controller `index()` → tersedia di view.

**Catatan:** select `options` yang butuh data relasi tetap di-build di view dari variabel
viewData (e.g. `$jenisList`) yang di-pass controller `index()`.

> Pola lama (pass `model`/`route`/`search`/`rules`/`authorize`/`authType` sebagai flat array
> dari view) masih didukung untuk backward compatibility, tapi tidak direkomendasikan untuk
> controller dengan `Rule::unique` atau nested store/update rules.

---

## Cara Membuat CRUD Baru dengan Livewire

### Step 1: Buat Controller dengan Crudlfix Trait

```php
<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Mapel;
use App\Support\Crudlfix\Crudlfix;

class MapelController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Mapel::class,
            'view'       => 'academic.mapel',
            'route'      => 'academic.mapel',
            'authorize'  => 'mapel',
            'search'     => ['nama', 'kode'],
            'with'       => ['mapelJenis'],
            'rules'      => [
                'nama' => 'required|string|max:100',
                'kode' => 'required|string|max:20|unique:mapel,kode',
                'mapel_jenis_id' => 'required|exists:mapel_jenis,id',
            ],
            'viewData'   => [
                'mapelJenis' => MapelJenis::orderBy('nama')->get(),
            ],
            'perPage' => 20,
        ];
    }
}
```

### Step 2: Buat Livewire View

Buat file `resources/views/academic/mapel/index.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Akademik — Mata Pelajaran')
@section('page-title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'model' => \App\Modules\Academic\Models\Mapel::class,
        'view' => 'academic.mapel',
        'route' => 'academic.mapel',
        'columns' => [
            'kode' => 'Kode',
            'nama' => 'Nama',
            'mapelJenis.nama' => 'Jenis',
        ],
        'formFields' => [
            'kode' => ['label' => 'Kode', 'type' => 'text', 'placeholder' => 'Contoh: MTK'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama mata pelajaran'],
            'mapel_jenis_id' => [
                'label' => 'Jenis Mapel',
                'type' => 'select',
                'options' => $mapelJenis->pluck('nama', 'id')->toArray(),
            ],
        ],
        'search' => ['nama', 'kode'],
        'with' => ['mapelJenis'],
        'rules' => [
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mapel,kode',
            'mapel_jenis_id' => 'required|exists:mapel_jenis,id',
        ],
        'viewData' => [
            'mapelJenis' => $mapelJenis,
        ],
        'perPage' => 20,
        'authorize' => 'mapel',
        'authType' => 'permission',
    ])
</div>
@endsection
```

### Step 3: Buat Route

Di module `routes.php`:

```php
Route::resource('mapel', MapelController::class);
```

**Selesai!** CRUD sudah berfungsi dengan Livewire.

---

## Parameter CrudlfixPage

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model` | string | ✅ | Full class name model (e.g., `Mapel::class`) |
| `view` | string | ✅ | View prefix (e.g., `'academic.mapel'`) |
| `route` | string | ✅ | Route name prefix (e.g., `'academic.mapel'`) |
| `columns` | array | ✅ | Table columns `['field' => 'Label']` |
| `formFields` | array | ✅ | Form fields configuration |
| `search` | array | ❌ | Searchable fields |
| `with` | array | ❌ | Eager load relations |
| `filters` | array | ❌ | Filter definitions |
| `rules` | array | ❌ | Validation rules |
| `viewData` | array | ❌ | Extra data for selects |
| `perPage` | int | ❌ | Items per page (default: 15) |
| `defaultSort` | string | ❌ | Default sort field (default: 'created_at') |
| `defaultDir` | string | ❌ | Default sort direction (default: 'desc') |
| `exportColumns` | array | ❌ | CSV export columns |
| `authorize` | string | ❌ | Permission prefix |
| `authType` | string | ❌ | Authorization type ('policy' or 'permission') |

---

## Form Fields Configuration

### Text Input
```php
'nama' => [
    'label' => 'Nama',
    'type' => 'text',
    'placeholder' => 'Masukkan nama',
]
```

### Number Input
```php
'tingkat' => [
    'label' => 'Tingkat',
    'type' => 'number',
    'placeholder' => '1 - 12',
]
```

### Textarea
```php
'deskripsi' => [
    'label' => 'Deskripsi',
    'type' => 'textarea',
    'rows' => 4,
    'placeholder' => 'Deskripsi singkat',
]
```

### Select Dropdown
```php
'wali_kelas_id' => [
    'label' => 'Wali Kelas',
    'type' => 'select',
    'options' => $gurus->pluck('nama', 'id')->toArray(),
]
```

### Date Input
```php
'tanggal_lahir' => [
    'label' => 'Tanggal Lahir',
    'type' => 'date',
]
```

### Checkbox
```php
'aktif' => [
    'label' => 'Status',
    'type' => 'checkbox',
    'checkbox_label' => 'Aktif',
]
```

---

## Columns Configuration

### Simple Field
```php
'columns' => [
    'nama' => 'Nama',
    'email' => 'Email',
]
```

### Relation Field (dot notation)
```php
'columns' => [
    'nama' => 'Nama',
    'waliKelas.nama' => 'Wali Kelas',
    'branch.nama' => 'Cabang',
]
```

---

## Filters Configuration

```php
'filters' => [
    'tingkat' => [
        'column' => 'tingkat',
        'operator' => '=',
    ],
    'status' => [
        'column' => 'status',
        'operator' => '=',
    ],
],
'viewData' => [
    'tingkatOptions' => [1 => 'Kelas 1', 2 => 'Kelas 2', ...],
    'statusOptions' => ['active' => 'Aktif', 'inactive' => 'Nonaktif'],
],
```

---

## Contoh Lengkap

### Controller
```php
<?php

namespace App\Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\ItemPembayaran;
use App\Support\Crudlfix\Crudlfix;

class ItemPembayaranController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => ItemPembayaran::class,
            'view'       => 'finance.item-pembayaran',
            'route'      => 'finance.item-pembayaran',
            'authorize'  => 'item-pembayaran',
            'search'     => ['nama', 'kode'],
            'rules'      => [
                'kode' => 'required|string|max:20|unique:item_pembayaran,kode',
                'nama' => 'required|string|max:100',
                'tipe' => 'required|in:bulanan,bebas',
                'nominal' => 'required|numeric|min:0',
                'deskripsi' => 'nullable|string|max:255',
                'aktif' => 'boolean',
            ],
            'perPage' => 20,
        ];
    }
}
```

### View
```blade
@extends('layouts.app')

@section('title', 'Keuangan — Item Pembayaran')
@section('page-title', 'Item Pembayaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @livewire('crudlfix.crudlfix-page', [
        'model' => \App\Modules\Finance\Models\ItemPembayaran::class,
        'view' => 'finance.item-pembayaran',
        'route' => 'finance.item-pembayaran',
        'columns' => [
            'kode' => 'Kode',
            'nama' => 'Nama',
            'tipe' => 'Tipe',
            'nominal' => 'Nominal',
            'aktif' => 'Status',
        ],
        'formFields' => [
            'kode' => ['label' => 'Kode', 'type' => 'text', 'placeholder' => 'Contoh: SPP'],
            'nama' => ['label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama item'],
            'tipe' => [
                'label' => 'Tipe',
                'type' => 'select',
                'options' => ['bulanan' => 'Bulanan', 'bebas' => 'Bebas'],
            ],
            'nominal' => ['label' => 'Nominal', 'type' => 'number', 'placeholder' => '0'],
            'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rows' => 3],
            'aktif' => ['label' => 'Status', 'type' => 'checkbox', 'checkbox_label' => 'Aktif'],
        ],
        'search' => ['nama', 'kode'],
        'rules' => [
            'kode' => 'required|string|max:20|unique:item_pembayaran,kode',
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:bulanan,bebas',
            'nominal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string|max:255',
            'aktif' => 'boolean',
        ],
        'perPage' => 20,
        'authorize' => 'item-pembayaran',
        'authType' => 'permission',
    ])
</div>
@endsection
```

---

## Migration dari Blade ke Livewire

Untuk controller yang sudah ada dengan Blade views:

1. **Buat view Livewire baru** (e.g., `index-livewire.blade.php`)
2. **Test di route terpisah** dulu
3. **Jika sudah oke**, rename file:
   - `index.blade.php` → `index-old.blade.php`
   - `index-livewire.blade.php` → `index.blade.php`
4. **Commit** perubahan

---

## Troubleshooting

### Livewire tidak render
- Pastikan `@livewireStyles` di `<head>` dan `@livewireScripts` sebelum `</body>`
- Cek browser console untuk error

### Real-time validation tidak jalan
- Pastikan menggunakan `wire:model.live` bukan `wire:model`
- Cek rules di config sudah benar

### Table tidak update setelah save
- Pastikan event `crudlfix-saved` ter-dispatch
- Cek listener di CrudlfixPage

### Select options tidak muncul
- Pastikan `viewData` berisi data yang dibutuhkan
- Cek format options: `['value' => 'label']`

---

## Referensi

- **Livewire v4 Documentation:** https://livewire.laravel.com/docs
- **Design Spec:** `docs/superpowers/specs/2026-06-26-hybrid-crudlfix-livewire-design.md`
- **Implementation Plan:** `docs/superpowers/plans/2026-06-26-hybrid-crudlfix-livewire.md`
