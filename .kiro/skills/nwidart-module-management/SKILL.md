---
name: nwidart-module-management
description: >
  Panduan wajib saat menambah, mengaktifkan, atau menonaktifkan modul nwidart/laravel-modules
  di project SIMT Backend. Berisi pelajaran dari bug produksi dan checklist aman.
  Gunakan skill ini setiap kali ada operasi yang berkaitan dengan Modules/, modules_statuses.json,
  atau composer.json autoload di project ini.
---

# Nwidart Module Management — SIMT Backend

## ⚠️ Pelajaran Penting (dari Bug Produksi 2026-06-14)

> **Setiap kali menambah modul nwidart baru dan mengaktifkannya di `modules_statuses.json`,
> WAJIB jalankan `composer dump-autoload` agar namespace-nya masuk ke classmap PHP.**

Jika langkah ini dilewati, Laravel akan **crash saat startup** dengan error:
```
Class "Modules\NamaModul\Providers\NamaModulServiceProvider" not found
```
Error ini terjadi karena nwidart membaca `modules_statuses.json` dan mencoba
me-load provider modul — tetapi PHP tidak tahu letak file-nya karena classmap
belum diperbarui.

---

## Checklist Wajib Saat Menambah Modul Baru

### 1. Buat modul via artisan
```bash
php83 artisan module:make NamaModul
```

### 2. Daftarkan namespace di root `composer.json`
Tambahkan ke bagian `autoload.psr-4`:
```json
"Modules\\NamaModul\\": "Modules/NamaModul/app/",
"Modules\\NamaModul\\Database\\Seeders\\": "Modules/NamaModul/database/seeders/"
```

### 3. Aktifkan modul di `modules_statuses.json`
```json
{
    "Core": true,
    "Student": true,
    "Attendance": true,
    "Finance": true,
    "Notification": true,
    "NamaModul": true
}
```

### 4. ⚡ WAJIB — Regenerate autoload classmap
```bash
# Matikan artisan serve dulu jika sedang berjalan
# (agar tidak mengunci file dan menyebabkan composer hang)
php83 D:\laragon\bin\composer\composer.phar dump-autoload --optimize --no-scripts
```

### 5. Restart server
```bash
php83 artisan serve --port=8000
```

---

## Troubleshooting: `composer dump-autoload` Menggantung

Jika `composer dump-autoload` tidak selesai dan menggantung:

**Penyebab:** `artisan serve` masih berjalan dan mengunci proses PHP,
sehingga script `post-autoload-dump` (`php artisan package:discover`) tidak bisa jalan.

**Solusi:**
```powershell
# 1. Kill semua proses PHP yang berjalan
taskkill /f /im php.exe

# 2. Jalankan dump-autoload TANPA scripts
php83 D:\laragon\bin\composer\composer.phar dump-autoload --optimize --no-scripts

# 3. Restart server
php83 artisan serve --port=8000
```

---

## Modul yang Terdaftar di Project Ini

| Modul | Path | Status Default |
|---|---|---|
| `Core` | `Modules/Core/app/` | ✅ Selalu aktif |
| `Student` | `Modules/Student/app/` | ✅ Aktif |
| `Attendance` | `Modules/Attendance/app/` | ✅ Aktif |
| `Finance` | `Modules/Finance/app/` | ✅ Aktif |
| `Notification` | `Modules/Notification/app/` | ✅ Aktif |

---

## Catatan Arsitektur

- **Modul Core** = mandatory, tidak pernah dinonaktifkan via `modules_statuses.json`
- **Modul lainnya** = Plug & Play, bisa diaktifkan/nonaktifkan per tenant via tabel `tenant_modules`
- Guard autoload ini berlaku untuk **semua environment** (local Windows, WSL, Linux server)
