# DEV_DOCS-033: Dev Report — Perbaikan Seeder, Pendaftaran SuperAdmin & Validasi Auth

- **Tanggal:** 2026-06-21 11:11
- **Status:** ✅ SELESAI
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** SISFOKOL v7 Laravel — Modular Monolith

---

## 🛠️ Perbaikan yang Dilakukan

1. **Pendaftaran SuperAdmin ke DatabaseSeeder**
   * **Masalah:** Berkas `SuperAdminSeeder` tidak dipanggil di `DatabaseSeeder.php`, sehingga akun SuperAdmin (`superadmin` / `SuperAdmin#2026`) tidak dibuat saat menjalankan perintah `db:seed`.
   * **Solusi:** Menambahkan `SuperAdminSeeder::class` tepat setelah `RolePermissionSeeder::class` di dalam [DatabaseSeeder.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/database/seeders/DatabaseSeeder.php).

2. **Resolusi Dynamic Day & TimeSlot di DemoSeeder**
   * **Masalah:** Penulisan `'day_id' => 1` dan `'time_slot_id' => 1` yang di-hardcode pada pembuatan jadwal di `DemoSeeder` menyebabkan kegagalan foreign key constraint saat dijalankan di pengujian otomatis PHPUnit. Hal ini terjadi karena MySQL InnoDB tidak me-reset counter auto-increment saat melakukan rollback transaksi test, sehingga pada pengujian kedua ID dari hari/slot bergeser (misal menjadi 8-14).
   * **Solusi:** Mengubah penetapan nilai menjadi dinamis berdasarkan pencarian data asli di database:
     ```php
     $day = \App\Models\Day::where('number', 1)->first() ?? \App\Models\Day::first();
     $timeSlot = \App\Models\TimeSlot::first();
     ```
     Ini menjamin seeder selalu berhasil dijalankan dalam keadaan apa pun tanpa melanggar foreign key constraint.

3. **Penulisan Automated Test Auth Login Terpadu**
   * **Tindakan:** Membuat file pengujian fitur baru di [SeededUsersLoginTest.php](file:///d:/laragon/www/sisfokolv7/sisfokol-laravel/tests/Feature/Auth/SeededUsersLoginTest.php) yang memvalidasi otentikasi login untuk seluruh 8 pengguna demo bawaan seeder:
     1. `superadmin` (SuperAdmin)
     2. `admin` (Admin Sekolah Global)
     3. `admin.sekolah` (Admin Sekolah Tenant)
     4. `piket.demo` (Guru Piket)
     5. `bk.demo` (Guru BK)
     6. `guru.demo` (Guru Mapel)
     7. `walikelas.demo` (Wali Kelas)
     8. `siswa.2024001` (Siswa Demo)
   * **Hasil Test:** Seluruh 8 pengujian berhasil lulus **(100% Passing / Green)**.

---

## 🎯 Kredensial Login Teruji & Aktif

| Peran (Role) | Username | Password | Status Pengujian |
| :--- | :--- | :--- | :--- |
| **SuperAdmin** | `superadmin` | `SuperAdmin#2026` | ✅ Passed |
| **Admin Sekolah** | `admin` | `password` | ✅ Passed |
| **Admin (Tenant)** | `admin.sekolah` | `demo1234` | ✅ Passed |
| **Guru Piket** | `piket.demo` | `demo1234` | ✅ Passed |
| **Guru BK** | `bk.demo` | `demo1234` | ✅ Passed |
| **Guru Mapel** | `guru.demo` | `demo1234` | ✅ Passed |
| **Wali Kelas** | `walikelas.demo` | `demo1234` | ✅ Passed |
| **Siswa (contoh)** | `siswa.2024001` | `demo1234` | ✅ Passed |
