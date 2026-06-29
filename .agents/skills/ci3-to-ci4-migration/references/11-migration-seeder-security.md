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
