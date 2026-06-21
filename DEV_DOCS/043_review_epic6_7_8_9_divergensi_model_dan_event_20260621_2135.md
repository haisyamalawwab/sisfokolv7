# DEV_DOCS-043: Temuan Kritis Lintas Epic 6, 7, 8, dan 9 — Masalah Divergensi Model Ganda dan Event-Hook Terputus

- **Tanggal:** 2026-06-21 21:35
- **Status:** Temuan Kritis (Critical Finding)
- **Penulis:** Agent Mode (Arena.ai)
- **Topik:** Audit Mendalam Arsitektur Epic 6, 7, 8, dan 9 (Evaluation, Finance, Presence, & Kurikulum)
- **Terhubung ke ADR:** ADR-002, ADR-003, ADR-009, ADR-010
- **Terhubung ke DEV_DOCS:** DEV_DOCS-012, DEV_DOCS-039, DEV_DOCS-041, DEV_DOCS-042

---

## ⚡ EXECUTIVE SUMMARY: TEMUAN EMAS ARSITEKTUR

Melanjutkan analisis mendalam langsung pada codebase fisik di `sisfokol-laravel/`, kami menemukan **masalah struktural tingkat tinggi (high-level structural issues)** yang sangat krusial. Masalah ini menyebabkan fitur-fitur di **Epic 6, 7, 8, dan 9** terisolasi, mengalami tumpang tindih fungsi (*functional duplication*), bahkan berisiko mengalami kerusakan fatal (*crash*) saat runtime.

Ada dua temuan arsitektural utama yang sangat fatal:
1. **Divergensi Model Ganda ("Parallel Universes"):** Codebase ini memiliki dua set tabel database paralel untuk entitas yang sama (misalnya: `siswa` vs `students`, `kelas` vs `classrooms`, `mapel` vs `subjects`). Sebagian modul (Academic, modular Finance, dan Presence) berjalan di alam semesta tabel Indonesia, sedangkan sebagian lainnya (Evaluation, core Finance, dan Teacher) berjalan di alam semesta tabel Inggris. Keduanya tidak saling sinkron!
2. **Event-Hook Terputus (Epic 9 Lepas dari Core):** Seluruh arsitektur integrasi dinamis milik **Plugin Kurikulum (Epic 9)** mengandalkan event subscribers untuk menyuplai kompetensi harian dan mencetak rapor. Namun, **Core Evaluation (Epic 6) tidak pernah memicu (*dispatch*) event-event tersebut**, membuat Plugin Kurikulum terisolasi sepenuhnya dan tidak berfungsi pada aplikasi utama.

---

## 1. TEMUAN UTAMA: DIPOLARITAS MODEL DAN SKEMA DATABASE

Ini adalah hutang teknis terbesar di dalam codebase saat ini. Terdapat pembagian skema database menjadi dua kubu bahasa yang merepresentasikan entitas logis yang sama:

### 1.1 Kubu Bahasa Indonesia (Modular Layer)
Didefinisikan di `app/Modules/Academic/Database/Migrations/`. Menggunakan penamaan tabel tunggal Indonesia:
* Tabel `siswa` (`App\Modules\Academic\Models\Siswa`)
* Tabel `kelas` (`App\Modules\Academic\Models\Kelas`)
* Tabel `mapel` (`App\Modules\Academic\Models\Mapel`)
* Tabel `tahun_ajaran` (`App\Modules\Academic\Models\TahunAjaran`)

**Digunakan oleh:** Modul Akademik, Kasir Pembayaran Modular (`PembayaranController`), Riwayat Tabungan, dan Mesin Presensi Scan QR (`QrScannerService`).

### 1.2 Kubu Bahasa Inggris (Core / Legacy Layer)
Didefinisikan di `database/migrations/`. Menggunakan penamaan plural Inggris standar Laravel:
* Tabel `students` (`App\Models\Student`)
* Tabel `classrooms` (`App\Models\Classroom`)
* Tabel `subjects` (`App\Models\Subject`)
* Tabel `academic_years` (`App\Models\AcademicYear`)

**Digunakan oleh:** Penilaian Harian (`GradeEntryController`), Pencetakan Rapor PDF (`RaporGeneratorService`), Agenda Guru, Absensi Manual Piket, dan Konseling/BK.

