<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-08: periode KPI (SDD Tabel 3.5). */
class PeriodeController extends Controller
{
    public function index()
    {
        return view('admin.periode.index', [
            'periode' => PeriodeKpi::withCount(['kelompok', 'pengajuan'])->orderByDesc('tanggal_mulai')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $periode = PeriodeKpi::create($this->validasi($request));
        AktivitasSistem::catat("Membuat periode {$periode->nama_periode}", 'periode', $periode->id);

        return redirect()->route('admin.periode.show', $periode)->with('sukses', 'Periode dibuat. Sekarang bentuk kelompok KPI.');
    }

    public function show(PeriodeKpi $periode)
    {
        $periode->load(['kelompok' => fn ($q) => $q->withCount('anggotaAktif')->with(['penempatan.dosen', 'anggotaAktif.mahasiswa.pengajuanTerakhir.tempatKpi'])]);

        return view('admin.periode.show', [
            'periode' => $periode,
            'siap' => self::querySiap($periode->id)->with(['mahasiswa', 'tempatKpi'])->get(),
        ]);
    }

    public function update(Request $request, PeriodeKpi $periode)
    {
        $periode->update($this->validasi($request, $periode));

        return back()->with('sukses', 'Periode diperbarui.');
    }

    public function destroy(PeriodeKpi $periode)
    {
        if ($periode->pengajuan()->exists()) {
            return back()->with('gagal', 'Periode sudah dipakai pengajuan mahasiswa sehingga tidak dapat dihapus. Ubah statusnya menjadi selesai.');
        }
        $periode->delete();

        return redirect()->route('admin.periode.index')->with('sukses', 'Periode dihapus.');
    }

    /** Pengajuan disetujui dengan surat balasan terverifikasi yang belum masuk kelompok. */
    public static function querySiap(int $periodeId)
    {
        return PengajuanKpi::where('periode_id', $periodeId)->where('status', PengajuanKpi::DISETUJUI)
            ->whereHas('dokumen', fn ($q) => $q->where('jenis_dokumen', 'surat_balasan')->where('status_verifikasi', DokumenKpi::DIVERIFIKASI))
            ->whereDoesntHave('anggotaAktif');
    }

    private function validasi(Request $request, ?PeriodeKpi $periode = null): array
    {
        return $request->validate([
            'kode_periode' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('periode_kpi', 'kode_periode')->ignore($periode?->id)],
            'nama_periode' => ['required', 'string', 'max:100'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([PeriodeKpi::AKTIF, PeriodeKpi::SELESAI])],
        ]);
    }
}
