<?php

namespace Database\Seeders;

use App\Models\JenisDisabilitas;
use Illuminate\Database\Seeder;

class JenisDisabilitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Tuna Wicara',
                'keterangan' => 'Hambatan dalam berbicara atau berkomunikasi secara verbal.',
            ],
            [
                'nama' => 'Tuna Rungu',
                'keterangan' => 'Hambatan dalam fungsi pendengaran.',
            ],
            [
                'nama' => 'Tuna Netra',
                'keterangan' => 'Hambatan dalam fungsi penglihatan.',
            ],
        ];

        foreach ($data as $item) {
            JenisDisabilitas::updateOrCreate(
                ['nama' => $item['nama']],
                ['keterangan' => $item['keterangan']]
            );
        }
    }
}
