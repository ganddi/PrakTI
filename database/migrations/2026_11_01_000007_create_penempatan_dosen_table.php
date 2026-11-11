<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.8 Penempatan Dosen
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penempatan_dosen', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dosen_id');
            $table->unsignedInteger('tempat_kpi_id');
            $table->unsignedInteger('kelompok_id');
            $table->date('tanggal_pengajuan');
            $table->string('status', 20)->default('ditawarkan'); // ditawarkan | diajukan | disetujui | perlu_perbaikan | dibatalkan
            $table->text('catatan_admin')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->foreign('dosen_id')->references('id')->on('dosen_pembimbing')->cascadeOnDelete();
            $table->foreign('tempat_kpi_id')->references('id')->on('tempat_kpi')->cascadeOnDelete();
            $table->foreign('kelompok_id')->references('id')->on('kelompok_kpi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan_dosen');
    }
};
