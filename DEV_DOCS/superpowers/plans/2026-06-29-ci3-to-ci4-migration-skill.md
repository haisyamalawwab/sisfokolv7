# Skill CI3 → CI4 Migration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun skill ZCode `ci3-to-ci4-migration` (hybrid guide+scripts) yang memandu migrasi CodeIgniter 3 ke CodeIgniter 4 dengan target feature parity.

**Architecture:** Skill berbentuk progressive disclosure — `SKILL.md` ramping sebagai router + decision tree, `references/` per area (dibaca on-demand), `scripts/` Node.js `.mjs` untuk konversi mekanis (TDD, zero-dependency via `node:test`), `assets/` untuk mapping table lengkap + checklist feature parity + code-quality gate.

**Tech Stack:** Markdown (konten skill), Node.js ESM `.mjs` (scripts), `node:test` + `node:assert` (testing, tanpa npm install).

**Spec:** `DOCS/superpowers/specs/2026-06-29-ci3-to-ci4-migration-skill-design.md` (revisi 2, lih. "Synthesis Notes")

**Konvensi testing:** markdown reference/SKILL/assets tidak punya unit test; verifikasi = cek file ada + section required ada + commit. Task scripts pakai TDD beneran (test → fail → implement → pass → commit).

---

## Task 1: Scaffold folder skill + tulis SKILL.md

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/SKILL.md`

- [ ] **Step 1: Buat folder + tulis SKILL.md**

Buat file `.agents/skills/ci3-to-ci4-migration/SKILL.md` dengan isi:

```markdown
---
name: ci3-to-ci4-migration
description: >
  Panduan + helper scripts untuk migrasi project CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4)
  dengan feature-parity. Gunakan skill ini setiap kali user menyebut konversi/migrasi/upgrade
  CodeIgniter 3 ke 4, pindah aplikasi CI3 ke CI4, atau minta bantuan convert controller/model/routing
  CI3 ke sintaks CI4 — bahkan kalau user tidak eksplisit bilang "migrasi".
---

# CI3 → CI4 Migration (Hybrid: Guide + Scripts)

Skill ini memandu migrasi CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4) dengan target **feature parity** — bukan sekadar "jalan tanpa error", tapi semua fitur CI3 berfungsi sama di CI4. Pendekatan hybrid: bagian mekanis (regex aman) via scripts, bagian judgment via panduan reference.

## Kapan skill ini dipakai

Trigger: user menyebut konversi/migrasi/upgrade CI3 ke CI4, pindah aplikasi CI3 ke CI4, atau minta convert controller/model/routing CI3 ke sintaks CI4.

## Workflow inti (7 langkah)

1. **Audit CI3 source**
   - Baca `references/00-audit-checklist.md`
   - Jalankan: `node scripts/audit-ci3.mjs <ci3-application-path>`
   - Output: laporan pola CI3 + impact analysis (dependency antar komponen) + estimasi effort

2. **Cek keberadaan project CI4**
   - Belum ada → setup via `references/01-bootstrap-config.md`
   - Sudah ada → lanjut step 3

3. **Pilih strategi urutan** (decision tree di bawah)

4. **Konversi per area** — WAJIB baca reference area sebelum handle

5. **Jalankan scripts mekanis** untuk bagian aman (selalu `--dry-run` dulu, review, baru `--apply`)

6. **Feature-parity check** via `assets/feature-parity-checklist.md` + `node scripts/feature-parity-check.mjs <ci3> <ci4>`, lalu **code-quality gate** via `assets/output-quality-checklist.md` (sekunder)

7. **Testing per fitur** (input/output/edge-case sama dengan CI3). Claim selesai HANYA setelah feature parity terpenuhi.

## Decision tree — urutan konversi

- Project kecil (<10 controller, sedikit custom library) → **layer-by-layer** OK
- Project besar / banyak modul / banyak custom library → **incremental per-modul** (testable, rollback mudah)
- Ada `MY_Controller`/`MY_Model` kritis → konversi dulu sebelum controller lain (banyak controller bergantung)
- Hosting constrain PHP version → cek `references/10-php-modernization.md` lebih awal
- Ada REST API controller → baca `references/03-controllers.md` (section ResourceController)

## Router reference (baca sebelum handle area tsb)

| Area | Reference | Mekanis? (script) |
|------|-----------|-------------------|
| Audit | `00-audit-checklist.md` | `audit-ci3.mjs` |
| Bootstrap/Config | `01-bootstrap-config.md` | - |
| Routing | `02-routing.md` | `convert-mechanical.mjs` (sebagian) |
| Controller | `03-controllers.md` | `convert-mechanical.mjs` |
| Model/DB | `04-models-db.md` | `rename-files.mjs` + `convert-mechanical.mjs` |
| View | `05-views.md` | `convert-mechanical.mjs` |
| Library/Helper | `06-libraries-helpers.md` | `rename-files.mjs` |
| Services | `07-services.md` | `convert-mechanical.mjs` |
| Hooks/Events | `08-hooks-events-filters.md` | - (judgment) |
| Third-party | `09-third-party.md` | - (judgment) |
| PHP modernisasi | `10-php-modernization.md` | - (judgment) |
| Migration/Seeder/Security/Pagination | `11-migration-seeder-security.md` | - (judgment) |

Untuk lookup cepat saat debug area tertentu, baca `assets/mapping-table.md` (superset semua mapping).

## Prinsip wajib

- **COMMENT jangan DELETE** — kode CI3 lama di-comment dengan label `[YYYY-MM-DD | agent]`, bukan dihapus. Rollback = uncomment. Contoh:
  ```php
  // [2026-06-29 | ci3-to-ci4] ganti ke CI4 request
  // $x = $this->input->post('x');
  $x = $this->request->getPost('x');
  ```
- **Mekanis via script, judgment via manual** — JANGAN regex-sendiri bagian berisiko (`extends CI_*`, `$this->load->model/library`, business logic, hooks→events, migration/seeder, security rewrite)
- **Script selalu `--dry-run` dulu** — review diff, baru `--apply`. Tidak ada perubahan file tanpa review.
- **Feature-parity WAJIB** sebelum claim selesai (def of done = feature parity, bukan "jalan tanpa error")
- **Verifikasi post-konversi per file** — baris 1 `<?php`, ada namespace, extends base yang benar
- **Code-quality gate sekunder** — setelah feature parity, jalankan `assets/output-quality-checklist.md`. Type declaration boleh jadi debt (jangan campur dengan konversi struktural)
```

- [ ] **Step 2: Verifikasi struktur**

Run: `ls -la .agents/skills/ci3-to-ci4-migration/`
Expected: `SKILL.md` terlihat.

Run: `head -5 .agents/skills/ci3-to-ci4-migration/SKILL.md`
Expected: baris 1 `---`, baris 2 `name: ci3-to-ci4-migration`.

- [ ] **Step 3: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/SKILL.md
git commit -m "feat(ci3-to-ci4-skill): scaffold skill + SKILL.md router"
```

---

## Task 2: assets/mapping-table.md (superset mapping CI3→CI4)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/assets/mapping-table.md`

- [ ] **Step 1: Tulis mapping table lengkap**

Buat file `.agents/skills/ci3-to-ci4-migration/assets/mapping-table.md`:

```markdown
# Mapping Table CI3 → CI4 (Quick Reference)

Lookup cepat saat debug area tertentu. Superset dari `references/`. Format: CI3 → CI4 (mekanis? / catatan).

## Controller

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `class Foo extends CI_Controller` | `namespace App\Controllers; class Foo extends BaseController` | manual (namespace+use) |
| `$this->load->model('foo_model','fm')` | `use App\Models\FooModel; $fm = new FooModel();` | manual |
| `$this->load->view('x', $data)` | `return view('x', $data)` | mekanis (call) + manual (return prefix) |
| `$this->input->post('x')` | `$this->request->getPost('x')` | mekanis |
| `$this->input->get('x')` | `$this->request->getGet('x')` | mekanis |
| `$this->input->post_get('x')` | `$this->request->getPostGet('x')` | mekanis |
| `$this->uri->segment(n)` | `$this->request->uri->getSegment(n)` | mekanis |
| `$this->output->set_content_type('json')` | `return $this->response->setJSON($d)` | manual |
| (REST) controller CI3 manual JSON | `extends ResourceController`, `$this->respond()/respondCreated()/failNotFound()` | manual |

## Model & DB

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `class Foo_model extends CI_Model` | `namespace App\Models; class FooModel extends Model` | manual |
| (file) `models/foo_model.php` | `app/Models/FooModel.php` | mekanis (rename-files.mjs) |
| `$this->db->get('t')->result()` | `$model->findAll()` / `db_connect()->table('t')->get()->getResult()` | manual |
| `$this->db->where('k',$v)->get('t')->result()` | `$model->where('k',$v)->findAll()` | manual |
| `$this->db->insert('t',$d)` | `$model->insert($d)` | manual |
| `$this->db->update('t',$d,$cond)` | `$model->update($id,$d)` / `->where()->update()` | manual |
| `$this->db->delete('t',$cond)` | `$model->delete($id)` / `->where()->delete()` | manual |
| `$query->row()` | `->first()` / `->getFirstRow()` | manual |
| `$query->result_array()` | `->getResultArray()` | manual |
| `$this->db->query($sql)` | `db_connect()->query($sql)` | manual |
| `$this->db->insert_id()` | `db_connect()->insertID()` / `$model->insertID` | manual |
| (opsional) return stdClass | `extends Entity` + `$returnType = App\Entities\Foo::class` | manual (opsional) |

## Routing

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$route['default_controller']='x'` | `$routes->setDefaultController('x')` | manual |
| `$route['foo/bar']='c/m'` | `$routes->get('foo/bar','C::m')` | sebagian (simple) |
| `$route['x/(:num)']='c/m/$1'` | `$routes->get('x/(:num)','C::m/$1')` | manual |
| `$route['404_override']='x'` | `$routes->set404Override('X::index')` | manual |
| `$route['x']['post']='c/m'` | `$routes->post('x','C::m')` | manual |

## Session

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->session->set_userdata('k',$v)` | `session()->set('k',$v)` | mekanis |
| `$this->session->userdata('k')` | `session()->get('k')` | mekanis |
| `$this->session->set_flashdata('k',$v)` | `session()->setFlashdata('k',$v)` | mekanis |
| `$this->session->flashdata('k')` | `session()->getFlashdata('k')` | manual |

## Form Validation

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('form_validation')` | (auto via service) | - |
| `$this->form_validation->set_rules('f','L','required')` | `service('validation')->setRules(['f'=>'required'],['f'=>'L'])` | manual |
| `if ($this->form_validation->run()==FALSE)` | `if (! $this->validate($rules))` | manual |

