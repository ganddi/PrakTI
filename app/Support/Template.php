<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Template dokumen (FR-06, FR-14, FR-17) disimpan sebagai berkas di storage/app/private/template/{jenis}.
 * SDD tidak mendefinisikan tabel template, sehingga tidak ada tabel tambahan.
 */
class Template
{
    public const JENIS = [
        'surat_permohonan' => 'Template surat permohonan KPI',
        'logbook' => 'Template logbook KPI',
        'laporan' => 'Template laporan KPI',
        'penilaian' => 'Template penilaian penguji & pembimbing lapangan',
    ];

    public static function daftar(string $jenis): Collection
    {
        return collect(Storage::disk('local')->files("template/{$jenis}"))
            ->map(fn ($path) => (object) [
                'jenis' => $jenis,
                'path' => $path,
                'nama' => basename($path),
                'ukuran' => Berkas::ukuran(Storage::disk('local')->size($path)),
                'kode' => rtrim(strtr(base64_encode($path), '+/', '-_'), '='),
            ])->sortBy('nama')->values();
    }

    public static function simpan(string $jenis, UploadedFile $berkas): void
    {
        $nama = preg_replace('/[^A-Za-z0-9._-]+/', '_', $berkas->getClientOriginalName());
        $berkas->storeAs("template/{$jenis}", $nama, 'local');
    }

    /** Path aman dari kode unduhan (mencegah akses di luar folder template). */
    public static function pathDariKode(string $kode): ?string
    {
        $b64 = strtr($kode, '-_', '+/');
        $path = base64_decode(str_pad($b64, strlen($b64) + (4 - strlen($b64) % 4) % 4, '='), true);

        return $path && str_starts_with($path, 'template/') && ! str_contains($path, '..') && Storage::disk('local')->exists($path) ? $path : null;
    }
}
