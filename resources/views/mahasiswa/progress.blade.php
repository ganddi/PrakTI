@extends('layouts.app')
@section('judul', 'Progress')
@section('subjudul', 'Perkembangan pelaksanaan KPI berdasarkan tahapan dan logbook')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-graph-up" aria-hidden="true"></i><h3>Belum ada progres</h3><p>Progres tampil setelah Anda mengajukan KPI.</p></div></section>
    @else
        @php $t = collect($pengajuan->tahapan()); @endphp
        <section class="pk-panel mb-4"><x-tahapan :pengajuan="$pengajuan" /></section>
        <div class="row g-4">
            <div class="col-lg-4">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Progres Anda</h2>
                    <x-donat :tengah="$pengajuan->persentase.'%'" ket="Total tahapan" :data="[
                        'Selesai' => [$t->filter(fn ($k) => $k === 'done')->count(), '#2E8B62'],
                        'Sedang berjalan' => [$t->filter(fn ($k) => in_array($k, ['current', 'failed']))->count(), '#1F3A5F'],
                        'Belum dimulai' => [$t->filter(fn ($k) => $k === '')->count(), '#DCE4EE'],
                    ]" />
                </section>
            </div>
            <div class="col-lg-8">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-1">Logbook per minggu</h2>
                    <p class="small text-secondary">Jumlah hari kegiatan yang tercatat setiap minggu pelaksanaan</p>
                    @if ($mingguan->isEmpty())<p class="text-secondary mb-0">Belum ada minggu pelaksanaan.</p>@else<x-batang :data="$mingguan->all()" :maks="5" />@endif
                </section>
            </div>
        </div>
        @if ($tim->isNotEmpty())
            <section class="pk-panel mt-4">
                <h2 class="pk-panel-title mb-1">Progres dalam satu tim</h2>
                <p class="small text-secondary">Persentase tahapan KPI tiap anggota kelompok</p>
                <x-batang :data="$tim->pluck('persen', 'nama')->all()" satuan="%" :maks="100" :sorot="$tim->firstWhere('saya', true)['nama'] ?? null" />
            </section>
        @endif
        <section class="pk-panel mt-4">
            <h2 class="pk-panel-title mb-3">Rincian tahapan</h2>
            <ol class="pk-checklist-steps">
                @foreach ($t as $label => $k)
                    <li class="{{ $k ? 'is-'.$k : '' }}"><span class="fw-semibold">{{ $label }}</span>
                        <span class="text-secondary small">{{ ['done' => 'Selesai', 'current' => 'Sedang berjalan', 'failed' => 'Perlu perbaikan', '' => 'Belum dimulai'][$k] }}</span></li>
                @endforeach
            </ol>
        </section>
    @endif
@endsection
