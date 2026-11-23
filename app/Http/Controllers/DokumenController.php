<?php

namespace App\Http\Controllers;

use App\Models\DokumenKpi;
use App\Models\LaporanKpi;
use App\Models\PengajuanKpi;
use App\Support\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Semua berkas tersimpan privat dan hanya diberikan setelah hak akses diperiksa. */
class DokumenController extends Controller
{
    public function unduh(Request $request, DokumenKpi $dokumen)
    {
        abort_unless($dokumen->pengajuan->bisaDiaksesOleh($request->user()), 403);

        return $this->kirim($dokumen->path_file, $dokumen->nama_file, $request->boolean('lihat'));
    }

    public function laporan(Request $request, LaporanKpi $laporan)
    {
        $pengajuan = PengajuanKpi::where('mahasiswa_id', $laporan->mahasiswa_id)->latest('id')->first();
        abort_unless($pengajuan?->bisaDiaksesOleh($request->user()), 403);

        return $this->kirim($laporan->path_file, $laporan->nama_file, $request->boolean('lihat'));
    }

    public function template(string $kode)
    {
        $path = Template::pathDariKode($kode);
        abort_unless($path, 404);

        return $this->kirim($path, basename($path));
    }

    private function kirim(string $path, string $nama, bool $lihat = false)
    {
        abort_unless(Storage::disk()->exists($path), 404, 'Berkas tidak ditemukan di server.');

        return $lihat ? Storage::disk()->response($path, $nama) : Storage::disk()->download($path, $nama);
    }
}
