DEV_DOCS-041: Review Kesiapan Arsitektur API-Driven pada SISFOKOL v7 (Code:SmartOffice)
Tanggal: 2026-06-21 21:15
Status: Draft / Diusulkan (Proposed)
Penulis: Agent Mode (Arena.ai)
Topik: Analisis Kritis Kesiapan Arsitektur API-Driven (Fase 1 vs Fase 2)
Terhubung ke ADR: ADR-002, ADR-003, ADR-009, ADR-010
Terhubung ke DEV_DOCS: DEV_DOCS-001, DEV_DOCS-002, DEV_DOCS-006, DEV_DOCS-010
⚡ EXECUTIVE SUMMARY
Berdasarkan analisis mendalam (deep-dive) terhadap codebase sisfokol-laravel/, skema rute, dokumen keputusan arsitektur (ADR), serta rencana implementasi (EPIC & Sprint Plans), ditarik kesimpulan utama:

Status Saat Ini: Aplikasi SISFOKOL v7 (Laravel 11) saat ini bukan murni aplikasi API-Driven. Aplikasi ini dirancang sebagai Domain-Modular Monolith dengan pendekatan Server-Side Rendering (SSR) menggunakan Laravel Blade Templates dan Tailwind CSS via Vite.
Ketersediaan API Layer: Terdapat lapisan API yang sangat minimalis menggunakan Laravel Sanctum untuk otentikasi dasar dan satu endpoint jadwal (/api/schedules/today), namun ini baru sebatas integrasi taktis (bukan motor penggerak UI).
Persiapan di dalam EPIC & Sprint: Persiapan struktural (architectural blueprint) untuk beralih ke API-driven sudah diletakkan (ganda autentikasi web & sanctum, placeholder app/Http/Resources), namun implementasi penuhnya secara eksplisit ditunda dan didelegasikan ke Fase 2 (Pasca-MVP).
Dokumen ini disusun sebagai tinjauan kritis guna memetakan kesiapan sistem saat ini serta mengidentifikasi kesenjangan (gaps) teknis yang harus dijembatani sebelum melakukan transisi penuh ke arsitektur API-Driven.

1. BUKTI TEKNIS: ANALISIS CODEBASE & ROUTING
Pembuktian bahwa aplikasi saat ini bertumpu pada arsitektur monolitik tradisional (bukan API-driven) didasarkan pada temuan file berikut:

1.1 Dependensi Frontend & Backend Menyatu
Pada file sisfokol-laravel/package.json dan composer.json, tidak ditemukan adapter Single Page Application (SPA) modern seperti Inertia.js atau framework reaktif client-side (seperti Vue atau React):

package.json: Hanya memuat tailwindcss, postcss, vite, dan axios sebagai utilitas ringan.
composer.json: Memuat pustaka monolitik seperti barryvdh/laravel-dompdf (rendering PDF di sisi server) dan lab404/laravel-impersonate (mengandalkan state session PHP).
1.2 Skema Rute yang Tidak Seimbang
Perbandingan rute di routes/ membuktikan fungsionalitas utama dikunci di rute web:

routes/web.php: Mengarahkan puluhan entitas administratif (Siswa, Kelas, Jadwal, Nilai, Pembayaran, Presensi) ke controller yang mengembalikan tampilan Blade.
routes/api.php: Hanya memiliki 4 rute fungsional:
PHP

Route::post('/login', [ApiAuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ApiAuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.logout');
    Route::get('/schedules/today', [ApiScheduleController::class, 'today'])->name('api.schedules.today');
});
1.3 Penggunaan Server-Side Rendering (SSR)
Setiap controller utama di dalam core modules (app/Modules/) menggunakan pengembalian data tradisional berupa HTML:

PHP

return view('admin.users.index', compact('users'));
Jika aplikasi sudah API-driven, respons harus dalam bentuk format data terstruktur seperti JSON menggunakan API Resources (JsonResource).

2. JALUR PERSIAPAN API-DRIVEN DALAM DOKUMEN RENCANA (EPIC & SPRINT)
Meskipun fokus utama Fase 1 adalah penyelesaian MVP berbasis Blade, dokumen perencanaan telah menyiapkan beberapa landasan teknis agar transisi ke API-driven di masa mendatang berjalan mulus:

2.1 Autentikasi Ganda (web & sanctum)
Dalam DEV_DOCS/002_bagian2_tenancy_auth_rbac_impersonation_20260620_0713.md, skema guard autentikasi dirancang untuk mengakomodasi token stateless di masa mendatang:

Guard web menggunakan driver session untuk SuperAdmin dan pengguna web fungsional.
Guard sanctum disiapkan dengan driver token dengan catatan khusus: "(Fase 2) API/PWA/mobile".
2.2 Struktur Folder Placeholder app/Http/Resources
Dokumen struktur folder final di DEV_DOCS/010_bagian6_folder_structure_techstack_deployment_20260620_0830.md telah mencantumkan direktori khusus untuk penanganan serialisasi API JSON:

text

sisfokol-laravel/
├── app/
│   ├── Http/
│   │   ├── Resources/      ← API Resources (Fase 2)
2.3 Konfigurasi Environment API-Ready
File .env.example telah mencantumkan parameter konfigurasi CORS dan stateful requests untuk pustaka Sanctum:

