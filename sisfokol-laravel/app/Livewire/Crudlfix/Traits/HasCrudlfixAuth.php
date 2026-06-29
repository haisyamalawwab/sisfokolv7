<?php

namespace App\Livewire\Crudlfix\Traits;

use App\Support\Crudlfix\CrudlfixConfig;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization for Livewire Crudlfix components.
 *
 * Mirrors backend Crudlfix::authorizeCrudlfix() so that AJAX operations
 * (save, delete, table query) enforce the same policy/permission checks
 * as the controller — closing the gap where Livewire requests bypassed
 * the controller's in-method authorization.
 *
 * Authorization modes (read from CrudlfixConfig):
 *   - 'policy'     → Gate::authorize('ability', $model) [policy-based]
 *   - 'permission' → Gate::authorize('permission.key')   [direct Spatie permission]
 *   - null/absent  → No in-component auth (relies on route middleware)
 *
 * ADR-006: sets team context for Spatie Permission teams mode.
 */
trait HasCrudlfixAuth
{
    /**
     * Authorize a CRUD action. Supports policy and permission modes.
     */
    protected function authorizeCrudlfixAction(string $action, ?Model $model = null): void
    {
        $config = $this->getConfigProperty();

        if ($config->authType === 'policy') {
            // Policy mode: Gate::authorize('ability', $model|Model::class)
            $model
                ? Gate::authorize($action, $model)
                : Gate::authorize($action, $config->model);
        } elseif ($config->authType === 'permission' && $config->authorize) {
            // ADR-006: set team context for Spatie Permission teams mode
            $tenantCtx = app(TenantContext::class);
            if ($tenantCtx->isInitialized()) {
                setPermissionsTeamId($tenantCtx->id);
            }

            $permission = "{$config->authorize}.{$action}";
            $user = auth()->user();

            if (!$user || !$user->can($permission)) {
                abort(403, 'Tidak memiliki akses.');
            }
        }
        // null/absent → no in-component auth (route middleware handles it)
    }

    /**
     * Check if a CRUD action is authorized. Returns boolean.
     */
    public function checkCrudlfixAction(string $action, ?Model $model = null): bool
    {
        $config = $this->getConfigProperty();

        if ($config->authType === 'policy') {
            return $model
                ? Gate::allows($action, $model)
                : Gate::allows($action, $config->model);
        } elseif ($config->authType === 'permission' && $config->authorize) {
            // ADR-006: set team context for Spatie Permission teams mode
            $tenantCtx = app(TenantContext::class);
            if ($tenantCtx->isInitialized()) {
                setPermissionsTeamId($tenantCtx->id);
            }

            $permission = "{$config->authorize}.{$action}";
            $user = auth()->user();

            return $user && $user->can($permission) ? true : false;
        }

        return true;
    }

    abstract protected function getConfigProperty(): CrudlfixConfig;
}
