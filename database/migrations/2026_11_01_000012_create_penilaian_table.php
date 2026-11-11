<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.13 Penilaian
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id');
            $table->string('jenis_penilaian', 50); // dosen_pembimbing | dosen_penguji | pembimbing_lapangan
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->date('tanggal_penilaian');
            $table->string('status', 20); // draf | final | diverifikasi | revisi
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
            $table->unique(['mahasiswa_id', 'jenis_penilaian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
