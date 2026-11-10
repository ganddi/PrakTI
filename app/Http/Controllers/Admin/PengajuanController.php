<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKpi;
use App\Models\User;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Fitur 6: Verifikasi pengajuan KPI oleh admin. */
class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', PengajuanKpi::DIAJUKAN);
        abort_unless(in_array($status, ['diajukan', 'berjalan', 'selesai', 'ditolak', 'semua'], true), 404);

        $pengajuan = PengajuanKpi::with(['mahasiswa', 'pembimbing'])
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), function ($q, $cari) {
                $q->where(fn ($w) => $w->where('nama_instansi', 'like', "%{$cari}%")
                    ->orWhereHas('mahasiswa', fn ($m) => $m->where('name', 'like', "%{$cari}%")
                        ->orWhere('nomor_induk', 'like', "%{$cari}%")));
            })
            ->when($status === PengajuanKpi::DIAJUKAN, fn ($q) => $q->oldest(), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $jumlah = PengajuanKpi::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.pengajuan.index', compact('pengajuan', 'status', 'jumlah'));
    }

    public function show(PengajuanKpi $pengajuan)
    {
        $pengajuan->load([
            'mahasiswa', 'pembimbing', 'verifikator', 'dokumen.pengunggah', 'progressTerakhir',
            'logbooks.komentar.user', 'progress.komentar.user',
        ]);

        $daftarPembimbing = User::where('role', User::PEMBIMBING)
            ->where('is_active', true)
            ->withCount(['mahasiswaBimbingan as jumlah_bimbingan' => fn ($q) => $q->where('status', PengajuanKpi::BERJALAN)])
            ->orderBy('name')
            ->get();

        return view('admin.pengajuan.show', compact('pengajuan', 'daftarPembimbing'));
    }

    public function verifikasi(Request $request, PengajuanKpi $pengajuan)
    {
        if ($pengajuan->status !== PengajuanKpi::DIAJUKAN) {
            return back()->with('gagal', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['setujui', 'tolak'])],
            'pembimbing_id' => ['nullable', 'required_if:keputusan,setujui', $this->aturanPembimbing()],
            'catatan_admin' => ['nullable', 'required_if:keputusan,tolak', 'string', 'max:1000'],
        ]);

        $disetujui = $data['keputusan'] === 'setujui';

        $pengajuan->update([
            'status' => $disetujui ? PengajuanKpi::BERJALAN : PengajuanKpi::DITOLAK,
            'pembimbing_id' => $disetujui ? $data['pembimbing_id'] : null,
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_pada' => now(),
        ]);
        $pengajuan->load(['mahasiswa', 'pembimbing']);

        if ($disetujui) {
            Notifikasi::kirim($pengajuan->mahasiswa, 'Pengajuan KPI disetujui', array_filter([
                "Pengajuan KPI Anda di {$pengajuan->nama_instansi} telah disetujui.",
                "Dosen pembimbing Anda: {$pengajuan->pembimbing->name}.",
                $pengajuan->catatan_admin ? 'Catatan admin: '.$pengajuan->catatan_admin : null,
                'Mulai sekarang Anda dapat mengisi logbook harian dan memperbarui progress KPI di PrakTI.',
            ]), route('dashboard'));

            $this->kabariPembimbing($pengajuan);
        } else {
            Notifikasi::kirim($pengajuan->mahasiswa, 'Pengajuan KPI belum disetujui', [
                "Pengajuan KPI Anda di {$pengajuan->nama_instansi} belum dapat disetujui.",
                'Catatan admin: '.$pengajuan->catatan_admin,
                'Silakan perbaiki sesuai catatan, lalu ajukan kembali melalui PrakTI.',
            ], route('mahasiswa.pengajuan.index'), 'Lihat pengajuan');
        }

        return redirect()->route('admin.pengajuan.show', $pengajuan)->with('sukses', $disetujui
            ? 'Pengajuan disetujui dan dosen pembimbing telah ditetapkan.'
            : 'Pengajuan ditolak. Mahasiswa akan menerima catatan Anda melalui email.');
    }

    public function gantiPembimbing(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->status === PengajuanKpi::BERJALAN, 422, 'Pembimbing hanya dapat diganti saat KPI sedang berjalan.');

        $data = $request->validate(['pembimbing_id' => ['required', $this->aturanPembimbing()]]);

        if ((int) $data['pembimbing_id'] === $pengajuan->pembimbing_id) {
            return back()->with('info', 'Dosen pembimbing tidak berubah.');
        }

        $pengajuan->update(['pembimbing_id' => $data['pembimbing_id']]);
        $pengajuan->load(['mahasiswa', 'pembimbing']);

        Notifikasi::kirim($pengajuan->mahasiswa, 'Dosen pembimbing KPI diperbarui', [
            "Dosen pembimbing KPI Anda sekarang adalah {$pengajuan->pembimbing->name}.",
        ], route('dashboard'));
        $this->kabariPembimbing($pengajuan);

        return back()->with('sukses', 'Dosen pembimbing berhasil diganti.');
    }

    public function selesai(PengajuanKpi $pengajuan)
    {
        if ($pengajuan->status !== PengajuanKpi::BERJALAN) {
            return back()->with('gagal', 'Hanya KPI yang sedang berjalan yang dapat ditandai selesai.');
        }

        $pengajuan->update(['status' => PengajuanKpi::SELESAI]);

        Notifikasi::kirim($pengajuan->mahasiswa, 'KPI dinyatakan selesai', [
            "Kegiatan KPI Anda di {$pengajuan->nama_instansi} telah ditandai selesai oleh admin program studi.",
            'Riwayat logbook, progress, dan dokumen tetap dapat Anda lihat di PrakTI.',
        ], route('dashboard'));

        return back()->with('sukses', 'KPI telah ditandai selesai.');
    }

    private function aturanPembimbing()
    {
        return Rule::exists('users', 'id')->where('role', User::PEMBIMBING)->where('is_active', true);
    }

    private function kabariPembimbing(PengajuanKpi $pengajuan): void
    {
        Notifikasi::kirim($pengajuan->pembimbing, 'Mahasiswa bimbingan KPI baru', [
            "Anda ditetapkan sebagai dosen pembimbing KPI untuk {$pengajuan->mahasiswa->name} ({$pengajuan->mahasiswa->nomor_induk}).",
            "Tempat KPI: {$pengajuan->nama_instansi}, ".$pengajuan->tanggal_mulai->translatedFormat('d F Y')
                .' sampai '.$pengajuan->tanggal_selesai->translatedFormat('d F Y').'.',
        ], route('pembimbing.bimbingan.show', $pengajuan), 'Lihat mahasiswa bimbingan');
    }
}
