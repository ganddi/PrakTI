<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    public const JENIS = [
        'surat_permohonan' => 'Surat permohonan KPI',
        'proposal' => 'Proposal KPI',
        'surat_balasan' => 'Surat balasan instansi',
        'laporan_akhir' => 'Laporan akhir KPI',
        'lainnya' => 'Dokumen lainnya',
    ];

    protected $fillable = ['pengajuan_kpi_id', 'jenis', 'nama_asli', 'path', 'ukuran', 'diunggah_oleh'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKpi::class, 'pengajuan_kpi_id');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getUkuranFormatAttribute(): string
    {
        $kb = $this->ukuran / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.').' MB' : number_format($kb, 0, ',', '.').' KB';
    }
}
