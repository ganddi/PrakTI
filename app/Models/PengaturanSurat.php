<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSurat extends Model
{
    protected $table = 'pengaturan_surat';

    public $incrementing = false;

    protected $fillable = [
        'kode_nomor', 'nomor_terakhir', 'tahun_nomor', 'nama_penandatangan', 'nip_penandatangan',
        'pangkat_golongan', 'jabatan_penandatangan', 'kota_surat',
    ];

    protected function casts(): array
    {
        return ['nomor_terakhir' => 'integer', 'tahun_nomor' => 'integer'];
    }
}
