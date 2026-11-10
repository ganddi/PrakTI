<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_kpi_id')->constrained('pengajuan_kpi')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('kegiatan');
            $table->string('lampiran')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'revisi'])->default('menunggu');
            $table->timestamps();

            $table->unique(['pengajuan_kpi_id', 'tanggal']); // satu logbook per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
