@extends('layouts.app')
@section('judul', 'Dashboard')
@section('subjudul', 'Kelola mahasiswa bimbingan dan pelaksanaan KPI')

@section('content')
    @if ($tindakan['penempatan'] > 0)
        <div class="pk-reminder"><i class="bi bi-geo-alt" aria-hidden="true"></i>
            <div class="flex-grow-1">{{ $tindakan['penempatan'] }} tempat KPI dibagikan kepada Anda dan menunggu pilihan Anda.</div>
            <a href="{{ route('pembimbing.penempatan.index') }}" class="btn btn-sm btn-primary">Pilih tempat KPI</a></div>
    @endif

    <div class="pk-stats mb-4">
        <x-kartu ikon="bi-mortarboard" label="Total mahasiswa" :nilai="$kartu['mahasiswa']" ket="Mahasiswa bimbingan" :href="route('pembimbing.bimbingan.index')" />
        <x-kartu ikon="bi-person-check" label="Mahasiswa aktif" :nilai="$kartu['aktif']" ket="Sedang KPI" warna="ink" />
        <x-kartu ikon="bi-building" label="Total instansi" :nilai="$kartu['instansi']" ket="Tempat KPI dibimbing" warna="gold" />
    </div>

    @if ($tindakan['logbook'] || $tindakan['laporan'])
        <div class="d-flex flex-wrap gap-2 mb-4">
            @if ($tindakan['logbook'])<a href="{{ route('pembimbing.logbook.index') }}" class="btn btn-outline-primary btn-sm">Logbook menunggu diperiksa <span class="pk-count-badge">{{ $tindakan['logbook'] }}</span></a>@endif
            @if ($tindakan['laporan'])<a href="{{ route('pembimbing.laporan.index') }}" class="btn btn-outline-primary btn-sm">Laporan menunggu diperiksa <span class="pk-count-badge">{{ $tindakan['laporan'] }}</span></a>@endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="pk-panel h-100">
                <h2 class="pk-panel-title mb-1"><i class="bi bi-graph-up me-1"></i>Aktivitas mahasiswa</h2>
                <p class="small text-secondary">Jumlah logbook yang diisi mahasiswa bimbingan per minggu</p>
                @php
                    $nilai = $mingguan->values(); $maks = max($nilai->max(), 1); $n = $nilai->count();
                    $titik = $nilai->map(fn ($v, $i) => round(30 + $i * (540 / max($n - 1, 1)), 1).','.round(170 - $v / $maks * 140, 1))->implode(' ');
                @endphp
                <svg viewBox="0 0 600 210" class="pk-line" role="img" aria-label="{{ $mingguan->map(fn ($v, $k) => $k.' '.$v)->implode(', ') }}">
                    @foreach ([30, 100, 170] as $y)<line x1="30" x2="570" y1="{{ $y }}" y2="{{ $y }}" class="pk-line-grid" />@endforeach
                    <polygon points="30,170 {{ $titik }} 570,170" class="pk-line-area" />
                    <polyline points="{{ $titik }}" class="pk-line-path" />
                    @foreach ($nilai as $i => $v)
                        @php $x = round(30 + $i * (540 / max($n - 1, 1)), 1); $y = round(170 - $v / $maks * 140, 1); @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" class="pk-line-dot" /><text x="{{ $x }}" y="{{ $y - 12 }}" class="pk-line-val">{{ $v }}</text>
                        <text x="{{ $x }}" y="198" class="pk-line-lbl">{{ $mingguan->keys()[$i] }}</text>
                    @endforeach
                </svg>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="pk-panel h-100">
                <h2 class="pk-panel-title mb-1"><i class="bi bi-bar-chart me-1"></i>Mahasiswa bimbingan</h2>
                <p class="small text-secondary">Jumlah mahasiswa per tahun periode</p>
                @if ($perTahun->isEmpty())<p class="text-secondary mb-0">Belum ada mahasiswa bimbingan.</p>
                @else<x-batang :data="$perTahun->all()" :sorot="$perTahun->keys()->last()" />@endif
            </section>
        </div>
    </div>

    <section class="pk-panel mt-4">
        <div class="pk-panel-head"><h2 class="pk-panel-title"><i class="bi bi-clock-history me-1"></i>Aktivitas mahasiswa</h2><a href="{{ route('pembimbing.riwayat') }}" class="small fw-semibold">Lihat semua</a></div>
        @include('shared.riwayat', ['aktivitas' => $aktivitas, 'namaMahasiswa' => $namaMahasiswa])
    </section>
@endsection
