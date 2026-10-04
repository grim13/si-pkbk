<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKeluarga extends Model
{
    protected $table = 'anggota_keluarga';

    protected $fillable = ['wali_id', 'nama', 'status', 'tanggal_lahir', 'pendidikan', 'pekerjaan'];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function wali(): BelongsTo
    {
        return $this->belongsTo(Wali::class);
    }
}
