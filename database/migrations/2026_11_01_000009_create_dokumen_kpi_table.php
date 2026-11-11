<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.10 Dokumen KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id');
            $table->unsignedInteger('pengajuan_id');
            $table->string('jenis_dokumen', 50);
            $table->string('nama_file', 255);
            $table->string('format_file', 20);
            $table->unsignedInteger('ukuran_file');
            $table->string('path_file', 255);
            $table->string('status_verifikasi', 20); // menunggu | diverifikasi | revisi | tersimpan
            $table->text('catatan')->nullable();
            $table->dateTime('uploaded_at');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('pengajuan_id')->references('id')->on('pengajuan_kpi')->cascadeOnDelete();
            $table->index(['jenis_dokumen', 'status_verifikasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_kpi');
    }
};
