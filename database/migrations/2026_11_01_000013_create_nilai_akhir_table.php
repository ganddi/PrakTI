<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.14 Nilai Akhir
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_akhir', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id')->unique();
            $table->decimal('nilai_dosen_pembimbing', 5, 2);
            $table->decimal('nilai_dosen_penguji', 5, 2)->nullable();
            $table->decimal('nilai_pembimbing_lapangan', 5, 2)->nullable();
            $table->decimal('bobot_dosen_pembimbing', 5, 2)->nullable();
            $table->decimal('bobot_dosen_penguji', 5, 2)->nullable();
            $table->decimal('bobot_pembimbing_lapangan', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2);
            $table->string('predikat', 20)->nullable();
            $table->string('status', 20)->default('dihitung'); // dihitung | diverifikasi
            $table->date('tanggal_hitung');
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
    }
};
