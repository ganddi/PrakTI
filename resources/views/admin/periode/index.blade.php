@extends('layouts.app')
@section('judul', 'Kerja Praktik Industri')
@section('subjudul', 'Periode dan kelompok KPI')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Periode KPI</h2>
                @if ($periode->isEmpty())
                    <div class="pk-empty"><i class="bi bi-collection" aria-hidden="true"></i><h3>Belum ada periode</h3><p>Buat periode agar mahasiswa dapat mengajukan KPI.</p></div>
                @else
                    <div class="table-responsive"><table class="table pk-table">
                        <thead><tr><th>Periode</th><th>Tanggal</th><th class="text-end">Kelompok</th><th class="text-end">Pengajuan</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($periode as $pr)
                            <tr>
                                <td class="fw-semibold">{{ $pr->nama_periode }}<span class="pk-sub">{{ $pr->kode_periode }}, {{ $pr->tahun }}</span></td>
                                <td class="text-nowrap">{{ $pr->tanggal_mulai->translatedFormat('d M Y') }}<span class="pk-sub">s.d. {{ $pr->tanggal_selesai->translatedFormat('d M Y') }}</span></td>
                                <td class="text-end">{{ $pr->kelompok_count }}</td>
                                <td class="text-end">{{ $pr->pengajuan_count }}</td>
                                <td><x-status :value="$pr->status" /></td>
                                <td class="text-end"><a href="{{ route('admin.periode.show', $pr) }}" class="btn btn-sm btn-outline-primary">Kelola kelompok</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>
        </div>
        <div class="col-lg-4">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Buat periode</h2>
                <form method="POST" action="{{ route('admin.periode.store') }}">@csrf
                    @include('admin.periode._form', ['pr' => new \App\Models\PeriodeKpi()])
                    <button class="btn btn-primary w-100" type="submit">Simpan periode</button>
                </form>
            </section>
        </div>
    </div>
@endsection