ini

# Sanctum (Fase 2)
SANCTUM_STATEFUL_DOMAINS=sisfokol-laravel.test
2.4 Isolasi Multi-Tenancy Berbasis Scope Eloquent
Persiapan arsitektur API-driven terbaik pada proyek ini terletak pada implementasi Multi-Tenancy-nya (ADR-003 dan BelongsToTenant trait).

Karena isolasi database dilakukan secara otomatis via Global Scope di tingkat Model, maka saat API Controller nantinya memanggil query seperti Siswa::all(), database secara otomatis hanya akan mengembalikan data milik sekolah (tenant) pengguna yang terautentikasi oleh token Sanctum.
Ini mengeliminasi risiko keamanan cross-tenant data leakage yang sangat rawan pada arsitektur API-driven tanpa isolasi otomatis.
3. KESENJANGAN KRITIS (GAPS) & TANTANGAN MIGRASI API
Apabila tim pengembang berniat mempercepat implementasi API-Driven, beberapa kesenjangan arsitektural dari Fase 1 ini harus diselesaikan terlebih dahulu:

3.1 Ketergantungan Impersonation pada Session Cookie
Masalah: Modul Impersonation (Login As) yang dikembangkan di Epic 2 menggunakan paket lab404/laravel-impersonate yang mengandalkan session cookies PHP di sisi server.
Tantangan: Pada arsitektur API-driven murni (stateless), session cookie tidak dikirim secara otomatis. Aplikasi membutuhkan kustomisasi alur pertukaran token (Token Swap / Oauth-like impersonation) untuk mengizinkan admin mendapatkan token Sanctum sementara atas nama user target.
3.2 Penyatuan File Rute di Tingkat Modul
Masalah: Saat ini, setiap core module di app/Modules/ (seperti Academic, Finance, Presence) hanya memiliki satu file rute tunggal, yaitu routes.php yang diasosiasikan secara global ke rute web (Blade).
Tantangan: Jika rute API langsung ditumpuk di file yang sama, deklarasi rute akan menjadi berantakan (spaghetti). Perlu didefinisikan pemisahan rute modular sejak awal, misalnya routes_web.php dan routes_api.php.
3.3 Integrasi Plugin UI Dinamis pada SPA
Masalah: Rencana infrastruktur plugin (ADR-009) berfokus pada rendering menu Blade secara dinamis menggunakan MenuRenderer di sisi server PHP.
Tantangan: Pada arsitektur API-driven murni, frontend (misal: React/Vue) tidak bisa mengeksekusi kode Blade. Sistem plugin harus dimodifikasi agar mengembalikan representasi skema UI (JSON UI Schema) sehingga frontend dapat menyusun rute navigasi dan tombol secara dinamis di sisi client.
4. REKOMENDASI STRATEGIS & LANGKAH TRANSISI
Untuk mengarahkan SISFOKOL v7 menuju arsitektur API-driven seutuhnya tanpa mengorbankan stabilitas kode yang sudah berjalan, direkomendasikan 3 langkah strategis berikut:

mermaid

flowchart TD
    A[Monolith Blade SSR - Fase 1] --> B[Adopsi Inertia.js - Transisi Cepat]
    B --> C[Headless API-Driven Terpisah - Fase 2]
    
    subgraph Transisi_Inertia
        B1[Ubah controller mengembalikan Inertia::render]
        B2[Tulis ulang view menggunakan Vue/React]
        B3[Pertahankan Session Auth & Security]
    end
    
    subgraph Headless_REST_API
        C1[Tulis API Resource app/Http/Resources]
        C2[Gunakan Sanctum Token Stateless]
        C3[Dekopel penuh frontend SPA & CDN]
    end
Langkah 1: Gunakan Inertia.js sebagai Jembatan SPA (Rekomendasi Terbaik)
Dibanding langsung memisahkan backend dan frontend ke repositori terpisah, gunakan Inertia.js dengan Vue.js atau React.

Keuntungan: Developer tetap menggunakan routing, auth session, Spatie permission, dan controller Laravel seperti biasa, tetapi antarmuka pengguna dirender sebagai Single Page Application (SPA). Ini adalah langkah termurah dan paling minim risiko untuk mendapatkan rasa "API-driven" tanpa kehilangan ekosistem kokoh Laravel.
Langkah 2: Modularisasi API Rute per Modul
Pisahkan rute modular secara eksplisit. Daftarkan file rute API di dalam ModuleServiceProvider agar otomatis termuat:

PHP

// Modifikasi di ModuleServiceProvider
if (file_exists($modulePath . '/routes_api.php')) {
    Route::middleware('api')
        ->prefix('api/v1')
        ->group($modulePath . '/routes_api.php');
}
Langkah 3: Ekstraksi Data Transfer Object (DTO) & API Resources
Mulai biasakan memisahkan logika query database dari logika penyajian data. Gunakan Laravel API Resources untuk membungkus data model sebelum dikirimkan ke client. Ini akan menjamin integritas struktur payload JSON di masa depan sekalipun skema database internal berubah.