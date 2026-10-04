<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pendaftaran_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wali_id')->constrained('wali')->cascadeOnDelete();
            $table->string('nik');
            $table->string('no_pendaftaran')->unique();
            $table->string('nama');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat_asal');
            $table->string('pendidikan');
            $table->string('keterampilan_yang_diminati');
            $table->enum('status_pendaftaran', ['draft', 'verifikasi_berkas', 'perbaikan_berkas', 'asesmen', 'selesai_asesmen', 'diterima', 'ditolak'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_peserta');
    }
};
