<?php

namespace App\Modules\Academic\Models;

use App\Models\Traits\{BelongsToTenant, TracksAuditColumns};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, TracksAuditColumns;

    protected static function newFactory()
    {
        return \Database\Factories\SiswaFactory::new();
    }

    protected $table = 'siswa';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Sync Indonesian to English
            if (isset($model->nama)) $model->name = $model->nama;
            if (isset($model->jenis_kelamin)) $model->gender = $model->jenis_kelamin;
            if (isset($model->tempat_lahir)) $model->birth_place = $model->tempat_lahir;
            if (isset($model->tanggal_lahir)) $model->birth_date = $model->tanggal_lahir;
            if (isset($model->alamat)) $model->address = $model->alamat;
            if (isset($model->telepon)) $model->phone = $model->telepon;
            if (isset($model->foto)) $model->photo_path = $model->foto;
            if (isset($model->qrcode)) $model->qrcode_path = $model->qrcode;

            // Sync English to Indonesian
            if (isset($model->name)) $model->nama = $model->name;
            if (isset($model->gender)) $model->jenis_kelamin = $model->gender;
            if (isset($model->birth_place)) $model->tempat_lahir = $model->birth_place;
            if (isset($model->birth_date)) $model->tanggal_lahir = $model->birth_date;
            if (isset($model->address)) $model->alamat = $model->address;
            if (isset($model->phone)) $model->telepon = $model->phone;
            if (isset($model->photo_path)) $model->foto = $model->photo_path;
            if (isset($model->qrcode_path)) $model->qrcode = $model->qrcode_path;
        });
    }

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

    // [2026-06-29 | AG] add relationship to TabunganSiswa model
    public function tabunganSiswa(): HasOne
    {
        return $this->hasOne(\App\Modules\Finance\Models\TabunganSiswa::class, 'siswa_id');
    }
}
