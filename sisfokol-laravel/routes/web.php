<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Counselor\DashboardController as CounselorDashboardController;
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;
use App\Http\Controllers\Homeroom\DashboardController as HomeroomDashboardController;
use App\Http\Controllers\Inventory\DashboardController as InventoryDashboardController;
use App\Http\Controllers\Picket\DashboardController as PicketDashboardController;
use App\Http\Controllers\Principal\DashboardController as PrincipalDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('school-profile', SchoolProfileController::class)->only(['index', 'update']);
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('classrooms', ClassroomController::class);
        Route::resource('users', UserController::class);
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
        Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class);
        Route::resource('attendance-times', \App\Http\Controllers\Admin\AttendanceTimeController::class);
    });

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        Route::resource('agendas', \App\Http\Controllers\Teacher\TeacherAgendaController::class);
        Route::resource('competencies', \App\Http\Controllers\Teacher\CurriculumCompetencyController::class);

        Route::get('/attendance/scan', [\App\Http\Controllers\Teacher\AttendanceController::class, 'scan'])->name('attendance.scan');
        Route::post('/attendance/scan', [\App\Http\Controllers\Teacher\AttendanceController::class, 'storeScan'])->name('attendance.scan.store');
        Route::get('/attendance/manual', [\App\Http\Controllers\Teacher\AttendanceController::class, 'manual'])->name('attendance.manual');
    });

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Homeroom Teacher Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:homeroom-teacher'])
    ->prefix('homeroom')
    ->name('homeroom.')
    ->group(function () {
        Route::get('/dashboard', [HomeroomDashboardController::class, 'index'])->name('dashboard');
        Route::get('/projects', [\App\Http\Controllers\Homeroom\ProjectController::class, 'index'])->name('projects.index');
    });

/*
|--------------------------------------------------------------------------
| Finance Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:finance'])
    ->prefix('finance')
    ->name('finance.')
    ->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Counselor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:counselor'])
    ->prefix('counselor')
    ->name('counselor.')
    ->group(function () {
        Route::get('/dashboard', [CounselorDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Picket Officer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:picket-officer'])
    ->prefix('picket')
    ->name('picket.')
    ->group(function () {
        Route::get('/dashboard', [PicketDashboardController::class, 'index'])->name('dashboard');

        Route::resource('absences', \App\Http\Controllers\Picket\AbsenceController::class);
        Route::resource('permits', \App\Http\Controllers\Picket\PermitController::class);
        Route::post('/permits/{permit}/approve', [\App\Http\Controllers\Picket\PermitController::class, 'approve'])->name('permits.approve');
    });

/*
|--------------------------------------------------------------------------
| Inventory Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:inventory'])
    ->prefix('inventory')
    ->name('inventory.')
    ->group(function () {
        Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Principal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:principal'])
    ->prefix('principal')
    ->name('principal.')
    ->group(function () {
        Route::get('/dashboard', [PrincipalDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Fallback Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();
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
        default => 'login',
    };

    return redirect()->route($route);
})->name('dashboard');
