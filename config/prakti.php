<?php

/*
| Pengaturan khusus PrakTI.
| Nilai di bawah adalah asumsi awal karena SRS tidak menyebutkan angka pastinya.
| Sesuaikan dengan pedoman KPI program studi, lalu jalankan: php artisan config:clear
*/
return [

    // Informasi dokumen persyaratan yang ditampilkan sebelum mahasiswa mengirim pengajuan (NFR-05)
    'persyaratan_pengajuan' => [
        'Dokumen persyaratan KPI digabung dalam satu berkas PDF (misalnya KHS/transkrip dan surat keterangan aktif kuliah), maksimal 5 MB.',
        'Proposal KPI (opsional) dalam format PDF atau Word, maksimal 5 MB.',
    ],

    // Bobot nilai akhir (FR-19). Total harus 100.
    'bobot_nilai_akhir' => [
        'pembimbing' => 40,
        'penguji' => 30,
        'lapangan' => 30,
    ],

    // Template penilaian Dosen Pembimbing (FR-17). Total bobot harus 100.
    'kriteria_pembimbing' => [
        'sikap' => ['label' => 'Sikap dan kedisiplinan selama bimbingan', 'bobot' => 20],
        'logbook' => ['label' => 'Kelengkapan dan kualitas logbook', 'bobot' => 20],
        'laporan' => ['label' => 'Isi dan penulisan laporan KPI', 'bobot' => 40],
        'penguasaan' => ['label' => 'Penguasaan pekerjaan dan materi KPI', 'bobot' => 20],
    ],

    // Konversi nilai angka ke huruf: batas bawah => huruf (urut dari terbesar)
    'skala_huruf' => [
        85 => 'A',
        80 => 'A-',
        75 => 'B+',
        70 => 'B',
        65 => 'B-',
        60 => 'C+',
        55 => 'C',
        40 => 'D',
        0 => 'E',
    ],

    // Penandatangan pada dokumen nilai akhir
    'penandatangan' => [
        ['jabatan' => 'Dosen Pembimbing', 'dari_pembimbing' => true],
        ['jabatan' => 'Koordinator KPI', 'nama' => '....................................'],
        ['jabatan' => 'Ketua Program Studi Informatika', 'nama' => '....................................'],
    ],

    'nama_prodi' => 'Program Studi Informatika',

    // Nilai bawaan saat registrasi (SDD Tabel 3.2 dan 3.3)
    'program_studi' => 'Informatika',
    'fakultas' => 'Fakultas Teknik',

    // Pilihan jenis instansi tempat KPI (SDD Tabel 3.4)
    'jenis_instansi' => ['Instansi pemerintah', 'Perusahaan swasta', 'BUMN/BUMD', 'Startup', 'Lembaga pendidikan', 'Lainnya'],
];
