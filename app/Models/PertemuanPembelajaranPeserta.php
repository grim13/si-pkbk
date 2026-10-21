<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PertemuanPembelajaranPeserta extends Model
{
    protected $table = 'pertemuan_pembelajaran_peserta';

    protected $fillable = [
        'pertemuan_pembelajaran_id',
        'pendaftaran_peserta_id',
        'presensi',
        'nilai',
        'keterangan',
    ];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(PertemuanPembelajaran::class, 'pertemuan_pembelajaran_id');
    }

    public function pendaftaranPeserta(): BelongsTo
    {
        return $this->belongsTo(PendaftaranPeserta::class, 'pendaftaran_peserta_id');
    }
}
