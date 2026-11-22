<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPermohonanKpi extends Model
{
    protected $table = 'surat_permohonan_kpi';

    protected $fillable = [
        'pengajuan_id', 'nomor_urut', 'kode_nomor', 'tahun_nomor', 'nomor_surat', 'penerima', 'nama_instansi', 'kota_instansi',
        'tanggal_mulai', 'tanggal_selesai', 'tanggal_surat', 'kota_surat', 'nama_penandatangan',
        'nip_penandatangan', 'pangkat_golongan', 'jabatan_penandatangan', 'anggota', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tahun_nomor' => 'integer', 'tanggal_mulai' => 'date', 'tanggal_selesai' => 'date', 'tanggal_surat' => 'date', 'anggota' => 'array',
        ];
    }

    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanKpi::class, 'pengajuan_id'); }
    public function pembuat(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
}
