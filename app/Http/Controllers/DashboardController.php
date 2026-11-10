<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            User::ADMIN => $this->admin(),
            User::PEMBIMBING => $this->pembimbing($user),
            default => $this->mahasiswa($user),
        };
    }

    private function admin()
    {
        return view('dashboard.admin', [
            'jumlah' => [
                'mahasiswa' => User::where('role', User::MAHASISWA)->count(),
                'pembimbing' => User::where('role', User::PEMBIMBING)->where('is_active', true)->count(),
                'menunggu' => PengajuanKpi::where('status', PengajuanKpi::DIAJUKAN)->count(),
                'berjalan' => PengajuanKpi::where('status', PengajuanKpi::BERJALAN)->count(),
                'selesai' => PengajuanKpi::where('status', PengajuanKpi::SELESAI)->count(),
            ],
            'akunMenunggu' => User::where('role', User::PEMBIMBING)->where('is_active', false)->count(),
            'pengajuanMenunggu' => PengajuanKpi::with('mahasiswa')
                ->where('status', PengajuanKpi::DIAJUKAN)->oldest()->take(6)->get(),
            'logbookHariIni' => Logbook::whereDate('tanggal', today())->count(),
        ]);
    }

    private function pembimbing(User $user)
    {
        $bimbingan = $user->mahasiswaBimbingan()
            ->with(['mahasiswa', 'progressTerakhir'])
            ->withCount([
                'logbooks',
                'logbooks as logbook_menunggu_count' => fn ($q) => $q->where('status', Logbook::MENUNGGU),
            ])
            ->where('status', PengajuanKpi::BERJALAN)
            ->orderBy('tanggal_mulai')
            ->get();

        $idPengajuan = $bimbingan->pluck('id');

        return view('dashboard.pembimbing', [
            'bimbingan' => $bimbingan,
            'logbookMenunggu' => Logbook::with('pengajuan.mahasiswa')
                ->whereIn('pengajuan_kpi_id', $idPengajuan)
                ->where('status', Logbook::MENUNGGU)
                ->orderBy('tanggal')->take(8)->get(),
            'jumlahLogbookMenunggu' => Logbook::whereIn('pengajuan_kpi_id', $idPengajuan)
                ->where('status', Logbook::MENUNGGU)->count(),
            'jumlahProgressMenunggu' => Progress::whereIn('pengajuan_kpi_id', $idPengajuan)
                ->where('status', Progress::MENUNGGU)->count(),
        ]);
    }

    private function mahasiswa(User $user)
    {
        $pengajuan = $user->pengajuanKpi()
            ->latest('id')
            ->with(['pembimbing', 'progressTerakhir'])
            ->withCount([
                'logbooks',
                'logbooks as logbook_revisi_count' => fn ($q) => $q->where('status', Logbook::REVISI),
            ])
            ->first();

        $feedbackTerbaru = collect();
        $sudahIsiHariIni = false;

        if ($pengajuan) {
            $feedbackTerbaru = Komentar::with(['user', 'commentable'])
                ->where('user_id', '!=', $user->id)
                ->whereHasMorph('commentable', [Logbook::class, Progress::class],
                    fn ($q) => $q->where('pengajuan_kpi_id', $pengajuan->id))
                ->latest()->take(5)->get();

            $sudahIsiHariIni = $pengajuan->logbooks()->whereDate('tanggal', today())->exists();
        }

        return view('dashboard.mahasiswa', compact('pengajuan', 'feedbackTerbaru', 'sudahIsiHariIni'));
    }
}
