<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanKpi extends Model
{
    public const DIAJUKAN = 'diajukan';
    public const DITOLAK = 'ditolak';
    public const BERJALAN = 'berjalan';
    public const SELESAI = 'selesai';

    protected $table = 'pengajuan_kpi';

    protected $fillable = [
        'mahasiswa_id', 'pembimbing_id', 'nama_instansi', 'alamat_instansi', 'bidang_kerja',
        'pembimbing_lapangan', 'kontak_instansi', 'tanggal_mulai', 'tanggal_selesai', 'deskripsi',
        'status', 'catatan_admin', 'diverifikasi_oleh', 'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'mahasiswa_id' => 'integer',
            'pembimbing_id' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'pengajuan_kpi_id')->latest();
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'pengajuan_kpi_id')->orderByDesc('tanggal');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'pengajuan_kpi_id')->latest('id');
    }

    public function progressTerakhir(): HasOne
    {
        return $this->hasOne(Progress::class, 'pengajuan_kpi_id')->latestOfMany();
    }

    /** Persentase progress terbaru (0 jika belum ada). */
    public function getPersentaseAttribute(): int
    {
        return (int) ($this->progressTerakhir?->persentase ?? 0);
    }

    /** Minggu ke berapa KPI berjalan, dihitung dari tanggal mulai. */
    public function getMingguKeAttribute(): int
    {
        if ($this->tanggal_mulai->isFuture()) {
            return 0;
        }

        return (int) floor($this->tanggal_mulai->diffInDays(today()) / 7) + 1;
    }

    /** Hak akses data KPI: admin, mahasiswa pemilik, atau dosen pembimbingnya. */
    public function bisaDiaksesOleh(User $user): bool
    {
        return $user->isAdmin()
            || $this->mahasiswa_id === $user->id
            || ($this->pembimbing_id !== null && $this->pembimbing_id === $user->id);
    }
}
