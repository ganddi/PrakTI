<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\DosenPembimbing;
use App\Models\LaporanKpi;
use App\Models\Logbook;
use App\Models\Mahasiswa;
use App\Models\PengajuanKpi;
use App\Models\PenempatanDosen;
use App\Models\TempatKpi;
use App\Models\User;
use Illuminate\Http\Request;

/** Dashboard sesuai peran, mengikuti mockup SDD Gambar 4.12–4.14. */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            User::ADMIN => $this->admin($request),
            User::PEMBIMBING => $this->pembimbing($user->dosen),
            default => $this->mahasiswa($user->mahasiswa),
        };
    }

    private function admin(Request $request)
    {
        $statusKpi = PengajuanKpi::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('dashboard.admin', [
            'kartu' => [
                'mahasiswa' => Mahasiswa::count(),
                'pembimbing' => User::where('role', User::PEMBIMBING)->where('status', User::AKTIF)->count(),
                'instansi' => TempatKpi::where('status', TempatKpi::AKTIF)->count(),
            ],
            'tindakan' => [
                'pengajuan' => $statusKpi[PengajuanKpi::DIAJUKAN] ?? 0,
                'surat' => DokumenKpi::where('jenis_dokumen', 'surat_balasan')->where('status_verifikasi', DokumenKpi::MENUNGGU)->count(),
                'penempatan' => PenempatanDosen::where('status', PenempatanDosen::DIAJUKAN)->count(),
                'nilai' => DokumenKpi::whereIn('jenis_dokumen', ['penilaian_penguji', 'penilaian_lapangan', 'nilai_akhir'])->where('status_verifikasi', DokumenKpi::MENUNGGU)->count(),
                'akun' => User::where('status', User::MENUNGGU)->count(),
            ],
            'statusKpi' => [
                'Menunggu verifikasi' => ($statusKpi['diajukan'] ?? 0) + ($statusKpi['revisi'] ?? 0),
                'Disetujui' => $statusKpi['disetujui'] ?? 0,
                'Sedang berjalan' => $statusKpi['berjalan'] ?? 0,
                'Selesai' => $statusKpi['selesai'] ?? 0,
            ],
            'pengajuanTerbaru' => PengajuanKpi::with(['mahasiswa', 'tempatKpi'])
                ->when($request->query('q'), fn ($q, $c) => $q->whereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nim', 'like', "%{$c}%")))
                ->latest('id')->take(6)->get(),
            'aktivitas' => AktivitasSistem::with('user.mahasiswa', 'user.dosen')->where('modul', '!=', 'auth')->latest('id')->take(6)->get(),
        ]);
    }

    private function pembimbing(DosenPembimbing $dosen)
    {
        $bimbingan = $dosen->pengajuanBimbingan()->with(['mahasiswa', 'tempatKpi', 'periode'])->get();
        $idMhs = $bimbingan->pluck('mahasiswa_id');

        // Grafik garis: jumlah logbook per minggu (6 minggu terakhir)
        $mingguan = collect(range(5, 0))->mapWithKeys(function ($i) use ($idMhs) {
            $awal = today()->startOfWeek()->subWeeks($i);

            return ['Minggu '.(6 - $i) => Logbook::whereIn('mahasiswa_id', $idMhs)->whereBetween('tanggal', [$awal, $awal->copy()->endOfWeek()])->count()];
        });

        // Grafik batang: mahasiswa bimbingan per tahun periode
        $perTahun = $bimbingan->groupBy(fn ($p) => $p->periode->tahun)->map->count()->sortKeys();

        return view('dashboard.pembimbing', [
            'kartu' => [
                'mahasiswa' => $bimbingan->count(),
                'aktif' => $bimbingan->where('status', PengajuanKpi::BERJALAN)->count(),
                'instansi' => $bimbingan->pluck('tempat_kpi_id')->unique()->count(),
            ],
            'tindakan' => [
                'penempatan' => $dosen->penempatan()->whereIn('status', [PenempatanDosen::DITAWARKAN, PenempatanDosen::PERLU_PERBAIKAN])->count(),
                'logbook' => Logbook::whereIn('mahasiswa_id', $idMhs)->where('status', Logbook::MENUNGGU)->count(),
                'laporan' => LaporanKpi::whereIn('mahasiswa_id', $idMhs)->where('status', LaporanKpi::MENUNGGU)->count(),
            ],
            'mingguan' => $mingguan,
            'perTahun' => $perTahun,
            'aktivitas' => AktivitasSistem::with('user')->whereIn('modul', AktivitasSistem::MODUL_KPI)
                ->whereIn('referensi_id', $idMhs)->latest('id')->take(6)->get(),
            'namaMahasiswa' => $bimbingan->pluck('mahasiswa.nama_lengkap', 'mahasiswa_id'),
        ]);
    }

    private function mahasiswa(Mahasiswa $mhs)
    {
        $pengajuan = $mhs->pengajuanTerakhir()
            ->with(['tempatKpi', 'periode', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'kelompok.penempatan.dosen'])
            ->withCount(['logbooks', 'laporan'])->first();

        $tim = collect();
        if ($pengajuan?->kelompok) {
            // Grafik "Progres dalam 1 tim": persentase tahapan tiap anggota kelompok
            $tim = PengajuanKpi::with(['mahasiswa', 'tempatKpi', 'dokumen', 'laporanTerakhir', 'nilaiAkhir', 'kelompok.penempatan.dosen'])
                ->whereHas('anggotaAktif', fn ($a) => $a->where('kelompok_id', $pengajuan->kelompok->id))
                ->get()->map(fn ($p) => ['nama' => strtok($p->mahasiswa->nama_lengkap, ' '), 'persen' => $p->persentase, 'saya' => $p->id === $pengajuan->id]);
        }

        $catatanDosen = collect();
        if ($pengajuan) {
            $catatanDosen = Logbook::where('mahasiswa_id', $mhs->id)->whereNotNull('catatan_dosen')->latest('updated_at')->take(3)->get()
                ->map(fn ($l) => ['judul' => 'Logbook '.$l->tanggal->translatedFormat('d M'), 'isi' => $l->catatan_dosen, 'url' => route('mahasiswa.logbook.index').'#logbook-'.$l->id, 'waktu' => $l->updated_at]);
            if ($pengajuan->laporanTerakhir?->komentar_dosen) {
                $catatanDosen->prepend(['judul' => 'Laporan versi '.$pengajuan->laporanTerakhir->versi, 'isi' => $pengajuan->laporanTerakhir->komentar_dosen, 'url' => route('mahasiswa.laporan.index'), 'waktu' => $pengajuan->laporanTerakhir->diperiksa_at]);
            }
        }

        return view('dashboard.mahasiswa', [
            'pengajuan' => $pengajuan,
            'tim' => $tim,
            'catatanDosen' => $catatanDosen,
            'sudahIsiHariIni' => $pengajuan && Logbook::where('mahasiswa_id', $mhs->id)->whereDate('tanggal', today())->exists(),
            'langkahBerikut' => $pengajuan ? $this->langkahBerikut($pengajuan) : null,
        ]);
    }

    private function langkahBerikut(PengajuanKpi $p): ?array
    {
        $surat = $p->dokumenTerakhir('surat_balasan');
        $laporan = $p->laporanTerakhir;

        return match (true) {
            $p->status === PengajuanKpi::REVISI => ['Perbaiki pengajuan sesuai catatan admin.', route('mahasiswa.pengajuan.edit', $p), 'Perbaiki pengajuan'],
            $p->status === PengajuanKpi::DIAJUKAN => ['Pengajuan sedang diverifikasi admin.', null, null],
            $p->status === PengajuanKpi::DISETUJUI && (! $surat || $surat->status_verifikasi === DokumenKpi::REVISI) => ['Unduh template surat permohonan, kirim ke instansi, lalu unggah surat balasannya.', route('mahasiswa.pengajuan.index').'#surat', 'Kelola surat KPI'],
            $p->status === PengajuanKpi::DISETUJUI && $surat->status_verifikasi === DokumenKpi::MENUNGGU => ['Surat balasan sedang diverifikasi admin.', null, null],
            $p->status === PengajuanKpi::DISETUJUI => ['Surat balasan terverifikasi. Tunggu admin menempatkan Anda di kelompok KPI.', null, null],
            $p->status === PengajuanKpi::BERJALAN && $laporan?->status === LaporanKpi::REVISI => ['Laporan perlu direvisi. Lihat bagian yang ditandai pembimbing.', route('mahasiswa.laporan.index'), 'Buka laporan'],
            $p->status === PengajuanKpi::BERJALAN && $laporan?->status === LaporanKpi::DISETUJUI && ! $p->nilaiAkhir => ['Laporan disetujui. Isi nilai dosen penguji dan pembimbing lapangan.', route('mahasiswa.penilaian.index'), 'Buka penilaian'],
            $p->status === PengajuanKpi::BERJALAN && $p->nilaiAkhir => ['Nilai akhir tersedia. Cetak dokumen nilai akhir, mintakan tanda tangan, lalu unggah kembali.', route('mahasiswa.nilai.index'), 'Rekapitulasi nilai'],
            $p->status === PengajuanKpi::BERJALAN => ['Isi logbook setiap hari kerja dan unggah laporan KPI saat sudah siap.', route('mahasiswa.logbook.index'), 'Buka logbook'],
            default => null,
        };
    }
}
