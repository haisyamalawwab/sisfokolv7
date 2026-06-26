<?php

namespace App\Livewire\Crudlfix\Traits;

use App\Support\Crudlfix\CrudlfixConfig;
use Illuminate\Support\Facades\Response;

/**
 * CRUD actions for Livewire Crudlfix components.
 *
 * Provides delete, bulk delete, and export functionality.
 */
trait HasCrudlfixActions
{
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public string $deleteType = 'single'; // single|bulk

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->deleteType = 'single';
        $this->showDeleteModal = true;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->deleteType = 'bulk';
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        $config = $this->getConfigProperty();
        $model = $config->model;

        if ($this->deleteType === 'single' && $this->deleteId) {
            $record = $model::findOrFail($this->deleteId);
            $record->delete();
        } elseif ($this->deleteType === 'bulk' && !empty($this->selected)) {
            $model::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
        }

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    /**
     * Export data as CSV.
     */
    public function export(): mixed
    {
        $config = $this->getConfigProperty();
        $query = $this->buildTableQuery($config);
        $rows = $query->get();

        $columns = $config->exportColumns ?? [];
        if (empty($columns)) {
            $columns = $rows->first() ? array_keys($rows->first()->toArray()) : [];
        }

        $filename = ($config->route ?? 'export') . '_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($rows as $row) {
                $data = [];
                foreach ($columns as $column) {
                    $data[] = data_get($row, $column);
                }
                fputcsv($handle, $data);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Get CrudlfixConfig. Must be implemented by using class.
     */
    abstract protected function getConfigProperty(): CrudlfixConfig;

    /**
     * Build table query. Must be implemented by HasCrudlfixTable.
     */
    abstract protected function buildTableQuery(CrudlfixConfig $config);
}
