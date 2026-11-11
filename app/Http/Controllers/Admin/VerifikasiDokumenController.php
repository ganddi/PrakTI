<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\NilaiAkhir;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-07 & FR-21: verifikasi surat balasan, dokumen penilaian, dan dokumen nilai akhir. */
class VerifikasiDokumenController extends Controller
{
    public const KELOMPOK = [
        'surat' => ['label' => 'Surat balasan', 'jenis' => ['surat_balasan']],
        'penilaian' => ['label' => 'Dokumen penilaian', 'jenis' => ['penilaian_penguji', 'penilaian_lapangan']],
        'nilai_akhir' => ['label' => 'Dokumen nilai akhir', 'jenis' => ['nilai_akhir']],
    ];

    private const JENIS_NILAI = ['penilaian_penguji' => Penilaian::PENGUJI, 'penilaian_lapangan' => Penilaian::LAPANGAN];

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'surat');
        abort_unless(array_key_exists($tab, self::KELOMPOK), 404);
        $status = $request->query('status', DokumenKpi::MENUNGGU);
        abort_unless(in_array($status, [DokumenKpi::MENUNGGU, DokumenKpi::DIVERIFIKASI, DokumenKpi::REVISI], true), 404);

        $dokumen = DokumenKpi::with(['pengajuan.mahasiswa', 'pengajuan.tempatKpi', 'pengajuan.penilaian', 'pengajuan.nilaiAkhir'])
            ->whereIn('jenis_dokumen', self::KELOMPOK[$tab]['jenis'])->where('status_verifikasi', $status)
            ->when($status === DokumenKpi::MENUNGGU, fn ($q) => $q->oldest('id'), fn ($q) => $q->latest('verified_at'))
            ->paginate(15)->withQueryString();

        $jumlah = collect(self::KELOMPOK)->map(fn ($k) => DokumenKpi::whereIn('jenis_dokumen', $k['jenis'])->where('status_verifikasi', DokumenKpi::MENUNGGU)->count());

        return view('admin.verifikasi.index', compact('dokumen', 'tab', 'status', 'jumlah'));
    }

    public function update(Request $request, DokumenKpi $dokumen)
    {
        abort_unless(in_array($dokumen->jenis_dokumen, DokumenKpi::PERLU_VERIFIKASI, true), 422);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in([DokumenKpi::DIVERIFIKASI, DokumenKpi::REVISI])],
            'catatan' => ['nullable', 'required_if:keputusan,revisi', 'string', 'max:1000'],
        ]);
        $ok = $data['keputusan'] === DokumenKpi::DIVERIFIKASI;
        $pengajuan = $dokumen->pengajuan()->with(['mahasiswa', 'dokumen'])->first();

        if ($ok && $dokumen->jenis_dokumen === 'nilai_akhir') {
            foreach (array_keys(self::JENIS_NILAI) as $jenis) {
                if ($pengajuan->dokumenTerakhir($jenis)?->status_verifikasi !== DokumenKpi::DIVERIFIKASI) {
                    return back()->with('gagal', 'Verifikasi dulu '.strtolower(DokumenKpi::JENIS[$jenis]).' milik mahasiswa ini.');
                }
            }
        }

        $dokumen->update(['status_verifikasi' => $data['keputusan'], 'catatan' => $data['catatan'] ?? null, 'verified_at' => now()]);

        // Status nilai eksternal mengikuti hasil verifikasi dokumennya (NFR-19)
        if ($jenisNilai = self::JENIS_NILAI[$dokumen->jenis_dokumen] ?? null) {
            Penilaian::where('mahasiswa_id', $pengajuan->mahasiswa_id)->where('jenis_penilaian', $jenisNilai)
                ->update(['status' => $ok ? Penilaian::DIVERIFIKASI : Penilaian::DIINPUT]);
        }

        if ($ok && $dokumen->jenis_dokumen === 'nilai_akhir') {
            NilaiAkhir::where('mahasiswa_id', $pengajuan->mahasiswa_id)->update(['status' => NilaiAkhir::DIVERIFIKASI]);
            $pengajuan->update(['status' => PengajuanKpi::SELESAI]);
            AktivitasSistem::catat('Proses KPI selesai', 'nilai_akhir', $pengajuan->mahasiswa_id);
        }

        $modul = match ($dokumen->jenis_dokumen) { 'surat_balasan' => 'surat', 'nilai_akhir' => 'nilai_akhir', default => 'penilaian' };
        AktivitasSistem::catat($dokumen->jenis_label.($ok ? ' diverifikasi' : ' perlu diperbaiki'), $modul, $pengajuan->mahasiswa_id, $data['catatan'] ?? null);

        Notifikasi::kirim($pengajuan->mahasiswa, $dokumen->jenis_label.($ok ? ' terverifikasi' : ' perlu diperbaiki'), [
            $ok ? "{$dokumen->jenis_label} Anda telah diverifikasi admin." : "{$dokumen->jenis_label} Anda belum dapat diterima.",
            ! empty($data['catatan']) ? 'Catatan admin: '.$data['catatan'] : null,
            $ok && $dokumen->jenis_dokumen === 'nilai_akhir' ? 'Selamat, seluruh proses KPI Anda selesai.' : null,
        ], $modul);

        return back()->with('sukses', $ok ? 'Dokumen diverifikasi.' : 'Catatan perbaikan terkirim ke mahasiswa.');
    }
}
