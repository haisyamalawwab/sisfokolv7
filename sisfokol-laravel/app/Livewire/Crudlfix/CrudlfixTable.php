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

        $this->initTable($this->getConfigProperty());
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

    public function render()
    {
        $rows = $this->getRowsProperty();

        return view('livewire.crudlfix.table', [
            'rows' => $rows,
        ]);
    }
}
