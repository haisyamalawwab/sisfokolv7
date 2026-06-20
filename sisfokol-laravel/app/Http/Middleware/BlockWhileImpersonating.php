<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWhileImpersonating
{
    /** Routes blocked while impersonating (sensitive actions per ADR-005) */
    private array $blockedPatterns = [
        'users.store', 'users.update', 'users.destroy',
        'rbac.*', 'plugins.activate', 'plugins.deactivate',
        'password.change.store',
    ];

    public function __construct(private ImpersonationService $impersonation) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->impersonation->isImpersonating() && $request->isMethod('POST|PUT|PATCH|DELETE')) {
            foreach ($this->blockedPatterns as $pattern) {
                if ($request->routeIs($pattern)) {
                    abort(403, 'Aksi ini diblokir selama impersonation.');
                }
            }
        }
        return $next($request);
    }
}
