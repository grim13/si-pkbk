<?php

use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\RoleMasterController;
use App\Http\Controllers\UserMasterController;
use App\Http\Controllers\WaliMasterController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

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
        Route::get('/admin/pendaftaran', [\App\Http\Controllers\Admin\PendaftaranController::class, 'index'])->name('admin.pendaftaran.index');
        Route::get('/admin/pendaftaran/{pendaftaran}', [\App\Http\Controllers\Admin\PendaftaranController::class, 'show'])->name('admin.pendaftaran.show');
        Route::post('/admin/pendaftaran/{pendaftaran}/verify', [\App\Http\Controllers\Admin\PendaftaranController::class, 'verify'])->name('admin.pendaftaran.verify');
    });

    Route::middleware(['can:wali.manage'])->group(function () {
        Route::get('/wali-master', [WaliMasterController::class, 'index'])->name('wali-master.index');
        Route::put('/wali-master/{wali}', [WaliMasterController::class, 'update'])->name('wali-master.update');
        Route::delete('/wali-master/{wali}', [WaliMasterController::class, 'destroy'])->name('wali-master.destroy');
    });

    Route::middleware(['role:wali'])->group(function () {
        Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::post('/pendaftaran/step2', [PendaftaranController::class, 'storeDraft'])->name('pendaftaran.store-draft');
        Route::post('/pendaftaran/step3', [PendaftaranController::class, 'uploadBerkas'])->name('pendaftaran.upload-berkas');
        Route::post('/pendaftaran/finalize', [PendaftaranController::class, 'finalize'])->name('pendaftaran.finalize');
    });

    Route::middleware(['can:asesmen.do'])->group(function () {
        Route::get('/pekerja-sosial/asesmen', [\App\Http\Controllers\PekerjaSosial\AsesmenController::class, 'index'])->name('pekerja-sosial.asesmen.index');
        Route::get('/pekerja-sosial/asesmen/{pendaftaran}', [\App\Http\Controllers\PekerjaSosial\AsesmenController::class, 'show'])->name('pekerja-sosial.asesmen.show');
        Route::post('/pekerja-sosial/asesmen/{pendaftaran}', [\App\Http\Controllers\PekerjaSosial\AsesmenController::class, 'store'])->name('pekerja-sosial.asesmen.store');
    });

    Route::middleware(['can:pendaftaran.kelulusan'])->group(function () {
        Route::get('/kepala-seksi/kelulusan', [\App\Http\Controllers\KepalaSeksi\KelulusanController::class, 'index'])->name('kepala-seksi.kelulusan.index');
        Route::get('/kepala-seksi/kelulusan/{pendaftaran}', [\App\Http\Controllers\KepalaSeksi\KelulusanController::class, 'show'])->name('kepala-seksi.kelulusan.show');
        Route::post('/kepala-seksi/kelulusan/{pendaftaran}', [\App\Http\Controllers\KepalaSeksi\KelulusanController::class, 'update'])->name('kepala-seksi.kelulusan.update');
    });
});

require __DIR__.'/settings.php';
