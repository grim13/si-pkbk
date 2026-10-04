<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsesmenSeksiPertanyaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds master data for the assessment form sections and questions
     * based on the official "Form Asesmen Klien" document.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('asesmen_jawaban')->truncate();
        DB::table('asesmen_pertanyaan')->truncate();
        DB::table('asesmen_seksi')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $seksiData = [
            [
                'kode' => 'masalah_setelah_kelahiran',
                'nama' => 'Masalah Setelah Kelahiran',
                'urutan' => 5,
                'pertanyaan' => [],
                // NOTE: Complex fields for this section are stored directly in the `asesmen` table:
                // bayi_kuning, bayi_kuning_penanganan, bayi_kuning_uv_hari,
                // bayi_hisap_asi, lama_asi_bulan, lama_asi_lainnya,
                // masalah_kejang_stuip, masalah_diare, masalah_lainnya (+ _ket variants)
            ],
            [
                'kode' => 'perkembangan_balita',
                'nama' => 'Riwayat Perkembangan Pada Masa Balita',
                'urutan' => 6,
                'pertanyaan' => [
                    ['nomor' => 1,  'pertanyaan' => 'Anak saya mampu untuk duduk sesuai dengan usianya (antara 6-8 bulan)',                                              'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 2,  'pertanyaan' => 'Ia mampu merangkak sesuai dengan usianya (antara 9-11 bulan)',                                                     'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 3,  'pertanyaan' => 'Anak saya mampu untuk berjalan sendiri sesuai dengan usianya',                                                     'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 4,  'pertanyaan' => 'Anak saya cukup aktif dan berani tapi konsentrasinya tampak baik',                                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 5,  'pertanyaan' => 'Anak saya dulu suka mencoret-coret dengan pensil atau alat-alat tulis yang ditemukanya',                           'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 6,  'pertanyaan' => 'Sekarang ia suka menggambar dan mewarnai',                                                                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 7,  'pertanyaan' => 'Saya mengizinkan anak untuk bermain dengan teman-temannya',                                                        'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 8,  'pertanyaan' => 'Ia menyukai bermain dengan teman-teman sebaya',                                                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 9,  'pertanyaan' => 'Setiap hari ia memiliki kesempatan untuk bermain dengan teman-temannya',                                           'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 10, 'pertanyaan' => 'Saya sulit sekali diatur untuk mengikuti jadwal sehari-harinya',                                                   'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 11, 'pertanyaan' => 'Ia masih megompol',                                                                                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 12, 'pertanyaan' => 'Ia harus selalu dekat dengan ibu',                                                                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 13, 'pertanyaan' => 'Ia mudah menangis',                                                                                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 14, 'pertanyaan' => 'Ia akan meminjamkan mainan atau memberikan makanan bila teman memintanya',                                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 15, 'pertanyaan' => 'Saya membiasakan kepada anak untuk memiliki pola hidup teratur (makan, minum, mandi, solat dan belajar)',         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 16, 'pertanyaan' => 'Anak saya dapat mengerti bila keinginannya tidak langsung saya penuhi',                                            'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 17, 'pertanyaan' => 'Pada usia antara 6-8 bulan anak saya masih lemas untuk duduk',                                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 18, 'pertanyaan' => 'Waktu merangkaknya sangat singkat dan gerakan-gerakannya kurang terkoordinasi',                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 19, 'pertanyaan' => 'Saya sulit untuk berjalan dengan tertib, tampak tergesa-gesa dan sering jatuh',                                   'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 20, 'pertanyaan' => 'Dulu ia takut untuk main ayunan, seluncuran atau melompat-lompat',                                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 21, 'pertanyaan' => 'Anak saya sangat banyak bergerak dan sulit diatur, kecuali jika menjelang tidur',                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 22, 'pertanyaan' => 'Anak saya suka merasa jijik dan tidak nyaman bila memegangi benda-benda yang kenyal atau terkena benda basah atau kotor', 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 23, 'pertanyaan' => 'Dirumah kami, tetangga-tetangga berjatuhan, sehingga anak kurang kesempatan untuk bermain',                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 24, 'pertanyaan' => 'Ia tampak pelit dan galak kepada teman-temannya',                                                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 25, 'pertanyaan' => 'Ia sering ngambek dan marah bila keinginannya tidak diikuti',                                                      'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                ],
            ],
            [
                'kode' => 'riwayat_kelahiran',
                'nama' => 'Riwayat Kelahiran',
                'urutan' => 1,
                'pertanyaan' => [
                    ['nomor' => 1,  'pertanyaan' => 'Rutin kontrol kandungan',                                             'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 2,  'pertanyaan' => 'Kontrol kandungan ke dokter',                                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 3,  'pertanyaan' => 'Kontrol kandungan ke dukun',                                          'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 4,  'pertanyaan' => 'Selama mengandung, gizi ibu tercukupi',                               'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 5,  'pertanyaan' => 'Selama mengandung pernah mengalami kecelakaan',                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 6,  'pertanyaan' => 'Usia kandungan pernah cukup bulan',                                   'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 7,  'pertanyaan' => 'Melahirkan dengan bantuan dokter',                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 8,  'pertanyaan' => 'Melahirkan dengan bantuan bidan',                                     'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 9,  'pertanyaan' => 'Melahirkan dengan bantuan dukun',                                     'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 10, 'pertanyaan' => 'Melahirkan tanpa bantuan ketiganya',                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 11, 'pertanyaan' => 'Lahir dalam keadaan sehat/normal',                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 12, 'pertanyaan' => 'Lahir dengan vacuum',                                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 13, 'pertanyaan' => 'Lahir dengan operasi',                                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 14, 'pertanyaan' => 'Berat badan waktu lahir',                                             'tipe_jawaban' => 'number',   'satuan' => 'kg'],
                    ['nomor' => 15, 'pertanyaan' => 'Panjang badan waktu lahir',                                           'tipe_jawaban' => 'number',   'satuan' => 'cm'],
                    ['nomor' => 16, 'pertanyaan' => 'Imunisasi cukup lengkap',                                             'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 17, 'pertanyaan' => 'Pernah step cukup lama',                                              'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 18, 'pertanyaan' => 'Asupan utama bayi adalah ASI',                                        'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 19, 'pertanyaan' => 'Asupan utama bayi adalah susu kaleng',                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 20, 'pertanyaan' => 'Ada riwayat penyakit tipes, diabetes, asma, jantung, darah tinggi',  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                ],
            ],
            [
                'kode' => 'sosial',
                'nama' => 'Assessment Sosial',
                'urutan' => 2,
                'pertanyaan' => [
                    ['nomor' => 1,  'pertanyaan' => 'Klien suka menyendiri',                                                             'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 2,  'pertanyaan' => 'Klien sulit diajak berbicara',                                                      'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 3,  'pertanyaan' => 'Klien pilih-pilih teman',                                                           'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 4,  'pertanyaan' => 'Klien sulit beradaptasi dengan lingkungan sekitar',                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 5,  'pertanyaan' => 'Klien hanya akrab dengan anggota keluarga tertentu',                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 6,  'pertanyaan' => 'Klien sulit mengenal orang-orang disekelilingnya',                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 7,  'pertanyaan' => 'Klien pernah mendapatkan diskriminasi dari sekitar',                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 8,  'pertanyaan' => 'Klien ikut dalam kegiatan kemanusiaan seperti donor darah',                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 9,  'pertanyaan' => 'Klien ikut dalam kegiatan kemasyarakatan seperti gotong royong',                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 10, 'pertanyaan' => 'Klien ikut aktif dalam kegiatan keagamaan seperti pengajian',                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                ],
            ],
            [
                'kode' => 'mental_spiritual',
                'nama' => 'Assessment Mental-Spiritual',
                'urutan' => 3,
                'pertanyaan' => [
                    ['nomor' => 1,  'pertanyaan' => 'Klien rajin beribadah',                                   'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 2,  'pertanyaan' => 'Klien bisa membaca kitab suci sesuai agama masing-masing', 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 3,  'pertanyaan' => 'Klien suka membantu orang lain',                          'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 4,  'pertanyaan' => 'Klien suka memberi / bersedekah',                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 5,  'pertanyaan' => 'Klien mudah marah / tersinggung',                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 6,  'pertanyaan' => 'Klien tidak punya semangat yang tinggi',                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 7,  'pertanyaan' => 'Klien mudah tertekan',                                    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 8,  'pertanyaan' => 'Klien tidak percaya diri',                                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 9,  'pertanyaan' => 'Klien belum mandiri',                                     'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 10, 'pertanyaan' => 'Klien tidak mau pisah dengan orang terdekat',             'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                ],
            ],
            [
                'kode' => 'penglihatan',
                'nama' => 'Assessment Penglihatan',
                'urutan' => 4,
                'pertanyaan' => [
                    ['nomor' => 1,  'pertanyaan' => 'Disabilitas sejak lahir',                                          'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 2,  'pertanyaan' => 'Disabilitas pada masa anak-anak',                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 3,  'pertanyaan' => 'Disabilitas setelah dewasa',                                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 4,  'pertanyaan' => 'Ada anggota keluarga lain yang memiliki disabilitas yang sama',    'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 5,  'pertanyaan' => 'Disabilitas disebabkan oleh penyakit',                            'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 6,  'pertanyaan' => 'Disabilitas disebabkan oleh kecelakaan',                          'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 7,  'pertanyaan' => 'Tingkat disabilitas: Total',                                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 8,  'pertanyaan' => 'Tingkat disabilitas: Low Vision – Bayang Hitam / Cahaya',          'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 9,  'pertanyaan' => 'Tingkat disabilitas: Low Vision – Blur / Buram',                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 10, 'pertanyaan' => 'Saat berjalan sering menabrak benda sekitar',                      'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 11, 'pertanyaan' => 'Saat berjalan tidak ada keseimbangan',                             'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 12, 'pertanyaan' => 'Saat berjalan tubuh condong ke satu sisi',                        'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 13, 'pertanyaan' => 'Klien menggunakan alat bantu kacamata',                            'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 14, 'pertanyaan' => 'Bola mata tertutup rapat',                                         'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 15, 'pertanyaan' => 'Bola mata tiroid',                                                 'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 16, 'pertanyaan' => 'Bola mata berlapis selaput',                                       'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 17, 'pertanyaan' => 'Bola mata keruh',                                                  'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 18, 'pertanyaan' => 'Klien sering menekan / menusuk mata dengan jari tangan',           'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 19, 'pertanyaan' => 'Klien dapat membaca buku dengan jarak dekat',                      'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                    ['nomor' => 20, 'pertanyaan' => 'Kepala klien sering bergerak diluar gerakan wajah',                'tipe_jawaban' => 'ya_tidak', 'satuan' => null],
                ],
            ],
        ];

        foreach ($seksiData as $seksi) {
            $pertanyaanList = $seksi['pertanyaan'];
            unset($seksi['pertanyaan']);

            $seksiId = DB::table('asesmen_seksi')->insertGetId(array_merge($seksi, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            foreach ($pertanyaanList as $idx => $pertanyaan) {
                DB::table('asesmen_pertanyaan')->insert(array_merge($pertanyaan, [
                    'asesmen_seksi_id' => $seksiId,
                    'urutan' => $idx + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
