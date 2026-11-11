<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * SDD Tabel 3.12. Kolom halaman_revisi menyimpan daftar bagian laporan yang ditandai dosen
 * (JSON: halaman, kutipan, catatan, status), komentar_dosen menyimpan feedback umum.
 */
class LaporanKpi extends Model
{
    public const MENUNGGU = 'menunggu';
    public const REVISI = 'revisi';
    public const DISETUJUI = 'disetujui';

    protected $table = 'laporan_kpi';

    protected $fillable = ['mahasiswa_id', 'nama_file', 'path_file', 'versi', 'status', 'komentar_dosen', 'halaman_revisi', 'tanggal_upload', 'diperiksa_at'];

    protected function casts(): array
    {
        return ['mahasiswa_id' => 'integer', 'halaman_revisi' => 'array', 'tanggal_upload' => 'datetime', 'diperiksa_at' => 'datetime'];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }

    /** Daftar bagian yang ditandai, masing-masing dengan indeks untuk diubah statusnya. */
    public function getTandaAttribute(): Collection
    {
        return collect($this->halaman_revisi ?? [])->map(fn ($t, $i) => (object) ($t + ['indeks' => $i]));
    }

    public function getTandaTerbukaAttribute(): int
    {
        return $this->tanda->where('status', 'terbuka')->count();
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower(pathinfo($this->nama_file, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function tambahTanda(?int $halaman, ?string $kutipan, string $catatan): void
    {
        $daftar = $this->halaman_revisi ?? [];
        $daftar[] = ['halaman' => $halaman, 'kutipan' => $kutipan, 'catatan' => $catatan, 'status' => 'terbuka', 'waktu' => now()->toDateTimeString()];
        $this->halaman_revisi = $daftar;
    }
}
