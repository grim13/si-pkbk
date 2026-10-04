<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Master data for assessment questions within each section.
     * Supports three answer types: ya_tidak (boolean), text, and number.
     */
    public function up(): void
    {
        Schema::create('asesmen_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_seksi_id')->constrained('asesmen_seksi')->cascadeOnDelete();
            $table->integer('nomor');
            $table->text('pertanyaan');
            $table->enum('tipe_jawaban', ['ya_tidak', 'text', 'number'])->default('ya_tidak');
            $table->string('satuan')->nullable()->comment('e.g.: kg, cm');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_pertanyaan');
    }
};
