@props(['value'])
@php
    $peta = [
        'diajukan' => ['Menunggu verifikasi', 'amber'],
        'ditolak' => ['Ditolak', 'red'],
        'berjalan' => ['Sedang berjalan', 'blue'],
        'selesai' => ['Selesai', 'green'],
        'menunggu' => ['Menunggu diperiksa', 'amber'],
        'disetujui' => ['Disetujui', 'green'],
        'diverifikasi' => ['Terverifikasi', 'green'],
        'revisi' => ['Perlu revisi', 'red'],
        'aktif' => ['Aktif', 'green'],
        'nonaktif' => ['Nonaktif', 'amber'],
        'perhatian' => ['Perlu perhatian', 'amber'],
    ];
    [$label, $warna] = $peta[$value] ?? [ucfirst((string) $value), 'grey'];
@endphp
<span {{ $attributes->merge(['class' => "pk-status pk-status--{$warna}"]) }}>{{ $label }}</span>
