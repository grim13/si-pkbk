<?php

use App\Models\Jadwal;
use App\Models\JenisDisabilitas;
use App\Models\MataPelajaran;
use App\Models\PendaftaranPeserta;
use App\Models\PertemuanPembelajaran;
use App\Models\User;
use App\Models\Wali;
use Database\Seeders\RolePermissionTableSeeder;

beforeEach(function () {
    $this->seed(RolePermissionTableSeeder::class);
});

function createAcceptedPeserta(Wali $wali, JenisDisabilitas $disabilitas, string $nama, string $nik, string $tanggalDiterima, string $status = 'diterima'): PendaftaranPeserta
{
    return PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'jenis_disabilitas_id' => $disabilitas->id,
        'nik' => $nik,
        'no_pendaftaran' => substr($nik, -2),
        'nama' => $nama,
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '2010-01-01',
        'alamat_asal' => 'Jl. Merdeka',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Komputer',
        'status_pendaftaran' => $status,
        'tanggal_mulai_diterima' => $status === 'diterima' ? $tanggalDiterima : null,
    ]);
}

test('peserta pada jadwal difilter berdasarkan status diterima, jenis disabilitas mata pelajaran, dan tanggal pembelajaran terhadap tanggal mulai diterima', function () {
    $instruktur = User::factory()->create();
    $instruktur->assignRole('instruktur');

    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali');
    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1671010000000001',
        'alamat' => 'Palembang',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();
    $tunaRungu = JenisDisabilitas::factory()->tunaRungu()->create();

    $mapelNetra = MataPelajaran::factory()->create(['nama' => 'Baca Tulis Braille']);
    $mapelNetra->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwal2026 = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelNetra->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelNetra->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2025,
        'hari' => 'Selasa',
    ]);

    // 1. Peserta Tuna Netra diterima tanggal 2026-03-01 (Memenuhi syarat untuk pembelajaran 2026-03-10)
    $pesertaValid = createAcceptedPeserta($wali, $tunaNetra, 'Peserta Netra Valid', '1671010000000011', '2026-03-01 09:00:00');

    // 2. Peserta Tuna Netra diterima tanggal 2026-03-20 (Tanggal pembelajaran 2026-03-10 lebih kecil dari tanggal mulai diterima -> Tidak muncul)
    $pesertaBelumDiterima = createAcceptedPeserta($wali, $tunaNetra, 'Peserta Netra Baru', '1671010000000012', '2026-03-20 09:00:00');

    // 3. Peserta Tuna Rungu diterima tanggal 2026-03-01 (Beda jenis disabilitas -> Tidak muncul)
    $pesertaBedaDisabilitas = createAcceptedPeserta($wali, $tunaRungu, 'Peserta Rungu', '1671010000000013', '2026-03-01 09:00:00');

    // 4. Peserta Tuna Netra tapi status masih selesai_asesmen (Belum diterima -> Tidak muncul)
    $pesertaBelumLulus = createAcceptedPeserta($wali, $tunaNetra, 'Peserta Belum Lulus', '1671010000000014', '2026-03-01 09:00:00', 'selesai_asesmen');

    $this->actingAs($instruktur);

    // Filter jadwal per tahun 2026
    $indexResponse = $this->get(route('instruktur.pembelajaran.index', ['tahun' => 2026]));
    $indexResponse->assertOk();
    $jadwalProps = $indexResponse->viewData('page')['props']['jadwalList'];
    expect($jadwalProps)->toHaveCount(1)
        ->and($jadwalProps[0]['id'])->toBe($jadwal2026->id);

    // Buka lembar pertemuan pada tanggal 2026-03-10
    $showResponse = $this->get(route('instruktur.pembelajaran.show', [
        'jadwal' => $jadwal2026->id,
        'tanggal' => '2026-03-10',
    ]));
    $showResponse->assertOk();

    $pesertaProps = collect($showResponse->viewData('page')['props']['pesertaList']);
    expect($pesertaProps)->toHaveCount(1)
        ->and($pesertaProps->first()['id'])->toBe($pesertaValid->id)
        ->and($pesertaProps->pluck('id'))->not->toContain(
            $pesertaBelumDiterima->id,
            $pesertaBedaDisabilitas->id,
            $pesertaBelumLulus->id
        );
});

