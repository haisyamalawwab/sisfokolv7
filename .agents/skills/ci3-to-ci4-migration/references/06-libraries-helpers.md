# 06 — Custom Libraries & Helpers

Area paling sering jadi stuck point — terutama library yang pakai `&get_instance()`.

## Library: struktur & load

CI3 (`application/libraries/Auth_lib.php`):
```php
<?php
class Auth_lib {
    private $CI;
    public function __construct() {
        $this->CI = &get_instance();
    }
    public function check($user) {
        return $this->CI->user_model->get_by_username($user);
    }
}
// pemakaian: $this->load->library('auth_lib'); $this->auth_lib->check($u);
```

CI4 (`app/Libraries/AuthLib.php`):
```php
<?php
namespace App\Libraries;
use App\Models\UserModel;
class AuthLib {
    public function check($user) {
        $model = new UserModel();
        return $model->where('username', $user)->first();
    }
}
// pemakaian: use App\Libraries\AuthLib; $lib = new AuthLib(); $lib->check($u);
```

## Mapping

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `application/libraries/Auth_lib.php` `class Auth_lib` | `app/Libraries/AuthLib.php` `namespace App\Libraries; class AuthLib` | mekanis (move) + manual (namespace, rename class) |
| `$this->load->library('auth_lib')` | `use App\Libraries\AuthLib; $lib = new AuthLib();` | manual |
| `$this->CI = &get_instance();` | hapus, pakai DI / `service()` / instantiate model langsung | **manual (stuck point!)** |
| `$this->CI->some_model->method()` | `use App\Models\SomeModel; (new SomeModel())->method()` | manual |
| `$this->CI->session->...` | `session()->...` | manual |
| `$this->CI->load->view(...)` | `return view(...)` (pass dari controller) | manual |

## Stuck point: &get_instance()

CI3 "super object" `&get_instance()` tidak ada di CI4. Library yang pakai pola ini HARUS di-rewrite:
- Akses model → instantiate langsung (`new Model()`) atau inject via constructor
- Akses session/request → pakai `session()`, `service('request')`, `Config\Services`
- Akses config → `config('App')` / `Config\App`

**Jangan** cari padanan `get_instance()` — rewrite arsitekturnya. Ini biasanya sumber bug terbesar saat migrasi.

## MY_Controller / MY_Model (core extensions)

CI3 `application/core/MY_Controller.php`:
```php
class MY_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        if (! $this->auth->is_logged_in()) redirect('login');
    }
}
```

CI4: extend `BaseController` di `app/Controllers/BaseController.php`, atau buat custom base:
```php
namespace App\Controllers;
use CodeIgniter\Controller;
abstract class AuthenticatedController extends Controller {
    public function initController($request, $logger, $session) {
        parent::initController($request, $logger, $session);
        helper('auth');
        if (! auth()->is_logged_in()) return redirect()->to('login');
    }
}
```
Controller lain `extends AuthenticatedController`. Konversi `MY_*` **sebelum** controller lain (banyak controller bergantung).

## Helper

CI3 `application/helpers/custom_helper.php`:
```php
<?php
function format_rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
// pemakaian: $this->load->helper('custom'); format_rupiah(1000);
```

CI4 `app/Helpers/custom_helper.php` — **function-based, no namespace**:
```php
<?php
function format_rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
// pemakaian: helper('custom'); format_rupiah(1000);
```

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `application/helpers/custom_helper.php` | `app/Helpers/custom_helper.php` | mekanis (move) |
| `$this->load->helper('custom')` | `helper('custom')` | **mekanis** |
| function global | function global (no namespace, tetap) | - |

Helper paling mudah — cukup pindah file + `load->helper` → `helper()` via script.
