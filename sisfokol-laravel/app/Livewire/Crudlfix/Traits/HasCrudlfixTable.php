<?php

namespace App\Livewire\Crudlfix\Traits;

use App\Support\Crudlfix\CrudlfixConfig;
use Illuminate\Database\Eloquent\Builder;

/**
 * Table query logic for Livewire Crudlfix components.
 *
 * Provides search, sort, filter, pagination, and bulk selection
 * by reading from CrudlfixConfig.
 */
trait HasCrudlfixTable
{
    public string $searchQuery = '';
    public string $sortField = '';
    public string $sortDirection = 'asc';
    public array $activeFilters = [];
    public int $perPage = 15;
    public int $currentPage = 1;
    public array $selected = [];
    public bool $selectAll = false;

    public function initTable(CrudlfixConfig $config): void
    {
        $this->sortField = $config->defaultSort ?? 'created_at';
        $this->sortDirection = $config->defaultDir ?? 'desc';
        $this->perPage = $config->perPage ?? 15;
    }

    public function updatedSearchQuery(): void
    {
        $this->currentPage = 1;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->currentPage = 1;
    }

    public function applyFilter(string $key, $value): void
    {
        $this->activeFilters[$key] = $value;
        $this->currentPage = 1;
    }

    public function clearFilter(string $key): void
    {
        unset($this->activeFilters[$key]);
        $this->currentPage = 1;
    }

    public function clearAllFilters(): void
    {
        $this->activeFilters = [];
        $this->currentPage = 1;
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = $page;
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = [];
            $this->selectAll = false;
        } else {
            $this->selected = $this->getRowsProperty()->pluck('id')->toArray();
            $this->selectAll = true;
        }
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_filter($this->selected, fn($s) => $s !== $id));
            $this->selectAll = false;
        } else {
            $this->selected[] = $id;
        }
    }

    /**
     * Build query with search, filters, sort, and scopes.
     */
    protected function buildTableQuery(CrudlfixConfig $config): Builder
    {
        $query = $config->model::query();

        // Eager load relations
        if ($config->with) {
            $query->with($config->with);
        }

        // Apply search
        if ($this->searchQuery && $config->search) {
            $query->where(function ($q) use ($config) {
                foreach ($config->search as $field) {
                    if (str_contains($field, '.')) {
                        $parts = explode('.', $field);
                        $relation = $parts[0];
                        $relationField = $parts[1];
                        $q->orWhereHas($relation, function ($rq) use ($relationField) {
                            $rq->where($relationField, 'like', "%{$this->searchQuery}%");
                        });
                    } else {
                        $q->orWhere($field, 'like', "%{$this->searchQuery}%");
                    }
                }
            });
        }

        // Apply filters
        if ($config->filters) {
            foreach ($config->filters as $field => $filterConfig) {
                if (isset($this->activeFilters[$field]) && $this->activeFilters[$field] !== '') {
                    $operator = $filterConfig['operator'] ?? '=';
                    $column = $filterConfig['column'] ?? $field;
                    $query->where($column, $operator, $this->activeFilters[$field]);
                }
            }
        }

        // Apply custom scopes
        if ($config->scope) {
            foreach ($config->scope as $scopeMethod) {
                $query->$scopeMethod();
            }
        }

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    /**
     * Get paginated rows.
     */
    public function getRowsProperty()
    {
        $config = $this->getConfigProperty();
        $query = $this->buildTableQuery($config);

        return $query->paginate($this->perPage, ['*'], 'page', $this->currentPage);
    }

    /**
     * Get total count.
     */
    public function getTotalProperty(): int
    {
        return $this->getRowsProperty()->total();
    }

    /**
     * Get CrudlfixConfig. Must be implemented by using class.
     */
    abstract protected function getConfigProperty(): CrudlfixConfig;
}
