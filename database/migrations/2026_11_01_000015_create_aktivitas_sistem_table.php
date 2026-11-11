<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.16 Aktivitas Sistem
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_sistem', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('aktivitas');
            $table->string('modul', 50);
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('referensi_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['modul', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_sistem');
    }
};
