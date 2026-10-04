<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds complex fields for "Masalah Setelah Kelahiran" and "Riwayat Perkembangan Masa Balita"
     * sections that don't fit the generic pertanyaan/jawaban pattern.
     */
    public function up(): void
    {
        Schema::table('asesmen', function (Blueprint $table) {
            // Masalah Setelah Kelahiran – kuning
            $table->boolean('bayi_kuning')->nullable()->after('keluhan_hambatan');
            $table->string('bayi_kuning_penanganan')->nullable()->comment('dijemur/uv/transfusi')->after('bayi_kuning');
            $table->string('bayi_kuning_uv_hari')->nullable()->comment('lama disinar UV dalam hari')->after('bayi_kuning_penanganan');

            // Masalah Setelah Kelahiran – ASI
            $table->boolean('bayi_hisap_asi')->nullable()->after('bayi_kuning_uv_hari');
            $table->string('lama_asi_bulan')->nullable()->after('bayi_hisap_asi');
            $table->string('lama_asi_lainnya')->nullable()->after('lama_asi_bulan');

            // Masalah Setelah Kelahiran – Masalah 1 Bulan Pertama
            $table->boolean('masalah_kejang_stuip')->nullable()->after('lama_asi_lainnya');
            $table->text('masalah_kejang_stuip_ket')->nullable()->after('masalah_kejang_stuip');
            $table->boolean('masalah_diare')->nullable()->after('masalah_kejang_stuip_ket');
            $table->text('masalah_diare_ket')->nullable()->after('masalah_diare');
            $table->boolean('masalah_lainnya')->nullable()->after('masalah_diare_ket');
            $table->text('masalah_lainnya_ket')->nullable()->after('masalah_lainnya');

            // Riwayat Perkembangan Masa Balita – tambahan
            $table->json('hal_hal_menyolok')->nullable()->after('masalah_lainnya_ket')
                ->comment('Array of selected traits from the checkbox list');
            $table->text('catatan_tambahan_balita')->nullable()->after('hal_hal_menyolok');
            $table->string('pendapat_sekolah')->nullable()->after('catatan_tambahan_balita')
                ->comment('ya/ragu-ragu/tidak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmen', function (Blueprint $table) {
            $table->dropColumn([
                'bayi_kuning',
                'bayi_kuning_penanganan',
                'bayi_kuning_uv_hari',
                'bayi_hisap_asi',
                'lama_asi_bulan',
                'lama_asi_lainnya',
                'masalah_kejang_stuip',
                'masalah_kejang_stuip_ket',
                'masalah_diare',
                'masalah_diare_ket',
                'masalah_lainnya',
                'masalah_lainnya_ket',
                'hal_hal_menyolok',
                'catatan_tambahan_balita',
                'pendapat_sekolah',
            ]);
        });
    }
};
