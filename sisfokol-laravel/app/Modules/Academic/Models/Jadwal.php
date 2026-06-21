<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'jadwal';

    protected $fillable = [
        'tahun_ajaran_id', 'semester_id', 'kelas_id', 'mapel_id', 'guru_id',
        'hari', 'jam_ke', 'jam_mulai', 'jam_selesai', 'ruang',
    ];

    protected function casts(): array
    {
        return [
            'tahun_ajaran_id' => 'integer',
            'semester_id' => 'integer',
            'kelas_id' => 'integer',
            'mapel_id' => 'integer',
            'guru_id' => 'integer',
            'hari' => 'integer',
            'jam_ke' => 'integer',
        ];
    }

    public function tahunAjaran(): BelongsTo { return $this->belongsTo(TahunAjaran::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function mapel(): BelongsTo { return $this->belongsTo(Mapel::class); }
    public function guru(): BelongsTo { return $this->belongsTo(Guru::class); }
}
