@extends('layouts.app')
@section('judul', 'Rekapitulasi nilai KPI')

@section('content')
    @php $f = fn ($n) => $n === null ? '–' : number_format($n, 2, ',', '.'); @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <form method="GET" class="pk-filterbar">
            <select name="status" class="form-select" aria-label="Cakupan">@foreach (['dinilai' => 'Sudah punya nilai akhir', 'selesai' => 'KPI selesai', 'semua' => 'Semua KPI berjalan & selesai'] as $k => $l)<option value="{{ $k }}" @selected(request('status', 'dinilai') === $k)>{{ $l }}</option>@endforeach</select>
            <select name="periode" class="form-select" aria-label="Periode"><option value="">Semua periode</option>@foreach ($daftarPeriode as $pr)<option value="{{ $pr->id }}" @selected((string) request('periode') === (string) $pr->id)>{{ $pr->nama_periode }}</option>@endforeach</select>
            <button class="btn btn-outline-primary" type="submit">Terapkan</button>
        </form>
        <a href="{{ route('admin.rekap.ekspor', request()->query()) }}" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Ekspor ke Excel</a>
    </div>
    <section class="pk-panel">
        @if ($rekap->isEmpty())
            <div class="pk-empty"><i class="bi bi-table" aria-hidden="true"></i><h3>Belum ada data nilai</h3><p>Nilai akhir muncul setelah ketiga komponen nilai lengkap.</p></div>
        @else
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Mahasiswa</th><th>Periode / kelompok</th><th class="text-end">Pembimbing</th><th class="text-end">Penguji</th><th class="text-end">Lapangan</th><th class="text-end">Nilai akhir</th><th>Status nilai</th></tr></thead>
                <tbody>
                @foreach ($rekap as $p)
                    @php $n = $p->nilaiAkhir; @endphp
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('admin.pengajuan.show', $p) }}#tab-penilaian" class="text-body">{{ $p->mahasiswa->nama_lengkap }}</a><span class="pk-sub">{{ $p->mahasiswa->nim }}</span></td>
                        <td>{{ $p->periode->nama_periode }}<span class="pk-sub">{{ $p->kelompok?->nama_kelompok }}</span></td>
                        <td class="text-end">{{ $f($n?->nilai_dosen_pembimbing) }}</td>
                        <td class="text-end">{{ $f($n?->nilai_dosen_penguji) }}</td>
                        <td class="text-end">{{ $f($n?->nilai_pembimbing_lapangan) }}</td>
                        <td class="text-end fw-semibold">{{ $f($n?->nilai_akhir) }} @if ($n)<span class="pk-grade">{{ $n->predikat }}</span>@endif</td>
                        <td>@if ($n)<x-status :value="$n->status" />@else<x-status value="belum" />@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-3">{{ $rekap->links() }}</div>
        @endif
    </section>
@endsection
