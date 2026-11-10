<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKpi;
use App\Models\Progress;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/** Fitur 8 (mahasiswa): memperbarui dan memantau progress KPI. */
class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $request->user()->pengajuanKpi()
            ->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])
            ->with(['progress.komentar.user', 'progressTerakhir', 'pembimbing'])
            ->latest()->first();

        return view('mahasiswa.progress.index', compact('pengajuan'));
    }

    public function store(Request $request)
    {
        $pengajuan = $request->user()->pengajuanKpi()->where('status', PengajuanKpi::BERJALAN)->latest()->first();
        abort_unless($pengajuan, 403, 'Tidak ada KPI yang sedang berjalan.');

        $minimal = $pengajuan->persentase;

        $data = Arr::except($request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'min:20', 'max:3000'],
            'persentase' => ['required', 'integer', 'min:'.$minimal, 'max:100'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ], [
            'persentase.min' => "Persentase tidak boleh lebih kecil dari progress sebelumnya ({$minimal}%).",
        ]), 'lampiran');

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store("lampiran/progress/{$pengajuan->id}", 'local');
        }

        $progress = $pengajuan->progress()->create($data + ['status' => Progress::MENUNGGU]);

        Notifikasi::kirim($pengajuan->pembimbing, 'Progress KPI baru dari '.$request->user()->name, [
            "{$request->user()->name} memperbarui progress KPI menjadi {$progress->persentase}%: {$progress->judul}.",
        ], route('pembimbing.bimbingan.show', $pengajuan).'#progress-'.$progress->id, 'Periksa progress');

        return redirect()->route('mahasiswa.progress.index')->with('sukses', 'Progress tersimpan dan dikirim ke pembimbing.');
    }
}
