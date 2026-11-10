<?php

namespace Database\Seeders;

use App\Models\Dokumen;
use App\Models\Logbook;
use App\Models\PengajuanKpi;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Data simulasi PrakTI (sesuai batasan proyek: bukan data asli institusi).
 * Semua akun memakai kata sandi: password
 */
class DatabaseSeeder extends Seeder
{
    private array $kegiatan = [
        'Mengikuti pengenalan lingkungan kerja, struktur organisasi, dan aturan keamanan data di instansi bersama pembimbing lapangan.',
        'Mempelajari alur layanan yang sedang berjalan dan mencatat kebutuhan pengguna dari hasil wawancara dengan staf bagian layanan.',
        'Menyusun rancangan basis data untuk modul pendataan dan mendiskusikan relasi antar tabel dengan pembimbing lapangan.',
        'Membuat rancangan antarmuka halaman input data menggunakan Figma, lalu merevisi tata letak berdasarkan masukan tim.',
        'Mengimplementasikan halaman login dan pengaturan hak akses pengguna menggunakan Laravel.',
        'Membuat fitur CRUD data layanan, termasuk validasi input dan pesan kesalahan yang mudah dipahami petugas.',
        'Melakukan pengujian fitur yang sudah dibuat bersama staf dan mencatat bug yang ditemukan untuk diperbaiki.',
        'Memperbaiki bug pada form pencarian data dan menambahkan fitur ekspor rekap ke format Excel.',
        'Membantu tim jaringan melakukan pengecekan perangkat dan dokumentasi topologi jaringan di ruang server.',
        'Menulis dokumentasi penggunaan sistem untuk petugas dan menyiapkan bahan presentasi progres mingguan.',
    ];

