<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.2 Mahasiswa
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('nim', 20)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('email', 100);
            $table->string('no_hp', 15)->nullable();
            $table->string('program_studi', 100);
            $table->string('fakultas', 100);
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
