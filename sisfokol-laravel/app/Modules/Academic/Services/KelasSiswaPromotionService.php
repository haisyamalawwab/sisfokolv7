<?php

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\KelasSiswa;
use Illuminate\Support\Facades\DB;

class KelasSiswaPromotionService
{
    /**
     * Promote siswa from $fromTapel to $toTapel based on kelas mapping.
     * $kelasMapping: [[oldKelasId => newKelasId], ...]
     * Idempotent — re-running on same tapel target won't duplicate.
     * ADR-003: History preserved — old kelas_siswa rows untouched.
     */
    public function promote(int $fromTapel, int $toTapel, array $kelasMapping): int
    {
        $moved = 0;
        DB::transaction(function () use ($fromTapel, $toTapel, $kelasMapping, &$moved) {
            foreach ($kelasMapping as $map) {
                foreach ($map as $oldKelasId => $newKelasId) {
                    $rows = KelasSiswa::withoutGlobalScope('tenant')
                        ->where('tahun_ajaran_id', $fromTapel)
                        ->where('kelas_id', $oldKelasId)
                        ->get();

                    foreach ($rows as $row) {
                        KelasSiswa::withoutGlobalScope('tenant')->firstOrCreate(
                            [
                                'tenant_id' => $row->tenant_id,
                                'siswa_id' => $row->siswa_id,
                                'kelas_id' => $newKelasId,
                                'tahun_ajaran_id' => $toTapel,
                            ],
                            ['no_urut' => $row->no_urut],
                        );
                        $moved++;
                    }
                }
            }
        });
        return $moved;
    }
}
