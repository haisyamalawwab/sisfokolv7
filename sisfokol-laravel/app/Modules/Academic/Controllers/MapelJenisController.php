<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\MapelJenis;
use App\Support\Crudlfix\Crudlfix;

class MapelJenisController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => MapelJenis::class,
            'view'       => 'academic.mapel-jenis',
            'route'      => 'academic.mapel-jenis',
            'authorize'  => 'mapel',
            'authType'   => 'permission',
            'search'     => ['kode', 'nama'],
            'rules'      => [
                'store' => [
                    'kode' => 'required|string|max:30|unique:mapel_jenis,kode',
                    'nama' => 'required|string|max:50',
                ],
                'update' => [
                    'kode' => 'required|string|max:30',
                    'nama' => 'required|string|max:50',
                ],
            ],
        ];
    }
}
