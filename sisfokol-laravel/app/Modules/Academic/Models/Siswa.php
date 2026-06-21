<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\SiswaFactory::new();
    }

    protected $table = 'siswa';

    protected $fillable = [
        'nis', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'telepon', 'foto', 'agama', 'status', 'qrcode',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function orangTuas(): BelongsToMany
    {
        return $this->belongsToMany(OrangTua::class, 'siswa_orang_tua', 'siswa_id', 'orang_tua_id');
    }

    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'siswa_id');
    }
}
