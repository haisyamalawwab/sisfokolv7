<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\KelasSiswa;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class KelasSiswaController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => KelasSiswa::class,
            'view'       => 'academic.kelas-siswa',
            'route'      => 'academic.kelas-siswa',
            'authorize'  => 'kelas-siswa',
            'authType'   => 'permission',
            'search'     => [],
            'with'       => ['siswa', 'kelas', 'tahunAjaran'],
            'rules'      => [
                'store' => [
                    'kelas_id'         => 'required|exists:kelas,id',
                    'siswa_id'         => 'required|exists:siswa,id',
                    'tahun_ajaran_id'  => 'required|exists:tahun_ajaran,id',
                    'no_urut'          => 'nullable|integer|min:1',
                ],
            ],
            'viewData' => [
                'kelasList'      => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
                'siswaList'      => Siswa::where('status', 'aktif')->orderBy('nama')->get(),
                'tahunAjaranList' => TahunAjaran::orderBy('nama', 'desc')->get(),
            ],
        ];
    }
}
