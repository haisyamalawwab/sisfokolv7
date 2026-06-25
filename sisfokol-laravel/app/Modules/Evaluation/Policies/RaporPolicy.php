<?php

namespace App\Modules\Evaluation\Policies;

use App\Models\User;

class RaporPolicy
{
    /**
     * SuperAdmin selalu diizinkan via Gate::before di AppServiceProvider.
     */

    /**
     * Boleh lihat daftar rapor: admin, wali kelas, dan guru.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin', 'wali_kelas'])
            || $user->tipe === 'pegawai';
    }

    /**
     * Boleh lihat rapor detail: admin, wali kelas, guru, dan siswa (rapornya sendiri).
     */
    public function view(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin', 'wali_kelas'])
            || $user->tipe === 'pegawai'
            || $user->tipe === 'siswa';
    }

    /**
     * Boleh download rapor PDF: hanya admin dan wali kelas.
     */
    public function download(User $user): bool
    {
        return $user->hasRole(['admin_sekolah', 'admin', 'wali_kelas']);
    }
}
