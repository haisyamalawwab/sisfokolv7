<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Guru;
use App\Modules\Tenancy\Models\Branch;
use App\Support\Crudlfix\Crudlfix;

class KelasController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Kelas::class,
            'view'       => 'academic.kelas',
            'route'      => 'academic.kelas',
            'authorize'  => 'kelas',
            'authType'   => 'policy',
            'search'     => ['nama'],
            'with'       => ['waliKelas', 'branch'],
            'rules'      => [
                'store' => [
                    'branch_id'    => 'nullable|exists:branches,id',
                    'wali_kelas_id' => 'nullable|exists:guru,id',
                    'nama'         => 'required|string|max:30',
                    'tingkat'      => 'required|integer|min:1|max:12',
                    'kapasitas'    => 'nullable|integer|min:1|max:100',
                ],
                'update' => [
                    'branch_id'    => 'nullable|exists:branches,id',
                    'wali_kelas_id' => 'nullable|exists:guru,id',
                    'nama'         => 'required|string|max:30',
                    'tingkat'      => 'required|integer|min:1|max:12',
                    'kapasitas'    => 'nullable|integer|min:1|max:100',
                ],
            ],
            'viewData' => [
                'gurus'   => Guru::where('aktif', true)->orderBy('nama')->get(),
                'branches' => Branch::orderBy('nama')->get(),
            ],
            'perPage' => 20,
        ];
    }
}
