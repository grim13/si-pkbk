<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriFoto extends Model
{
    protected $table = 'galeri_foto';

    protected $fillable = [
        'galeri_id',
        'file_path',
        'nama_file',
        'keterangan',
    ];

    public function galeri(): BelongsTo
    {
        return $this->belongsTo(Galeri::class, 'galeri_id');
    }
}
