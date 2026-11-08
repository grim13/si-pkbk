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

function createKlienEvaluasi(
    Wali $wali,
    JenisDisabilitas $disabilitas,
    string $nama,
    string $nik,
    ?string $tanggalDiterima,
    string $status = 'diterima',
): PendaftaranPeserta {
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
        'keterampilan_yang_diminati' => 'Komputer & Braille',
        'status_pendaftaran' => $status,
        'tanggal_mulai_diterima' => $tanggalDiterima,
    ]);
}

test('pekerja sosial dapat melihat daftar klien berstatus diterima beserta lama pembinaan dan jumlah kelas yang sudah dihadiri', function () {
    $this->travelTo('2026-05-15 10:00:00');

    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $instruktur = User::factory()->create(['name' => 'Instruktur Braille']);
    $instruktur->assignRole('instruktur');

    $waliUser = User::factory()->create(['name' => 'Wali Klien']);
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
    $mapel = MataPelajaran::factory()->create(['nama' => 'Baca Tulis Braille']);
    $mapel->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'instruktur_id' => $instruktur->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $klienDiterima = createKlienEvaluasi($wali, $tunaNetra, 'Andi Diterima', '1671010000000011', '2026-03-10 08:00:00', 'diterima');
    createKlienEvaluasi($wali, $tunaNetra, 'Budi Belum Lulus', '1671010000000012', null, 'selesai_asesmen');

    $pertemuanAcc1 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-16',
        'status' => 'acc',
        'keterangan' => 'Pertemuan 1',
    ]);
    $pertemuanAcc1->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klienDiterima->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
        'keterangan' => 'Hadir dan sangat aktif',
    ]);

    $pertemuanAcc2 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-23',
        'status' => 'acc',
        'keterangan' => 'Pertemuan 2',
    ]);
    $pertemuanAcc2->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klienDiterima->id,
        'presensi' => 'izin',
        'nilai' => 'baik',
        'keterangan' => 'Izin sakit',
    ]);

    $pertemuanDraft = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwal->id,
        'instruktur_id' => $instruktur->id,
        'tanggal' => '2026-03-30',
        'status' => 'draft',
        'keterangan' => 'Pertemuan 3 Draft',
    ]);
    $pertemuanDraft->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klienDiterima->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
    ]);

    $this->actingAs($pekerjaSosial);

    $response = $this->get(route('pekerja-sosial.evaluasi.index'));

    $response->assertOk();
    $pesertaProps = $response->viewData('page')['props']['peserta'];
    expect($pesertaProps)->toHaveCount(1);
    expect($pesertaProps[0]['id'])->toBe($klienDiterima->id);
    expect($pesertaProps[0]['hadir_count'])->toBe(1);
    expect($pesertaProps[0]['izin_count'])->toBe(1);
    expect($pesertaProps[0]['lama_pembinaan'])->toBe('2 Bulan 5 Hari');
    expect($pesertaProps[0]['lama_pembinaan_hari'])->toBe(66);
});

