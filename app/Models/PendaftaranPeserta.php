<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranPeserta extends Model
{
    protected $table = 'pendaftaran_peserta';

    protected $fillable = [
        'wali_id',
        'jenis_disabilitas_id',
        'nik',
        'no_pendaftaran',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_asal',
        'pendidikan',
        'keterampilan_yang_diminati',
        'status_pendaftaran',
        'tanggal_mulai_diterima',
        'catatan_verifikasi',
        'jadwal_asesmen',
        'tempat_asesmen',
        // Fields for Asesmen
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'tinggi_badan',
        'berat_badan',
        'bentuk_keadaan_mata',
        'pendengaran',
        'golongan_darah',
        'nomor_induk_klien',
        'disabilitas_netra_sejak',
        'penyebab_disabilitas',
        'tempat_cacat',
        'pengaruh_cacat_tingkah_laku',
        'dirawat_di',
        'gradasi_kecacatan',
    ];

    protected $casts = [
        'jadwal_asesmen' => 'datetime',
        'tanggal_mulai_diterima' => 'datetime',
    ];

    public function wali()
    {
        return $this->belongsTo(Wali::class);
    }

    public function jenisDisabilitas(): BelongsTo
    {
        return $this->belongsTo(JenisDisabilitas::class, 'jenis_disabilitas_id');
    }

    public function berkas()
    {
        return $this->hasMany(PendaftaranPesertaBerkas::class);
    }

    public function asesmen()
    {
        return $this->hasOne(Asesmen::class);
    }

    public function pertemuanPembelajaranPeserta()
    {
        return $this->hasMany(PertemuanPembelajaranPeserta::class, 'pendaftaran_peserta_id');
    }
}
