<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\PenempatanDosen;
use App\Models\User;
use Illuminate\Http\Request;

/** FR-09 & FR-10 (sisi dosen). */
class PenempatanController extends Controller
{
    public function index(Request $request)
    {
        $penempatan = $request->user()->dosen->penempatan()
            ->with(['tempatKpi', 'kelompok.periode', 'kelompok.penempatan.dosen'])->latest('id')->get()
            ->each(fn ($p) => $p->setAttribute('jumlah_mahasiswa', $p->pengajuanTercakup()->count()));

        return view('pembimbing.penempatan.index', [
            'tersedia' => $penempatan->filter->bisaDipilih(),
            'lainnya' => $penempatan->reject->bisaDipilih(),
        ]);
    }

    public function ajukan(Request $request)
    {
        $data = $request->validate(['penempatan_id' => ['required', 'array', 'min:1'], 'penempatan_id.*' => ['integer']]);
        $dipilih = $request->user()->dosen->penempatan()->with('tempatKpi')->whereIn('id', $data['penempatan_id'])
            ->whereIn('status', [PenempatanDosen::DITAWARKAN, PenempatanDosen::PERLU_PERBAIKAN])->get();

        if ($dipilih->isEmpty()) {
            return back()->with('gagal', 'Tidak ada tempat KPI yang dapat diajukan.');
        }

        $dipilih->each->update(['status' => PenempatanDosen::DIAJUKAN, 'tanggal_pengajuan' => today()]);
        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Pilihan penempatan dosen menunggu verifikasi',
            "{$request->user()->name} memilih: ".$dipilih->pluck('tempatKpi.nama_instansi')->implode(', ').'.', 'penempatan');

        return back()->with('sukses', $dipilih->count().' pilihan tempat KPI dikirim ke admin untuk diverifikasi.');
    }
}
