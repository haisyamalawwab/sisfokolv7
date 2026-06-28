<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Academic\Models\Jadwal;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Verifikasi gap view-only untuk JADWAL (gap nyata yang diperbaiki di Task 1
 * via permission academic.schedule.view untuk waka-kesiswaan & waka-sarpras).
 *
 * Catatan honest tentang TABUNGAN (spec §6): investigasi menemukan gap tabungan
 * TIDAK manifes — TabunganPolicy adalah dead code (AuthServiceProvider tidak
 * di-load di bootstrap/providers.php) DAN TabunganSiswaController::index tidak
 * ada in-controller auth (authType null) + route middleware hanya ['web','auth'].
 * Jadi waka (dan semua user ter-auth) sudah bisa view tabungan tanpa perubahan.
 * Permission finance.student-saving.view tetap di-assign ke waka (forward-looking,
 * untuk saat ACL tabungan di-wire properly di task terpisah).
 */
class WakaViewOnlyGapTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_kesiswaan_can_view_jadwal_via_policy(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000001']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-kesiswaan');

        // view-only jadwal: punya academic.schedule.view, bukan .* (manage)
        $this->assertTrue($waka->can('academic.schedule.view'));
        $this->assertFalse($waka->can('academic.schedule.*'));

        // JadwalPolicy::viewAny accept academic.schedule.view → waka view-only lolos
        $this->assertTrue(Gate::forUser($waka)->allows('viewAny', Jadwal::class));
    }

    public function test_waka_sarpras_can_view_jadwal_via_policy(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000002']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-sarpras');

        $this->assertTrue($waka->can('academic.schedule.view'));
        $this->assertTrue(Gate::forUser($waka)->allows('viewAny', Jadwal::class));
    }

    public function test_waka_kurikulum_can_manage_jadwal_via_policy(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $tenant = Tenant::create(['nama' => 'T', 'npsn' => '70000003']);
        $waka = User::factory()->create(['tenant_id' => $tenant->id, 'tipe' => 'pegawai']);
        $waka->assignRole('waka-kurikulum');

        // waka-kurikulum manage jadwal: punya academic.schedule.*
        $this->assertTrue($waka->can('academic.schedule.*'));
        $this->assertTrue(Gate::forUser($waka)->allows('viewAny', Jadwal::class));
    }
}
