<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Auth\Models\Menu;
use App\Modules\Auth\Models\MenuRoleOverride;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuRenderer
{
    /**
     * ADR-010 §1: Resolve menus for a user.
     * 1. Get active menus (system + tenant)
     * 2. Filter by permission_required (RBAC user)
     * 3. Apply menu_role_overrides (priority highest)
     * 4. SuperAdmin → all
     */
    public static function forUser(User $user): Collection
    {
        return Cache::remember("menu.{$user->id}." . ($user->tenant_id ?? 'global'), 60, function () use ($user) {
            $query = Menu::where('aktif', true)->orderBy('urutan');
            if (! $user->isSuperAdmin()) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
                });
            }
            $menus = $query->get();

            // Filter by permission
            if (! $user->isSuperAdmin()) {
                $menus = $menus->filter(function ($m) use ($user) {
                    if (! $m->permission_required) return true;
                    
                    // [2026-06-28 | Antigravity] Exclude global platform-only permissions for tenant users
                    $globalPermissions = ['tenant.view', 'plugin.activate', 'rbac.manage', 'audit.view'];
                    if (in_array($m->permission_required, $globalPermissions)) {
                        return false;
                    }
                    
                    return $user->can($m->permission_required);
                });
            }

            // Apply overrides in team context
            $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
            $originalTeamId = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId($user->tenant_id ?? 0);

            $roleIds = $user->roles->pluck('id');

            $registrar->setPermissionsTeamId($originalTeamId);

            $overrides = MenuRoleOverride::whereIn('role_id', $roleIds)
                ->where(function ($q) use ($user) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
                })
                ->get()
                ->keyBy('menu_id');

            return $menus->filter(function ($m) use ($overrides, $user) {
                if ($user->isSuperAdmin()) return true;
                $ov = $overrides->get($m->id);
                if ($ov) return $ov->visible !== 'hide';
                return true;
            })->values();
        });
    }

    public static function clearCache(?int $userId = null): void
    {
        if ($userId) {
            $user = User::find($userId);
            Cache::forget("menu.{$userId}." . ($user?->tenant_id ?? 'global'));
        } else {
            Cache::flush();
        }
    }
}
