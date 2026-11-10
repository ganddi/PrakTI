<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_kpi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->constrained('users')->nullOnDelete();

            // Data instansi / tempat KPI
            $table->string('nama_instansi');
            $table->text('alamat_instansi');
            $table->string('bidang_kerja');
            $table->string('pembimbing_lapangan')->nullable();
            $table->string('kontak_instansi', 50)->nullable();

            // Periode pelaksanaan
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('deskripsi')->nullable();

            // Alur: diajukan -> berjalan (disetujui) / ditolak -> selesai
            $table->enum('status', ['diajukan', 'ditolak', 'berjalan', 'selesai'])->default('diajukan');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kpi');
    }
};
