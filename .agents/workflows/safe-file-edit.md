---
description: Protokol wajib sebelum dan sesudah mengedit file kritis untuk mencegah penghapusan kode yang sudah ada
---

# Safe File Edit Protocol

// turbo-all

Workflow ini WAJIB dijalankan saat mengedit file-file kritis di project ini. Tujuannya mencegah LLM menghapus kode penting secara tidak sengaja saat menambahkan fitur baru.

## File Kritis yang Dilindungi

| File | Yang Tidak Boleh Hilang |
|------|------------------------|
| `core/routes/web.php` | `<?php` di baris 1, `Route::get('/')`, semua page routes |
| `core/routes/api.php` | `<?php` di baris 1 |
| `core/bootstrap/app.php` | Seluruh binding singleton |
| `core/.env` | Semua variabel konfigurasi |
| `index.php` | Tidak boleh diubah tanpa alasan kuat |
| `.htaccess` | Tidak boleh diubah tanpa alasan kuat |

---

## ⭐ Prinsip Utama — COMMENT, Jangan DELETE

**Kode lama di-comment, bukan dihapus. Cukup: tanggal, inisial, alasan singkat.**

```php
// [2026-02-19 | AI-Agent] tambah route filter institusi
Route::get('home/assetbycategory', 'Home@assetbycategory');

// [2026-02-19 | RW] nonaktifkan query lama
// $query = DB::table('assets')->get(); // nonaktif
$query = DB::table('assets')->leftJoin('location', ...)->get();
```

**Format:** `// [YYYY-MM-DD | Inisial] alasan singkat`  
Rollback = uncomment lama + hapus baru. Selesai.

---

## Langkah 1 — Baca File LENGKAP Sebelum Edit

Sebelum mengedit file apapun:

```powershell
# Catat jumlah baris saat ini
(Get-Content "d:\laragon\www\massetyppiwm\core\routes\web.php").Count
```

**Aturan:** Jangan mulai edit sebelum membaca seluruh isi file dengan `view_file`.

---

## Langkah 2 — Gunakan Edit SPESIFIK, Bukan Replace Besar

**WAJIB menggunakan `multi_replace_file_content`** untuk perubahan spesifik.

❌ DILARANG:
```
write_to_file dengan Overwrite: true  → menghapus seluruh file
replace_file_content dengan TargetContent yang mencakup banyak baris penting
```

✅ WAJIB:
```
multi_replace_file_content → hanya ubah baris yang perlu diubah
replace_file_content dengan TargetContent MINIMAL dan SPESIFIK
Untuk tambah routes baru → append di bagian yang relevan, jangan replace header
```

---

## Langkah 3 — Verifikasi Setelah Edit

Setelah setiap edit pada file kritis:

```powershell
# 1. Cek jumlah baris — harus >= jumlah sebelum edit (kecuali memang ada penghapusan)
(Get-Content "d:\laragon\www\massetyppiwm\core\routes\web.php").Count

# 2. Cek <?php ada di baris 1
(Get-Content "d:\laragon\www\massetyppiwm\core\routes\web.php")[0]

# 3. Cek route root masih ada
Select-String "Route::get\('/'," "d:\laragon\www\massetyppiwm\core\routes\web.php"
```

Jika baris berkurang tanpa alasan yang jelas → **STOP, restore dari git, investigasi ulang.**

---

## Langkah 4 — Restore Jika Ada yang Hilang

```powershell
# Lihat apa yang berubah
git diff core/routes/web.php

# Restore ke commit sebelumnya jika perlu
git checkout HEAD~1 -- core/routes/web.php

# Clear route cache setelah restore
& "D:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe" artisan route:clear
```

---

## Checklist Akhir — Wajib Dicentang Sebelum Selesai

- [ ] `<?php` ada di baris 1 pada semua file PHP yang diedit
- [ ] Jumlah baris file tidak berkurang tanpa alasan
- [ ] Route `'/'` dan `'/home'` masih ada di `web.php`
- [ ] Semua page routes (`/brandlist`, `/assetlist`, dll) masih ada
- [ ] Semua report routes (`/reports/*`) masih ada
- [ ] `route:clear` dijalankan setelah perubahan routes
- [ ] `cache:clear` dijalankan setelah perubahan konfigurasi
