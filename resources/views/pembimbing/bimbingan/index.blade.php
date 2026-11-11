@extends('layouts.app')
@section('judul', 'Kerja Praktik Industri')
@section('subjudul', 'Mahasiswa bimbingan per kelompok')

@section('content')
    @forelse ($perKelompok as $daftar)
        @php $k = $daftar->first()->kelompok; @endphp
        <section class="pk-panel">
            <div class="pk-panel-head"><div><h2 class="pk-panel-title">{{ $k?->nama_kelompok ?? 'Tanpa kelompok' }}</h2><span class="small text-secondary">{{ $daftar->first()->periode->nama_periode }}</span></div></div>
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Mahasiswa</th><th>Instansi</th><th>Logbook</th><th>Laporan</th><th>Progres</th><th>Nilai</th><th></th></tr></thead>
                <tbody>
                @foreach ($daftar as $p)
                    @php $akhir = $p->logbooks_max_tanggal ? \Illuminate\Support\Carbon::parse($p->logbooks_max_tanggal) : null;
                         $perhatian = $p->status === 'berjalan' && $p->tanggal_mulai->lte(today()->subDays(3)) && (! $akhir || $akhir->lt(today()->subDays(3)));
                         $tindakan = $p->logbook_menunggu_count > 0 || $p->laporanTerakhir?->status === 'menunggu'; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $p->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $p->mahasiswa->nim }}</span></td>
                        <td>{{ $p->nama_instansi }}</td>
                        <td class="text-nowrap">{{ $p->logbooks_count }} hari @if ($p->logbook_menunggu_count)<span class="d-block mt-1"><span class="pk-status pk-status--amber">{{ $p->logbook_menunggu_count }} menunggu</span></span>@endif
                            @if ($perhatian)<span class="d-block mt-1"><x-status value="perhatian" /></span>@endif</td>
                        <td>@if ($p->laporanTerakhir) v{{ $p->laporanTerakhir->versi }} <x-status :value="$p->laporanTerakhir->status" />@else<span class="text-secondary">–</span>@endif</td>
                        <td><x-meter :nilai="$p->persentase" /></td>
                        <td class="text-nowrap">{{ $p->nilaiAkhir ? number_format($p->nilaiAkhir->nilai_akhir, 2, ',', '.') : '–' }}</td>
                        <td class="text-end"><a href="{{ route('pembimbing.bimbingan.show', $p) }}" class="btn btn-sm {{ $tindakan ? 'btn-primary' : 'btn-outline-primary' }}">{{ $tindakan ? 'Periksa' : 'Buka' }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>
    @empty
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-mortarboard" aria-hidden="true"></i><h3>Belum ada mahasiswa bimbingan</h3>
            <p>Mahasiswa muncul setelah pilihan tempat KPI Anda diverifikasi admin.</p><a href="{{ route('pembimbing.penempatan.index') }}" class="btn btn-outline-primary">Lihat pilihan penempatan</a></div></section>
    @endforelse
@endsection
