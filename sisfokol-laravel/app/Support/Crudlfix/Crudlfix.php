<?php

namespace App\Support\Crudlfix;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUDLFIX — Dynamic reusable CRUD + List + Filter + Import + eXport trait.
 *
 * Drop this trait into any controller and define a `crudlfix()` method returning
 * a CrudlfixConfig array. All standard CRUD operations become automatic.
 *
 * Authorization modes:
 *   - 'policy'     → Gate::authorize('ability', $model) [policy-based]
 *   - 'permission' → Gate::authorize('permission.key')   [direct Spatie permission]
 *   - null/absent  → No in-controller authorization (relies on route middleware)
 */
trait Crudlfix
{
    protected ?CrudlfixConfig $_crudlfix = null;

    abstract protected function crudlfix(): array;

    protected function config(): CrudlfixConfig
    {
        if ($this->_crudlfix === null) {
            $this->_crudlfix = CrudlfixConfig::make($this->crudlfix());
        }
        return $this->_crudlfix;
    }

    /**
     * Resolve model from route parameter (bypasses global scopes).
     */
    protected function resolveModel(string $param): Model
    {
        $cfg = $this->config();
        $id = request()->route($param);
        return $cfg->model::withoutGlobalScopes()->findOrFail($id);
    }

    /**
     * Authorize an action. Supports policy mode and permission mode.
     */
    protected function authorizeCrudlfix(string $action, ?Model $model = null): void
    {
        $cfg = $this->config();

        if ($cfg->authType === 'policy') {
            // Policy mode: Gate::authorize('ability', $model) or Gate::authorize('ability', Model::class)
            if ($model) {
                Gate::authorize($action, $model);
            } else {
                // For viewAny/create, pass the model class
                Gate::authorize($action, $cfg->model);
            }
        } elseif ($cfg->authType === 'permission' && $cfg->authorize) {
            // Permission mode: Gate::authorize('prefix.action')
            Gate::authorize("{$cfg->authorize}.{$action}");
        }
        // null/absent → no in-controller auth (route middleware handles it)
    }

    // ─── LIST (index) ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        $cfg = $this->config();
        $this->authorizeCrudlfix('viewAny');

        $query = $cfg->model::query();

        if ($cfg->with) {
            $query->with($cfg->with);
        }

