<?php

use App\Models\Jadwal;
use App\Models\JenisDisabilitas;
use App\Models\MataPelajaran;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\JadwalSeeder;
use Database\Seeders\JenisDisabilitasSeeder;
use Database\Seeders\MataPelajaranSeeder;
use Database\Seeders\RolePermissionTableSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RolePermissionTableSeeder::class,
        JenisDisabilitasSeeder::class,
    ]);
});

test('seeder membuat role instruktur dan memberikan permission mata_pelajaran.manage serta jadwal.manage ke administrator', function () {
    $instrukturRole = Role::where('name', 'instruktur')->first();
    $adminRole = Role::where('name', 'administrator')->first();

    expect($instrukturRole)->not->toBeNull();
    expect($adminRole->hasPermissionTo('mata_pelajaran.manage'))->toBeTrue();
    expect($adminRole->hasPermissionTo('jadwal.manage'))->toBeTrue();
});

test('administrator dapat mengelola CRUD mata pelajaran dengan relasi many-to-many jenis disabilitas', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $disabilitas = JenisDisabilitas::orderBy('id')->get();

    $this->actingAs($admin);

    // Berhasil membuat mata pelajaran
    $storeResponse = $this->post(route('mata-pelajaran.store'), [
        'nama' => 'Pijat Shiatsu',
        'keterangan' => 'Materi pijat dasar',
        'jenis_disabilitas_ids' => [$disabilitas[0]->id, $disabilitas[2]->id],
    ]);
    $storeResponse->assertSessionHasNoErrors();

    $mapel = MataPelajaran::where('nama', 'Pijat Shiatsu')->first();
    expect($mapel)->not->toBeNull()
        ->and($mapel->jenisDisabilitas->pluck('id')->all())->toEqualCanonicalizing([
            $disabilitas[0]->id,
            $disabilitas[2]->id,
        ]);

    // Update mata pelajaran
    $updateResponse = $this->put(route('mata-pelajaran.update', $mapel->id), [
        'nama' => 'Pijat Refleksi Lanjutan',
        'keterangan' => 'Materi pijat lanjutan',
        'jenis_disabilitas_ids' => [$disabilitas[1]->id],
    ]);
    $updateResponse->assertSessionHasNoErrors();

    $mapel->refresh();
    expect($mapel->nama)->toBe('Pijat Refleksi Lanjutan')
        ->and($mapel->jenisDisabilitas->pluck('id')->all())->toBe([$disabilitas[1]->id]);

    // Hapus mata pelajaran
    $deleteResponse = $this->delete(route('mata-pelajaran.destroy', $mapel->id));
    $deleteResponse->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('mata_pelajaran', ['id' => $mapel->id]);
});

test('administrator dapat mengelola CRUD jadwal mingguan dengan relasi instruktur dan memfilter berdasarkan tahun', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $instruktur = User::factory()->create(['name' => 'Budi Instruktur']);
    $instruktur->assignRole('instruktur');

    $bukanInstruktur = User::factory()->create(['name' => 'Wali Biasa']);
    $bukanInstruktur->assignRole('wali');

    $mapel = MataPelajaran::factory()->create([
        'nama' => 'Bahasa Isyarat',
    ]);

    $this->actingAs($admin);

    // Gagal jika memilih user yang bukan instruktur pada jadwal
    $invalidInstrukturResponse = $this->post(route('jadwal.store'), [
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $bukanInstruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'ruangan' => 'Ruang A',
    ]);
    $invalidInstrukturResponse->assertSessionHasErrors(['instruktur_id']);

    // Gagal jika jam selesai lebih awal dari jam mulai
    $invalidTimeResponse = $this->post(route('jadwal.store'), [
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
        'jam_mulai' => '10:00',
        'jam_selesai' => '08:00',
        'ruangan' => 'Ruang A',
    ]);
    $invalidTimeResponse->assertSessionHasErrors(['jam_selesai']);

    // Buat jadwal tahun 2026 dan 2027
    $this->post(route('jadwal.store'), [
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'ruangan' => 'Ruang A',
        'keterangan' => 'Sesi pagi',
    ])->assertSessionHasNoErrors();

    $this->post(route('jadwal.store'), [
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2027,
        'hari' => 'Rabu',
        'jam_mulai' => '09:00',
        'jam_selesai' => '11:00',
        'ruangan' => 'Ruang B',
    ])->assertSessionHasNoErrors();

    expect(Jadwal::where('tahun', 2026)->count())->toBe(1)
        ->and(Jadwal::where('tahun', 2027)->count())->toBe(1);

    // Filter jadwal per tahun
    $index2026 = $this->get(route('jadwal.index', ['tahun' => 2026]));
    $index2026->assertOk();
    expect($index2026->viewData('page')['props']['jadwalList'])->toHaveCount(1)
        ->and($index2026->viewData('page')['props']['jadwalList'][0]['hari'])->toBe('Senin')
        ->and($index2026->viewData('page')['props']['jadwalList'][0]['instruktur']['id'])->toBe($instruktur->id);

    // Update jadwal
    $jadwal2026 = Jadwal::where('tahun', 2026)->first();
    $this->put(route('jadwal.update', $jadwal2026->id), [
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Selasa',
        'jam_mulai' => '13:00',
        'jam_selesai' => '15:00',
        'ruangan' => 'Ruang Praktik 2',
        'keterangan' => 'Sesi siang',
    ])->assertSessionHasNoErrors();

    $jadwal2026->refresh();
    expect($jadwal2026->hari)->toBe('Selasa')
        ->and($jadwal2026->ruangan)->toBe('Ruang Praktik 2');

    // Hapus jadwal
    $this->delete(route('jadwal.destroy', $jadwal2026->id))->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('jadwal', ['id' => $jadwal2026->id]);
});

test('user tanpa permission mata_pelajaran.manage dan jadwal.manage ditolak mengakses halaman terkait', function () {
    $wali = User::factory()->create();
    $wali->assignRole('wali');

    $this->actingAs($wali);

    $this->get(route('mata-pelajaran.index'))->assertForbidden();
    $this->get(route('jadwal.index'))->assertForbidden();
    $this->get(route('jadwal.cetak'))->assertForbidden();
});

test('seeder instruktur, mata pelajaran, dan jadwal mengisi data tahun 2026 serta halaman cetak dapat diakses', function () {
    $this->seed([
        AdminUserSeeder::class,
        MataPelajaranSeeder::class,
        JadwalSeeder::class,
    ]);

    $instruktur = User::where('email', 'instruktur@sipkbk.com')->first();
    expect($instruktur)->not->toBeNull()
        ->and($instruktur->hasRole('instruktur'))->toBeTrue();

    expect(MataPelajaran::count())->toBe(9)
        ->and(Jadwal::where('tahun', 2026)->count())->toBe(12)
        ->and(Jadwal::where('instruktur_id', $instruktur->id)->count())->toBe(12);

    $admin = User::where('email', 'admin@sipkbk.com')->first();
    $this->actingAs($admin);

    $cetakResponse = $this->get(route('jadwal.cetak', ['tahun' => 2026]));
    $cetakResponse->assertOk();
    expect($cetakResponse->viewData('page')['component'])->toBe('Jadwal/Cetak')
        ->and($cetakResponse->viewData('page')['props']['tahun'])->toBe(2026)
        ->and($cetakResponse->viewData('page')['props']['jadwalList'])->toHaveCount(12);
});
