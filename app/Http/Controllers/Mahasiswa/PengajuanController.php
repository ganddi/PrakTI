<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DokumenKpi;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use App\Models\TempatKpi;
use App\Models\User;
use App\Support\Berkas;
use App\Support\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** FR-04 (pengajuan), FR-06 & FR-07 (surat permohonan dan surat balasan). */
class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = $request->user()->mahasiswa->pengajuanTerakhir()->with(['dokumen', 'tempatKpi', 'periode', 'kelompok'])->first();

        return view('mahasiswa.pengajuan.index', ['pengajuan' => $pengajuan, 'templateSurat' => Template::daftar('surat_permohonan')]);
    }

    public function create(Request $request)
    {
        if ($request->user()->mahasiswa->pengajuanKpi()->where('status', '!=', PengajuanKpi::SELESAI)->exists()) {
            return redirect()->route('mahasiswa.pengajuan.index')->with('info', 'Anda sudah memiliki pengajuan KPI yang sedang diproses.');
        }

        return view('mahasiswa.pengajuan.form', $this->dataForm(new PengajuanKpi()));
    }

    public function store(Request $request)
    {
        $mhs = $request->user()->mahasiswa;
        if ($mhs->pengajuanKpi()->where('status', '!=', PengajuanKpi::SELESAI)->exists()) {
            return redirect()->route('mahasiswa.pengajuan.index')->with('info', 'Anda sudah memiliki pengajuan KPI yang sedang diproses.');
        }

        $data = $request->validate($this->aturan(true));

        $pengajuan = DB::transaction(function () use ($request, $data, $mhs) {
            $p = PengajuanKpi::create([
                'mahasiswa_id' => $mhs->id, 'periode_id' => $data['periode_id'], 'tempat_kpi_id' => $this->tempatId($data),
                'tanggal_pengajuan' => today(), 'keperluan' => $data['keperluan'], 'status' => PengajuanKpi::DIAJUKAN,
            ]);
            $this->simpanBerkas($request, $p);

            return $p;
        });

        AktivitasSistem::catat('Pengajuan KPI dikirim', 'pengajuan', $mhs->id);
        $this->kabariAdmin($pengajuan, 'Pengajuan KPI baru');

        return redirect()->route('mahasiswa.pengajuan.index')->with('sukses', 'Pengajuan KPI terkirim dan menunggu verifikasi admin.');
    }

    public function edit(Request $request, PengajuanKpi $pengajuan)
    {
        $this->pastikanBisaDiperbaiki($request, $pengajuan);

        return view('mahasiswa.pengajuan.form', $this->dataForm($pengajuan->load('dokumen', 'tempatKpi')));
    }

    public function update(Request $request, PengajuanKpi $pengajuan)
    {
        $this->pastikanBisaDiperbaiki($request, $pengajuan);
        $data = $request->validate($this->aturan(false));

        DB::transaction(function () use ($request, $pengajuan, $data) {
            $pengajuan->update(['periode_id' => $data['periode_id'], 'tempat_kpi_id' => $this->tempatId($data), 'keperluan' => $data['keperluan'], 'status' => PengajuanKpi::DIAJUKAN]);
            $this->simpanBerkas($request, $pengajuan);
        });

        AktivitasSistem::catat('Pengajuan KPI diperbaiki dan dikirim ulang', 'pengajuan', $pengajuan->mahasiswa_id);
        $this->kabariAdmin($pengajuan, 'Pengajuan KPI dikirim ulang');

        return redirect()->route('mahasiswa.pengajuan.index')->with('sukses', 'Perbaikan pengajuan terkirim dan kembali menunggu verifikasi.');
    }

    public function suratBalasan(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa->id, 403);
        $surat = $pengajuan->dokumenTerakhir('surat_balasan');

        if ($pengajuan->status !== PengajuanKpi::DISETUJUI || in_array($surat?->status_verifikasi, [DokumenKpi::DIVERIFIKASI, DokumenKpi::MENUNGGU], true)) {
            return back()->with('gagal', 'Surat balasan tidak dapat diunggah saat ini.');
        }

        $request->validate(['surat_balasan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        Berkas::simpanDokumen($pengajuan, $request->file('surat_balasan'), 'surat_balasan');
        AktivitasSistem::catat($surat ? 'Surat balasan diunggah ulang' : 'Surat balasan diunggah', 'surat', $pengajuan->mahasiswa_id);

        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Surat balasan menunggu verifikasi',
            "{$request->user()->name} mengunggah surat balasan dari {$pengajuan->nama_instansi}.", 'surat', $pengajuan->id);

        return back()->with('sukses', 'Surat balasan berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function unggahDokumen(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa->id, 403);
        $request->validate(['berkas' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120']]);
        Berkas::simpanDokumen($pengajuan, $request->file('berkas'), 'lainnya');

        return back()->with('sukses', 'Dokumen berhasil diunggah.');
    }

    private function dataForm(PengajuanKpi $pengajuan): array
    {
        return [
            'pengajuan' => $pengajuan,
            'periode' => PeriodeKpi::where('status', PeriodeKpi::AKTIF)->orderBy('tanggal_mulai')->get(),
            'tempat' => TempatKpi::where('status', TempatKpi::AKTIF)->orderBy('nama_instansi')->get(),
        ];
    }

    /** Instansi dipilih dari daftar, atau diusulkan baru (tersimpan sebagai tempat KPI berstatus usulan). */
    private function tempatId(array $data): int
    {
        if (! empty($data['tempat_kpi_id'])) {
            return (int) $data['tempat_kpi_id'];
        }

        return TempatKpi::create([
            'nama_instansi' => $data['nama_instansi'], 'jenis_instansi' => $data['jenis_instansi'], 'alamat' => $data['alamat'],
            'kota' => $data['kota'], 'provinsi' => $data['provinsi'], 'nama_pic' => $data['nama_pic'] ?? null,
            'no_hp_pic' => $data['no_hp_pic'] ?? null, 'email_pic' => $data['email_pic'] ?? null, 'status' => TempatKpi::USULAN,
        ])->id;
    }

    private function pastikanBisaDiperbaiki(Request $request, PengajuanKpi $pengajuan): void
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa->id, 403);
        abort_unless($pengajuan->status === PengajuanKpi::REVISI, 403, 'Pengajuan ini tidak sedang dalam status perlu perbaikan.');
    }

    private function simpanBerkas(Request $request, PengajuanKpi $pengajuan): void
    {
        foreach (['persyaratan', 'proposal'] as $jenis) {
            if ($request->hasFile($jenis)) {
                Berkas::simpanDokumen($pengajuan, $request->file($jenis), $jenis);
            }
        }
    }

    private function kabariAdmin(PengajuanKpi $p, string $judul): void
    {
        $p->load('mahasiswa', 'tempatKpi');
        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), $judul, "{$p->mahasiswa->nama_lengkap} ({$p->mahasiswa->nim}) mengajukan KPI di {$p->nama_instansi}.", 'pengajuan', $p->id);
    }

    private function aturan(bool $baru): array
    {
        return [
            'periode_id' => ['required', Rule::exists('periode_kpi', 'id')->where('status', PeriodeKpi::AKTIF)],
            'tempat_kpi_id' => ['nullable', Rule::exists('tempat_kpi', 'id')->whereIn('status', [TempatKpi::AKTIF, TempatKpi::USULAN])],
            'nama_instansi' => ['required_without:tempat_kpi_id', 'nullable', 'string', 'max:100'],
            'jenis_instansi' => ['required_without:tempat_kpi_id', 'nullable', 'string', 'max:50'],
            'alamat' => ['required_without:tempat_kpi_id', 'nullable', 'string', 'max:500'],
            'kota' => ['required_without:tempat_kpi_id', 'nullable', 'string', 'max:50'],
            'provinsi' => ['required_without:tempat_kpi_id', 'nullable', 'string', 'max:50'],
            'nama_pic' => ['nullable', 'string', 'max:100'],
            'no_hp_pic' => ['nullable', 'string', 'max:15'],
            'email_pic' => ['nullable', 'email', 'max:100'],
            'keperluan' => ['required', 'string', 'min:20', 'max:2000'],
            'persyaratan' => [$baru ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:5120'],
            'proposal' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
