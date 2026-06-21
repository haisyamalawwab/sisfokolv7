<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrangTua extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'orang_tua';

    protected $fillable = [
        'nama', 'hubungan', 'telepon', 'email', 'pekerjaan', 'alamat', 'username', 'password',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_orang_tua', 'orang_tua_id', 'siswa_id');
    }
}
