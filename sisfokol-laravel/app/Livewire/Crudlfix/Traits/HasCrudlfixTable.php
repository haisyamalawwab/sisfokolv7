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
    public $perPage = 25;
    public int $currentPage = 1;
    public array $selected = [];
    public bool $selectAll = false;

    public function initTable(CrudlfixConfig $config): void
    {
        $this->sortField = $config->defaultSort ?? 'created_at';
        $this->sortDirection = $config->defaultDir ?? 'desc';
        $this->perPage = $config->perPage ?? 25;
    }

    public function updatedSearchQuery(): void
    {
        $this->currentPage = 1;
    }

    public function updatedPerPage(): void
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

        // [2026-06-29 | AG] Apply advanced sorting (with relation support and sortKeys overrides)
        if ($this->sortField) {
            $field = $this->sortField;

            // Check if there is an override in sortKeys config
            if ($config->sortKeys && isset($config->sortKeys[$field])) {
                $field = $config->sortKeys[$field];
            }

            if (str_contains($field, '.')) {
                $parts = explode('.', $field);
                $relationName = $parts[0];
                $relationField = $parts[1];

                if (method_exists($query->getModel(), $relationName)) {
                    $relation = $query->getModel()->$relationName();
                    if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $relatedTable = $relation->getRelated()->getTable();
                        $foreignKey = $relation->getForeignKeyName();
                        $ownerKey = $relation->getOwnerKeyName();
                        $currentTable = $query->getModel()->getTable();

                        // Avoid joining the same table multiple times
                        $isJoined = collect($query->getQuery()->joins)->contains(function ($join) use ($relatedTable) {
                            return $join->table === $relatedTable;
                        });

                        if (!$isJoined) {
                            $query->select("{$currentTable}.*")
                                ->join($relatedTable, "{$currentTable}.{$foreignKey}", '=', "{$relatedTable}.{$ownerKey}");
                        }
                        $query->orderBy("{$relatedTable}.{$relationField}", $this->sortDirection);
                    } else {
                        $query->orderBy($field, $this->sortDirection);
                    }
                } else {
                    $query->orderBy($field, $this->sortDirection);
                }
            } else {
                $query->orderBy($field, $this->sortDirection);
            }
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

        $limit = $this->perPage;
        if ($limit === 'all' || (int)$limit >= 1000 || (int)$limit <= 0) {
            $limit = 1000;
        } else {
            $limit = (int) $limit;
        }

        return $query->paginate($limit, ['*'], 'page', $this->currentPage);
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
