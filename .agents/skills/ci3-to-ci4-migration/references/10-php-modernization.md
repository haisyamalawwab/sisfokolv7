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
