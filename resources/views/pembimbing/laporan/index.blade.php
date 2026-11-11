@extends('layouts.app')
@section('judul', 'Laporan mahasiswa')

@section('content')
    <section class="pk-panel">
        @if ($laporan->isEmpty())
            <div class="pk-empty"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i><h3>Belum ada laporan</h3></div>
        @else
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Mahasiswa</th><th>Versi terbaru</th><th>Diunggah</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($laporan as $l)
                    <tr><td class="fw-semibold">{{ $l->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $l->mahasiswa->nim }}</span></td><td>Versi {{ $l->versi }}</td>
                        <td class="text-nowrap">{{ $l->tanggal_upload->translatedFormat('d M Y H:i') }}</td><td><x-status :value="$l->status" /></td>
                        <td class="text-end"><a href="{{ route('pembimbing.laporan.show', $l) }}" class="btn btn-sm {{ $l->status === 'menunggu' ? 'btn-primary' : 'btn-outline-primary' }}">{{ $l->status === 'menunggu' ? 'Periksa' : 'Buka' }}</a></td></tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </section>
@endsection
