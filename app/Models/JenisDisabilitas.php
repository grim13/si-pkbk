<?php

namespace App\Models;

use Database\Factories\JenisDisabilitasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisDisabilitas extends Model
{
    /** @use HasFactory<JenisDisabilitasFactory> */
    use HasFactory;

    protected $table = 'jenis_disabilitas';

    protected $fillable = [
        'nama',
        'keterangan',
    ];

    public function pendaftaranPeserta(): HasMany
    {
        return $this->hasMany(PendaftaranPeserta::class, 'jenis_disabilitas_id');
    }

    public function mataPelajaran(): BelongsToMany
    {
        return $this->belongsToMany(
            MataPelajaran::class,
            'jenis_disabilitas_mata_pelajaran',
            'jenis_disabilitas_id',
            'mata_pelajaran_id'
        )->withTimestamps();
    }
}
