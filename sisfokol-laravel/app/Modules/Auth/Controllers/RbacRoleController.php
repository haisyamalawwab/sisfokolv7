<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\RbacBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\{Permission, Role};

class RbacRoleController extends Controller
{
    public function __construct(private RbacBuilderService $builder) {}

    public function index()
    {
        Gate::authorize('rbac.manage');
        
        // [2026-06-28 | Antigravity] Tenant admin cannot manage global roles/permissions, redirect to menus override
        if (! auth()->user()->isSuperAdmin()) {
            return redirect()->route('rbac.menus');
        }
        
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('rbac.index', compact('roles', 'permissions'));
    }

    public function syncPermissions(Request $request, int $roleId)
    {
        Gate::authorize('rbac.manage');
        
        // [2026-06-28 | Antigravity] Tenant admin cannot sync global permissions
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Aksi tidak diizinkan.');
        }
        
        $request->validate(['permissions' => 'array', 'permissions.*' => 'integer|exists:permissions,id']);
        $this->builder->syncRolePermissions($roleId, $request->permissions ?? []);
        return response()->json(['status' => 'ok']);
    }
}
