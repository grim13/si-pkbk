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
        Schema::create('pendaftaran_peserta_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_peserta_id')->constrained('pendaftaran_peserta')->cascadeOnDelete();
            $table->foreignId('jenis_berkas_pendaftaran_id')->constrained('jenis_berkas_pendaftaran')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->enum('status', ['menunggu_verifikasi', 'diterima', 'perbaikan'])->default('menunggu_verifikasi');
            $table->text('keterangan_perbaikan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_peserta_berkas');
    }
};
