<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\Logbook;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Monitoring logbook mahasiswa bimbingan (FR-13, menu "Daftar Logbook Mahasiswa"). */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = $request->user()->dosen->pengajuanBimbingan()->with('mahasiswa')->get();
        $logbook = Logbook::with('mahasiswa')->whereIn('mahasiswa_id', $bimbingan->pluck('mahasiswa_id'))
            ->when($request->query('status', 'menunggu') !== 'semua', fn ($q) => $q->where('status', $request->query('status', 'menunggu')))
            ->when($request->query('mahasiswa'), fn ($q, $id) => $q->where('mahasiswa_id', $id))
            ->orderByDesc('tanggal')->paginate(20)->withQueryString();

        return view('pembimbing.logbook', ['logbook' => $logbook, 'bimbingan' => $bimbingan, 'pengajuanPerMhs' => $bimbingan->keyBy('mahasiswa_id')]);
    }

    public function verifikasi(Request $request, Logbook $logbook)
    {
        $pengajuan = PengajuanKpi::where('mahasiswa_id', $logbook->mahasiswa_id)->latest('id')->firstOrFail();
        BimbinganController::pastikan($request, $pengajuan);

        $data = $request->validate([
            'status' => ['required', Rule::in([Logbook::DISETUJUI, Logbook::REVISI])],
            'catatan_dosen' => ['nullable', 'required_if:status,revisi', 'string', 'max:1000'],
        ]);
        $logbook->update(['status' => $data['status'], 'catatan_dosen' => $data['catatan_dosen'] ?: $logbook->catatan_dosen]);
        AktivitasSistem::catat('Logbook '.$logbook->tanggal->format('d/m/Y').($data['status'] === Logbook::DISETUJUI ? ' disetujui' : ' perlu direvisi'), 'logbook', $logbook->mahasiswa_id);

        if ($data['status'] === Logbook::REVISI || ! empty($data['catatan_dosen'])) {
            Notifikasi::kirim($pengajuan->mahasiswa, $data['status'] === Logbook::REVISI ? 'Logbook perlu direvisi' : 'Catatan baru pada logbook',
                'Logbook '.$logbook->tanggal->translatedFormat('d F Y').': '.$data['catatan_dosen'], 'logbook', $logbook->id);
        }

        return redirect()->to(url()->previous().'#logbook-'.$logbook->id)->with('sukses', $data['status'] === Logbook::DISETUJUI ? 'Logbook disetujui.' : 'Logbook dikembalikan untuk direvisi.');
    }

    public function setujuiSemua(Request $request, PengajuanKpi $pengajuan)
    {
        BimbinganController::pastikan($request, $pengajuan);
        $jumlah = Logbook::where('mahasiswa_id', $pengajuan->mahasiswa_id)->where('status', Logbook::MENUNGGU)->update(['status' => Logbook::DISETUJUI]);
        if ($jumlah) {
            AktivitasSistem::catat("{$jumlah} logbook disetujui sekaligus", 'logbook', $pengajuan->mahasiswa_id);
        }

        return back()->with('sukses', $jumlah ? "{$jumlah} logbook disetujui." : 'Tidak ada logbook yang menunggu diperiksa.');
    }
}
