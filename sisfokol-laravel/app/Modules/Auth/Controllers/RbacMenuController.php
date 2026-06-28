<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\{Menu, MenuRoleOverride};
use App\Modules\Auth\Services\RbacBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RbacMenuController extends Controller
{
    public function __construct(private RbacBuilderService $builder) {}

    public function index()
    {
        Gate::authorize('rbac.manage');
        $menus = Menu::orderBy('urutan')->get();
        $roles = Role::orderBy('name')->get();
        
        // [2026-06-28 | Antigravity] Scope overrides by current tenant if not super admin to prevent data leak
        $query = MenuRoleOverride::query();
        if (! auth()->user()->isSuperAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        $overrides = $query->get()->keyBy(fn($o) => "{$o->menu_id}.{$o->role_id}.{$o->tenant_id}");
        
        return view('rbac.menus', compact('menus', 'roles', 'overrides'));
    }

    public function update(Request $request)
    {
        Gate::authorize('rbac.manage');
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'role_id' => 'required|exists:roles,id',
            'visible' => 'required|in:show,hide,readonly',
        ]);
        $this->builder->setMenuOverride($request->menu_id, $request->role_id, auth()->user()->tenant_id, $request->visible);
        return back()->with('success', 'Override menu disimpan.');
    }
}
