# ADR-011: UI Architecture — Blade SSR + Alpine.js + Livewire Hybrid

- **Tanggal:** 2026-06-22 (Updated: 2026-06-26)
- **Status:** ✅ DISETUJUI (Updated)
- **Penulis:** ZCode (berdasarkan diskusi arsitektur)
- **Konteks:** DEV_DOCS-053, DEV_DOCS-053b, Hybrid Crudlfix + Livewire Implementation

---

## Ringkasan Eksekutif

ADR-011 diupdate dari pendekatan **Blade SSR + Alpine.js** menjadi **Hybrid (Blade SSR + Alpine.js + Livewire)** untuk operasi CRUD. Perubahan ini bertujuan memberikan real-time validation, no-reload table operations, dan better UX pada Crudlfix Library tanpa mengubah backend logic.

**Key Decision:** Livewire v4 diintegrasikan secara selektif HANYA untuk operasi CRUD (form, tabel, modal). Halaman non-CRUD (dashboard, reports, settings) tetap menggunakan Blade SSR + Alpine.js.

---

## Konteks & Motivasi

### Kondisi Sebelum Update (ADR-011 v1)

| Aspek | Teknologi |
|-------|-----------|
| Framework | Laravel 11.31 |
| Rendering | Blade SSR |
| CSS | Tailwind CSS 3.4 (CDN + Vite) |
| JS | Alpine.js v3 (CDN) |
| Build | Vite 6 |
| CRUD Library | Custom "Crudlfix" trait |
| Livewire | TIDAK terinstall |

### Masalah dengan Pendekatan Sebelumnya

1. **No real-time validation** — Error muncul setelah submit form (full page reload)
2. **Page reload untuk setiap interaksi** — Search, sort, filter, pagination semua reload
3. **UX kurang smooth** — Loading state tidak optimal, tidak ada optimistic UI
4. **Duplikasi kode di views** — Setiap CRUD view menulis ulang table/form markup

### Tujuan Update

1. Real-time validation di form (error saat mengetik)
2. Search, sort, filter, pagination tanpa page reload
3. Better loading states dan UX
4. Reusable base components untuk semua CRUD
5. Minimal disruption ke backend (Crudlfix trait tidak berubah)
6. Incremental migration (bisa satu per satu controller)

---

## Keputusan yang Diambil

### Opsi yang Dipertimbangkan

| Opsi | Server Load | Client Load | Complexity | Existing Code |
|------|------------|-------------|------------|---------------|
| A. Blade + Alpine.js (Status Quo) | ⭐⭐⭐⭐⭐ Paling ringan | ⭐⭐⭐⭐ ~15KB | ⭐⭐⭐⭐⭐ Tidak ada dep baru | ⭐⭐⭐⭐⭐ Sudah dipakai |
| B. Blade + Alpine + HTMX | ⭐⭐⭐⭐ Ringan | ⭐⭐⭐⭐ ~29KB | ⭐⭐⭐ Tambah 1 dep | ⭐⭐⭐ Perlu refactor |
| **C. Hybrid (Blade + Alpine + Livewire untuk CRUD)** | ⭐⭐⭐⭐ Cukup ringan | ⭐⭐⭐ ~30KB | ⭐⭐⭐⭐ Tambah dep tapi terkontrol | ⭐⭐⭐⭐⭐ Incremental migration |
| D. Full Livewire | ⭐⭐ Berat | ⭐⭐⭐ ~30KB+ | ⭐⭐ Tambah dep besar | ⭐⭐ Perlu rewrite |

### Keputusan: Opsi C — Hybrid (Blade + Alpine + Livewire untuk CRUD)

**Alasan:**

1. **Minimal disruption** — Backend Crudlfix trait tidak berubah
2. **Incremental migration** — Bisa pindah satu controller per waktu
3. **Reusable components** — Base Livewire components dipakai di semua CRUD
4. **Real-time validation** — Error muncul saat user mengetik
5. **No page reload** — Search, sort, filter, pagination tanpa reload
6. **Backward compatible** — Existing views masih work

**Trade-off:**

1. **+2MB dependency** — Livewire package
2. **Server load meningkat** — Setiap interaksi = HTTP request (tapi Livewire v4 sudah optimasi)
3. **Learning curve** — Tim perlu belajar Livewire pattern
4. **~3 minggu implementasi** — Dari foundation sampai migrasi semua controller

---

## Arsitektur Hybrid

