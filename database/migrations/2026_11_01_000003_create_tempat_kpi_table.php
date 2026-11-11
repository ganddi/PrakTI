<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SDD Tabel 3.4 Tempat KPI
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempat_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_instansi', 100);
            $table->string('jenis_instansi', 50);
            $table->text('alamat');
            $table->string('kota', 50);
            $table->string('provinsi', 50);
            $table->string('nama_pic', 100)->nullable();
            $table->string('email_pic', 100)->nullable();
            $table->string('no_hp_pic', 15)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif | usulan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tempat_kpi');
    }
};