        $search = $request->input('search');
        if ($search && $cfg->search) {
            $query->where(function ($q) use ($search, $cfg) {
                foreach ($cfg->search as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        if ($cfg->filters) {
            foreach ($cfg->filters as $field => $filterConfig) {
                if ($request->filled($field)) {
                    $operator = $filterConfig['operator'] ?? '=';
                    $query->where($filterConfig['column'] ?? $field, $operator, $request->input($field));
                }
            }
        }

        if ($cfg->scope) {
            foreach ($cfg->scope as $scopeMethod) {
                $query->$scopeMethod();
            }
        }

        $sortField = $request->input('sort', $cfg->defaultSort ?? 'created_at');
        $sortDir = $request->input('dir', $cfg->defaultDir ?? 'desc');
        $query->orderBy($sortField, $sortDir);

        $paginator = $query->paginate($cfg->perPage ?? 15)->withQueryString();

        // Use custom varName if provided, otherwise pluralVar
        $varName = $cfg->varName ?? $cfg->pluralVar();
        $data = array_merge($cfg->viewData ?? [], [
            $varName => $paginator,
            'search' => $search,
            'config' => $cfg,
        ]);

        return view("{$cfg->view}.index", $data);
    }

    // ─── CREATE (form) ──────────────────────────────────────────────

    public function create(): View
    {
        $cfg = $this->config();
        $this->authorizeCrudlfix('create');

        $data = array_merge($cfg->viewData ?? [], [
            'config' => $cfg,
        ]);

        return view("{$cfg->view}.create", $data);
    }

    // ─── STORE ──────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $cfg = $this->config();
        $this->authorizeCrudlfix('create');

        $validated = $this->validateCrudlfix($request, 'store');

        if (method_exists($this, 'beforeStore')) {
            $validated = $this->beforeStore($validated, $request);
        }

        $model = $cfg->model::create($validated);

        if (method_exists($this, 'afterStore')) {
            $this->afterStore($model, $request);
        }

        return redirect()
            ->route("{$cfg->route}.index")
            ->with('success', $cfg->message('created', $model));
    }

    // ─── SHOW ───────────────────────────────────────────────────────

    public function show(Request $request): View
    {
        $cfg = $this->config();
        $model = $this->resolveModel($cfg->singularVar());
        $this->authorizeCrudlfix('view', $model);

        if ($cfg->showWith) {
            $model->load($cfg->showWith);
        }

        $data = array_merge($cfg->viewData ?? [], [
            $cfg->singularVar() => $model,
            'config' => $cfg,
        ]);

        return view("{$cfg->view}.show", $data);
    }

    // ─── EDIT (form) ────────────────────────────────────────────────

    public function edit(Request $request): View
    {
        $cfg = $this->config();
        $model = $this->resolveModel($cfg->singularVar());
        $this->authorizeCrudlfix('update', $model);

        $data = array_merge($cfg->viewData ?? [], [
            $cfg->singularVar() => $model,
            'config' => $cfg,
        ]);

        return view("{$cfg->view}.edit", $data);
    }

    // ─── UPDATE ─────────────────────────────────────────────────────

    public function update(Request $request): RedirectResponse
    {
        $cfg = $this->config();
        $model = $this->resolveModel($cfg->singularVar());
        $this->authorizeCrudlfix('update', $model);

        $validated = $this->validateCrudlfix($request, 'update', $model);

        if (method_exists($this, 'beforeUpdate')) {
            $validated = $this->beforeUpdate($validated, $model, $request);
        }

        $model->update($validated);

        if (method_exists($this, 'afterUpdate')) {
            $this->afterUpdate($model, $request);
        }

        return redirect()
            ->route("{$cfg->route}.index")
            ->with('success', $cfg->message('updated', $model));
    }

    // ─── DELETE ─────────────────────────────────────────────────────

    public function destroy(Request $request): RedirectResponse
    {
        $cfg = $this->config();
        $model = $this->resolveModel($cfg->singularVar());
        $this->authorizeCrudlfix('delete', $model);

        if (method_exists($this, 'beforeDestroy')) {
            $this->beforeDestroy($model);
        }

        $model->delete();

        if (method_exists($this, 'afterDestroy')) {
            $this->afterDestroy($model);
        }

        return redirect()
            ->route("{$cfg->route}.index")
            ->with('success', $cfg->message('deleted', $model));
    }

    // ─── EXPORT ─────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $cfg = $this->config();
        $this->authorizeCrudlfix('viewAny');

        if (method_exists($this, 'handleExport')) {
            return $this->handleExport($request);
        }

        $query = $cfg->model::query();
        if ($cfg->with) {
            $query->with($cfg->with);
        }

        $records = $query->get();
        $filename = class_basename($cfg->model) . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($records, $cfg) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $cfg->exportColumns ?? array_keys($records->first()?->toArray() ?? []));
            foreach ($records as $record) {
                fputcsv($handle, $record->toArray());
            }
            fclose($handle);
        }, 200, $headers);
    }

    // ─── HELPERS ────────────────────────────────────────────────────

    protected function validateCrudlfix(Request $request, string $action, ?Model $model = null): array
    {
        $cfg = $this->config();

        if ($cfg->requestClass) {
            $formRequest = new $cfg->requestClass();
            return $formRequest->validateResolved($request);
        }

        if ($cfg->rules) {
            $rules = is_callable($cfg->rules)
                ? call_user_func($cfg->rules, $request, $model)
                : $cfg->rules[$action] ?? $cfg->rules;

            return $request->validate($rules);
        }

        return $request->all();
    }
}
