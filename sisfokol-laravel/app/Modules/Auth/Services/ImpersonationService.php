<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationService
{
    public function __construct(private AuditLogger $audit) {}

    public function canStart(User $impersonator, User $target): bool
    {
        if (! config('impersonate.enabled', false)) return false;
        if (! $impersonator->canImpersonate()) return false;
        if (! $impersonator->canBeImpersonated($target)) return false;
        return true;
    }

    public function start(User $impersonator, User $target, Request $request): void
    {
        session()->put('impersonated_by', $impersonator->id);
        Auth::login($target);
        $this->audit->log(
            'impersonate.start', $impersonator,
            ['target_user_id' => $target->id, 'target_username' => $target->username],
            $request,
        );
    }

    public function stop(Request $request): ?User
    {
        $impersonatorId = session()->pull('impersonated_by');
        if (! $impersonatorId) return null;

        $impersonator = User::find($impersonatorId);
        if ($impersonator) {
            $currentUser = $request->user();
            Auth::login($impersonator);
            $this->audit->log(
                'impersonate.stop', $impersonator,
                ['was_impersonating_user_id' => $currentUser?->id],
                $request,
            );
        }
        return $impersonator;
    }

    public function isImpersonating(): bool
    {
        return session()->has('impersonated_by');
    }
}
