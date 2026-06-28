# ADR-011: UI Architecture — Blade SSR + Alpine.js + Livewire Hybrid

- **Tanggal:** 2026-06-22 (Updated: 2026-06-26)
- **Status:** ✅ DISETUJUI (Updated)
- **Penulis:** ZCode (berdasarkan diskusi arsitektur)
- **Konteks:** DEV_DOCS-053, DEV_DOCS-053b

---

## Keputusan

Kita menggunakan **Blade SSR + Alpine.js + Tailwind CSS** sebagai arsitektur UI utama, dengan **Livewire v4** diintegrasikan secara selektif untuk operasi CRUD (form, tabel, modal) guna mendapatkan real-time validation dan better UX.

---

## Konteks

Aplikasi SISFOKOL v7 saat ini adalah **Blade-SSR monolith** dengan:
- Dark theme premium (slate-950 background)
- Tailwind CSS (CDN + Vite)
- Alpine.js (sudah dipakai di beberapa view)
- Plus Jakarta Sans font
- Glassmorphism card effects
- Responsive sidebar (desktop fixed + mobile off-canvas)

Pertanyaan yang muncul: apakah perlu beralih ke Livewire untuk reactivity yang lebih baik?

---

## Opsi yang Dipertimbangkan

| Opsi | Server Load | Client Load | Complexity | Existing Code |
|------|------------|-------------|------------|---------------|
| A. Blade + Alpine.js | ⭐⭐⭐⭐⭐ Paling ringan | ⭐⭐⭐⭐ ~15KB | ⭐⭐⭐⭐⭐ Tidak ada dep baru | ⭐⭐⭐⭐⭐ Sudah dipakai |
| B. Blade + Alpine + HTMX | ⭐⭐⭐⭐ Ringan | ⭐⭐⭐⭐ ~29KB | ⭐⭐⭐ Tambah 1 dep | ⭐⭐⭐ Perlu refactor |
| **C. Hybrid (Blade + Alpine + Livewire untuk CRUD)** | ⭐⭐⭐⭐ Cukup ringan | ⭐⭐⭐ ~30KB | ⭐⭐⭐⭐ Tambah dep tapi terkontrol | ⭐⭐⭐⭐⭐ Incremental migration |
| D. Full Livewire | ⭐⭐ Berat | ⭐⭐⭐ ~30KB+ | ⭐⭐ Tambah dep besar | ⭐⭐ Perlu rewrite |

---

## Keputusan: Opsi C — Hybrid (Blade + Alpine + Livewire untuk CRUD)

### Alasan:

1. **Sudah ada di codebase** — Alpine.js + Tailwind + Vite sudah terkonfigurasi
2. **Paling ringan untuk server** — render HTML sekali, selesai, 0 state tersimpan
3. **Livewire hanya untuk CRUD** — form, tabel, modal (real-time validation, no-reload)
4. **Blade SSR untuk halaman lain** — dashboard, reports, halaman statis
5. **Incremental migration** — bisa pindah satu controller per waktu
6. **Backward compatible** — existing views masih work

### Apa yang TIDAK dipilih dan kenapa:

- **Full Livewire** — terlalu berat, perlu rewrite semua views
- **HTMX** — bagus tapi Livewire lebih terintegrasi dengan Laravel ecosystem

---

## Arsitektur Hybrid

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel Application                    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌─────────────────────────────────────────────────────┐ │
│  │  Blade SSR + Alpine.js (Halaman Utama)              │ │
│  │  - Dashboard                                        │ │
│  │  - Reports                                          │ │
│  │  - Settings                                         │ │
│  │  - Halaman Statis Lainnya                           │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                          │
│  ┌─────────────────────────────────────────────────────┐ │
│  │  Livewire Components (Operasi CRUD)                 │ │
│  │  - CrudlfixPage (orchestrator)                      │ │
│  │  - CrudlfixTable (search, sort, filter, pagination) │ │
│  │  - CrudlfixForm (real-time validation)              │ │
│  │  - CrudlfixModal (delete confirmation)              │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Komponen Livewire

| Component | Fungsi | File |
|-----------|--------|------|
| `CrudlfixPage` | Orchestrator — mode switching | `app/Livewire/Crudlfix/CrudlfixPage.php` |
| `CrudlfixTable` | Data table — search, sort, filter | `app/Livewire/Crudlfix/CrudlfixTable.php` |
| `CrudlfixForm` | Form — real-time validation | `app/Livewire/Crudlfix/CrudlfixForm.php` |

### Traits (Logic Layer)