### Overview

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
| `CrudlfixPage` | Orchestrator — mode switching (index/create/edit/show) | `app/Livewire/Crudlfix/CrudlfixPage.php` |
| `CrudlfixTable` | Data table — search, sort, filter, pagination | `app/Livewire/Crudlfix/CrudlfixTable.php` |
| `CrudlfixForm` | Form — real-time validation, save | `app/Livewire/Crudlfix/CrudlfixForm.php` |

### Traits (Logic Layer)

| Trait | Fungsi | File |
|-------|--------|------|
| `HasCrudlfixTable` | Table query logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixTable.php` |
| `HasCrudlfixForm` | Form validation logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixForm.php` |
| `HasCrudlfixActions` | Delete, export logic | `app/Livewire/Crudlfix/Traits/HasCrudlfixActions.php` |

### Views (Blade Templates)

| View | Fungsi | File |
|------|--------|------|
| `page.blade.php` | Main page layout | `resources/views/livewire/crudlfix/page.blade.php` |
| `table.blade.php` | Table with search, sort, pagination | `resources/views/livewire/crudlfix/table.blade.php` |
| `form.blade.php` | Form with real-time validation | `resources/views/livewire/crudlfix/form.blade.php` |

---

## Technical Decisions

### 1. Livewire v4 (bukan v3)

Composer resolve ke v4.3.2 karena constraint `^3.0` compatible dengan v4. Livewire v4 lebih stabil dan memiliki performance improvements.

### 2. Raw Arrays (bukan Complex Objects)

Livewire tidak support passing complex objects (seperti `CrudlfixConfig`) sebagai property. Solusi: pass raw arrays, build `CrudlfixConfig` di dalam component.

```php
// ❌ Tidak work
'config' => CrudlfixConfig::make([...])

// ✅ Work
'model' => Kelas::class,
'route' => 'academic.kelas',
'columns' => [...],
```

### 3. Rename `$search` → `$searchQuery`

Property `$search` di trait `HasCrudlfixTable` conflict dengan parameter `$search` (array) yang dipassing dari view. Solusi: rename ke `$searchQuery` untuk input search value.

### 4. Alpine.js CDN Dihapus

Livewire v4 bundle Alpine.js secara internal. Loading Alpine.js via CDN akan menyebabkan conflict. Solusi: hapus CDN import, biarkan Livewire handle Alpine.js.

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

---

## Struktur Folder Views

