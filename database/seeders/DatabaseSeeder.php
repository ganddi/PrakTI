<?php

namespace Database\Seeders;

use App\Models\AktivitasSistem;
use App\Models\AnggotaKelompok;
use App\Models\DokumenKpi;
use App\Models\DosenPembimbing;
use App\Models\KelompokKpi;
use App\Models\LaporanKpi;
use App\Models\Logbook;
use App\Models\Mahasiswa;
use App\Models\NilaiAkhir;
use App\Models\Notifikasi;
use App\Models\PengajuanKpi;
use App\Models\PenempatanDosen;
use App\Models\Penilaian;
use App\Models\PeriodeKpi;
use App\Models\TempatKpi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/** Data simulasi PrakTI sesuai struktur tabel SDD. Kata sandi semua akun: password */
class DatabaseSeeder extends Seeder
{
    private User $admin;

    private array $kegiatan = [
        ['Orientasi lingkungan kerja', 'Mengikuti pengenalan struktur organisasi, aturan kerja, dan keamanan data bersama pembimbing lapangan.', 'Memahami alur kerja unit', null],
        ['Analisis kebutuhan pengguna', 'Mewawancarai staf bagian layanan dan mencatat kebutuhan sistem yang akan dikembangkan.', 'Daftar kebutuhan fungsional', 'Jadwal staf padat sehingga wawancara dibagi dua sesi'],
        ['Perancangan basis data', 'Menyusun ERD modul pendataan dan mendiskusikan relasi antartabel dengan pembimbing lapangan.', 'ERD versi pertama', null],
        ['Perancangan antarmuka', 'Membuat rancangan halaman input data di Figma lalu merevisi tata letak sesuai masukan tim.', 'Mockup 4 halaman', null],
        ['Implementasi login dan hak akses', 'Mengimplementasikan halaman login dan pembagian hak akses pengguna menggunakan Laravel.', 'Login berjalan untuk 3 peran', 'Konfigurasi session di server uji'],
        ['Implementasi CRUD data layanan', 'Membuat fitur tambah, ubah, hapus data layanan beserta validasi input.', 'Modul CRUD selesai', null],
        ['Pengujian bersama staf', 'Menguji fitur bersama staf dan mencatat bug yang ditemukan.', '7 bug tercatat', null],
        ['Perbaikan bug dan ekspor rekap', 'Memperbaiki bug pencarian data dan menambahkan ekspor rekap ke Excel.', 'Ekspor Excel berfungsi', null],
        ['Dokumentasi topologi jaringan', 'Membantu tim jaringan memeriksa perangkat dan mendokumentasikan topologi di ruang server.', 'Diagram topologi', 'Akses ruang server terbatas'],
        ['Penyusunan dokumentasi pengguna', 'Menulis panduan penggunaan sistem dan menyiapkan presentasi progres mingguan.', 'Draf panduan pengguna', null],
    ];

