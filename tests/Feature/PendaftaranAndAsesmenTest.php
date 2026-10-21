<?php

use App\Models\JenisDisabilitas;
use App\Models\PendaftaranPeserta;
use App\Models\User;
use App\Models\Wali;
use Database\Seeders\RolePermissionTableSeeder;

beforeEach(function () {
    $this->seed(RolePermissionTableSeeder::class);
});

test('satu wali dapat mendaftarkan lebih dari 1 klien dengan nomor urut 01, 02, dan seterusnya', function () {
    $user = User::factory()->create();
    $user->assignRole('wali');

    $wali = Wali::create([
        'user_id' => $user->id,
        'nik' => '1234567890123456',
        'alamat' => 'Jl. Contoh No. 1',
        'tempat_lahir' => 'Banjarmasin',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $this->actingAs($user);

    // Klien pertama
    $response1 = $this->post(route('pendaftaran.store-draft'), [
        'nik' => '6371010000000001',
        'nama' => 'Klien Pertama',
        'tempat_lahir' => 'Banjarbaru',
        'tanggal_lahir' => now()->subYears(15)->format('Y-m-d'),
        'alamat_asal' => 'Jl. Merdeka 1',
        'pendidikan' => 'SD',
        'keterampilan_yang_diminati' => 'Pijat',
    ]);

    $klien1 = PendaftaranPeserta::where('nik', '6371010000000001')->first();
    expect($klien1)->not->toBeNull()
        ->and($klien1->no_pendaftaran)->toBe('01')
        ->and($klien1->wali_id)->toBe($wali->id);

    $response1->assertRedirect(route('pendaftaran.index', ['pendaftaran_id' => $klien1->id]));

    // Ubah status klien pertama ke verifikasi_berkas (simulasi sudah disubmit)
    $klien1->update(['status_pendaftaran' => 'verifikasi_berkas']);

    // Klien kedua oleh wali yang sama
    $response2 = $this->post(route('pendaftaran.store-draft'), [
        'nik' => '6371010000000002',
        'nama' => 'Klien Kedua',
        'tempat_lahir' => 'Martapura',
        'tanggal_lahir' => now()->subYears(18)->format('Y-m-d'),
        'alamat_asal' => 'Jl. Merdeka 2',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Musik',
    ]);

    $klien2 = PendaftaranPeserta::where('nik', '6371010000000002')->first();
    expect($klien2)->not->toBeNull()
        ->and($klien2->no_pendaftaran)->toBe('02')
        ->and($klien2->wali_id)->toBe($wali->id)
        ->and(PendaftaranPeserta::where('wali_id', $wali->id)->count())->toBe(2);

    $response2->assertRedirect(route('pendaftaran.index', ['pendaftaran_id' => $klien2->id]));
});

test('form asesmen mewajibkan data diri dan mengizinkan bagian kuesioner/asesmen kosong (opsional)', function () {
    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $waliUser = User::factory()->create(['name' => 'Nama Wali']);
    $waliUser->assignRole('wali');

    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1234567890123456',
        'alamat' => 'Jl. Contoh No. 1',
        'tempat_lahir' => 'Banjarmasin',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $pendaftaran = PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'nik' => '6371010000000001',
        'no_pendaftaran' => '01',
        'nama' => 'Klien Asesmen',
        'tempat_lahir' => 'Banjarbaru',
        'tanggal_lahir' => '2010-05-10',
        'alamat_asal' => 'Jl. Klien No. 1',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Massage',
        'status_pendaftaran' => 'asesmen',
    ]);

    $this->actingAs($pekerjaSosial);

    // Gagal jika data diri wajib (misal jenis_kelamin / agama) kosong
    $invalidResponse = $this->post(route('pekerja-sosial.asesmen.store', $pendaftaran->id), [
        'status' => 'selesai',
        'pendaftaran' => [
            'nama' => '',
            'nik' => '',
            'jenis_kelamin' => '',
            'tempat_lahir' => '',
            'tanggal_lahir' => '',
            'agama' => '',
            'pendidikan' => '',
            'alamat_asal' => '',
        ],
        'wali' => [
            'nama' => '',
            'nomor_hp' => '',
            'tempat_lahir' => '',
            'tanggal_lahir' => '',
            'alamat' => '',
        ],
    ]);

    $invalidResponse->assertSessionHasErrors([
        'pendaftaran.nama',
        'pendaftaran.nik',
        'pendaftaran.jenis_kelamin',
        'pendaftaran.tempat_lahir',
        'pendaftaran.tanggal_lahir',
        'pendaftaran.agama',
        'pendaftaran.pendidikan',
        'pendaftaran.alamat_asal',
        'pendaftaran.jenis_disabilitas_id',
        'wali.nama',
        'wali.nomor_hp',
        'wali.tempat_lahir',
        'wali.tanggal_lahir',
        'wali.alamat',
    ]);

    $jenisDisabilitas = JenisDisabilitas::factory()->tunaRungu()->create();

    // Berhasil jika data diri wajib (termasuk jenis_disabilitas_id) diisi sedangkan seluruh bagian kuesioner/asesmen lainnya dikosongkan
    $validResponse = $this->post(route('pekerja-sosial.asesmen.store', $pendaftaran->id), [
        'status' => 'selesai',
        'pendaftaran' => [
            'nama' => 'Klien Asesmen Updated',
            'nik' => '6371010000000001',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Banjarbaru',
            'tanggal_lahir' => '2010-05-10',
            'agama' => 'Islam',
            'pendidikan' => 'SMP',
            'alamat_asal' => 'Jl. Klien No. 1',
            'jenis_disabilitas_id' => $jenisDisabilitas->id,
            'tinggi_badan' => '',
            'berat_badan' => '',
        ],
        'wali' => [
            'nama' => 'Nama Wali',
            'nomor_hp' => '081234567890',
            'tempat_lahir' => 'Banjarmasin',
            'tanggal_lahir' => '1980-01-01',
            'alamat' => 'Jl. Contoh No. 1',
            'penghasilan_perbulan' => '',
            'tanggungan_keluarga' => '',
        ],
        'anggota_keluarga' => [
            ['nama' => '', 'status' => '', 'tanggal_lahir' => '', 'pendidikan' => '', 'pekerjaan' => ''],
        ],
        'jawaban' => [],
    ]);

    $validResponse->assertSessionHasNoErrors();
    $pendaftaran->refresh();

    expect($pendaftaran->status_pendaftaran)->toBe('selesai_asesmen')
        ->and($pendaftaran->jenis_kelamin)->toBe('Laki-laki')
        ->and($pendaftaran->agama)->toBe('Islam')
        ->and($pendaftaran->jenis_disabilitas_id)->toBe($jenisDisabilitas->id)
        ->and($pendaftaran->asesmen)->not->toBeNull()
        ->and($pendaftaran->asesmen->tujuan_bimbingan_sekolah)->toBeFalse();
});

