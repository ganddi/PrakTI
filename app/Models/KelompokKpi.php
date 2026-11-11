<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/** SDD Tabel 3.6. Instansi kelompok dibaca dari tempat KPI anggotanya. */
class KelompokKpi extends Model
{
    protected $table = 'kelompok_kpi';

    protected $fillable = ['periode_id', 'kode_kelompok', 'nama_kelompok', 'keterangan', 'status'];

    public function periode(): BelongsTo { return $this->belongsTo(PeriodeKpi::class, 'periode_id'); }
    public function anggota(): HasMany { return $this->hasMany(AnggotaKelompok::class, 'kelompok_id'); }
    public function anggotaAktif(): HasMany { return $this->hasMany(AnggotaKelompok::class, 'kelompok_id')->where('status', 'aktif'); }
    public function penempatan(): HasMany { return $this->hasMany(PenempatanDosen::class, 'kelompok_id'); }

    /** Tempat KPI yang dipakai anggota aktif kelompok ini (biasanya satu). */
    public function daftarTempat(): Collection
    {
        $this->loadMissing('anggotaAktif.mahasiswa.pengajuanTerakhir.tempatKpi');

        return $this->anggotaAktif->map(fn ($a) => $a->mahasiswa->pengajuanTerakhir?->tempatKpi)->filter()->unique('id')->values();
    }

    public function getNamaInstansiAttribute(): string
    {
        return $this->daftarTempat()->pluck('nama_instansi')->implode(', ') ?: 'Belum ada anggota';
    }

    /** Dosen pembimbing yang disetujui untuk tempat tertentu di kelompok ini. */
    public function pembimbingUntuk(int $tempatId): ?DosenPembimbing
    {
        $this->loadMissing('penempatan.dosen');

        return $this->penempatan->first(fn ($p) => $p->status === PenempatanDosen::DISETUJUI && (int) $p->tempat_kpi_id === $tempatId)?->dosen;
    }
}
