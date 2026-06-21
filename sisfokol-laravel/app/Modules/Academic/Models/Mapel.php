<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'mapel';

    protected $fillable = [
        'kode', 'nama', 'mapel_jenis_id', 'kkm', 'kurikulum_id', 'jenjang',
    ];

    protected function casts(): array
    {
        return [
            'kkm' => 'decimal:2',
            'mapel_jenis_id' => 'integer',
            'kurikulum_id' => 'integer',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(MapelJenis::class, 'mapel_jenis_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'mapel_id');
    }
}
