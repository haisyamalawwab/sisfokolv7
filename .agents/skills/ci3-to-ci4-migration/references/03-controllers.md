# 03 — Controllers

Konversi controller CI3 → CI4. Baca reference ini sebelum handle controller.

## Struktur class

CI3 (`application/controllers/Auth.php`):
```php
<?php
class Auth extends CI_Controller {
    public function login() {
        $this->load->model('user_model');
        $user = $this->user_model->get_by_username($this->input->post('username'));
        $this->load->view('auth/login', ['user' => $user]);
    }
}
```

CI4 (`app/Controllers/Auth.php`):
```php
<?php
namespace App\Controllers;
use App\Models\UserModel;
class Auth extends BaseController {
    public function login() {
        $model = new UserModel();
        $user = $model->getByUsername($this->request->getPost('username'));
        return view('auth/login', ['user' => $user]);
    }
}
```

## Mapping per baris

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `class Auth extends CI_Controller` | `namespace App\Controllers;` + `class Auth extends BaseController` | manual |
| `$this->load->model('user_model')` | `use App\Models\UserModel;` + `$model = new UserModel();` | manual (namespace+use) |
| `$this->input->post('username')` | `$this->request->getPost('username')` | **mekanis** (`convert-mechanical.mjs`) |
| `$this->input->get('q')` | `$this->request->getGet('q')` | **mekanis** |
| `$this->uri->segment(3)` | `$this->request->uri->getSegment(3)` | **mekanis** |
| `$this->load->view('x',$d)` | `return view('x',$d)` | mekanis (call) + manual (prefix `return`) |
| `$this->load->helper('form')` | `helper('form')` (atau set di `BaseController::$helpers`) | **mekanis** |

## Gotcha wajib

- **CI4 return-based**: setiap method controller WAJIB `return` (string/view/Response). CI3 echo-based. Script konversi call `load->view` → `view()` tapi TIDAK menambah `return` — tambahkan manual sesuai konteks.
- Nama file controller CI3 sudah PascalCase (`Auth.php`) → tetap, pindah ke `app/Controllers/`.
- `$this->output->set_content_type('json')->set_output($d)` → `return $this->response->setJSON($d)`.
- Constructor CI3 `public function __construct() { parent::__construct(); ... }` → CI4 pakai `initController($request, $logger, $session)` atau constructor biasa tanpa `parent::__construct()` (BaseController tidak butuh).

## ResourceController (untuk REST API)

Jika controller CI3 menyajikan REST API (manual JSON), di CI4 pakai `ResourceController`:

```php
<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
class Users extends ResourceController {
    public function index() {
        $model = new \App\Models\UserModel();
        return $this->respond($model->findAll());        // 200 JSON
    }
    public function show($id = null) {
        $user = (new \App\Models\UserModel())->find($id);
        if (! $user) return $this->failNotFound('User tidak ditemukan');
        return $this->respond($user);
    }
    public function create() {
        // ... insert
        return $this->respondCreated($user);             // 201
    }
    public function update($id = null) { /* ... */ return $this->respond($user); }
    public function delete($id = null) { /* ... */ return $this->respondDeleted(); }
}
```

Response helper: `$this->respond()`, `respondCreated()`, `respondDeleted()`, `failNotFound()`, `failValidationErrors()`, `failUnauthorized()`. Format response (JSON/XML) di `app/Config/Format.php`. Route: `$routes->resource('users')` auto-map REST verbs.

## Urutan konversi per file

1. Tambah `namespace App\Controllers;` + `use App\Models\...;` di atas
2. Ganti `extends CI_Controller` → `extends BaseController` (atau `ResourceController` untuk REST)
3. Jalankan `convert-mechanical.mjs --dry-run` → review → `--apply`
4. Tambah `return` di depan `view(...)` / `setJSON(...)` (manual)
5. Hapus `$this->load->model(...)` ganti dengan `new Model()` (manual)
6. Verifikasi: baris 1 `<?php`, ada namespace, extends base yang benar
