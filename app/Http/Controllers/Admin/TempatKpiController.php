<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\TempatKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-24: informasi tempat KPI (SDD Tabel 3.4). Tempat berstatus aktif tampil di landing page. */
class TempatKpiController extends Controller
{
    public function index(Request $request)
    {
        $tempat = TempatKpi::withCount('pengajuan')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByRaw("status = 'usulan' desc")->orderBy('nama_instansi')->get();

        return view('admin.tempat.index', compact('tempat'));
    }

    public function create() { return view('admin.tempat.form', ['tempat' => new TempatKpi(['status' => TempatKpi::AKTIF, 'provinsi' => 'Sulawesi Barat'])]); }

    public function store(Request $request)
    {
        $t = TempatKpi::create($this->validasi($request));
        AktivitasSistem::catat("Menambah tempat KPI {$t->nama_instansi}", 'tempat', $t->id);

        return redirect()->route('admin.tempat.index')->with('sukses', 'Tempat KPI ditambahkan.');
    }

    public function edit(TempatKpi $tempat) { return view('admin.tempat.form', compact('tempat')); }

    public function update(Request $request, TempatKpi $tempat)
    {
        $tempat->update($this->validasi($request));
        AktivitasSistem::catat("Memperbarui tempat KPI {$tempat->nama_instansi}", 'tempat', $tempat->id);

        return redirect()->route('admin.tempat.index')->with('sukses', 'Informasi tempat KPI diperbarui.');
    }

    public function destroy(TempatKpi $tempat)
    {
        if ($tempat->pengajuan()->exists()) {
            return back()->with('gagal', 'Tempat ini dipakai pengajuan mahasiswa. Ubah statusnya menjadi nonaktif agar tidak tampil.');
        }
        $tempat->delete();

        return back()->with('sukses', 'Tempat KPI dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_instansi' => ['required', 'string', 'max:100'],
            'jenis_instansi' => ['required', 'string', 'max:50'],
            'alamat' => ['required', 'string', 'max:500'],
            'kota' => ['required', 'string', 'max:50'],
            'provinsi' => ['required', 'string', 'max:50'],
            'nama_pic' => ['nullable', 'string', 'max:100'],
            'email_pic' => ['nullable', 'email', 'max:100'],
            'no_hp_pic' => ['nullable', 'string', 'max:15'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in([TempatKpi::AKTIF, TempatKpi::NONAKTIF, TempatKpi::USULAN])],
        ]);
    }
}
