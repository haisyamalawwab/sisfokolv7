# DEV_DOCS-048: Implementation Plan — Tahap 3: Finalisasi Plugin & ETL

- **Tanggal:** 2026-06-22
- **Status:** 📅 PLANNING (Akan dieksekusi setelah Tahap 1 & 2)
- **Tujuan:** Mengubah boilerplate (scaffold) menjadi fitur fungsional nyata dan memigrasikan data dari sistem legacy.
- **Terhubung ke:** DEV_DOCS-042, ADR-009, ADR-010

---

## ⚡ EXECUTIVE SUMMARY

Tahap 3 adalah fase penyelesaian fitur. Setelah fondasi data diperbaiki (Tahap 1) dan pipa API disiapkan (Tahap 2), kita akan mengisi "kerangka kosong" yang ada pada 8 plugin tambahan dan menyelesaikan pipa migrasi data (ETL). 

Tujuan akhirnya adalah memastikan bahwa SISFOKOL v7 tidak hanya memiliki struktur yang benar, tetapi juga memiliki fungsionalitas lengkap yang siap digunakan oleh pengguna akhir.

---

## 🛠️ RENCANA IMPLEMENTASI TEKNIS

### 1. Implementasi Logika Plugin (Transformasi Boilerplate)
**Masalah:** 8 plugin (Tahfidz, BK, Discipline, Inventory, Hafalan Hadist, Pendidikan Karakter, Pelaporan Ortu, PWA) saat ini hanya berupa folder dengan manifest dan view sederhana.
**Action Items:**
- [ ] **Pengembangan Domain Logic:** Mengimplementasikan Model, Migration, dan Service khusus untuk setiap plugin.
    - *Contoh Tahfidz:* Tabel `tahfidz_progress` $\rightarrow$ `TahfidzService` untuk tracking juz/surah.
    - *Contoh Discipline:* Tabel `violations` $\rightarrow$ `DisciplineService` untuk perhitungan poin pelanggaran.
- [ ] **Pengembangan Controller & CRUD:** Mengganti placeholder route dengan Controller yang mengelola data nyata.
- [ ] **Integrasi UI/UX:** Mengembangkan view Blade yang interaktif (menggunakan Tailwind + Alpine.js) untuk penggantian data.
- [ ] **Penghubungan ke Core:** Menggunakan *Event Subscribers* agar data dari plugin ini bisa mengalir ke Modul Penilaian/Rapor (Sesuai arsitektur ADR-009).

### 2. Eksekusi Pipeline ETL (Migrasi Data Legacy)
**Masalah:** Perintah `MigrateLegacyDataCommand` masih berupa draf. Data dari database lama perlu dipindahkan ke skema baru tanpa kehilangan integritas.
**Action Items:**
- [ ] **Implementasi Step Classes:** Membuat kelas-kelas migrasi yang mengimplementasikan `StepInterface`.
    - `Step1_MigrateTenants`, `Step2_MigrateUsers`, `Step3_MigrateSiswa`, dst.
- [ ] **Aktivasi IdMapper:** Menggunakan `IdMapper.php` untuk mencatat pemetaan ID lama $\rightarrow$ ID baru guna menjaga relasi antar tabel.
- [ ] **Data Cleansing:** Menambahkan logika pembersihan data (trimming, formatting) pada setiap step migrasi.
- [ ] **Eksekusi & Validasi:** Menjalankan command migrasi dan melakukan verifikasi jumlah record antara database legacy dan database baru.

### 3. Integrasi Pelaporan & Dashboard (Cross-Module Reporting)
**Masalah:** Data plugin tidak berguna jika hanya tersimpan di tabelnya sendiri tanpa muncul di laporan utama.
**Action Items:**
- [ ] **Rapor Plugin Integration:** Mengimplementasikan `RaporSectionSubscriber` di setiap plugin agar data (misal: progres Tahfidz atau catatan BK) otomatis muncul di PDF Rapor.
- [ ] **Admin Dashboard Widgets:** Membuat widget ringkasan di dashboard admin yang menarik data agregat dari berbagai plugin (misal: "Total Pelanggaran Hari Ini" dari Plugin Discipline).
- [ ] **Export Data:** Mengimplementasikan fitur export Excel/PDF untuk laporan spesifik plugin menggunakan `Maatwebsite\Excel`.

### 4. Final QA & User Acceptance Testing (UAT)
**Masalah:** Risiko regresi setelah penambahan fitur masif di tahap akhir.
**Action Items:**
- [ ] **Full Suite Testing:** Menjalankan seluruh unit dan feature test (`php artisan test`) untuk memastikan tidak ada fitur core yang rusak.
- [ ] **End-to-End Walkthrough:** Melakukan simulasi alur pengguna lengkap:
    - *Admin $\rightarrow$ Input Siswa $\rightarrow$ Guru $\rightarrow$ Input Nilai $\rightarrow$ Plugin Tahfidz $\rightarrow$ Cetak Rapor.*
- [ ] **Bug Fixing:** Memperbaiki isu yang ditemukan selama UAT sebelum rilis final.

---

## 🧪 METODE VERIFIKASI (Definition of Done)

Tahap 3 dianggap **SELESAI** jika:

1. **Plugin Functional:** Semua 8 plugin dapat melakukan CRUD data secara nyata (bukan sekadar view statis).
2. **ETL Success:** Data dari database legacy berhasil dipindahkan 100% tanpa error foreign key dan jumlah record sesuai.
3. **Rapor Complete:** Lembar Rapor PDF menampilkan data dari Core Evaluation **DAN** data dari Plugin (Tahfidz, BK, dll).
4. **Zero Critical Bugs:** Tidak ada error fatal (`500 Internal Server Error`) pada seluruh alur utama aplikasi.

---

## ⚠️ RISIKO & MITIGASI

| Risiko | Mitigasi |
| :--- | :--- |
| **Data Corruption** saat ETL | Gunakan database transaction pada setiap step migrasi; jika satu step gagal, rollback seluruh proses step tersebut. |
| **Performance Drop** karena terlalu banyak plugin | Gunakan `Lazy Loading` pada subscriber dan optimasi query database dengan indexing yang tepat. |
| **Scope Creep** (Permintaan fitur tambahan di tengah jalan) | Patuhi daftar fitur yang ada di `DEV_DOCS-042` dan masukkan permintaan baru ke dalam backlog post-MVP. |
