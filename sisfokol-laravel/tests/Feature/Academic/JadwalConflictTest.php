<?php

namespace Tests\Feature\Academic;

use App\Modules\Academic\Models\{Guru, Jadwal, Kelas, Mapel, Semester, TahunAjaran};
use App\Modules\Academic\Services\JadwalConflictChecker;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalConflictTest extends TestCase
{
    use RefreshDatabase;

    private JadwalConflictChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = app(JadwalConflictChecker::class);
    }

    public function test_no_conflict_for_new_jadwal(): void
    {
        $data = $this->setupScenario();
        $newAttrs = array_merge($data['jadwal1_attrs'], ['hari' => 2]);
        unset($newAttrs['id'], $newAttrs['created_at'], $newAttrs['updated_at']);

        $conflicts = $this->checker->validate($newAttrs);
        $this->assertEmpty($conflicts);
    }

    public function test_conflict_same_kelas_same_slot(): void
    {
        $data = $this->setupScenario();
        // jadwal1 already exists, try to insert another jadwal at same slot for same kelas
        $newAttrs = array_merge($data['jadwal1_attrs'], ['mapel_id' => $data['mapel2']->id, 'guru_id' => $data['guru2']->id]);
        
        // Remove id and timestamps so we evaluate it as a new insert payload
        unset($newAttrs['id'], $newAttrs['created_at'], $newAttrs['updated_at']);
        
        $conflicts = $this->checker->validate($newAttrs);
        $this->assertNotEmpty($conflicts);
        $this->assertStringContainsString('kelas', implode('', $conflicts));
    }

    public function test_conflict_same_guru_same_slot(): void
    {
        $data = $this->setupScenario();
        // Use same guru, different kelas, same slot
        $newAttrs = array_merge($data['jadwal1_attrs'], [
            'kelas_id' => $data['kelas2']->id,
            'mapel_id' => $data['mapel2']->id,
        ]);
        
        unset($newAttrs['id'], $newAttrs['created_at'], $newAttrs['updated_at']);

        $conflicts = $this->checker->validate($newAttrs);
        $this->assertNotEmpty($conflicts);
        $this->assertStringContainsString('guru', implode('', $conflicts));
    }

    private function setupScenario(): array
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        app(TenantContext::class)->set(tenantId: $tenant->id);

        $tapel = TahunAjaran::create(['nama' => '2026/2027', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2027-06-30', 'aktif' => true, 'tenant_id' => $tenant->id]);
        $smt = Semester::create(['tahun_ajaran_id' => $tapel->id, 'nama' => 1, 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-31', 'aktif' => true, 'tenant_id' => $tenant->id]);
        $kelas1 = Kelas::create(['nama' => '7-A', 'tingkat' => 7, 'kapasitas' => 32, 'tenant_id' => $tenant->id]);
        $kelas2 = Kelas::create(['nama' => '7-B', 'tingkat' => 7, 'kapasitas' => 32, 'tenant_id' => $tenant->id]);
        $mapel1 = Mapel::create(['kode' => 'MTH', 'nama' => 'Matematika', 'kkm' => 75, 'tenant_id' => $tenant->id]);
        $mapel2 = Mapel::create(['kode' => 'SCI', 'nama' => 'IPA', 'kkm' => 75, 'tenant_id' => $tenant->id]);
        $guru1 = Guru::create(['nip' => 'G1', 'nama' => 'Guru 1', 'tenant_id' => $tenant->id]);
        $guru2 = Guru::create(['nip' => 'G2', 'nama' => 'Guru 2', 'tenant_id' => $tenant->id]);

        $jadwal1_attrs = [
            'tenant_id' => $tenant->id, 'tahun_ajaran_id' => $tapel->id, 'semester_id' => $smt->id,
            'kelas_id' => $kelas1->id, 'mapel_id' => $mapel1->id, 'guru_id' => $guru1->id,
            'hari' => 1, 'jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00', 'ruang' => 'R1',
        ];
        Jadwal::create($jadwal1_attrs);

        return compact('tenant', 'tapel', 'smt', 'kelas1', 'kelas2', 'mapel1', 'mapel2', 'guru1', 'guru2', 'jadwal1_attrs');
    }
}
