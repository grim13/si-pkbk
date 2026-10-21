<?php

namespace App\Models;

use Database\Factories\MataPelajaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    /** @use HasFactory<MataPelajaranFactory> */
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama',
        'keterangan',
    ];

    public function jenisDisabilitas(): BelongsToMany
    {
        return $this->belongsToMany(
            JenisDisabilitas::class,
            'jenis_disabilitas_mata_pelajaran',
            'mata_pelajaran_id',
            'jenis_disabilitas_id'
        )->withTimestamps();
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'mata_pelajaran_id');
    }
}
