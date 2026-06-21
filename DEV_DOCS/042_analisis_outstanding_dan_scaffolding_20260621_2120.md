# DEV_DOCS-042: Analisis Outstanding Proyek & Scaffolding Boilerplate Terstandarisasi

- **Tanggal:** 2026-06-21 21:20
- **Status:** Diimplementasikan (Implemented)
- **Penulis:** Agent Mode (Arena.ai)
- **Topik:** Laporan Scaffolding Fisik 8 Plugin & Fondasi Pipeline ETL
- **Terhubung ke ADR:** ADR-002, ADR-009, ADR-010
- **Terhubung ke DEV_DOCS:** DEV_DOCS-010, DEV_DOCS-012, DEV_DOCS-041

---

## ⚡ EXECUTIVE SUMMARY

Berdasarkan tinjauan kritis antara perencanaan proyek (**DEV_DOCS-012**) dan codebase fisik di `sisfokol-laravel/`, ditemukan area krusial yang statusnya masih tertunda (*pending/unimplemented*):
1. **Epic 10 (8 Plugin Tambahan):** Belum ada folder fisik ataupun manifes untuk modul penunjang selain Kurikulum.
2. **Epic 11 (ETL Pipeline):** Perintah migrasi data (`MigrateLegacyDataCommand.php`) masih berupa draf kosong tanpa kelas-kelas tahapan (*Step classes*).
3. **Epic 12 (CI/CD Deployment):** Konfigurasi rilis otomatis belum dibuat di repositori.

Untuk menyelesaikan kesenjangan ini secara taktis, kami telah melakukan **scaffolding fisik secara otomatis** terhadap seluruh struktur folder baru tersebut, lengkap dengan manifest standar, rute aman, template views, serta penanda status (`SCAFFOLD / UNIMPLEMENTED`) untuk mempermudah tim pengembang melakukan pembaruan di masa mendatang.

---

## 1. PETA STRUKTUR CODEBASE TERBARU

Berikut adalah struktur folder baru hasil proses scaffolding terstandarisasi yang telah disuntikkan ke dalam repositori:

```
sisfokol-laravel/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── Etl/                          [FOLDER BARU - SCAFFOLD]
│   │           ├── IdMapper.php              (Utility Mapping ID Legacy ↔ Baru)
│   │           ├── StepInterface.php         (Contract Interface Tahapan ETL)
│   │           └── README.md                 (Panduan & 20 Alur Urutan Migrasi)
│   │
│   └── Plugins/
│       ├── BimbinganKonseling/               [FOLDER BARU - SCAFFOLD]
│       ├── Discipline/                       [FOLDER BARU - SCAFFOLD]
│       ├── Inventory/                        [FOLDER BARU - SCAFFOLD]
│       ├── Tahfidz/                          [FOLDER BARU - SCAFFOLD]
│       ├── HafalanHadist/                    [FOLDER BARU - SCAFFOLD]
│       ├── PendidikanKarakter/               [FOLDER BARU - SCAFFOLD]
│       ├── PelaporanOrtu/                    [FOLDER BARU - SCAFFOLD]
│       └── PWA/                              [FOLDER BARU - SCAFFOLD]
```

---

## 2. DETAIL IMPLEMENTASI 8 BOILERPLATE PLUGIN BARU (EPIC 10)

Setiap folder dari 8 plugin tambahan di atas kini telah dilengkapi dengan file berikut untuk menjamin kompabilitas penuh dengan `PluginRegistryServiceProvider`:

### 2.1 Manifes Plugin (`<NamaPlugin>Plugin.php`)
Setiap manifes mengimplementasikan `PluginContract` secara murni, mendaftarkan kode identitas unik, nama fungsional, permissions, dan menu navigasi default:
```php
<?php

namespace App\Plugins\Tahfidz;

use App\Support\{PluginContract, PluginContext};

class TahfidzPlugin implements PluginContract
{
    public function kode(): string { return 'tahfidz'; }
    public function nama(): string { return 'Hafalan Tahfidz Al-Qur\'an'; }
    public function versi(): string { return '1.0.0'; }
    public function isCore(): bool { return false; }
    public function dependencies(): array { return []; }

    public function providerClass(): string
    {
        return \App\Plugins\Tahfidz\Providers\TahfidzServiceProvider::class;
    }

    public function permissions(): array
    {
        return [
            ['name' => 'tahfidz.view',   'display_name' => 'Lihat Hafalan Tahfidz Al-Qur\'an',    'module' => 'Tahfidz'],
            ['name' => 'tahfidz.manage', 'display_name' => 'Kelola Hafalan Tahfidz Al-Qur\'an',   'module' => 'Tahfidz'],
        ];
    }

    public function menu(): array
    {
        return [
            [
                'kode' => 'tahfidz.index',
                'label' => 'Tahfidz',
                'route' => 'tahfidz.index',
                'permission_required' => 'tahfidz.view',
                'urutan' => 80,
                'group' => 'Plugin'
            ],
        ];
    }

    public function boot(PluginContext $ctx): void {}
}
```

