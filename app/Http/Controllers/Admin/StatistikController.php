<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;

/** FR-23: statistik pelaksanaan dan riwayat institusi KPI. */
class StatistikController extends Controller
{
    public function __invoke(Request $request)
    {
        $tahun = $request->query('tahun');
        $semua = PengajuanKpi::with(['periode', 'tempatKpi'])->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])->get();
        $terpilih = $tahun ? $semua->filter(fn ($p) => $p->periode->tahun === (int) $tahun) : $semua;

        $institusi = $terpilih->groupBy('tempat_kpi_id')->map(fn ($g) => [
            'nama' => $g->first()->tempatKpi->nama_instansi,
            'kota' => $g->first()->tempatKpi->kota,
            'total' => $g->count(),
            'tahun' => $g->map(fn ($p) => $p->periode->tahun)->unique()->sort()->values(),
            'terakhir' => $g->max(fn ($p) => $p->periode->tanggal_mulai),
        ])->sortByDesc('total')->values();

        return view('admin.statistik.index', [
            'tahun' => $tahun,
            'daftarTahun' => $semua->map(fn ($p) => $p->periode->tahun)->unique()->sortDesc()->values(),
            'total' => $terpilih->count(),
            'selesai' => $terpilih->where('status', PengajuanKpi::SELESAI)->count(),
            'perTahun' => $semua->groupBy(fn ($p) => $p->periode->tahun)->map->count()->sortKeys(),
            'perPeriode' => PeriodeKpi::orderBy('tanggal_mulai')->get()->map(fn ($pr) => ['nama' => $pr->nama_periode, 'tahun' => $pr->tahun, 'total' => $semua->where('periode_id', $pr->id)->count()]),
            'institusi' => $institusi,
        ]);
    }
}
