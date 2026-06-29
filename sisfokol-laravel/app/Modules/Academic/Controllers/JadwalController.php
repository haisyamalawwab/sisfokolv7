<?php

namespace App\Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Jadwal;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\Mapel;
use App\Modules\Academic\Models\Guru;
use App\Modules\Academic\Models\TahunAjaran;
use App\Modules\Academic\Models\Semester;
use App\Modules\Academic\Services\JadwalConflictChecker;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'      => Jadwal::class,
            'view'       => 'academic.jadwal',
            'route'      => 'academic.jadwal',
            'authorize'  => 'jadwal',
            'authType'   => 'policy',
            'search'     => [],
            'with'       => ['tahunAjaran', 'semester', 'kelas', 'mapel', 'guru'],

            // Cascading: tahun ajaran → semester
            'cascades'   => [
                'tahun_ajaran_id' => [
                    'target' => 'semester_id',
                    'query'  => fn ($value) => \App\Modules\Academic\Models\Semester::where('tahun_ajaran_id', $value),
                    'value'  => 'id',
                    'label'  => 'nama',
                ],
            ],

            // Search select for guru
            'searchSelects' => [
                'guru_id' => [
                    'query' => fn ($q) => \App\Modules\Academic\Models\Guru::where('nama', 'like', "%{$q}%")->where('aktif', true),
                    'value' => 'id',
                    'label' => 'nama',
                ],
            ],

            'rules'      => [
                'store' => [
                    'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                    'semester_id'     => 'required|exists:semester,id',
                    'kelas_id'        => 'required|exists:kelas,id',
                    'mapel_id'        => 'required|exists:mapel,id',
                    'guru_id'         => 'required|exists:guru,id',
                    'hari'            => 'required|integer|min:1|max:7',
                    'jam_ke'          => 'required|integer|min:1|max:10',
                    'jam_mulai'       => 'required|date_format:H:i',
                    'jam_selesai'     => 'required|date_format:H:i|after:jam_mulai',
                    'ruang'           => 'nullable|string|max:30',
                ],
            ],
            'viewData' => [
                'tahunAjarans' => \App\Modules\Academic\Models\TahunAjaran::orderBy('nama', 'desc')->get(),
                'kelasList'    => \App\Modules\Academic\Models\Kelas::orderBy('tingkat')->orderBy('nama')->get(),
                'mapels'       => \App\Modules\Academic\Models\Mapel::orderBy('nama')->get(),
                // guru removed — now uses searchSelect
            ],
        ];
    }

    /**
     * Hook: validate conflicts before storing.
     */
    protected function beforeStore(array $validated, Request $request): array
    {
        $checker = app(JadwalConflictChecker::class);
        $conflicts = $checker->validate($validated);

        if (!empty($conflicts)) {
            abort(422, implode(' ', $conflicts));
        }

        return $validated;
    }

    /**
     * Hook: validate conflicts before updating.
     */
    protected function beforeUpdate(array $validated, $model, Request $request): array
    {
        $checker = app(JadwalConflictChecker::class);
        $conflicts = $checker->validate($validated, $model->id);

        if (!empty($conflicts)) {
            abort(422, implode(' ', $conflicts));
        }

        return $validated;
    }
}
