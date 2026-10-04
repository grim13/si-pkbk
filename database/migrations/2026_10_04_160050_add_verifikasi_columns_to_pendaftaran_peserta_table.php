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
        Schema::table('pendaftaran_peserta', function (Blueprint $table) {
            $table->text('catatan_verifikasi')->nullable();
            $table->dateTime('jadwal_asesmen')->nullable();
            $table->string('tempat_asesmen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_peserta', function (Blueprint $table) {
            $table->dropColumn(['catatan_verifikasi', 'jadwal_asesmen', 'tempat_asesmen']);
        });
    }
};
