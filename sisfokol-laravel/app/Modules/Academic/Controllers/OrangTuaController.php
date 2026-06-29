<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\OrangTua;
use App\Support\Crudlfix\Crudlfix;

class OrangTuaController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => OrangTua::class,
            'view'       => 'academic.orang-tua',
            'route'      => 'academic.orang-tua',
            'authorize'  => 'orang-tua',
            'authType'   => 'permission',
            'search'     => ['nama', 'telepon', 'email'],
            'rules'      => [
                'store' => [
                    'nama'      => 'required|string|max:100',
                    'hubungan'  => 'required|in:ayah,ibu,wali',
                    'telepon'   => 'nullable|string|max:30',
                    'email'     => 'nullable|email|max:100',
                    'pekerjaan' => 'nullable|string|max:100',
                    'alamat'    => 'nullable|string',
                    'username'  => 'nullable|string|max:50|unique:orang_tua,username',
                    'password'  => 'nullable|string|min:6',
                ],
                'update' => [
                    'nama'      => 'required|string|max:100',
                    'hubungan'  => 'required|in:ayah,ibu,wali',
                    'telepon'   => 'nullable|string|max:30',
                    'email'     => 'nullable|email|max:100',
                    'pekerjaan' => 'nullable|string|max:100',
                    'alamat'    => 'nullable|string',
                    'username'  => 'nullable|string|max:50',
                    'password'  => 'nullable|string|min:6',
                ],
            ],
        ];
    }
}
