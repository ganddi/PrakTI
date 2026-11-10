<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menggantikan migrasi users bawaan Laravel.
 * Tambahan: role (admin/mahasiswa/pembimbing), nomor_induk (NIM/NIDN), no_hp, is_active.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'mahasiswa', 'pembimbing'])->default('mahasiswa');
            $table->string('nomor_induk', 30)->nullable()->unique(); // NIM untuk mahasiswa, NIDN/NIP untuk dosen
            $table->string('no_hp', 20)->nullable();
            $table->boolean('is_active')->default(true);           // akun dosen aktif setelah disetujui admin
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
