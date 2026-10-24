<?php

use App\Models\Galeri;
use App\Models\Jadwal;
use App\Models\JenisDisabilitas;
use App\Models\MataPelajaran;
use App\Models\PendaftaranPeserta;
use App\Models\PertemuanPembelajaran;
use App\Models\User;
use App\Models\Wali;
use Database\Seeders\RolePermissionTableSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard menyajikan data sesuai masing-masing role (admin, wali, pekerja sosial, instruktur, kepala seksi)', function () {
    $this->seed(RolePermissionTableSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $peksos = User::factory()->create();
    $peksos->assignRole('pekerja_sosial');

    $instruktur = User::factory()->create();
    $instruktur->assignRole('instruktur');

    $kepalaSeksi = User::factory()->create();
    $kepalaSeksi->assignRole('kepala_seksi');

    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali');
    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1671010000009001',
        'alamat' => 'Palembang',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1985-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $disabilitas = JenisDisabilitas::factory()->tunaNetra()->create();
    $peserta = PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'jenis_disabilitas_id' => $disabilitas->id,
        'nik' => '1671010000009002',
        'no_pendaftaran' => '99',
        'nama' => 'Peserta Dashboard',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '2012-05-10',
        'alamat_asal' => 'Jl. Merdeka',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Komputer',
        'status_pendaftaran' => 'diterima',
        'tanggal_mulai_diterima' => '2026-02-01 08:00:00',
    ]);

    $mapel = MataPelajaran::factory()->create(['nama' => 'Braille Dasar']);
    $mapel->jenisDisabilitas()->sync([$disabilitas->id]);

    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $pertemuanAcc = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'pekerja_sosial_id' => $peksos->id,
        'tanggal' => '2026-03-16',
        'status' => 'acc',
        'keterangan' => 'Pertemuan 1',
        'approved_at' => now(),
    ]);

    $pertemuanAcc->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $peserta->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
        'keterangan' => 'Hadir dan baik',
    ]);

    Galeri::create([
        'pertemuan_pembelajaran_id' => $pertemuanAcc->id,
        'judul' => 'Galeri Pertemuan 1',
        'keterangan' => 'Foto kegiatan',
        'created_by' => $admin->id,
    ]);

    // 1. Admin Dashboard
    $adminRes = $this->actingAs($admin)->get(route('dashboard'));
    $adminRes->assertOk();
    expect($adminRes->viewData('page')['props']['role'])->toBe('administrator')
        ->and($adminRes->viewData('page')['props']['dashboardData']['stats']['peserta_diterima'])->toBe(1)
        ->and($adminRes->viewData('page')['props']['dashboardData']['stats']['total_galeri'])->toBe(1);

    // 2. Wali Dashboard
    $waliRes = $this->actingAs($waliUser)->get(route('dashboard'));
    $waliRes->assertOk();
    expect($waliRes->viewData('page')['props']['role'])->toBe('wali')
        ->and($waliRes->viewData('page')['props']['dashboardData']['stats']['total_peserta'])->toBe(1)
        ->and($waliRes->viewData('page')['props']['dashboardData']['stats']['total_hadir'])->toBe(1)
        ->and($waliRes->viewData('page')['props']['dashboardData']['stats']['total_galeri'])->toBe(1);

    // 3. Pekerja Sosial Dashboard
    $peksosRes = $this->actingAs($peksos)->get(route('dashboard'));
    $peksosRes->assertOk();
    expect($peksosRes->viewData('page')['props']['role'])->toBe('pekerja_sosial')
        ->and($peksosRes->viewData('page')['props']['dashboardData']['stats']['pertemuan_acc'])->toBe(1);

    // 4. Instruktur Dashboard
    $instrukturRes = $this->actingAs($instruktur)->get(route('dashboard'));
    $instrukturRes->assertOk();
    expect($instrukturRes->viewData('page')['props']['role'])->toBe('instruktur')
        ->and($instrukturRes->viewData('page')['props']['dashboardData']['stats']['total_jadwal'])->toBe(1)
        ->and($instrukturRes->viewData('page')['props']['dashboardData']['stats']['disetujui_acc'])->toBe(1);

    // 5. Kepala Seksi Dashboard
    $kasiRes = $this->actingAs($kepalaSeksi)->get(route('dashboard'));
    $kasiRes->assertOk();
    expect($kasiRes->viewData('page')['props']['role'])->toBe('kepala_seksi')
        ->and($kasiRes->viewData('page')['props']['dashboardData']['stats']['peserta_diterima'])->toBe(1)
        ->and($kasiRes->viewData('page')['props']['dashboardData']['stats']['total_pertemuan_acc'])->toBe(1);
});
