<?php

namespace Tests\Feature;

use App\Models\DokumenKpi;
use App\Models\Mahasiswa;
use App\Models\PengajuanKpi;
use App\Models\PeriodeKpi;
use App\Models\TempatKpi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiDokumenSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_documents_by_student_name(): void
    {
        $admin = $this->createAdmin();
        $this->createStudentDocument('Alya Pratama', '2026001');
        $this->createStudentDocument('Bima Saputra', '2026002');

        $response = $this->actingAs($admin)->get(route('admin.verifikasi.index', ['q' => 'Alya']));

        $response->assertOk()
            ->assertSeeText('Alya Pratama')
            ->assertDontSeeText('Bima Saputra');
    }

    public function test_admin_can_search_documents_by_student_number(): void
    {
        $admin = $this->createAdmin();
        $this->createStudentDocument('Alya Pratama', '2026001');
        $this->createStudentDocument('Bima Saputra', '2026002');

        $response = $this->actingAs($admin)->get(route('admin.verifikasi.index', [
            'tab' => 'surat',
            'status' => 'menunggu',
            'q' => '2026002',
        ]));

        $response->assertOk()
            ->assertSeeText('Bima Saputra')
            ->assertDontSeeText('Alya Pratama')
            ->assertViewHas('dokumen', fn ($dokumen) => $dokumen->total() === 1);
    }

    private function createAdmin(): User
    {
        return User::query()->create([
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => User::ADMIN,
            'status' => User::AKTIF,
        ]);
    }

    private function createStudentDocument(string $name, string $nim): DokumenKpi
    {
        $mahasiswa = Mahasiswa::query()->create([
            'user_id' => User::query()->create([
                'username' => strtolower($nim),
                'email' => "{$nim}@example.test",
                'password' => 'password',
                'role' => User::MAHASISWA,
                'status' => User::AKTIF,
            ])->id,
            'nim' => $nim,
            'nama_lengkap' => $name,
            'email' => "{$nim}@example.test",
            'program_studi' => 'Informatika',
            'fakultas' => 'Teknik',
        ]);

        $tempat = TempatKpi::query()->create([
            'nama_instansi' => 'Instansi '.$nim,
            'jenis_instansi' => 'Perusahaan',
            'alamat' => 'Alamat',
            'kota' => 'Majene',
            'provinsi' => 'Sulawesi Barat',
            'status' => 'aktif',
        ]);
        $periode = PeriodeKpi::query()->create([
            'kode_periode' => 'P'.$nim,
            'nama_periode' => 'Periode '.$nim,
            'tahun' => 2026,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);
        $pengajuan = PengajuanKpi::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'periode_id' => $periode->id,
            'tempat_kpi_id' => $tempat->id,
            'tanggal_pengajuan' => '2026-01-01',
            'keperluan' => 'Kerja praktik',
            'status' => PengajuanKpi::BERJALAN,
        ]);

        return DokumenKpi::query()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => 'surat_balasan',
            'nama_file' => 'surat.pdf',
            'format_file' => 'pdf',
            'ukuran_file' => 100,
            'path_file' => 'dokumen/surat.pdf',
            'status_verifikasi' => DokumenKpi::MENUNGGU,
            'uploaded_at' => now(),
        ]);
    }
}
