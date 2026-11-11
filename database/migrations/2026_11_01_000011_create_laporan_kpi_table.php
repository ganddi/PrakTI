<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.12 Laporan KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id');
            $table->string('nama_file', 255);
            $table->string('path_file', 255);
            $table->string('versi', 20);
            $table->string('status', 20)->default('menunggu'); // menunggu | revisi | disetujui
            $table->text('komentar_dosen')->nullable();
            $table->text('halaman_revisi')->nullable(); // daftar bagian yang ditandai (JSON): halaman, kutipan, catatan, status
            $table->dateTime('tanggal_upload');
            $table->dateTime('diperiksa_at')->nullable();
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kpi');
    }
};
