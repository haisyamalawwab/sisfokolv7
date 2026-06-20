<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\ChangePasswordRequest;
use App\Modules\Auth\Services\AuditLogger;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function show()
    {
        return view('auth.change-password');
    }

    public function store(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $oldHash = $user->password;
        $user->update([
            'password' => $request->password, // automatically hashed by Laravel's cast in User model
            'must_reset_password' => false,
        ]);
        $this->audit->log('password.changed', $user, [], $request, ['old_password_hash' => $oldHash]);
        return redirect()->route('dashboard')->with('status', 'Password berhasil diubah.');
    }
}
