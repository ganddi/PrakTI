<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** FR-11: informasi pelaksanaan KPI (menu "Kerja Praktik Industri"). */
class InformasiController extends Controller
{
    public function __invoke(Request $request)
    {
        $pengajuan = $request->user()->mahasiswa->pengajuanTerakhir()
            ->with(['tempatKpi', 'periode', 'kelompok.periode', 'kelompok.penempatan.dosen', 'kelompok.anggotaAktif.mahasiswa', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'riwayat.user'])
            ->first();

        return view('mahasiswa.informasi', compact('pengajuan'));
    }
}
