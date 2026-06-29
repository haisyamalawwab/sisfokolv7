# 09 — Third-party Libraries

CI3 `application/third_party/` → CI4 composer `vendor/` atau `app/ThirdParty/`.

## Strategi

1. **Cek apakah library punya composer package** — kalau ada, `composer require vendor/pkg`. Preferred.
2. **Cek apakah punya versi CI4-compatible** — banyak library CI3 (PHPExcel→PhpSpreadsheet, dll) sudah ada padanan.
3. **Tidak ada padanan** → pindah ke `app/ThirdParty/` + autoload manual, atau rewrite (out of scope, flag ke user).

## Contoh

CI3 (load manual):
```php
// application/third_party/FPDF/fpdf.php
require_once APPPATH . 'third_party/FPDF/fpdf.php';
$pdf = new FPDF();
```

CI4 (composer preferred):
```bash
composer require setasign/fpdf
```
```php
use setasign\Fpdf\Fpdf;
$pdf = new Fpdf();
```

Atau manual autoload `app/ThirdParty/`:
```php
// composer.json autoload
"autoload": {
    "files": ["app/ThirdParty/FPDF/fpdf.php"]
}
```
```bash
composer dump-autoload
```

## Stuck point

- Library yang pakai `&get_instance()` di dalamnya → hampir pasti perlu rewrite (lihat `06-libraries-helpers.md`).
- Library PHP5-only yang tidak kompatibel PHP 7.4/8.x → cari padanan modern.
- Semua manual (judgment) — tidak ada script mekanis.
