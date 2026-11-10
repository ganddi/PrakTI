<?php

use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\Mahasiswa\LogbookController;
use App\Http\Controllers\Mahasiswa\PengajuanController as MahasiswaPengajuanController;
use App\Http\Controllers\Mahasiswa\ProgressController;
use App\Http\Controllers\Pembimbing\BimbinganController;
use App\Http\Controllers\Pembimbing\VerifikasiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

/*
| Fitur 1 & 2: Register dan Login
*/
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'role:admin,mahasiswa,pembimbing'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Fitur 3: Dashboard sesuai peran
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Unduh dokumen & lampiran (hak akses dicek di controller)
    Route::get('/dokumen/{dokumen}/unduh', [DokumenController::class, 'unduh'])->name('dokumen.unduh');
    Route::get('/lampiran/{tipe}/{id}', [DokumenController::class, 'lampiran'])
        ->whereIn('tipe', ['logbook', 'progress'])->whereNumber('id')->name('lampiran.unduh');

    // Komentar/konsultasi pada logbook & progress (mahasiswa <-> pembimbing)
    Route::post('/komentar/{tipe}/{id}', [KomentarController::class, 'store'])
        ->whereIn('tipe', ['logbook', 'progress'])->whereNumber('id')->name('komentar.store');

    /*
    | ADMIN — Fitur 4 (kelola pengguna), 6 (verifikasi pengajuan), 8 (monitoring)
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('pengguna', PenggunaController::class)
            ->except('show')->parameters(['pengguna' => 'pengguna']);
        Route::patch('pengguna/{pengguna}/aktivasi', [PenggunaController::class, 'aktivasi'])->name('pengguna.aktivasi');

        Route::controller(AdminPengajuanController::class)->group(function () {
            Route::get('pengajuan', 'index')->name('pengajuan.index');
            Route::get('pengajuan/{pengajuan}', 'show')->name('pengajuan.show');
            Route::patch('pengajuan/{pengajuan}/verifikasi', 'verifikasi')->name('pengajuan.verifikasi');
            Route::patch('pengajuan/{pengajuan}/pembimbing', 'gantiPembimbing')->name('pengajuan.pembimbing');
            Route::patch('pengajuan/{pengajuan}/selesai', 'selesai')->name('pengajuan.selesai');
        });

        Route::get('monitoring', MonitoringController::class)->name('monitoring');
    });

    /*
    | MAHASISWA — Fitur 5 (pengajuan), 7 (logbook), 8 (progress)
    */
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::controller(MahasiswaPengajuanController::class)->group(function () {
            Route::get('pengajuan', 'index')->name('pengajuan.index');
            Route::get('pengajuan/buat', 'create')->name('pengajuan.create');
            Route::post('pengajuan', 'store')->name('pengajuan.store');
            Route::post('pengajuan/{pengajuan}/dokumen', 'unggahDokumen')->name('pengajuan.dokumen');
        });

        Route::resource('logbook', LogbookController::class)->except('show');

        Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::post('progress', [ProgressController::class, 'store'])->name('progress.store');
    });

    /*
    | PEMBIMBING — Fitur 7 (lihat logbook), 9 (verifikasi & feedback)
    */
    Route::middleware('role:pembimbing')->prefix('pembimbing')->name('pembimbing.')->group(function () {
        Route::get('bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
        Route::get('bimbingan/{pengajuan}', [BimbinganController::class, 'show'])->name('bimbingan.show');

        Route::controller(VerifikasiController::class)->group(function () {
            Route::patch('logbook/{logbook}/verifikasi', 'logbook')->name('logbook.verifikasi');
            Route::patch('bimbingan/{pengajuan}/setujui-logbook', 'setujuiSemuaLogbook')->name('logbook.setujui-semua');
            Route::patch('progress/{progress}/verifikasi', 'progress')->name('progress.verifikasi');
        });
    });
});
