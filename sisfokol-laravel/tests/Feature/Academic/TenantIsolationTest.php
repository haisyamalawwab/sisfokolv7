<?php

namespace Tests\Feature\Academic;

use App\Modules\Academic\Models\{Guru, Jadwal, Kelas, Mapel, MapelJenis, OrangTua, Semester, Siswa, TahunAjaran};
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_academic_models_enforce_tenant_isolation(): void
    {
        $tenant1 = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        $tenant2 = Tenant::create(['nama' => 'Sekolah B', 'npsn' => '10000002']);

        // Set context to Tenant 1
        app(TenantContext::class)->set($tenant1->id);

        $mapelJenis1 = MapelJenis::create(['nama' => 'Wajib', 'tenant_id' => $tenant1->id]);
        $tapel1 = TahunAjaran::create(['nama' => '2025/2026', 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2026-06-30', 'tenant_id' => $tenant1->id]);
        $semester1 = Semester::create([
            'nama' => 1,
            'tahun_ajaran_id' => $tapel1->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'aktif' => true,
            'tenant_id' => $tenant1->id
        ]);
        $ortu1 = OrangTua::create(['nama' => 'Bapak A', 'tenant_id' => $tenant1->id]);
        $siswa1 = Siswa::factory()->create(['tenant_id' => $tenant1->id]);
        $guru1 = Guru::factory()->create(['tenant_id' => $tenant1->id]);
        $kelas1 = Kelas::factory()->create([
            'tenant_id' => $tenant1->id,
            'wali_kelas_id' => $guru1->id,
        ]);
        $mapel1 = Mapel::factory()->create(['tenant_id' => $tenant1->id]);
        $jadwal1 = Jadwal::create([
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapel1->id,
            'guru_id' => $guru1->id,
            'tahun_ajaran_id' => $tapel1->id,
            'semester_id' => $semester1->id,
            'hari' => 1,
            'jam_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'ruang' => 'Teori 1',
            'tenant_id' => $tenant1->id,
        ]);

        // Set context to Tenant 2
        app(TenantContext::class)->set($tenant2->id);

        $mapelJenis2 = MapelJenis::create(['nama' => 'Peminatan', 'tenant_id' => $tenant2->id]);
        $tapel2 = TahunAjaran::create(['nama' => '2025/2026', 'tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2026-06-30', 'tenant_id' => $tenant2->id]);
        $semester2 = Semester::create([
            'nama' => 1,
            'tahun_ajaran_id' => $tapel2->id,
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'aktif' => true,
            'tenant_id' => $tenant2->id
        ]);
        $ortu2 = OrangTua::create(['nama' => 'Bapak B', 'tenant_id' => $tenant2->id]);
        $siswa2 = Siswa::factory()->create(['tenant_id' => $tenant2->id]);
        $guru2 = Guru::factory()->create(['tenant_id' => $tenant2->id]);
        $kelas2 = Kelas::factory()->create([
            'tenant_id' => $tenant2->id,
            'wali_kelas_id' => $guru2->id,
        ]);
        $mapel2 = Mapel::factory()->create(['tenant_id' => $tenant2->id]);
        $jadwal2 = Jadwal::create([
            'kelas_id' => $kelas2->id,
            'mapel_id' => $mapel2->id,
            'guru_id' => $guru2->id,
            'tahun_ajaran_id' => $tapel2->id,
            'semester_id' => $semester2->id,
            'hari' => 1,
            'jam_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'ruang' => 'Teori 1',
            'tenant_id' => $tenant2->id,
        ]);

        // Verify isolation for Tenant 1
        app(TenantContext::class)->set($tenant1->id);

        $this->assertCount(1, MapelJenis::all());
        $this->assertEquals($mapelJenis1->id, MapelJenis::first()->id);

        $this->assertCount(1, TahunAjaran::all());
        $this->assertEquals($tapel1->id, TahunAjaran::first()->id);

        $this->assertCount(1, Semester::all());
        $this->assertEquals($semester1->id, Semester::first()->id);

        $this->assertCount(1, OrangTua::all());
        $this->assertEquals($ortu1->id, OrangTua::first()->id);

        $this->assertCount(1, Siswa::all());
        $this->assertEquals($siswa1->id, Siswa::first()->id);

        $this->assertCount(1, Guru::all());
        $this->assertEquals($guru1->id, Guru::first()->id);

        $this->assertCount(1, Kelas::all());
        $this->assertEquals($kelas1->id, Kelas::first()->id);

        $this->assertCount(1, Mapel::all());
        $this->assertEquals($mapel1->id, Mapel::first()->id);

        $this->assertCount(1, Jadwal::all());
        $this->assertEquals($jadwal1->id, Jadwal::first()->id);

        // Verify isolation for Tenant 2
        app(TenantContext::class)->set($tenant2->id);

        $this->assertCount(1, MapelJenis::all());
        $this->assertEquals($mapelJenis2->id, MapelJenis::first()->id);

        $this->assertCount(1, TahunAjaran::all());
        $this->assertEquals($tapel2->id, TahunAjaran::first()->id);

        $this->assertCount(1, Semester::all());
        $this->assertEquals($semester2->id, Semester::first()->id);

        $this->assertCount(1, OrangTua::all());
        $this->assertEquals($ortu2->id, OrangTua::first()->id);

        $this->assertCount(1, Siswa::all());
        $this->assertEquals($siswa2->id, Siswa::first()->id);

        $this->assertCount(1, Guru::all());
        $this->assertEquals($guru2->id, Guru::first()->id);

        $this->assertCount(1, Kelas::all());
        $this->assertEquals($kelas2->id, Kelas::first()->id);

        $this->assertCount(1, Mapel::all());
        $this->assertEquals($mapel2->id, Mapel::first()->id);

        $this->assertCount(1, Jadwal::all());
        $this->assertEquals($jadwal2->id, Jadwal::first()->id);
    }
}
