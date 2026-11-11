<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.8: pembagian (kelompok + tempat KPI) ke dosen, pilihan dosen, dan verifikasi admin. */
class PenempatanDosen extends Model
{
    public const DITAWARKAN = 'ditawarkan';
    public const DIAJUKAN = 'diajukan';
    public const DISETUJUI = 'disetujui';
    public const PERLU_PERBAIKAN = 'perlu_perbaikan';
    public const DIBATALKAN = 'dibatalkan';

    protected $table = 'penempatan_dosen';

    protected $fillable = ['dosen_id', 'tempat_kpi_id', 'kelompok_id', 'tanggal_pengajuan', 'status', 'catatan_admin', 'verified_at'];

    protected function casts(): array
    {
        return [
            'dosen_id' => 'integer', 'tempat_kpi_id' => 'integer', 'kelompok_id' => 'integer',
            'tanggal_pengajuan' => 'date', 'verified_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo { return $this->belongsTo(DosenPembimbing::class, 'dosen_id'); }
    public function kelompok(): BelongsTo { return $this->belongsTo(KelompokKpi::class, 'kelompok_id'); }
    public function tempatKpi(): BelongsTo { return $this->belongsTo(TempatKpi::class); }

    public function bisaDipilih(): bool
    {
        return in_array($this->status, [self::DITAWARKAN, self::PERLU_PERBAIKAN], true);
    }

    /** Pengajuan mahasiswa yang tercakup penempatan ini. */
    public function pengajuanTercakup()
    {
        return PengajuanKpi::where('tempat_kpi_id', $this->tempat_kpi_id)
            ->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $this->kelompok_id));
    }
}
