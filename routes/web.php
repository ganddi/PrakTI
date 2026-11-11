<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\EksporController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Pembimbing;
use App\Http\Controllers\PengaturanController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('beranda');

Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'role:admin,mahasiswa,pembimbing'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Pengaturan akun (menu "Pengaturan" di mockup SDD)
    Route::controller(PengaturanController::class)->group(function () {
        Route::get('/pengaturan', 'edit')->name('pengaturan');
        Route::put('/pengaturan/profil', 'profil')->name('pengaturan.profil');
        Route::put('/pengaturan/kata-sandi', 'kataSandi')->name('pengaturan.sandi');
    });

    // Unduh berkas, hak akses diperiksa di controller (SDD Bab V: Document Access Control)
    Route::controller(DokumenController::class)->group(function () {
        Route::get('/dokumen/{dokumen}/unduh', 'unduh')->name('dokumen.unduh');
        Route::get('/laporan/{laporan}/berkas', 'laporan')->name('laporan.berkas');
        Route::get('/template/{kode}', 'template')->name('template.unduh');
    });

    Route::controller(EksporController::class)->group(function () {
        Route::get('/kpi/{pengajuan}/logbook/cetak', 'logbookCetak')->name('ekspor.logbook.cetak');
        Route::get('/kpi/{pengajuan}/logbook/excel', 'logbookCsv')->name('ekspor.logbook.csv');
        Route::get('/kpi/{pengajuan}/nilai-akhir/cetak', 'nilaiAkhirCetak')->name('ekspor.nilai-akhir');
    });

    Route::controller(NotifikasiController::class)->group(function () {
        Route::get('/notifikasi', 'index')->name('notifikasi.index');
        Route::get('/notifikasi/{notifikasi}', 'buka')->name('notifikasi.buka');
        Route::post('/notifikasi/baca-semua', 'bacaSemua')->name('notifikasi.baca-semua');
    });

    /* ADMIN */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('pengguna', Admin\PenggunaController::class)->except('show')->parameters(['pengguna' => 'pengguna']);
        Route::patch('pengguna/{pengguna}/aktivasi', [Admin\PenggunaController::class, 'aktivasi'])->name('pengguna.aktivasi');

        Route::controller(Admin\PengajuanController::class)->group(function () {
            Route::get('pengajuan', 'index')->name('pengajuan.index');
            Route::get('pengajuan/{pengajuan}', 'show')->name('pengajuan.show');
            Route::patch('pengajuan/{pengajuan}/verifikasi', 'verifikasi')->name('pengajuan.verifikasi');
        });

        Route::get('verifikasi-dokumen', [Admin\VerifikasiDokumenController::class, 'index'])->name('verifikasi.index');
        Route::patch('verifikasi-dokumen/{dokumen}', [Admin\VerifikasiDokumenController::class, 'update'])->name('verifikasi.update');

        Route::resource('periode', Admin\PeriodeController::class)->except(['create', 'edit'])->parameters(['periode' => 'periode']);
        Route::controller(Admin\KelompokController::class)->group(function () {
            Route::post('periode/{periode}/kelompok', 'store')->name('kelompok.store');
            Route::post('periode/{periode}/kelompok-otomatis', 'otomatis')->name('kelompok.otomatis');
            Route::get('kelompok/{kelompok}', 'show')->name('kelompok.show');
            Route::put('kelompok/{kelompok}', 'update')->name('kelompok.update');
            Route::delete('kelompok/{kelompok}', 'destroy')->name('kelompok.destroy');
            Route::post('kelompok/{kelompok}/anggota', 'tambahAnggota')->name('kelompok.anggota.tambah');
            Route::delete('anggota/{anggota}', 'hapusAnggota')->name('kelompok.anggota.hapus');
        });

        Route::controller(Admin\PenempatanController::class)->group(function () {
            Route::get('penempatan', 'index')->name('penempatan.index');
            Route::post('kelompok/{kelompok}/tawarkan', 'tawarkan')->name('penempatan.tawarkan');
            Route::patch('penempatan/{penempatan}/verifikasi', 'verifikasi')->name('penempatan.verifikasi');
            Route::patch('penempatan/{penempatan}/batalkan', 'batalkan')->name('penempatan.batalkan');
        });

        Route::get('monitoring', Admin\MonitoringController::class)->name('monitoring');
        Route::get('rekap-nilai', [Admin\RekapController::class, 'index'])->name('rekap.index');
        Route::get('rekap-nilai/ekspor', [Admin\RekapController::class, 'ekspor'])->name('rekap.ekspor');
        Route::get('statistik', Admin\StatistikController::class)->name('statistik');

        Route::get('template', [Admin\TemplateController::class, 'index'])->name('template.index');
        Route::post('template', [Admin\TemplateController::class, 'store'])->name('template.store');
        Route::delete('template/{kode}', [Admin\TemplateController::class, 'destroy'])->name('template.destroy');

        Route::resource('tempat', Admin\TempatKpiController::class)->except('show')->parameters(['tempat' => 'tempat']);
        Route::get('aktivitas', Admin\AktivitasController::class)->name('aktivitas');
    });

    /* MAHASISWA */
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::controller(Mahasiswa\PengajuanController::class)->group(function () {
            Route::get('pengajuan', 'index')->name('pengajuan.index');
            Route::get('pengajuan/buat', 'create')->name('pengajuan.create');
            Route::post('pengajuan', 'store')->name('pengajuan.store');
            Route::get('pengajuan/{pengajuan}/perbaiki', 'edit')->name('pengajuan.edit');
            Route::put('pengajuan/{pengajuan}', 'update')->name('pengajuan.update');
            Route::post('pengajuan/{pengajuan}/surat-balasan', 'suratBalasan')->name('pengajuan.surat-balasan');
            Route::post('pengajuan/{pengajuan}/dokumen', 'unggahDokumen')->name('pengajuan.dokumen');
        });

        Route::get('kerja-praktik', Mahasiswa\InformasiController::class)->name('informasi');
        Route::resource('logbook', Mahasiswa\LogbookController::class)->except('show');
        Route::post('logbook-dokumen', [Mahasiswa\LogbookController::class, 'unggahDokumen'])->name('logbook.dokumen');
        Route::get('progress', Mahasiswa\ProgressController::class)->name('progress');
        Route::get('laporan', [Mahasiswa\LaporanController::class, 'index'])->name('laporan.index');
        Route::post('laporan', [Mahasiswa\LaporanController::class, 'store'])->name('laporan.store');

        Route::controller(Mahasiswa\PenilaianController::class)->group(function () {
            Route::get('penilaian', 'index')->name('penilaian.index');
            Route::post('penilaian', 'eksternal')->name('penilaian.eksternal');
            Route::get('rekapitulasi-nilai', 'nilai')->name('nilai.index');
            Route::post('rekapitulasi-nilai', 'unggahNilaiAkhir')->name('nilai.unggah');
        });
    });

    /* DOSEN PEMBIMBING */
    Route::middleware('role:pembimbing')->prefix('pembimbing')->name('pembimbing.')->group(function () {
        Route::get('penempatan', [Pembimbing\PenempatanController::class, 'index'])->name('penempatan.index');
        Route::post('penempatan', [Pembimbing\PenempatanController::class, 'ajukan'])->name('penempatan.ajukan');

        Route::controller(Pembimbing\BimbinganController::class)->group(function () {
            Route::get('kerja-praktik', 'index')->name('bimbingan.index');
            Route::get('kerja-praktik/{pengajuan}', 'show')->name('bimbingan.show');
            Route::get('dokumen', 'dokumen')->name('dokumen');
            Route::get('riwayat-aktivitas', 'riwayat')->name('riwayat');
        });

        Route::patch('logbook/{logbook}/verifikasi', [Pembimbing\LogbookController::class, 'verifikasi'])->name('logbook.verifikasi');
        Route::patch('kerja-praktik/{pengajuan}/setujui-logbook', [Pembimbing\LogbookController::class, 'setujuiSemua'])->name('logbook.setujui-semua');
        Route::get('logbook', [Pembimbing\LogbookController::class, 'index'])->name('logbook.index');

        Route::controller(Pembimbing\LaporanController::class)->group(function () {
            Route::get('laporan', 'index')->name('laporan.index');
            Route::get('laporan/{laporan}', 'show')->name('laporan.show');
            Route::post('laporan/{laporan}/tanda', 'tambahTanda')->name('laporan.tanda');
            Route::patch('laporan/{laporan}/tanda/{indeks}', 'ubahTanda')->whereNumber('indeks')->name('laporan.tanda.ubah');
            Route::delete('laporan/{laporan}/tanda/{indeks}', 'hapusTanda')->whereNumber('indeks')->name('laporan.tanda.hapus');
            Route::patch('laporan/{laporan}/keputusan', 'putuskan')->name('laporan.putuskan');
        });

        Route::controller(Pembimbing\PenilaianController::class)->group(function () {
            Route::get('nilai-mahasiswa', 'index')->name('penilaian.index');
            Route::get('nilai-mahasiswa/{pengajuan}', 'edit')->name('penilaian.edit');
            Route::put('nilai-mahasiswa/{pengajuan}', 'update')->name('penilaian.update');
        });
    });
});
