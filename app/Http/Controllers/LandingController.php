<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKpi;
use App\Models\TempatKpi;

class LandingController extends Controller
{
    public function __invoke()
    {
        $tempat = TempatKpi::where('status', TempatKpi::AKTIF)
            ->withCount(['pengajuan as pernah_count' => fn ($q) => $q->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])])
            ->orderBy('nama_instansi')->get();

        return view('landing', compact('tempat'));
    }
}
