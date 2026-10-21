<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instruktur\PembelajaranController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KepalaSeksi\KelulusanController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\PekerjaSosial\AsesmenController;
use App\Http\Controllers\PekerjaSosial\PembelajaranApprovalController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\RoleMasterController;
use App\Http\Controllers\UserMasterController;
use App\Http\Controllers\WaliMasterController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::middleware(['can:access.manage'])->group(function () {
        Route::get('/user-master', [UserMasterController::class, 'index'])->name('user-master.index');
        Route::post('/user-master', [UserMasterController::class, 'store'])->name('user-master.store');
        Route::put('/user-master/{user}', [UserMasterController::class, 'update'])->name('user-master.update');
        Route::delete('/user-master/{user}', [UserMasterController::class, 'destroy'])->name('user-master.destroy');

        Route::get('/role-master', [RoleMasterController::class, 'index'])->name('role-master.index');
        Route::post('/role-master', [RoleMasterController::class, 'store'])->name('role-master.store');
        Route::put('/role-master/{role}', [RoleMasterController::class, 'update'])->name('role-master.update');
        Route::delete('/role-master/{role}', [RoleMasterController::class, 'destroy'])->name('role-master.destroy');
    });

    Route::middleware(['can:pendaftaran.manage'])->group(function () {
        Route::get('/admin/pendaftaran', [App\Http\Controllers\Admin\PendaftaranController::class, 'index'])->name('admin.pendaftaran.index');
        Route::get('/admin/pendaftaran/{pendaftaran}', [App\Http\Controllers\Admin\PendaftaranController::class, 'show'])->name('admin.pendaftaran.show');
        Route::post('/admin/pendaftaran/{pendaftaran}/verify', [App\Http\Controllers\Admin\PendaftaranController::class, 'verify'])->name('admin.pendaftaran.verify');
    });

    Route::middleware(['can:wali.manage'])->group(function () {
        Route::get('/wali-master', [WaliMasterController::class, 'index'])->name('wali-master.index');
        Route::put('/wali-master/{wali}', [WaliMasterController::class, 'update'])->name('wali-master.update');
        Route::delete('/wali-master/{wali}', [WaliMasterController::class, 'destroy'])->name('wali-master.destroy');
    });

    Route::middleware(['can:mata_pelajaran.manage'])->group(function () {
        Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran.index');
        Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
        Route::put('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
        Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');
    });

    Route::middleware(['can:jadwal.manage'])->group(function () {
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/cetak', [JadwalController::class, 'cetak'])->name('jadwal.cetak');
        Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::put('/jadwal/{jadwal}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    });

    Route::middleware(['role:wali'])->group(function () {
        Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::post('/pendaftaran/step2', [PendaftaranController::class, 'storeDraft'])->name('pendaftaran.store-draft');
        Route::post('/pendaftaran/step3', [PendaftaranController::class, 'uploadBerkas'])->name('pendaftaran.upload-berkas');
        Route::post('/pendaftaran/finalize', [PendaftaranController::class, 'finalize'])->name('pendaftaran.finalize');
    });

    Route::middleware(['can:asesmen.do'])->group(function () {
        Route::get('/pekerja-sosial/asesmen', [AsesmenController::class, 'index'])->name('pekerja-sosial.asesmen.index');
        Route::get('/pekerja-sosial/asesmen/{pendaftaran}', [AsesmenController::class, 'show'])->name('pekerja-sosial.asesmen.show');
        Route::post('/pekerja-sosial/asesmen/{pendaftaran}', [AsesmenController::class, 'store'])->name('pekerja-sosial.asesmen.store');
    });

    Route::middleware(['can:pembelajaran.approve'])->group(function () {
        Route::get('/pekerja-sosial/pembelajaran', [PembelajaranApprovalController::class, 'index'])->name('pekerja-sosial.pembelajaran.index');
        Route::get('/pekerja-sosial/pembelajaran/{pertemuan}', [PembelajaranApprovalController::class, 'show'])->name('pekerja-sosial.pembelajaran.show');
        Route::post('/pekerja-sosial/pembelajaran/{pertemuan}', [PembelajaranApprovalController::class, 'update'])->name('pekerja-sosial.pembelajaran.update');
    });

    Route::middleware(['can:pembelajaran.do'])->group(function () {
        Route::get('/instruktur/pembelajaran', [PembelajaranController::class, 'index'])->name('instruktur.pembelajaran.index');
        Route::get('/instruktur/pembelajaran/jadwal/{jadwal}', [PembelajaranController::class, 'show'])->name('instruktur.pembelajaran.show');
        Route::post('/instruktur/pembelajaran/jadwal/{jadwal}', [PembelajaranController::class, 'store'])->name('instruktur.pembelajaran.store');
        Route::post('/instruktur/pembelajaran/{pertemuan}/ajukan', [PembelajaranController::class, 'ajukan'])->name('instruktur.pembelajaran.ajukan');
    });

    Route::middleware(['can:pendaftaran.kelulusan'])->group(function () {
        Route::get('/kepala-seksi/kelulusan', [KelulusanController::class, 'index'])->name('kepala-seksi.kelulusan.index');
        Route::get('/kepala-seksi/kelulusan/{pendaftaran}', [KelulusanController::class, 'show'])->name('kepala-seksi.kelulusan.show');
        Route::post('/kepala-seksi/kelulusan/{pendaftaran}', [KelulusanController::class, 'update'])->name('kepala-seksi.kelulusan.update');
    });
});

require __DIR__.'/settings.php';
