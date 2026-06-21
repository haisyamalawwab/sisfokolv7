<?php

namespace Tests\Feature\Academic;

use App\Modules\Academic\Models\{Kelas, KelasSiswa, Siswa, TahunAjaran};
use App\Modules\Academic\Services\KelasSiswaPromotionService;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasSiswaPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_promote_moves_siswa_to_next_kelas_in_new_tapel(): void
    {
        [$tenant, $t1, $t2, $k7A, $k8A, $siswa] = $this->setupScenario();
        $svc = app(KelasSiswaPromotionService::class);

        $svc->promote($t1->id, $t2->id, [[$k7A->id => $k8A->id]]);

        // Siswa now in kelas 8-A for tapel 2
        $this->assertDatabaseHas('kelas_siswa', [
            'siswa_id' => $siswa->id, 'kelas_id' => $k8A->id, 'tahun_ajaran_id' => $t2->id,
        ]);
        // Old kelas_siswa for t1 still intact (history preserved)
        $this->assertDatabaseHas('kelas_siswa', [
            'siswa_id' => $siswa->id, 'kelas_id' => $k7A->id, 'tahun_ajaran_id' => $t1->id,
        ]);
    }

    public function test_promote_idempotent_does_not_duplicate(): void
    {
        [$tenant, $t1, $t2, $k7A, $k8A, $siswa] = $this->setupScenario();
        $svc = app(KelasSiswaPromotionService::class);
        $svc->promote($t1->id, $t2->id, [[$k7A->id => $k8A->id]]);
        $svc->promote($t1->id, $t2->id, [[$k7A->id => $k8A->id]]); // again

        $count = KelasSiswa::where('siswa_id', $siswa->id)
            ->where('kelas_id', $k8A->id)
            ->where('tahun_ajaran_id', $t2->id)
            ->count();
        $this->assertSame(1, $count);
    }

    private function setupScenario(): array
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        app(TenantContext::class)->set(tenantId: $tenant->id);
        $t1 = TahunAjaran::create(['nama' => '2025/2026', 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2026-06-30', 'tenant_id' => $tenant->id]);
        $t2 = TahunAjaran::create(['nama' => '2026/2027', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2027-06-30', 'tenant_id' => $tenant->id]);
        $k7A = Kelas::create(['nama' => '7-A', 'tingkat' => 7, 'tenant_id' => $tenant->id]);
        $k8A = Kelas::create(['nama' => '8-A', 'tingkat' => 8, 'tenant_id' => $tenant->id]);
        $siswa = Siswa::factory()->create(['tenant_id' => $tenant->id]);
        KelasSiswa::create(['siswa_id' => $siswa->id, 'kelas_id' => $k7A->id, 'tahun_ajaran_id' => $t1->id, 'tenant_id' => $tenant->id, 'no_urut' => 1]);
        return [$tenant, $t1, $t2, $k7A, $k8A, $siswa];
    }
}