    public function run(): void
    {
        $this->admin = User::create(['username' => 'admin', 'email' => 'admin@prakti.test', 'password' => 'password', 'role' => User::ADMIN, 'status' => User::AKTIF]);

        // ---------- Dosen pembimbing ----------
        $dosen = fn ($nidn, $nama, $email, $jabatan, $status = User::AKTIF) => DosenPembimbing::create([
            'user_id' => User::create(['username' => $nidn, 'email' => $email, 'password' => 'password', 'role' => User::PEMBIMBING, 'status' => $status])->id,
            'nidn' => $nidn, 'nama_lengkap' => $nama, 'email' => $email, 'no_hp' => '0813'.substr($nidn, -7), 'program_studi' => 'Informatika', 'jabatan' => $jabatan,
        ]);
        $hasan = $dosen('0012058801', 'Dr. Hasanuddin Rahim, M.Kom.', 'hasanuddin@prakti.test', 'Lektor Kepala');
        $aminah = $dosen('0021079002', 'Siti Aminah Latief, S.Kom., M.T.', 'aminah@prakti.test', 'Lektor');
        $rahmat = $dosen('0015049204', 'Rahmat Hidayat, S.Kom., M.Cs.', 'rahmat@prakti.test', 'Asisten Ahli');
        $dosen('0003119103', 'Andi Baso Tenri, S.T., M.Kom.', 'andibaso@prakti.test', null, User::MENUNGGU);

        // ---------- Tempat KPI ----------
        $tempat = collect([
            ['Balai Layanan Digital Kabupaten Majene', 'Instansi pemerintah', 'Jl. Jenderal Sudirman No. 12', 'Majene', 'Ir. Muhammad Arif', 'Mengembangkan dan memelihara aplikasi layanan publik daerah.'],
            ['PT Mandar Teknologi Nusantara', 'Perusahaan swasta', 'Jl. Poros Majene-Mamuju Km 5', 'Mamuju', 'Sahrul Ramadhan, S.Kom.', 'Penyedia layanan internet dan pusat data di Sulawesi Barat.'],
            ['CV Lontara Data Kreatif', 'Startup', 'Jl. Ahmad Yani No. 45', 'Polewali Mandar', 'Nurhaliza Putri, S.Si.', 'Konsultan data untuk UMKM dan instansi pemerintah.'],
            ['Dinas Pendidikan Kabupaten Majene (simulasi)', 'Instansi pemerintah', 'Jl. Pahlawan No. 8', 'Majene', 'Rahmawati, S.Pd., M.Si.', null],
            ['Diskominfo Kabupaten Mamuju (simulasi)', 'Instansi pemerintah', 'Jl. Soekarno Hatta No. 3', 'Mamuju', 'Andi Firman, S.T.', 'Pengelolaan jaringan dan layanan TIK pemerintah daerah.'],
        ])->map(fn ($t, $i) => TempatKpi::create([
            'nama_instansi' => $t[0], 'jenis_instansi' => $t[1], 'alamat' => $t[2], 'kota' => $t[3], 'provinsi' => 'Sulawesi Barat',
            'nama_pic' => $t[4], 'email_pic' => 'pic'.($i + 1).'@instansi.test', 'no_hp_pic' => '08124455'.str_pad($i, 4, '0'), 'deskripsi' => $t[5], 'status' => TempatKpi::AKTIF,
        ]))->all();

        // ---------- Template (berkas di storage, tanpa tabel) ----------
        foreach (['surat_permohonan' => 'Template_Surat_Permohonan_KPI.pdf', 'logbook' => 'Format_Logbook_KPI.pdf', 'laporan' => 'Template_Laporan_KPI.pdf', 'penilaian' => 'Form_Penilaian_Penguji_dan_Lapangan.pdf'] as $jenis => $nama) {
            Storage::disk('local')->put("template/{$jenis}/{$nama}", $this->pdf([str_replace('_', ' ', pathinfo($nama, PATHINFO_FILENAME)), 'Template simulasi. Ganti dengan template resmi program studi.']));
        }

        // ---------- Periode dan kelompok ----------
        $mulai = today()->subWeeks(3)->startOfWeek();
        $p1 = PeriodeKpi::create(['kode_periode' => 'KPI-2026-1', 'nama_periode' => 'KPI Semester Ganjil 2026/2027', 'tahun' => $mulai->year, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $mulai->copy()->addMonths(3), 'status' => PeriodeKpi::AKTIF]);
        $mulaiLama = today()->subMonths(6)->startOfWeek();
        $p0 = PeriodeKpi::create(['kode_periode' => 'KPI-2026-0', 'nama_periode' => 'KPI Semester Genap 2025/2026', 'tahun' => $mulaiLama->year, 'tanggal_mulai' => $mulaiLama, 'tanggal_selesai' => $mulaiLama->copy()->addWeeks(10), 'status' => PeriodeKpi::SELESAI]);

        $kel = fn ($periode, $n, $ket) => KelompokKpi::create(['periode_id' => $periode->id, 'kode_kelompok' => $periode->kode_periode.'-K0'.$n, 'nama_kelompok' => 'Kelompok '.$n, 'keterangan' => $ket, 'status' => 'aktif']);
        $k1 = $kel($p1, 1, 'Balai Layanan Digital Majene');
        $k2 = $kel($p1, 2, 'PT Mandar Teknologi Nusantara');
        $k3 = $kel($p1, 3, 'CV Lontara Data Kreatif');
        $kLama = $kel($p0, 1, 'Dinas Pendidikan Majene');

        $tempatkan = fn ($k, $t, $d, $status, $tgl) => PenempatanDosen::create(['kelompok_id' => $k->id, 'tempat_kpi_id' => $t->id, 'dosen_id' => $d->id, 'tanggal_pengajuan' => $tgl, 'status' => $status, 'verified_at' => $status === PenempatanDosen::DISETUJUI ? $tgl->copy()->addDay() : null]);
        $tempatkan($k1, $tempat[0], $hasan, PenempatanDosen::DISETUJUI, $mulai->copy()->subDays(5));
        $tempatkan($k2, $tempat[1], $aminah, PenempatanDosen::DISETUJUI, $mulai->copy()->subDays(5));
        $tempatkan($k3, $tempat[2], $rahmat, PenempatanDosen::DITAWARKAN, today()->subDays(2));
        $tempatkan($k3, $tempat[2], $aminah, PenempatanDosen::DIAJUKAN, today()->subDay());
        $tempatkan($kLama, $tempat[3], $hasan, PenempatanDosen::DISETUJUI, $mulaiLama->copy()->subDays(5));

        // ---------- Mahasiswa ----------
        $mhs = collect([
            'nurul' => ['D0223101', 'Nurul Fadhilah'], 'putri' => ['D0223107', 'Putri Ayu Lestari'], 'rizky' => ['D0223102', 'Muh. Rizky Pratama'],
            'wulan' => ['D0223108', 'Wulan Sari'], 'siti' => ['D0223109', 'Siti Rahma'], 'arif' => ['D0223110', 'Arif Budiman'],
            'tenri' => ['D0223103', 'Andi Tenri Ajeng'], 'fitriani' => ['D0223104', 'Fitriani Syam'], 'fauzan' => ['D0223105', 'Ahmad Fauzan'], 'dewi' => ['D0223106', 'Dewi Kartika Sari'],
        ])->map(fn ($d, $kunci) => Mahasiswa::create([
            'user_id' => User::create(['username' => $d[0], 'email' => "{$kunci}@prakti.test", 'password' => 'password', 'role' => User::MAHASISWA, 'status' => User::AKTIF])->id,
            'nim' => $d[0], 'nama_lengkap' => $d[1], 'email' => "{$kunci}@prakti.test", 'no_hp' => '0852400'.substr($d[0], -4),
            'program_studi' => 'Informatika', 'fakultas' => 'Fakultas Teknik', 'alamat' => 'Majene, Sulawesi Barat',
        ]));

        $keperluan = 'Melaksanakan Kerja Praktik Industri untuk menerapkan ilmu rekayasa perangkat lunak pada pengembangan sistem informasi instansi.';

        // 1. Nurul: berjalan, laporan v1 direvisi dan v2 menunggu diperiksa
        $nurul = $this->berjalan($mhs['nurul'], $p1, $tempat[0], $k1, $keperluan);
        $this->logbook($nurul, $mulai, today()->subDay(), 2);
        Logbook::where('mahasiswa_id', $nurul->mahasiswa_id)->where('status', Logbook::DISETUJUI)->orderByDesc('tanggal')->skip(1)->first()
            ?->update(['status' => Logbook::REVISI, 'catatan_dosen' => 'Jelaskan modul yang dikerjakan dan hasilnya. Tambahkan kendala yang ditemui.']);
        $v1 = $this->laporan($nurul, 1, LaporanKpi::REVISI, now()->subDays(6), 'Secara umum sudah baik. Perbaiki bagian yang ditandai sebelum diunggah ulang.');
        $v1->update(['halaman_revisi' => [
            ['halaman' => 3, 'kutipan' => 'Sistem ini dibuat untuk mempermudah semua pekerjaan di kantor.', 'catatan' => 'Terlalu umum. Sebutkan proses layanan apa yang dibantu.', 'status' => 'terbuka', 'waktu' => now()->subDays(5)->toDateTimeString()],
            ['halaman' => 7, 'kutipan' => 'Gambar 3.1 Use case', 'catatan' => 'Use case belum memuat aktor petugas verifikasi.', 'status' => 'terbuka', 'waktu' => now()->subDays(5)->toDateTimeString()],
            ['halaman' => null, 'kutipan' => null, 'catatan' => 'Daftar pustaka gunakan format APA, minimal 5 referensi.', 'status' => 'terbuka', 'waktu' => now()->subDays(5)->toDateTimeString()],
        ]]);
        $this->laporan($nurul, 2, LaporanKpi::MENUNGGU, now()->subDay());
        $this->catat($nurul, 'Laporan versi 1 perlu direvisi', 'laporan', $hasan->user, now()->subDays(5), '3 bagian ditandai');
        $this->catat($nurul, 'Laporan KPI versi 2 diunggah', 'laporan', $mhs['nurul']->user, now()->subDay());

        // 2. Putri: laporan disetujui, nilai lengkap, dokumen penilaian menunggu verifikasi
        $putri = $this->berjalan($mhs['putri'], $p1, $tempat[0], $k1, $keperluan);
        $this->logbook($putri, $mulai, today()->subDay(), 1);
        $this->laporan($putri, 1, LaporanKpi::DISETUJUI, now()->subDays(4), 'Laporan rapi dan lengkap.');
        $this->nilai($putri, $hasan, ['sikap' => 85, 'logbook' => 80, 'laporan' => 82, 'penguasaan' => 88], 80, 86, Penilaian::DIINPUT);
        $this->dokumen($putri, 'penilaian_penguji', DokumenKpi::MENUNGGU);
        $this->dokumen($putri, 'penilaian_lapangan', DokumenKpi::MENUNGGU);
        $this->catat($putri, 'Nilai dosen penguji dan pembimbing lapangan diisi', 'penilaian', $mhs['putri']->user, now()->subDay());

        // 3. Rizky: logbook terakhir 4 hari lalu (perlu perhatian di monitoring)
        $mulaiR = today()->subWeeks(2)->startOfWeek();
        $rizky = $this->berjalan($mhs['rizky'], $p1, $tempat[1], $k2, $keperluan);
        $this->logbook($rizky, $mulaiR, today()->subDays(4), 3);

        // 4. Wulan: di kelompok 3, dosen pembimbing masih dalam proses penempatan
        $wulan = $this->berjalan($mhs['wulan'], $p1, $tempat[2], $k3, $keperluan);
        $this->logbook($wulan, $mulai->copy()->addWeek(), today()->subDay(), 5);

        // 5. Siti: disetujui, surat balasan menunggu verifikasi
        $siti = $this->pengajuan($mhs['siti'], $p1, $tempat[1], PengajuanKpi::DISETUJUI, $keperluan);
        $this->dokumen($siti, 'surat_balasan', DokumenKpi::MENUNGGU);
        $this->catat($siti, 'Surat balasan diunggah', 'surat', $mhs['siti']->user, now()->subHours(5));

        // 6. Arif: surat balasan terverifikasi, siap dikelompokkan (tempat sama dengan kelompok 3)
        $arif = $this->pengajuan($mhs['arif'], $p1, $tempat[2], PengajuanKpi::DISETUJUI, $keperluan);
        $this->dokumen($arif, 'surat_balasan', DokumenKpi::DIVERIFIKASI);

        // 7. Tenri: mengusulkan instansi baru, menunggu verifikasi
        $usulan = TempatKpi::create(['nama_instansi' => 'Studio Kreatif Pattae', 'jenis_instansi' => 'Startup', 'alamat' => 'Jl. Gatot Subroto No. 21', 'kota' => 'Majene', 'provinsi' => 'Sulawesi Barat', 'nama_pic' => 'Rizal Mahendra', 'no_hp_pic' => '085311223344', 'status' => TempatKpi::USULAN]);
        $this->pengajuan($mhs['tenri'], $p1, $usulan, PengajuanKpi::DIAJUKAN, 'Membantu tim mengembangkan aplikasi mobile pemesanan untuk UMKM di Majene.');

        // 8. Fitriani: diminta memperbaiki pengajuan
        $fit = $this->pengajuan($mhs['fitriani'], $p1, $tempat[4], PengajuanKpi::REVISI, 'Ingin belajar jaringan.');
        $fit->update(['catatan_admin' => 'Uraikan keperluan KPI lebih jelas (minimal rencana kegiatan dan target) dan lampirkan KHS terbaru.']);

        // 9. Fauzan: seluruh proses selesai di periode sebelumnya
        $fauzan = $this->berjalan($mhs['fauzan'], $p0, $tempat[3], $kLama, $keperluan);
        $this->logbook($fauzan, $mulaiLama, $mulaiLama->copy()->addWeeks(8), 0);
        $this->laporan($fauzan, 1, LaporanKpi::DISETUJUI, $mulaiLama->copy()->addWeeks(9), 'Laporan memenuhi seluruh ketentuan.');
        $this->nilai($fauzan, $hasan, ['sikap' => 88, 'logbook' => 85, 'laporan' => 84, 'penguasaan' => 86], 84, 90, Penilaian::DIVERIFIKASI);
        $this->dokumen($fauzan, 'logbook', DokumenKpi::TERSIMPAN);
        foreach (['penilaian_penguji', 'penilaian_lapangan', 'nilai_akhir'] as $j) {
            $this->dokumen($fauzan, $j, DokumenKpi::DIVERIFIKASI);
        }
        NilaiAkhir::where('mahasiswa_id', $fauzan->mahasiswa_id)->update(['status' => NilaiAkhir::DIVERIFIKASI]);
        $fauzan->update(['status' => PengajuanKpi::SELESAI]);
        $this->catat($fauzan, 'Proses KPI selesai', 'nilai_akhir', $this->admin, $mulaiLama->copy()->addWeeks(11));

        // 10. Dewi: belum mengajukan KPI

        // ---------- Contoh notifikasi ----------
        $laporanNurul = LaporanKpi::where('mahasiswa_id', $nurul->mahasiswa_id)->max('id');
        foreach ([
            [$hasan->user, 'Laporan revisi siap diperiksa', 'Nurul Fadhilah mengunggah laporan KPI versi 2.', 'laporan', $laporanNurul],
            [$aminah->user, 'Tempat KPI tersedia untuk dipilih', 'CV Lontara Data Kreatif (Kelompok 3) dibagikan kepada Anda.', 'penempatan', null],
            [$mhs['nurul']->user, 'Laporan KPI perlu direvisi', 'Dr. Hasanuddin Rahim menandai 3 bagian laporan versi 1 yang perlu diperbaiki.', 'laporan', null],
            [$this->admin, 'Akun dosen pembimbing menunggu aktivasi', 'Andi Baso Tenri (0003119103) mendaftar sebagai dosen pembimbing.', 'akun', null],
        ] as [$u, $judul, $pesan, $jenis, $ref]) {
            Notifikasi::create(['user_id' => $u->id, 'judul' => $judul, 'pesan' => $pesan, 'jenis' => $jenis, 'referensi_id' => $ref, 'is_read' => false]);
        }
    }

