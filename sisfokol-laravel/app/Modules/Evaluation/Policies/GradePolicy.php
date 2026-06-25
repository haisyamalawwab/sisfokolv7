<?php

namespace App\Modules\Evaluation\Policies;

use App\Models\User;

class GradePolicy
{
    /**
     * SuperAdmin selalu diizinkan via Gate::before di AppServiceProvider.
     */

    /**
     * Boleh lihat daftar nilai: admin & guru (pegawai).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin'])
            || $user->tipe === 'pegawai';
    }

    /**
     * Boleh input nilai batch: admin & guru (pegawai).
     */
    public function store(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin'])
            || $user->tipe === 'pegawai';
    }

    /**
     * Hitung nilai akhir semester: hanya admin.
     */
    public function calculate(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin']);
    }
}
