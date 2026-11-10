<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;

/** Fitur 7 & 8 (pembimbing): daftar mahasiswa bimbingan dan detail perkembangannya. */
class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = $request->user()->mahasiswaBimbingan()
            ->with(['mahasiswa', 'progressTerakhir'])
            ->withCount([
                'logbooks',
                'logbooks as logbook_menunggu_count' => fn ($q) => $q->where('status', Logbook::MENUNGGU),
            ])
            ->withMax('logbooks', 'tanggal')
            ->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])
            ->orderByRaw("status = 'selesai'")   // yang sedang berjalan tampil lebih dulu
            ->orderBy('tanggal_mulai')
            ->get();

        return view('pembimbing.bimbingan.index', compact('bimbingan'));
    }

    public function show(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->pembimbing_id === $request->user()->id, 403);

        $pengajuan->load([
            'mahasiswa', 'pembimbing', 'dokumen.pengunggah', 'progressTerakhir',
            'logbooks.komentar.user', 'progress.komentar.user',
        ]);

        return view('pembimbing.bimbingan.show', compact('pengajuan'));
    }
}
