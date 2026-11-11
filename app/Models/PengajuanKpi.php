<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * SDD Tabel 3.9. Menjadi titik pusat proses KPI di kode: logbook, laporan, penilaian,
 * dan nilai akhir (yang di database terhubung ke mahasiswa_id) diakses lewat relasi di sini.
 */
class PengajuanKpi extends Model
{
    public const DIAJUKAN = 'diajukan';
    public const REVISI = 'revisi';
    public const DISETUJUI = 'disetujui';
    public const BERJALAN = 'berjalan';
    public const SELESAI = 'selesai';

    protected $table = 'pengajuan_kpi';

    protected $fillable = ['mahasiswa_id', 'periode_id', 'tempat_kpi_id', 'tanggal_pengajuan', 'keperluan', 'status', 'catatan_admin', 'verified_at'];

    private bool $pembimbingDihitung = false;

    private ?DosenPembimbing $pembimbingCache = null;

    protected function casts(): array
    {
        return [
            'mahasiswa_id' => 'integer', 'periode_id' => 'integer', 'tempat_kpi_id' => 'integer',
            'tanggal_pengajuan' => 'date', 'verified_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }
    public function periode(): BelongsTo { return $this->belongsTo(PeriodeKpi::class, 'periode_id'); }
    public function tempatKpi(): BelongsTo { return $this->belongsTo(TempatKpi::class); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenKpi::class, 'pengajuan_id')->latest('id'); }

    public function logbooks(): HasMany { return $this->hasMany(Logbook::class, 'mahasiswa_id', 'mahasiswa_id')->orderByDesc('tanggal'); }
    public function laporan(): HasMany { return $this->hasMany(LaporanKpi::class, 'mahasiswa_id', 'mahasiswa_id')->latest('id'); }
    public function laporanTerakhir(): HasOne { return $this->hasOne(LaporanKpi::class, 'mahasiswa_id', 'mahasiswa_id')->latestOfMany(); }
    public function penilaian(): HasMany { return $this->hasMany(Penilaian::class, 'mahasiswa_id', 'mahasiswa_id'); }
    public function nilaiAkhir(): HasOne { return $this->hasOne(NilaiAkhir::class, 'mahasiswa_id', 'mahasiswa_id'); }
    public function anggotaAktif(): HasMany { return $this->hasMany(AnggotaKelompok::class, 'mahasiswa_id', 'mahasiswa_id')->where('status', 'aktif'); }

    /** Kelompok aktif mahasiswa (lewat tabel anggota_kelompok). */
    public function kelompok(): HasOneThrough
    {
        return $this->hasOneThrough(KelompokKpi::class, AnggotaKelompok::class, 'mahasiswa_id', 'id', 'mahasiswa_id', 'kelompok_id')
            ->where('anggota_kelompok.status', 'aktif');
    }

    /** Riwayat aktivitas proses KPI mahasiswa ini. */
    public function riwayat(): HasMany
    {
        return $this->hasMany(AktivitasSistem::class, 'referensi_id', 'mahasiswa_id')
            ->whereIn('modul', AktivitasSistem::MODUL_KPI)->latest('id');
    }

    /** Dosen pembimbing: penempatan disetujui untuk kelompok + tempat KPI mahasiswa ini. */
    public function getPembimbingAttribute(): ?DosenPembimbing
    {
        if (! $this->pembimbingDihitung) {
            $this->pembimbingCache = $this->kelompok?->pembimbingUntuk($this->tempat_kpi_id);
            $this->pembimbingDihitung = true;
        }

        return $this->pembimbingCache;
    }

    // Akses ringkas ke data instansi dan periode
    public function getNamaInstansiAttribute(): ?string { return $this->tempatKpi?->nama_instansi; }
    public function getAlamatInstansiAttribute(): ?string { return $this->tempatKpi?->alamat_lengkap; }
    public function getBidangKerjaAttribute(): ?string { return $this->tempatKpi?->jenis_instansi; }
    public function getPembimbingLapanganAttribute(): ?string { return $this->tempatKpi?->nama_pic; }
    public function getKontakInstansiAttribute(): ?string { return $this->tempatKpi?->no_hp_pic; }
    public function getTanggalMulaiAttribute(): ?Carbon { return $this->periode?->tanggal_mulai; }
    public function getTanggalSelesaiAttribute(): ?Carbon { return $this->periode?->tanggal_selesai; }

    public function getMingguKeAttribute(): int
    {
        $mulai = $this->tanggal_mulai;
        if (! $mulai || $mulai->isFuture()) {
            return 0;
        }

        return (int) floor($mulai->diffInDays(today()) / 7) + 1;
    }

    public function dokumenTerakhir(string $jenis): ?DokumenKpi
    {
        $daftar = $this->relationLoaded('dokumen') ? $this->dokumen : $this->dokumen()->get();

        return $daftar->where('jenis_dokumen', $jenis)->sortByDesc('id')->first();
    }

    public function suratBalasanTerverifikasi(): bool
    {
        return $this->dokumenTerakhir('surat_balasan')?->status_verifikasi === DokumenKpi::DIVERIFIKASI;
    }

    public function penilaianJenis(string $jenis): ?Penilaian
    {
        $daftar = $this->relationLoaded('penilaian') ? $this->penilaian : $this->penilaian()->get();

        return $daftar->firstWhere('jenis_penilaian', $jenis);
    }

    public function bisaDiaksesOleh(User $user): bool
    {
        return $user->isAdmin()
            || ($user->isMahasiswa() && $user->mahasiswa?->id === $this->mahasiswa_id)
            || ($user->isPembimbing() && $user->dosen && $this->pembimbing?->id === $user->dosen->id);
    }

    /**
     * 8 tahapan KPI sesuai mockup dashboard SDD. Keadaan: done | current | failed | ''.
     */
    public function tahapan(): array
    {
        $laporan = $this->laporanTerakhir;
        $nilai = $this->nilaiAkhir;
        $suratOk = $this->suratBalasanTerverifikasi();

        $langkah = [
            'Pengajuan KPI' => match ($this->status) { self::DIAJUKAN => 'current', self::REVISI => 'failed', default => 'done' },
            'Surat balasan' => $suratOk ? 'done' : ($this->status === self::DISETUJUI ? 'current' : ''),
            'Penempatan dosen' => $this->kelompok && $this->pembimbing ? 'done' : ($suratOk ? 'current' : ''),
            'Logbook harian' => $laporan ? 'done' : ($this->status === self::BERJALAN ? 'current' : ''),
            'Laporan KPI' => $laporan?->status === LaporanKpi::DISETUJUI ? 'done' : ($laporan ? 'current' : ''),
            'Penilaian' => $nilai ? 'done' : ($laporan?->status === LaporanKpi::DISETUJUI ? 'current' : ''),
            'Nilai akhir' => $nilai?->status === NilaiAkhir::DIVERIFIKASI ? 'done' : ($nilai ? 'current' : ''),
            'Selesai' => $this->status === self::SELESAI ? 'done' : '',
        ];

        return $this->status === self::SELESAI ? array_map(fn () => 'done', $langkah) : $langkah;
    }

    /** Progres = persentase tahapan yang sudah selesai (dipakai grafik donat dan perbandingan kelompok). */
    public function getPersentaseAttribute(): int
    {
        $tahap = $this->tahapan();

        return (int) round(count(array_filter($tahap, fn ($k) => $k === 'done')) / count($tahap) * 100);
    }
}
