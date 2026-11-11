@extends('layouts.app')
@section('judul', 'Pengajuan KPI')

@section('content')
    <ul class="nav pk-filter mb-3">
        @foreach (\App\Http\Controllers\Admin\PengajuanController::FILTER as $k => $l)
            <li class="nav-item"><a class="nav-link {{ $status === $k ? 'active' : '' }}" href="{{ route('admin.pengajuan.index', ['status' => $k]) }}">{{ $l }}<span class="pk-count">{{ $k === 'semua' ? $jumlah->sum() : ($jumlah[$k] ?? 0) }}</span></a></li>
        @endforeach
    </ul>
    <section class="pk-panel">
        <form method="GET" class="pk-filterbar mb-3" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <select name="periode" class="form-select" aria-label="Periode"><option value="">Semua periode</option>
                @foreach ($daftarPeriode as $pr)<option value="{{ $pr->id }}" @selected((string) request('periode') === (string) $pr->id)>{{ $pr->nama_periode }}</option>@endforeach</select>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, NIM, atau instansi" aria-label="Cari pengajuan">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </form>
        @if ($pengajuan->isEmpty())
            <div class="pk-empty"><i class="bi bi-inbox" aria-hidden="true"></i><h3>Tidak ada pengajuan di sini</h3></div>
        @else
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Mahasiswa</th><th>Tempat KPI</th><th>Periode</th><th>Tanggal pengajuan</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($pengajuan as $p)
                    <tr>
                        <td class="fw-semibold">{{ $p->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $p->mahasiswa->nim }}</span></td>
                        <td>{{ $p->nama_instansi }}@if ($p->tempatKpi->status === 'usulan')<span class="pk-sub">Instansi usulan baru</span>@endif</td>
                        <td>{{ $p->periode->nama_periode }}<span class="pk-sub">{{ $p->kelompok?->nama_kelompok }}</span></td>
                        <td class="text-nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                        <td><x-status :value="$p->status" /></td>
                        <td class="text-end"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm {{ $p->status === 'diajukan' ? 'btn-primary' : 'btn-outline-primary' }}">{{ $p->status === 'diajukan' ? 'Periksa' : 'Detail' }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-3">{{ $pengajuan->links() }}</div>
        @endif
    </section>
@endsection