```text
               ┌────────────────────────────────────────────────────────┐
               │              CODEBASE SISFOKOL-LARAVEL                 │
               └───────────────────┬────────────────────────────────────┘
                                   │
         ┌─────────────────────────┴─────────────────────────┐
         ▼                                                   ▼
┌─────────────────────────────────┐                 ┌─────────────────────────────────┐
│     ALAM SEMESTA INDONESIA      │                 │       ALAM SEMESTA INGGRIS      │
│  (Modular: Academic/Presence)   │                 │   (Core: Evaluation/Counseling) │
├─────────────────────────────────┤                 ├─────────────────────────────────┤
│ Model: Siswa   -> tabel 'siswa' │                 │ Model: Student  -> 'students'   │
│ Model: Kelas   -> tabel 'kelas' │                 │ Model: Classroom-> 'classrooms' │
│ Model: Mapel   -> tabel 'mapel' │                 │ Model: Subject  -> 'subjects'   │
└────────┬────────────────────────┘                 └────────┬────────────────────────┘
         │                                                   │
         ▼                                                   ▼
   Siswa registrasi,                                   Guru beri nilai,
  scan QR di gerbang,                                 cetak Rapor PDF,
  bayar SPP di kasir.                                 buat surat teguran BK.
         │                                                   │
         └───────────────── X TIDAK SINKRON X ───────────────┘
                     (Siswa Baru Tidak Bisa Dinilai,
                      Siswa Dinilai Tidak Bisa Scan QR)
```

### 1.3 Dampak Kegagalan Nyata
* **Siswa Baru Tidak Bisa Dinilai:** Jika staf administrasi memasukkan siswa baru via `SiswaController` (Academic), data masuk ke tabel `siswa`. Namun, saat guru membuka halaman input nilai (`GradeEntryController`), siswa tersebut **tidak akan muncul** karena kueri mengambil data dari tabel `students`.
* **Presensi Tidak Sinkron:** Scan QR gerbang mencatat kehadiran di tabel `attendances` menggunakan relasi siswa dari tabel `siswa`, sedangkan agenda guru memeriksa presensi kelas menggunakan model `Student`.
* **Hacks pada Unit Test:** Di dalam `RaporGeneratorTest.php` (Epic 7 Fix), tim pengembang terpaksa memasukkan baris paksaan (*hack*) demi menyinkronkan ID autoincrement yang bergeser antara kedua tabel agar pengujian bisa lulus (*green*):
  ```php
  $this->student = new Student([...]);
  $this->student->id = $this->siswa->id; // Penugasan paksa bypass mass-assignment
  $this->student->save();
  ```

---

## 2. DETAIL AUDIT MODUL PER-EPIC (6, 7, 8, 9)

Berikut adalah hasil audit kritis pada masing-masing modul untuk mendeteksi komponen yang belum selesai (*unimplemented*):

### 2.1 Epic 6: Evaluation Module (Modul Penilaian & Rapor)

* **CRASH RUNTIME - Controller Hilang:**
  Di dalam `sisfokol-laravel/app/Modules/Evaluation/routes.php`, terdapat deklarasi rute kustom kurikulum:
  ```php
  Route::get('/curriculum', [CurriculumController::class, 'index'])->name('curriculum.index');
  ```
  Namun, file fisik **`app/Modules/Evaluation/Controllers/CurriculumController.php` sama sekali tidak ada di disk**. Mengakses rute ini dipastikan akan memicu error fatal `Class ... not found`.
* **Event-Hook Integrasi Tidak Pernah Dipanggil:**
  Modul ini menyediakan kelas event `EvaluationResolveFramework.php` dan `RaportRenderSection.php`, namun **tidak ada satu pun kode di dalam `GradeEntryController` ataupun `RaporGeneratorService` yang memicu (*dispatch*) event tersebut**.
  * Akibatnya, `EvaluationFrameworkResolver` menjadi kelas mati (*dangling service*) yang tidak pernah dieksekusi.

### 2.2 Epic 7: Finance Module (Modul Keuangan & Tabungan)

