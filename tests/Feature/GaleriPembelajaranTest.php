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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionTableSeeder::class);
});

function fakePhoto(string $name = 'foto.png'): UploadedFile
{
    // 1x1 transparent PNG bytes (works without GD extension)
    $pngBytes = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );

    return UploadedFile::fake()->createWithContent($name, $pngBytes);
}

function createWaliWithPeserta(
    string $waliName,
    string $waliNik,
    JenisDisabilitas $disabilitas,
    string $pesertaNama,
    string $pesertaNik,
    string $tanggalDiterima = '2026-02-01 08:00:00',
    string $statusPendaftaran = 'diterima',
): array {
    $waliUser = User::factory()->create(['name' => $waliName]);
    $waliUser->assignRole('wali');

    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => $waliNik,
        'alamat' => 'Palembang',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1980-01-01',
        'nomor_hp' => '081234567890',
    ]);

    $peserta = PendaftaranPeserta::create([
        'wali_id' => $wali->id,
        'jenis_disabilitas_id' => $disabilitas->id,
        'nik' => $pesertaNik,
        'no_pendaftaran' => substr($pesertaNik, -2),
        'nama' => $pesertaNama,
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '2012-05-10',
        'alamat_asal' => 'Jl. Merdeka',
        'pendidikan' => 'SMP',
        'keterampilan_yang_diminati' => 'Komputer',
        'status_pendaftaran' => $statusPendaftaran,
        'tanggal_mulai_diterima' => $statusPendaftaran === 'diterima' ? $tanggalDiterima : null,
    ]);

    return [$waliUser, $wali, $peserta];
}

