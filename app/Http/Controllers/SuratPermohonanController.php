<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSistem;
use App\Models\PengajuanKpi;
use App\Models\PengaturanSurat;
use App\Models\SuratPermohonanKpi;
use App\Support\SuratPermohonanGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SuratPermohonanController extends Controller
{
    public function simpanMahasiswa(Request $request, PengajuanKpi $pengajuan, SuratPermohonanGenerator $generator)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa?->id, 403);
        abort_unless(in_array($pengajuan->status, [PengajuanKpi::DISETUJUI, PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI], true), 403);

        $data = $request->validate($this->aturanAnggota());
        $pengajuan->loadMissing('mahasiswa', 'periode', 'tempatKpi');
        $anggota = array_values($data['anggota']);
        $anggota[0] = ['nama' => $pengajuan->mahasiswa->nama_lengkap, 'nim' => $pengajuan->mahasiswa->nim];
        $surat = DB::transaction(function () use ($pengajuan, $data, $anggota, $request) {
            $setelan = PengaturanSurat::query()->lockForUpdate()->findOrFail(1);
            $surat = SuratPermohonanKpi::query()->where('pengajuan_id', $pengajuan->id)->lockForUpdate()->first();

            $isianMahasiswa = [
                'penerima' => $data['penerima'],
                'nama_instansi' => $data['nama_instansi'],
                'kota_instansi' => $data['kota_instansi'],
                'anggota' => $anggota,
            ];

            if ($surat) {
                $surat->update($isianMahasiswa);

                return $surat->fresh('pengajuan.mahasiswa');
            }

            $tahun = now()->year;
            if ($setelan->tahun_nomor !== $tahun) {
                $setelan->update(['tahun_nomor' => $tahun, 'nomor_terakhir' => 0]);
            }
            $nomor = $setelan->nomor_terakhir + 1;
            $setelan->update(['nomor_terakhir' => $nomor]);

            return SuratPermohonanKpi::create($isianMahasiswa + [
                'pengajuan_id' => $pengajuan->id,
                'nomor_urut' => $nomor,
                'kode_nomor' => $setelan->kode_nomor,
                'tahun_nomor' => $tahun,
                'nomor_surat' => "{$nomor}/{$setelan->kode_nomor}/{$tahun}",
                'tanggal_mulai' => $pengajuan->periode->tanggal_mulai,
                'tanggal_selesai' => $pengajuan->periode->tanggal_selesai,
                'tanggal_surat' => today(),
                'kota_surat' => $setelan->kota_surat,
                'nama_penandatangan' => $setelan->nama_penandatangan,
                'nip_penandatangan' => $setelan->nip_penandatangan,
                'pangkat_golongan' => $setelan->pangkat_golongan,
                'jabatan_penandatangan' => $setelan->jabatan_penandatangan,
                'dibuat_oleh' => $request->user()->id,
            ])->load('pengajuan.mahasiswa');
        });

        AktivitasSistem::catat('Menyiapkan surat permohonan KPI', 'surat', $pengajuan->mahasiswa_id, $surat->nomor_surat);

        if ($request->input('aksi') === 'pratinjau') {
            return view('mahasiswa.pengajuan.surat-preview', compact('pengajuan', 'surat'));
        }

        return $generator->unduh($surat);
    }

    public function lihatMahasiswa(Request $request, PengajuanKpi $pengajuan)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa?->id, 403);
        abort_unless(in_array($pengajuan->status, [PengajuanKpi::DISETUJUI, PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI], true), 403);

        $surat = $pengajuan->suratPermohonan()->firstOrFail();

        return view('mahasiswa.pengajuan.surat-preview', compact('pengajuan', 'surat'));
    }

    public function unduhMahasiswa(Request $request, PengajuanKpi $pengajuan, SuratPermohonanGenerator $generator)
    {
        abort_unless($pengajuan->mahasiswa_id === $request->user()->mahasiswa?->id, 403);
        abort_unless(in_array($pengajuan->status, [PengajuanKpi::DISETUJUI, PengajuanKpi::BERJALAN, PengajuanKpi::SELESAI], true), 403);

        return $generator->unduh($pengajuan->suratPermohonan()->with('pengajuan.mahasiswa')->firstOrFail());
    }

    public function editAdmin(PengajuanKpi $pengajuan)
    {
        $surat = $pengajuan->suratPermohonan()->with('pengajuan.mahasiswa')->firstOrFail();

        return view('admin.pengajuan.surat', compact('pengajuan', 'surat'));
    }

    public function unduhAdmin(PengajuanKpi $pengajuan, SuratPermohonanGenerator $generator)
    {
        return $generator->unduh($pengajuan->suratPermohonan()->with('pengajuan.mahasiswa')->firstOrFail());
    }

    public function lihatAdmin(PengajuanKpi $pengajuan)
    {
        $surat = $pengajuan->suratPermohonan()->firstOrFail();

        return view('mahasiswa.pengajuan.surat-preview', compact('pengajuan', 'surat'));
    }

    public function updateAdmin(Request $request, PengajuanKpi $pengajuan)
    {
        $surat = $pengajuan->suratPermohonan()->firstOrFail();
        $data = $request->validate($this->aturanAdmin());
        $nomorSurat = "{$data['nomor_urut']}/{$data['kode_nomor']}/{$data['tahun_nomor']}";
        validator(['nomor_surat' => $nomorSurat], [
            'nomor_surat' => [Rule::unique('surat_permohonan_kpi', 'nomor_surat')->ignore($surat->id)],
        ], ['nomor_surat.unique' => 'Nomor surat ini sudah digunakan.'])->validate();

        DB::transaction(function () use ($data, $surat, $nomorSurat) {
            $surat->update([
                ...$data,
                'nomor_surat' => $nomorSurat,
                'anggota' => array_values($data['anggota']),
            ]);

            $pengaturan = PengaturanSurat::query()->lockForUpdate()->findOrFail(1);
            if ((int) $data['tahun_nomor'] === $pengaturan->tahun_nomor && (int) $data['nomor_urut'] > $pengaturan->nomor_terakhir) {
                $pengaturan->update(['nomor_terakhir' => (int) $data['nomor_urut']]);
            }
        });
        AktivitasSistem::catat('Admin mengubah data surat permohonan KPI', 'surat', $pengajuan->mahasiswa_id, $surat->nomor_surat);

        return redirect()->route('admin.pengajuan.show', $pengajuan)->with('sukses', 'Data surat permohonan berhasil diperbarui.');
    }

    private function aturanAnggota(): array
    {
        return [
            'penerima' => ['required', 'string', 'max:150'],
            'nama_instansi' => ['required', 'string', 'max:150'],
            'kota_instansi' => ['required', 'string', 'max:100'],
            'anggota' => ['required', 'array', 'min:1', 'max:4'],
            'anggota.*.nama' => ['required', 'string', 'max:100'],
            'anggota.*.nim' => ['required', 'string', 'max:20', 'alpha_dash'],
        ];
    }

    private function aturanAdmin(): array
    {
        return $this->aturanAnggota() + [
            'nomor_urut' => ['required', 'integer', 'min:1', 'max:999999'],
            'kode_nomor' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9.\/-]+$/'],
            'tahun_nomor' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2200'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tanggal_surat' => ['required', 'date'],
            'kota_surat' => ['required', 'string', 'max:80'],
            'nama_penandatangan' => ['required', 'string', 'max:150'],
            'nip_penandatangan' => ['required', 'string', 'max:40'],
            'pangkat_golongan' => ['required', 'string', 'max:100'],
            'jabatan_penandatangan' => ['required', 'string', 'max:100'],
        ];
    }
}
