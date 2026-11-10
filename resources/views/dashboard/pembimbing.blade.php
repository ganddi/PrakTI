@extends('layouts.app')
@section('judul', 'Dashboard pembimbing')

@section('content')
    <div class="pk-summary mb-4">
        <a href="{{ route('pembimbing.bimbingan.index') }}" class="pk-summary-item">
            <span class="pk-summary-value">{{ $bimbingan->count() }}</span>
            <span class="pk-summary-label">Mahasiswa sedang KPI</span>
        </a>
        <div class="pk-summary-item">
            <span class="pk-summary-value">@if ($jumlahLogbookMenunggu > 0)<span class="pk-mark">{{ $jumlahLogbookMenunggu }}</span>@else 0 @endif</span>
            <span class="pk-summary-label">Logbook menunggu diperiksa</span>
        </div>
        <div class="pk-summary-item">
            <span class="pk-summary-value">@if ($jumlahProgressMenunggu > 0)<span class="pk-mark">{{ $jumlahProgressMenunggu }}</span>@else 0 @endif</span>
            <span class="pk-summary-label">Progress menunggu verifikasi</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="pk-panel h-100">
                <div class="pk-panel-head">
                    <h2 class="pk-panel-title">Logbook menunggu diperiksa</h2>
                </div>
                @forelse ($logbookMenunggu as $log)
                    <a href="{{ route('pembimbing.bimbingan.show', $log->pengajuan) }}#logbook-{{ $log->id }}"
                       class="d-block text-decoration-none text-body py-2 {{ $loop->first ? '' : 'border-top' }}">
                        <div class="d-flex justify-content-between gap-3">
                            <span class="fw-semibold">{{ $log->pengajuan->mahasiswa->name }}</span>
                            <span class="text-secondary small text-nowrap">{{ $log->tanggal->translatedFormat('l, d M') }}</span>
                        </div>
                        <div class="text-secondary small text-truncate">{{ $log->kegiatan }}</div>
                    </a>
                @empty
                    <div class="pk-empty">
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        <h3>Semua logbook sudah diperiksa</h3>
                        <p>Logbook baru dari mahasiswa bimbingan akan muncul di sini.</p>
                    </div>
                @endforelse
                @if ($jumlahLogbookMenunggu > $logbookMenunggu->count())
                    <p class="small text-secondary mt-2 mb-0">Dan {{ $jumlahLogbookMenunggu - $logbookMenunggu->count() }} logbook lainnya.</p>
                @endif
            </section>
        </div>

        <div class="col-xl-5">
            <section class="pk-panel h-100">
                <div class="pk-panel-head">
                    <h2 class="pk-panel-title">Progress mahasiswa</h2>
                    <a href="{{ route('pembimbing.bimbingan.index') }}" class="small fw-semibold">Semua mahasiswa</a>
                </div>
                @forelse ($bimbingan as $p)
                    <a href="{{ route('pembimbing.bimbingan.show', $p) }}" class="d-block text-decoration-none text-body py-2 {{ $loop->first ? '' : 'border-top' }}">
                        <div class="d-flex justify-content-between gap-2 mb-1">
                            <span class="fw-semibold">{{ $p->mahasiswa->name }}</span>
                            <span class="small text-secondary">Minggu ke-{{ $p->minggu_ke }}</span>
                        </div>
                        <x-meter :nilai="$p->persentase" />
                    </a>
                @empty
                    <div class="pk-empty">
                        <i class="bi bi-mortarboard" aria-hidden="true"></i>
                        <h3>Belum ada mahasiswa bimbingan</h3>
                        <p>Admin program studi akan menetapkan mahasiswa bimbingan setelah pengajuan KPI disetujui.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
