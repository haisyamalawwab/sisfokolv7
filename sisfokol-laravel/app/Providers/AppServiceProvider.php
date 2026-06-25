<?php

namespace App\Providers;

use App\Support\TenantContext;
use App\Modules\Auth\Models\AuditLog;
use App\Modules\Auth\Policies\AuditLogPolicy;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Academic\Policies\SiswaPolicy;
use App\Modules\Academic\Observers\SiswaObserver;
use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Policies\GuruPolicy;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Policies\KelasPolicy;
use App\Modules\Academic\Models\Jadwal;
use App\Modules\Academic\Policies\JadwalPolicy;
use App\Models\Attendance;
use App\Models\Permit;
use App\Models\StudentSemesterScore;
use App\Modules\Presence\Observers\AttendanceObserver;
use App\Modules\Presence\Policies\PresensiPolicy;
use App\Modules\Presence\Policies\IzinPolicy;
use App\Modules\Evaluation\Policies\GradePolicy;
use App\Modules\Evaluation\Policies\RaporPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });
        $this->app->singleton(\App\Modules\Auth\Services\AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default locale Indonesia
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id');

        // Register observers
        \App\Models\User::observe(\App\Modules\Auth\Observers\UserObserver::class);
        Siswa::observe(SiswaObserver::class);
        Attendance::observe(AttendanceObserver::class);

        // Register Policies
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Siswa::class, SiswaPolicy::class);
        Gate::policy(Guru::class, GuruPolicy::class);
        Gate::policy(Kelas::class, KelasPolicy::class);
        Gate::policy(Jadwal::class, JadwalPolicy::class);
        Gate::policy(Attendance::class, PresensiPolicy::class);
        Gate::policy(Permit::class, IzinPolicy::class);
        Gate::policy(StudentSemesterScore::class, GradePolicy::class);
        // RaporPolicy — applied via manual gate checks in RaporController
        Gate::define('rapor.viewAny', [RaporPolicy::class, 'viewAny']);
        Gate::define('rapor.view', [RaporPolicy::class, 'view']);
        Gate::define('rapor.download', [RaporPolicy::class, 'download']);

        Blueprint::macro('tenantAndAuditColumns', function (bool $withSoftDelete = true) {
            tenant_and_audit_columns($this, $withSoftDelete);
        });

        Blueprint::macro('auditColumns', function (bool $withSoftDelete = true) {
            audit_columns($this, $withSoftDelete);
        });

        // Register custom Blade directives for Field ACL
        \App\Support\BladeDirectives::register();
    }
}
