<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pertemuan_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal')->cascadeOnDelete();
            $table->foreignId('instruktur_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->index();
            $table->enum('status', ['draft', 'pengajuan', 'perbaikan', 'acc'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->text('catatan_pekerja_sosial')->nullable();
            $table->foreignId('pekerja_sosial_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'tanggal'], 'pertemuan_jadwal_tanggal_unique');
        });

        Schema::create('pertemuan_pembelajaran_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_pembelajaran_id')->constrained('pertemuan_pembelajaran')->cascadeOnDelete();
            $table->foreignId('pendaftaran_peserta_id')->constrained('pendaftaran_peserta')->cascadeOnDelete();
            $table->enum('presensi', ['hadir', 'izin', 'absen']);
            $table->enum('nilai', ['sangat_baik', 'baik', 'cukup', 'kurang'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_pembelajaran_id', 'pendaftaran_peserta_id'], 'pertemuan_peserta_unique');
        });

        // Backfill tanggal_mulai_diterima jika ada peserta diterima sebelum kolom ditambahkan
        DB::table('pendaftaran_peserta')
            ->where('status_pendaftaran', 'diterima')
            ->whereNull('tanggal_mulai_diterima')
            ->update([
                'tanggal_mulai_diterima' => DB::raw('COALESCE(updated_at, created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan_pembelajaran_peserta');
        Schema::dropIfExists('pertemuan_pembelajaran');
    }
};
