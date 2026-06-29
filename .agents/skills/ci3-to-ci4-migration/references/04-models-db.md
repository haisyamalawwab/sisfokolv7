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
