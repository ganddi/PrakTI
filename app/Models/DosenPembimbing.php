<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** SDD Tabel 3.3 */
class DosenPembimbing extends Model
{
    protected $table = 'dosen_pembimbing';

    protected $fillable = ['user_id', 'nidn', 'nama_lengkap', 'email', 'no_hp', 'program_studi', 'jabatan'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function penempatan(): HasMany { return $this->hasMany(PenempatanDosen::class, 'dosen_id'); }

    public function getNameAttribute(): string { return $this->nama_lengkap; }
    public function getNomorIndukAttribute(): string { return $this->nidn; }

    /**
     * Pengajuan KPI mahasiswa bimbingan: mahasiswa yang menjadi anggota aktif kelompok
     * dan tempat KPI-nya sama dengan penempatan dosen ini yang sudah disetujui.
     */
    public function pengajuanBimbingan(): Builder
    {
        $pasangan = $this->penempatan()->where('status', PenempatanDosen::DISETUJUI)->get(['kelompok_id', 'tempat_kpi_id']);

        return PengajuanKpi::query()
            ->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])
            ->where(function ($q) use ($pasangan) {
                $q->whereRaw('1 = 0');
                foreach ($pasangan as $p) {
                    $q->orWhere(fn ($w) => $w->where('tempat_kpi_id', $p->tempat_kpi_id)
                        ->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $p->kelompok_id)));
                }
            });
    }
}
