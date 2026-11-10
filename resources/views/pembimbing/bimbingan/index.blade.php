@extends('layouts.app')
@section('judul', 'Mahasiswa bimbingan')

@section('content')
    <section class="pk-panel">
        @if ($bimbingan->isEmpty())
            <div class="pk-empty">
                <i class="bi bi-mortarboard" aria-hidden="true"></i>
                <h3>Belum ada mahasiswa bimbingan</h3>
                <p>Mahasiswa akan muncul di sini setelah admin menyetujui pengajuan KPI dan menetapkan Anda sebagai pembimbing.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead>
                    <tr><th>Mahasiswa</th><th>Instansi</th><th>Logbook</th><th>Terakhir mengisi</th><th>Progress</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($bimbingan as $p)
                        @php
                            $terakhir = $p->logbooks_max_tanggal ? \Illuminate\Support\Carbon::parse($p->logbooks_max_tanggal) : null;
                            $perluPerhatian = $p->status === 'berjalan' && $p->tanggal_mulai->lte(today()->subDays(3))
                                && (! $terakhir || $terakhir->lt(today()->subDays(3)));
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $p->mahasiswa->name }}<span class="pk-sub">{{ $p->mahasiswa->nomor_induk }}</span></td>
                            <td>{{ $p->nama_instansi }}
                                <span class="pk-sub">{{ $p->status === 'selesai' ? 'KPI selesai' : 'Minggu ke-'.$p->minggu_ke }}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $p->logbooks_count }} hari
                                @if ($p->logbook_menunggu_count > 0)
                                    <span class="d-block mt-1"><span class="pk-status pk-status--amber">{{ $p->logbook_menunggu_count }} menunggu</span></span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                {{ $terakhir ? $terakhir->translatedFormat('l, d M') : 'Belum ada' }}
                                @if ($perluPerhatian)<span class="d-block mt-1"><x-status value="perhatian" /></span>@endif
                            </td>
                            <td><x-meter :nilai="$p->persentase" /></td>
                            <td class="text-end">
                                <a href="{{ route('pembimbing.bimbingan.show', $p) }}" class="btn btn-sm {{ $p->logbook_menunggu_count > 0 ? 'btn-primary' : 'btn-outline-primary' }}">
                                    {{ $p->logbook_menunggu_count > 0 ? 'Periksa' : 'Buka' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
