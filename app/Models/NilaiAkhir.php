<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.14: hasil perhitungan nilai akhir otomatis (FR-19). */
class NilaiAkhir extends Model
{
    public const DIHITUNG = 'dihitung';
    public const DIVERIFIKASI = 'diverifikasi';

    protected $table = 'nilai_akhir';

    protected $fillable = [
        'mahasiswa_id', 'nilai_dosen_pembimbing', 'nilai_dosen_penguji', 'nilai_pembimbing_lapangan',
        'bobot_dosen_pembimbing', 'bobot_dosen_penguji', 'bobot_pembimbing_lapangan',
        'nilai_akhir', 'predikat', 'status', 'tanggal_hitung',
    ];

    protected function casts(): array
    {
        return [
            'nilai_dosen_pembimbing' => 'float', 'nilai_dosen_penguji' => 'float', 'nilai_pembimbing_lapangan' => 'float',
            'bobot_dosen_pembimbing' => 'float', 'bobot_dosen_penguji' => 'float', 'bobot_pembimbing_lapangan' => 'float',
            'nilai_akhir' => 'float', 'tanggal_hitung' => 'date',
        ];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }

    /** Nilai yang belum tersedia untuk menghitung nilai akhir. */
    public static function belumLengkap(int $mahasiswaId): array
    {
        $p = Penilaian::where('mahasiswa_id', $mahasiswaId)->get()->keyBy('jenis_penilaian');

        return array_keys(array_filter([
            'nilai dosen pembimbing (belum ditetapkan)' => ($p[Penilaian::PEMBIMBING]->status ?? null) !== Penilaian::FINAL,
            'nilai dosen penguji' => ! isset($p[Penilaian::PENGUJI]),
            'nilai pembimbing lapangan' => ! isset($p[Penilaian::LAPANGAN]),
        ]));
    }

    /**
     * Hitung ulang nilai akhir dengan bobot di config/prakti.php.
     * Data masukan yang sama selalu menghasilkan nilai yang sama (NFR-20).
     */
    public static function hitung(int $mahasiswaId): ?self
    {
        if (self::belumLengkap($mahasiswaId)) {
            self::where('mahasiswa_id', $mahasiswaId)->delete();

            return null;
        }

        $p = Penilaian::where('mahasiswa_id', $mahasiswaId)->get()->keyBy('jenis_penilaian');
        $bobot = config('prakti.bobot_nilai_akhir');
        $nilai = [
            'pembimbing' => $p[Penilaian::PEMBIMBING]->nilai,
            'penguji' => $p[Penilaian::PENGUJI]->nilai,
            'lapangan' => $p[Penilaian::LAPANGAN]->nilai,
        ];
        $akhir = round(collect($nilai)->map(fn ($n, $k) => $n * $bobot[$k] / 100)->sum(), 2);

        return self::updateOrCreate(['mahasiswa_id' => $mahasiswaId], [
            'nilai_dosen_pembimbing' => $nilai['pembimbing'],
            'nilai_dosen_penguji' => $nilai['penguji'],
            'nilai_pembimbing_lapangan' => $nilai['lapangan'],
            'bobot_dosen_pembimbing' => $bobot['pembimbing'],
            'bobot_dosen_penguji' => $bobot['penguji'],
            'bobot_pembimbing_lapangan' => $bobot['lapangan'],
            'nilai_akhir' => $akhir,
            'predikat' => Penilaian::predikat($akhir),
            'status' => self::DIHITUNG,
            'tanggal_hitung' => today(),
        ]);
    }
}
