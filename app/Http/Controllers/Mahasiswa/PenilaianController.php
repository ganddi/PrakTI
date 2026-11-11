<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\LaporanKpi;
use App\Models\NilaiAkhir;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\Penilaian;
use App\Models\User;
use App\Support\Berkas;
use App\Support\Template;
use Illuminate\Http\Request;

/** FR-17 (melihat nilai), FR-18 (nilai eksternal), FR-19 & FR-20 (rekapitulasi dan dokumen nilai akhir). */
class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        $pengajuan?->load(['penilaian', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'tempatKpi', 'kelompok.penempatan.dosen']);

        return view('mahasiswa.penilaian.index', [
            'pengajuan' => $pengajuan,
            'template' => Template::daftar('penilaian'),
            'bisaUbah' => $pengajuan && $this->bisaUbah($pengajuan),
        ]);
    }

    public function eksternal(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        abort_unless($pengajuan?->status === PengajuanKpi::BERJALAN && $pengajuan->laporanTerakhir?->status === LaporanKpi::DISETUJUI, 403,
            'Nilai eksternal diisi setelah laporan disetujui dosen pembimbing.');
        $pengajuan->load('dokumen');

        if (! $this->bisaUbah($pengajuan)) {
            return back()->with('gagal', 'Nilai eksternal sudah diverifikasi admin dan tidak dapat diubah.');
        }

        $data = $request->validate([
            'nama_penguji' => ['required', 'string', 'max:100'],
            'nilai_penguji' => ['required', 'numeric', 'between:0,100'],
            'nama_pembimbing_lapangan' => ['required', 'string', 'max:100'],
            'nilai_lapangan' => ['required', 'numeric', 'between:0,100'],
            'dokumen_penguji' => [$pengajuan->dokumenTerakhir('penilaian_penguji') ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'dokumen_lapangan' => [$pengajuan->dokumenTerakhir('penilaian_lapangan') ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        foreach ([Penilaian::PENGUJI => ['nama_penguji', 'nilai_penguji', 'dokumen_penguji', 'penilaian_penguji'],
                  Penilaian::LAPANGAN => ['nama_pembimbing_lapangan', 'nilai_lapangan', 'dokumen_lapangan', 'penilaian_lapangan']] as $jenis => [$nama, $nilai, $input, $jenisDok]) {
            Penilaian::updateOrCreate(['mahasiswa_id' => $pengajuan->mahasiswa_id, 'jenis_penilaian' => $jenis], [
                'nilai' => $data[$nilai], 'catatan' => Penilaian::bungkusCatatan(['nama_penilai' => $data[$nama]]),
                'tanggal_penilaian' => today(), 'status' => Penilaian::DIINPUT,
            ]);
            if ($request->hasFile($input)) {
                Berkas::simpanDokumen($pengajuan, $request->file($input), $jenisDok);
            } else {
                $pengajuan->dokumenTerakhir($jenisDok)?->update(['status_verifikasi' => DokumenKpi::MENUNGGU]);
            }
        }

        $akhir = NilaiAkhir::hitung($pengajuan->mahasiswa_id);
        AktivitasSistem::catat('Nilai dosen penguji dan pembimbing lapangan diisi', 'penilaian', $pengajuan->mahasiswa_id, $akhir ? "Nilai akhir {$akhir->nilai_akhir} ({$akhir->predikat})" : null);
        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Dokumen penilaian menunggu verifikasi',
            "{$request->user()->name} mengisi nilai penguji dan pembimbing lapangan beserta dokumennya.", 'penilaian', $pengajuan->id);

        return back()->with('sukses', $akhir
            ? "Nilai tersimpan. Nilai akhir Anda: {$akhir->nilai_akhir} ({$akhir->predikat}). Lihat menu Rekapitulasi nilai."
            : 'Nilai tersimpan. Nilai akhir dihitung setelah dosen pembimbing menetapkan nilainya.');
    }

    public function nilai(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        $pengajuan?->load(['penilaian', 'dokumen', 'nilaiAkhir', 'tempatKpi', 'kelompok.penempatan.dosen']);

        return view('mahasiswa.penilaian.nilai', compact('pengajuan'));
    }

    public function unggahNilaiAkhir(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        abort_unless($pengajuan?->nilaiAkhir, 403, 'Nilai akhir belum tersedia.');

        $terakhir = $pengajuan->dokumenTerakhir('nilai_akhir');
        if (in_array($terakhir?->status_verifikasi, [DokumenKpi::DIVERIFIKASI, DokumenKpi::MENUNGGU], true)) {
            return back()->with('info', 'Dokumen nilai akhir sudah diunggah.');
        }

        $request->validate(['berkas' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        Berkas::simpanDokumen($pengajuan, $request->file('berkas'), 'nilai_akhir');
        AktivitasSistem::catat('Dokumen nilai akhir bertanda tangan diunggah', 'nilai_akhir', $pengajuan->mahasiswa_id);
        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Dokumen nilai akhir menunggu verifikasi',
            "{$request->user()->name} mengunggah dokumen nilai akhir yang sudah disahkan.", 'nilai_akhir', $pengajuan->id);

        return back()->with('sukses', 'Dokumen nilai akhir berhasil diunggah dan menunggu verifikasi admin.');
    }

    private function bisaUbah(PengajuanKpi $p): bool
    {
        return $p->status === PengajuanKpi::BERJALAN && ! collect(['penilaian_penguji', 'penilaian_lapangan'])
            ->every(fn ($j) => $p->dokumenTerakhir($j)?->status_verifikasi === DokumenKpi::DIVERIFIKASI);
    }

    private function pengajuan(Request $request): ?PengajuanKpi
    {
        return $request->user()->mahasiswa->pengajuanKpi()->whereIn('status', [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI])->latest('id')->first();
    }
}
