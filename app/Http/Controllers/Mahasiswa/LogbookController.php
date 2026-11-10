<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Fitur 7 (mahasiswa): logbook kegiatan harian. */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $this->pengajuanBerjalan($request)
            ?? $request->user()->pengajuanKpi()->where('status', PengajuanKpi::SELESAI)->latest()->first();

        $logbooks = $pengajuan?->logbooks()->with('komentar.user')->paginate(10);

        $perluIsiHariIni = $pengajuan
            && $pengajuan->status === PengajuanKpi::BERJALAN
            && today()->between($pengajuan->tanggal_mulai, $pengajuan->tanggal_selesai)
            && ! today()->isWeekend()
            && ! $pengajuan->logbooks()->whereDate('tanggal', today())->exists();

        return view('mahasiswa.logbook.index', compact('pengajuan', 'logbooks', 'perluIsiHariIni'));
    }

    public function create(Request $request)
    {
        $pengajuan = $this->pengajuanBerjalan($request);

        if (! $pengajuan) {
            return redirect()->route('mahasiswa.logbook.index')
                ->with('info', 'Logbook bisa diisi setelah pengajuan KPI Anda disetujui.');
        }

        $logbook = new Logbook([
            'tanggal' => today()->min($pengajuan->tanggal_selesai)->max($pengajuan->tanggal_mulai),
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
        ]);

        return view('mahasiswa.logbook.form', compact('pengajuan', 'logbook'));
    }

    public function store(Request $request)
    {
        $pengajuan = $this->pengajuanBerjalan($request);
        abort_unless($pengajuan, 403, 'Tidak ada KPI yang sedang berjalan.');

        // Kolom lampiran hanya diisi jika ada berkas baru (input file kosong tidak boleh menimpa data)
        $data = Arr::except($request->validate($this->aturan($pengajuan)), 'lampiran');

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store("lampiran/logbook/{$pengajuan->id}", 'local');
        }

        $pengajuan->logbooks()->create($data + ['status' => Logbook::MENUNGGU]);

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook tersimpan dan menunggu diperiksa pembimbing.');
    }

    public function edit(Request $request, Logbook $logbook)
    {
        $this->pastikanBisaDiubah($request, $logbook);
        $logbook->load('komentar.user');

        return view('mahasiswa.logbook.form', ['pengajuan' => $logbook->pengajuan, 'logbook' => $logbook]);
    }

    public function update(Request $request, Logbook $logbook)
    {
        $this->pastikanBisaDiubah($request, $logbook);
        $pengajuan = $logbook->pengajuan;

        $data = Arr::except($request->validate($this->aturan($pengajuan, $logbook)), 'lampiran');

        if ($request->hasFile('lampiran')) {
            if ($logbook->lampiran) {
                Storage::disk('local')->delete($logbook->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store("lampiran/logbook/{$pengajuan->id}", 'local');
        }

        // Setelah diperbaiki, logbook kembali menunggu diperiksa
        $logbook->update($data + ['status' => Logbook::MENUNGGU]);

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook diperbarui dan dikirim ulang ke pembimbing.');
    }

    public function destroy(Request $request, Logbook $logbook)
    {
        $this->pastikanBisaDiubah($request, $logbook);

        if ($logbook->lampiran) {
            Storage::disk('local')->delete($logbook->lampiran);
        }
        $logbook->komentar()->delete();
        $logbook->delete();

        return redirect()->route('mahasiswa.logbook.index')->with('sukses', 'Logbook dihapus.');
    }

    private function pengajuanBerjalan(Request $request): ?PengajuanKpi
    {
        return $request->user()->pengajuanKpi()->where('status', PengajuanKpi::BERJALAN)->latest()->first();
    }

    private function pastikanBisaDiubah(Request $request, Logbook $logbook): void
    {
        abort_unless($logbook->pengajuan->mahasiswa_id === $request->user()->id, 403);
        abort_if($logbook->status === Logbook::DISETUJUI, 403, 'Logbook yang sudah disetujui pembimbing tidak dapat diubah.');
        abort_if($logbook->pengajuan->status !== PengajuanKpi::BERJALAN, 403, 'KPI ini sudah tidak berjalan.');
    }

    private function aturan(PengajuanKpi $pengajuan, ?Logbook $logbook = null): array
    {
        $batasAkhir = today()->min($pengajuan->tanggal_selesai);

        return [
            'tanggal' => [
                'required', 'date',
                'after_or_equal:'.$pengajuan->tanggal_mulai->toDateString(),
                'before_or_equal:'.$batasAkhir->toDateString(),
                Rule::unique('logbooks', 'tanggal')->where('pengajuan_kpi_id', $pengajuan->id)->ignore($logbook?->id),
            ],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'kegiatan' => ['required', 'string', 'min:20', 'max:3000'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
