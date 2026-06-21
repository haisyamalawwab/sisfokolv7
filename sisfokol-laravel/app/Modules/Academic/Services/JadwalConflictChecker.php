<?php

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Jadwal;
use App\Support\TenantContext;

class JadwalConflictChecker
{
    /**
     * Validate a jadwal payload. Return list of conflict messages (empty = no conflict).
     */
    public function validate(array $attrs, ?int $excludeId = null): array
    {
        $conflicts = [];
        $tenantId = $attrs['tenant_id'] ?? app(TenantContext::class)->id;

        if (!$tenantId) {
            return ["Tenant tidak valid atau belum diinisialisasi."];
        }

        // Kelas slot conflict
        $kelasQuery = Jadwal::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('tahun_ajaran_id', $attrs['tahun_ajaran_id'])
            ->where('semester_id', $attrs['semester_id'])
            ->where('kelas_id', $attrs['kelas_id'])
            ->where('hari', $attrs['hari'])
            ->where('jam_ke', $attrs['jam_ke']);
            
        if ($excludeId) {
            $kelasQuery->where('id', '!=', $excludeId);
        }
        if ($kelasQuery->exists()) {
            $conflicts[] = "Bentrok: kelas sudah ada jadwal di hari ke-{$attrs['hari']} jam ke-{$attrs['jam_ke']}.";
        }

        // Guru slot conflict
        $guruQuery = Jadwal::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('tahun_ajaran_id', $attrs['tahun_ajaran_id'])
            ->where('semester_id', $attrs['semester_id'])
            ->where('guru_id', $attrs['guru_id'])
            ->where('hari', $attrs['hari'])
            ->where('jam_ke', $attrs['jam_ke']);
            
        if ($excludeId) {
            $guruQuery->where('id', '!=', $excludeId);
        }
        if ($guruQuery->exists()) {
            $conflicts[] = "Bentrok: guru sudah mengajar di kelas lain pada hari ke-{$attrs['hari']} jam ke-{$attrs['jam_ke']}.";
        }

        return $conflicts;
    }
}
