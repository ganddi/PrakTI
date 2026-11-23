<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_permohonan_kpi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengajuan_id')->unique();
            $table->unsignedInteger('nomor_urut');
            $table->string('kode_nomor', 80);
            $table->unsignedSmallInteger('tahun_nomor');
            $table->string('nomor_surat', 150)->unique();
            $table->string('penerima', 150)->default('Pimpinan');
            $table->string('nama_instansi', 150);
            $table->string('kota_instansi', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('tanggal_surat');
            $table->string('kota_surat', 80);
            $table->string('nama_penandatangan', 150);
            $table->string('nip_penandatangan', 40);
            $table->string('pangkat_golongan', 100);
            $table->string('jabatan_penandatangan', 100);
            $table->json('anggota');
            $table->unsignedInteger('dibuat_oleh')->nullable();
            $table->timestamps();
            $table->foreign('pengajuan_id')->references('id')->on('pengajuan_kpi')->cascadeOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_permohonan_kpi');
    }
};
