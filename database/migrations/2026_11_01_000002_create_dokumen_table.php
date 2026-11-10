<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_kpi_id')->constrained('pengajuan_kpi')->cascadeOnDelete();
            $table->string('jenis', 30); // surat_permohonan, proposal, surat_balasan, laporan_akhir, lainnya
            $table->string('nama_asli');
            $table->string('path');      // disimpan di storage privat, diunduh lewat route yang dicek hak aksesnya
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
