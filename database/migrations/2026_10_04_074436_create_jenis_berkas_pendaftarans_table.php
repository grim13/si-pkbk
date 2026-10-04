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
        Schema::create('jenis_berkas_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_berkas');
            $table->text('keterangan')->nullable();
            $table->boolean('required')->default(true);
            $table->string('format_file')->default('application/pdf, image/jpeg, image/png, image/jpg');
            $table->string('template_surat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_berkas_pendaftaran');
    }
};