test('pekerja sosial dapat melihat rata-rata nilai setiap mata pelajaran, komentar terakhir, dan riwayat detail seluruh pertemuan', function () {
    $this->travelTo('2026-06-01 09:00:00');

    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $instruktur1 = User::factory()->create(['name' => 'Pak Hendra']);
    $instruktur1->assignRole('instruktur');

    $instruktur2 = User::factory()->create(['name' => 'Bu Rina']);
    $instruktur2->assignRole('instruktur');

    $waliUser = User::factory()->create(['name' => 'Wali Klien']);
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

    $mapelBraille = MataPelajaran::factory()->create(['nama' => 'Baca Tulis Braille']);
    $mapelBraille->jenisDisabilitas()->sync([$tunaNetra->id]);

    $mapelOm = MataPelajaran::factory()->create(['nama' => 'Orientasi dan Mobilitas']);
    $mapelOm->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwalBraille = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelBraille->id,
        'instruktur_id' => $instruktur1->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $jadwalOm = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelOm->id,
        'instruktur_id' => $instruktur2->id,
        'tahun' => 2026,
        'hari' => 'Rabu',
    ]);

    $klien = createKlienEvaluasi($wali, $tunaNetra, 'Citra Mandiri', '1671010000000021', '2026-03-01 08:00:00', 'diterima');

    // Mata Pelajaran 1: Braille -> 2 pertemuan hadir (sangat_baik=4, baik=3 -> rata-rata 3.5 -> sangat_baik)
    $pBraille1 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalBraille->id,
        'instruktur_id' => $instruktur1->id,
        'tanggal' => '2026-03-16',
        'status' => 'acc',
        'keterangan' => 'Pengenalan huruf dasar',
    ]);
    $pBraille1->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klien->id,
        'presensi' => 'hadir',
        'nilai' => 'baik',
        'keterangan' => 'Mulai mengenal huruf A sampai J',
    ]);

    $pBraille2 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalBraille->id,
        'instruktur_id' => $instruktur1->id,
        'tanggal' => '2026-03-23',
        'status' => 'acc',
        'keterangan' => 'Membaca kalimat pendek',
    ]);
    $pBraille2->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klien->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
        'keterangan' => 'Sudah lancar membaca kalimat pendek secara mandiri',
    ]);

    // Mata Pelajaran 2: OM -> 1 pertemuan hadir (cukup=2 -> rata-rata 2.0 -> cukup), komentar di pertemuan.keterangan
    $pOm1 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalOm->id,
        'instruktur_id' => $instruktur2->id,
        'tanggal' => '2026-03-18',
        'status' => 'acc',
        'keterangan' => 'Perlu latihan orientasi ruang lebih lanjut',
    ]);
    $pOm1->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $klien->id,
        'presensi' => 'hadir',
        'nilai' => 'cukup',
        'keterangan' => null,
    ]);

    $this->actingAs($pekerjaSosial);

    $response = $this->get(route('pekerja-sosial.evaluasi.show', $klien->id));

    $response->assertOk();
    $props = $response->viewData('page')['props'];

    expect($props['summary']['total_hadir'])->toBe(3);
    expect($props['summary']['total_mata_pelajaran'])->toBe(2);
    expect($props['summary']['rata_rata_skor'])->toBe(3.0);
    expect($props['summary']['rata_rata_nilai'])->toBe('baik');

    $mapelList = collect($props['mataPelajaranList']);
    expect($mapelList)->toHaveCount(2);

    $brailleSummary = $mapelList->firstWhere('mata_pelajaran_id', $mapelBraille->id);
    expect($brailleSummary['nama_mata_pelajaran'])->toBe('Baca Tulis Braille');
    expect($brailleSummary['total_hadir'])->toBe(2);
    expect($brailleSummary['rata_rata_skor'])->toBe(3.5);
    expect($brailleSummary['rata_rata_nilai'])->toBe('sangat_baik');
    expect($brailleSummary['rata_rata_label'])->toBe('Sangat Baik');
    expect($brailleSummary['komentar_terakhir'])->toBe('Sudah lancar membaca kalimat pendek secara mandiri');
    expect($brailleSummary['instruktur_terakhir'])->toBe('Pak Hendra');
    expect($brailleSummary['riwayat'])->toHaveCount(2);
    expect($brailleSummary['riwayat'][0]['tanggal'])->toBe('2026-03-23');
    expect($brailleSummary['riwayat'][0]['komentar_instruktur'])->toBe('Sudah lancar membaca kalimat pendek secara mandiri');
    expect($brailleSummary['riwayat'][1]['tanggal'])->toBe('2026-03-16');
    expect($brailleSummary['riwayat'][1]['komentar_instruktur'])->toBe('Mulai mengenal huruf A sampai J');

    $omSummary = $mapelList->firstWhere('mata_pelajaran_id', $mapelOm->id);
    expect($omSummary['nama_mata_pelajaran'])->toBe('Orientasi dan Mobilitas');
    expect($omSummary['rata_rata_skor'])->toBe(2.0);
    expect($omSummary['rata_rata_nilai'])->toBe('cukup');
    expect($omSummary['rata_rata_label'])->toBe('Cukup');
    expect($omSummary['komentar_terakhir'])->toBe('Perlu latihan orientasi ruang lebih lanjut');
});

