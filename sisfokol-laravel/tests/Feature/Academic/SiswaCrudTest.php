<?php

namespace Tests\Feature\Academic;

use App\Models\User;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SiswaCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;
    private User $admin1;
    private User $admin2;
    private User $teacher1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->tenant1 = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        $this->tenant2 = Tenant::create(['nama' => 'Sekolah B', 'npsn' => '10000002']);

        $registrar = app(PermissionRegistrar::class);

        // Admin for Tenant 1
        $this->admin1 = User::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'email' => 'admin1@sekolaha.sch.id',
        ]);
        $registrar->setPermissionsTeamId($this->tenant1->id);
        $this->admin1->assignRole('admin');
        $registrar->setPermissionsTeamId(null);

        // Admin for Tenant 2
        $this->admin2 = User::factory()->create([
            'tenant_id' => $this->tenant2->id,
            'email' => 'admin2@sekolahb.sch.id',
        ]);
        $registrar->setPermissionsTeamId($this->tenant2->id);
        $this->admin2->assignRole('admin');
        $registrar->setPermissionsTeamId(null);

        // Teacher for Tenant 1 with student.view role (homeroom-teacher)
        $this->teacher1 = User::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'email' => 'teacher1@sekolaha.sch.id',
        ]);
        $registrar->setPermissionsTeamId($this->tenant1->id);
        $this->teacher1->assignRole('homeroom-teacher');
        $registrar->setPermissionsTeamId(null);
    }

    public function test_authorized_admin_can_view_siswa_index(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->get('/academic/siswa');
        $response->assertStatus(200);
        $response->assertSee($siswa->nama);
    }

    public function test_teacher_can_view_siswa_index_but_cannot_create(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        // Can view index
        $response = $this->actingAs($this->teacher1)->get('/academic/siswa');
        $response->assertStatus(200);
        $response->assertSee($siswa->nama);

        // Cannot view create page
        $response = $this->actingAs($this->teacher1)->get('/academic/siswa/create');
        $response->assertStatus(403);

        // Cannot store
        $response = $this->actingAs($this->teacher1)->post('/academic/siswa', [
            'nis' => '1234567890',
            'nama' => 'New Student',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_create_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        $response = $this->actingAs($this->admin1)->post('/academic/siswa', [
            'nis' => '9988776655',
            'nisn' => '0099887766',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2012-05-15',
            'telepon' => '08123456789',
            'agama' => 'Islam',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/academic/siswa');
        $this->assertDatabaseHas('siswa', [
            'tenant_id' => $this->tenant1->id,
            'nis' => '9988776655',
            'nama' => 'Budi Santoso',
        ]);
    }

    public function test_admin_can_update_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id, 'nama' => 'Sebelum Update']);

        $response = $this->actingAs($this->admin1)->put("/academic/siswa/{$siswa->id}", [
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'nama' => 'Setelah Update',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2012-05-15',
            'telepon' => '08123456789',
            'agama' => 'Islam',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/academic/siswa');
        $this->assertDatabaseHas('siswa', [
            'id' => $siswa->id,
            'nama' => 'Setelah Update',
        ]);
    }

    public function test_admin_can_delete_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->delete("/academic/siswa/{$siswa->id}");
        $response->assertRedirect('/academic/siswa');
        
        $this->assertSoftDeleted('siswa', [
            'id' => $siswa->id,
        ]);
    }

    public function test_tenant_isolation_on_siswa(): void
    {
        // Create Siswa in Tenant 1
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa1 = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        // Create Siswa in Tenant 2
        app(TenantContext::class)->set($this->tenant2->id);
        $siswa2 = Siswa::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Clear TenantContext before GET request
        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->get("/academic/siswa/{$siswa2->id}");
        $response->assertStatus(403);

        // Clear TenantContext before PUT request
        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->put("/academic/siswa/{$siswa2->id}", [
            'nis' => $siswa2->nis,
            'nama' => 'Hacked Name',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
        $response->assertStatus(403);

        // Clear TenantContext before DELETE request
        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->delete("/academic/siswa/{$siswa2->id}");
        $response->assertStatus(403);
    }
}