    public function run(): void
    {
        User::create([
            'name' => 'Admin Prodi Informatika',
            'email' => 'admin@prakti.test',
            'password' => 'password',
            'role' => User::ADMIN,
            'no_hp' => '081200000001',
            'is_active' => true,
        ]);

        $dosen = collect([
            ['name' => 'Dr. Hasanuddin Rahim, M.Kom.', 'nomor_induk' => '0012058801', 'email' => 'hasanuddin@prakti.test'],
            ['name' => 'Siti Aminah Latief, S.Kom., M.T.', 'nomor_induk' => '0021079002', 'email' => 'aminah@prakti.test'],
            ['name' => 'Andi Baso Tenri, S.T., M.Kom.', 'nomor_induk' => '0003119103', 'email' => 'andibaso@prakti.test', 'is_active' => false],
        ])->map(fn ($d) => User::create($d + ['password' => 'password', 'role' => User::PEMBIMBING, 'is_active' => true]));

        $mhs = collect([
            ['name' => 'Nurul Fadhilah', 'nomor_induk' => 'D0223101', 'email' => 'nurul@prakti.test'],
            ['name' => 'Muh. Rizky Pratama', 'nomor_induk' => 'D0223102', 'email' => 'rizky@prakti.test'],
            ['name' => 'Andi Tenri Ajeng', 'nomor_induk' => 'D0223103', 'email' => 'tenri@prakti.test'],
            ['name' => 'Fitriani Syam', 'nomor_induk' => 'D0223104', 'email' => 'fitriani@prakti.test'],
            ['name' => 'Ahmad Fauzan', 'nomor_induk' => 'D0223105', 'email' => 'fauzan@prakti.test'],
            ['name' => 'Dewi Kartika Sari', 'nomor_induk' => 'D0223106', 'email' => 'dewi@prakti.test'],
        ])->map(fn ($d, $i) => User::create($d + [
            'password' => 'password',
            'role' => User::MAHASISWA,
            'no_hp' => '08524000010'.$i,
            'is_active' => true,
        ]));

        $admin = User::where('role', User::ADMIN)->first();

        // 1. Nurul: KPI berjalan sejak 3 minggu lalu, logbook lengkap + progress
        $mulai = today()->subWeeks(3)->startOfWeek();
        $nurul = $this->pengajuan($mhs[0], $dosen[0], $admin, PengajuanKpi::BERJALAN, [
            'nama_instansi' => 'Balai Layanan Digital Kabupaten Majene',
            'alamat_instansi' => 'Jl. Jenderal Sudirman No. 12, Majene, Sulawesi Barat',
            'bidang_kerja' => 'Pengembangan aplikasi web layanan publik',
            'pembimbing_lapangan' => 'Ir. Muhammad Arif',
            'kontak_instansi' => '0422-21000',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $mulai->copy()->addMonths(3),
        ]);
        $this->logbook($nurul, $mulai, today()->subDay(), 2);
        $revisi = $nurul->logbooks()->where('status', Logbook::DISETUJUI)->orderByDesc('tanggal')->skip(1)->first();
        if ($revisi) {
            $revisi->update(['status' => Logbook::REVISI]);
            $revisi->komentar()->create([
                'user_id' => $dosen[0]->id,
                'isi' => 'Tolong jelaskan modul apa yang kamu kerjakan dan hasilnya apa. Tambahkan juga kendala yang ditemui hari itu.',
            ]);
        }
        $this->progress($nurul, $dosen[0], [
            ['Orientasi dan analisis kebutuhan', 'Menyelesaikan orientasi, wawancara dengan staf layanan, dan dokumen kebutuhan sistem.', 15, Progress::DIVERIFIKASI],
            ['Perancangan basis data dan antarmuka', 'ERD dan rancangan antarmuka halaman utama selesai dan sudah disetujui pembimbing lapangan.', 35, Progress::DIVERIFIKASI],
            ['Implementasi modul pendataan', 'Login, hak akses, dan CRUD data layanan sudah berjalan. Sedang mengerjakan fitur ekspor rekap.', 50, Progress::MENUNGGU],
        ]);

        // 2. Rizky: berjalan sejak 2 minggu lalu, logbook terakhir 3 hari lalu (muncul peringatan di monitoring)
        $mulai = today()->subWeeks(2)->startOfWeek();
        $rizky = $this->pengajuan($mhs[1], $dosen[1], $admin, PengajuanKpi::BERJALAN, [
            'nama_instansi' => 'PT Mandar Teknologi Nusantara',
            'alamat_instansi' => 'Jl. Poros Majene–Mamuju Km 5, Mamuju, Sulawesi Barat',
            'bidang_kerja' => 'Administrasi jaringan dan infrastruktur server',
            'pembimbing_lapangan' => 'Sahrul Ramadhan, S.Kom.',
            'kontak_instansi' => '0812-4455-6677',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $mulai->copy()->addMonths(2),
        ]);
        $this->logbook($rizky, $mulai, today()->subDays(4), 3);
        $this->progress($rizky, $dosen[1], [
            ['Pengenalan infrastruktur', 'Mempelajari topologi jaringan kantor dan prosedur pemeliharaan server.', 20, Progress::MENUNGGU],
        ]);

        // 3. Tenri: pengajuan menunggu verifikasi admin
        $this->pengajuan($mhs[2], null, null, PengajuanKpi::DIAJUKAN, [
            'nama_instansi' => 'CV Lontara Data Kreatif',
            'alamat_instansi' => 'Jl. Ahmad Yani No. 45, Polewali, Sulawesi Barat',
            'bidang_kerja' => 'Analisis data dan visualisasi dashboard',
            'pembimbing_lapangan' => 'Nurhaliza Putri, S.Si.',
            'tanggal_mulai' => today()->addWeeks(2)->startOfWeek(),
            'tanggal_selesai' => today()->addWeeks(2)->startOfWeek()->addMonths(2),
            'deskripsi' => 'Membantu tim data menyusun dashboard penjualan dan laporan bulanan.',
        ]);

        // 4. Fitriani: pengajuan ditolak dengan catatan
        $this->pengajuan($mhs[3], null, $admin, PengajuanKpi::DITOLAK, [
            'nama_instansi' => 'Toko Komputer Sinar Jaya',
            'alamat_instansi' => 'Jl. Gatot Subroto No. 3, Majene',
            'bidang_kerja' => 'Servis perangkat keras',
            'tanggal_mulai' => today()->addWeek(),
            'tanggal_selesai' => today()->addWeek()->addWeeks(4),
            'catatan_admin' => 'Durasi KPI minimal 8 minggu dan bidang kerja perlu terkait pengembangan atau pengelolaan sistem informasi. Silakan ajukan ulang dengan surat permohonan yang sudah ditandatangani koordinator.',
        ]);

        // 5. Fauzan: KPI sudah selesai
        $mulai = today()->subMonths(5)->startOfWeek();
        $fauzan = $this->pengajuan($mhs[4], $dosen[0], $admin, PengajuanKpi::SELESAI, [
            'nama_instansi' => 'Dinas Pendidikan Kabupaten Majene (simulasi)',
            'alamat_instansi' => 'Jl. Pahlawan No. 8, Majene',
            'bidang_kerja' => 'Pengelolaan sistem informasi data sekolah',
            'pembimbing_lapangan' => 'Rahmawati, S.Pd., M.Si.',
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $mulai->copy()->addWeeks(8),
        ]);
        $this->logbook($fauzan, $mulai, $mulai->copy()->addWeeks(8), 0);
        $this->progress($fauzan, $dosen[0], [
            ['Analisis sistem', 'Analisis kebutuhan sistem data sekolah selesai.', 30, Progress::DIVERIFIKASI],
            ['Implementasi', 'Modul rekap data siswa dan guru selesai diimplementasikan.', 75, Progress::DIVERIFIKASI],
            ['Penyelesaian dan laporan akhir', 'Sistem diserahkan ke instansi dan laporan akhir sudah diunggah.', 100, Progress::DIVERIFIKASI],
        ]);
        $this->dokumen($fauzan, 'laporan_akhir');

        // 6. Dewi: belum mengajukan KPI (untuk mencoba alur pengajuan dari awal)
    }

