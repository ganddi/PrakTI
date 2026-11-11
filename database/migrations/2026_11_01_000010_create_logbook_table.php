<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.11 Logbook
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mahasiswa_id');
            $table->date('tanggal');
            $table->string('judul_kegiatan', 100);
            $table->text('deskripsi_kegiatan');
            $table->text('hasil_kegiatan')->nullable();
            $table->text('kendala')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu | disetujui | revisi
            $table->text('catatan_dosen')->nullable();
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete();
            $table->unique(['mahasiswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook');
    }
};
