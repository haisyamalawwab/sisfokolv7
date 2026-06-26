<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire page orchestrator for Crudlfix.
 *
 * Manages CRUD mode switching (index/create/edit/show) and
 * coordinates between Table, Form, and Modal sub-components.
 *
 * Accepts raw arrays and builds CrudlfixConfig internally.
 */
class CrudlfixPage extends Component
{
    // Raw config arrays (Livewire-safe)
    public string $modelClass = '';
    public string $viewPrefix = '';
    public string $routePrefix = '';
    public array $columns = [];
    public array $formFields = [];
    public array $searchFields = [];
    public array $withRelations = [];
    public array $filterConfig = [];
    public array $validationRules = [];
    public array $extraViewData = [];
    public int $perPage = 15;
    public string $defaultSort = 'created_at';
    public string $defaultDir = 'desc';
    public ?string $permissionPrefix = null;
    public ?string $authMode = null;

    // State
    public string $mode = 'index'; // index|create|edit|show
    public ?int $editId = null;
    public string $title = '';

    // Built internally
    protected ?CrudlfixConfig $_config = null;

    protected $listeners = [
        'crudlfix-saved' => 'handleSaved',
    ];

    public function mount(
        string $model,
        string $view,
        string $route,
        array $columns = [],
        array $formFields = [],
        array $search = [],
        array $with = [],
        array $filters = [],
        array $rules = [],
        array $viewData = [],
        int $perPage = 15,
        string $defaultSort = 'created_at',
        string $defaultDir = 'desc',
        ?string $authorize = null,
        ?string $authType = null,
        string $action = 'index',
        ?int $editId = null,
    ): void {
        // Store raw values (Livewire-safe)
        $this->modelClass = $model;
        $this->viewPrefix = $view;
        $this->routePrefix = $route;
        $this->columns = $columns;
        $this->formFields = $formFields;
        $this->searchFields = $search;
        $this->withRelations = $with;
        $this->filterConfig = $filters;
        $this->validationRules = $rules;
        $this->extraViewData = $viewData;
        $this->perPage = $perPage;
        $this->defaultSort = $defaultSort;
        $this->defaultDir = $defaultDir;
        $this->permissionPrefix = $authorize;
        $this->authMode = $authType;

        $this->title = ucfirst(str_replace('.', ' ', $view));

        // Set mode
        $this->mode = in_array($action, ['index', 'create', 'edit', 'show']) ? $action : 'index';
        $this->editId = $editId;
    }

    /**
     * Build CrudlfixConfig from raw arrays.
     */
    public function getConfigProperty(): CrudlfixConfig
    {
        if ($this->_config === null) {
            $this->_config = CrudlfixConfig::make([
                'model' => $this->modelClass,
                'view' => $this->viewPrefix,
                'route' => $this->routePrefix,
                'search' => $this->searchFields,
                'with' => $this->withRelations,
                'filters' => $this->filterConfig,
                'rules' => $this->validationRules,
                'perPage' => $this->perPage,
                'defaultSort' => $this->defaultSort,
                'defaultDir' => $this->defaultDir,
                'authorize' => $this->permissionPrefix,
                'authType' => $this->authMode,
            ]);
        }
        return $this->_config;
    }

    public function setMode(string $mode, ?int $id = null): void
    {
        $this->mode = $mode;
        $this->editId = $id;
    }

    public function handleSaved(array $data): void
    {
        $this->mode = 'index';
        $this->editId = null;
    }

    public function render()
    {
        return view('livewire.crudlfix.page');
    }
}
