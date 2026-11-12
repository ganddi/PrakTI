<?php

namespace App\Support;

use App\Models\DokumenKpi;
use App\Models\PengajuanKpi;
use Illuminate\Http\UploadedFile;

/** Penyimpanan berkas di storage privat; hanya bisa diunduh lewat route yang memeriksa hak akses (SDD Bab V no. 7). */
class Berkas
{
    public static function ukuran(int $byte): string
    {
        $kb = $byte / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.').' MB' : number_format(max($kb, 1), 0, ',', '.').' KB';
    }

    /** Nama berkas asli dipotong agar muat di kolom nama_file VARCHAR(255) (SDD Tabel 3.10 dan 3.12). */
    public static function namaAman(UploadedFile $berkas): string
    {
        $nama = $berkas->getClientOriginalName();
        $ext = $berkas->getClientOriginalExtension();

        return mb_strlen($nama) <= 255 ? $nama : mb_substr(pathinfo($nama, PATHINFO_FILENAME), 0, 250 - mb_strlen($ext)).'.'.$ext;
    }

    public static function simpanDokumen(PengajuanKpi $pengajuan, UploadedFile $berkas, string $jenis): DokumenKpi
    {
        return DokumenKpi::create([
            'mahasiswa_id' => $pengajuan->mahasiswa_id,
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => $jenis,
            'nama_file' => self::namaAman($berkas),
            'format_file' => substr(strtolower($berkas->getClientOriginalExtension()), 0, 20),
            'ukuran_file' => $berkas->getSize(),
            'path_file' => $berkas->store("dokumen/{$pengajuan->mahasiswa_id}", config('filesystems.default')),
            'status_verifikasi' => in_array($jenis, DokumenKpi::PERLU_VERIFIKASI, true) ? DokumenKpi::MENUNGGU : DokumenKpi::TERSIMPAN,
            'uploaded_at' => now(),
        ]);
    }
}
