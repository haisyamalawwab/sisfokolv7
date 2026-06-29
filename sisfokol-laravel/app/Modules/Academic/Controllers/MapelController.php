<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Mapel;
use App\Modules\Academic\Models\MapelJenis;
use App\Support\Crudlfix\Crudlfix;

class MapelController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Mapel::class,
            'view'       => 'academic.mapel',
            'route'      => 'academic.mapel',
            'authorize'  => 'mapel',
            'authType'   => 'permission',
            'search'     => ['kode', 'nama'],
            'with'       => ['jenis'],
            'rules'      => [
                'store' => [
                    'kode'           => 'required|string|max:30|unique:mapel,kode,NULL,id,tenant_id,' . (auth()->user()->tenant_id ?? ''),
                    'nama'           => 'required|string|max:100',
                    'mapel_jenis_id' => 'nullable|exists:mapel_jenis,id',
                    'kkm'            => 'nullable|numeric|min:0|max:100',
                    'jenjang'        => 'nullable|string|max:10',
                ],
                'update' => [
                    'kode'           => 'required|string|max:30',
                    'nama'           => 'required|string|max:100',
                    'mapel_jenis_id' => 'nullable|exists:mapel_jenis,id',
                    'kkm'            => 'nullable|numeric|min:0|max:100',
                    'jenjang'        => 'nullable|string|max:10',
                ],
            ],
            'viewData' => [
                'jenisList' => MapelJenis::orderBy('nama')->get(),
            ],
        ];
    }
}
