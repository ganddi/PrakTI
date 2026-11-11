<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;

/** Menu "Kerja Praktik Industri", "Dokumen", dan "Riwayat Aktivitas Mahasiswa" pada mockup dosen. */
class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = $request->user()->dosen->pengajuanBimbingan()
            ->with(['mahasiswa', 'tempatKpi', 'periode', 'kelompok', 'laporanTerakhir', 'nilaiAkhir', 'dokumen'])
            ->withCount(['logbooks', 'logbooks as logbook_menunggu_count' => fn ($q) => $q->where('status', Logbook::MENUNGGU)])
            ->withMax('logbooks', 'tanggal')->orderByRaw("status = 'selesai'")->get();

        return view('pembimbing.bimbingan.index', ['perKelompok' => $bimbingan->groupBy(fn ($p) => $p->kelompok?->id)]);
    }

    public function show(Request $request, PengajuanKpi $pengajuan)
    {
        $this->pastikan($request, $pengajuan);
        $pengajuan->load(['mahasiswa', 'tempatKpi', 'periode', 'kelompok.penempatan.dosen', 'dokumen', 'logbooks', 'laporan', 'laporanTerakhir', 'penilaian', 'nilaiAkhir', 'riwayat.user']);

        return view('pembimbing.bimbingan.show', compact('pengajuan'));
    }

    public function dokumen(Request $request)
    {
        $id = $request->user()->dosen->pengajuanBimbingan()->pluck('id');
        $dokumen = DokumenKpi::with('pengajuan.mahasiswa')->whereIn('pengajuan_id', $id)
            ->when($request->query('jenis'), fn ($q, $j) => $q->where('jenis_dokumen', $j))->latest('id')->paginate(20)->withQueryString();

        return view('pembimbing.dokumen', compact('dokumen'));
    }

    public function riwayat(Request $request)
    {
        $bimbingan = $request->user()->dosen->pengajuanBimbingan()->with('mahasiswa')->get();
        $aktivitas = AktivitasSistem::with('user')->whereIn('modul', AktivitasSistem::MODUL_KPI)
            ->whereIn('referensi_id', $bimbingan->pluck('mahasiswa_id'))->latest('id')->paginate(30);

        return view('pembimbing.riwayat', ['aktivitas' => $aktivitas, 'namaMahasiswa' => $bimbingan->pluck('mahasiswa.nama_lengkap', 'mahasiswa_id')]);
    }

    public static function pastikan(Request $request, PengajuanKpi $pengajuan): void
    {
        abort_unless($request->user()->dosen && $pengajuan->pembimbing?->id === $request->user()->dosen->id, 403);
    }
}
