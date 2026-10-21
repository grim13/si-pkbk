<?php

use App\Models\JenisDisabilitas;
use App\Models\PendaftaranPeserta;
use App\Models\User;
use App\Models\Wali;
use Database\Seeders\JenisDisabilitasSeeder;

test('seeder mengisi data tuna wicara, tuna rungu, dan tuna netra tanpa duplikasi saat dijalankan ulang', function () {
    $this->seed(JenisDisabilitasSeeder::class);
    $this->seed(JenisDisabilitasSeeder::class);

    $names = JenisDisabilitas::query()->orderBy('id')->pluck('nama')->all();

    expect($names)->toBe([
        'Tuna Wicara',
        'Tuna Rungu',
        'Tuna Netra',
    ]);
});

test('pendaftaran peserta berelasi dengan jenis disabilitas dan mengatur null saat jenis disabilitas dihapus', function () {
    $jenisDisabilitas = JenisDisabilitas::factory()->tunaNetra()->create();
    $user = User::factory()->create();
    $wali = Wali::create([
        'user_id' => $user->id,
        'nik' => '1234567890123456',
        'alamat' => 'Jl. Contoh No. 1',
        'tempat_lahir' => 'Banjarmasin',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $pendaftaran = PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'jenis_disabilitas_id' => $jenisDisabilitas->id,
        'nik' => '6371010000000001',
        'no_pendaftaran' => '01',
        'nama' => 'Klien Disabilitas',
        'tempat_lahir' => 'Banjarbaru',
        'tanggal_lahir' => '2010-05-10',
        'alamat_asal' => 'Jl. Klien No. 1',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Massage',
        'status_pendaftaran' => 'draft',
    ]);

    expect($pendaftaran->jenisDisabilitas->is($jenisDisabilitas))->toBeTrue();
    expect($jenisDisabilitas->pendaftaranPeserta)->toHaveCount(1);

    $jenisDisabilitas->delete();
    $pendaftaran->refresh();

    expect($pendaftaran->jenis_disabilitas_id)->toBeNull();
});
