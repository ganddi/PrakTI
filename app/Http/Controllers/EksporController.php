<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Ekspor logbook (FR-12) dan dokumen nilai akhir (FR-20). */
class EksporController extends Controller
{
    private function muat(Request $request, PengajuanKpi $pengajuan): PengajuanKpi
    {
        abort_unless($pengajuan->bisaDiaksesOleh($request->user()), 403);

        return $pengajuan->load(['mahasiswa', 'tempatKpi', 'periode', 'kelompok', 'nilaiAkhir', 'penilaian']);
    }

    public function logbookCetak(Request $request, PengajuanKpi $pengajuan)
    {
        $pengajuan = $this->muat($request, $pengajuan);
        $logbooks = $pengajuan->logbooks()->reorder('tanggal')->get();

        return view('ekspor.logbook', compact('pengajuan', 'logbooks'));
    }

    public function logbookCsv(Request $request, PengajuanKpi $pengajuan)
    {
        $pengajuan = $this->muat($request, $pengajuan);
        $logbooks = $pengajuan->logbooks()->reorder('tanggal')->get();
        $nama = 'logbook-'.Str::slug($pengajuan->mahasiswa->nim.'-'.$pengajuan->mahasiswa->nama_lengkap).'.csv';

        return response()->streamDownload(function () use ($logbooks) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No', 'Tanggal', 'Judul kegiatan', 'Deskripsi kegiatan', 'Hasil kegiatan', 'Kendala', 'Status', 'Catatan dosen'], ';');
            foreach ($logbooks as $i => $l) {
                fputcsv($out, [$i + 1, $l->tanggal->format('d/m/Y'), $l->judul_kegiatan, $l->deskripsi_kegiatan, $l->hasil_kegiatan, $l->kendala, ucfirst($l->status), $l->catatan_dosen], ';');
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function nilaiAkhirCetak(Request $request, PengajuanKpi $pengajuan)
    {
        $pengajuan = $this->muat($request, $pengajuan);
        abort_unless($pengajuan->nilaiAkhir, 404, 'Nilai akhir belum dapat dihitung.');

        return view('ekspor.nilai-akhir', compact('pengajuan'));
    }
}
