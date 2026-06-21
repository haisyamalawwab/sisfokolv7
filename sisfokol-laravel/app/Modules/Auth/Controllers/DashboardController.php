<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin() || $user->hasRole('super_admin')) {
            return view('dashboard.index', [
                'user' => $user,
                'isSuperAdmin' => true,
            ]);
        }

        // Redirect school roles to their specific dashboards
        $route = match (true) {
            $user->hasRole('admin') => 'admin.dashboard',
            $user->hasRole('teacher') => 'teacher.dashboard',
            $user->hasRole('student') => 'student.dashboard',
            $user->hasRole('homeroom-teacher') => 'homeroom.dashboard',
            $user->hasRole('finance') => 'finance.dashboard',
            $user->hasRole('counselor') => 'counselor.dashboard',
            $user->hasRole('picket-officer') => 'picket.dashboard',
            $user->hasRole('inventory') => 'inventory.dashboard',
            $user->hasRole('principal') => 'principal.dashboard',
            default => null,
        };

        if ($route) {
            return redirect()->route($route);
        }

        return view('dashboard.index', [
            'user' => $user,
            'isSuperAdmin' => false,
        ]);
    }
}
