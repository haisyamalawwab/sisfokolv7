# Rencana Pengamanan Parameter URL Presence Rekap

Rencana ini mengevaluasi apakah pengamanan parameter pada halaman Rekap Presensi (`/presence/rekap?date=&status=present`) perlu diterapkan, tingkat ancaman nyata, serta opsi solusi terbaik dari sisi efektivitas, efisiensi, dan kenyamanan pengguna (*user experience*).

---

## Analisis Risiko & Kebutuhan

Secara default, parameter URL menggunakan metode `GET` digunakan untuk filter data. Risiko yang mungkin terjadi adalah:
1. **URL Tampering (Parameter Guessing)**: Penyerang mengubah nilai status/date secara acak.
2. **SQL Injection (SQLi)**: Penyerang memasukkan skrip SQL berbahaya melalui parameter input.
3. **Automated URL Injection Testing**: Bot memindai endpoint berulang kali dengan payload berbahaya.

> **Catatan:**
> Karena database diakses menggunakan **Laravel Eloquent ORM** dengan parameter binding, SQL Injection secara teoritis sudah dicegah oleh framework. Namun, pengetatan validasi tipe data tetap direkomendasikan sebagai garis pertahanan pertama (*Defense in Depth*).

---

## Opsi Implementasi

### Opsi A — Tetap GET + Validasi Ketat & Rate Limiting (Direkomendasikan)
Menjaga parameter tetap di URL (GET), namun memperketat filter di sisi controller dan membatasi request per IP.

- **Perubahan**:
  - Menambahkan aturan validasi yang sangat ketat pada `date` (format tanggal `Y-m-d`) dan `status` (`in:present,late,early`).
  - Menambahkan middleware `throttle` (rate limiter) pada route `/presence/rekap` untuk mencegah automated scanning.
- **Kelebihan**: Sangat efisien, pengerjaan cepat (< 10 baris kode), halaman tetap bisa di-bookmark/dibagikan (*shareable*).
- **Kekurangan**: Parameter filter masih terlihat di address bar browser.

### Opsi B — Menggunakan Metode POST & Session
Menyembunyikan parameter dari URL dengan memindahkan metode pengiriman ke `POST` dan menyimpan state filter di PHP Session.

- **Perubahan**:
  - Mengubah form filter menjadi `POST`.
  - Membuat route `POST` baru untuk menyimpan filter ke dalam `session()`.
  - Mengubah route `GET` `/presence/rekap` untuk membaca filter dari session.
- **Kelebihan**: URL bersih tanpa parameter (`/presence/rekap`), parameter tidak terekspos di address bar.
- **Kekurangan**: Menghilangkan kemampuan bookmark/copy-paste URL filter langsung, menambah kompleksitas pemeliharaan session.

### Opsi C — Migrasi Penuh ke Livewire
Membuat halaman rekap presensi menjadi Livewire component.

- **Perubahan**:
  - Membuat komponen Livewire baru untuk Rekap Presensi.
  - Memanfaatkan state Livewire (`wire:model`) untuk filter data secara asinkron tanpa query binding ke URL.
- **Kelebihan**: Interaksi dinamis tanpa reload halaman, URL bersih secara default.
- **Kekurangan**: Membutuhkan penulisan ulang view dan logika controller ke Livewire.

---

## Rencana Perubahan (Opsi A - Direkomendasikan)

Jika disetujui, kami merekomendasikan **Opsi A** karena memberikan perlindungan nyata terhadap tebakan otomatis (via rate-limiting) dan pembersihan data (via validasi) tanpa merusak fungsionalitas pembagian laporan.

### 1. Modifikasi Controller
#### [MODIFY] [PresensiController.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Presence/Controllers/PresensiController.php)
Menambahkan validasi ketat di method `index()` sebelum query dijalankan:
```php
     public function index(Request $request)
     {
         Gate::authorize('viewAny', Attendance::class);
 
         // Validasi input parameter secara ketat
         $request->validate([
             'date'   => 'nullable|date_format:Y-m-d',
             'status' => 'nullable|in:present,late,early',
         ]);

         $query = Attendance::with('attendable')
             ->latest('date');
```

### 2. Modifikasi Route
#### [MODIFY] [routes.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/app/Modules/Presence/routes.php)
Membatasi upaya brute-force dengan menambahkan middleware `throttle` (misal maks 60 request per menit):
```php
         // Rekap Kehadiran
         Route::get('/rekap', [PresensiController::class, 'index'])
             ->middleware('throttle:60,1')
             ->name('rekap');
```

---

## Rencana Verifikasi

### Automated Tests
- Menjalankan `php artisan test` untuk memastikan route rekap tetap berfungsi.

### Manual Verification
- Memasukkan input berbahaya ke URL (seperti `/presence/rekap?status=DROP TABLE`) dan memastikan server mengembalikan error validasi (`422` atau redirect kembali dengan error) alih-alih mengeksekusinya.
- Mengakses halaman `/presence/rekap` secara cepat berulang-ulang untuk memicu limiter `429 Too Many Requests`.
