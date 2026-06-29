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