    private function pengajuan(Mahasiswa $m, PeriodeKpi $periode, TempatKpi $t, string $status, string $keperluan): PengajuanKpi
    {
        $p = PengajuanKpi::create([
            'mahasiswa_id' => $m->id, 'periode_id' => $periode->id, 'tempat_kpi_id' => $t->id, 'tanggal_pengajuan' => $periode->tanggal_mulai->copy()->subWeeks(5),
            'keperluan' => $keperluan, 'status' => $status, 'verified_at' => $status === PengajuanKpi::DIAJUKAN ? null : $periode->tanggal_mulai->copy()->subWeeks(4),
        ]);
        $this->dokumen($p, 'persyaratan', DokumenKpi::TERSIMPAN);
        $this->dokumen($p, 'proposal', DokumenKpi::TERSIMPAN);
        $this->catat($p, 'Pengajuan KPI dikirim', 'pengajuan', $m->user, $periode->tanggal_mulai->copy()->subWeeks(5));
        if ($status !== PengajuanKpi::DIAJUKAN) {
            $this->catat($p, $status === PengajuanKpi::REVISI ? 'Pengajuan KPI perlu diperbaiki' : 'Pengajuan KPI disetujui', 'pengajuan', $this->admin, $periode->tanggal_mulai->copy()->subWeeks(4));
        }

        return $p;
    }

