<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\LaporanKpi;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-15 & FR-16: memeriksa laporan, menandai bagian yang perlu diperbaiki, memeriksa revisi. */
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = $request->user()->dosen->pengajuanBimbingan()->with('mahasiswa')->get();
        $laporan = LaporanKpi::with('mahasiswa')->whereIn('mahasiswa_id', $bimbingan->pluck('mahasiswa_id'))
            ->whereIn('id', LaporanKpi::selectRaw('max(id)')->groupBy('mahasiswa_id'))
            ->orderByRaw("status = 'menunggu' desc")->latest('id')->get();

        return view('pembimbing.laporan.index', compact('laporan'));
    }

    public function show(Request $request, LaporanKpi $laporan)
    {
        $pengajuan = $this->pengajuan($request, $laporan);
        $semuaVersi = LaporanKpi::where('mahasiswa_id', $laporan->mahasiswa_id)->latest('id')->get();
        $sebelumnya = $semuaVersi->first(fn ($v) => $v->id < $laporan->id);

        return view('pembimbing.laporan.show', compact('laporan', 'pengajuan', 'semuaVersi', 'sebelumnya'));
    }

    public function tambahTanda(Request $request, LaporanKpi $laporan)
    {
        $this->pengajuan($request, $laporan);
        $data = $request->validate([
            'halaman' => ['nullable', 'integer', 'min:1', 'max:999'],
            'kutipan' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['required', 'string', 'max:2000'],
        ]);

        $laporan->tambahTanda(isset($data['halaman']) ? (int) $data['halaman'] : null, $data['kutipan'] ?? null, $data['catatan']);
        $laporan->save();

        return redirect()->to(route('pembimbing.laporan.show', $laporan).'#tanda')->with('sukses', 'Bagian laporan ditandai.');
    }

    public function ubahTanda(Request $request, LaporanKpi $laporan, int $indeks)
    {
        $this->pengajuan($request, $laporan);
        $daftar = $laporan->halaman_revisi ?? [];
        abort_unless(isset($daftar[$indeks]), 404);
        $daftar[$indeks]['status'] = ($daftar[$indeks]['status'] ?? 'terbuka') === 'selesai' ? 'terbuka' : 'selesai';
        $laporan->update(['halaman_revisi' => $daftar]);

        return back()->with('sukses', $daftar[$indeks]['status'] === 'selesai' ? 'Ditandai sudah diperbaiki.' : 'Dibuka kembali.');
    }

    public function hapusTanda(Request $request, LaporanKpi $laporan, int $indeks)
    {
        $this->pengajuan($request, $laporan);
        $daftar = $laporan->halaman_revisi ?? [];
        abort_unless(isset($daftar[$indeks]), 404);
        unset($daftar[$indeks]);
        $laporan->update(['halaman_revisi' => array_values($daftar)]);

        return back()->with('sukses', 'Tanda dihapus.');
    }

    public function putuskan(Request $request, LaporanKpi $laporan)
    {
        $pengajuan = $this->pengajuan($request, $laporan);
        abort_unless($pengajuan->laporanTerakhir?->id === $laporan->id, 422, 'Hanya versi terbaru yang dapat diputuskan.');

        $data = $request->validate([
            'keputusan' => ['required', Rule::in([LaporanKpi::REVISI, LaporanKpi::DISETUJUI])],
            'komentar_dosen' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($data['keputusan'] === LaporanKpi::REVISI && ! $laporan->tanda_terbuka && empty($data['komentar_dosen'])) {
            return back()->with('gagal', 'Tandai bagian yang perlu diperbaiki atau tulis komentar sebelum meminta revisi.');
        }

        $laporan->update(['status' => $data['keputusan'], 'komentar_dosen' => $data['komentar_dosen'] ?? null, 'diperiksa_at' => now()]);
        $ok = $data['keputusan'] === LaporanKpi::DISETUJUI;

        AktivitasSistem::catat("Laporan versi {$laporan->versi} ".($ok ? 'disetujui' : 'perlu direvisi'), 'laporan', $laporan->mahasiswa_id,
            $ok ? null : "{$laporan->tanda_terbuka} bagian ditandai");
        Notifikasi::kirim($pengajuan->mahasiswa, $ok ? 'Laporan KPI disetujui' : 'Laporan KPI perlu direvisi', $ok
            ? "Laporan versi {$laporan->versi} disetujui. Langkah berikutnya adalah penilaian."
            : "{$request->user()->name} menandai {$laporan->tanda_terbuka} bagian laporan versi {$laporan->versi} yang perlu diperbaiki.", 'laporan', $laporan->id);

        return redirect()->route('pembimbing.bimbingan.show', $pengajuan)->with('sukses', $ok ? 'Laporan disetujui. Anda dapat memberikan penilaian.' : 'Catatan revisi terkirim ke mahasiswa.');
    }

    private function pengajuan(Request $request, LaporanKpi $laporan): PengajuanKpi
    {
        $pengajuan = PengajuanKpi::with(['mahasiswa', 'tempatKpi', 'kelompok.penempatan.dosen', 'laporanTerakhir'])->where('mahasiswa_id', $laporan->mahasiswa_id)->latest('id')->firstOrFail();
        BimbinganController::pastikan($request, $pengajuan);

        return $pengajuan;
    }
}
