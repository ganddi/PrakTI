# PrakTI — Platform Praktik Industri Terintegrasi

Sistem informasi KPI mahasiswa Program Studi Informatika, disusun mengikuti **SRS** (25 FR, 25 NFR, Look & Feel) dan **SDD** Kelompok 4 (struktur database Tabel 3.1–3.16, arsitektur MVC, mockup Bab IV, security design Bab V). Dibangun dengan Laravel, MySQL, dan Bootstrap 5.

## 1. Instalasi di Laragon

<<<<<<< HEAD
1. Buat proyek Laravel baru (SDD menetapkan Laravel 13; struktur kode ini berjalan di Laravel 11 ke atas):
=======
## 1. Kebutuhan sistem
- Laragon (atau XAMPP) dengan PHP sesuai versi Laravel yang dipakai (Laravel 11 butuh minimal PHP 8.2) dan MySQL
- Composer
- Laravel 11 ke atas (struktur `bootstrap/app.php` versi baru)

## 2. Instalasi

1. Buat proyek Laravel baru dari terminal Laragon:
>>>>>>> aa6bd4ecd7f64f75723ad2558187ad82ef558c08

   ```bash
   cd C:\laragon\www
   composer create-project laravel/laravel prakti
   ```

2. **Jika sebelumnya sudah memasang PrakTI versi lama**, hapus dulu `database/migrations/2026_11_01_*.php`, seluruh isi `app/Models`, dan folder `resources/views` milik PrakTI, karena nama tabel dan model berubah total.

3. Salin isi folder ini (`app`, `bootstrap`, `config`, `database`, `lang`, `public`, `resources`, `routes`) ke folder `prakti`, pilih **Replace**.

4. Buat database `prakti`, lalu ubah `.env`:

   ```env
   APP_NAME=PrakTI
   APP_URL=http://prakti.test
   APP_LOCALE=id
   APP_FALLBACK_LOCALE=en
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=prakti
   DB_USERNAME=root
   DB_PASSWORD=
   MAIL_MAILER=log
   ```

5. Ubah `'timezone' => 'Asia/Makassar'` di `config/app.php`.

6. Jalankan:

   ```bash
   php artisan migrate:fresh --seed
   php artisan config:clear
   ```

7. Buka `http://prakti.test`, lalu klik **Masuk**.

## 2. Akun demo (login dengan email, kata sandi semua akun: `password`)

| Peran | Email | Keadaan data |
| --- | --- | --- |
| Admin | admin@prakti.test | Ada pengajuan, surat balasan, pilihan penempatan, dokumen penilaian, dan akun dosen yang menunggu |
| Dosen | hasanuddin@prakti.test | Pembimbing Kelompok 1. Laporan v2 milik Nurul menunggu diperiksa |
| Dosen | aminah@prakti.test | Pembimbing Kelompok 2, sudah mengajukan pilihan Kelompok 3 |
| Dosen | rahmat@prakti.test | Kelompok 3 dibagikan kepadanya, belum dipilih |
| Dosen | andibaso@prakti.test | Menunggu aktivasi admin (tidak bisa masuk) |
| Mahasiswa | nurul@prakti.test | Laporan v1 punya 3 bagian ditandai, v2 menunggu diperiksa |
| Mahasiswa | putri@prakti.test | Nilai lengkap, dokumen penilaian menunggu verifikasi |
| Mahasiswa | rizky@prakti.test | Logbook terakhir 4 hari lalu (perlu perhatian) |
| Mahasiswa | wulan@prakti.test | Di Kelompok 3, dosen pembimbing masih proses penempatan |
| Mahasiswa | siti@prakti.test | Surat balasan menunggu verifikasi |
| Mahasiswa | arif@prakti.test | Siap dikelompokkan |
| Mahasiswa | tenri@prakti.test | Mengusulkan instansi baru, pengajuan menunggu verifikasi |
| Mahasiswa | fitriani@prakti.test | Pengajuan diminta diperbaiki |
| Mahasiswa | fauzan@prakti.test | Proses KPI selesai |
| Mahasiswa | dewi@prakti.test | Belum mengajukan KPI |