## Email / Upload / Cache / Logging

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('email'); $this->email->send()` | `$e = service('email'); $e->send()` / `email()->send()` | manual |
| `$this->load->library('upload'); $this->upload->do_upload('f')` | `$f=$this->request->getFile('f'); $f->move(WRITEPATH.'uploads')` | manual |
| `$this->load->driver('cache'); $this->cache->save('k',$v)` | `cache()->save('k',$v)` | manual |
| `log_message('error','...')` | `log_message('error','...')` (sama, global) | - |
| `$this->CI->...` di library | DI / `service()` / `Config\Services` | manual (stuck point) |

## Library / Helper

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `application/libraries/Foo.php` `class Foo` | `app/Libraries/Foo.php` `namespace App\Libraries; class Foo` | mekanis (move) + manual (namespace) |
| `$this->load->library('foo')` | `use App\Libraries\Foo; $foo = new Foo();` | manual |
| `$this->CI = &get_instance()` | DI / `service()` / `Config\Services` | manual (stuck point!) |
| `application/helpers/foo_helper.php` | `app/Helpers/foo_helper.php` | mekanis (move) |
| `$this->load->helper('foo')` | `helper('foo')` | mekanis |

## View

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->view('x',$data)` | `return view('x',$data)` | mekanis (call) + manual (return) |
| `$this->load->view('header');...;view('footer')` | `extend('layout') + section('content')` | manual |
| `$this->load->vars($d)` | `view('x',[...$d])` | manual |

## Config

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `config/config.php` (array) | `app/Config/App.php` (class) | manual |
| `config/database.php` (array) | `app/Config/Database.php` + `.env` | manual |
| `config/autoload.php` `$autoload` | service registration / Filters | manual |
| `$this->config->item('k')` | `config('App')->k` / `config('App')` | manual |

## Hooks → Events/Filters

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$hook['pre_controller']=...` (config/hooks.php) | `Events::on('pre_controller',...)` + `Config/Filters.php` | manual |
| `$hook['pre_system']` | `Events::on('pre_system',...)` | manual |

## Migration & Seeder

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->migration->current()/latest()` | `php spark migrate` / `php spark migrate:rollback` | manual |
| `application/migrations/001_xxx.php` | `app/Database/Migrations/001_xxx.php` | manual (move + rewrite) |
| `$this->dbforge->create_table('t')` | `$this->forge->createTable('t')` | manual |
| `$this->dbforge->add_column()/drop_table()` | `$this->forge->addColumn()/dropTable()` | manual |
| (seeder) manual | `php spark db:seed` + `app/Database/Seeds/` | manual |

## Security

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->security->xss_clean($v)` | `esc($v)` / `esc($v,'attr')` (context: html/js/css/url/attr) | manual |
| `$this->input->post('f', TRUE)` (XSS filter) | `$this->request->getPost('f')` + `esc()` saat output | manual |
| CSRF (config-based) | Filter `csrf` (default global) + `csrf_token()/csrf_hash()/csrf_field()` | manual |
| `$this->security->csrf_verify()` | (otomatis via csrf filter) | manual |

## Pagination

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->pagination->initialize($config)` | `$model->paginate(10)` + `app/Config/Pager.php` | manual |
| `$this->pagination->create_links()` | `$pager->links()` / `simpleLinks()` | manual |
```

- [ ] **Step 2: Verifikasi**

Run: `test -f .agents/skills/ci3-to-ci4-migration/assets/mapping-table.md && echo OK`
Expected: `OK`

- [ ] **Step 3: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/assets/mapping-table.md
git commit -m "feat(ci3-to-ci4-skill): add mapping-table asset (superset CI3->CI4, +migration/security/pagination)"
```

---

## Task 3: references 00, 01, 02 (audit+impact, bootstrap+spark, routing)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/references/00-audit-checklist.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/01-bootstrap-config.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/02-routing.md`

- [ ] **Step 1: Tulis 00-audit-checklist.md (dengan impact analysis)**

