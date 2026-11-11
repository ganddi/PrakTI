<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;

/** Progress KPI dihitung dari tahapan dan logbook (FR-13), tanpa tabel tambahan. */
class ProgressController extends Controller
{
    public function __invoke(Request $request)
    {
        $pengajuan = $request->user()->mahasiswa->pengajuanTerakhir()
            ->with(['tempatKpi', 'periode', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'kelompok.penempatan.dosen'])->first();

        $mingguan = collect();
        $tim = collect();
        if ($pengajuan && $pengajuan->tanggal_mulai) {
            $logbook = Logbook::where('mahasiswa_id', $pengajuan->mahasiswa_id)->get();
            $mulai = $pengajuan->tanggal_mulai->copy()->startOfWeek();
            for ($m = $mulai->copy(), $i = 1; $m->lte(today()) && $m->lte($pengajuan->tanggal_selesai); $m->addWeek(), $i++) {
                $mingguan->put('M'.$i, $logbook->filter(fn ($l) => $l->tanggal->between($m, $m->copy()->endOfWeek()))->count());
            }

            if ($pengajuan->kelompok) {
                $tim = PengajuanKpi::with(['mahasiswa', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'kelompok.penempatan.dosen'])
                    ->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $pengajuan->kelompok->id))->get()
                    ->map(fn ($p) => ['nama' => strtok($p->mahasiswa->nama_lengkap, ' '), 'persen' => $p->persentase, 'saya' => $p->id === $pengajuan->id]);
            }
        }

        return view('mahasiswa.progress', compact('pengajuan', 'mingguan', 'tim'));
    }
}
