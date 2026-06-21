<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasSiswa extends Model
{
    use BelongsToTenant, TracksAuditColumns;

    protected $table = 'kelas_siswa';

    public $timestamps = true;

    protected $fillable = ['kelas_id', 'siswa_id', 'tahun_ajaran_id', 'no_urut', 'tenant_id'];

    protected function casts(): array
    {
        return [
            'kelas_id' => 'integer',
            'siswa_id' => 'integer',
            'tahun_ajaran_id' => 'integer',
            'no_urut' => 'integer',
        ];
    }

    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }
    public function tahunAjaran(): BelongsTo { return $this->belongsTo(TahunAjaran::class); }
}
