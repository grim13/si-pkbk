<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsesmenJawaban extends Model
{
    protected $table = 'asesmen_jawaban';

    protected $fillable = [
        'asesmen_id',
        'asesmen_pertanyaan_id',
        'jawaban_ya_tidak',
        'jawaban_text',
        'jawaban_number',
    ];

    protected $casts = [
        'jawaban_ya_tidak' => 'boolean',
        'jawaban_number' => 'float',
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(AsesmenPertanyaan::class, 'asesmen_pertanyaan_id');
    }
}
