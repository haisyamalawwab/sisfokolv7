<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user')->latest();
        if ($request->filled('event')) {
            $query->where('event', 'like', "%{$request->event}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if (! $request->user()->isSuperAdmin()) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        $logs = $query->paginate(50);
        return view('audit.index', compact('logs'));
    }
}
