<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.7 Anggota Kelompok
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_kelompok', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kelompok_id');
            $table->unsignedInteger('mahasiswa_id');
            $table->date('tanggal_masuk');
            $table->string('status', 20)->default('aktif'); // aktif | keluar
            $table->timestamps();
            $table->foreign('kelompok_id')->references('id')->on('kelompok_kpi')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_kelompok');
    }
};
