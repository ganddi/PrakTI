<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.9 Pengajuan KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id');
            $table->unsignedInteger('periode_id');
            $table->unsignedInteger('tempat_kpi_id');
            $table->date('tanggal_pengajuan');
            $table->text('keperluan');
            $table->string('status', 20)->default('diajukan'); // diajukan | revisi | disetujui | berjalan | selesai
            $table->text('catatan_admin')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('periode_id')->references('id')->on('periode_kpi');
            $table->foreign('tempat_kpi_id')->references('id')->on('tempat_kpi');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kpi');
    }
};
