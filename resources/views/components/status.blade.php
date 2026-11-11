@props(['value'])
@php
    $peta = [
        'diajukan' => ['Menunggu verifikasi', 'amber'], 'revisi' => ['Perlu perbaikan', 'red'], 'disetujui' => ['Disetujui', 'green'],
        'berjalan' => ['Sedang berjalan', 'blue'], 'selesai' => ['Selesai', 'green'], 'menunggu' => ['Menunggu diperiksa', 'amber'],
        'diverifikasi' => ['Terverifikasi', 'green'], 'tersimpan' => ['Tersimpan', 'grey'], 'ditawarkan' => ['Tersedia untuk dipilih', 'blue'],
        'perlu_perbaikan' => ['Perlu perbaikan', 'red'], 'dibatalkan' => ['Tidak berlaku', 'grey'], 'terbuka' => ['Belum diperbaiki', 'amber'],
        'diperbaiki' => ['Sudah diperbaiki', 'green'], 'aktif' => ['Aktif', 'green'], 'nonaktif' => ['Nonaktif', 'grey'],
        'usulan' => ['Usulan mahasiswa', 'amber'], 'keluar' => ['Keluar', 'grey'], 'perhatian' => ['Perlu perhatian', 'amber'],
        'draf' => ['Draf', 'grey'], 'final' => ['Ditetapkan', 'green'], 'diinput' => ['Menunggu verifikasi', 'amber'],
        'dihitung' => ['Terhitung', 'blue'], 'belum' => ['Belum ada', 'grey'],
    ];
    [$label, $warna] = $peta[$value] ?? [ucfirst(str_replace('_', ' ', (string) $value)), 'grey'];
@endphp
<span {{ $attributes->merge(['class' => "pk-status pk-status--{$warna}"]) }}>{{ $label }}</span>
