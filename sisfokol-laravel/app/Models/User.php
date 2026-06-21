<?php

namespace App\Models;

use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\PermissionRegistrar;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Impersonate, SoftDeletes;

    use HasRoles, HasPermissions {
        HasRoles::assignRole as traitAssignRole;
        HasRoles::removeRole as traitRemoveRole;
        HasRoles::syncRoles as traitSyncRoles;
        HasRoles::hasRole as traitHasRole;
        HasRoles::hasAnyRole as traitHasAnyRole;
        HasRoles::hasAllRoles as traitHasAllRoles;
        HasPermissions::hasPermissionTo as traitHasPermissionTo;
        HasPermissions::givePermissionTo as traitGivePermissionTo;
        HasPermissions::revokePermissionTo as traitRevokePermissionTo;
        HasPermissions::syncPermissions as traitSyncPermissions;
    }

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'username',
        'nama',
        'email',
        'tipe',
        'password',
        'foto',
        'aktif',
        'must_reset_password',
        'last_login_at',
        'userable_type',
        'userable_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'             => 'hashed',
            'aktif'                => 'boolean',
            'must_reset_password'  => 'boolean',
            'last_login_at'        => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->userable?->name ?? $this->nama ?? $this->username;
    }

    public function getDisplayCodeAttribute(): ?string
    {
        return $this->userable?->code ?? null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->tenant_id === null;
    }

    public function canImpersonate(): bool
    {
        return config('impersonate.enabled', false)
            && ($this->hasRole(['super_admin', 'admin_sekolah', 'admin']) || in_array($this->tipe, ['super_admin', 'admin_sekolah']));
    }

    public function canBeImpersonated($target): bool
    {
        if ($this->id === $target->id) return false;
        if (! $target->aktif) return false;

        // SuperAdmin -> anyone
        if ($this->isSuperAdmin() || $this->tipe === 'super_admin' || $this->hasRole('super_admin')) return true;

        // Admin Sekolah / Admin -> anyone in the same tenant
        if ($this->hasRole(['admin_sekolah', 'admin']) || $this->tipe === 'admin_sekolah') {
            return $this->tenant_id === $target->tenant_id;
        }

        return false;
    }

    protected function runInTeamContext(\Closure $callback)
    {
        $registrar = app(PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();
        
        $teamId = $this->tenant_id ?? 0;
        $registrar->setPermissionsTeamId($teamId);
        
        try {
            return $callback();
        } finally {
            $registrar->setPermissionsTeamId($originalTeamId);
        }
    }

    public function assignRole(...$roles)
    {
        return $this->runInTeamContext(fn() => $this->traitAssignRole(...$roles));
    }

    public function removeRole(...$role)
    {
        return $this->runInTeamContext(fn() => $this->traitRemoveRole(...$role));
    }

    public function syncRoles(...$roles)
    {
        return $this->runInTeamContext(fn() => $this->traitSyncRoles(...$roles));
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        return $this->runInTeamContext(fn() => $this->traitHasRole($roles, $guard));
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->runInTeamContext(fn() => $this->traitHasAnyRole(...$roles));
    }

    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        return $this->runInTeamContext(fn() => $this->traitHasAllRoles($roles, $guard));
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->runInTeamContext(fn() => $this->traitHasPermissionTo($permission, $guardName));
    }

    public function givePermissionTo(...$permissions)
    {
        return $this->runInTeamContext(fn() => $this->traitGivePermissionTo(...$permissions));
    }

    public function revokePermissionTo($permission)
    {
        return $this->runInTeamContext(fn() => $this->traitRevokePermissionTo($permission));
    }

    public function syncPermissions(...$permissions)
    {
        return $this->runInTeamContext(fn() => $this->traitSyncPermissions(...$permissions));
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
