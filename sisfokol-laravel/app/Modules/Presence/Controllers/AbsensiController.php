<?php

namespace App\Modules\Presence\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\AcademicYear;
use App\Modules\Academic\Models\Kelas;
use App\Modules\Academic\Models\KelasSiswa;
use App\Modules\Academic\Models\Siswa;
use App\Support\Crudlfix\Crudlfix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    use Crudlfix;

    protected function crudlfix(): array
    {
        return [
            'model'     => Absence::class,
            'view'      => 'presence.absensi',
            'route'     => 'presence.absensi',
            'authorize' => null,
            'search'    => [],
            'with'      => ['absentable'],
            'perPage'   => 25,
            'viewData'  => [],
        ];
    }

    /**
     * Override index: tampilkan dengan filter kelas.
     */
    public function index(Request $request)
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $selectedKelasId = $request->input('kelas_id');
        $selectedDate    = $request->input('date');

        $query = Absence::with('absentable')->latest('date');

        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
        }
        if ($selectedKelasId) {
            $siswaIds = KelasSiswa::where('kelas_id', $selectedKelasId)->pluck('siswa_id');
            $query->where('absentable_type', Siswa::class)
                  ->whereIn('absentable_id', $siswaIds);
        }

        $absences = $query->paginate(30)->withQueryString();

        return view('presence.absensi.index', compact('absences', 'kelasList', 'selectedKelasId', 'selectedDate'));
    }

    /**
     * Create: form absensi per kelas/rombel.
     */
    public function create(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        // Ambil semua kelas aktif beserta siswa-nya
        $kelasList = Kelas::with([
            'kelasSiswa.siswa' => function ($q) {
                $q->where('status', 'aktif')->orderBy('nama');
            },
        ])->orderBy('tingkat')->orderBy('nama')->get();

        $selectedKelasId = $request->input('kelas_id', $kelasList->first()?->id);

        return view('presence.absensi.create', compact('kelasList', 'selectedKelasId', 'academicYear'));
    }

    /**
     * Bulk store: simpan kehadiran seluruh siswa dalam satu kelas.
     * Status bukan 'hadir' → simpan Absence record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'kelas_id'   => 'required|exists:kelas,id',
            'status'     => 'required|array',
            'status.*'   => 'required|in:hadir,alpha,ijin,sakit',
        ]);

        $date    = $request->input('date');
        $statuses = $request->input('status', []);
        $userId  = Auth::id();

        $typeMap = [
            'alpha' => 'alpha',
            'ijin'  => 'permission',
            'sakit' => 'sick',
        ];

        $saved = 0;

        foreach ($statuses as $siswaId => $status) {
            // Hapus record lama untuk siswa + tanggal ini (idempotent)
            Absence::where([
                'absentable_type' => Siswa::class,
                'absentable_id'   => $siswaId,
                'date'            => $date,
            ])->delete();

            if ($status === 'hadir') {
                continue;
            }

            $siswa = Siswa::find($siswaId);
            if (! $siswa) continue;

            Absence::create([
                'user_id'         => $userId,
                'absentable_type' => Siswa::class,
                'absentable_id'   => $siswa->id,
                'date'            => $date,
                'type'            => $typeMap[$status] ?? $status,
                'reason'          => ucfirst($status),
            ]);

            $saved++;
        }

        $kelas   = Kelas::find($request->input('kelas_id'));
        $tanggal = \Carbon\Carbon::parse($date)->format('d M Y');
        $msg = $saved > 0
            ? "Absensi {$kelas?->nama} pada {$tanggal} tersimpan: {$saved} siswa tidak hadir."
            : "Semua siswa {$kelas?->nama} tercatat hadir pada {$tanggal}.";

        return redirect()
            ->route('presence.absensi.index')
            ->with('success', $msg);
    }
}
