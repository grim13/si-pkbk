<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Master transaction table for each assessment per client registration.
     * Stores general/narrative fields and progress state.
     */
    public function up(): void
    {
        Schema::create('asesmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_peserta_id')->constrained('pendaftaran_peserta')->cascadeOnDelete();
            $table->foreignId('pekerja_sosial_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->date('tanggal_asesmen')->nullable();

            // Latar Belakang Pendidikan – Formal
            $table->string('pendidikan_tk')->nullable();
            $table->string('pendidikan_sd')->nullable();
            $table->string('pendidikan_smp')->nullable();
            $table->string('pendidikan_sma')->nullable();
            $table->string('pendidikan_pt')->nullable();

            // Latar Belakang Pendidikan – Non-Formal
            $table->string('kursus_nama')->nullable();
            $table->string('kursus_diberikan_oleh')->nullable();
            $table->string('kursus_tempat')->nullable();
            $table->string('kursus_lama')->nullable();

            // Sikap Emosional / Mental
            $table->text('sikap_klien')->nullable();
            $table->text('sikap_keluarga')->nullable();
            $table->text('sikap_masyarakat')->nullable();
            $table->text('keluhan_hambatan')->nullable();

            // Lain-lain – Maksud dan Tujuan (checkbox)
            $table->boolean('tujuan_bimbingan_keterampilan')->default(false);
            $table->boolean('tujuan_bimbingan_sekolah')->default(false);

            // Lain-lain – Keterampilan yang dipilih
            $table->boolean('keterampilan_praktis_produktif')->default(false);
            $table->boolean('keterampilan_massage_pijat')->default(false);

            // Lain-lain – Sumber Informasi
            $table->boolean('info_dokter_rs')->default(false);
            $table->boolean('info_petugas_sosial_kec')->default(false);
            $table->boolean('info_dinas_sosial')->default(false);
            $table->boolean('info_psm')->default(false);
            $table->boolean('info_teman')->default(false);

            // Kesimpulan
            $table->text('kesimpulan_latar_belakang')->nullable();
            $table->text('kesimpulan_pengaruh_kecacatan')->nullable();
            $table->text('kesimpulan_keinginan_harapan')->nullable();
            $table->text('kesimpulan_bentuk_bantuan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen');
    }
};