### 2.2 Service Provider (`Providers/<NamaPlugin>ServiceProvider.php`)
Berfungsi memuat file tampilan (*views*) di bawah namespace kustom (seperti `tahfidz::index`) dan mendaftarkan rute plugin ke middleware web:
```php
<?php

namespace App\Plugins\Tahfidz\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Route;

class TahfidzServiceProvider extends EventServiceProvider
{
    public function boot(): void
    {
        parent::boot();
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'tahfidz');

        $routesFile = __DIR__ . '/../routes.php';
        if (file_exists($routesFile)) {
            Route::middleware('web')->group($routesFile);
        }
    }
}
```

### 2.3 File Rute Terproteksi (`routes.php`)
Rute penapis dinamis dienkapsulasi menggunakan middleware `plugin:<kode>` sesuai spesifikasi `ADR-009`:
```php
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'plugin:tahfidz'])->prefix('plugin/tahfidz')->name('tahfidz.')->group(function () {
    Route::get('/', function () {
        return view('tahfidz::index');
    })->name('index');
});
```

### 2.4 Halaman Tampilan (`Resources/views/index.blade.php`)
Tampilan menggunakan desain *glassmorphism dark-theme* premium yang konsisten dengan standar UI SISFOKOL v7, memuat badge status serta daftar tugas masa depan (TODO).

### 2.5 Penanda Status (`README.md`)
Diletakkan di setiap akar folder plugin untuk memberikan penanda yang tegas bagi sistem manajemen proyek:
```markdown
# [Nama Plugin] Plugin (Boilerplate Scaffold)
- **Status:** ⏳ **SCAFFOLD / UNIMPLEMENTED** (Epic 10 Pending)
```

---

## 3. DETAIL FONDASI PIPELINE ETL (EPIC 11)

Untuk menjembatani kebutuhan konversi data dari database legacy, kami telah membangun fondasi pipa ETL di folder `app/Console/Commands/Etl/`:

### 3.1 Kontrak Antarmuka (`StepInterface.php`)
Memaksa setiap kelas tahapan migrasi untuk mengadopsi struktur penanganan yang seragam:
```php
<?php
namespace App\Console\Commands\Etl;
use App\Modules\Tenancy\Models\Tenant;

interface StepInterface
{
    public function handle(Tenant $tenant): void;
}
```

### 3.2 Kamus Pemetaan ID (`IdMapper.php`)
Menyediakan mekanisme penerjemahan ID lama (seperti UUID/MD5 string) ke ID auto-increment database baru secara real-time (singleton caching):
```php
<?php
namespace App\Console\Commands\Etl;

class IdMapper
{
    private array $mappings = [];

    public function record(string $entity, string|int $legacyId, int $newId): void
    {
        $this->mappings[$entity][(string)$legacyId] = $newId;
    }

    public function lookup(string $entity, string|int $legacyId): ?int
    {
        return $this->mappings[$entity][(string)$legacyId] ?? null;
    }
}
```

### 3.3 Dokumentasi Rencana Jalur Migrasi (`README.md`)
Memetakan secara detail **20 urutan topologis pemindahan tabel data** agar terhindar dari benturan batasan kunci asing (*Foreign Key constraints*), serta merancang kebutuhan utilitas pembersihan (*Cleansing*) data masif.

---

## 4. KEUNTUNGAN BAGI DEVELOPE MASA DEPAN

Dengan scaffolding yang telah kami selesaikan ini, tim pengembang akan mendapatkan keuntungan operasional berupa:
1. **Bebas Crash pada Registry:** Dashboard admin dapat memindai plugin-plugin ini tanpa error karena manifes fisiknya telah tersedia nyata.
2. **Standardisasi Namespace & Folder:** Meminimalisir kesalahan penulisan (*typo*) struktur folder dan namespace oleh developer baru.
3. **Peta Jalan Jelas:** Cukup membuka file `README.md` di tiap folder kustom untuk melanjutkan pembangunan fitur secara spesifik.
