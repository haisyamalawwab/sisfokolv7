<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahunAjaran extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\TahunAjaranFactory::new();
    }

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama', 'tanggal_mulai', 'tanggal_selesai', 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran_id');
    }

    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'tahun_ajaran_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'tahun_ajaran_id');
    }
}
