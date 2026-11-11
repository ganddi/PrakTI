<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SDD Tabel 3.13: satu baris per jenis penilaian per mahasiswa.
 * Kolom catatan menyimpan JSON: nama_penilai, rincian (kriteria dosen pembimbing), dan catatan teks.
 */
class Penilaian extends Model
{
    public const PEMBIMBING = 'dosen_pembimbing';
    public const PENGUJI = 'dosen_penguji';
    public const LAPANGAN = 'pembimbing_lapangan';

    public const JENIS = [
        self::PEMBIMBING => 'Dosen pembimbing',
        self::PENGUJI => 'Dosen penguji',
        self::LAPANGAN => 'Pembimbing lapangan',
    ];

    public const DRAF = 'draf';
    public const FINAL = 'final';               // nilai dosen pembimbing yang sudah ditetapkan
    public const DIINPUT = 'diinput';           // nilai eksternal menunggu verifikasi dokumen
    public const DIVERIFIKASI = 'diverifikasi';

    protected $table = 'penilaian';

    protected $fillable = ['mahasiswa_id', 'jenis_penilaian', 'nilai', 'catatan', 'tanggal_penilaian', 'status'];

    protected function casts(): array
    {
        return ['mahasiswa_id' => 'integer', 'nilai' => 'float', 'tanggal_penilaian' => 'date'];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }

    public function getDataAttribute(): array
    {
        $data = json_decode((string) $this->catatan, true);

        return is_array($data) ? $data : ['catatan' => $this->catatan];
    }

    public function getNamaPenilaiAttribute(): ?string { return $this->data['nama_penilai'] ?? null; }
    public function getRincianAttribute(): array { return $this->data['rincian'] ?? []; }
    public function getCatatanTeksAttribute(): ?string { return $this->data['catatan'] ?? null; }

    public static function bungkusCatatan(array $data): string
    {
        return json_encode(array_filter($data, fn ($v) => $v !== null && $v !== ''), JSON_UNESCAPED_UNICODE);
    }

    public static function hitungNilaiPembimbing(array $rincian): float
    {
        $total = 0;
        foreach (config('prakti.kriteria_pembimbing') as $kunci => $k) {
            $total += ((float) ($rincian[$kunci] ?? 0)) * $k['bobot'] / 100;
        }

        return round($total, 2);
    }

    public static function predikat(float $nilai): string
    {
        foreach (config('prakti.skala_huruf') as $batas => $huruf) {
            if ($nilai >= $batas) {
                return $huruf;
            }
        }

        return 'E';
    }
}