test('admin dapat mengupload multiple foto dalam 1 galeri yang terhubung ke pertemuan pembelajaran instruktur di kelas, mengupdate, dan menghapus galeri', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $instruktur = User::factory()->create(['name' => 'Instruktur Kelas']);
    $instruktur->assignRole('instruktur');

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();
    $mapel = MataPelajaran::factory()->create(['nama' => 'Baca Tulis Braille']);
    $mapel->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $pertemuan = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-16',
        'status' => 'acc',
        'keterangan' => 'Pertemuan 1 Pengenalan Huruf Braille',
    ]);

    $this->actingAs($admin);

    // 1. Gagal jika tidak ada foto yang diupload
    $invalidResponse = $this->post(route('admin.galeri.store'), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Galeri Kosong',
        'fotos' => [],
    ]);
    $invalidResponse->assertSessionHasErrors(['fotos']);

    // 1b. Gagal jika file bukan gambar
    $nonImage = UploadedFile::fake()->createWithContent('dokumen.txt', 'Bukan file gambar');
    $invalidFileResponse = $this->post(route('admin.galeri.store'), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Galeri File Salah',
        'fotos' => [$nonImage],
    ]);
    $invalidFileResponse->assertSessionHasErrors(['fotos.0']);

    // 2. Berhasil upload multiple foto dalam 1 galeri
    $foto1 = fakePhoto('kegiatan-1.png');
    $foto2 = fakePhoto('kegiatan-2.png');
    $foto3 = fakePhoto('kegiatan-3.png');

    $storeResponse = $this->post(route('admin.galeri.store'), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Dokumentasi Praktik Braille Pertemuan 1',
        'keterangan' => 'Suasana belajar braille di kelas Senin pagi',
        'fotos' => [$foto1, $foto2, $foto3],
    ]);

    $storeResponse->assertSessionHasNoErrors();

    $galeri = Galeri::with('foto')->first();
    expect($galeri)->not->toBeNull()
        ->and($galeri->pertemuan_pembelajaran_id)->toBe($pertemuan->id)
        ->and($galeri->judul)->toBe('Dokumentasi Praktik Braille Pertemuan 1')
        ->and($galeri->foto)->toHaveCount(3);

    foreach ($galeri->foto as $foto) {
        Storage::disk('public')->assertExists($foto->file_path);
    }

    // 3. Update galeri: hapus 1 foto lama dan tambah 1 foto baru
    $removedFoto = $galeri->foto->first();
    $foto4 = fakePhoto('kegiatan-4.png');

    $updateResponse = $this->put(route('admin.galeri.update', $galeri->id), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Dokumentasi Praktik Braille Pertemuan 1 (Updated)',
        'keterangan' => 'Keterangan diperbarui',
        'hapus_foto_ids' => [$removedFoto->id],
        'fotos' => [$foto4],
    ]);

    $updateResponse->assertSessionHasNoErrors();

    $galeri->refresh();
    expect($galeri->judul)->toBe('Dokumentasi Praktik Braille Pertemuan 1 (Updated)')
        ->and($galeri->foto)->toHaveCount(3);

    Storage::disk('public')->assertMissing($removedFoto->file_path);

    // 3b. Gagal jika mencoba menghapus seluruh foto tanpa menyisakan minimal 1 foto
    $allCurrentFotoIds = $galeri->foto->pluck('id')->all();
    $emptyRemainingResponse = $this->put(route('admin.galeri.update', $galeri->id), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Coba Kosongkan Semua Foto',
        'hapus_foto_ids' => $allCurrentFotoIds,
    ]);
    $emptyRemainingResponse->assertSessionHasErrors(['fotos']);

    // 4. Hapus galeri menghapus seluruh file foto dari storage
    $remainingPaths = $galeri->foto->pluck('file_path')->all();

    $deleteResponse = $this->delete(route('admin.galeri.destroy', $galeri->id));
    $deleteResponse->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('galeri', ['id' => $galeri->id]);
    foreach ($remainingPaths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

test('wali yang pesertanya terdaftar di kelas dan hadir dapat melihat galeri sedangkan yang tidak hadir atau tidak terdaftar tidak dapat melihat galeri', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $instruktur = User::factory()->create(['name' => 'Instruktur OM']);
    $instruktur->assignRole('instruktur');

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();
    $tunaRungu = JenisDisabilitas::factory()->tunaRungu()->create();

    $mapelNetra = MataPelajaran::factory()->create(['nama' => 'Orientasi dan Mobilitas']);
    $mapelNetra->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwalNetra = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelNetra->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    // 1. Wali dengan peserta terdaftar di kelas Tuna Netra dan HADIR
    [$waliHadirUser, , $pesertaHadir] = createWaliWithPeserta(
        'Wali Peserta Hadir',
        '1671010000000101',
        $tunaNetra,
        'Peserta Hadir',
        '1671010000000201',
    );

    // 2. Wali dengan peserta terdaftar di kelas Tuna Netra tetapi IZIN (tidak hadir)
    [$waliIzinUser, , $pesertaIzin] = createWaliWithPeserta(
        'Wali Peserta Izin',
        '1671010000000102',
        $tunaNetra,
        'Peserta Izin',
        '1671010000000202',
    );

    // 3. Wali dengan peserta terdaftar di kelas Tuna Netra tetapi ABSEN (tidak hadir)
    [$waliAbsenUser, , $pesertaAbsen] = createWaliWithPeserta(
        'Wali Peserta Absen',
        '1671010000000103',
        $tunaNetra,
        'Peserta Absen',
        '1671010000000203',
    );

    // 4. Wali dengan peserta TIDAK TERDAFTAR di kelas tersebut (beda jenis disabilitas / tidak di kelas itu)
    [$waliTidakTerdaftarUser] = createWaliWithPeserta(
        'Wali Peserta Tidak Terdaftar',
        '1671010000000104',
        $tunaRungu,
        'Peserta Tuna Rungu',
        '1671010000000204',
    );

    // Instruktur mencatat pertemuan pembelajaran beserta presensi peserta
    $pertemuan = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalNetra->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-16',
        'status' => 'acc',
        'keterangan' => 'Latihan Tongkat di Halaman',
    ]);

    $pertemuan->pesertaDetail()->createMany([
        [
            'pendaftaran_peserta_id' => $pesertaHadir->id,
            'presensi' => 'hadir',
            'nilai' => 'sangat_baik',
            'keterangan' => 'Hadir dan aktif',
        ],
        [
            'pendaftaran_peserta_id' => $pesertaIzin->id,
            'presensi' => 'izin',
            'nilai' => 'baik',
            'keterangan' => 'Izin sakit',
        ],
        [
            'pendaftaran_peserta_id' => $pesertaAbsen->id,
            'presensi' => 'absen',
            'nilai' => 'cukup',
            'keterangan' => 'Tanpa keterangan',
        ],
    ]);

    // Admin mengupload galeri untuk pertemuan pembelajaran tersebut
    $this->actingAs($admin);
    $this->post(route('admin.galeri.store'), [
        'pertemuan_pembelajaran_id' => $pertemuan->id,
        'judul' => 'Galeri Latihan Tongkat Pertemuan 1',
        'keterangan' => 'Dokumentasi latihan tongkat',
        'fotos' => [
            fakePhoto('foto1.png'),
            fakePhoto('foto2.png'),
        ],
    ])->assertSessionHasNoErrors();

    $galeri = Galeri::firstOrFail();

    // A. Wali yang pesertanya terdaftar dan HADIR -> BISA melihat galeri di daftar maupun halaman detail
    $this->actingAs($waliHadirUser);
    $hadirIndex = $this->get(route('galeri.index'));
    $hadirIndex->assertOk();
    $hadirGaleriProps = $hadirIndex->viewData('page')['props']['galeriList'];
    expect($hadirGaleriProps)->toHaveCount(1)
        ->and($hadirGaleriProps[0]['id'])->toBe($galeri->id)
        ->and($hadirGaleriProps[0]['foto'])->toHaveCount(2);

    $this->get(route('galeri.show', $galeri->id))->assertOk();

    // B. Wali yang pesertanya terdaftar tapi IZIN (tidak hadir) -> TIDAK BISA melihat galeri
    $this->actingAs($waliIzinUser);
    $izinIndex = $this->get(route('galeri.index'));
    $izinIndex->assertOk();
    expect($izinIndex->viewData('page')['props']['galeriList'])->toHaveCount(0);

    $this->get(route('galeri.show', $galeri->id))->assertForbidden();

    // C. Wali yang pesertanya terdaftar tapi ABSEN (tidak hadir) -> TIDAK BISA melihat galeri
    $this->actingAs($waliAbsenUser);
    $absenIndex = $this->get(route('galeri.index'));
    $absenIndex->assertOk();
    expect($absenIndex->viewData('page')['props']['galeriList'])->toHaveCount(0);

    $this->get(route('galeri.show', $galeri->id))->assertForbidden();

    // D. Wali yang pesertanya TIDAK TERDAFTAR di kelas tersebut -> TIDAK BISA melihat galeri
    $this->actingAs($waliTidakTerdaftarUser);
    $tidakTerdaftarIndex = $this->get(route('galeri.index'));
    $tidakTerdaftarIndex->assertOk();
    expect($tidakTerdaftarIndex->viewData('page')['props']['galeriList'])->toHaveCount(0);

    $this->get(route('galeri.show', $galeri->id))->assertForbidden();
});

