# Output Quality Checklist (Code-Quality Gate, Sekunder)

Dijalankan SETELAH feature parity tercapai. Ini gate **sekunder** — def of done utama tetap feature parity (`feature-parity-checklist.md`). Tujuan: memastikan kode CI4 hasil konversi sehat & mudah dipelihara.

## Per file (cek setiap file hasil konversi)

- [ ] Tidak ada syntax error (`php -l <file>`)
- [ ] Namespace benar sesuai PSR-4 (`App\Controllers\...`, `App\Models\...`, dll)
- [ ] Type declaration lengkap — param + return type. **Boleh ditunda sebagai debt** (catat di kolom Catatan), jangan campur dengan konversi struktural
- [ ] PHPDoc untuk method publik (izin deskripsi, param, return)
- [ ] Tidak pakai deprecated API CI4 (cek changelog CI4)
- [ ] Pakai fitur bawaan CI4 semaksimal mungkin (`service()`, `Model`, `Filter`, `Entity` bila perlu)
- [ ] Business logic tidak berubah vs CI3 (verifikasi via feature-parity checklist)
- [ ] Mengikuti PSR-12 (indentasi, brace position, dll)
- [ ] baris 1 `<?php`, extends base yang benar (`BaseController`/`Model`/`ResourceController`)

## Debt tracking

Type declaration & modernisasi PHP yang ditunda (lihat `references/10-php-modernization.md`) catat di sini, **bukan blocker** def of done:

| File | Debt | Catatan |
|------|------|---------|
| `app/Controllers/Auth.php` | return type belum | ditunda, jangan campur konversi struktural |
| ... | ... | ... |

## Def of done (quality gate)

Feature parity = **primer** (WAJIB). Code quality gate = **sekunder** (recommended, debt boleh). Claim selesai setelah feature parity; quality gate idealnya selesai tapi debt dicatat.
