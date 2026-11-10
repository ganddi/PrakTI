<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use App\Models\Progress;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Fitur 9: verifikasi & feedback pembimbing pada logbook dan progress. */
class VerifikasiController extends Controller
{
    public function logbook(Request $request, Logbook $logbook)
    {
        $pengajuan = $logbook->pengajuan;
        abort_unless($pengajuan->pembimbing_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([Logbook::DISETUJUI, Logbook::REVISI])],
            'catatan' => ['nullable', 'required_if:status,revisi', 'string', 'max:1000'],
        ]);

        $logbook->update(['status' => $data['status']]);

        if (! empty($data['catatan'])) {
            $logbook->komentar()->create(['user_id' => $request->user()->id, 'isi' => $data['catatan']]);
        }

        // Email dikirim jika perlu revisi atau ada catatan, agar mahasiswa tidak dibanjiri email
        if ($data['status'] === Logbook::REVISI || ! empty($data['catatan'])) {
            Notifikasi::kirim($pengajuan->mahasiswa,
                $data['status'] === Logbook::REVISI ? 'Logbook perlu direvisi' : 'Feedback baru pada logbook', array_filter([
                    'Logbook tanggal '.$logbook->tanggal->translatedFormat('d F Y').' telah diperiksa oleh '.$request->user()->name.'.',
                    $data['status'] === Logbook::REVISI ? 'Silakan perbaiki logbook tersebut sesuai catatan pembimbing.' : null,
                    ! empty($data['catatan']) ? 'Catatan pembimbing: '.$data['catatan'] : null,
                ]), route('mahasiswa.logbook.index').'#logbook-'.$logbook->id, 'Lihat logbook');
        }

        return redirect()->to(url()->previous().'#logbook-'.$logbook->id)->with('sukses', $data['status'] === Logbook::DISETUJUI
            ? 'Logbook disetujui.'
            : 'Logbook dikembalikan ke mahasiswa untuk direvisi.');
    }

    public function setujuiSemuaLogbook(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->pembimbing_id === $request->user()->id, 403);

        $jumlah = Logbook::where('pengajuan_kpi_id', $pengajuan->id)
            ->where('status', Logbook::MENUNGGU)
            ->update(['status' => Logbook::DISETUJUI]);

        return back()->with('sukses', $jumlah > 0
            ? "{$jumlah} logbook disetujui."
            : 'Tidak ada logbook yang menunggu diperiksa.');
    }

    public function progress(Request $request, Progress $progress)
    {
        $pengajuan = $progress->pengajuan;
        abort_unless($pengajuan->pembimbing_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([Progress::DIVERIFIKASI, Progress::REVISI])],
            'catatan' => ['nullable', 'required_if:status,revisi', 'string', 'max:1000'],
        ]);

        $progress->update(['status' => $data['status']]);

        if (! empty($data['catatan'])) {
            $progress->komentar()->create(['user_id' => $request->user()->id, 'isi' => $data['catatan']]);
        }

        Notifikasi::kirim($pengajuan->mahasiswa,
            $data['status'] === Progress::DIVERIFIKASI ? 'Progress KPI diverifikasi' : 'Progress KPI perlu direvisi', array_filter([
                "Progress \"{$progress->judul}\" ({$progress->persentase}%) telah diperiksa oleh {$request->user()->name}.",
                ! empty($data['catatan']) ? 'Catatan pembimbing: '.$data['catatan'] : null,
            ]), route('mahasiswa.progress.index').'#progress-'.$progress->id, 'Lihat progress');

        return redirect()->to(url()->previous().'#progress-'.$progress->id)->with('sukses', $data['status'] === Progress::DIVERIFIKASI
            ? 'Progress diverifikasi.'
            : 'Progress dikembalikan ke mahasiswa untuk direvisi.');
    }
}
