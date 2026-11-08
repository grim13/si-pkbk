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
            $table->string('status_pendaftaran')->default('draft')->change();
            $table->text('keterangan_pengajuan_lepas_mandiri')->nullable()->after('tanggal_mulai_diterima');
            $table->dateTime('tanggal_pengajuan_lepas_mandiri')->nullable()->after('keterangan_pengajuan_lepas_mandiri');
            $table->text('keterangan_keputusan_lepas_mandiri')->nullable()->after('tanggal_pengajuan_lepas_mandiri');
            $table->dateTime('tanggal_lepas_mandiri')->nullable()->after('keterangan_keputusan_lepas_mandiri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_peserta', function (Blueprint $table) {
            $table->dropColumn([
                'keterangan_pengajuan_lepas_mandiri',
                'tanggal_pengajuan_lepas_mandiri',
                'keterangan_keputusan_lepas_mandiri',
                'tanggal_lepas_mandiri',
            ]);
        });
    }
};
