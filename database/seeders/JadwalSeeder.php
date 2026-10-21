<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instruktur = User::updateOrCreate(
            ['email' => 'instruktur@sipkbk.com'],
            [
                'name' => 'Instruktur',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $instruktur->hasRole('instruktur')) {
            $instruktur->assignRole('instruktur');
        }

        $tahun = 2026;

        $jadwalData = [
            // PAGI (08:00 s/d 10:00)
            [
                'hari' => 'Senin',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Pengajaran Bahasa Isyarat Komunikasi',
                'keterangan' => null,
            ],
            [
                'hari' => 'Selasa',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Pengajaran Bahasa Isyarat Berhitung',
                'keterangan' => null,
            ],
            [
                'hari' => 'Rabu',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Pengajaran Bahasa Isyarat Komunikasi',
                'keterangan' => null,
            ],
            [
                'hari' => 'Kamis',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Pengajaran Bahasa Isyarat Berhitung',
                'keterangan' => null,
            ],
            [
                'hari' => 'Jumat',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Pengajaran Bahasa Isyarat Komunikasi',
                'keterangan' => null,
            ],
            [
                'hari' => 'Jumat',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'nama_mapel' => 'Olahraga pagi / Jalan Santai Bersama',
                'keterangan' => '2 Minggu Sekali',
            ],

            // SIANG (13:15 s/d 15:30)
            [
                'hari' => 'Senin',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Bimbingan Konseling',
                'keterangan' => null,
            ],
            [
                'hari' => 'Selasa',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Keterampilan Baca Tulis braille',
                'keterangan' => null,
            ],
            [
                'hari' => 'Rabu',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Keterampilan Musik',
                'keterangan' => null,
            ],
            [
                'hari' => 'Kamis',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Bimbingan Keagamaan dan Tausyiah',
                'keterangan' => null,
            ],
            [
                'hari' => 'Jumat',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Keterampilan Vokal',
                'keterangan' => null,
            ],
            [
                'hari' => 'Jumat',
                'jam_mulai' => '13:15',
                'jam_selesai' => '15:30',
                'nama_mapel' => 'Keterampilan Tataboga',
                'keterangan' => '1 bulan sekali',
            ],
        ];

        foreach ($jadwalData as $item) {
            $mapel = MataPelajaran::where('nama', $item['nama_mapel'])->first();
            if (! $mapel) {
                continue;
            }

            Jadwal::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'hari' => $item['hari'],
                    'jam_mulai' => $item['jam_mulai'],
                    'jam_selesai' => $item['jam_selesai'],
                    'mata_pelajaran_id' => $mapel->id,
                ],
                [
                    'instruktur_id' => $instruktur->id,
                    'keterangan' => $item['keterangan'],
                ]
            );
        }
    }
}
