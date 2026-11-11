<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DosenPembimbing;
use App\Models\KelompokKpi;
use App\Models\Notifikasi;
use App\Models\PenempatanDosen;
use App\Models\PeriodeKpi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * FR-09 & FR-10 (SDD Tabel 3.8). Satuan penempatan = kelompok + tempat KPI.
 * Admin membagikan ke dosen, dosen memilih, admin memverifikasi.
 */
class PenempatanController extends Controller
{
    public function index(Request $request)
    {
        $daftarPeriode = PeriodeKpi::orderByDesc('tanggal_mulai')->get();
        $periode = $daftarPeriode->firstWhere('id', (int) $request->query('periode')) ?? $daftarPeriode->firstWhere('status', PeriodeKpi::AKTIF) ?? $daftarPeriode->first();

        $kelompok = $periode
            ? $periode->kelompok()->with(['penempatan.dosen', 'anggotaAktif.mahasiswa.pengajuanTerakhir.tempatKpi'])->get()
            : collect();

        $dosen = DosenPembimbing::whereHas('user', fn ($u) => $u->where('status', User::AKTIF))
            ->withCount(['penempatan as jumlah_disetujui' => fn ($q) => $q->where('status', PenempatanDosen::DISETUJUI)])
            ->orderBy('nama_lengkap')->get();

        return view('admin.penempatan.index', [
            'daftarPeriode' => $daftarPeriode, 'periode' => $periode, 'kelompok' => $kelompok, 'dosen' => $dosen,
            'menunggu' => PenempatanDosen::where('status', PenempatanDosen::DIAJUKAN)->count(),
        ]);
    }

    public function tawarkan(Request $request, KelompokKpi $kelompok)
    {
        $data = $request->validate([
            'tempat_kpi_id' => ['required', Rule::in($kelompok->daftarTempat()->pluck('id')->all())],
            'dosen_id' => ['required', 'array', 'min:1'],
            'dosen_id.*' => ['exists:dosen_pembimbing,id'],
        ]);

        if ($kelompok->pembimbingUntuk((int) $data['tempat_kpi_id'])) {
            return back()->with('gagal', 'Tempat ini sudah memiliki dosen pembimbing di kelompok tersebut.');
        }

        foreach ($data['dosen_id'] as $id) {
            $ada = PenempatanDosen::where(['kelompok_id' => $kelompok->id, 'tempat_kpi_id' => $data['tempat_kpi_id'], 'dosen_id' => $id])
                ->where('status', '!=', PenempatanDosen::DIBATALKAN)->exists();
            if ($ada) {
                continue;
            }
            $p = PenempatanDosen::create([
                'kelompok_id' => $kelompok->id, 'tempat_kpi_id' => $data['tempat_kpi_id'], 'dosen_id' => $id,
                'tanggal_pengajuan' => today(), 'status' => PenempatanDosen::DITAWARKAN,
            ]);
            Notifikasi::kirim($p->dosen, 'Tempat KPI tersedia untuk dipilih',
                "{$p->tempatKpi->nama_instansi} ({$kelompok->nama_kelompok}) dibagikan kepada Anda. Pilih jika Anda bersedia membimbing.", 'penempatan', $p->id);
        }

        return back()->with('sukses', 'Tempat KPI dibagikan ke dosen yang dipilih.');
    }

    public function verifikasi(Request $request, PenempatanDosen $penempatan)
    {
        if ($penempatan->status !== PenempatanDosen::DIAJUKAN) {
            return back()->with('gagal', 'Pilihan ini tidak sedang menunggu verifikasi.');
        }

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['setujui', 'perbaikan'])],
            'catatan_admin' => ['nullable', 'required_if:keputusan,perbaikan', 'string', 'max:1000'],
        ]);
        $penempatan->load(['dosen', 'tempatKpi', 'kelompok']);

        if ($data['keputusan'] === 'perbaikan') {
            $penempatan->update(['status' => PenempatanDosen::PERLU_PERBAIKAN, 'catatan_admin' => $data['catatan_admin'], 'verified_at' => now()]);
            Notifikasi::kirim($penempatan->dosen, 'Pilihan tempat KPI perlu diperbaiki', 'Catatan admin: '.$data['catatan_admin'], 'penempatan', $penempatan->id);

            return back()->with('sukses', 'Catatan perbaikan terkirim ke dosen.');
        }

        if ($penempatan->kelompok->pembimbingUntuk($penempatan->tempat_kpi_id)) {
            return back()->with('gagal', 'Tempat ini sudah memiliki dosen pembimbing di kelompok tersebut.');
        }

        DB::transaction(function () use ($penempatan, $data) {
            $penempatan->update(['status' => PenempatanDosen::DISETUJUI, 'catatan_admin' => $data['catatan_admin'] ?? null, 'verified_at' => now()]);
            PenempatanDosen::where('kelompok_id', $penempatan->kelompok_id)->where('tempat_kpi_id', $penempatan->tempat_kpi_id)
                ->where('id', '!=', $penempatan->id)->where('status', '!=', PenempatanDosen::DISETUJUI)->update(['status' => PenempatanDosen::DIBATALKAN]);
        });

        $tercakup = $penempatan->pengajuanTercakup()->with('mahasiswa')->get();
        foreach ($tercakup as $p) {
            AktivitasSistem::catat("Dosen pembimbing ditetapkan: {$penempatan->dosen->nama_lengkap}", 'penempatan', $p->mahasiswa_id);
        }

        Notifikasi::kirim($penempatan->dosen, 'Penempatan bimbingan disetujui',
            "Anda menjadi dosen pembimbing {$penempatan->kelompok->nama_kelompok} di {$penempatan->tempatKpi->nama_instansi} ({$tercakup->count()} mahasiswa).", 'penempatan', $penempatan->id);
        Notifikasi::kirim($tercakup->pluck('mahasiswa'), 'Dosen pembimbing KPI ditetapkan', "Dosen pembimbing Anda adalah {$penempatan->dosen->nama_lengkap}.", 'penempatan');

        return back()->with('sukses', "Penempatan disetujui. {$penempatan->dosen->nama_lengkap} menjadi pembimbing.");
    }

    public function batalkan(PenempatanDosen $penempatan)
    {
        abort_unless($penempatan->status === PenempatanDosen::DISETUJUI, 422);
        $penempatan->update(['status' => PenempatanDosen::DIBATALKAN]);

        foreach ($penempatan->pengajuanTercakup()->get() as $p) {
            AktivitasSistem::catat("Penempatan dosen {$penempatan->dosen->nama_lengkap} dibatalkan", 'penempatan', $p->mahasiswa_id);
        }

        return back()->with('sukses', 'Penempatan dibatalkan. Tempat ini dapat dibagikan ulang ke dosen lain.');
    }
}
