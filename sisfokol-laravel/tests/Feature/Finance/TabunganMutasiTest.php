<?php

namespace Tests\Feature\Finance;

use App\Modules\Academic\Models\Siswa;
use App\Modules\Finance\Models\TabunganSiswa;
use App\Modules\Finance\Services\TabunganMutasiService;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabunganMutasiTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Siswa $siswa;
    private TabunganMutasiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['nama' => 'Tenant Keuangan', 'npsn' => '22222222']);
        app(TenantContext::class)->set($this->tenant->id);

        $this->siswa = Siswa::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->service = app(TabunganMutasiService::class);
    }

    public function test_get_or_create_rekening_tabungan(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);

        $this->assertNotNull($tabungan);
        $this->assertEquals($this->siswa->id, $tabungan->siswa_id);
        $this->assertEquals(0, $tabungan->saldo);
        $this->assertNotEmpty($tabungan->no_rekening);

        // Fetching again should return the same account
        $tabungan2 = $this->service->getOrCreateAccount($this->siswa);
        $this->assertEquals($tabungan->id, $tabungan2->id);
    }

    public function test_setor_tabungan_increases_saldo(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);
        
        $updated = $this->service->setor($tabungan, 150000);

        $this->assertEquals(150000, $updated->saldo);
        $this->assertDatabaseHas('tabungan_siswa', [
            'id' => $tabungan->id,
            'saldo' => 150000
        ]);
    }

    public function test_tarik_tabungan_decreases_saldo(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);
        $this->service->setor($tabungan, 200000);

        $updated = $this->service->tarik($tabungan, 50000);

        $this->assertEquals(150000, $updated->saldo);
        $this->assertDatabaseHas('tabungan_siswa', [
            'id' => $tabungan->id,
            'saldo' => 150000
        ]);
    }

    public function test_tarik_tabungan_throws_exception_if_insufficient_saldo(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);
        $this->service->setor($tabungan, 30000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Saldo tidak mencukupi untuk melakukan penarikan.');

        $this->service->tarik($tabungan, 50000);
    }

    public function test_setor_and_tarik_validation_negative_amount(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nominal harus lebih besar dari nol.');

        $this->service->setor($tabungan, -100);
    }

    public function test_siswa_has_one_tabungan_siswa_relation(): void
    {
        $tabungan = $this->service->getOrCreateAccount($this->siswa);
        
        $this->assertTrue($this->siswa->tabunganSiswa()->exists());
        $this->assertEquals($tabungan->id, $this->siswa->tabunganSiswa->id);
    }
}
