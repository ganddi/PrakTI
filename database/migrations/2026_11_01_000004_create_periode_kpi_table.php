<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.5 Periode KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_periode', 20)->unique();
            $table->string('nama_periode', 100);
            $table->year('tahun');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_kpi');
    }
};
