<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;

/** FR-22: rekapitulasi nilai dan ekspor. */
class RekapController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.rekap.index', [
            'rekap' => $this->query($request)->paginate(25)->withQueryString(),
            'daftarPeriode' => PeriodeKpi::with('kelompok')->orderByDesc('tanggal_mulai')->get(),
        ]);
    }

    public function ekspor(Request $request)
    {
        $data = $this->query($request)->get();
        $f = fn ($n) => $n === null ? '' : number_format($n, 2, ',', '');

        return response()->streamDownload(function () use ($data, $f) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No', 'NIM', 'Nama', 'Periode', 'Kelompok', 'Tempat KPI', 'Dosen pembimbing', 'Nilai pembimbing', 'Bobot', 'Nilai penguji', 'Bobot', 'Nilai pembimbing lapangan', 'Bobot', 'Nilai akhir', 'Predikat', 'Status nilai'], ';');
            foreach ($data as $i => $p) {
                $n = $p->nilaiAkhir;
                fputcsv($out, [
                    $i + 1, $p->mahasiswa->nim, $p->mahasiswa->nama_lengkap, $p->periode->nama_periode, $p->kelompok?->nama_kelompok,
                    $p->nama_instansi, $p->pembimbing?->nama_lengkap,
                    $f($n?->nilai_dosen_pembimbing), $f($n?->bobot_dosen_pembimbing), $f($n?->nilai_dosen_penguji), $f($n?->bobot_dosen_penguji),
                    $f($n?->nilai_pembimbing_lapangan), $f($n?->bobot_pembimbing_lapangan), $f($n?->nilai_akhir), $n?->predikat, $n?->status,
                ], ';');
            }
            fclose($out);
        }, 'rekap-nilai-kpi-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function query(Request $request)
    {
        return PengajuanKpi::with(['mahasiswa', 'periode', 'tempatKpi', 'kelompok.penempatan.dosen', 'nilaiAkhir', 'dokumen'])
            ->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])
            ->when($request->query('status', 'dinilai') === 'dinilai', fn ($q) => $q->has('nilaiAkhir'))
            ->when($request->query('status') === 'selesai', fn ($q) => $q->where('status', PengajuanKpi::SELESAI))
            ->when($request->query('periode'), fn ($q, $id) => $q->where('periode_id', $id))
            ->when($request->query('kelompok'), fn ($q, $id) => $q->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $id)))
            ->orderBy('periode_id')->orderBy('id');
    }
}