## 3. Kesesuaian dengan struktur tabel SDD

| SDD | Tabel | Migrasi | Model |
| --- | --- | --- | --- |
| Tabel 3.1 | users | `0001_01_01_000000_create_users_table` | `User` |
| Tabel 3.2 | mahasiswa | `..._000001_create_mahasiswa_table` | `Mahasiswa` |
| Tabel 3.3 | dosen_pembimbing | `..._000002_create_dosen_pembimbing_table` | `DosenPembimbing` |
| Tabel 3.4 | tempat_kpi | `..._000003_create_tempat_kpi_table` | `TempatKpi` |
| Tabel 3.5 | periode_kpi | `..._000004_create_periode_kpi_table` | `PeriodeKpi` |
| Tabel 3.6 | kelompok_kpi | `..._000005_create_kelompok_kpi_table` | `KelompokKpi` |
| Tabel 3.7 | anggota_kelompok | `..._000006_create_anggota_kelompok_table` | `AnggotaKelompok` |
| Tabel 3.8 | penempatan_dosen | `..._000007_create_penempatan_dosen_table` | `PenempatanDosen` |
| Tabel 3.9 | pengajuan_kpi | `..._000008_create_pengajuan_kpi_table` | `PengajuanKpi` |
| Tabel 3.10 | dokumen_kpi | `..._000009_create_dokumen_kpi_table` | `DokumenKpi` |
| Tabel 3.11 | logbook | `..._000010_create_logbook_table` | `Logbook` |
| Tabel 3.12 | laporan_kpi | `..._000011_create_laporan_kpi_table` | `LaporanKpi` |
| Tabel 3.13 | penilaian | `..._000012_create_penilaian_table` | `Penilaian` |
| Tabel 3.14 | nilai_akhir | `..._000013_create_nilai_akhir_table` | `NilaiAkhir` |
| Tabel 3.15 | notifikasi | `..._000014_create_notifikasi_table` | `Notifikasi` |
| Tabel 3.16 | aktivitas_sistem | `..._000015_create_aktivitas_sistem_table` | `AktivitasSistem` |

Kolom, tipe data, panjang VARCHAR, dan kunci primer `INT` mengikuti tabel SDD. Tambahan di luar SDD hanya tabel bawaan Laravel (`sessions`, `password_reset_tokens`, `cache`, `jobs`) dan kolom `remember_token` di `users` untuk fitur "Tetap masuk".

### Cara beberapa kebutuhan SRS dipenuhi tanpa menambah tabel

- **Dosen pembimbing seorang mahasiswa** ditentukan dari `penempatan_dosen` yang berstatus `disetujui` untuk pasangan kelompok (dari `anggota_kelompok`) dan tempat KPI (dari `pengajuan_kpi`). Satu kelompok bisa punya pembimbing berbeda per tempat.
- **Instansi baru** yang diusulkan mahasiswa saat pengajuan disimpan di `tempat_kpi` dengan status `usulan`. Tempat berstatus `aktif` tampil di landing page.
- **Penandaan bagian laporan (FR-15)** disimpan di kolom `laporan_kpi.halaman_revisi` dalam format JSON (halaman, kutipan yang disorot, catatan, status). Feedback umum di `komentar_dosen`.
- **Rincian kriteria nilai dosen** dan nama penilai disimpan di kolom `penilaian.catatan` dalam format JSON. Satu baris per jenis penilaian: `dosen_pembimbing`, `dosen_penguji`, `pembimbing_lapangan`.
- **Catatan bimbingan logbook** memakai kolom `logbook.catatan_dosen`.
- **Progress KPI** dihitung dari 8 tahapan (mockup dashboard SDD) dan data logbook, tidak disimpan di tabel terpisah.
- **Template dokumen** disimpan sebagai berkas di `storage/app/private/template/{jenis}`, dikelola admin lewat menu Template dokumen.
- **Riwayat proses KPI**: untuk modul proses KPI, `aktivitas_sistem.referensi_id` berisi `mahasiswa_id`. Login, logout, dan perubahan akun juga dicatat beserta alamat IP dan user agent (SDD Bab V: Activity Logging).

