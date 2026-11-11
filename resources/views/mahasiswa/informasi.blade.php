@extends('layouts.app')
@section('judul', 'Kerja Praktik Industri')
@section('subjudul', 'Informasi periode, kelompok, tempat, dan dosen pembimbing')

@section('content')
    @if (! $pengajuan || ! $pengajuan->kelompok)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-building" aria-hidden="true"></i><h3>Penempatan KPI belum ditetapkan</h3>
            <p>Informasi tampil di sini setelah surat balasan Anda diverifikasi dan admin menempatkan Anda di kelompok.</p>
            <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-primary">Lihat status pengajuan</a></div></section>
    @else
        @php $k = $pengajuan->kelompok; $d = $pengajuan->pembimbing; @endphp
        <section class="pk-panel mb-4"><x-tahapan :pengajuan="$pengajuan" /></section>
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Pelaksanaan KPI</h2>
                    <dl class="pk-info pk-info--stack">
                        <div><dt>Periode</dt><dd>{{ $pengajuan->periode->nama_periode }} ({{ $pengajuan->periode->kode_periode }})<span class="d-block fw-normal small text-secondary">{{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</span></dd></div>
                        <div><dt>Kelompok</dt><dd>{{ $k->nama_kelompok }} <span class="fw-normal text-secondary">({{ $k->kode_kelompok }})</span></dd></div>
                        <div><dt>Tempat pelaksanaan</dt><dd>{{ $pengajuan->nama_instansi }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->alamat_instansi }}</span></dd></div>
                        <div><dt>Penanggung jawab instansi</dt><dd>{{ $pengajuan->pembimbing_lapangan ?: '–' }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->kontak_instansi }}</span></dd></div>
                        <div><dt>Dosen pembimbing</dt><dd>@if ($d){{ $d->nama_lengkap }}<span class="d-block fw-normal small text-secondary">{{ $d->email }}@if ($d->no_hp), {{ $d->no_hp }}@endif</span>@else<span class="fw-normal text-secondary">Sedang dalam proses penempatan</span>@endif</dd></div>
                    </dl>
                </section>
            </div>
            <div class="col-lg-5">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Anggota kelompok</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($k->anggotaAktif as $a)
                            <li class="py-2 {{ $loop->first ? '' : 'border-top' }}"><span class="fw-semibold">{{ $a->mahasiswa->nama_lengkap }}</span>@if ($a->mahasiswa_id === $pengajuan->mahasiswa_id) <span class="small text-secondary">(Anda)</span>@endif
                                <span class="d-block small text-secondary">{{ $a->mahasiswa->nim }}</span></li>
                        @endforeach
                    </ul>
                </section>
            </div>
        </div>
        <section class="pk-panel mt-4"><h2 class="pk-panel-title mb-3">Riwayat proses KPI</h2>@include('shared.riwayat', ['aktivitas' => $pengajuan->riwayat])</section>
    @endif
@endsection