test('user selain admin ditolak mengakses manajemen galeri admin', function () {
    $wali = User::factory()->create();
    $wali->assignRole('wali');

    $this->actingAs($wali);

    $this->get(route('admin.galeri.index'))->assertForbidden();
    $this->post(route('admin.galeri.store'), [])->assertForbidden();
});

test('kepala seksi dapat melihat semua pembelajaran yang sudah di acc oleh pekerja sosial beserta detail peserta dan galeri foto jika ada', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $instruktur = User::factory()->create(['name' => 'Instruktur Kelas']);
    $instruktur->assignRole('instruktur');

    $pekerjaSosial = User::factory()->create(['name' => 'Pekerja Sosial Verifikator']);
    $pekerjaSosial->assignRole('pekerja_sosial');

    $kepalaSeksi = User::factory()->create(['name' => 'Kepala Seksi Rehabilitasi']);
    $kepalaSeksi->assignRole('kepala_seksi');

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();
    $mapel = MataPelajaran::factory()->create(['nama' => 'Komputer Bicara']);
    $mapel->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Selasa',
    ]);

    [, , $peserta1] = createWaliWithPeserta(
        'Wali Satu',
        '1671010000000301',
        $tunaNetra,
        'Peserta Satu',
        '1671010000000401',
    );

    // 1. Pertemuan yang sudah di-ACC oleh Pekerja Sosial
    $pertemuanAcc = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'pekerja_sosial_id' => $pekerjaSosial->id,
        'tanggal' => '2026-03-17',
        'status' => 'acc',
        'keterangan' => 'Praktik Mengetik dengan Screen Reader',
        'catatan_pekerja_sosial' => 'Laporan sudah lengkap dan sesuai.',
        'approved_at' => now(),
    ]);

    $pertemuanAcc->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $peserta1->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
        'keterangan' => 'Sangat lancar mengetik 10 jari',
    ]);

    // 2. Pertemuan yang masih berstatus pengajuan (belum di-ACC)
    $pertemuanBelumAcc = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-24',
        'status' => 'pengajuan',
        'keterangan' => 'Pertemuan 2 Belum ACC',
    ]);

    // Admin mengunggah galeri foto ke pertemuan yang sudah ACC
    $this->actingAs($admin);
    $this->post(route('admin.galeri.store'), [
        'pertemuan_pembelajaran_id' => $pertemuanAcc->id,
        'judul' => 'Dokumentasi Praktik Screen Reader',
        'keterangan' => 'Foto kegiatan di lab komputer',
        'fotos' => [
            fakePhoto('lab-1.png'),
            fakePhoto('lab-2.png'),
        ],
    ])->assertSessionHasNoErrors();

    // Kepala Seksi mengakses daftar pembelajaran -> hanya muncul yang sudah ACC
    $this->actingAs($kepalaSeksi);
    $indexResponse = $this->get(route('kepala-seksi.pembelajaran.index'));
    $indexResponse->assertOk();

    $pertemuanListProps = $indexResponse->viewData('page')['props']['pertemuanList'];
    expect($pertemuanListProps)->toHaveCount(1)
        ->and($pertemuanListProps[0]['id'])->toBe($pertemuanAcc->id)
        ->and($pertemuanListProps[0]['status'])->toBe('acc')
        ->and($pertemuanListProps[0]['galeri'])->toHaveCount(1)
        ->and($pertemuanListProps[0]['galeri'][0]['foto'])->toHaveCount(2);

    // Kepala Seksi melihat detail pertemuan yang sudah ACC -> memuat data pembelajaran lengkap + peserta + galeri foto
    $showResponse = $this->get(route('kepala-seksi.pembelajaran.show', $pertemuanAcc->id));
    $showResponse->assertOk();

    $pertemuanProp = $showResponse->viewData('page')['props']['pertemuan'];
    expect($pertemuanProp['id'])->toBe($pertemuanAcc->id)
        ->and($pertemuanProp['peserta_detail'])->toHaveCount(1)
        ->and($pertemuanProp['peserta_detail'][0]['presensi'])->toBe('hadir')
        ->and($pertemuanProp['peserta_detail'][0]['nilai'])->toBe('sangat_baik')
        ->and($pertemuanProp['galeri'])->toHaveCount(1)
        ->and($pertemuanProp['galeri'][0]['foto'])->toHaveCount(2);

    // Kepala Seksi tidak dapat membuka detail pertemuan yang belum di-ACC
    $this->get(route('kepala-seksi.pembelajaran.show', $pertemuanBelumAcc->id))
        ->assertForbidden();

    // Role selain Kepala Seksi (misal Instruktur) ditolak mengakses menu Kepala Seksi
    $this->actingAs($instruktur);
    $this->get(route('kepala-seksi.pembelajaran.index'))->assertForbidden();
});
