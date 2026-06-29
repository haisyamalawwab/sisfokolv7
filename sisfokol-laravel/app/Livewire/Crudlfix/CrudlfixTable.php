<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire data table component for Crudlfix.
 *
 * Accepts raw arrays and builds CrudlfixConfig internally.
 */
class CrudlfixTable extends Component
{
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixTable;
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixActions;
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixAuth;

    // Raw config (Livewire-safe)
    public string $modelClass = '';
    public string $routePrefix = '';
    public array $columns = [];
    public array $searchFields = [];
    public array $withRelations = [];
    public array $filterConfig = [];
    public int $perPage = 15;
    public string $defaultSort = 'created_at';
    public string $defaultDir = 'desc';
    public ?array $exportColumns = null;
    public ?string $permissionPrefix = null;
    public ?string $authMode = null;

    // Behavior flags
    public bool $inlineEdit = true;   // false → edit navigates to route (index-only mode)
    public bool $showEdit = true;     // false → hide edit button (e.g. no edit view)
    public bool $showDetail = true;   // false → hide detail button (e.g. no show view)

    // Built internally
    protected ?CrudlfixConfig $_config = null;

    public function mount(
        string $model,
        string $route,
        array $columns = [],
        array $search = [],
        array $with = [],
        array $filters = [],
        int $perPage = 15,
        string $defaultSort = 'created_at',
        string $defaultDir = 'desc',
        ?array $exportColumns = null,
        ?string $authorize = null,
        ?string $authType = null,
        bool $inlineEdit = true,
        bool $showEdit = true,
        bool $showDetail = true,
    ): void {
        $this->modelClass = $model;
        $this->routePrefix = $route;
        $this->columns = $columns;
        $this->searchFields = $search;
        $this->withRelations = $with;
        $this->filterConfig = $filters;
        $this->perPage = $perPage;
        $this->defaultSort = $defaultSort;
        $this->defaultDir = $defaultDir;
        $this->exportColumns = $exportColumns;
        $this->permissionPrefix = $authorize;
        $this->authMode = $authType;
        $this->inlineEdit = $inlineEdit;
        $this->showEdit = $showEdit;
        $this->showDetail = $showDetail;

        $this->initTable($this->getConfigProperty());

        // Pick up initial search from the URL query param (e.g. ?search=Ahmad)
        // so deep-links and traditional search links keep working on first load.
        $this->searchQuery = (string) (request()->input('search', ''));
    }

    public function getConfigProperty(): CrudlfixConfig
    {
        if ($this->_config === null) {
            $this->_config = CrudlfixConfig::make([
                'model' => $this->modelClass,
                'route' => $this->routePrefix,
                'search' => $this->searchFields,
                'with' => $this->withRelations,
                'filters' => $this->filterConfig,
                'perPage' => $this->perPage,
                'defaultSort' => $this->defaultSort,
                'defaultDir' => $this->defaultDir,
                'exportColumns' => $this->exportColumns,
                'authorize' => $this->permissionPrefix,
                'authType' => $this->authMode,
            ]);
        }
        return $this->_config;
    }

    /**
     * Dispatch edit request to the parent CrudlfixPage for inline mode switching.
     */
    public function editRecord(int $id): void
    {
        $this->dispatch('crudlfix-edit', ['id' => $id]);
    }

    public function render()
    {
        // Authorize viewAny on every render (including AJAX) — Livewire requests
        // bypass the controller's in-method authorization otherwise
        $this->authorizeCrudlfixAction('viewAny');

        $rows = $this->getRowsProperty();

        return view('livewire.crudlfix.table', [
            'rows' => $rows,
        ]);
    }
}
