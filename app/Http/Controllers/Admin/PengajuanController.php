<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-05: verifikasi pengajuan KPI. */
class PengajuanController extends Controller
{
    public const FILTER = ['diajukan' => 'Menunggu verifikasi', 'revisi' => 'Perlu perbaikan', 'disetujui' => 'Disetujui', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'semua' => 'Semua'];

    public function index(Request $request)
    {
        $status = $request->query('status', PengajuanKpi::DIAJUKAN);
        abort_unless(array_key_exists($status, self::FILTER), 404);

        $pengajuan = PengajuanKpi::with(['mahasiswa', 'tempatKpi', 'periode', 'kelompok'])
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($request->query('periode'), fn ($q, $id) => $q->where('periode_id', $id))
            ->when($request->query('q'), fn ($q, $c) => $q->where(fn ($w) => $w->whereHas('tempatKpi', fn ($t) => $t->where('nama_instansi', 'like', "%{$c}%"))
                ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nim', 'like', "%{$c}%"))))
            ->when($status === PengajuanKpi::DIAJUKAN, fn ($q) => $q->oldest('updated_at'), fn ($q) => $q->latest('id'))
            ->paginate(15)->withQueryString();

        return view('admin.pengajuan.index', [
            'pengajuan' => $pengajuan, 'status' => $status,
            'jumlah' => PengajuanKpi::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'daftarPeriode' => PeriodeKpi::orderByDesc('tanggal_mulai')->get(),
        ]);
    }

    public function show(PengajuanKpi $pengajuan)
    {
        $pengajuan->load(['mahasiswa.user', 'tempatKpi', 'periode', 'kelompok.penempatan.dosen', 'dokumen', 'logbooks', 'laporan', 'laporanTerakhir', 'penilaian', 'nilaiAkhir', 'riwayat.user']);

        return view('admin.pengajuan.show', compact('pengajuan'));
    }

    public function verifikasi(Request $request, PengajuanKpi $pengajuan)
    {
        if ($pengajuan->status !== PengajuanKpi::DIAJUKAN) {
            return back()->with('gagal', 'Pengajuan ini tidak sedang menunggu verifikasi.');
        }

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['setujui', 'revisi'])],
            'catatan_admin' => ['nullable', 'required_if:keputusan,revisi', 'string', 'max:1000'],
        ]);
        $ok = $data['keputusan'] === 'setujui';

        $pengajuan->update(['status' => $ok ? PengajuanKpi::DISETUJUI : PengajuanKpi::REVISI, 'catatan_admin' => $data['catatan_admin'] ?? null, 'verified_at' => now()]);
        AktivitasSistem::catat($ok ? 'Pengajuan KPI disetujui' : 'Pengajuan KPI perlu diperbaiki', 'pengajuan', $pengajuan->mahasiswa_id, $data['catatan_admin'] ?? null);

        Notifikasi::kirim($pengajuan->mahasiswa, $ok ? 'Pengajuan KPI disetujui' : 'Pengajuan KPI perlu diperbaiki', $ok
            ? ["Pengajuan KPI Anda di {$pengajuan->nama_instansi} disetujui.", 'Unduh template surat permohonan, kirim ke instansi, lalu unggah surat balasannya.']
            : ["Pengajuan KPI Anda di {$pengajuan->nama_instansi} perlu diperbaiki.", 'Catatan admin: '.$data['catatan_admin']], 'pengajuan', $pengajuan->id);

        return redirect()->route('admin.pengajuan.show', $pengajuan)->with('sukses', $ok ? 'Pengajuan disetujui.' : 'Catatan perbaikan terkirim ke mahasiswa.');
    }
}
