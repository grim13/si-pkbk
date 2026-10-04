<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds additional fields to wali table based on the "Keluarga"
     * section of the Form Asesmen Klien.
     */
    public function up(): void
    {
        Schema::table('wali', function (Blueprint $table) {
            $table->string('agama')->nullable()->after('tanggal_lahir');
            $table->string('pendidikan_terakhir')->nullable()->after('agama');
            $table->string('status_dalam_keluarga')->nullable()->after('pendidikan_terakhir');
            $table->string('pekerjaan')->nullable()->after('status_dalam_keluarga');
            $table->decimal('penghasilan_perbulan', 12, 2)->nullable()->after('pekerjaan');
            $table->integer('tanggungan_keluarga')->nullable()->after('penghasilan_perbulan');
            $table->string('rumah_tempat_tinggal')->nullable()->after('tanggungan_keluarga');
            $table->text('keadaan_lingkungan')->nullable()->after('rumah_tempat_tinggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wali', function (Blueprint $table) {
            $table->dropColumn([
                'agama',
                'pendidikan_terakhir',
                'status_dalam_keluarga',
                'pekerjaan',
                'penghasilan_perbulan',
                'tanggungan_keluarga',
                'rumah_tempat_tinggal',
                'keadaan_lingkungan',
            ]);
        });
    }
};
