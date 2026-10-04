<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asesmen extends Model
{
    protected $table = 'asesmen';

    protected $fillable = [
        'pendaftaran_peserta_id',
        'pekerja_sosial_id',
        'status',
        'tanggal_asesmen',
        // Latar Belakang Pendidikan – Formal
        'pendidikan_tk',
        'pendidikan_sd',
        'pendidikan_smp',
        'pendidikan_sma',
        'pendidikan_pt',
        // Latar Belakang Pendidikan – Non-Formal
        'kursus_nama',
        'kursus_diberikan_oleh',
        'kursus_tempat',
        'kursus_lama',
        // Sikap Emosional / Mental
        'sikap_klien',
        'sikap_keluarga',
        'sikap_masyarakat',
        'keluhan_hambatan',
        // Lain-lain
        'tujuan_bimbingan_keterampilan',
        'tujuan_bimbingan_sekolah',
        'keterampilan_praktis_produktif',
        'keterampilan_massage_pijat',
        'info_dokter_rs',
        'info_petugas_sosial_kec',
        'info_dinas_sosial',
        'info_psm',
        'info_teman',
        // Kesimpulan
        'kesimpulan_latar_belakang',
        'kesimpulan_pengaruh_kecacatan',
        'kesimpulan_keinginan_harapan',
        'kesimpulan_bentuk_bantuan',
    ];

    protected $casts = [
        'tanggal_asesmen' => 'date',
        'tujuan_bimbingan_keterampilan' => 'boolean',
        'tujuan_bimbingan_sekolah' => 'boolean',
        'keterampilan_praktis_produktif' => 'boolean',
        'keterampilan_massage_pijat' => 'boolean',
        'info_dokter_rs' => 'boolean',
        'info_petugas_sosial_kec' => 'boolean',
        'info_dinas_sosial' => 'boolean',
        'info_psm' => 'boolean',
        'info_teman' => 'boolean',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranPeserta::class, 'pendaftaran_peserta_id');
    }

    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pekerja_sosial_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(AsesmenJawaban::class);
    }
}
