<?php

namespace App\Support\Crudlfix;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Configuration object for CRUDLFIX trait.
 *
 * Build via array: CrudlfixConfig::make([...])
 * Or via fluent:  CrudlfixConfig::make()->model(Siswa::class)->view('academic.siswa')
 */
class CrudlfixConfig
{
    /** @var class-string<Model> */
    public string $model;

    /** Blade view prefix (e.g. 'academic.siswa') */
    public string $view;

    /** Route name prefix (e.g. 'academic.siswa') */
    public string $route;

    /** Permission prefix for Gate::authorize (e.g. 'siswa' → siswa.view, siswa.create, etc.) */
    public ?string $authorize = null;

    /**
     * Authorization type:
     *   - 'policy'     → Gate::authorize('ability', $model) [policy-based]
     *   - 'permission' → Gate::authorize('prefix.action')   [direct Spatie permission]
     *   - null/absent  → No in-controller authorization (relies on route middleware)
     */
    public ?string $authType = null;

    /** Eager-load relations for index */
    public ?array $with = null;

    /** Eager-load relations for show */
    public ?array $showWith = null;

    /** Searchable fields */
    public ?array $search = null;

    /** Filter definitions: ['field' => ['column' => 'db_col', 'operator' => '=']] */
    public ?array $filters = null;

    /** Validation rules — array of rules or closure */
    public $rules = null;

    /** FormRequest class (alternative to inline rules) */
    public ?string $requestClass = null;

    /** Extra view data to merge into every view */
    public ?array $viewData = null;

    /** Pagination per page */
    public int $perPage = 15;

    /** Default sort field */
    public ?string $defaultSort = 'created_at';

    /** Default sort direction */
    public string $defaultDir = 'desc';

    /** Export columns (for CSV export) */
    public ?array $exportColumns = null;

    /** Custom variable name for index view (overrides pluralVar) */
    public ?string $varName = null;

    /** Custom scope methods to call on query */
    public ?array $scope = null;

    /** Flash message template — {action} and {name} placeholders */
    public ?array $messages = null;

    /** Cascading select definitions */
    public ?array $cascades = null;

    /** Search select definitions (Select2 replacement) */
    public ?array $searchSelects = null;

    /** Data table configuration */
    public ?array $dataTable = null;

    /** Live edit configuration */
    public ?array $liveEdit = null;

    // ─── CONSTRUCTORS ───────────────────────────────────────────────

    public static function make(array $config = []): static
    {
        $instance = new static();
        foreach ($config as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    // ─── DERIVED NAMES ──────────────────────────────────────────────

    /**
     * Singular variable name from model class.
     * e.g. App\Models\Siswa → 'siswa'
     */
    public function singularVar(): string
    {
        return Str::camel(class_basename($this->model));
    }

    /**
     * Plural variable name from model class.
     * e.g. App\Models\Siswa → 'siswas'
     */
    public function pluralVar(): string
    {
        return Str::camel(Str::plural(class_basename($this->model)));
    }

    /**
     * Default flash message for an action.
     */
    public function message(string $action, ?Model $model = null): string
    {
        // Custom messages override
        if ($this->messages && isset($this->messages[$action])) {
            $msg = $this->messages[$action];
            $name = $model?->{$this->nameColumn()} ?? '';
            return str_replace(['{action}', '{name}'], [$action, $name], $msg);
        }

        $label = $this->label();
        $name = $model?->{$this->nameColumn()} ?? '';

        return match ($action) {
            'created' => "Data {$label} {$name} berhasil ditambahkan.",
            'updated' => "Data {$label} {$name} berhasil diperbarui.",
            'deleted' => "Data {$label} {$name} berhasil dihapus.",
            default   => "Operasi {$action} berhasil.",
        };
    }

    /**
     * Human-readable label from model class.
     * e.g. App\Models\Siswa → 'Siswa'
     */
    public function label(): string
    {
        return class_basename($this->model);
    }

    /**
     * Guess the 'name' column for flash messages.
     */
    public function nameColumn(): string
    {
        // Try common name columns
        $candidates = ['nama', 'name', 'title', 'label', 'username', 'code', 'kode'];
        foreach ($candidates as $col) {
            if (in_array($col, $this->model::first()?->getAttributes() ?? [])) {
                return $col;
            }
        }
        return 'id';
    }
}
