# SISFOKOL v7.00 - Laravel Modern Edition (SmartOffice & SaaS)

[![Laravel v11](https://img.shields.io/badge/Laravel-v11.x-red.svg)](https://laravel.com)
[![PHP v8.3](https://img.shields.io/badge/PHP-v8.3.x-blue.svg)](https://php.net)
[![Database MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)

![CodeRabbit Pull Request Reviews](https://img.shields.io/coderabbit/prs/github/haisyamalawwab/sisfokolv7?utm_source=oss&utm_medium=github&utm_campaign=haisyamalawwab%2Fsisfokolv7&labelColor=171717&color=FF570A&link=https%3A%2F%2Fcoderabbit.ai&label=CodeRabbit+Reviews)

**NEO SISFOKOL Zero (Laravel Modern Edition)** adalah pembangunan ulang (*total rebuild*) secara menyeluruh dari aplikasi legacy SISFOKOL v7.00 (PHP Native, MyISAM) menjadi aplikasi web modern berbasis **Laravel 11 Domain-Modular Monolith** yang dirancang khusus untuk memenuhi standar keamanan tinggi, skalabilitas multi-sekolah (SaaS Multi-Tenant), serta performa optimal (*SmartOffice*).

Aplikasi ini berada pada folder: `sisfokol-laravel/`

---

## 🏛️ Arsitektur Sistem & Keputusan Desain (ADR)

Pengembangan ini didasarkan pada 10 keputusan arsitektur utama (**Architecture Decision Records - ADR**):

1. **[ADR-002] Rebuild Total ke Laravel 11 Modular Monolith**: Menghilangkan seluruh hutang teknis legacy (*MD5 hash, MyISAM engine, SQL injection vulnerability*).
2. **[ADR-003] Multi-Tenant SaaS (Shared Database)**: Membagi akses data antar tenant sekolah secara logis menggunakan kolom `tenant_id` dan diisolasi secara otomatis via Eloquent Global Scope (`BelongsToTenant`).
3. **[ADR-005] Impersonation "Login As"**: Memungkinkan SuperAdmin / Admin Sekolah login sebagai pengguna lain secara hierarkis ke bawah dengan banner pengaman dan pencatatan audit log yang immutable.
4. **[ADR-006] Granular Database-Driven RBAC**: Menggunakan Spatie Permission dengan mode Teams (`team_id = tenant_id`) untuk pembagian hak akses granular.
5. **[ADR-007] Normalisasi Skema Database**: Menggunakan InnoDB engine, Primary Key berbasis BIGINT Auto-Increment, Foreign Key, Soft Deletes, kolom Audit (`created_by`, `updated_by`), serta standardisasi tipe data keuangan (`decimal`).
6. **[ADR-009] Plugin Contract Plug-and-Play**: Arsitektur modular di mana modul opsional dapat diaktifkan/dinonaktifkan per tenant tanpa mengganggu stabilitas core platform.
7. **[ADR-010] RBAC Menjangkau Menu & Field-Level ACL**: Membatasi visibilitas menu sidebar dan atribut kolom sensitif di UI langsung melalui manajemen database.

---

## 🏗️ Struktur Folder Proyek

Struktur folder mengadopsi pendekatan **Domain-Modular Monolith** dengan membagi kode ke dalam Domain Modules dan Plugins:

```
sisfokol-laravel/
├── app/
│   ├── Modules/              # Modul Core (Selalu Aktif)
│   │   ├── Tenancy/          # Manajemen Tenant, Branch, & Settings
│   │   ├── Auth/             # Otentikasi, Impersonation, Audit Logs, & RBAC
│   │   ├── Academic/         # Siswa, Guru, Kelas, Mapel, Jadwal (11 Tabel)
│   │   ├── Evaluation/       # Nilai Formatif, Sumatif, Cetak Rapor (7 Tabel)
│   │   ├── Finance/          # Keuangan, Tagihan, Pembayaran, Tabungan (5 Tabel)
│   │   └── Presence/         # Kehadiran QrCode, Absensi, Izin (3 Tabel)
│   ├── Plugins/              # Modul Plug-and-Play (Dinamis per Tenant)
│   │   └── Kurikulum/        # Referensi penuh (Kurikulum Merdeka / K13)
│   └── Support/              # Kelas Utility (FieldAcl, MenuRenderer, PluginRegistry)
├── config/                   # Berkas Konfigurasi Utama
├── database/                 # Migrasi Global & Seeders
├── resources/                # Front-End (Blade Views, CSS/JS Vite, Alpine.js)
├── routes/                   # Routing System
└── tests/                    # Feature & Unit Testing
```

---

## 🛠️ Stack Teknologi Modern

| Layer | Teknologi | Versi | Catatan / Kegunaan |
| :--- | :--- | :--- | :--- |
| **Bahasa Utama** | PHP | `8.3.x` | Diwajibkan untuk menjalankan Laravel 11 secara stabil. |
| **Framework** | Laravel | `11.x` | Modern, clean routing, ORM Eloquent tangguh. |
| **Engine DB** | MySQL / MariaDB | `8.0+ / 10.6+` | Wajib menggunakan **InnoDB** untuk integritas FK & Transaksi. |
| **Otorisasi (RBAC)** | Spatie Laravel Permission | `6.x` | Diintegrasikan dengan fitur Teams berbasis `tenant_id`. |
| **Impersonation** | Lab404 Impersonate | `1.7.x` | Login As terintegrasi pengaman `BlockWhileImpersonating`. |
| **PDF Renderer** | DomPDF | `3.1.x` | Untuk ekspor Rapor PDF resmi, kuitansi bayar, rekap presensi. |
| **Excel Handler** | Maatwebsite Excel | `3.1.x` | Untuk impor dan ekspor data masal (siswa/guru/mapel). |
| **QR Code Generator** | Simple QrCode | `4.x` | Untuk generate QRCode presensi & kartu tanda siswa. |
| **Front-End Stack** | Blade + Bootstrap 5 + Alpine.js | Latest | Antarmuka interaktif, ringan, responsive, dan ramah plugin. |
| **Build Tools** | Vite | `5.x` | Asset bundling modern pengganti Laravel Mix. |

---

## ⚙️ Panduan Menjalankan Aplikasi di Lokal (Laragon)

### Prasyarat
1. **Laragon** dengan PHP versi **8.3.x** terpasang.
2. Port database default MySQL `3306` aktif.

### Langkah-Langkah Setup

1. **Konfigurasi Virtual Host Laragon**
   Secara default, Laragon akan mendeteksi folder `sisfokol-laravel/public` dan membuat virtual domain lokal:
   * **URL:** `http://sisfokol-laravel.test`
   * **Alt Port (Artisan Serve):** `php83 artisan serve` -> `http://127.0.0.1:8000`

2. **Buat Database**
   Buat database baru di MySQL dengan nama:
   ```sql
   CREATE DATABASE sisfokol_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Duplikasi Konfigurasi Environment (`.env`)**
   Salin `.env.example` menjadi `.env` di dalam subfolder `sisfokol-laravel/` dan sesuaikan koneksi database utama serta koneksi database legacy untuk kebutuhan ETL:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sisfokol_laravel
   DB_USERNAME=root
   DB_PASSWORD=

   # Database Legacy (Read-Only Source untuk ETL)
   LEGACY_DB_CONNECTION=legacy_mysql
   LEGACY_DB_HOST=127.0.0.1
   LEGACY_DB_PORT=3306
   LEGACY_DB_DATABASE=sisfokol_v7
   LEGACY_DB_USERNAME=root
   LEGACY_DB_PASSWORD=
   ```

4. **Instal Dependensi Composer**
   Jalankan instalasi dependensi (gunakan PHP 8.3 sesuai path binary sistem Anda):
   ```bash
   cd sisfokol-laravel
   php83 D:\composer\composer.phar install
   ```

5. **Migrasi & Seeding Awal**
   Jalankan migrasi tabel database beserta data seeders default (SuperAdmin, profil sekolah demo, RBAC metadata, menu, & field ACL):
   ```bash
   php83 artisan migrate --seed
   ```

6. **Instal Dependensi & Jalankan Front-End Assets**
   Instal paket Node.js dan jalankan development server Vite:
   ```bash
   npm install
   npm run dev
   ```

7. **Jalankan Uji Coba Otomatis (Testing)**
   Pastikan seluruh test cases berjalan dengan sukses (hijau):
   ```bash
   php83 artisan test
   ```

---

## 🎬 Skenario & Kredensial Akun Demo

Gunakan data akun demo ter-seed berikut untuk melakukan demonstrasi alur kerja aplikasi:

| Peran (Role) | Username | Password | Deskripsi Kasus Uji / Skenario |
| :--- | :--- | :--- | :--- |
| **SuperAdmin** | `superadmin` | `SuperAdmin#2026` | Mengelola data Master Tenant (Sekolah), Cabang (*Branch*), aktivasi Plugin global, & audit log platform. |
| **Admin Sekolah** | `admin.sekolah` | `demo1234` | Mengelola data internal sekolah, mengesahkan wali kelas/guru, memetakan RBAC, mengatur bobot penilaian rapor. |
| **Guru Mapel** | `guru.demo` | `demo1234` | Melakukan **real-time AJAX auto-save** nilai Formatif (40%) & Sumatif (60%) di Grid Penilaian berbasis **Alpine.js**. |
| **Wali Kelas** | `walikelas.demo` | `demo1234` | Menerima rekap presensi kelas, melakukan review nilai, & mencetak Rapor PDF resmi siswa. |
| **Siswa** | `siswa.2024001` | `demo1234` | Melihat kehadiran harian secara personal & mengunduh Rapor PDF resmi yang telah diunggah. |

*Untuk login, arahkan peramban Anda ke: `http://sisfokol-laravel.test/login`*

---

## 🔄 Pipeline ETL (Migrasi Data Legacy)

Untuk memindahkan data dari database SISFOKOL v7 lama (`sisfokol_v7`) ke database modern (`sisfokol_laravel`), jalankan command konsol ETL:

```bash
php83 artisan db:migrate-legacy
```

**Aturan Migrasi ETL:**
* Dilakukan dalam 20 tahap logis (*topological steps*) untuk menjaga integritas relasional data (*foreign keys*).
* Semua *password* pengguna legacy yang dimigrasi akan diset secara otomatis ke kondisi **`must_reset_password = true`** untuk alasan keamanan. Pengguna diwajibkan mengganti kata sandi mereka pada saat pertama kali login di platform Laravel baru.
* Lakukan verifikasi hasil migrasi data dengan perintah:
  ```bash
  php83 artisan etl:verify
  ```

---

## 📑 Referensi & Dokumentasi Teknis

Semua detail keputusan teknis, arsitektur, dan rekam jejak migrasi tercatat lengkap pada direktori:
* **[ADR/ (Architecture Decision Records)](file:///d:/laragon/www/sisfokolv7/ADR)** — Berisi 10 berkas dokumentasi keputusan fundamental.
* **[DEV_DOCS/ (Developer Handover logs)](file:///d:/laragon/www/sisfokolv7/DEV_DOCS)** — Laporan progress antar sesi, panduan integrasi modul, audit bug, dan handover antar AI-Agent.
