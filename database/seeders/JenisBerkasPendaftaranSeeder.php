<?php

namespace Database\Seeders;

use App\Models\JenisBerkasPendaftaran;
use Illuminate\Database\Seeder;

class JenisBerkasPendaftaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisBerkasPendaftaran::query()->delete();

        $berkas = [
            'Surat Permohonan Orang Tua / Wali',
            'Surat Pengantar dari Desa/Kelurahan',
            'Surat Keterangan Tidak Mampu',
            'Surat Keterangan Kesehatan',
            'Surat Keterangan Dokter (disabilitas) (bila ada)',
            'Pas Photo ukuran 3 x 4 (4 lembar)',
            'Ijazah / STTB',
            'Kartu Keluarga',
            'KTP Orang Tua / Wali',
            'Akte / Keterangan / Surat Kelahiran',
            'KTP Klien',
            'Surat Pernyataan Tidak Menikah',
            'Surat Perjanjian',
            'Surat Pernyataan Menerima Kembali',
        ];

        $dengan_template = [
            'Surat Permohonan Orang Tua / Wali',
            'Surat Pernyataan Tidak Menikah',
            'Surat Perjanjian',
            'Surat Pernyataan Menerima Kembali'
        ];

        foreach ($berkas as $nama_berkas) {
            $format_file = str_contains($nama_berkas, 'Pas Photo') 
                ? 'image/jpeg, image/png, image/jpg' 
                : 'application/pdf, image/jpeg, image/png, image/jpg';

            $template_surat = null;
            if (in_array($nama_berkas, $dengan_template)) {
                $template_surat = strtolower(str_replace([' ', '/', '\\', '___', '__'], '_', $nama_berkas)) . '.docx';
            }

            JenisBerkasPendaftaran::create([
                'nama_berkas' => $nama_berkas,
                'required' => !str_contains($nama_berkas, '(bila ada)'),
                'format_file' => $format_file,
                'template_surat' => $template_surat,
            ]);
        }
    }
}