    private function berjalan(Mahasiswa $m, PeriodeKpi $periode, TempatKpi $t, KelompokKpi $k, string $keperluan): PengajuanKpi
    {
        $p = $this->pengajuan($m, $periode, $t, PengajuanKpi::BERJALAN, $keperluan);
        $this->dokumen($p, 'surat_balasan', DokumenKpi::DIVERIFIKASI);
        AnggotaKelompok::create(['kelompok_id' => $k->id, 'mahasiswa_id' => $m->id, 'tanggal_masuk' => $periode->tanggal_mulai->copy()->subDays(3), 'status' => 'aktif']);
        $this->catat($p, 'Surat balasan diverifikasi', 'surat', $this->admin, $periode->tanggal_mulai->copy()->subDays(4));
        $this->catat($p, "Ditempatkan di {$k->nama_kelompok}", 'kelompok', $this->admin, $periode->tanggal_mulai->copy()->subDays(3));

        return $p;
    }

    private function logbook(PengajuanKpi $p, Carbon $dari, Carbon $sampai, int $menunggu): void
    {
        $hari = [];
        for ($t = $dari->copy(); $t->lte($sampai); $t->addDay()) {
            if (! $t->isWeekend()) {
                $hari[] = $t->copy();
            }
        }
        foreach ($hari as $i => $h) {
            [$judul, $desk, $hasil, $kendala] = $this->kegiatan[$i % count($this->kegiatan)];
            Logbook::create(['mahasiswa_id' => $p->mahasiswa_id, 'tanggal' => $h->toDateString(), 'judul_kegiatan' => $judul, 'deskripsi_kegiatan' => $desk,
                'hasil_kegiatan' => $hasil, 'kendala' => $kendala, 'status' => $i >= count($hari) - $menunggu ? Logbook::MENUNGGU : Logbook::DISETUJUI]);
        }
    }

