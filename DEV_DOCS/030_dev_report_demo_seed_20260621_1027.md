# DEV_DOCS-030: Dev Report — Demo Seeder & Database Reset

- **Tanggal:** 2026-06-21 10:27
- **Status:** ✅ SELESAI
- **Penulis:** Antigravity (Google DeepMind)
- **Proyek:** SISFOKOL v7 Laravel — Modular Monolith

---

## Tindakan yang Dilakukan

1. Membuat `DemoSeeder.php` baru di `database/seeders/`
2. Mendaftarkan `DemoSeeder` ke `DatabaseSeeder.php`
3. Menjalankan `php83 artisan migrate:fresh --seed`

**Hasil:** Semua migrasi dan seeder berjalan sukses ✅

---

## 🎯 Akun Demo — Langsung Bisa Login

Aplikasi berjalan di: `http://127.0.0.1:8000`

| Role | Username | Password | URL Dashboard |
|------|----------|----------|---------------|
| **SuperAdmin** | `superadmin` | `SuperAdmin#2026` | `/dashboard` |
| **Admin Sekolah** (global) | `admin` | `password` | `/admin/dashboard` |
| **Admin Sekolah** (tenant) | `admin.sekolah` | `demo1234` | `/admin/dashboard` |
| **Guru Piket** | `piket.demo` | `demo1234` | `/picket/dashboard` |
| **Guru BK / Counselor** | `bk.demo` | `demo1234` | `/counselor/dashboard` |
| **Guru Mapel** | `guru.demo` | `demo1234` | `/teacher/dashboard` |
| **Wali Kelas** | `walikelas.demo` | `demo1234` | `/homeroom/dashboard` |
| **Siswa (contoh)** | `siswa.2024001` | `demo1234` | `/student/dashboard` |

---

## 📦 Data Demo yang Tersedia

| Item | Detail |
|------|--------|
| **Tenant** | 1 — SMA Demo Sisfokol (NPSN: 20000001) |
| **Siswa Aktif** | 20 siswa (NIS: 2024001 – 2024020) |
| **Data Presensi** | 7 hari weekday terakhir (~18 siswa/hari hadir, 3 terlambat, 2 alpa) |
| **Data Izin** | 6 record: 3 `pending`, 2 `approved`, 1 `rejected` |
| **Kelas** | 7 kelas (X IPA 1, X IPA 2, X IPS 1, XI IPA 1, XI IPS 1, XII IPA 1, XII IPS 1) |
| **Jam Presensi** | Masuk: 06:30–07:30 · Pulang: 14:00–15:00 |

---

## 🔗 URL untuk Test UI/UX Presence Module

> Login sebagai **`piket.demo`** (password: `demo1234`), lalu akses URL berikut:

| Halaman | URL |
|---------|-----|
| 📷 Scanner QR Real-time | `/presence/scan` |
| 📊 Rekap Kehadiran | `/presence/rekap` |
| 📋 Daftar Izin | `/presence/izin` |
| 📝 Form Pengajuan Izin Baru | `/presence/izin/create` |
| 📈 Laporan Presensi Bulanan | `/presence/laporan` |

> **💡 Tip scan manual:** Masukkan NIS siswa (contoh: `2024001`) di field input manual pada halaman scanner untuk test presensi tanpa kamera fisik.

---

## 🔗 URL untuk Test Approval Izin

> Login sebagai **`bk.demo`** (password: `demo1234`), lalu:

1. Buka `/presence/izin` → filter status **Menunggu**
2. Klik **Detail** pada salah satu izin `pending`
3. Klik **Setujui Izin** atau **Tolak Izin** (isi alasan penolakan)
4. Verifikasi status berubah menjadi `approved` / `rejected`

---

## File yang Dibuat / Diubah

| File | Keterangan |
|------|------------|
| `database/seeders/DemoSeeder.php` | **[NEW]** Seeder demo lengkap semua role + tenant + siswa + presensi + izin |
| `database/seeders/DatabaseSeeder.php` | **[MODIFY]** Tambahkan `DemoSeeder::class` setelah `UserSeeder::class` |

---

## Output Seeder (Ringkasan)

```
Database\Seeders\RolePermissionSeeder  ................. 966 ms  DONE
Database\Seeders\SchoolProfileSeeder   ................. 6 ms    DONE
Database\Seeders\AcademicYearSeeder    ................. 6 ms    DONE
Database\Seeders\DaySeeder             ................. 28 ms   DONE
Database\Seeders\HourSeeder            ................. 48 ms   DONE
Database\Seeders\TimeSlotSeeder        ................. 55 ms   DONE
Database\Seeders\SubjectTypeSeeder     ................. 24 ms   DONE
Database\Seeders\AttendanceTimeSeeder  ................. 12 ms   DONE
Database\Seeders\UserSeeder            ................. 585 ms  DONE
Database\Seeders\DemoSeeder            ................. 7,885 ms DONE  ✅
Database\Seeders\ClassroomSeeder       ................. 40 ms   DONE
Database\Seeders\MenuSeeder            ................. 107 ms  DONE
Database\Seeders\FieldSeeder           ................. 54 ms   DONE
```

---

## Cara Reset Ulang Data Demo

Jika ingin reset database ke kondisi bersih + data demo ulang:

```powershell
php83 artisan migrate:fresh --seed
```

> ⚠️ Perintah ini akan **menghapus semua data** di database dan mengisi ulang dari awal.