test('pekerja sosial dapat mengajukan lepas mandiri dengan keterangan dan kepala seksi dapat menolak kembali ke diterima atau menerima menjadi lepas_mandiri dengan keterangan', function () {
    $this->travelTo('2026-07-10 14:00:00');

    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $kepalaSeksi = User::factory()->create();
    $kepalaSeksi->assignRole('kepala_seksi');

    $waliUser = User::factory()->create(['name' => 'Wali Klien']);
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
    $klien = createKlienEvaluasi($wali, $tunaNetra, 'Eka Siap Mandiri', '1671010000000041', '2026-01-10 08:00:00', 'diterima');

    // 1. Pekerja Sosial gagal mengajukan jika keterangan kosong
    $this->actingAs($pekerjaSosial);

    $emptyPengajuan = $this->post(route('pekerja-sosial.evaluasi.store', $klien->id), [
        'keterangan' => '',
    ]);
    $emptyPengajuan->assertSessionHasErrors([
        'keterangan' => 'Keterangan pengajuan lepas mandiri wajib diisi.',
    ]);

    // 2. Pekerja Sosial berhasil mengajukan lepas mandiri ke Kepala Seksi
    $validPengajuan = $this->post(route('pekerja-sosial.evaluasi.store', $klien->id), [
        'keterangan' => 'Klien telah menguasai keterampilan braille dan mobilitas dengan sangat baik.',
    ]);
    $validPengajuan->assertRedirect(route('pekerja-sosial.evaluasi.show', $klien->id));

    $klien->refresh();
    expect($klien->status_pendaftaran)->toBe('pengajuan_lepas_mandiri');
    expect($klien->keterangan_pengajuan_lepas_mandiri)->toBe('Klien telah menguasai keterampilan braille dan mobilitas dengan sangat baik.');
    expect($klien->tanggal_pengajuan_lepas_mandiri?->format('Y-m-d H:i:s'))->toBe('2026-07-10 14:00:00');

    // 3. Kepala Seksi melihat daftar pengajuan lepas mandiri
    $this->actingAs($kepalaSeksi);

    $indexKepalaSeksi = $this->get(route('kepala-seksi.lepas-mandiri.index'));
    $indexKepalaSeksi->assertOk();
    $pesertaList = $indexKepalaSeksi->viewData('page')['props']['peserta'];
    expect($pesertaList)->toHaveCount(1);
    expect($pesertaList[0]['id'])->toBe($klien->id);
    expect($pesertaList[0]['status_pendaftaran'])->toBe('pengajuan_lepas_mandiri');

    // 4. Kepala Seksi gagal memutuskan jika keterangan kosong
    $emptyDecision = $this->post(route('kepala-seksi.lepas-mandiri.update', $klien->id), [
        'keputusan' => 'diterima',
        'keterangan' => '',
    ]);
    $emptyDecision->assertSessionHasErrors([
        'keterangan' => 'Keterangan keputusan wajib diisi.',
    ]);

    // 5. Kepala Seksi menolak pengajuan -> status kembali ke 'diterima' beserta keterangannya
    $rejectResponse = $this->post(route('kepala-seksi.lepas-mandiri.update', $klien->id), [
        'keputusan' => 'diterima',
        'keterangan' => 'Tambahkan 2 pertemuan praktik pijat terlebih dahulu sebelum lepas mandiri.',
    ]);
    $rejectResponse->assertRedirect(route('kepala-seksi.lepas-mandiri.index'));

    $klien->refresh();
    expect($klien->status_pendaftaran)->toBe('diterima');
    expect($klien->keterangan_keputusan_lepas_mandiri)->toBe('Tambahkan 2 pertemuan praktik pijat terlebih dahulu sebelum lepas mandiri.');
    expect($klien->tanggal_lepas_mandiri)->toBeNull();

    // 6. Pekerja Sosial mengajukan kembali setelah evaluasi tambahan
    $this->actingAs($pekerjaSosial);
    $this->post(route('pekerja-sosial.evaluasi.store', $klien->id), [
        'keterangan' => 'Klien sudah menyelesaikan 2 pertemuan praktik pijat tambahan dengan nilai Sangat Baik.',
    ])->assertRedirect(route('pekerja-sosial.evaluasi.show', $klien->id));

    $klien->refresh();
    expect($klien->status_pendaftaran)->toBe('pengajuan_lepas_mandiri');

    // 7. Kepala Seksi menerima pengajuan -> status berubah menjadi 'lepas_mandiri' beserta keterangannya
    $this->actingAs($kepalaSeksi);
    $this->travelTo('2026-07-20 15:30:00');

    $approveResponse = $this->post(route('kepala-seksi.lepas-mandiri.update', $klien->id), [
        'keputusan' => 'lepas_mandiri',
        'keterangan' => 'Seluruh kompetensi kemandirian telah terpenuhi, disetujui lepas mandiri.',
    ]);
    $approveResponse->assertRedirect(route('kepala-seksi.lepas-mandiri.index'));

    $klien->refresh();
    expect($klien->status_pendaftaran)->toBe('lepas_mandiri');
    expect($klien->keterangan_keputusan_lepas_mandiri)->toBe('Seluruh kompetensi kemandirian telah terpenuhi, disetujui lepas mandiri.');
    expect($klien->tanggal_lepas_mandiri?->format('Y-m-d H:i:s'))->toBe('2026-07-20 15:30:00');
});

