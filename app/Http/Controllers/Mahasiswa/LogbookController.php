<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use App\Support\Berkas;
use App\Support\Template;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-12: logbook kegiatan (SDD Tabel 3.11). */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $this->pengajuan($request, [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI]);
        $logbooks = $pengajuan?->logbooks()->paginate(10);

        $perluIsi = $pengajuan && $pengajuan->status === PengajuanKpi::BERJALAN && ! today()->isWeekend()
            && today()->between($pengajuan->tanggal_mulai, $pengajuan->tanggal_selesai)
            && ! Logbook::where('mahasiswa_id', $pengajuan->mahasiswa_id)->whereDate('tanggal', today())->exists();

        return view('mahasiswa.logbook.index', [
            'pengajuan' => $pengajuan, 'logbooks' => $logbooks, 'perluIsiHariIni' => $perluIsi,
            'dokumenLogbook' => $pengajuan?->dokumen()->where('jenis_dokumen', 'logbook')->get() ?? collect(),
            'templateLogbook' => Template::daftar('logbook'),
        ]);
    }

    public function create(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        if (! $pengajuan) {
            return redirect()->route('mahasiswa.logbook.index')->with('info', 'Logbook bisa diisi setelah Anda ditempatkan di kelompok KPI.');
        }

        return view('mahasiswa.logbook.form', ['pengajuan' => $pengajuan, 'logbook' => new Logbook(['tanggal' => today()->min($pengajuan->tanggal_selesai)->max($pengajuan->tanggal_mulai)])]);
    }

    public function store(Request $request)
    {
        $pengajuan = $this->pengajuan($request);
        abort_unless($pengajuan, 403);

        $data = $request->validate($this->aturan($pengajuan));
        Logbook::create($data + ['mahasiswa_id' => $pengajuan->mahasiswa_id, 'status' => Logbook::MENUNGGU]);
        AktivitasSistem::catat('Mengisi logbook '.$data['tanggal'], 'logbook', $pengajuan->mahasiswa_id);

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook tersimpan dan menunggu diperiksa pembimbing.');
    }

    public function edit(Request $request, Logbook $logbook)
    {
        $pengajuan = $this->pastikanBisaDiubah($request, $logbook);

        return view('mahasiswa.logbook.form', compact('pengajuan', 'logbook'));
    }

    public function update(Request $request, Logbook $logbook)
    {
        $pengajuan = $this->pastikanBisaDiubah($request, $logbook);
        $logbook->update($request->validate($this->aturan($pengajuan, $logbook)) + ['status' => Logbook::MENUNGGU]);

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook diperbarui dan dikirim ulang ke pembimbing.');
    }

    public function destroy(Request $request, Logbook $logbook)
    {
        $this->pastikanBisaDiubah($request, $logbook);
        $logbook->delete();

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook dihapus.');
    }

    public function unggahDokumen(Request $request)
    {
        $pengajuan = $this->pengajuan($request, [PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI]);
        abort_unless($pengajuan, 403);

        $request->validate(['berkas' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']]);
        Berkas::simpanDokumen($pengajuan, $request->file('berkas'), 'logbook');
        AktivitasSistem::catat('Dokumen logbook bertanda tangan diunggah', 'dokumen', $pengajuan->mahasiswa_id);

        return back()->with('sukses', 'Dokumen logbook berhasil diunggah.');
    }

    private function pengajuan(Request $request, array $status = [PengajuanKpi::BERJALAN]): ?PengajuanKpi
    {
        return $request->user()->mahasiswa->pengajuanKpi()->with('periode', 'tempatKpi')->whereIn('status', $status)->latest('id')->first();
    }

    private function pastikanBisaDiubah(Request $request, Logbook $logbook): PengajuanKpi
    {
        abort_unless($logbook->mahasiswa_id === $request->user()->mahasiswa->id, 403);
        abort_if($logbook->status === Logbook::DISETUJUI, 403, 'Logbook yang sudah disetujui pembimbing tidak dapat diubah.');
        $pengajuan = $this->pengajuan($request);
        abort_unless($pengajuan, 403, 'KPI ini sudah tidak berjalan.');

        return $pengajuan;
    }

    private function aturan(PengajuanKpi $p, ?Logbook $logbook = null): array
    {
        return [
            'tanggal' => ['required', 'date', 'after_or_equal:'.$p->tanggal_mulai->toDateString(), 'before_or_equal:'.today()->min($p->tanggal_selesai)->toDateString(),
                Rule::unique('logbook', 'tanggal')->where('mahasiswa_id', $p->mahasiswa_id)->ignore($logbook?->id)],
            'judul_kegiatan' => ['required', 'string', 'max:100'],
            'deskripsi_kegiatan' => ['required', 'string', 'min:20', 'max:3000'],
            'hasil_kegiatan' => ['nullable', 'string', 'max:2000'],
            'kendala' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
