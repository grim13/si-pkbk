<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $fillable = [
        'pertemuan_pembelajaran_id',
        'judul',
        'keterangan',
        'created_by',
    ];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(PertemuanPembelajaran::class, 'pertemuan_pembelajaran_id');
    }

    public function pertemuanPembelajaran(): BelongsTo
    {
        return $this->belongsTo(PertemuanPembelajaran::class, 'pertemuan_pembelajaran_id');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(GaleriFoto::class, 'galeri_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(GaleriFoto::class, 'galeri_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Filter galeri hanya untuk wali yang pesertanya terdaftar pada kelas (jadwal/mata pelajaran)
     * dan tercatat hadir pada pertemuan pembelajaran terkait.
     */
    public function scopeAccessibleByWali(Builder $query, int $waliId): Builder
    {
        return $query->whereHas('pertemuan', function (Builder $pertemuanQuery) use ($waliId) {
            $pertemuanQuery->whereHas('pesertaDetail', function (Builder $detailQuery) use ($waliId) {
                $detailQuery->where('presensi', 'hadir')
                    ->whereHas('pendaftaranPeserta', function (Builder $pesertaQuery) use ($waliId) {
                        $pesertaQuery->where('wali_id', $waliId)
                            ->where('status_pendaftaran', 'diterima')
                            ->where(function (Builder $dateQuery) {
                                $dateQuery->whereNull('tanggal_mulai_diterima')
                                    ->orWhereRaw('DATE(pendaftaran_peserta.tanggal_mulai_diterima) <= DATE(pertemuan_pembelajaran.tanggal)');
                            })
                            ->where(function (Builder $disabilitasQuery) {
                                $disabilitasQuery->whereNotExists(function ($subQuery) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('jadwal')
                                        ->join('jenis_disabilitas_mata_pelajaran', 'jenis_disabilitas_mata_pelajaran.mata_pelajaran_id', '=', 'jadwal.mata_pelajaran_id')
                                        ->whereColumn('jadwal.id', 'pertemuan_pembelajaran.jadwal_id');
                                })->orWhereExists(function ($subQuery) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('jadwal')
                                        ->join('jenis_disabilitas_mata_pelajaran', 'jenis_disabilitas_mata_pelajaran.mata_pelajaran_id', '=', 'jadwal.mata_pelajaran_id')
                                        ->whereColumn('jadwal.id', 'pertemuan_pembelajaran.jadwal_id')
                                        ->whereColumn('jenis_disabilitas_mata_pelajaran.jenis_disabilitas_id', 'pendaftaran_peserta.jenis_disabilitas_id');
                                });
                            });
                    });
            });
        });
    }
}
