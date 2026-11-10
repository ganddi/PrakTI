@extends('layouts.app')
@section('judul', 'Monitoring KPI')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <ul class="nav pk-filter">
            @foreach (['berjalan' => 'Sedang berjalan', 'selesai' => 'Selesai', 'semua' => 'Semua'] as $kunci => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $status === $kunci ? 'active' : '' }}" href="{{ route('admin.monitoring', ['status' => $kunci]) }}">
                        {{ $label }}<span class="pk-count">{{ $kunci === 'semua' ? $jumlah->sum() : ($jumlah[$kunci] ?? 0) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <form method="GET" class="d-flex gap-2" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari mahasiswa atau instansi" aria-label="Cari">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </form>
    </div>

    <section class="pk-panel">
        @if ($daftar->isEmpty())
            <div class="pk-empty">
                <i class="bi bi-activity" aria-hidden="true"></i>
                <h3>Belum ada KPI untuk dipantau</h3>
                <p>KPI muncul di sini setelah pengajuan mahasiswa disetujui.</p>
            </div>
        @else
            <p class="small text-secondary mb-3">
                <span class="pk-status pk-status--amber">Perlu perhatian</span> menandai KPI berjalan yang tidak mengisi logbook lebih dari 3 hari.
            </p>
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead>
                    <tr><th>Mahasiswa</th><th>Instansi</th><th>Pembimbing</th><th>Logbook</th><th>Terakhir mengisi</th><th>Progress</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($daftar as $p)
                        @php
                            $terakhir = $p->logbooks_max_tanggal ? \Illuminate\Support\Carbon::parse($p->logbooks_max_tanggal) : null;
                            $perluPerhatian = $p->status === 'berjalan' && $p->tanggal_mulai->lte(today()->subDays(3))
                                && (! $terakhir || $terakhir->lt(today()->subDays(3)));
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $p->mahasiswa->name }}<span class="pk-sub">{{ $p->mahasiswa->nomor_induk }}</span></td>
                            <td>{{ $p->nama_instansi }}
                                <span class="pk-sub">{{ $p->status === 'selesai' ? 'Selesai' : 'Minggu ke-'.$p->minggu_ke }}, s.d. {{ $p->tanggal_selesai->translatedFormat('d M Y') }}</span>
                            </td>
                            <td>{{ $p->pembimbing->name ?? '–' }}</td>
                            <td class="text-nowrap">{{ $p->logbooks_count }} hari
                                @if ($p->logbook_menunggu_count > 0)<span class="pk-sub">{{ $p->logbook_menunggu_count }} belum diperiksa</span>@endif
                            </td>
                            <td class="text-nowrap">
                                {{ $terakhir ? $terakhir->translatedFormat('d M') : 'Belum ada' }}
                                @if ($perluPerhatian)<span class="d-block mt-1"><x-status value="perhatian" /></span>@endif
                            </td>
                            <td><x-meter :nilai="$p->persentase" /></td>
                            <td class="text-end"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $daftar->links() }}</div>
        @endif
    </section>
@endsection
