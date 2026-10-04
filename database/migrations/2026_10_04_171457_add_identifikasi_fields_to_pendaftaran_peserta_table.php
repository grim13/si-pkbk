<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds additional identification fields to pendaftaran_peserta
     * based on the "Identifikasi Klien" section of the Form Asesmen Klien.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_peserta', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->after('nama');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('status_perkawinan')->nullable()->after('agama');
            $table->decimal('tinggi_badan', 5, 2)->nullable()->after('status_perkawinan')->comment('cm');
            $table->decimal('berat_badan', 5, 2)->nullable()->after('tinggi_badan')->comment('kg');
            $table->string('bentuk_keadaan_mata')->nullable()->after('berat_badan');
            $table->string('pendengaran')->nullable()->after('bentuk_keadaan_mata');
            $table->string('golongan_darah')->nullable()->after('pendengaran');
            $table->string('nomor_induk_klien')->nullable()->after('golongan_darah');
            $table->string('disabilitas_netra_sejak')->nullable()->after('nomor_induk_klien');
            $table->string('penyebab_disabilitas')->nullable()->after('disabilitas_netra_sejak');
            $table->string('tempat_cacat')->nullable()->after('penyebab_disabilitas');
            $table->text('pengaruh_cacat_tingkah_laku')->nullable()->after('tempat_cacat');
            $table->string('dirawat_di')->nullable()->after('pengaruh_cacat_tingkah_laku');
            $table->string('gradasi_kecacatan')->nullable()->after('dirawat_di');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_peserta', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_kelamin',
                'agama',
                'status_perkawinan',
                'tinggi_badan',
                'berat_badan',
                'bentuk_keadaan_mata',
                'pendengaran',
                'golongan_darah',
                'nomor_induk_klien',
                'disabilitas_netra_sejak',
                'penyebab_disabilitas',
                'tempat_cacat',
                'pengaruh_cacat_tingkah_laku',
                'dirawat_di',
                'gradasi_kecacatan',
            ]);
        });
    }
};
