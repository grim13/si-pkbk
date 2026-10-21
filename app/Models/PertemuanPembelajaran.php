<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertemuanPembelajaran extends Model
{
    protected $table = 'pertemuan_pembelajaran';

    protected $fillable = [
        'jadwal_id',
        'instruktur_id',
        'tanggal',
        'status',
        'keterangan',
        'catatan_pekerja_sosial',
        'pekerja_sosial_id',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'approved_at' => 'datetime',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    public function pekerjaSosial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pekerja_sosial_id');
    }

    public function pesertaDetail(): HasMany
    {
        return $this->hasMany(PertemuanPembelajaranPeserta::class, 'pertemuan_pembelajaran_id');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['pengajuan', 'acc'], true);
    }
}
