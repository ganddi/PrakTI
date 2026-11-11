<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;

/** Monitoring seluruh kegiatan KPI (FR-13, FR-22). */
class MonitoringController extends Controller
{
    public function __invoke(Request $request)
    {
        $status = $request->query('status', PengajuanKpi::BERJALAN);
        abort_unless(in_array($status, ['berjalan', 'selesai', 'semua'], true), 404);

        $daftar = PengajuanKpi::with(['mahasiswa', 'tempatKpi', 'periode', 'kelompok.penempatan.dosen', 'laporanTerakhir', 'nilaiAkhir', 'dokumen'])
            ->withCount('logbooks')->withMax('logbooks', 'tanggal')
            ->whereIn('status', $status === 'semua' ? [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI] : [$status])
            ->when($request->query('periode'), fn ($q, $id) => $q->where('periode_id', $id))
            ->when($request->query('kelompok'), fn ($q, $id) => $q->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $id)))
            ->when($request->query('q'), fn ($q, $c) => $q->where(fn ($w) => $w->whereHas('tempatKpi', fn ($t) => $t->where('nama_instansi', 'like', "%{$c}%"))
                ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nim', 'like', "%{$c}%"))))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.monitoring.index', [
            'daftar' => $daftar, 'status' => $status,
            'daftarPeriode' => PeriodeKpi::with('kelompok')->orderByDesc('tanggal_mulai')->get(),
        ]);
    }
}
