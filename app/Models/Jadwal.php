<?php

namespace App\Models;

use Database\Factories\JadwalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    /** @use HasFactory<JadwalFactory> */
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'mata_pelajaran_id',
        'instruktur_id',
        'tahun',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    public function pertemuan(): HasMany
    {
        return $this->hasMany(PertemuanPembelajaran::class, 'jadwal_id');
    }
}
