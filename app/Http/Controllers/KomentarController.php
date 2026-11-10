<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Progress;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Fitur komentar/konsultasi: pembimbing memberi feedback,
 * mahasiswa bisa membalas atau bertanya pada logbook dan progress yang sama.
 */
class KomentarController extends Controller
{
    public function store(Request $request, string $tipe, int $id)
    {
        $model = $tipe === 'logbook' ? Logbook::class : Progress::class;
        $item = $model::with('pengajuan.mahasiswa', 'pengajuan.pembimbing')->findOrFail($id);
        $pengajuan = $item->pengajuan;
        $user = $request->user();

        $dariMahasiswa = $pengajuan->mahasiswa_id === $user->id;
        $dariPembimbing = $pengajuan->pembimbing_id === $user->id;
        abort_unless($dariMahasiswa || $dariPembimbing, 403);

        $data = $request->validate(['isi' => ['required', 'string', 'max:1000']]);

        $item->komentar()->create(['user_id' => $user->id, 'isi' => $data['isi']]);

        $penerima = $dariMahasiswa ? $pengajuan->pembimbing : $pengajuan->mahasiswa;
        $url = $dariMahasiswa
            ? route('pembimbing.bimbingan.show', $pengajuan).'#'.$tipe.'-'.$item->id
            : route($tipe === 'logbook' ? 'mahasiswa.logbook.index' : 'mahasiswa.progress.index').'#'.$tipe.'-'.$item->id;

        Notifikasi::kirim($penerima, 'Komentar baru pada '.$tipe.' KPI', [
            $user->name.' menulis komentar:',
            '"'.Str::limit($data['isi'], 300).'"',
        ], $url, 'Lihat komentar');

        return redirect()->to(url()->previous().'#'.$tipe.'-'.$item->id)->with('sukses', 'Komentar terkirim.');
    }
}
