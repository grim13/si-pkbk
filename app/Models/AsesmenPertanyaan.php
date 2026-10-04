<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsesmenPertanyaan extends Model
{
    protected $table = 'asesmen_pertanyaan';

    protected $fillable = ['asesmen_seksi_id', 'nomor', 'pertanyaan', 'tipe_jawaban', 'satuan', 'urutan'];

    public function seksi(): BelongsTo
    {
        return $this->belongsTo(AsesmenSeksi::class, 'asesmen_seksi_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(AsesmenJawaban::class);
    }
}
