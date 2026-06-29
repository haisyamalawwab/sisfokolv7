<?php

namespace App\Support\Crudlfix;

use App\Support\TenantContext;
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
 *
 * Tenant Isolation (ADR-003):
 *   - resolveModel() now enforces tenant check instead of bypassing global scope
 *   - Throws 404 if model belongs to different tenant (no data leakage)
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
     * Get CrudlfixConfig instance for Livewire components.
     */
    public function getCrudlfixConfig(): CrudlfixConfig
    {
        return $this->config();
    }

    /**
     * Resolve model from route parameter WITH tenant isolation.
     *
     * ADR-003: Models using BelongsToTenant trait have global scope.
     * Instead of bypassing, we find within tenant scope.
     * Returns 404 if model belongs to different tenant (no data leakage).
     */
    protected function resolveModel(string $param): Model
    {
        $cfg = $this->config();
        $id = request()->route($param);

        // Check if model uses BelongsToTenant trait
        $usesTenantTrait = in_array(
            \App\Models\Traits\BelongsToTenant::class,
            class_uses_recursive($cfg->model)
        );

        if ($usesTenantTrait) {
            // Find within tenant scope (global scope applies)
            $model = $cfg->model::find($id);

            if (!$model) {
                // Model not found OR belongs to different tenant
                // Return 404 to avoid data leakage (don't reveal existence)
                abort(404, 'Data tidak ditemukan.');
            }

            return $model;
        }

        // Model doesn't use BelongsToTenant (e.g., Tenant itself, global models)
        // Use standard findOrFail
        return $cfg->model::findOrFail($id);
    }

    /**
     * Authorize an action. Supports policy mode and permission mode.
     *
     * ADR-006: Sets team context for Spatie Permission teams mode.
     */
    protected function authorizeCrudlfix(string $action, ?Model $model = null): void
    {
        $cfg = $this->config();

        if ($cfg->authType === 'policy') {
            // Policy mode: Gate::authorize('ability', $model) or Gate::authorize('ability', Model::class)
            if ($model) {
                Gate::authorize($action, $model);
            } else {
                Gate::authorize($action, $cfg->model);
            }
        } elseif ($cfg->authType === 'permission' && $cfg->authorize) {
            // Permission mode: Spatie permission check
            $permission = "{$cfg->authorize}.{$action}";
            $user = auth()->user();

            if (!$user) {
                abort(403, 'Tidak memiliki akses.');
            }

            // ADR-006: Set team context for Spatie Permission teams mode
            $tenantCtx = app(TenantContext::class);
            if ($tenantCtx->isInitialized()) {
                // Set team_id for permission check
                setPermissionsTeamId($tenantCtx->id);
            }

            if (!$user->can($permission)) {
                abort(403, 'Tidak memiliki akses.');
            }
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

    public function create()
    {
        $cfg = $this->config();
        $this->authorizeCrudlfix('create');

        $data = array_merge($cfg->viewData ?? [], [
            'config' => $cfg,
        ]);

        $viewName = "{$cfg->view}.create";
        if (!view()->exists($viewName)) {
            // [2026-06-29 | AG] Fallback to index if traditional create view is missing
            return redirect()->route("{$cfg->route}.index", ['action' => 'create']);
        }

        // return view("{$cfg->view}.create", $data); // [2026-06-29 | AG] commented for fallback support
        return view($viewName, $data);
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

    public function show(Request $request)
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

        $viewName = "{$cfg->view}.show";
        if (!view()->exists($viewName)) {
            // [2026-06-29 | AG] Fallback to index with parameters if traditional show view is missing
            return redirect()->route("{$cfg->route}.index", [
                'action' => 'show',
                'editId' => $model->getKey()
            ]);
        }

        // return view("{$cfg->view}.show", $data); // [2026-06-29 | AG] commented for fallback support
        return view($viewName, $data);
    }

    // ─── EDIT (form) ────────────────────────────────────────────────

    public function edit(Request $request)
    {
        $cfg = $this->config();
        $model = $this->resolveModel($cfg->singularVar());
        $this->authorizeCrudlfix('update', $model);

        $data = array_merge($cfg->viewData ?? [], [
            $cfg->singularVar() => $model,
            'config' => $cfg,
        ]);

        $viewName = "{$cfg->view}.edit";
        if (!view()->exists($viewName)) {
            // [2026-06-29 | AG] Fallback to index with parameters if traditional edit view is missing
            return redirect()->route("{$cfg->route}.index", [
                'action' => 'edit',
                'editId' => $model->getKey()
            ]);
        }

        // return view("{$cfg->view}.edit", $data); // [2026-06-29 | AG] commented for fallback support
        return view($viewName, $data);
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
            // Resolve the FormRequest through the container so its validator,
            // redirector and container are wired (FormRequest::validate() needs them).
            /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
            $formRequest = app($cfg->requestClass);
            $formRequest->setContainer(app())->setRedirector(app('redirect'));

            // Merge the incoming request data + route parameters so the FormRequest
            // (which extends Request) can resolve its own input and route bindings.
            $formRequest->merge($request->input());
            $formRequest->setJson($request->json());
            $formRequest->setRouteResolver(fn () => $request->route());

            $formRequest->validateResolved();

            return $formRequest->validated();
        }

        if ($cfg->rules) {
            $rules = is_callable($cfg->rules)
                ? call_user_func($cfg->rules, $request, $model)
                : $cfg->rules[$action] ?? $cfg->rules;

            // Resolve {{id}} placeholder against the model being updated,
            // so rules like unique:table,col,{{id}} exclude the current record.
            if ($model && $model->getKey()) {
                $id = $model->getKey();
                $rules = collect($rules)->map(function ($rule) use ($id) {
                    return is_string($rule) ? str_replace('{{id}}', $id, $rule) : $rule;
                })->all();
            }

            return $request->validate($rules);
        }

        return $request->all();
    }

    // ─── API ENDPOINT HANDLER ──────────────────────────────────────

    /**
     * Handle API requests for cascading, search select, and lazy load.
     * Register route: Route::get('api/resource', [Controller::class, 'api']);
     */
    public function api(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->query('type');

        return match ($type) {
            'cascade' => $this->handleCascade($request),
            'search'  => $this->handleSearchSelect($request),
            default   => response()->json(['error' => 'Unknown type'], 400),
        };
    }

    /**
     * Handle cascade select: return child options based on parent value.
     */
    protected function handleCascade(Request $request): \Illuminate\Http\JsonResponse
    {
        $cfg = $this->config();
        $field = $request->query('field');
        $value = $request->query('value');

        $cascade = $cfg->cascades[$field] ?? null;
        if (!$cascade) {
            return response()->json(['error' => 'Cascade not found'], 404);
        }

        $query = ($cascade['query'])($value);
        $results = $query->get()->map(fn ($item) => [
            'value' => $item->{$cascade['value']},
            'label' => $item->{$cascade['label']},
        ]);

        return response()->json($results);
    }

    /**
     * Handle search select: return filtered options based on query.
     */
    protected function handleSearchSelect(Request $request): \Illuminate\Http\JsonResponse
    {
        $cfg = $this->config();
        $field = $request->query('field');
        $search = $request->query('q', '');

        $selectConfig = $cfg->searchSelects[$field] ?? null;
        if (!$selectConfig) {
            return response()->json(['error' => 'Search select not found'], 404);
        }

        $query = ($selectConfig['query'])($search);
        $results = $query->limit(20)->get()->map(fn ($item) => [
            'value' => $item->{$selectConfig['value']},
            'label' => $item->{$selectConfig['label']},
        ]);

        return response()->json($results);
    }
}