    private function laporan(PengajuanKpi $p, int $versi, string $status, Carbon $waktu, ?string $komentar = null): LaporanKpi
    {
        $p->loadMissing('mahasiswa');
        $path = "laporan/{$p->mahasiswa_id}/laporan-v{$versi}.pdf";
        Storage::disk('local')->put($path, $this->pdf(["Laporan KPI {$p->mahasiswa->nama_lengkap}", "Versi {$versi}", 'BAB I Pendahuluan', 'Sistem ini dibuat untuk mempermudah semua pekerjaan di kantor.', 'Dokumen simulasi PrakTI.']));

        return LaporanKpi::create([
            'mahasiswa_id' => $p->mahasiswa_id, 'nama_file' => "Laporan_KPI_{$p->mahasiswa->nim}_v{$versi}.pdf", 'path_file' => $path, 'versi' => (string) $versi,
            'status' => $status, 'komentar_dosen' => $status === LaporanKpi::MENUNGGU ? null : $komentar, 'tanggal_upload' => $waktu,
            'diperiksa_at' => $status === LaporanKpi::MENUNGGU ? null : $waktu->copy()->addDay(),
        ]);
    }

    private function nilai(PengajuanKpi $p, DosenPembimbing $dosen, array $rincian, float $penguji, float $lapangan, string $statusEksternal): void
    {
        Penilaian::create(['mahasiswa_id' => $p->mahasiswa_id, 'jenis_penilaian' => Penilaian::PEMBIMBING, 'nilai' => Penilaian::hitungNilaiPembimbing($rincian),
            'catatan' => Penilaian::bungkusCatatan(['nama_penilai' => $dosen->nama_lengkap, 'rincian' => $rincian, 'catatan' => 'Logbook konsisten dan laporan tersusun rapi.']),
            'tanggal_penilaian' => today()->subDays(2), 'status' => Penilaian::FINAL]);
        Penilaian::create(['mahasiswa_id' => $p->mahasiswa_id, 'jenis_penilaian' => Penilaian::PENGUJI, 'nilai' => $penguji,
            'catatan' => Penilaian::bungkusCatatan(['nama_penilai' => 'Rahmat Hidayat, S.Kom., M.Cs.']), 'tanggal_penilaian' => today()->subDay(), 'status' => $statusEksternal]);
        Penilaian::create(['mahasiswa_id' => $p->mahasiswa_id, 'jenis_penilaian' => Penilaian::LAPANGAN, 'nilai' => $lapangan,
            'catatan' => Penilaian::bungkusCatatan(['nama_penilai' => $p->tempatKpi->nama_pic]), 'tanggal_penilaian' => today()->subDay(), 'status' => $statusEksternal]);
        NilaiAkhir::hitung($p->mahasiswa_id);
        $this->catat($p, 'Nilai dosen pembimbing ditetapkan', 'penilaian', $dosen->user, now()->subDays(2));
    }

