<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\{Field, FieldRoleOverride};
use App\Modules\Auth\Services\RbacBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RbacFieldController extends Controller
{
    public function __construct(private RbacBuilderService $builder) {}

    public function index()
    {
        Gate::authorize('rbac.manage');
        $fields = Field::orderBy('model')->orderBy('label')->get();
        $roles = Role::orderBy('name')->get();
        
        // [2026-06-28 | Antigravity] Scope overrides by current tenant if not super admin to prevent data leak
        $query = FieldRoleOverride::query();
        if (! auth()->user()->isSuperAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        $overrides = $query->get()->keyBy(fn($o) => "{$o->field_id}.{$o->role_id}.{$o->tenant_id}");
        
        return view('rbac.fields', compact('fields', 'roles', 'overrides'));
    }

    public function update(Request $request)
    {
        Gate::authorize('rbac.manage');
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'role_id' => 'required|exists:roles,id',
            'visibility' => 'required|in:visible,hidden,readonly',
        ]);
        $this->builder->setFieldOverride($request->field_id, $request->role_id, auth()->user()->tenant_id, $request->visibility);
        return back()->with('success', 'Override field disimpan.');
    }
}
