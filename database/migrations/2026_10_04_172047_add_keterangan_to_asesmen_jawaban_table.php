<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a keterangan column so ya_tidak answers can also carry
     * a free-text note (as used in the Riwayat Perkembangan section).
     */
    public function up(): void
    {
        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('jawaban_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
