<?php

namespace Tests\Feature\Crudlfix;

use App\Models\User;
use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive CRUDLFIX RBAC Test
 *
 * Tests all authorization modes:
 * 1. Policy-based auth (SiswaController → SiswaPolicy)
 * 2. Permission-based auth (if any controller uses it)
 * 3. No auth (Admin controllers with route middleware)
 * 4. Tenant isolation
 * 5. Role-based access control
 */
class CrudlfixRbacTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;
    protected Tenant $tenant2;
    protected User $superAdmin;
    protected User $admin1;
    protected User $teacher1;
    protected User $student1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        // Create tenants
        $this->tenant1 = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        $this->tenant2 = Tenant::create(['nama' => 'Sekolah B', 'npsn' => '10000002']);

        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);

        // SuperAdmin
        $this->superAdmin = User::factory()->create(['tenant_id' => null]);

        // Admin for Tenant 1
        $this->admin1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $registrar->setPermissionsTeamId($this->tenant1->id);
        $this->admin1->assignRole('admin');
        $registrar->setPermissionsTeamId(null);

        // Teacher for Tenant 1
        $this->teacher1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $registrar->setPermissionsTeamId($this->tenant1->id);
        $this->teacher1->assignRole('teacher');
        $registrar->setPermissionsTeamId(null);

        // Student for Tenant 1
        $this->student1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $registrar->setPermissionsTeamId($this->tenant1->id);
        $this->student1->assignRole('student');
        $registrar->setPermissionsTeamId(null);
    }

    // ─── POLICY-BASED AUTH (SiswaController) ───────────────────────

    public function test_policy_admin_can_view_siswa_index(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->get('/academic/siswa');
        $response->assertStatus(200);
    }

    public function test_policy_teacher_can_view_siswa_index(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->teacher1)->get('/academic/siswa');
        $response->assertStatus(200);
    }

    public function test_policy_student_cannot_view_siswa_index(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        $response = $this->actingAs($this->student1)->get('/academic/siswa');
        $response->assertStatus(403);
    }

    public function test_policy_admin_can_create_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        $response = $this->actingAs($this->admin1)->post('/academic/siswa', [
            'nis' => '9988776655',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/academic/siswa');
        $this->assertDatabaseHas('siswa', ['nis' => '9988776655']);
    }

    public function test_policy_teacher_cannot_create_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        $response = $this->actingAs($this->teacher1)->post('/academic/siswa', [
            'nis' => '9988776655',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response->assertStatus(403);
    }

    public function test_policy_admin_can_update_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->put("/academic/siswa/{$siswa->id}", [
            'nis' => $siswa->nis,
            'nama' => 'Updated Name',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/academic/siswa');
        $this->assertDatabaseHas('siswa', ['id' => $siswa->id, 'nama' => 'Updated Name']);
    }

    public function test_policy_teacher_cannot_update_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->teacher1)->put("/academic/siswa/{$siswa->id}", [
            'nis' => $siswa->nis,
            'nama' => 'Hacked Name',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response->assertStatus(403);
    }

    public function test_policy_admin_can_delete_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->delete("/academic/siswa/{$siswa->id}");
        $response->assertRedirect('/academic/siswa');
        $this->assertSoftDeleted('siswa', ['id' => $siswa->id]);
    }

    public function test_policy_teacher_cannot_delete_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->teacher1)->delete("/academic/siswa/{$siswa->id}");
        $response->assertStatus(403);
    }

    // ─── TENANT ISOLATION ──────────────────────────────────────────

    public function test_tenant_isolation_admin_cannot_view_other_tenant_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa2 = Siswa::factory()->create(['tenant_id' => $this->tenant2->id]);

        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->get("/academic/siswa/{$siswa2->id}");
        $response->assertStatus(403);
    }

    public function test_tenant_isolation_admin_cannot_update_other_tenant_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa2 = Siswa::factory()->create(['tenant_id' => $this->tenant2->id]);

        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->put("/academic/siswa/{$siswa2->id}", [
            'nis' => $siswa2->nis,
            'nama' => 'Hacked',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);
        $response->assertStatus(403);
    }

    public function test_tenant_isolation_admin_cannot_delete_other_tenant_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa2 = Siswa::factory()->create(['tenant_id' => $this->tenant2->id]);

        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->admin1)->delete("/academic/siswa/{$siswa2->id}");
        $response->assertStatus(403);
    }

    // ─── SUPERADMIN BYPASS ─────────────────────────────────────────

    public function test_superadmin_can_view_any_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->superAdmin)->get("/academic/siswa/{$siswa->id}");
        $response->assertStatus(200);
    }

    public function test_superadmin_can_update_any_siswa(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        $siswa = Siswa::factory()->create(['tenant_id' => $this->tenant1->id]);

        app(TenantContext::class)->clear();
        $response = $this->actingAs($this->superAdmin)->put("/academic/siswa/{$siswa->id}", [
            'nis' => $siswa->nis,
            'nama' => 'SuperAdmin Updated',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/academic/siswa');
        $this->assertDatabaseHas('siswa', ['id' => $siswa->id, 'nama' => 'SuperAdmin Updated']);
    }

    // ─── NO AUTH (Admin controllers with route middleware) ──────────

    public function test_no_auth_admin_can_access_academic_years(): void
    {
        $this->actingAs($this->admin1);
        $response = $this->get('/admin/academic-years');
        // Should work if route middleware allows admin role
        $response->assertStatus(200);
    }

    // ─── SEARCH FUNCTIONALITY WITH RBAC ────────────────────────────

    public function test_search_works_with_policy_auth(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        Siswa::factory()->create(['tenant_id' => $this->tenant1->id, 'nama' => 'Ahmad Fauzi']);
        Siswa::factory()->create(['tenant_id' => $this->tenant1->id, 'nama' => 'Budi Santoso']);

        $response = $this->actingAs($this->admin1)->get('/academic/siswa?search=Ahmad');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Budi Santoso');
    }

    // ─── PAGINATION WITH RBAC ──────────────────────────────────────

    public function test_pagination_works_with_policy_auth(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);
        Siswa::factory()->count(20)->create(['tenant_id' => $this->tenant1->id]);

        $response = $this->actingAs($this->admin1)->get('/academic/siswa');
        $response->assertStatus(200);
    }

    // ─── GATE AUTHORIZATION (Non-policy controllers) ───────────────

    public function test_gate_authorization_guru_controller(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        // Admin can access guru index (has guru.view permission)
        $response = $this->actingAs($this->admin1)->get('/academic/guru');
        $response->assertStatus(200);
    }

    public function test_gate_authorization_student_cannot_access_guru(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        // Student cannot access guru index (no guru.view permission)
        $response = $this->actingAs($this->student1)->get('/academic/guru');
        $response->assertStatus(403);
    }

    public function test_gate_authorization_teacher_can_access_guru(): void
    {
        app(TenantContext::class)->set($this->tenant1->id);

        // Teacher can access guru index (has guru.view permission)
        $response = $this->actingAs($this->teacher1)->get('/academic/guru');
        $response->assertStatus(200);
    }
}
