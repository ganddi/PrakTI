<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\PengajuanKpi;
use App\Support\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DokumenController extends Controller
{
    public function store(Request $request, PengajuanKpi $pengajuan)
    {
        $data = $request->validate([
            'jenis_dokumen' => ['required', Rule::in(array_keys(DokumenKpi::JENIS))],
            'berkas' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $dokumen = Berkas::simpanDokumen($pengajuan, $request->file('berkas'), $data['jenis_dokumen']);
        AktivitasSistem::catat('Admin menambahkan dokumen KPI', 'dokumen', $pengajuan->mahasiswa_id, $dokumen->jenis_label.': '.$dokumen->nama_file);

        return back()->with('sukses', 'Dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, DokumenKpi $dokumen)
    {
        $data = $request->validate([
            'jenis_dokumen' => ['required', Rule::in(array_keys(DokumenKpi::JENIS))],
            'berkas' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $jenisBerubah = $dokumen->jenis_dokumen !== $data['jenis_dokumen'];
        $berkasBaru = $request->file('berkas');
        $pathLama = $dokumen->path_file;
        $perluVerifikasiUlang = $jenisBerubah || $berkasBaru !== null;

        $perubahan = ['jenis_dokumen' => $data['jenis_dokumen']];
        if ($berkasBaru) {
            $perubahan += [
                'nama_file' => Berkas::namaAman($berkasBaru),
                'format_file' => substr(strtolower($berkasBaru->getClientOriginalExtension()), 0, 20),
                'ukuran_file' => $berkasBaru->getSize(),
                'path_file' => $berkasBaru->store('dokumen/'.$dokumen->mahasiswa_id, config('filesystems.default')),
                'uploaded_at' => now(),
            ];
        }

        if ($perluVerifikasiUlang) {
            $perubahan += [
                'status_verifikasi' => in_array($data['jenis_dokumen'], DokumenKpi::PERLU_VERIFIKASI, true) ? DokumenKpi::MENUNGGU : DokumenKpi::TERSIMPAN,
                'catatan' => null,
                'verified_at' => null,
            ];
        }

        $dokumen->update($perubahan);
        if ($berkasBaru) {
            Storage::disk()->delete($pathLama);
        }

        AktivitasSistem::catat('Admin memperbarui dokumen KPI', 'dokumen', $dokumen->mahasiswa_id, $dokumen->jenis_label.': '.$dokumen->nama_file);

        return back()->with('sukses', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(DokumenKpi $dokumen)
    {
        $nama = $dokumen->nama_file;
        $mahasiswaId = $dokumen->mahasiswa_id;
        $path = $dokumen->path_file;

        $dokumen->delete();
        Storage::disk()->delete($path);
        AktivitasSistem::catat('Admin menghapus dokumen KPI', 'dokumen', $mahasiswaId, $nama);

        return back()->with('sukses', 'Dokumen berhasil dihapus.');
    }
}