    private function dokumen(PengajuanKpi $p, string $jenis, string $status): void
    {
        $p->loadMissing('mahasiswa');
        $isi = $this->pdf([DokumenKpi::JENIS[$jenis], $p->mahasiswa->nama_lengkap.' ('.$p->mahasiswa->nim.')', 'Dokumen simulasi untuk pengujian sistem PrakTI.']);
        $path = "dokumen/{$p->mahasiswa_id}/{$jenis}-simulasi.pdf";
        Storage::disk('local')->put($path, $isi);

        DokumenKpi::create([
            'mahasiswa_id' => $p->mahasiswa_id, 'pengajuan_id' => $p->id, 'jenis_dokumen' => $jenis, 'nama_file' => "{$jenis}_{$p->mahasiswa->nim}.pdf",
            'format_file' => 'pdf', 'ukuran_file' => strlen($isi), 'path_file' => $path, 'status_verifikasi' => $status,
            'uploaded_at' => now()->subWeeks(4), 'verified_at' => $status === DokumenKpi::DIVERIFIKASI ? now()->subWeeks(3) : null,
        ]);
    }

    private function catat(PengajuanKpi $p, string $aktivitas, string $modul, User $oleh, Carbon $waktu, ?string $deskripsi = null): void
    {
        $a = new AktivitasSistem(['user_id' => $oleh->id, 'aktivitas' => $aktivitas, 'modul' => $modul, 'deskripsi' => $deskripsi, 'referensi_id' => $p->mahasiswa_id, 'ip_address' => '127.0.0.1']);
        $a->created_at = $waktu;
        $a->save();
    }

    private function pdf(array $baris): string
    {
        $teks = '';
        foreach ($baris as $i => $b) {
            $b = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $b);
            $teks .= 'BT /F1 '.($i === 0 ? 18 : 11).' Tf 72 '.($i === 0 ? 770 : 740 - ($i - 1) * 18)." Td ({$b}) Tj ET\n";
        }
        $objek = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($teks)." >>\nstream\n{$teks}endstream", '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
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
