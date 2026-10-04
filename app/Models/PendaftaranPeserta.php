<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendaftaranPeserta extends Model
{
    protected $table = 'pendaftaran_peserta';

    protected $fillable = [
        'wali_id',
        'nik',
        'no_pendaftaran',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_asal',
        'pendidikan',
        'keterampilan_yang_diminati',
        'status_pendaftaran',
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
    ];

    public function wali()
    {
        return $this->belongsTo(Wali::class);
    }

    public function berkas()
    {
        return $this->hasMany(PendaftaranPesertaBerkas::class);
    }

    public function asesmen()
    {
        return $this->hasOne(Asesmen::class);
    }
}
