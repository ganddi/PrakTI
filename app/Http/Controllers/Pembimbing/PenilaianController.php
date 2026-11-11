<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\LaporanKpi;
use App\Models\NilaiAkhir;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-17: halaman "Nilai Mahasiswa" (mockup Gambar 4.18) dan form penilaian dosen pembimbing. */
class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $daftar = $request->user()->dosen->pengajuanBimbingan()
            ->with(['mahasiswa', 'tempatKpi', 'laporanTerakhir', 'penilaian', 'nilaiAkhir', 'kelompok.penempatan.dosen'])
            ->when($request->query('q'), fn ($q, $c) => $q->whereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nim', 'like', "%{$c}%")))
            ->paginate(10)->withQueryString();

        return view('pembimbing.penilaian.index', compact('daftar'));
    }

    public function edit(Request $request, PengajuanKpi $pengajuan)
    {
        $this->pastikan($request, $pengajuan);
        $pengajuan->load(['mahasiswa', 'tempatKpi', 'penilaian', 'laporanTerakhir'])->loadCount('logbooks');

        return view('pembimbing.penilaian.form', ['pengajuan' => $pengajuan, 'penilaian' => $pengajuan->penilaianJenis(Penilaian::PEMBIMBING)]);
    }

    public function update(Request $request, PengajuanKpi $pengajuan)
    {
        $this->pastikan($request, $pengajuan);
        $penilaian = $pengajuan->penilaianJenis(Penilaian::PEMBIMBING);
        if ($penilaian?->status === Penilaian::FINAL) {
            return back()->with('gagal', 'Nilai sudah ditetapkan dan tidak dapat diubah lagi.');
        }

        $aturan = ['aksi' => ['required', Rule::in(['draf', 'tetapkan'])], 'catatan' => ['nullable', 'string', 'max:2000']];
        foreach (array_keys(config('prakti.kriteria_pembimbing')) as $k) {
            $aturan["nilai.{$k}"] = ['required', 'numeric', 'between:0,100'];
        }
        $data = $request->validate($aturan, [], collect(config('prakti.kriteria_pembimbing'))->mapWithKeys(fn ($k, $kunci) => ["nilai.{$kunci}" => 'nilai '.strtolower($k['label'])])->all());

        $rincian = array_map('floatval', $data['nilai']);
        $final = $data['aksi'] === 'tetapkan';

        $penilaian = Penilaian::updateOrCreate(['mahasiswa_id' => $pengajuan->mahasiswa_id, 'jenis_penilaian' => Penilaian::PEMBIMBING], [
            'nilai' => Penilaian::hitungNilaiPembimbing($rincian),
            'catatan' => Penilaian::bungkusCatatan(['nama_penilai' => $request->user()->name, 'rincian' => $rincian, 'catatan' => $data['catatan'] ?? null]),
            'tanggal_penilaian' => today(),
            'status' => $final ? Penilaian::FINAL : Penilaian::DRAF,
        ]);

        if ($final) {
            $akhir = NilaiAkhir::hitung($pengajuan->mahasiswa_id);
            AktivitasSistem::catat("Nilai dosen pembimbing ditetapkan: {$penilaian->nilai}", 'penilaian', $pengajuan->mahasiswa_id);
            Notifikasi::kirim($pengajuan->mahasiswa, 'Nilai dosen pembimbing tersedia', [
                "{$request->user()->name} menetapkan nilai KPI Anda: {$penilaian->nilai}.",
                $akhir ? "Nilai akhir Anda: {$akhir->nilai_akhir} ({$akhir->predikat})." : 'Lengkapi nilai dosen penguji dan pembimbing lapangan agar nilai akhir dapat dihitung.',
            ], 'penilaian');
        }

        return redirect()->route('pembimbing.penilaian.index')->with('sukses', $final ? 'Nilai ditetapkan dan dapat dilihat mahasiswa.' : 'Draf nilai tersimpan.');
    }

    private function pastikan(Request $request, PengajuanKpi $pengajuan): void
    {
        BimbinganController::pastikan($request, $pengajuan);
        abort_unless($pengajuan->laporanTerakhir?->status === LaporanKpi::DISETUJUI, 403, 'Penilaian dapat diberikan setelah laporan mahasiswa disetujui.');
    }
}
