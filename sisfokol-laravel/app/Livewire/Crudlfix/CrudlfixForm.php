<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire form component for Crudlfix.
 *
 * Accepts raw arrays and builds CrudlfixConfig internally.
 */
class CrudlfixForm extends Component
{
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixForm;

    // Raw config (Livewire-safe)
    public string $modelClass = '';
    public string $routePrefix = '';
    public array $formFields = [];
    public array $validationRules = [];
    public array $extraViewData = [];

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
    ): void {
        $this->modelClass = $model;
        $this->routePrefix = $route;
        $this->formFields = $formFields;
        $this->validationRules = $rules;
        $this->extraViewData = $viewData;

        $this->initForm($this->getConfigProperty(), $editId);
    }

    public function getConfigProperty(): CrudlfixConfig
    {
        if ($this->_config === null) {
            $this->_config = CrudlfixConfig::make([
                'model' => $this->modelClass,
                'route' => $this->routePrefix,
                'rules' => $this->validationRules,
            ]);
        }
        return $this->_config;
    }

    public function save(): void
    {
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
