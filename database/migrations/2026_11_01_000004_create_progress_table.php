<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_kpi_id')->constrained('pengajuan_kpi')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->unsignedTinyInteger('persentase')->default(0); // 0 - 100
            $table->string('lampiran')->nullable();
            $table->enum('status', ['menunggu', 'diverifikasi', 'revisi'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