* **Tumpang Tindih Menu Kasir (Duplicate Feature):**
  Akibat divergensi model, terdapat dua fitur kasir pembayaran yang berjalan secara terpisah:
  1. **Kasir Modular (`app/Modules/Finance/Controllers/PembayaranController.php`):** Melakukan pemrosesan transaksi SPP terhadap model `Siswa` (tabel `siswa`) dan mencatat mutasi menggunakan model `Pembayaran` kustom.
  2. **Kasir Core (`app/Http/Controllers/Finance/StudentPaymentController.php`):** Melakukan penagihan terhadap model `Student` (tabel `students`) dan mencatat mutasi di tabel `student_payments`.
* **Risiko Fatal Keuangan:** Pembayaran yang dilakukan melalui kasir modular tidak akan memotong tagihan di kasir core, menyebabkan kekacauan laporan keuangan sekolah (*double bookkeeping/divergent billing*).

### 2.3 Epic 8: Presence Module (Modul Presensi & Kehadiran)

* **Roster Kehadiran Terbelah:**
  * Mesin pemindai `QrScannerService` mendeteksi NIS dari tabel `siswa`, kemudian jika user account belum ada, ia akan membuat user siswa baru yang dikaitkan secara polimorfis ke `App\Modules\Academic\Models\Siswa`.
  * Namun, menu kehadiran guru di kelas (`app/Http/Controllers/Teacher/AttendanceController.php`) memuat daftar murid menggunakan model `App\Models\Student`. Guru tidak akan bisa melihat hasil scan masuk siswa dari gerbang sekolah karena perbedaan jalur pembacaan data.

### 2.4 Epic 9: Plugin Kurikulum (Referensi Plugin)

* **Ketidakbergunaan Fungsionalitas (*Useless Functionality*):**
  * Plugin Kurikulum dirancang dengan sangat indah menggunakan Event Subscribers (`EvaluationFrameworkSubscriber` dan `RaporSectionSubscriber`) untuk menyuplai struktur Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP) secara dinamis ke Core Evaluation.
  * Namun, karena Core Evaluation (Epic 6) **tidak pernah memicu event-event tersebut**, seluruh kode pelanggan (*subscriber*) di dalam Plugin Kurikulum **tidak pernah dijalankan**. 
  * Plugin ini terisolasi sepenuhnya dan data kurikulum dinamis (seperti Kurikulum Merdeka atau K13) tidak pernah mengalir ke lembar penilaian guru atau halaman cetak rapor PDF.

---

## 3. REKOMENDASI STRATEGIS PENYELESAIAN (ROADMAP REFACTOR)

Untuk menyelamatkan integritas aplikasi SISFOKOL v7, tim pengembang wajib menyatukan kembali dualisme alam semesta model ini dengan langkah-langkah berikut:

### Langkah 1: Konsolidasi Skema Database (Wajib)
Hapus tabel `students`, `classrooms`, `subjects`, dan `academic_years`. Alihkan seluruh model core untuk menggunakan tabel modular (`siswa`, `kelas`, `mapel`, `tahun_ajaran`) dengan menambahkan properti `$table` pada model core, atau lakukan migrasi kode bertahap.
```php
// Modifikasi App\Models\Student agar merujuk ke tabel modular 'siswa'
class Student extends Model
{
    protected $table = 'siswa'; // Menyatukan database!
    
    // Sesuaikan mapping kolom: 'name' -> 'nama', 'gender' -> 'jenis_kelamin'
}
```

### Langkah 2: Hubungkan Event-Hook pada Core Evaluation
Suntikkan pemanggilan `EvaluationFrameworkResolver` ke dalam `GradeEntryController` saat merender form nilai agar Plugin Kurikulum dapat menyuplai kompetensi harian secara dinamis:
```php
// Di dalam GradeEntryController.php
$framework = app(EvaluationFrameworkResolver::class)->resolve($subject, $classroom);
// Kirim $framework ke view Blade untuk merender kolom KI/KD atau CP/TP
```

### Langkah 3: Picu Event Rapor Section saat Cetak PDF
Suntikkan pemicuan event `RaportRenderSection` di dalam `RaporGeneratorService::getReportData()` agar data kustom dari sistem konseling/pelanggaran/kurikulum dapat dirender secara otomatis pada lembar rapor PDF:
```php
// Di dalam RaporGeneratorService.php
$event = new RaportRenderSection($student, $academicYear, $semester);
event($event);
$customSections = $event->sections; // Berisi html/data tambahan dari plugin aktif
```
