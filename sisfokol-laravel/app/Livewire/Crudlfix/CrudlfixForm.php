<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire form component for Crudlfix.
 *
 * When a `controller` FQCN is provided, rules and authorization are resolved
 * from the controller's getCrudlfixConfig() on each render. This keeps rules
 * as the single source of truth and supports closure/Rule-based rules (e.g.
 * Rule::unique) that cannot be serialized as Livewire public properties.
 *
 * Falls back to raw arrays (flat rules) for backward compatibility.
 */
class CrudlfixForm extends Component
{
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixForm;
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixAuth;

    // Raw config (Livewire-safe)
    public string $modelClass = '';
    public string $routePrefix = '';
    public array $formFields = [];
    public array $validationRules = [];
    public array $extraViewData = [];

    // Controller FQCN — when set, rules + auth resolved from controller
    public ?string $controllerClass = null;

    // Built internally
    protected ?CrudlfixConfig $_config = null;

    public function mount(
        string $model,
        string $route,
        array $formFields = [],
        array $rules = [],
        array $viewData = [],
        bool $isEdit = false,
        ?int $editId = null,
        ?string $controller = null,
    ): void {
        $this->modelClass = $model;
        $this->routePrefix = $route;
        $this->formFields = $formFields;
        $this->validationRules = $rules;
        $this->extraViewData = $viewData;
        $this->controllerClass = $controller;

        $this->initForm($this->getConfigProperty(), $editId);
    }

    public function getConfigProperty(): CrudlfixConfig
    {
        if ($this->_config === null) {
            if ($this->controllerClass) {
                // Resolve from controller — re-built each request so closure/Rule
                // objects in rules are fresh (not serialized across Livewire calls)
                $this->_config = app($this->controllerClass)->getCrudlfixConfig();
            } else {
                $this->_config = CrudlfixConfig::make([
                    'model' => $this->modelClass,
                    'route' => $this->routePrefix,
                    'rules' => $this->validationRules,
                ]);
            }
        }
        return $this->_config;
    }

    public function save(): void
    {
        $config = $this->getConfigProperty();

        // Authorize create/update — closes the gap where Livewire AJAX
        // bypassed the controller's in-method authorization
        if ($this->isEdit && $this->editId) {
            $record = $config->model::find($this->editId);
            if (!$record) {
                abort(404, 'Data tidak ditemukan.');
            }
            $this->authorizeCrudlfixAction('update', $record);
        } else {
            $this->authorizeCrudlfixAction('create');
        }

        $result = $this->saveForm();

        if ($result) {
            $this->dispatch('crudlfix-saved', [
                'route' => $this->routePrefix,
                'isEdit' => $this->isEdit,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.crudlfix.form');
    }
}
