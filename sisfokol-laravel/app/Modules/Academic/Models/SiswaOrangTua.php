<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaOrangTua extends Model
{
    use BelongsToTenant;

    protected $table = 'siswa_orang_tua';

    protected $fillable = ['siswa_id', 'orang_tua_id', 'tenant_id'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(OrangTua::class);
    }
}
