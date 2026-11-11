@extends('layouts.cetak')
@section('judul', 'Logbook KPI '.$pengajuan->mahasiswa->nama_lengkap)

@section('content')
    <h1>LOGBOOK KERJA PRAKTIK INDUSTRI</h1>
    <table class="identitas">
        <tr><td>Nama mahasiswa</td><td>: {{ $pengajuan->mahasiswa->nama_lengkap }}</td></tr>
        <tr><td>NIM</td><td>: {{ $pengajuan->mahasiswa->nim }}</td></tr>
        <tr><td>Instansi</td><td>: {{ $pengajuan->nama_instansi }}</td></tr>
        <tr><td>Periode</td><td>: {{ $pengajuan->periode->nama_periode }} ({{ $pengajuan->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $pengajuan->tanggal_selesai->translatedFormat('d F Y') }})</td></tr>
        <tr><td>Kelompok</td><td>: {{ $pengajuan->kelompok?->nama_kelompok ?? '-' }}</td></tr>
        <tr><td>Dosen pembimbing</td><td>: {{ $pengajuan->pembimbing?->nama_lengkap ?? '-' }}</td></tr>
    </table>
    <table class="tabel">
        <thead><tr><th style="width:5%">No</th><th style="width:16%">Tanggal</th><th>Deskripsi kegiatan</th><th style="width:12%">Paraf mahasiswa</th><th style="width:12%">Paraf pembimbing</th></tr></thead>
        <tbody>
        @forelse ($logbooks as $i => $log)
            <tr>
                <td class="angka">{{ $i + 1 }}</td>
                <td>{{ $log->tanggal->translatedFormat('d M Y') }}</td>
                <td><strong>{{ $log->judul_kegiatan }}</strong><br>{!! nl2br(e($log->deskripsi_kegiatan)) !!}@if ($log->hasil_kegiatan)<br><em>Hasil:</em> {{ $log->hasil_kegiatan }}@endif</td>
                <td></td><td></td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center">Belum ada logbook.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="ttd">
        <div>Pembimbing lapangan<div class="ruang"></div><div class="nama">{{ $pengajuan->pembimbing_lapangan ?: '....................................' }}</div></div>
        <div>Mahasiswa<div class="ruang"></div><div class="nama">{{ $pengajuan->mahasiswa->nama_lengkap }}</div>NIM {{ $pengajuan->mahasiswa->nim }}</div>
    </div>
@endsection