## 4. Menu sesuai mockup SDD

- **Mahasiswa**: Dashboard · Pengajuan KPI · Kerja Praktik Industri · Logbook harian · Laporan · Progress · Penilaian KPI · Rekapitulasi nilai · Pengaturan
- **Dosen pembimbing**: Dashboard · Kerja Praktik Industri · Pilihan penempatan · Daftar logbook mahasiswa · Laporan mahasiswa · Dokumen · Riwayat aktivitas mahasiswa · Nilai mahasiswa · Pengaturan
- **Admin**: Dashboard · Kerja Praktik Industri (periode & kelompok) · Pengguna · Tempat KPI · Pengajuan KPI · Penempatan dosen · Monitoring KPI · Dokumen · Rekap nilai · Statistik · Template dokumen · Aktivitas sistem · Pengaturan

Dashboard mengikuti Gambar 4.12–4.14: indikator 8 tahapan, kartu ringkasan, grafik progres satu tim dan donat progres (mahasiswa); kartu, grafik aktivitas mingguan dan mahasiswa per tahun (dosen); kartu, tabel pengajuan terbaru, donat status KPI, dan aktivitas terbaru (admin).

## 5. Pengaturan penilaian

`config/prakti.php` berisi asumsi awal yang perlu disesuaikan dengan pedoman prodi: bobot nilai akhir (pembimbing 40%, penguji 30%, lapangan 30%), kriteria dosen pembimbing, skala predikat, daftar persyaratan pengajuan, nilai bawaan program studi dan fakultas, serta penandatangan dokumen nilai akhir. Jalankan `php artisan config:clear` setelah mengubahnya.

## 6. Skenario uji untuk Tester/QA

1. Daftar akun mahasiswa baru, ajukan KPI dengan **Instansi lain**, lalu sebagai admin pastikan instansi itu muncul di Tempat KPI berstatus usulan.
2. Sebagai `dewi@prakti.test`, ajukan KPI. Admin menyetujui, Dewi mengunggah surat balasan, admin memverifikasi, lalu di **Kerja Praktik Industri > periode** klik **Kelompokkan otomatis per tempat KPI**.
3. Sebagai `rahmat@prakti.test`, pilih Kelompok 3 dan kirim. Admin menyetujui di Penempatan dosen. Pastikan pilihan dosen lain menjadi "Tidak berlaku" dan `wulan@prakti.test` melihat nama dosen di menu Kerja Praktik Industri.
4. Sebagai `hasanuddin@prakti.test`, buka Laporan mahasiswa, periksa laporan Nurul versi 2: tandai bagian versi 1 yang sudah diperbaiki, tambah tanda baru, lalu minta revisi. Pastikan Nurul melihat kutipan disorot kuning.
5. Setujui laporan, input nilai (draf lalu tetapkan). Sebagai mahasiswa, isi nilai penguji dan pembimbing lapangan, cocokkan nilai akhir dengan perhitungan manual.
6. Sebagai admin, coba verifikasi dokumen nilai akhir sebelum dokumen penilaian terverifikasi (harus ditolak). Setelah semua terverifikasi, status KPI menjadi Selesai.
7. Hak akses: mahasiswa membuka `/admin/pengguna` (403); `aminah@prakti.test` membuka `/pembimbing/kerja-praktik/{id}` milik mahasiswa Kelompok 1 (403); tautan unduh dokumen Nurul dibuka sebagai Rizky (403).
8. Periksa tabel `aktivitas_sistem` atau menu Aktivitas sistem: login, logout, verifikasi, dan penilaian tercatat beserta alamat IP.
9. Ganti kata sandi lewat Pengaturan dengan kata sandi lama yang salah (harus ditolak).
10. Ekspor logbook (cetak dan CSV) serta rekap nilai, buka di Excel.

Catatan: `tests/Feature/ExampleTest.php` bawaan Laravel membuka `/` yang kini membaca tabel `tempat_kpi`, sehingga tes itu memerlukan database termigrasi. Tambahkan trait `RefreshDatabase` pada tes tersebut atau hapus jika tidak dipakai.
