<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Logbook;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Semua berkas disimpan di storage privat (bukan folder public),
 * sehingga hanya bisa diunduh lewat route ini setelah hak aksesnya dicek.
 */
class DokumenController extends Controller
{
    public function unduh(Request $request, Dokumen $dokumen)
    {
        abort_unless($dokumen->pengajuan->bisaDiaksesOleh($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($dokumen->path), 404, 'Berkas tidak ditemukan di server.');

        return Storage::disk('local')->download($dokumen->path, $dokumen->nama_asli);
    }

    public function lampiran(Request $request, string $tipe, int $id)
    {
        $model = $tipe === 'logbook' ? Logbook::class : Progress::class;
        $item = $model::with('pengajuan')->findOrFail($id);

        abort_unless($item->lampiran && $item->pengajuan->bisaDiaksesOleh($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($item->lampiran), 404, 'Lampiran tidak ditemukan di server.');

        return Storage::disk('local')->response($item->lampiran);
    }
}