test('kepala seksi mencatat tanggal_mulai_diterima saat menyetujui peserta dan mengosongkannya saat ditolak', function () {
    $kepalaSeksi = User::factory()->create();
    $kepalaSeksi->assignRole('kepala_seksi');

    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali');

    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1234567890123456',
        'alamat' => 'Jl. Contoh No. 1',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $pendaftaran = PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'nik' => '6371010000000001',
        'no_pendaftaran' => '01',
        'nama' => 'Klien Kelulusan',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '2010-05-10',
        'alamat_asal' => 'Jl. Klien No. 1',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Massage',
        'status_pendaftaran' => 'selesai_asesmen',
    ]);

    $this->actingAs($kepalaSeksi);

    $this->travelTo('2026-10-21 10:30:00');

    $this->post(route('kepala-seksi.kelulusan.update', $pendaftaran->id), [
        'keputusan' => 'diterima',
    ])->assertRedirect(route('kepala-seksi.kelulusan.index'));

    $pendaftaran->refresh();
    expect($pendaftaran->status_pendaftaran)->toBe('diterima')
        ->and($pendaftaran->tanggal_mulai_diterima)->not->toBeNull()
        ->and($pendaftaran->tanggal_mulai_diterima->format('Y-m-d H:i:s'))->toBe('2026-10-21 10:30:00');

    // Jika keputusan diubah menjadi ditolak, tanggal_mulai_diterima dikosongkan kembali
    $this->post(route('kepala-seksi.kelulusan.update', $pendaftaran->id), [
        'keputusan' => 'ditolak',
    ])->assertRedirect(route('kepala-seksi.kelulusan.index'));

    $pendaftaran->refresh();
    expect($pendaftaran->status_pendaftaran)->toBe('ditolak')
        ->and($pendaftaran->tanggal_mulai_diterima)->toBeNull();
});
