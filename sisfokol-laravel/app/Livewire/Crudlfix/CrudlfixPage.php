<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire page orchestrator for Crudlfix.
 *
 * Manages CRUD mode switching (index/create/edit/show) and coordinates
 * between Table and Form sub-components.
 *
 * When a `controller` FQCN is provided, backend config (model, route, search,
 * with, rules, auth, viewData) is resolved from the controller's
 * getCrudlfixConfig() at mount — keeping the controller as the single source
 * of truth. View-layer config (columns, formFields) still comes from the
 * Blade view since CrudlfixConfig does not carry display definitions.
 *
 * Falls back to raw arrays for backward compatibility (pilot pattern).
 */
class CrudlfixPage extends Component
{
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixAuth;

    // Raw config (Livewire-safe)
    public ?string $controllerClass = null;
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
    public $perPage = 25;
    public string $defaultSort = 'created_at';
    public string $defaultDir = 'desc';
    public ?array $sortKeys = null;
    public ?string $permissionPrefix = null;
    public ?string $authMode = null;
    public bool $showDetail = true;   // auto: hidden when no show.blade.php exists

    // State
    public string $mode = 'index'; // index|create|edit|show
    public ?int $editId = null;
    public string $title = '';

    // Built internally
    protected ?CrudlfixConfig $_config = null;

    protected $listeners = [
        'crudlfix-saved' => 'handleSaved',
        'crudlfix-edit' => 'handleEditRequest',
    ];

    public function mount(
        ?string $controller = null,
        string $model = '',
        string $view = '',
        string $route = '',
        array $columns = [],
        array $formFields = [],
        array $search = [],
        array $with = [],
        array $filters = [],
        array $rules = [],
        array $viewData = [],
        $perPage = 25,
        string $defaultSort = 'created_at',
        string $defaultDir = 'desc',
        ?array $sortKeys = null,
        ?string $authorize = null,
        ?string $authType = null,
        string $action = 'index',
        ?int $editId = null,
    ): void {
        $this->controllerClass = $controller;
        $this->columns = $columns;
        $this->formFields = $formFields;

        if ($controller) {
            // Resolve backend config from the controller (single source of truth)
            $cfg = app($controller)->getCrudlfixConfig();
            $this->modelClass = $cfg->model;
            $this->viewPrefix = $cfg->view;
            $this->routePrefix = $cfg->route;
            $this->searchFields = $cfg->search ?? [];
            $this->withRelations = $cfg->with ?? [];
            $this->filterConfig = $cfg->filters ?? [];
            $this->validationRules = []; // form resolves rules from controller
            $this->extraViewData = $cfg->viewData ?? [];
            $this->perPage = $cfg->perPage ?? 25;
            $this->defaultSort = $cfg->defaultSort ?? 'created_at';
            $this->defaultDir = $cfg->defaultDir ?? 'desc';
            $this->sortKeys = $cfg->sortKeys ?? null;
            $this->permissionPrefix = $cfg->authorize;
            $this->authMode = $cfg->authType;

            $this->title = class_basename($cfg->model);
        } else {
            // Backward-compatible flat-array mode (pilot pattern)
            $this->modelClass = $model;
            $this->viewPrefix = $view;
            $this->routePrefix = $route;
            $this->searchFields = $search;
            $this->withRelations = $with;
            $this->filterConfig = $filters;
            $this->validationRules = $rules;
            $this->extraViewData = $viewData;
            $this->perPage = $perPage;
            $this->defaultSort = $defaultSort;
            $this->defaultDir = $defaultDir;
            $this->sortKeys = $sortKeys;
            $this->permissionPrefix = $authorize;
            $this->authMode = $authType;

            $this->title = ucfirst(str_replace('.', ' ', $view));
        }

        // Auto-hide detail button when no show view exists (prevents "view not found" 404)
        $this->showDetail = view()->exists($this->viewPrefix . '.show');

        // [2026-06-29 | AG] Resolve action and editId from request if present (for URL fallbacks when view doesn't exist)
        $resolvedAction = request()->input('action', $action);
        $resolvedEditId = request()->input('editId') !== null ? (int) request()->input('editId') : $editId;

        // Set mode
        // $this->mode = in_array($action, ['index', 'create', 'edit', 'show']) ? $action : 'index'; // [2026-06-29 | AG] commented for fallback support
        // $this->editId = $editId; // [2026-06-29 | AG] commented for fallback support
        $this->mode = in_array($resolvedAction, ['index', 'create', 'edit', 'show']) ? $resolvedAction : 'index';
        $this->editId = $resolvedEditId;
    }

    /**
     * Build CrudlfixConfig from raw arrays (for child components that need it).
     */
    public function getConfigProperty(): CrudlfixConfig
    {
        if ($this->_config === null) {
            if ($this->controllerClass) {
                $this->_config = app($this->controllerClass)->getCrudlfixConfig();
            } else {
                $this->_config = CrudlfixConfig::make([
                    'model' => $this->modelClass,
                    'view' => $this->viewPrefix,
                    'route' => $this->routePrefix,
                    'search' => $this->searchFields,
                    'with' => $this->withRelations,
                    'filters' => $this->filterConfig,
                    'rules' => $this->validationRules,
                    'perPage' => $this->perPage === 'all' ? 1000 : (int)$this->perPage,
                    'defaultSort' => $this->defaultSort,
                    'defaultDir' => $this->defaultDir,
                    'sortKeys' => $this->sortKeys,
                    'authorize' => $this->permissionPrefix,
                    'authType' => $this->authMode,
                ]);
            }
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

    /**
     * Handle inline edit request from the table (no URL navigation).
     */
    public function handleEditRequest(array $data): void
    {
        $this->setMode('edit', (int) ($data['id'] ?? 0));
    }

    public function render()
    {
        return view('livewire.crudlfix.page');
    }
}