    private function pengajuan(User $mahasiswa, ?User $pembimbing, ?User $admin, string $status, array $data): PengajuanKpi
    {
        $pengajuan = PengajuanKpi::create($data + [
            'mahasiswa_id' => $mahasiswa->id,
            'pembimbing_id' => $pembimbing?->id,
            'status' => $status,
            'diverifikasi_oleh' => $admin?->id,
            'diverifikasi_pada' => $admin ? now()->subWeeks(3) : null,
        ]);

        $this->dokumen($pengajuan, 'surat_permohonan');
        $this->dokumen($pengajuan, 'proposal');

        return $pengajuan;
    }

    /** Isi logbook di hari kerja. $menunggu = jumlah logbook terakhir yang belum diperiksa. */
    private function logbook(PengajuanKpi $pengajuan, Carbon $dari, Carbon $sampai, int $menunggu): void
    {
        $hariKerja = [];
        for ($tgl = $dari->copy(); $tgl->lte($sampai); $tgl->addDay()) {
            if (! $tgl->isWeekend()) {
                $hariKerja[] = $tgl->copy();
            }
        }

        $total = count($hariKerja);
        foreach ($hariKerja as $i => $hari) {
            $pengajuan->logbooks()->create([
                'tanggal' => $hari->toDateString(),
                'jam_mulai' => '08:00',
                'jam_selesai' => $i % 3 === 0 ? '16:30' : '16:00',
                'kegiatan' => $this->kegiatan[$i % count($this->kegiatan)],
                'status' => $i >= $total - $menunggu ? Logbook::MENUNGGU : Logbook::DISETUJUI,
            ]);
        }
    }

    private function progress(PengajuanKpi $pengajuan, User $pembimbing, array $daftar): void
    {
        foreach ($daftar as $i => [$judul, $deskripsi, $persen, $status]) {
            $progress = $pengajuan->progress()->create(compact('judul', 'deskripsi', 'status') + ['persentase' => $persen]);

            if ($i === 1 && $status === Progress::DIVERIFIKASI) {
                $progress->komentar()->create([
                    'user_id' => $pembimbing->id,
                    'isi' => 'Bagus, rancangannya sudah rapi. Lanjutkan ke implementasi dan jangan lupa dokumentasikan perubahan rancangan.',
                ]);
            }
        }
    }

    private function dokumen(PengajuanKpi $pengajuan, string $jenis): void
    {
        $pengajuan->loadMissing('mahasiswa');
        $isi = $this->pdfSimulasi(Dokumen::JENIS[$jenis].' - '.$pengajuan->mahasiswa->name);
        $path = "dokumen/{$pengajuan->id}/{$jenis}-simulasi.pdf";

        Storage::disk('local')->put($path, $isi);

        $pengajuan->dokumen()->create([
            'jenis' => $jenis,
            'nama_asli' => $jenis.'_'.$pengajuan->mahasiswa->nomor_induk.'.pdf',
            'path' => $path,
            'ukuran' => strlen($isi),
            'diunggah_oleh' => $pengajuan->mahasiswa_id,
        ]);
    }

    /** Membuat berkas PDF satu halaman sederhana sebagai dokumen simulasi. */
    private function pdfSimulasi(string $judul): string
    {
        $teks = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $judul);
        $stream = "BT /F1 18 Tf 72 760 Td ({$teks}) Tj ET\n"
            ."BT /F1 11 Tf 72 736 Td (Dokumen simulasi untuk pengujian sistem PrakTI.) Tj ET";

        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offset = [];
        foreach ($objek as $i => $isi) {
            $offset[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$isi}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objek) + 1)."\n0000000000 65535 f \n";
        foreach ($offset as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf."trailer\n<< /Size ".(count($objek) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