test('pekerja sosial diarahkan kembali jika membuka evaluasi untuk klien yang belum berstatus diterima', function () {
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
    $klienBelumDiterima = createKlienEvaluasi($wali, $tunaNetra, 'Dodi Asesmen', '1671010000000031', null, 'asesmen');

    $this->actingAs($pekerjaSosial);

    $response = $this->get(route('pekerja-sosial.evaluasi.show', $klienBelumDiterima->id));

    $response->assertRedirect(route('pekerja-sosial.evaluasi.index'));
    $response->assertSessionHas('error');
});

test('role yang tidak berwenang ditolak mengakses halaman evaluasi pekerja sosial maupun persetujuan lepas mandiri kepala seksi', function () {
    $instruktur = User::factory()->create();
    $instruktur->assignRole('instruktur');

    $this->actingAs($instruktur);

    $this->get(route('pekerja-sosial.evaluasi.index'))->assertForbidden();
    $this->get(route('kepala-seksi.lepas-mandiri.index'))->assertForbidden();
});

test('wali dapat melihat status lepas mandiri beserta seluruh ringkasan pembelajaran anaknya setelah di-acc kepala seksi', function () {
    $this->travelTo('2026-08-01 09:00:00');

    $pekerjaSosial = User::factory()->create();
    $pekerjaSosial->assignRole('pekerja_sosial');

    $kepalaSeksi = User::factory()->create();
    $kepalaSeksi->assignRole('kepala_seksi');

    $instruktur1 = User::factory()->create(['name' => 'Pak Hendra']);
    $instruktur1->assignRole('instruktur');

    $instruktur2 = User::factory()->create(['name' => 'Bu Rina']);
    $instruktur2->assignRole('instruktur');

    $waliUser = User::factory()->create(['name' => 'Wali Anak']);
    $waliUser->assignRole('wali');
    $wali = Wali::create([
        'user_id' => $waliUser->id,
        'nik' => '1671010000000099',
        'alamat' => 'Palembang',
        'tempat_lahir' => 'Palembang',
        'tanggal_lahir' => '1982-05-10',
        'nomor_hp' => '081234560099',
    ]);

    $tunaNetra = JenisDisabilitas::factory()->tunaNetra()->create();

    $mapelBraille = MataPelajaran::factory()->create(['nama' => 'Baca Tulis Braille']);
    $mapelBraille->jenisDisabilitas()->sync([$tunaNetra->id]);

    $mapelPijat = MataPelajaran::factory()->create(['nama' => 'Keterampilan Pijat Shiatsu']);
    $mapelPijat->jenisDisabilitas()->sync([$tunaNetra->id]);

    $jadwalBraille = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelBraille->id,
        'instruktur_id' => $instruktur1->id,
        'tahun' => 2026,
        'hari' => 'Senin',
    ]);

    $jadwalPijat = Jadwal::factory()->create([
        'mata_pelajaran_id' => $mapelPijat->id,
        'instruktur_id' => $instruktur2->id,
        'tahun' => 2026,
        'hari' => 'Kamis',
    ]);

    $anak = createKlienEvaluasi($wali, $tunaNetra, 'Fajar Mandiri', '1671010000000051', '2026-02-01 08:00:00', 'diterima');

    $pertemuan1 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalBraille->id,
        'instruktur_id' => $instruktur1->id,
        'tanggal' => '2026-03-10',
        'status' => 'acc',
        'keterangan' => 'Evaluasi membaca teks panjang',
    ]);
    $pertemuan1->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $anak->id,
        'presensi' => 'hadir',
        'nilai' => 'sangat_baik',
        'keterangan' => 'Sangat mahir membaca dan menulis huruf Braille mandiri',
    ]);

    $pertemuan2 = PertemuanPembelajaran::create([
        'jadwal_id' => $jadwalPijat->id,
        'instruktur_id' => $instruktur2->id,
        'tanggal' => '2026-04-12',
        'status' => 'acc',
        'keterangan' => 'Praktik pijat punggung dan bahu',
    ]);
    $pertemuan2->pesertaDetail()->create([
        'pendaftaran_peserta_id' => $anak->id,
        'presensi' => 'hadir',
        'nilai' => 'baik',
        'keterangan' => 'Teknik tekanan pijat sudah tepat dan siap praktik kerja',
    ]);

    // 1. Sebelum di-ACC lepas mandiri, ringkasan_pembelajaran pada halaman pendaftaran wali masih null
    $this->actingAs($waliUser);
    $beforeAccResponse = $this->get(route('pendaftaran.index'));
    $beforeAccResponse->assertOk();
    $beforeProps = $beforeAccResponse->viewData('page')['props'];
    expect($beforeProps['pendaftaran']['status_pendaftaran'])->toBe('diterima');
    expect($beforeProps['pendaftaran']['ringkasan_pembelajaran'])->toBeNull();
    expect($beforeProps['ringkasan_pembelajaran'])->toBeNull();

    // 2. Pekerja Sosial mengajukan & Kepala Seksi meng-ACC lepas mandiri
    $this->actingAs($pekerjaSosial);
    $this->post(route('pekerja-sosial.evaluasi.store', $anak->id), [
        'keterangan' => 'Anak telah siap kembali ke masyarakat secara mandiri.',
    ])->assertRedirect(route('pekerja-sosial.evaluasi.show', $anak->id));

    $this->actingAs($kepalaSeksi);
    $this->post(route('kepala-seksi.lepas-mandiri.update', $anak->id), [
        'keputusan' => 'lepas_mandiri',
        'keterangan' => 'Disetujui lepas mandiri, seluruh kompetensi telah dikuasai.',
    ])->assertRedirect(route('kepala-seksi.lepas-mandiri.index'));

    // 3. Wali membuka halaman /pendaftaran -> status lepas_mandiri beserta ringkasan seluruh pembelajaran tampil
    $this->actingAs($waliUser);
    $afterAccResponse = $this->get(route('pendaftaran.index'));
    $afterAccResponse->assertOk();

    $afterProps = $afterAccResponse->viewData('page')['props'];
    expect($afterProps['pendaftaran']['status_pendaftaran'])->toBe('lepas_mandiri');
    expect($afterProps['pendaftaran']['keterangan_keputusan_lepas_mandiri'])->toBe('Disetujui lepas mandiri, seluruh kompetensi telah dikuasai.');
    expect($afterProps['ringkasan_pembelajaran'])->not->toBeNull();
    expect($afterProps['ringkasan_pembelajaran']['summary']['total_mata_pelajaran'])->toBe(2);
    expect($afterProps['ringkasan_pembelajaran']['summary']['total_hadir'])->toBe(2);
    expect($afterProps['ringkasan_pembelajaran']['summary']['rata_rata_skor'])->toBe(3.5);
    expect($afterProps['ringkasan_pembelajaran']['summary']['rata_rata_nilai'])->toBe('sangat_baik');

    $mapelList = collect($afterProps['ringkasan_pembelajaran']['mata_pelajaran_list']);
    expect($mapelList)->toHaveCount(2);

    $braille = $mapelList->firstWhere('mata_pelajaran_id', $mapelBraille->id);
    expect($braille['nama_mata_pelajaran'])->toBe('Baca Tulis Braille');
    expect($braille['rata_rata_nilai'])->toBe('sangat_baik');
    expect($braille['komentar_terakhir'])->toBe('Sangat mahir membaca dan menulis huruf Braille mandiri');
    expect($braille['riwayat'])->toHaveCount(1);

    $pijat = $mapelList->firstWhere('mata_pelajaran_id', $mapelPijat->id);
    expect($pijat['nama_mata_pelajaran'])->toBe('Keterampilan Pijat Shiatsu');
    expect($pijat['rata_rata_nilai'])->toBe('baik');
    expect($pijat['komentar_terakhir'])->toBe('Teknik tekanan pijat sudah tepat dan siap praktik kerja');
    expect($pijat['riwayat'])->toHaveCount(1);

    // 4. Wali membuka /dashboard -> peserta_list juga memuat ringkasan_pembelajaran untuk anak yang sudah lepas_mandiri
    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertOk();
    $dashboardData = $dashboardResponse->viewData('page')['props']['dashboardData'];
    expect($dashboardData['stats']['peserta_lepas_mandiri'])->toBe(1);
    expect($dashboardData['peserta_list'][0]['status_pendaftaran'])->toBe('lepas_mandiri');
    expect($dashboardData['peserta_list'][0]['ringkasan_pembelajaran']['mata_pelajaran_list'])->toHaveCount(2);
});
