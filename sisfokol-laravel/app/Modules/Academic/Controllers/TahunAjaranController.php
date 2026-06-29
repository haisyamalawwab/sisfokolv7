<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\TahunAjaran;
use App\Support\Crudlfix\Crudlfix;

class TahunAjaranController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => TahunAjaran::class,
            'view'       => 'academic.tahun-ajaran',
            'route'      => 'academic.tahun-ajaran',
            'authorize'  => 'tahun-ajaran',
            'authType'   => 'permission',
            'search'     => ['nama'],
            'rules'      => [
                'store' => [
                    'nama'            => 'required|string|max:20|unique:tahun_ajaran,nama',
                    'tanggal_mulai'   => 'required|date',
                    'tanggal_selesai' => 'required|date|after:tanggal_mulai',
                    'aktif'           => 'boolean',
                ],
                'update' => [
                    'nama'            => 'required|string|max:20',
                    'tanggal_mulai'   => 'required|date',
                    'tanggal_selesai' => 'required|date|after:tanggal_mulai',
                    'aktif'           => 'boolean',
                ],
            ],
        ];
    }

    protected function beforeStore(array $validated): array
    {
        $validated['aktif'] = $validated['aktif'] ?? false;
        return $validated;
    }
}
