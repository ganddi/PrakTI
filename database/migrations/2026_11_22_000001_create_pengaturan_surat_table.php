<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_surat', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('kode_nomor', 80)->default('UN55.F4/KM.22');
            $table->unsignedInteger('nomor_terakhir')->default(0);
            $table->unsignedSmallInteger('tahun_nomor');
            $table->string('nama_penandatangan', 150)->default('Muh. Fahmi Rustan, S.Kom., M.T.');
            $table->string('nip_penandatangan', 40)->default('19911227 201903 1 010');
            $table->string('pangkat_golongan', 100)->default('Penata/III.c');
            $table->string('jabatan_penandatangan', 100)->default('Wakil Dekan III');
            $table->string('kota_surat', 80)->default('Majene');
            $table->timestamps();
        });

        DB::table('pengaturan_surat')->insert([
            'id' => 1,
            'kode_nomor' => 'UN55.F4/KM.22',
            'nomor_terakhir' => 0,
            'tahun_nomor' => (int) now()->format('Y'),
            'nama_penandatangan' => 'Muh. Fahmi Rustan, S.Kom., M.T.',
            'nip_penandatangan' => '19911227 201903 1 010',
            'pangkat_golongan' => 'Penata/III.c',
            'jabatan_penandatangan' => 'Wakil Dekan III',
            'kota_surat' => 'Majene',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_surat');
    }
};
