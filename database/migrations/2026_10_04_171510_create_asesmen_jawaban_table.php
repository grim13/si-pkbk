<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Transaction table storing individual answers per question per asesmen.
     */
    public function up(): void
    {
        Schema::create('asesmen_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->cascadeOnDelete();
            $table->foreignId('asesmen_pertanyaan_id')->constrained('asesmen_pertanyaan')->cascadeOnDelete();
            $table->boolean('jawaban_ya_tidak')->nullable()->comment('For tipe_jawaban = ya_tidak');
            $table->text('jawaban_text')->nullable()->comment('For tipe_jawaban = text');
            $table->decimal('jawaban_number', 8, 2)->nullable()->comment('For tipe_jawaban = number (kg, cm, etc.)');
            $table->timestamps();

            $table->unique(['asesmen_id', 'asesmen_pertanyaan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_jawaban');
    }
};
