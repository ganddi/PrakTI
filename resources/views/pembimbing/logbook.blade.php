@extends('layouts.app')
@section('judul', 'Daftar logbook mahasiswa')
@section('subjudul', 'Periksa dan beri catatan pada logbook mahasiswa bimbingan')

@section('content')
    <form method="GET" class="pk-filterbar mb-3">
        <select name="status" class="form-select" aria-label="Status">@foreach (['menunggu' => 'Menunggu diperiksa', 'revisi' => 'Perlu revisi', 'disetujui' => 'Disetujui', 'semua' => 'Semua'] as $k => $l)<option value="{{ $k }}" @selected(request('status', 'menunggu') === $k)>{{ $l }}</option>@endforeach</select>
        <select name="mahasiswa" class="form-select" aria-label="Mahasiswa"><option value="">Semua mahasiswa</option>@foreach ($bimbingan as $b)<option value="{{ $b->mahasiswa_id }}" @selected((string) request('mahasiswa') === (string) $b->mahasiswa_id)>{{ $b->mahasiswa->nama_lengkap }}</option>@endforeach</select>
        <button class="btn btn-outline-primary" type="submit">Terapkan</button>
        @if (request('mahasiswa') && isset($pengajuanPerMhs[request('mahasiswa')]))<a href="{{ route('ekspor.logbook.cetak', $pengajuanPerMhs[request('mahasiswa')]) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Lihat dokumen logbook</a>@endif
    </form>
    @include('shared.logbook', ['logbooks' => $logbook, 'mode' => 'pembimbing', 'tampilkanNama' => true])
    <div class="mt-3">{{ $logbook->links() }}</div>
@endsection
