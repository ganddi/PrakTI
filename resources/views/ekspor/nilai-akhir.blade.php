@extends('layouts.cetak')
@section('judul', 'Nilai akhir KPI '.$pengajuan->mahasiswa->nama_lengkap)

@php
    $n = $pengajuan->nilaiAkhir;
    $f = fn ($v) => number_format((float) $v, 2, ',', '.');
    $pb = $pengajuan->penilaianJenis('dosen_pembimbing');
    $baris = [
        ['Dosen pembimbing', $pengajuan->pembimbing?->nama_lengkap, $n->bobot_dosen_pembimbing, $n->nilai_dosen_pembimbing],
        ['Dosen penguji', $pengajuan->penilaianJenis('dosen_penguji')?->nama_penilai, $n->bobot_dosen_penguji, $n->nilai_dosen_penguji],
        ['Pembimbing lapangan', $pengajuan->penilaianJenis('pembimbing_lapangan')?->nama_penilai, $n->bobot_pembimbing_lapangan, $n->nilai_pembimbing_lapangan],
    ];
    $ttd = config('prakti.penandatangan');
@endphp

@section('content')
    <h1>DOKUMEN NILAI AKHIR KERJA PRAKTIK INDUSTRI</h1>
    <table class="identitas">
        <tr><td>Nama mahasiswa</td><td>: {{ $pengajuan->mahasiswa->nama_lengkap }}</td></tr>
        <tr><td>NIM</td><td>: {{ $pengajuan->mahasiswa->nim }}</td></tr>
        <tr><td>Program studi</td><td>: {{ $pengajuan->mahasiswa->program_studi }}, {{ $pengajuan->mahasiswa->fakultas }}</td></tr>
        <tr><td>Instansi</td><td>: {{ $pengajuan->nama_instansi }}</td></tr>
        <tr><td>Periode</td><td>: {{ $pengajuan->periode->nama_periode }}</td></tr>
    </table>
    <table class="tabel">
        <thead><tr><th>Komponen</th><th>Nama penilai</th><th class="angka">Bobot</th><th class="angka">Nilai</th><th class="angka">Nilai x bobot</th></tr></thead>
        <tbody>
        @foreach ($baris as [$k, $nama, $b, $v])
            <tr><td>{{ $k }}</td><td>{{ $nama ?? '-' }}</td><td class="angka">{{ $f($b) }}%</td><td class="angka">{{ $f($v) }}</td><td class="angka">{{ $f($v * $b / 100) }}</td></tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="4">Nilai akhir</th><th class="angka">{{ $f($n->nilai_akhir) }}</th></tr>
            <tr><th colspan="4">Predikat</th><th class="angka">{{ $n->predikat }}</th></tr>
        </tfoot>
    </table>
    @if ($pb?->rincian)
        <table class="tabel">
            <thead><tr><th>Rincian nilai dosen pembimbing</th><th class="angka">Bobot</th><th class="angka">Nilai</th></tr></thead>
            <tbody>
            @foreach (config('prakti.kriteria_pembimbing') as $kunci => $k)
                <tr><td>{{ $k['label'] }}</td><td class="angka">{{ $k['bobot'] }}%</td><td class="angka">{{ isset($pb->rincian[$kunci]) ? $f($pb->rincian[$kunci]) : '-' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif
    <div class="ttd" style="--kolom: {{ count($ttd) }}">
        @foreach ($ttd as $t)
            <div>{{ $t['jabatan'] }}<div class="ruang"></div><div class="nama">{{ ($t['dari_pembimbing'] ?? false) ? ($pengajuan->pembimbing?->nama_lengkap ?? '....................................') : $t['nama'] }}</div></div>
        @endforeach
    </div>
    <p class="catatan">Nilai akhir dihitung otomatis oleh PrakTI pada {{ $n->tanggal_hitung->translatedFormat('d F Y') }}. Setelah ditandatangani, unggah kembali dokumen ini melalui menu Rekapitulasi nilai.</p>
@endsection