```
resources/views/
├── layouts/
│   ├── app.blade.php              ← Main layout
│   └── partials/
│       ├── menu.blade.php         ← Sidebar menu
│       ├── navbar.blade.php       ← Top navbar
│       ├── footer.blade.php       ← Footer
│       └── flash.blade.php        ← Flash messages
│
├── components/                    ← Reusable Blade Components
│   ├── ui/                        ← UI primitives
│   ├── form/                      ← Form components
│   └── table/                     ← Table components
│
├── livewire/                      ← Livewire Components (NEW)
│   └── crudlfix/
│       ├── page.blade.php         ← Main page template
│       ├── table.blade.php        ← Table template
│       └── form.blade.php         ← Form template
│
├── partials/                      ← Shared partials
├── auth/                          ← Auth views
├── admin/                         ← Admin views
├── academic/                      ← Academic module views
├── evaluation/                    ← Evaluation views
├── finance/                       ← Finance views
├── presence/                      ← Presence views
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
6. **Form fields** didefinisikan sebagai array di view
7. **Columns** didefinisikan sebagai array di view

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

### Config Changes

- `config/livewire.php` — Published dan tersedia
- `resources/views/layouts/app.blade.php` — `@livewireStyles` dan `@livewireScripts` ditambahkan

---

## Implementasi yang Sudah Selesai

### Phase 1: Foundation (DONE)

- ✅ Install Livewire v4.3.2
- ✅ Publish Livewire config
- ✅ Update layout (`@livewireStyles`, `@livewireScripts`)
- ✅ Hapus Alpine.js CDN (Livewire bundle Alpine.js)

### Phase 2: Base Components (DONE)

- ✅ `HasCrudlfixTable` trait
- ✅ `HasCrudlfixForm` trait
- ✅ `HasCrudlfixActions` trait
- ✅ `CrudlfixPage` component
- ✅ `CrudlfixTable` component
- ✅ `CrudlfixForm` component

### Phase 3: Pilot Migration (DONE)

- ✅ KelasController Livewire view
- ✅ Test route `/academic/kelas-livewire`
- ✅ Documentation

---

## Known Issues & Limitations

### 1. Show Mode Belum Diimplementasikan

View untuk mode "show" (detail) belum dibuat. Hanya placeholder.

### 2. Cascade Select Belum Support

Fitur cascade select (dropdown yang bergantung pada dropdown lain) belum diimplementasikan di Livewire version.

### 3. Search Select Belum Support

Fitur search select (AJAX dropdown) belum diimplementasikan.

### 4. Export Belum Teruji

Fitur export CSV sudah diimplementasikan di `HasCrudlfixActions` tapi belum teruji di Livewire context.

---

## Referensi Dokumen

### Design & Planning

| Dokumen | Lokasi | Deskripsi |
|---------|--------|-----------|
| Design Spec | `docs/superpowers/specs/2026-06-26-hybrid-crudlfix-livewire-design.md` | Spesifikasi desain hybrid approach |
| Implementation Plan | `docs/superpowers/plans/2026-06-26-hybrid-crudlfix-livewire.md` | Detail implementasi step-by-step |

### Documentation

| Dokumen | Lokasi | Deskripsi |
|---------|--------|-----------|
| Livewire Crudlfix Guide | `DEV_DOCS/072_panduan_livewire_crudlfix_hybrid_20260626.md` | Panduan penggunaan untuk developer |
| Dev Report | `docs/dev-reports/2026-06-26-hybrid-crudlfix-livewire.md` | Laporan implementasi lengkap |

### Existing Documents

| Dokumen | Lokasi | Deskripsi |
|---------|--------|-----------|
| ADR-002 | `ADR/002_*.md` | Rebuild sebagai Laravel 11 modular monolith |
| ADR-003 | `ADR/003_*.md` | Multi-tenant SaaS shared-DB |
| ADR-006 | `ADR/006_*.md` | Granular DB-driven RBAC |
| ADR-009 | `ADR/009_*.md` | Plugin system plug-and-play |
| ADR-010 | `ADR/010_*.md` | RBAC sampai menu & field level |
| DEV_DOCS-053 | `DEV_DOCS/053_*.md` | Master implementation plan |
| DEV_DOCS-053b | `DEV_DOCS/053b_*.md` | Verifikasi API-Driven MVC |
| DEV_DOCS-053c | `DEV_DOCS/053c_*.md` | Reusable component library spec |
| DEV_DOCS-054 | `DEV_DOCS/054_*.md` | Memory/handoff konteks terkini |

### External References

| Resource | URL | Deskripsi |
|----------|-----|-----------|
| Livewire v4 Documentation | https://livewire.laravel.com/docs | Official Livewire docs |
| Laravel 11 Documentation | https://laravel.com/docs/11 | Laravel 11 docs |
| Tailwind CSS | https://tailwindcss.com/docs | Tailwind CSS docs |
| Alpine.js | https://alpinejs.dev/start-here | Alpine.js docs |

---

## Changelog

| Tanggal | Versi | Perubahan | Oleh |
|---------|-------|-----------|------|
| 2026-06-22 | v1.0 | ADR-011 awal: Blade SSR + Alpine.js | ZCode |
| 2026-06-26 | v2.0 | Update: Tambah Livewire v4 untuk CRUD operations | ZCode |
| 2026-06-26 | v2.1 | Tambah konteks, referensi dokumen, technical decisions | ZCode |

---

## Catatan untuk Agentic AI

### Antigravity (Google DeepMind)
- Gunakan Livewire components untuk operasi CRUD baru
- Ikuti pattern di `DEV_DOCS/072_panduan_livewire_crudlfix_hybrid_20260626.md`
- Jangan gunakan complex objects sebagai Livewire property

### Kiro (kiro.dev)
- Referensi ke `.kiro/` untuk skills dan workflows
- Livewire components di `app/Livewire/Crudlfix/`
- Blade views di `resources/views/livewire/crudlfix/`

### Opencode
- Shared steering di `.agents/steering/`
- Follow Karpathy Guidelines untuk coding standards
- Backup sebelum edit file kritis

### ZCode
- Skills di `.zcode/`
- Gunakan `smart-debugging` skill untuk investigasi error
- Follow `safe-file-edit` workflow untuk edit file

---

*Keputusan ini mengikat untuk seluruh pengembangan UI/UX di Fase 1.*
*Last Updated: 2026-06-26 oleh ZCode*
