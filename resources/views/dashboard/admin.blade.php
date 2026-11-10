@extends('layouts.app')
@section('judul', 'Dashboard admin')

@section('content')
    <div class="pk-summary mb-4">
        <a href="{{ route('admin.pengajuan.index') }}" class="pk-summary-item">
            <span class="pk-summary-value">@if ($jumlah['menunggu'] > 0)<span class="pk-mark">{{ $jumlah['menunggu'] }}</span>@else 0 @endif</span>
            <span class="pk-summary-label">Pengajuan menunggu verifikasi</span>
        </a>
        <a href="{{ route('admin.monitoring') }}" class="pk-summary-item">
            <span class="pk-summary-value">{{ $jumlah['berjalan'] }}</span>
            <span class="pk-summary-label">KPI sedang berjalan</span>
        </a>
        <a href="{{ route('admin.monitoring', ['status' => 'selesai']) }}" class="pk-summary-item">
            <span class="pk-summary-value">{{ $jumlah['selesai'] }}</span>
            <span class="pk-summary-label">KPI selesai</span>
        </a>
        <a href="{{ route('admin.pengguna.index') }}" class="pk-summary-item">
            <span class="pk-summary-value">{{ $jumlah['mahasiswa'] }}</span>
            <span class="pk-summary-label">Mahasiswa terdaftar</span>
        </a>
        <a href="{{ route('admin.pengguna.index', ['peran' => 'pembimbing']) }}" class="pk-summary-item">
            <span class="pk-summary-value">{{ $jumlah['pembimbing'] }}</span>
            <span class="pk-summary-label">Dosen pembimbing aktif</span>
        </a>
    </div>

    @if ($akunMenunggu > 0)
        <div class="pk-reminder">
            <i class="bi bi-person-check" aria-hidden="true"></i>
            <div class="flex-grow-1">{{ $akunMenunggu }} akun dosen pembimbing menunggu aktivasi.</div>
            <a href="{{ route('admin.pengguna.index', ['peran' => 'pembimbing']) }}" class="btn btn-sm btn-outline-primary">Periksa akun</a>
        </div>
    @endif

    <section class="pk-panel">
        <div class="pk-panel-head">
            <h2 class="pk-panel-title">Pengajuan yang perlu diverifikasi</h2>
            <a href="{{ route('admin.pengajuan.index') }}" class="small fw-semibold">Lihat semua pengajuan</a>
        </div>

        @if ($pengajuanMenunggu->isEmpty())
            <div class="pk-empty">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <h3>Tidak ada pengajuan yang menunggu</h3>
                <p>Pengajuan KPI baru dari mahasiswa akan muncul di sini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Periode</th><th>Diajukan</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($pengajuanMenunggu as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->mahasiswa->name }}<span class="pk-sub">{{ $p->mahasiswa->nomor_induk }}</span></td>
                            <td>{{ $p->nama_instansi }}<span class="pk-sub">{{ $p->bidang_kerja }}</span></td>
                            <td class="text-nowrap">{{ $p->tanggal_mulai->translatedFormat('d M Y') }}<span class="pk-sub">s.d. {{ $p->tanggal_selesai->translatedFormat('d M Y') }}</span></td>
                            <td class="text-nowrap">{{ $p->created_at->diffForHumans() }}</td>
                            <td class="text-end"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-primary">Periksa</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="text-secondary small mt-3 mb-0">
        <i class="bi bi-journal-text me-1" aria-hidden="true"></i>{{ $logbookHariIni }} logbook diisi mahasiswa hari ini.
        <a href="{{ route('admin.monitoring') }}">Buka monitoring</a>
    </p>
@endsection
