<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Master data for assessment sections (kelompok pertanyaan).
     * e.g.: Riwayat Kelahiran, Assessment Sosial, Assessment Mental-Spiritual, Assessment Penglihatan
     */
    public function up(): void
    {
        Schema::create('asesmen_seksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique()->comment('e.g.: riwayat_kelahiran, sosial, mental_spiritual, penglihatan');
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_seksi');
    }
};
