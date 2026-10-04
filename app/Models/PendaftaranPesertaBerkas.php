<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendaftaranPesertaBerkas extends Model
{
    protected $table = 'pendaftaran_peserta_berkas';

    protected $fillable = [
        'pendaftaran_peserta_id',
        'jenis_berkas_pendaftaran_id',
        'file_path',
        'status',
        'keterangan_perbaikan',
    ];

    public function pendaftaranPeserta()
    {
        return $this->belongsTo(PendaftaranPeserta::class);
    }

    public function jenisBerkasPendaftaran()
    {
        return $this->belongsTo(JenisBerkasPendaftaran::class);
    }
}
