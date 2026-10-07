<?php

namespace Database\Seeders;

use App\Models\PersyaratanAdministrasi;
use Illuminate\Database\Seeder;

class PersyaratanAdministrasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Anak berusia 9 tahun s/d 35 tahun.',
            'Belum menikah dan tidak akan menikah selama masa rehabilitasi.',
            'Berbadan sehat, tidak bertato, dan tidak mempunyai cacat ganda.',
            'Surat Pengantar dan Surat Keterangan tidak mampu dari RT/RW, Lurah, atau Camat.',
            'Surat Keterangan Dokter tentang tingkat kecacatan (bila ada).',
            'Pas Photo 3x4 berwarna sebanyak 4 lembar.',
            'Photocopy Ijazah / STTB yang dimiliki.',
            'Fotocopy Kartu Keluarga (KK) dan KTP Orang Tua/Wali.',
            'Fotocopy KTP dan Akta Kelahiran Klien',
            "Mengisi formulir yang disediakan:\na. Surat Pernyataan Orang Tua / Wali tentang kesanggupan menerima kembali klien setelah selesai atau dikembalikan karena melanggar tata tertib / dinyatakan tidak dapat mengikuti program.\nb. Data Diri klien dan keluarga yang diisi oleh pekerja sosial.",
        ];

        foreach ($data as $deskripsi) {
            PersyaratanAdministrasi::firstOrCreate(['deskripsi' => $deskripsi]);
        }
    }
}
