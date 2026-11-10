@extends('layouts.app')
@section('judul', 'Logbook harian')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel">
            <div class="pk-empty">
                <i class="bi bi-journal" aria-hidden="true"></i>
                <h3>Logbook belum bisa diisi</h3>
                <p>Logbook terbuka setelah pengajuan KPI Anda disetujui admin dan dosen pembimbing ditetapkan.</p>
                <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-primary">Lihat status pengajuan</a>
            </div>
        </section>
    @else
        @php $berjalan = $pengajuan->status === 'berjalan'; @endphp

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <p class="pk-lead mb-0">
                {{ $pengajuan->nama_instansi }}, {{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}.
                {{ $logbooks->total() }} hari tercatat.
            </p>
            @if ($berjalan)
                <a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Isi logbook</a>
            @endif
        </div>

        @if ($perluIsiHariIni)
            <div class="pk-reminder">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <div class="flex-grow-1">Logbook untuk hari ini, {{ today()->translatedFormat('l d F') }}, belum diisi.</div>
                <a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-sm btn-primary">Isi sekarang</a>
            </div>
        @endif

        @unless ($berjalan)
            <div class="alert alert-secondary">KPI ini sudah selesai. Logbook tetap bisa dilihat sebagai arsip.</div>
        @endunless

        @include('shared.logbook', ['logbooks' => $logbooks, 'mode' => 'mahasiswa', 'bisaTambah' => $berjalan])

        <div class="mt-3">{{ $logbooks->links() }}</div>
    @endif
@endsection
