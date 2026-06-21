<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapelJenis extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected $table = 'mapel_jenis';

    protected $fillable = ['nama', 'kode'];

    public function mapels(): HasMany
    {
        return $this->hasMany(Mapel::class, 'mapel_jenis_id');
    }
}
