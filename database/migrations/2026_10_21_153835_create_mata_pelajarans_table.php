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
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_disabilitas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('jenis_disabilitas_id')->constrained('jenis_disabilitas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mata_pelajaran_id', 'jenis_disabilitas_id'], 'mapel_disabilitas_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_disabilitas_mata_pelajaran');
        Schema::dropIfExists('mata_pelajaran');
    }
};
