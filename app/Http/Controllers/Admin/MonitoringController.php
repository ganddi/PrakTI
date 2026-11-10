<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;

/** Fitur 8 (admin): memantau seluruh KPI yang berjalan maupun selesai. */
class MonitoringController extends Controller
{
    public function __invoke(Request $request)
    {
        $status = $request->query('status', PengajuanKpi::BERJALAN);
        abort_unless(in_array($status, ['berjalan', 'selesai', 'semua'], true), 404);

        $daftar = PengajuanKpi::with(['mahasiswa', 'pembimbing', 'progressTerakhir'])
            ->withCount([
                'logbooks',
                'logbooks as logbook_menunggu_count' => fn ($q) => $q->where('status', Logbook::MENUNGGU),
            ])
            ->withMax('logbooks', 'tanggal')
            ->whereIn('status', $status === 'semua' ? [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI] : [$status])
            ->when($request->query('q'), function ($q, $cari) {
                $q->where(fn ($w) => $w->where('nama_instansi', 'like', "%{$cari}%")
                    ->orWhereHas('mahasiswa', fn ($m) => $m->where('name', 'like', "%{$cari}%")
                        ->orWhere('nomor_induk', 'like', "%{$cari}%")));
            })
            ->orderBy('tanggal_mulai')
            ->paginate(20)
            ->withQueryString();

        $jumlah = PengajuanKpi::selectRaw('status, count(*) as total')
            ->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.monitoring.index', compact('daftar', 'status', 'jumlah'));
    }
}
