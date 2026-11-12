<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\LaporanKpi;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Support\Template;
use Illuminate\Http\Request;

/** FR-14 & FR-16 (SDD Tabel 3.12). */
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        $pengajuan?->load(['laporan', 'laporanTerakhir', 'kelompok.penempatan.dosen']);

        return view('mahasiswa.laporan.index', ['pengajuan' => $pengajuan, 'template' => Template::daftar('laporan')]);
    }

    public function store(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        abort_unless($pengajuan?->status === PengajuanKpi::BERJALAN, 403, 'Laporan dapat diunggah saat KPI sedang berjalan.');

        $terakhir = $pengajuan->laporanTerakhir;
        if (in_array($terakhir?->status, [LaporanKpi::DISETUJUI, LaporanKpi::MENUNGGU], true)) {
            return back()->with('info', $terakhir->status === LaporanKpi::DISETUJUI ? 'Laporan Anda sudah disetujui.' : 'Laporan versi terakhir masih diperiksa pembimbing.');
        }

        $request->validate(['laporan' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480']]);
        $berkas = $request->file('laporan');

        $laporan = LaporanKpi::create([
            'mahasiswa_id' => $pengajuan->mahasiswa_id,
            'nama_file' => \App\Support\Berkas::namaAman($berkas),
            'path_file' => $berkas->store("laporan/{$pengajuan->mahasiswa_id}", config('filesystems.default')),
            'versi' => (string) ((int) ($terakhir?->versi ?? 0) + 1),
            'status' => LaporanKpi::MENUNGGU,
            'tanggal_upload' => now(),
        ]);

        AktivitasSistem::catat("Laporan KPI versi {$laporan->versi} diunggah", 'laporan', $pengajuan->mahasiswa_id);
        Notifikasi::kirim($pengajuan->pembimbing, $laporan->versi > 1 ? 'Laporan revisi siap diperiksa' : 'Laporan KPI siap diperiksa',
            "{$request->user()->name} mengunggah laporan KPI versi {$laporan->versi}.", 'laporan', $laporan->id);

        return back()->with('sukses', "Laporan versi {$laporan->versi} berhasil diunggah dan dikirim ke dosen pembimbing.");
    }

    private function pengajuan(Request $request): ?PengajuanKpi
    {
        return $request->user()->mahasiswa->pengajuanKpi()->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])->latest('id')->first();
    }
}
