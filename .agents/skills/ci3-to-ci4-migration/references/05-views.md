# 05 — Views

Konversi view CI3 → CI4.

## Mapping

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->view('x', $data)` | `return view('x', $data)` | mekanis (call) + manual (return) |
| `$this->load->view('header'); $this->load->view('body'); $this->load->view('footer')` | layout `extend('layout')` + `section('content')` | manual |
| `$this->load->vars($shared)` | `view('x', [...$data, ...$shared])` | manual |
| `$this->load->helper('form')` (di view) | `helper('form')` atau set di BaseController | mekanis |

## Layout (header/footer → extend/section)

CI3 (controller load 3 view):
```php
$this->load->view('header', $data);
$this->load->view('auth/login', $data);
$this->load->view('footer', $data);
```

CI4 (layout + section). Buat `app/Views/layout.php`:
```php
<!DOCTYPE html>
<html><head><title><?= esc($title ?? '') ?></title></head>
<body>
  <?= $this->renderSection('content') ?>
</body></html>
```

View `app/Views/auth/login.php`:
```php
<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
  <h1>Login</h1>
  <!-- form -->
<?= $this->endSection() ?>
```

Controller: `return view('auth/login', $data);`

## Gotcha

- `esc()` CI4 untuk htmlspecialchars — pakai `esc($val, 'attr')` di atribut HTML.
- View file tidak perlu namespace (pure PHP template).
- `$this->load->vars()` di CI3 inject ke semua view — di CI4 lewat argumen `view(..., $data)` atau `setVar()` di controller via `view()` ketiga arg.