test('instruktur mencatat presensi dan nilai kualitatif, mewajibkan keterangan jika nilai kurang, mengunci saat pengajuan/acc, dan pekerja sosial melakukan approval', function () {
    $instruktur = User::factory()->create();
    $instruktur->assignRole('instruktur');

    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali');
    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1671010000000001',
        'alamat' => 'Palembang',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();
    $mapel = MataPelajaran::factory()->create(['nama' => 'Orientasi dan Mobilitas (OM)']);
    $mapel->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $peserta1 = createAcceptedPeserta($wali, $tunaNetra, 'Budi Netra', '1671010000000021', '2026-02-01 08:00:00');
    $peserta2 = createAcceptedPeserta($wali, $tunaNetra, 'Siti Netra', '1671010000000022', '2026-02-01 08:00:00');

    $this->actingAs($instruktur);

    // 1. Gagal jika nilai 'kurang' tetapi keterangan kosong
    $invalidResponse = $this->post(route('instruktur.pembelajaran.store', $jadwal->id), [
        'tanggal' => '2026-03-16',
        'status' => 'draft',
        'keterangan' => 'Pertemuan 1',
        'peserta' => [
            [
                'pendaftaran_peserta_id' => $peserta1->id,
                'presensi' => 'hadir',
                'nilai' => 'sangat_baik',
                'keterangan' => '', // Opsional untuk selain kurang
            ],
            [
                'pendaftaran_peserta_id' => $peserta2->id,
                'presensi' => 'izin',
                'nilai' => 'kurang',
                'keterangan' => '', // Wajib diisi karena nilai kurang
            ],
        ],
    ]);

    $invalidResponse->assertSessionHasErrors(['peserta.1.keterangan']);

    // 2. Berhasil simpan draft jika keterangan nilai 'kurang' diisi
    $draftResponse = $this->post(route('instruktur.pembelajaran.store', $jadwal->id), [
        'tanggal' => '2026-03-16',
        'status' => 'draft',
        'keterangan' => 'Pertemuan 1 Latihan Tongkat',
        'peserta' => [
            [
                'pendaftaran_peserta_id' => $peserta1->id,
                'presensi' => 'hadir',
                'nilai' => 'sangat_baik',
                'keterangan' => '',
            ],
            [
                'pendaftaran_peserta_id' => $peserta2->id,
                'presensi' => 'izin',
                'nilai' => 'kurang',
                'keterangan' => 'Perlu bimbingan ulang karena tidak hadir penuh',
            ],
        ],
    ]);

    $draftResponse->assertSessionHasNoErrors();

    $pertemuan = PertemuanPembelajaran::where('jadwal_id', $jadwal->id)->first();
    expect($pertemuan)->not->toBeNull()
        ->and($pertemuan->status)->toBe('draft')
        ->and($pertemuan->pesertaDetail)->toHaveCount(2);

    // 3. Instruktur melakukan pengajuan
    $ajukanResponse = $this->post(route('instruktur.pembelajaran.ajukan', $pertemuan->id));
    $ajukanResponse->assertSessionHasNoErrors();

    $pertemuan->refresh();
    expect($pertemuan->status)->toBe('pengajuan');

    // 4. Di status pengajuan, instruktur TIDAK DAPAT mengubah presensi dan nilai
    $lockedAttempt = $this->post(route('instruktur.pembelajaran.store', $jadwal->id), [
        'tanggal' => '2026-03-16',
        'status' => 'draft',
        'keterangan' => 'Coba ubah saat pengajuan',
        'peserta' => [
            [
                'pendaftaran_peserta_id' => $peserta1->id,
                'presensi' => 'absen',
                'nilai' => 'cukup',
                'keterangan' => '',
            ],
            [
                'pendaftaran_peserta_id' => $peserta2->id,
                'presensi' => 'hadir',
                'nilai' => 'baik',
                'keterangan' => '',
            ],
        ],
    ]);

    $lockedAttempt->assertSessionHasErrors(['status']);
    expect($pertemuan->pesertaDetail()->where('pendaftaran_peserta_id', $peserta1->id)->first()->presensi)->toBe('hadir');

    // 5. Pekerja Sosial meminta perbaikan beserta keterangannya
    $this->actingAs($pekerjaSosial);

    $perbaikanResponse = $this->post(route('pekerja-sosial.pembelajaran.update', $pertemuan->id), [
        'status' => 'perbaikan',
        'keterangan' => 'Tolong perjelas catatan perkembangan peserta kedua.',
    ]);
    $perbaikanResponse->assertRedirect(route('pekerja-sosial.pembelajaran.index', ['tahun' => 2026]));

    $pertemuan->refresh();
    expect($pertemuan->status)->toBe('perbaikan')
        ->and($pertemuan->catatan_pekerja_sosial)->toBe('Tolong perjelas catatan perkembangan peserta kedua.');

    // 6. Karena status sudah 'perbaikan', instruktur DAPAT memperbaiki dan mengajukan kembali
    $this->actingAs($instruktur);

    $resubmitResponse = $this->post(route('instruktur.pembelajaran.store', $jadwal->id), [
        'tanggal' => '2026-03-16',
        'status' => 'pengajuan',
        'keterangan' => 'Pertemuan 1 Latihan Tongkat (Telah Diperbaiki)',
        'peserta' => [
            [
                'pendaftaran_peserta_id' => $peserta1->id,
                'presensi' => 'hadir',
                'nilai' => 'sangat_baik',
                'keterangan' => 'Sangat mandiri',
            ],
            [
                'pendaftaran_peserta_id' => $peserta2->id,
                'presensi' => 'hadir',
                'nilai' => 'baik',
                'keterangan' => 'Sudah mengikuti sesi susulan dengan baik',
            ],
        ],
    ]);
    $resubmitResponse->assertSessionHasNoErrors();

    $pertemuan->refresh();
    expect($pertemuan->status)->toBe('pengajuan');

    // 7. Pekerja Sosial melakukan ACC pertemuan
    $this->actingAs($pekerjaSosial);

    $accResponse = $this->post(route('pekerja-sosial.pembelajaran.update', $pertemuan->id), [
        'status' => 'acc',
        'keterangan' => 'Laporan pertemuan disetujui.',
    ]);
    $accResponse->assertRedirect(route('pekerja-sosial.pembelajaran.index', ['tahun' => 2026]));

    $pertemuan->refresh();
    expect($pertemuan->status)->toBe('acc')
        ->and($pertemuan->approved_at)->not->toBeNull();

    // 8. Di status ACC, instruktur TIDAK DAPAT mengubah presensi dan nilai
    $this->actingAs($instruktur);

    $accLockedAttempt = $this->post(route('instruktur.pembelajaran.store', $jadwal->id), [
        'tanggal' => '2026-03-16',
        'status' => 'draft',
        'peserta' => [
            [
                'pendaftaran_peserta_id' => $peserta1->id,
                'presensi' => 'absen',
                'nilai' => 'cukup',
                'keterangan' => '',
            ],
            [
                'pendaftaran_peserta_id' => $peserta2->id,
                'presensi' => 'absen',
                'nilai' => 'cukup',
                'keterangan' => '',
            ],
        ],
    ]);

    $accLockedAttempt->assertSessionHasErrors(['status']);
});
