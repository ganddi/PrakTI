@extends('layouts.app')
@section('judul', 'Monitoring KPI')

@section('content')
    <form method="GET" class="pk-filterbar mb-3">
        <select name="status" class="form-select" aria-label="Status">@foreach (['berjalan' => 'Sedang berjalan', 'selesai' => 'Selesai', 'semua' => 'Semua'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach</select>
        <select name="periode" class="form-select" aria-label="Periode"><option value="">Semua periode</option>@foreach ($daftarPeriode as $pr)<option value="{{ $pr->id }}" @selected((string) request('periode') === (string) $pr->id)>{{ $pr->nama_periode }}</option>@endforeach</select>
        <select name="kelompok" class="form-select" aria-label="Kelompok"><option value="">Semua kelompok</option>
            @foreach ($daftarPeriode as $pr)<optgroup label="{{ $pr->nama_periode }}">@foreach ($pr->kelompok as $k)<option value="{{ $k->id }}" @selected((string) request('kelompok') === (string) $k->id)>{{ $k->nama_kelompok }}</option>@endforeach</optgroup>@endforeach</select>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari mahasiswa atau instansi" aria-label="Cari">
        <button class="btn btn-outline-primary" type="submit">Terapkan</button>
    </form>
    <section class="pk-panel">
        @if ($daftar->isEmpty())
            <div class="pk-empty"><i class="bi bi-activity" aria-hidden="true"></i><h3>Tidak ada KPI yang cocok</h3></div>
        @else
            <p class="small text-secondary mb-3"><x-status value="perhatian" /> menandai KPI berjalan yang tidak mengisi logbook lebih dari 3 hari.</p>
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Mahasiswa</th><th>Kelompok</th><th>Pembimbing</th><th>Logbook</th><th>Laporan</th><th>Progres</th><th>Nilai</th><th></th></tr></thead>
                <tbody>
                @foreach ($daftar as $p)
                    @php $akhir = $p->logbooks_max_tanggal ? \Illuminate\Support\Carbon::parse($p->logbooks_max_tanggal) : null;
                         $perhatian = $p->status === 'berjalan' && $p->tanggal_mulai->lte(today()->subDays(3)) && (! $akhir || $akhir->lt(today()->subDays(3))); @endphp
                    <tr>
                        <td class="fw-semibold">{{ $p->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $p->mahasiswa->nim }}</span></td>
                        <td>{{ $p->kelompok?->nama_kelompok ?? '–' }}<span class="pk-sub">{{ $p->nama_instansi }}</span></td>
                        <td>{{ $p->pembimbing?->nama_lengkap ?? '–' }}</td>
                        <td class="text-nowrap">{{ $p->logbooks_count }} hari<span class="pk-sub">terakhir {{ $akhir ? $akhir->translatedFormat('d M') : 'belum ada' }}</span>@if ($perhatian)<x-status value="perhatian" class="mt-1" />@endif</td>
                        <td>@if ($p->laporanTerakhir) v{{ $p->laporanTerakhir->versi }} <x-status :value="$p->laporanTerakhir->status" />@else<span class="text-secondary">–</span>@endif</td>
                        <td><x-meter :nilai="$p->persentase" /></td>
                        <td class="text-nowrap">{{ $p->nilaiAkhir ? number_format($p->nilaiAkhir->nilai_akhir, 2, ',', '.').' ('.$p->nilaiAkhir->predikat.')' : '–' }}</td>
                        <td class="text-end"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-3">{{ $daftar->links() }}</div>
        @endif
    </section>
@endsection
