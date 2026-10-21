<?php

namespace Database\Seeders;

use App\Models\JenisDisabilitas;
use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tunaWicara = JenisDisabilitas::firstOrCreate(['nama' => 'Tuna Wicara']);
        $tunaRungu = JenisDisabilitas::firstOrCreate(['nama' => 'Tuna Rungu']);
        $tunaNetra = JenisDisabilitas::firstOrCreate(['nama' => 'Tuna Netra']);

        $semuaDisabilitasIds = [$tunaWicara->id, $tunaRungu->id, $tunaNetra->id];
        $runguWicaraIds = [$tunaWicara->id, $tunaRungu->id];
        $netraIds = [$tunaNetra->id];

        $mataPelajaranData = [
            [
                'nama' => 'Pengajaran Bahasa Isyarat Komunikasi',
                'keterangan' => 'Pembelajaran bahasa isyarat untuk komunikasi sehari-hari.',
                'disabilitas_ids' => $runguWicaraIds,
            ],
            [
                'nama' => 'Pengajaran Bahasa Isyarat Berhitung',
                'keterangan' => 'Pembelajaran berhitung menggunakan bahasa isyarat.',
                'disabilitas_ids' => $runguWicaraIds,
            ],
            [
                'nama' => 'Olahraga pagi / Jalan Santai Bersama',
                'keterangan' => 'Kegiatan olahraga pagi dan jalan santai bersama klien.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
            [
                'nama' => 'Bimbingan Konseling',
                'keterangan' => 'Layanan bimbingan dan konseling bagi klien.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
            [
                'nama' => 'Keterampilan Baca Tulis braille',
                'keterangan' => 'Pembelajaran membaca dan menulis huruf Braille.',
                'disabilitas_ids' => $netraIds,
            ],
            [
                'nama' => 'Keterampilan Musik',
                'keterangan' => 'Pelatihan keterampilan bermain alat musik.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
            [
                'nama' => 'Bimbingan Keagamaan dan Tausyiah',
                'keterangan' => 'Pembinaan mental spiritual, keagamaan, dan tausyiah.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
            [
                'nama' => 'Keterampilan Vokal',
                'keterangan' => 'Pelatihan olah vokal dan bernyanyi.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
            [
                'nama' => 'Keterampilan Tataboga',
                'keterangan' => 'Pelatihan keterampilan memasak dan pengolahan makanan.',
                'disabilitas_ids' => $semuaDisabilitasIds,
            ],
        ];

        foreach ($mataPelajaranData as $item) {
            $mapel = MataPelajaran::updateOrCreate(
                ['nama' => $item['nama']],
                ['keterangan' => $item['keterangan']]
            );

            $mapel->jenisDisabilitas()->sync($item['disabilitas_ids']);
        }
    }
}
