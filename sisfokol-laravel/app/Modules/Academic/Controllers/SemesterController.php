<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Semester;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class SemesterController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Semester::class,
            'view'       => 'academic.semester',
            'route'      => 'academic.semester',
            'authorize'  => 'semester',
            'authType'   => 'permission',
            'search'     => [],
            'with'       => ['tahunAjaran'],
            'rules'      => [
                'store' => [
                    'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                    'nama'            => 'required|integer|in:1,2',
                    'tanggal_mulai'   => 'required|date',
                    'tanggal_selesai' => 'required|date|after:tanggal_mulai',
                    'aktif'           => 'boolean',
                ],
                'update' => [
                    'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                    'nama'            => 'required|integer|in:1,2',
                    'tanggal_mulai'   => 'required|date',
                    'tanggal_selesai' => 'required|date|after:tanggal_mulai',
                    'aktif'           => 'boolean',
                ],
            ],
            'viewData' => [
                'tahunAjarans' => TahunAjaran::orderBy('nama', 'desc')->get(),
            ],
        ];
    }
}
