<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wali extends Model
{
    protected $table = 'wali';

    protected $fillable = [
        'user_id',
        'nik',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'nomor_hp',
        'agama',
        'pendidikan_terakhir',
        'status_dalam_keluarga',
        'pekerjaan',
        'penghasilan_perbulan',
        'tanggungan_keluarga',
        'rumah_tempat_tinggal',
        'keadaan_lingkungan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anggotaKeluarga()
    {
        return $this->hasMany(AnggotaKeluarga::class);
    }
}
