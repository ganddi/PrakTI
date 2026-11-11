<?php
// Jalankan dari folder proyek: php bersihkan-migrasi.php
$izin = [
    '0001_01_01_000000_create_users_table.php',
    '0001_01_01_000001_create_cache_table.php',
    '0001_01_01_000002_create_jobs_table.php',
    '2026_11_01_000001_create_mahasiswa_table.php',
    '2026_11_01_000002_create_dosen_pembimbing_table.php',
    '2026_11_01_000003_create_tempat_kpi_table.php',
    '2026_11_01_000004_create_periode_kpi_table.php',
    '2026_11_01_000005_create_kelompok_kpi_table.php',
    '2026_11_01_000006_create_anggota_kelompok_table.php',
    '2026_11_01_000007_create_penempatan_dosen_table.php',
    '2026_11_01_000008_create_pengajuan_kpi_table.php',
    '2026_11_01_000009_create_dokumen_kpi_table.php',
    '2026_11_01_000010_create_logbook_table.php',
    '2026_11_01_000011_create_laporan_kpi_table.php',
    '2026_11_01_000012_create_penilaian_table.php',
    '2026_11_01_000013_create_nilai_akhir_table.php',
    '2026_11_01_000014_create_notifikasi_table.php',
    '2026_11_01_000015_create_aktivitas_sistem_table.php',
];

$folder = __DIR__.'/database/migrations';
foreach (glob($folder.'/*.php') as $file) {
    if (! in_array(basename($file), $izin, true)) {
        unlink($file);
        echo 'Dihapus: '.basename($file).PHP_EOL;
    }
}

$ada = array_map('basename', glob($folder.'/*.php'));
echo count($ada).' file migrasi tersisa (seharusnya 18).'.PHP_EOL;

$kurang = array_diff($izin, $ada);
if ($kurang) {
    echo 'BELUM ADA, salin dari prakti.zip: '.implode(', ', $kurang).PHP_EOL;
}

$users = $folder.'/0001_01_01_000000_create_users_table.php';
if (is_file($users)) {
    echo str_contains(file_get_contents($users), "'username'")
        ? 'File users sudah versi PrakTI.'.PHP_EOL
        : 'File users MASIH versi bawaan Laravel, timpa dari prakti.zip.'.PHP_EOL;
}
