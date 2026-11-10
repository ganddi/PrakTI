@extends('layouts.app')
@section('judul', 'Pengajuan KPI')

@section('content')
    @php
        $filter = ['diajukan' => 'Menunggu verifikasi', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'semua' => 'Semua'];
    @endphp
    <ul class="nav pk-filter mb-3">
        @foreach ($filter as $kunci => $label)
            <li class="nav-item">
                <a class="nav-link {{ $status === $kunci ? 'active' : '' }}" href="{{ route('admin.pengajuan.index', ['status' => $kunci]) }}">
                    {{ $label }}<span class="pk-count">{{ $kunci === 'semua' ? $jumlah->sum() : ($jumlah[$kunci] ?? 0) }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <section class="pk-panel">
        <form method="GET" class="row g-2 mb-3" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-sm-8 col-md-6">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, NIM, atau instansi" aria-label="Cari pengajuan">
            </div>
            <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Cari</button></div>
        </form>

        @if ($pengajuan->isEmpty())
            <div class="pk-empty">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <h3>Tidak ada pengajuan di sini</h3>
                <p>{{ $status === 'diajukan' ? 'Semua pengajuan sudah diverifikasi.' : 'Coba pilih status lain atau ubah kata kunci pencarian.' }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead>
                    <tr><th>Mahasiswa</th><th>Instansi</th><th>Periode</th><th>Pembimbing</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($pengajuan as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->mahasiswa->name }}<span class="pk-sub">{{ $p->mahasiswa->nomor_induk }}</span></td>
                            <td>{{ $p->nama_instansi }}<span class="pk-sub">{{ $p->bidang_kerja }}</span></td>
                            <td class="text-nowrap">{{ $p->tanggal_mulai->translatedFormat('d M Y') }}<span class="pk-sub">s.d. {{ $p->tanggal_selesai->translatedFormat('d M Y') }}</span></td>
                            <td>{{ $p->pembimbing->name ?? '–' }}</td>
                            <td><x-status :value="$p->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm {{ $p->status === 'diajukan' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    {{ $p->status === 'diajukan' ? 'Periksa' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $pengajuan->links() }}</div>
        @endif
    </section>
@endsection
