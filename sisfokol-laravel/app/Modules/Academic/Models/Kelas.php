<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\KelasFactory::new();
    }

    protected $table = 'kelas';

    protected $fillable = [
        'branch_id', 'wali_kelas_id', 'nama', 'tingkat', 'kapasitas',
    ];

    protected function casts(): array
    {
        return [
            'tingkat' => 'integer',
            'kapasitas' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'kelas_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'kelas_id');
    }
}