```markdown
# 00 — Audit Codebase CI3

Scan codebase CI3 sebelum mulai konversi. Tujuan: inventaris + impact analysis + estimasi effort + identifikasi stuck point.

## Checklist manual

- [ ] Cek struktur: `application/{controllers,models,views,config,libraries,helpers,hooks,third_party}`
- [ ] Daftar semua controller (class + method publik)
- [ ] Daftar semua model
- [ ] Daftar custom library & helper
- [ ] Daftar hook di `config/hooks.php`
- [ ] Cek `config/autoload.php` — apa yang autoload (library/helper/model)
- [ ] Cek `config/config.php`, `config/database.php`, `config/routes.php`
- [ ] Daftar `application/third_party/*` — apakah ada padanan composer/CI4?
- [ ] Cek PHP version (CI3 min 5.6 → CI4 butuh 7.2+, CI4.4+ butuh 7.4+)

## Scan otomatis

```bash
node scripts/audit-ci3.mjs <ci3-application-dir>
```

Output: JSON + ringkasan human — list file per type, ~17 pola CI3 terdeteksi (`extends CI_Controller`, `load->model`, `get_instance()`, `migration->`, `dbforge->`, `security->`, dll), custom `MY_*`, third-party, dan estimasi effort:
- **kecil**: <10 controller, sedikit custom library
- **sedang**: 10-25 controller
- **besar**: >25 controller / banyak custom library / banyak `&get_instance()`

## Impact analysis (sebelum konversi)

Sebelum konversi, petakan dependency antar komponen agar tahu urutan aman:

- [ ] **Controller → library/model**: controller mana yang pakai library X / model Y? (konversi dependency dulu)
- [ ] **Model → dipakai banyak controller**: model mana yang paling banyak dipakai? (konversi dulu, tapi pastikan `$allowedFields` benar agar tidak break banyak controller)
- [ ] **Library → `&get_instance()`**: library mana yang pakai super object? (flag sebagai stuck point, butuh rewrite DI — lihat `06-libraries-helpers.md`)
- [ ] **Controller base (`MY_Controller`)**: controller mana yang `extends MY_Controller`? (konversi MY_Controller **sebelum** controller lain)
- [ ] **Route → custom controller**: route yang hardcode nama controller/method — pastikan nama class tidak berubah saat konversi

Output impact analysis: daftar urutan konversi yang aman (dependency-aware).

## Stuck point yang wajib di-flag

- Library/helper pakai `$this->CI = &get_instance()` → butuh rewrite ke DI/`service()` (lihat `06-libraries-helpers.md`)
- `MY_Controller`/`MY_Model` di `application/core/` → konversi dulu sebelum controller lain
- Third-party yang tidak punya padanan CI4 → flag ke user (out of scope rewrite)
- Migration pakai `dbforge` → lihat `11-migration-seeder-security.md`
```

- [ ] **Step 2: Tulis 01-bootstrap-config.md (dengan Spark CLI)**

```markdown
# 01 — Bootstrap & Config CI4

Pakai reference ini saat project CI4 belum ada (step 2 workflow). Jika CI4 sudah ada, skip ke `02-routing.md`.

## Setup project CI4

```bash
composer create-project codeigniter4/appstarter ci4-project
cd ci4-project
```

Struktur hasil:
```
app/{Controllers,Models,Views,Config,Libraries,Helpers}
system/      (core, jangan diubah)
writable/    (cache, logs, sessions, uploads)
public/      (index.php, web root)
.env         (konfigurasi)
spark        (CLI tool, mirip artisan)
```

## .env

Copy `env` → `.env`, set:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080'
database.default.hostname = localhost
database.default.database = mydb
database.default.username = root
database.default.password = ''
```

## Konversi config (array → class)

### config/config.php → app/Config/App.php
- `$config['base_url']` → `public $baseURL` (di .env: `app.baseURL`)
- `$config['index_page']` → `public $indexPage = ''` (CI4 pakai mod_rewrite)
- `$config['encryption_key']` → `app/Config/Encryption.php` `$key`
- `$config['csrf_token_name']`/`csrf_regenerate` → `app/Config/Security.php`/`App.php`

### config/database.php → app/Config/Database.php + .env
- Array `$db['default']` → class property + override via .env `database.default.*`

### config/autoload.php → service registration
- `$autoload['libraries']` (session, database) → otomatis tersedia di CI4 via service/factory
- `$autoload['helper']` → set di `app/Config/Autoload.php` `$helpers` atau di `BaseController::$helpers`
- `$autoload['packages']` (third_party) → composer atau `app/ThirdParty/`

## Spark CLI (setara artisan Laravel)

CI4 punya CLI `spark` untuk scaffolding & DB:

```bash
php spark serve                      # jalankan dev server
php spark make:controller Foo        # scaffold controller
php spark make:model FooModel
php spark make:migration AddXTable
php spark make:seeder FooSeeder
php spark make:filter AuthFilter
php spark make:command FooCommand
php spark routes                     # list route terdaftar
php spark migrate                    # jalankan DB migration
php spark migrate:rollback
php spark migrate:status
php spark db:seed FooSeeder
```

Gunakan `spark` untuk scaffolding saat konversi (lebih cepat + konsisten dari pada buat manual).

## Verifikasi

```bash
php spark serve
```
Buka `http://localhost:8080` — harus muncul welcome page CI4 tanpa error.
```

- [ ] **Step 3: Tulis 02-routing.md**

```markdown
# 02 — Routing

Konversi `application/config/routes.php` (CI3) → `app/Config/Routes.php` (CI4).

## Mapping

### Default controller
CI3:
```php
$route['default_controller'] = 'welcome';
```
CI4:
```php
$routes->setDefaultController('Welcome::index');
```

### Route sederhana
CI3:
```php
$route['foo/bar'] = 'foo/bar';   // controller/method
```
CI4:
```php
$routes->get('foo/bar', 'Foo::bar');
```

### Route dengan parameter
CI3:
```php
$route['user/(:num)'] = 'user/view/$1';
```
CI4:
```php
$routes->get('user/(:num)', 'User::view/$1');
```

### 404 override
CI3:
```php
$route['404_override'] = 'errors/show_404';
```
CI4:
```php
$routes->set404Override('Errors::show_404');
```

### HTTP verb spesifik
CI3:
```php
$route['form/submit']['post'] = 'form/submit';
```
CI4:
```php
$routes->post('form/submit', 'Form::submit');
```

## Gotcha

- CI4 case-sensitive: controller `Foo` di-route sebagai `Foo::method`, bukan `foo/method`. Nama class harus PascalCase.
- CI4 wajib eksplisit verb (`get`/`post`/...) — tidak ada "ANY" implisit seperti CI3 wildcard. Pakai `$routes->add()` untuk ANY (tapi tidak recommended).
- Regex route kompleks + group + filter → manual, lihat docs CI4 `Routing`.

## Mekanis?

Sebagian. Route sederhana `$route['x']='c/m'` bisa dibantu `feature-parity-check.mjs` untuk verifikasi parity, tapi penulisan `Routes.php` manual (butuh keputusan verb + nama class PascalCase).
```

- [ ] **Step 4: Verifikasi**

Run: `ls .agents/skills/ci3-to-ci4-migration/references/`
Expected: `00-audit-checklist.md  01-bootstrap-config.md  02-routing.md`

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/references/00-audit-checklist.md \
        .agents/skills/ci3-to-ci4-migration/references/01-bootstrap-config.md \
        .agents/skills/ci3-to-ci4-migration/references/02-routing.md
git commit -m "feat(ci3-to-ci4-skill): add references 00-02 (audit+impact, bootstrap+spark, routing)"
```

---

## Task 4: references 03, 04, 05 (controllers+REST, models-db+Entity, views)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/references/03-controllers.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/04-models-db.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/05-views.md`

- [ ] **Step 1: Tulis 03-controllers.md (dengan ResourceController)**

```markdown
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
```

- [ ] **Step 2: Tulis 04-models-db.md (dengan Entity)**

```markdown
# 04 — Models & Database

Konversi model CI3 → CI4 + Active Record → Query Builder.

## Struktur file & class

CI3 (`application/models/user_model.php` — snake_case):
```php
<?php
class User_model extends CI_Model {
    public function get_by_username($username) {
        return $this->db->where('username', $username)->get('users')->row();
    }
}
```

CI4 (`app/Models/UserModel.php` — PascalCase):
```php
<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'email'];

    public function getByUsername($username) {
        return $this->where('username', $username)->first();
    }
}
```

## Mapping

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| file `models/user_model.php` | `app/Models/UserModel.php` | **mekanis** (`rename-files.mjs`) |
| `class User_model extends CI_Model` | `namespace App\Models; class UserModel extends Model` | manual |
| `$this->db->get('users')->result()` | `$this->findAll()` / `db_connect()->table('users')->get()->getResult()` | manual |
| `$this->db->where('k',$v)->get('t')->row()` | `$this->where('k',$v)->first()` | manual |
| `$this->db->where('k',$v)->get('t')->result()` | `$this->where('k',$v)->findAll()` | manual |
| `$this->db->insert('t',$d)` | `$this->insert($d)` | manual |
| `$this->db->where('id',$i)->update('t',$d)` | `$this->update($i, $d)` | manual |
| `$this->db->where('id',$i)->delete('t')` | `$this->delete($i)` | manual |
| `$q->row()` | `->first()` / `->getFirstRow()` | manual |
| `$q->result_array()` | `->getResultArray()` | manual |
| `$this->db->query($sql)->result()` | `db_connect()->query($sql)->getResult()` | manual |
| `$this->db->insert_id()` | `$this->insertID` / `db_connect()->insertID()` | manual |

## Gotcha wajib

- **`$allowedFields` WAJIB** untuk mass-assignment (`insert`/`update` dengan array). Tanpa ini, field di-block. Daftar semua kolom yang boleh di-set.
- **`$primaryKey` WAJIB** untuk method `find($id)`/`update($id,$d)`/`delete($id)`.
- Nama method: CI3 bebas (`get_by_username`), CI4 recommended camelCase (`getByUsername`) — opsional tapi konsisten.
- Transaction: `$this->db->trans_start()/complete()` → `db_connect()->transBegin()/transCommit()/transRollback()`.
- `$this->db->last_query()` (debug) → `db_connect()->getLastQuery()`.

## Entity class (opsional, untuk model return object rich)

Untuk model yang return object dengan logic getter/setter/casting, pakai Entity:

```php
// app/Entities/User.php
namespace App\Entities;
use CodeIgniter\Entity\Entity;
class User extends Entity {
    protected $casts = ['is_active' => 'boolean', 'meta' => 'json'];
    public function setPassword(string $pass): static {   // auto-hash saat set
        $this->attributes['password'] = password_hash($pass, PASSWORD_DEFAULT);
        return $this;
    }
}
// app/Models/UserModel.php
protected $returnType = \App\Entities\User::class;   // find() return User entity
```

Type casting: `boolean`, `int`, `float`, `json`, `array`, `datetime`, `timestamp`. Date/JSON/encrypted casting tersedia. **Opsional** — jangan campur dengan konversi struktural (buat commit terpisah, lihat `10-php-modernization.md`).

## Urutan konversi per file

1. `rename-files.mjs` untuk pindah + rename `user_model.php` → `UserModel.php`
2. Tambah `namespace App\Models;` + `use CodeIgniter\Model;`
3. Ganti `extends CI_Model` → `extends Model`, rename class `User_model` → `UserModel`
4. Set `$table`, `$primaryKey`, `$allowedFields`
5. Konversi query manual (Active Record → Query Builder/Model methods)
6. (Opsional, terpisah) set `$returnType` + buat Entity
7. Verifikasi: baris 1 `<?php`, ada namespace, ada 3 property wajib
```

- [ ] **Step 3: Tulis 05-views.md**

```markdown
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
```

- [ ] **Step 4: Verifikasi**

Run: `ls .agents/skills/ci3-to-ci4-migration/references/`
Expected: 6 file (00-05).

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/references/03-controllers.md \
        .agents/skills/ci3-to-ci4-migration/references/04-models-db.md \
        .agents/skills/ci3-to-ci4-migration/references/05-views.md
git commit -m "feat(ci3-to-ci4-skill): add references 03-05 (controllers+REST, models-db+Entity, views)"
```

---

## Task 5: references 06, 07 (libraries-helpers, services+logging)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/references/06-libraries-helpers.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/07-services.md`

- [ ] **Step 1: Tulis 06-libraries-helpers.md**

```markdown
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
```

- [ ] **Step 2: Tulis 07-services.md (dengan Logging)**

```markdown
# 07 — Services (Session, Form Validation, Email, Upload, Cache, Logging)

## Session

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('session')` | (auto, tidak perlu load) | - |
| `$this->session->set_userdata('k', $v)` | `session()->set('k', $v)` | **mekanis** |
| `$this->session->set_userdata(['a'=>1,'b'=>2])` | `session()->set(['a'=>1,'b'=>2])` | mekanis (sama pattern) |
| `$this->session->userdata('k')` | `session()->get('k')` | **mekanis** |
| `$this->session->set_flashdata('k', $v)` | `session()->setFlashdata('k', $v)` | **mekanis** |
| `$this->session->flashdata('k')` | `session()->getFlashdata('k')` | manual |
| `$this->session->unset_userdata('k')` | `session()->remove('k')` | manual |
| `$this->session->sess_destroy()` | `session()->destroy()` | manual |

Catatan: session CI4 perlu `session` auto-started (default via filter) — cek `app/Config/Filters.php` ada `session` di `$globals`.

## Form Validation

CI3:
```php
$this->load->library('form_validation');
$this->form_validation->set_rules('username', 'Username', 'required|min_length[3]');
if ($this->form_validation->run() == FALSE) {
    $this->load->view('form', $this->input->post());
} else {
    // save
}
```

CI4 (inline rules via controller):
```php
$rules = ['username' => 'required|min_length[3]'];
if (! $this->validate($rules)) {
    return view('form', ['validation' => $this->validator] + $this->request->getPost());
}
// save
```

Atau via config `app/Config/Validation.php` + `service('validation')->setRules(...)`.

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->form_validation->set_rules('f','L','rules')` | `$rules=['f'=>'rules']` + `$this->validate($rules)` | manual |
| `$this->form_validation->run()` | `$this->validate($rules)` (returns bool) | manual |
| `form_error('f')` (di view) | `$this->validator->getError('f')` / `validation_list_errors()` | manual |
| `set_value('f')` | `set_value('f')` (sama, helper form) | - |

## Email

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('email')` | `$email = service('email')` / `email()` | manual |
| `$this->email->from('a@b.c')` | `$email->setFrom('a@b.c')` | manual |
| `$this->email->to('x@y.z')` | `$email->setTo('x@y.z')` | manual |
| `$this->email->subject('S')` | `$email->setSubject('S')` | manual |
| `$this->email->message('M')` | `$email->setMessage('M')` | manual |
| `$this->email->send()` | `$email->send()` | manual |

## Upload

CI3:
```php
$this->load->library('upload', $config);
if (! $this->upload->do_upload('file')) {
    $error = $this->upload->display_errors();
} else {
    $data = $this->upload->data();
}
```

CI4:
```php
$file = $this->request->getFile('file');
if (! $file->isValid()) {
    $error = $file->getErrorString();
} else {
    $file->move(WRITEPATH . 'uploads');
    $name = $file->getName();
}
```

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('upload', $cfg)` | `$file = $this->request->getFile('f')` | manual |
| `$this->upload->do_upload('f')` | `$file->move(WRITEPATH.'uploads')` | manual |
| `$this->upload->data()` | `$file->getName()` / `getClientName()` / `getExtension()` | manual |
| `$this->upload->display_errors()` | `$file->getErrorString()` | manual |

## Cache

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->driver('cache')` | `cache()` | manual |
| `$this->cache->save('k', $v, 60)` | `cache()->save('k', $v, 60)` | manual |
| `$this->cache->get('k')` | `cache()->get('k')` | manual |
| `$this->cache->delete('k')` | `cache()->delete('k')` | manual |

## Logging

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `log_message('error', '...')` | `log_message('error', '...')` (sama, fungsi global) | - (tidak perlu ubah) |
| `log_message('debug'/'info'/'error', $msg)` | sama, level: `debug/info/error/warning/emergency` | - |
| (butuh logger instance) | `$this->logger->debug('...')` di controller, atau `service('logger')` di library | manual |

Catatan: `log_message()` tidak berubah di CI4 — langsung jalan. Untuk logger instance (mis. di library yang butuh DI), pakai `service('logger')`. Config level/handler di `app/Config/Logger.php`.
```

- [ ] **Step 3: Verifikasi**

Run: `ls .agents/skills/ci3-to-ci4-migration/references/`
Expected: 8 file (00-07).

- [ ] **Step 4: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/references/06-libraries-helpers.md \
        .agents/skills/ci3-to-ci4-migration/references/07-services.md
git commit -m "feat(ci3-to-ci4-skill): add references 06-07 (libraries-helpers, services+logging)"
```

---

## Task 6: references 08, 09, 10 (hooks-events, third-party, php-modernization)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/references/08-hooks-events-filters.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/09-third-party.md`
- Create: `.agents/skills/ci3-to-ci4-migration/references/10-php-modernization.md`

- [ ] **Step 1: Tulis 08-hooks-events-filters.md**

```markdown
# 08 — Hooks → Events/Filters

CI3 hooks (`application/config/hooks.php`) → CI4 Events + Filters.

## Mapping hook point

| CI3 hook point | CI4 equivalent | Tipe |
|----------------|---------------|------|
| `pre_system` | `Events::on('pre_system', ...)` | Event |
| `pre_controller` | `Events::on('pre_controller', ...)` / Filter `before` | Event/Filter |
| `post_controller_constructor` | `Events::on('post_controller_constructor', ...)` | Event |
| `post_controller` | `Events::on('post_controller', ...)` / Filter `after` | Event/Filter |
| `display_override` | (output filter) | manual |
| `cache_override` | (custom cache) | manual |

## Contoh konversi

CI3 `config/hooks.php`:
```php
$hook['pre_controller'] = function() {
    $GLOBALS['start'] = microtime(true);
};
$hook['post_controller'] = array(
    'class' => 'Logger',
    'function' => 'log',
    'filename' => 'Logger.php',
    'filepath' => 'hooks',
);
```

CI4 Events (`app/Config/Events.php`, dimuat otomatis):
```php
namespace Config;
use CodeIgniter\Events\Events as CI_Events;
Events::on('pre_controller', function() {
    $GLOBALS['start'] = microtime(true);
});
```

Filter (per-route, `app/Config/Filters.php`):
```php
public $filters = [
    'auth' => ['before' => ['dashboard/*']],
];
```
Buat filter class `app/Filters/AuthFilter.php implements FilterInterface`.

## Gotcha

- Hook yang akses `$this` controller → di CI4 pakai Filter (dapat `$request`) atau service.
- Hooks CI3 eksekusi global; CI4 Events bisa global, Filter per-route. Pilih sesuai kebutuhan.
- Semua manual (judgment) — tidak ada script mekanis untuk area ini.
```

- [ ] **Step 2: Tulis 09-third-party.md**

```markdown
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
```

- [ ] **Step 3: Tulis 10-php-modernization.md**

```markdown
# 10 — PHP Modernization

Opsional tapi recommended. CI3 (PHP 5.6 style) → CI4 (PHP 7.4/8.x). Manfaat: type safety, performance, maintainability.

## Target version

- CI4.0-4.3: PHP 7.2+
- CI4.4+: PHP 7.4+
- Latest CI4: PHP 8.1+ recommended

Cek versi PHP hosting sebelum mulai (`php -v`).

## Modernisasi (opsional, per file)

### Typed properties & return types
CI3:
```php
class User_model extends CI_Model {
    private $table;
    public function get_all() { return $this->db->get($this->table)->result(); }
}
```
CI4 modern:
```php
class UserModel extends Model {
    protected string $table = 'users';
    public function getAll(): array { return $this->findAll(); }
}
```

### Null coalescing
```php
// CI3: $x = isset($_GET['q']) ? $_GET['q'] : '';
$x = $this->request->getGet('q') ?? '';
```

### Constructor promotion (PHP 8+)
```php
class AuthLib {
    public function __construct(private UserModel $model) {}
}
```

### Short array syntax
```php
// CI3: array('a' => 1)
['a' => 1]
```

## Prioritas

1. Wajib: konversi CI3→CI4 API dulu (feature parity)
2. Opsional: modernisasi PHP (type hints, dll) — boleh ditunda, jangan campur dengan konversi struktural (sulit review)

## Gotcha

- Jangan modernisasi + konversi CI3→CI4 sekaligus di satu commit — campur bingung saat review. Pisahkan commit.
- Typed return pada method yang bisa return `null` → pakai `?Type` (nullable).
- Type declaration boleh dicatat sebagai **debt** di `output-quality-checklist.md` — bukan blocker def of done (feature parity yang primer).
```

- [ ] **Step 4: Verifikasi**

Run: `ls .agents/skills/ci3-to-ci4-migration/references/ | wc -l`
Expected: `11` (file 00-10).

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/references/08-hooks-events-filters.md \
        .agents/skills/ci3-to-ci4-migration/references/09-third-party.md \
        .agents/skills/ci3-to-ci4-migration/references/10-php-modernization.md
git commit -m "feat(ci3-to-ci4-skill): add references 08-10 (hooks, third-party, php-modernization)"
```

---

## Task 7: references 11 (migration-seeder-security) [BARU]

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/references/11-migration-seeder-security.md`

- [ ] **Step 1: Tulis reference 11**

```markdown
# 11 — Migration, Seeder, Security, Pagination

Reference gabungan untuk area yang tidak masuk reference lain. Semua manual (judgment) — tidak ada script mekanis.

## Migration (CI3 → CI4)

CI3 migration pakai `$this->migration` + `$this->dbforge`, file di `application/migrations/`:

```php
// application/migrations/001_create_users.php
class Migration_Create_users extends CI_Migration {
    public function up() {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'auto_increment' => TRUE),
            'username' => array('type' => 'VARCHAR', 'constraint' => 100),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
    }
    public function down() {
        $this->dbforge->drop_table('users');
    }
}
```

CI4 migration pakai `$this->forge` + `spark`, file di `app/Database/Migrations/`:

```php
<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateUsers extends Migration {
    public function up() {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 5, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }
    public function down() {
        $this->forge->dropTable('users');
    }
}
```

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `extends CI_Migration` | `extends CodeIgniter\Database\Migration` + `namespace App\Database\Migrations` | manual |
| `$this->dbforge->add_field($arr)` | `$this->forge->addField($arr)` | manual (rename) |
| `$this->dbforge->add_key('id', TRUE)` | `$this->forge->addKey('id', true)` | manual |
| `$this->dbforge->create_table('t')` | `$this->forge->createTable('t')` | manual |
| `$this->dbforge->add_column('t',$c)` | `$this->forge->addColumn('t',$c)` | manual |
| `$this->dbforge->drop_table('t')` | `$this->forge->dropTable('t')` | manual |
| `$this->dbforge->modify_column(...)` | `$this->forge->modifyColumn(...)` | manual |
| `$this->migration->current()/latest()` | `php spark migrate` | manual (CLI, bukan code) |
| `$this->migration->version($v)` | `php spark migrate:rollback` / `php spark migrate:status` | manual |
| (foreign key) `$this->dbforge->add_field('CONSTRAINT...')` | `$this->forge->addForeignKey('col','tbl','col')` | manual |

Jalankan: `php spark migrate` (lihat `01-bootstrap-config.md` Spark CLI section).

## Seeder

CI3 seeder terbatas (manual insert). CI4 punya seeder native:

```php
<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class UserSeeder extends Seeder {
    public function run() {
        $this->db->table('users')->insert(['username' => 'admin', 'password' => password_hash('secret', PASSWORD_DEFAULT)]);
        $this->call('OtherSeeder');   // panggil seeder lain
    }
}
```

Jalankan: `php spark db:seed UserSeeder`. Scaffold: `php spark make:seeder UserSeeder`.

## Security

### XSS
| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->security->xss_clean($v)` | `esc($v)` (default html) / `esc($v,'attr')` / `'js'/'css'/'url'` | manual |
| `$this->input->post('f', TRUE)` (XSS filter saat input) | `$this->request->getPost('f')` + `esc()` **saat output** (output-escaping, bukan input-filtering) | manual |

**Penting:** CI4 filosofinya **output-escaping** (`esc()` di view), bukan input-filtering. Jangan filter di input lalu simpan — simpan raw, escape saat tampil. `esc()` context-aware (`html`/`js`/`css`/`url`/`attr`).

### CSRF
| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| CSRF via `config/config.php` (`csrf_protection=TRUE`) | Filter `csrf` — default global di `app/Config/Filters.php` `$globals['before']` | manual (config) |
| `$this->security->csrf_verify()` | (otomatis via filter) | - |
| (form) manual hidden token | `<?= csrf_field() ?>` di form (auto hidden input) | manual |

Helper: `csrf_token()` (nama), `csrf_hash()` (value), `csrf_field()` (HTML input), `csrf_meta()` (meta tag).

### Secure headers
CI4: aktifkan `secureheaders` filter di `app/Config/Filters.php`. Honeypot: `honeypot` filter. CORS: buat custom CORS filter atau pakai `cors` filter bawaan.

## Pagination

CI3:
```php
$this->load->library('pagination');
$config['base_url'] = site_url('users');
$config['total_rows'] = $this->user_model->count_all();
$config['per_page'] = 10;
$this->pagination->initialize($config);
$links = $this->pagination->create_links();
$users = $this->user_model->get_users($config['per_page'], $this->uri->segment(3));
```

CI4 (terintegrasi Model):
```php
// controller
$model = new \App\Models\UserModel();
$users = $model->paginate(10);
$pager = $model->pager;
// di view: <?= $pager->links() ?>
```

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->pagination->initialize($config)` | `$model->paginate(10)` | manual |
| `$this->pagination->create_links()` | `$model->pager->links()` / `simpleLinks()` | manual |
| `$this->model->count_all()` (total) | `$model->countAll()` / `$model->countAllResults(false)` | manual |
| (offset manual via uri->segment) | otomatis via `?page=N` (query string) | manual |

Config template: `app/Config/Pager.php` `$templates`. Custom template view bisa override.

## Audit hook

Pola area ini dideteksi `audit-ci3.mjs`: `migration->`, `dbforge->`, `pagination->`, `security->`, `config->item`, `parser->`. Saat audit menemukan pola-pola ini, baca reference 11.
```

- [ ] **Step 2: Verifikasi**

Run: `ls .agents/skills/ci3-to-ci4-migration/references/ | wc -l`
Expected: `12` (file 00-11).

- [ ] **Step 3: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/references/11-migration-seeder-security.md
git commit -m "feat(ci3-to-ci4-skill): add reference 11 (migration-seeder-security) [from synthesis]"
```

---

## Task 8: assets/feature-parity-checklist.md

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/assets/feature-parity-checklist.md`

- [ ] **Step 1: Tulis template checklist**

```markdown
# Feature Parity Checklist

Template per-fitur. Def of done = feature parity (input/output/edge-case sama). Salin block ini per fitur.

## Fitur: [nama fitur]

- **CI3 route/endpoint:** `METHOD /path` → controller::method
- **CI3 input (param/body):** ...
- **CI3 output (view/response):** ...
- **CI3 edge case:** (input kosong? input invalid? limit? pagination? error handling?)
- **CI4 status:**
  - [ ] belum
  - [ ] konversi
  - [ ] test
- **CI4 test result:**
  - output sama? [ ] ya / [ ] tidak
  - edge case sama? [ ] ya / [ ] tidak
- **Catatan:** ...

---

## Checklist global (jalankan di akhir migrasi)

- [ ] Semua route CI3 ada di CI4 (`node scripts/feature-parity-check.mjs <ci3> <ci4>` → 0 missing)
- [ ] Semua controller method publik CI3 ada di CI4
- [ ] Semua model method publik CI3 ada di CI4
- [ ] Session behavior sama (login/logout/flashdata)
- [ ] Form validation behavior sama (error message, rule)
- [ ] Upload behavior sama (validation, storage path)
- [ ] Email sending behavior sama
- [ ] Database query result shape sama (row/result/array)
- [ ] Pagination behavior sama (jumlah per page, links, offset)
- [ ] CSRF behavior sama (token, form)
- [ ] XSS protection behavior sama (esc di output)
- [ ] Custom library behavior sama (terutama yg pakai &get_instance())
- [ ] DB migration & seeder jalan (`php spark migrate` tanpa error, seed data benar)
- [ ] Smoke test: akses semua route utama, tidak ada 500/fatal

## Def of done

Migrasi selesai HANYA jika semua checklist di atas tercentang. Bukan "jalan tanpa error", tapi **feature parity**. Setelah ini, jalankan code-quality gate (`output-quality-checklist.md`) — sekunder.
```

- [ ] **Step 2: Verifikasi**

Run: `test -f .agents/skills/ci3-to-ci4-migration/assets/feature-parity-checklist.md && echo OK`
Expected: `OK`

- [ ] **Step 3: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/assets/feature-parity-checklist.md
git commit -m "feat(ci3-to-ci4-skill): add feature-parity checklist asset"
```

---

## Task 9: assets/output-quality-checklist.md [BARU — dari sintesis]

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/assets/output-quality-checklist.md`

- [ ] **Step 1: Tulis code-quality gate checklist**

```markdown
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
```

- [ ] **Step 2: Verifikasi**

Run: `test -f .agents/skills/ci3-to-ci4-migration/assets/output-quality-checklist.md && echo OK`
Expected: `OK`

- [ ] **Step 3: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/assets/output-quality-checklist.md
git commit -m "feat(ci3-to-ci4-skill): add output-quality-checklist asset (code-quality gate, sekunder) [from synthesis]"
```

---

## Task 10: scripts/convert-mechanical.mjs (TDD)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/convert-mechanical.mjs`
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/convert-mechanical.test.mjs`

- [ ] **Step 1: Tulis test (failing first)**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/convert-mechanical.test.mjs`:

```js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { transformCode, transformations } from '../convert-mechanical.mjs';

test('input->post transformed', () => {
  assert.equal(transformCode(`$x = $this->input->post('name');`), `$x = $this->request->getPost('name');`);
});
test('input->get transformed', () => {
  assert.equal(transformCode(`$x = $this->input->get('q');`), `$x = $this->request->getGet('q');`);
});
test('session set_userdata transformed', () => {
  assert.equal(transformCode(`$this->session->set_userdata('k', $v);`), `session()->set('k', $v);`);
});
test('session userdata read transformed', () => {
  assert.equal(transformCode(`$v = $this->session->userdata('k');`), `$v = session()->get('k');`);
});
test('session set_flashdata transformed', () => {
  assert.equal(transformCode(`$this->session->set_flashdata('msg', 'hi');`), `session()->setFlashdata('msg', 'hi');`);
});
test('load->view call transformed (no return prefix added)', () => {
  assert.equal(transformCode(`$this->load->view('foo', $data);`), `view('foo', $data);`);
});
test('load->helper transformed', () => {
  assert.equal(transformCode(`$this->load->helper('form');`), `helper('form');`);
});
test('uri->segment transformed', () => {
  assert.equal(transformCode(`$id = $this->uri->segment(3);`), `$id = $this->request->uri->getSegment(3);`);
});
test('multiple transformations in one snippet', () => {
  const src = [
    `$this->load->helper('form');`,
    `$x = $this->input->post('x');`,
    `$this->session->set_userdata('k', $x);`,
    `$this->load->view('v', $data);`,
  ].join('\n');
  const expected = [
    `helper('form');`,
    `$x = $this->request->getPost('x');`,
    `session()->set('k', $x);`,
    `view('v', $data);`,
  ].join('\n');
  assert.equal(transformCode(src), expected);
});
test('does NOT touch load->model (judgment, left alone)', () => {
  const src = `$this->load->model('user_model');`;
  assert.equal(transformCode(src), src);
});
test('does NOT touch extends CI_Controller', () => {
  const src = `class Foo extends CI_Controller { }`;
  assert.equal(transformCode(src), src);
});
test('does NOT touch load->library (judgment)', () => {
  const src = `$this->load->library('session');`;
  assert.equal(transformCode(src), src);
});
test('transformations list has 8 entries', () => {
  assert.equal(transformations.length, 8);
});
```

- [ ] **Step 2: Run test — verify FAIL**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/convert-mechanical.test.mjs`
Expected: FAIL — `Cannot find module '../convert-mechanical.mjs'` (file belum ada).

- [ ] **Step 3: Implement convert-mechanical.mjs**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/convert-mechanical.mjs`:

```js
#!/usr/bin/env node
// convert-mechanical.mjs — konversi mekanis sintaks CI3 -> CI4 (SAFE regex only).
// Default --dry-run (print diff, tidak ubah). --apply untuk tulis file.

import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

// Urutan penting: transformasi spesifik dulu.
export const transformations = [
  { name: 'input->post',           pattern: /\$this->input->post\(/g,  replacement: '$this->request->getPost(' },
  { name: 'input->get',            pattern: /\$this->input->get\(/g,   replacement: '$this->request->getGet(' },
  { name: 'session set_userdata',  pattern: /\$this->session->set_userdata\(/g, replacement: 'session()->set(' },
  { name: 'session userdata',      pattern: /\$this->session->userdata\(/g,     replacement: 'session()->get(' },
  { name: 'session set_flashdata', pattern: /\$this->session->set_flashdata\(/g, replacement: 'session()->setFlashdata(' },
  { name: 'load->view',            pattern: /\$this->load->view\(/g,  replacement: 'view(' },
  { name: 'load->helper',          pattern: /\$this->load->helper\(/g, replacement: 'helper(' },
  { name: 'uri->segment',          pattern: /\$this->uri->segment\(/g, replacement: '$this->request->uri->getSegment(' },
];

export function transformCode(src) {
  let out = src;
  for (const t of transformations) out = out.replace(t.pattern, t.replacement);
  return out;
}

export function diffLines(src, out) {
  const a = src.split('\n'), b = out.split('\n');
  const n = Math.max(a.length, b.length);
  const changes = [];
  for (let i = 0; i < n; i++) {
    if (a[i] !== b[i]) changes.push({ line: i + 1, before: a[i] ?? '', after: b[i] ?? '' });
  }
  return changes;
}

function walkPhp(dir) {
  const out = [];
  for (const e of readdirSync(dir)) {
    const p = join(dir, e);
    if (statSync(p).isDirectory()) out.push(...walkPhp(p));
    else if (extname(p) === '.php') out.push(p);
  }
  return out;
}

function main() {
  const args = process.argv.slice(2);
  const apply = args.includes('--apply');
  const target = args.find(a => !a.startsWith('--'));
  if (!target) {
    console.error('Usage: node convert-mechanical.mjs <file|dir> [--apply]');
    process.exit(1);
  }
  const files = statSync(target).isDirectory() ? walkPhp(target) : [target];
  let total = 0;
  for (const f of files) {
    const src = readFileSync(f, 'utf8');
    const out = transformCode(src);
    if (src === out) continue;
    const changes = diffLines(src, out);
    total += changes.length;
    console.log(`\n=== ${f} (${changes.length} change${changes.length > 1 ? 's' : ''}) [${apply ? 'APPLIED' : 'DRY-RUN'}] ===`);
    for (const c of changes) {
      console.log(`  line ${c.line}:`);
      console.log(`    - ${c.before}`);
      console.log(`    + ${c.after}`);
    }
    if (apply) writeFileSync(f, out, 'utf8');
  }
  console.log(`\nTotal: ${total} change${total !== 1 ? 's' : ''} ${apply ? 'applied' : '(dry-run; --apply to write)'}.`);
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
```

- [ ] **Step 4: Run test — verify PASS**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/convert-mechanical.test.mjs`
Expected: PASS — semua 13 test lulus, `tests 13` `pass 13`.

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/scripts/convert-mechanical.mjs \
        .agents/skills/ci3-to-ci4-migration/scripts/__tests__/convert-mechanical.test.mjs
git commit -m "feat(ci3-to-ci4-skill): add convert-mechanical.mjs with tests (TDD)"
```

---

## Task 11: scripts/rename-files.mjs (TDD)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/rename-files.mjs`
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/rename-files.test.mjs`

- [ ] **Step 1: Tulis test**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/rename-files.test.mjs`:

```js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { toPascalCase, classifyTarget } from '../rename-files.mjs';

test('user_model.php -> UserModel.php', () => {
  assert.equal(toPascalCase('user_model.php'), 'UserModel.php');
});
test('auth.php -> Auth.php', () => {
  assert.equal(toPascalCase('auth.php'), 'Auth.php');
});
test('foo_bar_baz.php -> FooBarBaz.php', () => {
  assert.equal(toPascalCase('foo_bar_baz.php'), 'FooBarBaz.php');
});
test('hyphenated name split too', () => {
  assert.equal(toPascalCase('foo-bar.php'), 'FooBar.php');
});
test('models/ -> app/Models, rename true', () => {
  assert.deepEqual(classifyTarget('models/user_model.php'), { dest: 'app/Models', rename: true });
});
test('controllers/ -> app/Controllers, no rename', () => {
  assert.deepEqual(classifyTarget('controllers/Auth.php'), { dest: 'app/Controllers', rename: false });
});
test('libraries/ -> app/Libraries, no rename', () => {
  assert.deepEqual(classifyTarget('libraries/Auth_lib.php'), { dest: 'app/Libraries', rename: false });
});
test('helpers/ -> app/Helpers, no rename', () => {
  assert.deepEqual(classifyTarget('helpers/custom_helper.php'), { dest: 'app/Helpers', rename: false });
});
test('config/ -> app/Config, no rename', () => {
  assert.deepEqual(classifyTarget('config/config.php'), { dest: 'app/Config', rename: false });
});
test('views/ not handled (returns null)', () => {
  assert.equal(classifyTarget('views/foo.php'), null);
});
```

- [ ] **Step 2: Run test — verify FAIL**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/rename-files.test.mjs`
Expected: FAIL — `Cannot find module '../rename-files.mjs'`.

- [ ] **Step 3: Implement rename-files.mjs**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/rename-files.mjs`:

```js
#!/usr/bin/env node
// rename-files.mjs — rename+pindah file CI3 (application/) ke struktur CI4 (app/).
// models/user_model.php -> app/Models/UserModel.php (snake -> PascalCase)
// Default --dry-run. --apply untuk tulis.

import { readdirSync, statSync, mkdirSync, renameSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

export function toPascalCase(name) {
  const base = name.replace(/\.(php)$/i, '');
  return base.split(/[_\-]/).map(p => p.charAt(0).toUpperCase() + p.slice(1)).join('') + '.php';
}

export function classifyTarget(relPath) {
  const norm = relPath.replace(/\\/g, '/');
  if (norm.startsWith('models/'))      return { dest: 'app/Models',      rename: true  };
  if (norm.startsWith('controllers/')) return { dest: 'app/Controllers', rename: false };
  if (norm.startsWith('libraries/'))   return { dest: 'app/Libraries',   rename: false };
  if (norm.startsWith('helpers/'))     return { dest: 'app/Helpers',     rename: false };
  if (norm.startsWith('config/'))      return { dest: 'app/Config',      rename: false };
  return null; // views, hooks, third_party, dll — tidak dihandle
}

export function planRename(applicationDir, appDir) {
  const plan = [];
  function walk(dir) {
    for (const e of readdirSync(dir)) {
      const full = join(dir, e);
      const s = statSync(full);
      if (s.isDirectory()) { walk(full); continue; }
      if (extname(e) !== '.php') continue;
      const rel = full.slice(applicationDir.length + 1).replace(/\\/g, '/');
      const cls = classifyTarget(rel);
      if (!cls) continue;
      const newName = cls.rename ? toPascalCase(e) : e;
      plan.push({ from: full, to: join(appDir, cls.dest, newName), rel, dest: cls.dest, newName });
    }
  }
  walk(applicationDir);
  return plan;
}

function main() {
  const args = process.argv.slice(2);
  const apply = args.includes('--apply');
  const ci3App = args.find(a => !a.startsWith('--') && a.includes('application'));
  const ci4App = args.find(a => !a.startsWith('--') && a.includes('app'));
  if (!ci3App || !ci4App) {
    console.error('Usage: node rename-files.mjs <ci3-application-dir> <ci4-app-dir> [--apply]');
    process.exit(1);
  }
  const plan = planRename(ci3App, ci4App);
  if (plan.length === 0) { console.log('Tidak ada file untuk direname.'); return; }
  console.log(`${plan.length} file direncanakan [${apply ? 'APPLIED' : 'DRY-RUN'}]:`);
  for (const p of plan) {
    console.log(`  ${p.rel}  ->  ${p.dest}/${p.newName}`);
    if (apply) {
      mkdirSync(join(ci4App, p.dest), { recursive: true });
      renameSync(p.from, p.to);
    }
  }
  if (!apply) console.log('\n(dry-run; --apply untuk eksekusi)');
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
```

- [ ] **Step 4: Run test — verify PASS**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/rename-files.test.mjs`
Expected: PASS — 10 test lulus.

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/scripts/rename-files.mjs \
        .agents/skills/ci3-to-ci4-migration/scripts/__tests__/rename-files.test.mjs
git commit -m "feat(ci3-to-ci4-skill): add rename-files.mjs with tests (TDD)"
```

---

## Task 12: scripts/audit-ci3.mjs (TDD, ~17 pola — ENRICHED)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/audit-ci3.mjs`
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/audit-ci3.test.mjs`

- [ ] **Step 1: Tulis test (dengan pola tambahan dari sintesis)**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/audit-ci3.test.mjs`:

```js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { classifyFile, detectPatterns } from '../audit-ci3.mjs';

test('classify controller', () => {
  assert.equal(classifyFile('controllers/Auth.php'), 'controller');
});
test('classify model', () => {
  assert.equal(classifyFile('models/user_model.php'), 'model');
});
test('classify helper', () => {
  assert.equal(classifyFile('helpers/custom_helper.php'), 'helper');
});
test('classify config', () => {
  assert.equal(classifyFile('config/config.php'), 'config');
});
test('classify hook', () => {
  assert.equal(classifyFile('hooks/log.php'), 'hook');
});
test('classify third_party', () => {
  assert.equal(classifyFile('third_party/fpdf.php'), 'third_party');
});
test('classify view', () => {
  assert.equal(classifyFile('views/auth/login.php'), 'view');
});
test('detect extends CI_Controller', () => {
  assert.ok(detectPatterns(`class Auth extends CI_Controller {}`).includes('extends CI_Controller'));
});
test('detect extends CI_Model', () => {
  assert.ok(detectPatterns(`class User_model extends CI_Model {}`).includes('extends CI_Model'));
});
test('detect get_instance', () => {
  assert.ok(detectPatterns(`$this->CI = &get_instance();`).includes('get_instance'));
});
test('detect db-> Active Record', () => {
  assert.ok(detectPatterns(`$q = $this->db->get('users');`).includes('db-> (Active Record)'));
});
test('detect load->model', () => {
  assert.ok(detectPatterns(`$this->load->model('user_model');`).includes('load->model'));
});
test('detect load->library', () => {
  assert.ok(detectPatterns(`$this->load->library('session');`).includes('load->library'));
});
test('detect form_validation', () => {
  assert.ok(detectPatterns(`$this->form_validation->set_rules('f','L','required');`).includes('form_validation'));
});
test('detect upload->do_upload', () => {
  assert.ok(detectPatterns(`$this->upload->do_upload('f');`).includes('upload->do_upload'));
});
test('detect input->post', () => {
  assert.ok(detectPatterns(`$x = $this->input->post('x');`).includes('input->post'));
});
test('detect session->set_userdata', () => {
  assert.ok(detectPatterns(`$this->session->set_userdata('k', $v);`).includes('session->set_userdata'));
});
// --- pola tambahan dari sintesis ---
test('detect migration->', () => {
  assert.ok(detectPatterns(`$this->migration->latest();`).includes('migration->'));
});
test('detect dbforge->', () => {
  assert.ok(detectPatterns(`$this->dbforge->create_table('t');`).includes('dbforge->'));
});
test('detect pagination->', () => {
  assert.ok(detectPatterns(`$this->pagination->create_links();`).includes('pagination->'));
});
test('detect security->', () => {
  assert.ok(detectPatterns(`$this->security->xss_clean($v);`).includes('security->'));
});
test('detect config->item', () => {
  assert.ok(detectPatterns(`$v = $this->config->item('k');`).includes('config->item'));
});
test('detect parser->', () => {
  assert.ok(detectPatterns(`$this->parser->parse('t', $d);`).includes('parser->'));
});
test('no false positive on clean CI4 code', () => {
  const ci4 = `namespace App\\Controllers;\\nclass Auth extends BaseController {}`;
  assert.equal(detectPatterns(ci4).length, 0);
});
```

- [ ] **Step 2: Run test — verify FAIL**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/audit-ci3.test.mjs`
Expected: FAIL — `Cannot find module '../audit-ci3.mjs'`.

- [ ] **Step 3: Implement audit-ci3.mjs (~17 pola)**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/audit-ci3.mjs`:

```js
#!/usr/bin/env node
// audit-ci3.mjs — scan codebase CI3 (application/), laporkan ~17 pola + estimasi effort.

import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

export function classifyFile(relPath) {
  const norm = relPath.replace(/\\/g, '/');
  if (norm.includes('/controllers/')) return 'controller';
  if (norm.includes('/models/'))     return 'model';
  if (norm.includes('/libraries/'))  return 'library';
  if (norm.includes('/helpers/'))    return 'helper';
  if (norm.includes('/config/'))     return 'config';
  if (norm.includes('/hooks/'))      return 'hook';
  if (norm.includes('/third_party/')) return 'third_party';
  if (norm.includes('/views/'))      return 'view';
  return 'other';
}

export function detectPatterns(src) {
  const checks = [
    { name: 'extends CI_Controller', re: /extends\s+CI_Controller/ },
    { name: 'extends CI_Model',      re: /extends\s+CI_Model/ },
    { name: 'load->model',           re: /\$this->load->model\(/ },
    { name: 'load->library',         re: /\$this->load->library\(/ },
    { name: 'load->view',            re: /\$this->load->view\(/ },
    { name: 'input->post',           re: /\$this->input->post\(/ },
    { name: 'session->set_userdata', re: /\$this->session->set_userdata\(/ },
    { name: 'form_validation',       re: /\$this->form_validation/ },
    { name: 'get_instance',          re: /get_instance\(\)/ },
    { name: 'db-> (Active Record)',  re: /\$this->db->/ },
    { name: 'upload->do_upload',     re: /\$this->upload->do_upload\(/ },
    // --- pola tambahan dari sintesis ---
    { name: 'migration->',           re: /\$this->migration->/ },
    { name: 'dbforge->',             re: /\$this->dbforge->/ },
    { name: 'pagination->',          re: /\$this->pagination->/ },
    { name: 'security->',            re: /\$this->security->/ },
    { name: 'config->item',          re: /\$this->config->item\(/ },
    { name: 'parser->',              re: /\$this->parser->/ },
  ];
  return checks.filter(c => c.re.test(src)).map(c => c.name);
}

export function auditProject(applicationDir) {
  const files = [];
  (function walk(dir) {
    for (const e of readdirSync(dir)) {
      const full = join(dir, e);
      if (statSync(full).isDirectory()) walk(full);
      else if (extname(e) === '.php') files.push(full);
    }
  })(applicationDir);

  const report = { files: [], byType: {}, patterns: {}, customMy: [], thirdParty: [] };
  for (const f of files) {
    const src = readFileSync(f, 'utf8');
    const rel = f.slice(applicationDir.length + 1).replace(/\\/g, '/');
    const type = classifyFile(rel);
    const pats = detectPatterns(src);
    report.byType[type] = (report.byType[type] || 0) + 1;
    for (const p of pats) report.patterns[p] = (report.patterns[p] || 0) + 1;
    if (/\bclass\s+MY_/.test(src)) report.customMy.push(rel);
    if (/third_party/.test(rel)) report.thirdParty.push(rel);
    report.files.push({ rel, type, patterns: pats });
  }
  const ctrl = report.byType.controller || 0;
  report.effort = ctrl < 10 ? 'kecil' : ctrl < 25 ? 'sedang' : 'besar';
  return report;
}

function main() {
  const target = process.argv.slice(2).find(a => !a.startsWith('--'));
  if (!target) {
    console.error('Usage: node audit-ci3.mjs <ci3-application-dir>');
    process.exit(1);
  }
  const r = auditProject(target);
  console.log('=== AUDIT CI3 ===');
  console.log(`Total file PHP: ${r.files.length}`);
  console.log('Per type:', r.byType);
  console.log('Pola terdeteksi:', r.patterns);
  if (r.customMy.length) console.log('Custom MY_*:', r.customMy);
  if (r.thirdParty.length) console.log('Third-party:', r.thirdParty);
  console.log(`Estimasi effort: ${r.effort} (${r.byType.controller || 0} controller)`);
  console.log('\n--- JSON ---');
  console.log(JSON.stringify(r, null, 2));
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
```

- [ ] **Step 4: Run test — verify PASS**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/audit-ci3.test.mjs`
Expected: PASS — 23 test lulus (7 classify + 16 detect incl. 6 baru + 0 false-positive... hitung: 7 classify + 17 detect patterns + 1 false positive = 25? recheck: classify tests = 7 (controller,model,helper,config,hook,third_party,view). detect tests = 17 (extends CI_Controller, CI_Model, get_instance, db->, load->model, load->library, form_validation, upload->do_upload, input->post, session->set_userdata, migration->, dbforge->, pagination->, security->, config->item, parser-> = 16... + no false positive = 17). Total = 7 + 16 + 1 = 24. Lihat output aktual untuk count pasti.)

> Catatan: jumlah test pasti diverifikasi dari output `node --test`. Yang penting: semua PASS, termasuk 6 pola baru (migration->, dbforge->, pagination->, security->, config->item, parser->).

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/scripts/audit-ci3.mjs \
        .agents/skills/ci3-to-ci4-migration/scripts/__tests__/audit-ci3.test.mjs
git commit -m "feat(ci3-to-ci4-skill): add audit-ci3.mjs with tests (TDD, ~17 patterns) [enriched from synthesis]"
```

---

## Task 13: scripts/feature-parity-check.mjs (TDD)

**Files:**
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/feature-parity-check.mjs`
- Create: `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/feature-parity-check.test.mjs`

- [ ] **Step 1: Tulis test**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/__tests__/feature-parity-check.test.mjs`:

```js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { parseCi3Routes, parseCi4Routes, compareRoutes } from '../feature-parity-check.mjs';

test('parse CI3 simple route', () => {
  const r = parseCi3Routes(`$route['foo/bar'] = 'foo/bar';`);
  assert.equal(r.length, 1);
  assert.equal(r[0].route, 'foo/bar');
  assert.equal(r[0].handler, 'foo/bar');
  assert.equal(r[0].method, 'ANY');
});
test('parse CI3 param route', () => {
  const r = parseCi3Routes(`$route['user/(:num)'] = 'user/view/$1';`);
  assert.equal(r[0].route, 'user/(:num)');
  assert.equal(r[0].handler, 'user/view/$1');
});
test('parse CI3 verb route', () => {
  const r = parseCi3Routes(`$route['x']['post'] = 'c/m';`);
  assert.equal(r[0].method, 'POST');
});
test('parse CI3 skips default_controller/404_override', () => {
  const r = parseCi3Routes(`$route['default_controller'] = 'welcome'; $route['foo'] = 'c/m';`);
  assert.equal(r.length, 1);
  assert.equal(r[0].route, 'foo');
});
test('parse CI4 get route', () => {
  const r = parseCi4Routes(`$routes->get('foo/bar', 'Foo::bar');`);
  assert.equal(r.length, 1);
  assert.equal(r[0].method, 'GET');
  assert.equal(r[0].route, 'foo/bar');
  assert.equal(r[0].handler, 'Foo::bar');
});
test('parse CI4 post route', () => {
  const r = parseCi4Routes(`$routes->post('x', 'C::m');`);
  assert.equal(r[0].method, 'POST');
});
test('parse CI4 add() maps to ANY', () => {
  const r = parseCi4Routes(`$routes->add('x', 'C::m');`);
  assert.equal(r[0].method, 'ANY');
});
test('compare finds missing routes', () => {
  const ci3 = [
    { method: 'ANY', route: 'a/b', handler: 'a@b' },
    { method: 'ANY', route: 'c/d', handler: 'c@d' },
  ];
  const ci4 = [{ method: 'ANY', route: 'a/b', handler: 'A::b' }];
  const r = compareRoutes(ci3, ci4);
  assert.equal(r.missing.length, 1);
  assert.equal(r.missing[0].route, 'c/d');
});
test('ANY in CI4 covers verb-specific CI3 route', () => {
  const ci3 = [{ method: 'POST', route: 'a/b', handler: 'a@b' }];
  const ci4 = [{ method: 'ANY', route: 'a/b', handler: 'A::b' }];
  assert.equal(compareRoutes(ci3, ci4).missing.length, 0);
});
test('counts correct', () => {
  const ci3 = [{ method: 'ANY', route: 'a', handler: 'x' }, { method: 'ANY', route: 'b', handler: 'y' }];
  const ci4 = [{ method: 'ANY', route: 'a', handler: 'X' }];
  const r = compareRoutes(ci3, ci4);
  assert.equal(r.ci3Count, 2);
  assert.equal(r.ci4Count, 1);
});
```

- [ ] **Step 2: Run test — verify FAIL**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/feature-parity-check.test.mjs`
Expected: FAIL — `Cannot find module '../feature-parity-check.mjs'`.

- [ ] **Step 3: Implement feature-parity-check.mjs**

Buat `.agents/skills/ci3-to-ci4-migration/scripts/feature-parity-check.mjs`:

```js
#!/usr/bin/env node
// feature-parity-check.mjs — bandingkan struktur route CI3 vs CI4, laporkan yg belum termigrasi.

import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

export function parseCi3Routes(src) {
  const routes = [];
  // $route['x']['post'] = 'c/m';  (verb-specific, cek dulu sebelum simple)
  const verb = /\$route\[(['"])([^'"]+)\1\]\[(['"])(get|post|put|delete|patch)\3\]\s*=\s*(['"])([^'"]+)\5\s*;?/gi;
  let m;
  while ((m = verb.exec(src))) {
    routes.push({ method: m[4].toUpperCase(), route: m[2], handler: m[6] });
  }
  // $route['foo/bar'] = 'c/m';  (simple, skip default_controller / 404_override / translate_uri_dashes)
  const simple = /\$route\[(['"])([^'"]+)\1\]\s*=\s*(['"])([^'"]+)\3\s*;?/g;
  while ((m = simple.exec(src))) {
    if (['default_controller', '404_override', 'translate_uri_dashes'].includes(m[2])) continue;
    routes.push({ method: 'ANY', route: m[2], handler: m[4] });
  }
  return routes;
}

export function parseCi4Routes(src) {
  const routes = [];
  const re = /\$routes->(get|post|put|delete|patch|match|add)\(\s*(['"])([^'"]+)\2\s*,\s*(['"])([^'"]+)\4/gi;
  let m;
  while ((m = re.exec(src))) {
    let method = m[1].toLowerCase();
    if (method === 'add' || method === 'match') method = 'ANY';
    else method = method.toUpperCase();
    routes.push({ method, route: m[3], handler: m[5] });
  }
  return routes;
}

export function compareRoutes(ci3, ci4) {
  const ci4Any = new Set(ci4.filter(r => r.method === 'ANY').map(r => r.route));
  const ci4Set = new Set(ci4.map(r => `${r.method} ${r.route}`));
  const missing = ci3.filter(r => {
    if (ci4Any.has(r.route)) return false;        // CI4 ANY covers any verb
    return !ci4Set.has(`${r.method} ${r.route}`) && !ci4Set.has(`ANY ${r.route}`);
  });
  return { ci3Count: ci3.length, ci4Count: ci4.length, missing };
}

function main() {
  const args = process.argv.slice(2);
  const ci3RoutesFile = args.find(a => a.includes('routes.php') && !a.startsWith('--'));
  const ci4RoutesFile = args.find(a => a.includes('Routes.php') && !a.startsWith('--'));
  if (!ci3RoutesFile || !ci4RoutesFile) {
    console.error('Usage: node feature-parity-check.mjs <ci3-routes.php> <ci4-Routes.php>');
    process.exit(1);
  }
  const ci3 = parseCi3Routes(readFileSync(ci3RoutesFile, 'utf8'));
  const ci4 = parseCi4Routes(readFileSync(ci4RoutesFile, 'utf8'));
  const r = compareRoutes(ci3, ci4);
  console.log('=== FEATURE PARITY (route) ===');
  console.log(`CI3 routes: ${r.ci3Count} | CI4 routes: ${r.ci4Count} | missing: ${r.missing.length}`);
  if (r.missing.length) {
    console.log('\nRoute CI3 belum ada di CI4:');
    for (const m of r.missing) console.log(`  ${m.method} ${m.route}  ->  ${m.handler}`);
  } else {
    console.log('\n✓ Semua route CI3 sudah ada di CI4.');
  }
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
```

- [ ] **Step 4: Run test — verify PASS**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/feature-parity-check.test.mjs`
Expected: PASS — 11 test lulus.

- [ ] **Step 5: Commit**

```bash
git add .agents/skills/ci3-to-ci4-migration/scripts/feature-parity-check.mjs \
        .agents/skills/ci3-to-ci4-migration/scripts/__tests__/feature-parity-check.test.mjs
git commit -m "feat(ci3-to-ci4-skill): add feature-parity-check.mjs with tests (TDD)"
```

---

## Task 14: Run full test suite + smoke test scripts CLI

**Files:**
- Tidak ada file baru — verifikasi semua script

- [ ] **Step 1: Run semua test sekaligus**

Run: `cd .agents/skills/ci3-to-ci4-migration && node --test scripts/__tests__/`
Expected: 4 test file, total ±55 test (convert 13 + rename 10 + audit ±24 + parity 11), semua PASS. Output: `# pass <N>` dengan 0 fail. (Jumlah pasti dari output `node --test`.)

- [ ] **Step 2: Smoke test convert-mechanical.mjs CLI (dry-run)**

Buat file sample sementara untuk uji CLI:
```bash
cat > /tmp/ci3-sample.php << 'EOF'
<?php
class Sample extends CI_Controller {
    public function index() {
        $this->load->helper('form');
        $x = $this->input->post('name');
        $this->session->set_userdata('k', $x);
        $this->load->view('sample', ['x' => $x]);
    }
}
EOF
node .agents/skills/ci3-to-ci4-migration/scripts/convert-mechanical.mjs /tmp/ci3-sample.php
```
Expected: output `[DRY-RUN]` dengan diff — `load->helper`→`helper`, `input->post`→`request->getPost`, `session->set_userdata`→`session()->set`, `load->view`→`view`. File `/tmp/ci3-sample.php` TIDAK berubah (dry-run).

Verifikasi file tidak berubah:
```bash
grep "this->input->post" /tmp/ci3-sample.php && echo "OK: file unchanged (dry-run)"
```
Expected: `OK: file unchanged (dry-run)`.

- [ ] **Step 3: Smoke test audit-ci3.mjs CLI**

```bash
node .agents/skills/ci3-to-ci4-migration/scripts/audit-ci3.mjs /tmp 2>/dev/null || true
```
(Bila `/tmp` tidak punya `application/`, script tetap jalan di folder PHP apapun — verifikasi CLI tidak crash & output JSON valid.)

Atau buat mini CI3 fixture:
```bash
mkdir -p /tmp/ci3app/application/controllers
cat > /tmp/ci3app/application/controllers/Auth.php << 'EOF'
<?php
class Auth extends CI_Controller {
    public function login() {
        $this->load->model('user_model');
        $this->load->view('login');
    }
}
EOF
node .agents/skills/ci3-to-ci4-migration/scripts/audit-ci3.mjs /tmp/ci3app/application
```
Expected: output `=== AUDIT CI3 ===` + JSON dengan `byType: { controller: 1 }`, patterns `extends CI_Controller`, `load->model`, `load->view`, effort `kecil`.

- [ ] **Step 4: Bersihkan sample file**

```bash
rm -rf /tmp/ci3-sample.php /tmp/ci3app
```

- [ ] **Step 5: Tidak ada perubahan file — skip commit, lanjut eval**

---

## Task 15: Eval — jalankan 3 test prompt (integration test skill)

**Files:**
- Tidak ada file baru — verifikasi behavior skill

Reference rubrik: spec `DOCS/superpowers/specs/2026-06-29-ci3-to-ci4-migration-skill-design.md` section "Eval Plan".

- [ ] **Step 1: Verifikasi skill discoverable**

Run: `ls .agents/skills/ci3-to-ci4-migration/SKILL.md && echo OK`
Expected: `OK` (skill siap dipakai ZCode).

- [ ] **Step 2: Jalankan test prompt 1 (fresh turn)**

Buka fresh ZCode turn. Ketik:
> *"aku punya project ci3 lama di `D:\laragon\www\simtold`, mau migrate ke ci4. ada sekitar 20 controller 15 model, ada library custom buat auth pake &get_instance(). gimana mulainya?"*

**Verifikasi rubrik:**
- [ ] Skill trigger otomatis (description pushy)
- [ ] Mulai step 1 audit → sebut `audit-ci3.mjs` / `00-audit-checklist.md`
- [ ] Decision tree: project besar + custom library → pilih **incremental per-modul**, prioritaskan library auth dulu
- [ ] Sorot `&get_instance()` di `06-libraries-helpers.md` sebagai stuck point
- [ ] Impact analysis: petakan dependency controller→library sebelum konversi (dari `00-audit-checklist.md`)
- [ ] Tidak langsung konversi 20 controller sekaligus

- [ ] **Step 3: Jalankan test prompt 2 (fresh turn)**

> *"bantuin convert controller Auth.php ini dr ci3 ke ci4, bingungnya di session flashdata sama form validation. ini file nya `application/controllers/Auth.php`"*

**Verifikasi rubrik:**
- [ ] Baca `references/03-controllers.md` + `07-services.md` sebelum handle
- [ ] Cek file sudah di-move ke `app/Controllers/` atau konversi in-place
- [ ] Mapping `set_flashdata` → `setFlashdata`, `form_validation->set_rules` → `service('validation')`
- [ ] Kasih `namespace App\Controllers;` + `extends BaseController`
- [ ] Highlight: method controller CI4 WAJIB `return`

- [ ] **Step 4: Jalankan test prompt 3 (fresh turn)**

> *"ci4 project udah jalan di `D:\laragon\www\simv4`, tinggal mindahin model ci3 dari `D:\laragon\www\simold\application\models`. banyak yg pake active record + query builder"*

**Verifikasi rubrik:**
- [ ] Skip bootstrap (CI4 sudah ada)
- [ ] Sebut `rename-files.mjs` untuk `foo_model.php` → `FooModel.php`
- [ ] Baca `04-models-db.md`: Active Record → Query Builder, wajib `$table/$primaryKey/$allowedFields`
- [ ] Jalankan `convert-mechanical.mjs --dry-run` dulu, minta review sebelum `--apply`
- [ ] Feature-parity check di akhir

- [ ] **Step 5: Catat hasil eval + iterasi jika ada fail**

Jika ada rubrik yang gagal ( ❌ ), catat di `DOCS/superpowers/specs/2026-06-29-ci3-to-ci4-migration-eval-results.md` lalu revisi SKILL.md / reference terkait. Ulangi prompt yg gagal sampai lulus. Jika semua lulus (3/3), lanjut Step 6.

- [ ] **Step 6: Commit hasil eval (jika ada file hasil)**

```bash
git add DOCS/superpowers/specs/2026-06-29-ci3-to-ci4-migration-eval-results.md 2>/dev/null
git commit -m "docs(ci3-to-ci4-skill): eval results" --allow-empty
```

---

## Self-Review (dilakukan setelah plan lengkap)

**1. Spec coverage** — cek tiap section spec (revisi 2) punya task:
- Identitas/frontmatter → Task 1 ✓
- Struktur folder progressive disclosure (incl. ref 11 + output-quality-checklist) → Task 1-9 ✓
- SKILL.md body (workflow/decision tree/router/prinsip, router row ref 11) → Task 1 ✓
- 12 references (00-11, dgn expand 00/01/03/04/07 + new 11) → Task 3,4,5,6,7 ✓
- 4 scripts API (audit ~17 pola) → Task 10,11,12,13 ✓
- 3 assets (mapping-table diperluas, feature-parity-checklist, output-quality-checklist) → Task 2,8,9 ✓
- Data flow workflow 7 langkah (incl. code-quality gate step 6) → Task 1 (SKILL.md) ✓
- Error handling & safety (dry-run, COMMENT jangan DELETE, batas script) → Task 1 (prinsip) + Task 10 (script tidak sentuh berisiko) ✓
- Eval plan (3 test prompts + rubrik, incl. impact analysis di prompt 1) → Task 15 ✓
- Out of scope → di spec, tidak perlu task ✓
- Synthesis notes (6 incorporate) → Task 2 (mapping migration/security/pagination), Task 3 (impact+spark), Task 4 (REST+Entity), Task 5 (logging), Task 7 (ref 11), Task 9 (quality-checklist), Task 12 (audit ~17 pola) ✓

**2. Placeholder scan** — tidak ada TBD/TODO/"implement later"/"similar to Task N". Semua step punya code/commands nyata. (Task 12 Step 4 ada catatan "jumlah pasti diverifikasi dari output" — itu bukan placeholder kode, itu acknowledgment bahwa test count diverifikasi saat run, code-nya complete.) ✓

**3. Type consistency** — nama function konsisten cross-task:
- `transformCode`, `transformations`, `diffLines` (Task 10) — dipakai hanya Task 10 ✓
- `toPascalCase`, `classifyTarget`, `planRename` (Task 11) ✓
- `classifyFile`, `detectPatterns`, `auditProject` (Task 12) ✓
- `parseCi3Routes`, `parseCi4Routes`, `compareRoutes` (Task 13) ✓
- Nama script di SKILL.md router (Task 1) match nama file di Task 10-13: `audit-ci3.mjs`, `convert-mechanical.mjs`, `rename-files.mjs`, `feature-parity-check.mjs` ✓
- Reference filename di SKILL.md router (Task 1) match file dibuat Task 3-7: `00-audit-checklist.md` s.d. `11-migration-seeder-security.md` ✓
- Asset filename di SKILL.md (Task 1) match file dibuat Task 2,8,9: `mapping-table.md`, `feature-parity-checklist.md`, `output-quality-checklist.md` ✓
- audit-ci3 detectPatterns di Task 12 cek pola `migration->`, `dbforge->`, `pagination->`, `security->`, `config->item`, `parser->` — match yang disebut di reference 11 (Task 7) "Audit hook" + mapping-table (Task 2) ✓

Tidak ada inkonsistensi ditemukan.

---

## Execution Handoff

Plan complete and saved to `DOCS/superpowers/plans/2026-06-29-ci3-to-ci4-migration-skill.md` (revisi 2, 15 task). Two execution options:

**1. Subagent-Driven (recommended)** — dispatch fresh subagent per task, review antar task, iterasi cepat.

**2. Inline Execution** — eksekusi task di session ini pakai executing-plans, batch execution dengan checkpoint.

Which approach?