| Trait | Fungsi | File |
|-------|--------|------|
| `HasCrudlfixTable` | Table query logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixTable.php` |
| `HasCrudlfixForm` | Form validation logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixForm.php` |
| `HasCrudlfixActions` | Delete, export logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixActions.php` |

---

## Standar UI/UX yang Diterapkan

### 1. Design System

```
Theme:          Dark (slate-950 base)
Font:           Plus Jakarta Sans (Google Fonts)
Icon:           Font Awesome 6.5
Colors:         Indigo (primary), Emerald (success), Amber (warning), Rose (danger)
Border Radius:  rounded-xl (card), rounded-2xl (modal), rounded-full (badge)
Shadow:         shadow-lg (card), shadow-2xl (modal)
Glassmorphism:  backdrop-blur-md + bg-slate-900/50
```

### 2. Responsive Breakpoints

```
Mobile:   < 640px  (sm)  — sidebar hidden, hamburger menu
Tablet:   640-1024px      — sidebar hidden, hamburger menu
Desktop:  ≥ 1024px (lg)  — sidebar fixed visible
```

### 3. Micro-Interactions (Alpine.js)

```
Transition:     x-transition (ease-out 100ms / ease-in 75ms)
Hover:          hover:bg-slate-800 transition
Active:         active:scale-95
Focus:          focus:ring-2 focus:ring-indigo-500 focus:outline-none
Dropdown:       x-show + x-transition + x-cloak
Modal:          x-show + backdrop-blur + x-transition
Toggle:         x-model + transition-colors
Loading:        Alpine.js polling + spinner animation
Toast:          Auto-dismiss with setTimeout + x-show
```

### 4. Reusable Components (Blade Components)

Lihat file `DEV_DOCS-053c` untuk detail component library.

---

## Struktur Folder Views

```
resources/views/
├── layouts/
│   ├── app.blade.php              ← Main layout (sudah ada)
│   └── partials/
│       ├── menu.blade.php         ← Sidebar menu (sudah ada)
│       ├── navbar.blade.php       ← Top navbar (extract dari app.blade.php)
│       ├── footer.blade.php       ← Footer (extract dari app.blade.php)
│       └── flash.blade.php        ← Flash messages (extract dari app.blade.php)
│
├── components/                    ← Reusable Blade Components
│   ├── ui/                        ← UI primitives
│   │   ├── alert.blade.php        ← Toast/alert notification
│   │   ├── badge.blade.php        ← Status badge
│   │   ├── button.blade.php       ← Button variants
│   │   ├── card.blade.php         ← Glassmorphism card
│   │   ├── modal.blade.php        ← Modal dialog
│   │   ├── stat-card.blade.php    ← Dashboard stat card
│   │   └── empty-state.blade.php  ← Empty state illustration
│   │
│   ├── form/                      ← Form components
│   │   ├── input.blade.php        ← Text input with label + error
│   │   ├── select.blade.php       ← Select dropdown
│   │   ├── textarea.blade.php     ← Textarea
│   │   ├── checkbox.blade.php     ← Checkbox
│   │   └── group.blade.php        ← Form group (label + input + error)
│   │
│   └── table/                     ← Table components
│       ├── table.blade.php        ← Responsive table wrapper
│       ├── thead.blade.php        ← Table header
│       ├── row.blade.php          ← Table row with hover
│       └── pagination.blade.php   ← Pagination links
│
├── livewire/                      ← Livewire Components
│   └── crudlfix/
│       ├── page.blade.php         ← Main page template
│       ├── table.blade.php        ← Table template
│       └── form.blade.php         ← Form template
│
├── partials/                      ← Shared partials
│   ├── impersonation_banner.blade.php  ← Sudah ada
│   ├── search-form.blade.php      ← Reusable search form
│   ├── delete-confirm.blade.php   ← Delete confirmation modal
│   └── loading-spinner.blade.php  ← Loading indicator
│
├── auth/                          ← Auth views (sudah ada)
├── admin/                         ← Admin views (sudah ada)
├── academic/                      ← Academic module views (sudah ada)
├── evaluation/                    ← Evaluation views (sudah ada)
├── finance/                       ← Finance views (sudah ada)
├── presence/                      ← Presence views (sudah ada)
└── ...                            ← Other module views
```

---

## Aturan Implementasi

### Untuk Halaman CRUD (Livewire):

1. **Livewire components HARUS** menggunakan raw arrays (bukan complex objects)
2. **CrudlfixConfig** di-build di dalam component, bukan dipassing dari view
3. **Real-time validation** menggunakan `wire:model.live`
4. **Search** menggunakan `wire:model.live.debounce.300ms`
5. **Pagination** tanpa page reload

### Untuk Halaman Non-CRUD (Blade SSR):

1. **Semua view HARUS** menggunakan `layouts/app.blade.php` sebagai parent
2. **Komponen reusable** menggunakan Blade component syntax (`<x-ui.card>`)
3. **Micro-interaction** menggunakan Alpine.js (`x-data`, `x-show`, `x-transition`)
4. **Responsive** — mobile-first, sidebar auto-hide di mobile
5. **Dark theme** — semua warna mengacu ke slate color palette
6. **Glassmorphism** — card menggunakan `backdrop-blur-md + bg-slate-900/50`
7. **Font** — Plus Jakarta Sans (sudah terkonfigurasi)
8. **Icon** — Font Awesome 6.5 (sudah terkonfigurasi)
9. **Accessibility** — `aria-label`, `sr-only` untuk screen reader
10. **Performance** — CDN fallback untuk Tailwind, Vite untuk production

---

## Dependencies

### Composer
```json
{
    "require": {
        "livewire/livewire": "^4.0"
    }
}
```

### NPM
Tidak ada dependency baru — Livewire v4 sudah include Alpine.js.

---

## Referensi

- **ADR-002** — Rebuild sebagai Laravel 11 modular monolith
- **DEV_DOCS-053** — Master implementation plan
- **DEV_DOCS-053b** — Verifikasi API-Driven MVC
- **layouts/app.blade.php** — Existing layout (sudah modern)
- **Livewire v4 Documentation** — https://livewire.laravel.com/docs
- **Dev Report** — `docs/dev-reports/2026-06-26-hybrid-crudlfix-livewire.md`

---

## Changelog

| Tanggal | Perubahan | Oleh |
|---------|-----------|------|
| 2026-06-22 | ADR-011 awal: Blade SSR + Alpine.js | ZCode |
| 2026-06-26 | Update: Tambah Livewire v4 untuk CRUD operations | ZCode |

---

*Keputusan ini mengikat untuk seluruh pengembangan UI/UX di Fase 1.*
