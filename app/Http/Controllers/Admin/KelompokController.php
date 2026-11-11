<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\AnggotaKelompok;
use App\Models\KelompokKpi;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** FR-08: kelompok KPI dan anggotanya (SDD Tabel 3.6 dan 3.7). */
class KelompokController extends Controller
{
    public function store(Request $request, PeriodeKpi $periode)
    {
        $kelompok = $periode->kelompok()->create($this->validasi($request) + ['status' => 'aktif']);

        return redirect()->route('admin.kelompok.show', $kelompok)->with('sukses', 'Kelompok dibuat. Tambahkan anggota di bawah.');
    }

    /** Satu kelompok per tempat KPI untuk mahasiswa yang siap ditempatkan. */
    public function otomatis(PeriodeKpi $periode)
    {
        $siap = PeriodeController::querySiap($periode->id)->with(['mahasiswa', 'tempatKpi'])->get()->groupBy('tempat_kpi_id');
        if ($siap->isEmpty()) {
            return back()->with('info', 'Tidak ada mahasiswa yang siap dikelompokkan.');
        }

        $periode->load('kelompok.anggotaAktif.mahasiswa.pengajuanTerakhir');
        $jumlah = 0;

        DB::transaction(function () use ($siap, $periode, &$jumlah) {
            $nomor = $periode->kelompok()->reorder()->count();
            foreach ($siap as $tempatId => $daftar) {
                $kelompok = $periode->kelompok->first(fn ($k) => $k->daftarTempat()->pluck('id')->contains($tempatId));
                if (! $kelompok) {
                    $nomor++;
                    $kelompok = $periode->kelompok()->create([
                        'kode_kelompok' => $periode->kode_periode.'-K'.str_pad($nomor, 2, '0', STR_PAD_LEFT),
                        'nama_kelompok' => 'Kelompok '.$nomor,
                        'keterangan' => 'Dibentuk otomatis untuk '.$daftar->first()->tempatKpi->nama_instansi,
                        'status' => 'aktif',
                    ]);
                }
                $daftar->each(function ($p) use ($kelompok, &$jumlah) {
                    $this->masukkan($kelompok, $p);
                    $jumlah++;
                });
            }
        });

        return back()->with('sukses', "{$jumlah} mahasiswa dikelompokkan ke ".$siap->count().' kelompok berdasarkan tempat KPI.');
    }

    public function show(KelompokKpi $kelompok)
    {
        $kelompok->load(['periode', 'anggota.mahasiswa.pengajuanTerakhir.tempatKpi', 'penempatan.dosen', 'penempatan.tempatKpi']);
        $tempatKelompok = $kelompok->daftarTempat()->pluck('id');

        $kandidat = PeriodeController::querySiap($kelompok->periode_id)->with(['mahasiswa', 'tempatKpi'])->get()
            ->sortBy(fn ($p) => $tempatKelompok->contains($p->tempat_kpi_id) ? 0 : 1)->values();

        return view('admin.kelompok.show', compact('kelompok', 'kandidat', 'tempatKelompok'));
    }

    public function update(Request $request, KelompokKpi $kelompok)
    {
        $kelompok->update($this->validasi($request, $kelompok) + $request->validate(['status' => ['required', Rule::in(['aktif', 'selesai'])]]));

        return back()->with('sukses', 'Data kelompok diperbarui.');
    }

    public function destroy(KelompokKpi $kelompok)
    {
        if ($kelompok->anggotaAktif()->exists()) {
            return back()->with('gagal', 'Kelompok masih memiliki anggota aktif.');
        }
        $periode = $kelompok->periode;
        $kelompok->delete();

        return redirect()->route('admin.periode.show', $periode)->with('sukses', 'Kelompok dihapus.');
    }

    public function tambahAnggota(Request $request, KelompokKpi $kelompok)
    {
        $data = $request->validate(['pengajuan_id' => ['required', 'array', 'min:1'], 'pengajuan_id.*' => ['integer']]);
        $daftar = PeriodeController::querySiap($kelompok->periode_id)->with('mahasiswa', 'tempatKpi')->whereIn('id', $data['pengajuan_id'])->get();

        if ($daftar->isEmpty()) {
            return back()->with('gagal', 'Mahasiswa yang dipilih belum memenuhi syarat.');
        }

        DB::transaction(fn () => $daftar->each(fn ($p) => $this->masukkan($kelompok, $p)));

        return back()->with('sukses', $daftar->count().' mahasiswa ditambahkan ke '.$kelompok->nama_kelompok.'.');
    }

    public function hapusAnggota(AnggotaKelompok $anggota)
    {
        $anggota->update(['status' => 'keluar']);
        PengajuanKpi::where('mahasiswa_id', $anggota->mahasiswa_id)->where('status', PengajuanKpi::BERJALAN)->update(['status' => PengajuanKpi::DISETUJUI]);
        AktivitasSistem::catat("Dikeluarkan dari {$anggota->kelompok->nama_kelompok}", 'kelompok', $anggota->mahasiswa_id);

        return back()->with('sukses', 'Mahasiswa dikeluarkan dari kelompok. Logbook dan dokumennya tetap tersimpan.');
    }

    private function masukkan(KelompokKpi $kelompok, PengajuanKpi $pengajuan): void
    {
        AnggotaKelompok::create(['kelompok_id' => $kelompok->id, 'mahasiswa_id' => $pengajuan->mahasiswa_id, 'tanggal_masuk' => today(), 'status' => 'aktif']);
        $pengajuan->update(['status' => PengajuanKpi::BERJALAN]);

        AktivitasSistem::catat("Ditempatkan di {$kelompok->nama_kelompok}", 'kelompok', $pengajuan->mahasiswa_id);
        Notifikasi::kirim($pengajuan->mahasiswa, 'Informasi pelaksanaan KPI tersedia',
            "Anda ditempatkan di {$kelompok->nama_kelompok} untuk KPI di {$pengajuan->nama_instansi}. Mulai sekarang Anda dapat mengisi logbook harian.", 'kelompok');
    }

    private function validasi(Request $request, ?KelompokKpi $kelompok = null): array
    {
        return $request->validate([
            'kode_kelompok' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('kelompok_kpi', 'kode_kelompok')->ignore($kelompok?->id)],
            'nama_kelompok' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
