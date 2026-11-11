<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.6 Kelompok KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('periode_id');
            $table->string('kode_kelompok', 20)->unique();
            $table->string('nama_kelompok', 100);
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
            $table->foreign('periode_id')->references('id')->on('periode_kpi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_kpi');
    }
};
