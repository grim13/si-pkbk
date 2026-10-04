<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsesmenSeksi extends Model
{
    protected $table = 'asesmen_seksi';

    protected $fillable = ['kode', 'nama', 'urutan'];

    public function pertanyaan(): HasMany
    {
        return $this->hasMany(AsesmenPertanyaan::class)->orderBy('urutan');
    }
}
