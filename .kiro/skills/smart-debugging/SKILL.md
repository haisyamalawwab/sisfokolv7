---
name: smart-debugging
description: Metodologi debugging cerdas untuk aplikasi Laravel/PHP — selalu mulai dari hal paling sederhana, baru ke hal kompleks. Gunakan skill ini setiap kali ada error yang perlu diinvestigasi.
---

# Smart Debugging — Simple First, Complex Last

## Prinsip Dasar

> **"Error yang terlihat kompleks hampir selalu punya penyebab yang sangat sederhana."**

Agentic coding sering gagal karena langsung melompat ke analisis kompleks (git history, konfigurasi server, arsitektur) padahal penyebabnya bisa sesederhana tag `<?php` yang hilang. Skill ini memaksa pendekatan berlapis dari yang termudah.

---

## Protokol Investigasi Berlapis

### 🔴 Layer 1 — Syntax & File Dasar (WAJIB CEK PERTAMA, < 2 menit)

**Untuk SETIAP error di aplikasi PHP/Laravel, selalu mulai di sini:**

```
Checklist Layer 1:
□ Buka file PHP yang paling langsung relevan
□ Apakah baris 1 adalah `<?php`? (PALING SERING TERLEWAT)
□ Apakah file tidak kosong/terpotong?
□ Adakah syntax error kasat mata?
□ Apakah file tersimpan/tidak ada perubahan yang belum disimpan?
```

**Contoh kasus nyata:** Error "Not Found" di Laravel — penyebabnya `<?php` hilang dari `routes/web.php`. Tidak ada pesan error sama sekali karena PHP tidak membaca file itu sebagai PHP.

---

### 🟠 Layer 2 — Routing & Endpoint (CEK KEDUA)

**Khusus untuk error: 404 Not Found, Route Not Found, Method Not Allowed**

```
Checklist Layer 2:
□ Buka routes/web.php dan routes/api.php secara visual
□ Apakah route untuk URL yang error ADA di file?
□ Apakah route '/' (root) ada? (sering hilang tanpa disadari)
□ Apakah nama controller dan method di route sudah benar?
□ Apakah ada typo pada nama route?
□ Jalankan: php artisan route:list | grep [keyword]
```

---

### 🟡 Layer 3 — Perbandingan Versi (CEK KETIGA)

**Gunakan jika ada versi lain yang berjalan dengan baik:**

```
Langkah:
1. Identifikasi file kunci yang berbeda antara versi lama (berjalan) vs baru (error)
2. Bandingkan langsung: Compare-Object atau diff
3. Fokus pada APA YANG HILANG dari versi baru
4. Jangan asumsi — lihat perbedaan konkret
```

**Command cepat:**
```powershell
Compare-Object (Get-Content "versilama\core\routes\web.php") (Get-Content "versinew\core\routes\web.php")
```

---

### 🟢 Layer 4 — Log & Cache (CEK KEEMPAT)

**Untuk error 500, blank page, atau error yang tidak jelas:**

```
Langkah:
1. Baca log: storage/logs/laravel.log (baris paling bawah = error terbaru)
2. Clear semua cache:
   php artisan route:clear
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
3. Pastikan APP_DEBUG=true di .env untuk melihat error detail
```

---

### 🔵 Layer 5 — Database & Model (CEK KELIMA)

**Untuk error berhubungan dengan data, query, atau model:**

```
Checklist Layer 5:
□ Apakah tabel yang direferensikan ADA di database?
□ Apakah kolom yang di-query ada di tabel?
□ Apakah foreign key constraint terpenuhi?
□ Cek DB credentials di .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
□ Jalankan query secara manual di MySQL client untuk verifikasi
```

---

### ⚫ Layer 6 — Infrastruktur (TERAKHIR)

**Baru masuk ke sini jika Layer 1-5 tidak menemukan masalah:**

```
Langkah:
1. Cek konfigurasi Apache/Nginx (.htaccess, VirtualHost)
2. Cek AllowOverride di httpd.conf
3. Cek versi PHP yang aktif
4. Cek git history untuk commit yang mengubah perilaku
5. Cek dependency dan composer.json
```

---

## Tabel Quick Reference — Error Type → Layer Prioritas

| Error | Mulai dari Layer |
|-------|-----------------|
| 404 Not Found | **Layer 1** → cek `<?php`, lalu **Layer 2** → cek route |
| 500 Internal Server Error | **Layer 1** → cek syntax, lalu **Layer 4** → cek log |
| Blank/White Page | **Layer 1** → file ada?, lalu **Layer 4** → lihat log |
| Route tidak ditemukan | **Layer 2** → cek routes/web.php |
| DB Error / Query Error | **Layer 5** → cek tabel & kolom |
| App tidak load sama sekali | **Layer 1** → cek index.php dan bootstrap/app.php |
| Redirect loop | **Layer 2** → cek route middleware dan redirect |

---

## ⭐ Prinsip #1 — COMMENT, Jangan DELETE

**Kode lama di-comment, bukan dihapus. Sertakan: tanggal, inisial, alasan singkat. Seperlunya.**

```php
// [2026-02-19 | RW] ganti dengan filter institusi
// $query = DB::table('assets')->get(); // nonaktif
$query = DB::table('assets')
    ->leftJoin('location', 'location.id', '=', 'assets.locationid')
    ->get();

// [2026-02-19 | AI-Agent] tambah route baru, jangan hapus yang lama
Route::get('home/assetbycategory', 'Home@assetbycategory');
```

**Format:** `// [YYYY-MM-DD | Inisial] alasan singkat`

Rollback = uncomment lama + hapus baru. Selesai. Tidak perlu git, tidak perlu AI.

---

## Safe File Editing Protocol

Kesalahan paling merusak dalam agentic coding adalah **menghapus kode yang sudah ada** saat menambahkan fitur baru. Ikuti protokol ini:

### Sebelum Edit File Apapun
```
1. view_file — baca SELURUH file dulu
2. Catat konten penting yang sudah ada (terutama baris 1 dan struktur awal)
3. Gunakan multi_replace_file_content untuk edit SPESIFIK
4. JANGAN gunakan write_to_file (Overwrite: true) kecuali memang ingin replace total
```

### Setelah Edit File PHP
```
□ Verifikasi baris 1 masih `<?php`
□ File tidak lebih pendek dari sebelumnya tanpa alasan
□ Konten yang sudah ada sebelum edit masih ada
```

### Khusus routes/web.php — File Paling Kritis
```
WAJIB setelah setiap perubahan:
□ Baris 1: <?php
□ Route::get('/', ...) ADA
□ Route::get('/home', ...) ADA
□ Semua page routes di bagian atas masih ada
□ Tidak ada page routes yang terpotong
```

---

## Anti-Pattern yang Harus Dihindari

❌ **JANGAN** langsung cek git history, Apache config, atau Windows settings sebelum cek file relevan
❌ **JANGAN** replace seluruh isi file saat hanya perlu menambah beberapa baris
❌ **JANGAN** asumsi penyebab error tanpa melihat file yang bersangkutan secara langsung
❌ **JANGAN** skip Layer 1 meski error terlihat "pasti" bukan masalah syntax

✅ **SELALU** buka dan baca file yang paling langsung relevan dengan error
✅ **SELALU** bandingkan dengan versi yang berjalan jika tersedia
✅ **SELALU** verifikasi `<?php` ada sebelum dan sesudah edit file PHP
✅ **SELALU** selesaikan Layer sebelumnya sebelum pindah ke Layer berikutnya
