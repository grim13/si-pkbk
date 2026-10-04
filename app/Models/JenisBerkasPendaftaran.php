<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBerkasPendaftaran extends Model
{
    protected $table = 'jenis_berkas_pendaftaran';

    protected $fillable = [
        'nama_berkas',
        'keterangan',
        'required',
        'format_file',
        'template_surat',
    ];
}
