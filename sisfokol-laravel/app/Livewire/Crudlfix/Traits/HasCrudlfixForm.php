<?php

namespace App\Livewire\Crudlfix\Traits;

use App\Support\Crudlfix\CrudlfixConfig;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form logic for Livewire Crudlfix components.
 *
 * Provides real-time validation, data binding, and save logic
 * by reading from CrudlfixConfig.
 */
trait HasCrudlfixForm
{
    public array $data = [];
    public array $errors = [];
    public bool $isEdit = false;
    public ?int $editId = null;

    public function initForm(CrudlfixConfig $config, ?int $editId = null): void
    {
        $this->isEdit = $editId !== null;
        $this->editId = $editId;

        if ($this->isEdit && $editId) {
            $model = $config->model;
            $record = $model::findOrFail($editId);
            $this->data = $record->toArray();
        } else {
            $this->data = [];
        }
    }

    /**
     * Resolve action-specific rules (store vs update) and apply {{id}} placeholder.
     *
     * CrudlfixConfig rules may be:
     *   - Flat array: ['nis' => 'required|...', ...]  (same for create+edit)
     *   - Nested:     ['store' => [...], 'update' => [...]]
     *
     * For edit mode, {{id}} is replaced with the record's ID so rules like
     * `unique:table,col,{{id}}` exclude the current record.
     */
    protected function resolveRules(): array
    {
        $config = $this->getConfigProperty();
        $rules = $config->rules ?? [];

        // Pick store/update ruleset when nested
        if (is_array($rules) && isset($rules['store']) && is_array($rules['store'])) {
            $action = $this->isEdit ? 'update' : 'store';
            $rules = $rules[$action] ?? $rules['store'];
        }

        // Resolve {{id}} placeholder against the record being updated
        if ($this->isEdit && $this->editId) {
            foreach ($rules as $field => $rule) {
                if (is_string($rule)) {
                    $rules[$field] = str_replace('{{id}}', $this->editId, $rule);
                }
            }
        }

        return is_array($rules) ? $rules : [];
    }

    /**
     * Real-time single field validation on change.
     */
    public function updated($field): void
    {
        $config = $this->getConfigProperty();
        $allRules = $this->resolveRules();

        if (empty($allRules)) {
            return;
        }

        // Strip the "data." prefix Livewire adds to nested property updates
        $field = str_starts_with($field, 'data.') ? substr($field, 5) : $field;

        $rules = [$field => $allRules[$field] ?? ''];
        $messages = $config->messages ?? [];

        if (empty($rules[$field])) {
            return;
        }

        try {
            Validator::make(
                [$field => data_get($this->data, $field)],
                $rules,
                $messages
            )->validate();
            unset($this->errors[$field]);
        } catch (ValidationException $e) {
            $this->errors[$field] = $e->errors()[$field][0] ?? '';
        }
    }

    /**
     * Save form data (create or update).
     */
    public function saveForm(): mixed
    {
        $config = $this->getConfigProperty();
        $rules = $this->resolveRules();

        // Validate all fields
        try {
            $validated = Validator::make(
                $this->data,
                $rules,
                $config->messages ?? []
            )->validate();
        } catch (ValidationException $e) {
            $this->errors = [];
            foreach ($e->errors() as $field => $messages) {
                $this->errors[$field] = $messages[0] ?? '';
            }
            return null;
        }

        $model = $config->model;

        if ($this->isEdit) {
            $record = $model::findOrFail($this->editId);
            $record->fill($validated);
            $record->save();
            return $record;
        } else {
            return $model::create($validated);
        }
    }

    /**
     * Reset form state.
     */
    public function resetForm(): void
    {
        $this->data = [];
        $this->errors = [];
        $this->isEdit = false;
        $this->editId = null;
    }

    /**
     * Get CrudlfixConfig. Must be implemented by using class.
     */
    abstract protected function getConfigProperty(): CrudlfixConfig;
}
