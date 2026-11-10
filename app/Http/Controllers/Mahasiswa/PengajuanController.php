<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\PengajuanKpi;
use App\Models\User;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Fitur 5: Pengajuan KPI dan unggah dokumen oleh mahasiswa. */
class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $riwayat = $request->user()->pengajuanKpi()->with(['pembimbing', 'dokumen'])->latest()->get();
        $utama = $riwayat->first();

        return view('mahasiswa.pengajuan.index', [
            'utama' => $utama,
            'riwayat' => $riwayat->slice(1),
            'bisaAjukan' => ! $request->user()->pengajuanAktif(),
        ]);
    }

    public function create(Request $request)
    {
        if ($request->user()->pengajuanAktif()) {
            return redirect()->route('mahasiswa.pengajuan.index')
                ->with('info', 'Anda masih memiliki pengajuan yang menunggu verifikasi atau sedang berjalan.');
        }

        // Jika pernah ditolak, isian sebelumnya dipakai sebagai nilai awal
        $sebelumnya = $request->user()->pengajuanKpi()->where('status', PengajuanKpi::DITOLAK)->latest()->first();

        return view('mahasiswa.pengajuan.create', compact('sebelumnya'));
    }

    public function store(Request $request)
    {
        if ($request->user()->pengajuanAktif()) {
            return redirect()->route('mahasiswa.pengajuan.index')
                ->with('info', 'Anda masih memiliki pengajuan yang menunggu verifikasi atau sedang berjalan.');
        }

        $data = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:150'],
            'alamat_instansi' => ['required', 'string', 'max:500'],
            'bidang_kerja' => ['required', 'string', 'max:150'],
            'pembimbing_lapangan' => ['nullable', 'string', 'max:100'],
            'kontak_instansi' => ['nullable', 'string', 'max:50'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'surat_permohonan' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'proposal' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $pengajuan = DB::transaction(function () use ($request, $data) {
            $pengajuan = $request->user()->pengajuanKpi()->create(
                Arr::except($data, ['surat_permohonan', 'proposal']) + ['status' => PengajuanKpi::DIAJUKAN]
            );

            foreach (['surat_permohonan', 'proposal'] as $jenis) {
                if ($request->hasFile($jenis)) {
                    $this->simpanDokumen($pengajuan, $request->file($jenis), $jenis, $request->user()->id);
                }
            }

            return $pengajuan;
        });

        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Pengajuan KPI baru', [
            "{$request->user()->name} ({$request->user()->nomor_induk}) mengajukan KPI di {$pengajuan->nama_instansi}.",
        ], route('admin.pengajuan.show', $pengajuan), 'Periksa pengajuan');

        return redirect()->route('mahasiswa.pengajuan.index')
            ->with('sukses', 'Pengajuan KPI terkirim. Anda akan menerima email setelah admin memverifikasinya.');
    }

    public function unggahDokumen(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->id, 403);

        if ($pengajuan->status === PengajuanKpi::DITOLAK) {
            return back()->with('gagal', 'Pengajuan yang ditolak tidak dapat menerima dokumen baru. Silakan buat pengajuan baru.');
        }

        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(Dokumen::JENIS))],
            'berkas' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $dokumen = $this->simpanDokumen($pengajuan, $request->file('berkas'), $data['jenis'], $request->user()->id);

        if ($pengajuan->pembimbing && in_array($data['jenis'], ['laporan_akhir', 'surat_balasan'], true)) {
            Notifikasi::kirim($pengajuan->pembimbing, $dokumen->jenis_label.' diunggah', [
                "{$request->user()->name} mengunggah {$dokumen->jenis_label}: {$dokumen->nama_asli}.",
            ], route('pembimbing.bimbingan.show', $pengajuan).'#tab-dokumen', 'Lihat dokumen');
        }

        return back()->with('sukses', $dokumen->jenis_label.' berhasil diunggah.');
    }

    private function simpanDokumen(PengajuanKpi $pengajuan, UploadedFile $berkas, string $jenis, int $userId): Dokumen
    {
        return $pengajuan->dokumen()->create([
            'jenis' => $jenis,
            'nama_asli' => $berkas->getClientOriginalName(),
            'path' => $berkas->store("dokumen/{$pengajuan->id}", 'local'),
            'ukuran' => $berkas->getSize(),
            'diunggah_oleh' => $userId,
        ]);
    }
}
