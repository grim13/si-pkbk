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
        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_pembelajaran_id')
                ->constrained('pertemuan_pembelajaran')
                ->cascadeOnDelete();
            $table->string('judul');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('galeri_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('galeri_id')
                ->constrained('galeri')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('nama_file')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeri_foto');
        Schema::dropIfExists('galeri');
    }
};
