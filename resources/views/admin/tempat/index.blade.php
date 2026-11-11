@extends('layouts.app')
@section('judul', 'Tempat KPI')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <ul class="nav pk-filter">
            @foreach (['' => 'Semua', 'aktif' => 'Aktif', 'usulan' => 'Usulan mahasiswa', 'nonaktif' => 'Nonaktif'] as $k => $l)
                <li class="nav-item"><a class="nav-link {{ request('status', '') === $k ? 'active' : '' }}" href="{{ route('admin.tempat.index', $k ? ['status' => $k] : []) }}">{{ $l }}</a></li>
            @endforeach
        </ul>
        <a href="{{ route('admin.tempat.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah tempat KPI</a>
    </div>
    <p class="pk-lead">Tempat berstatus aktif tampil di <a href="{{ route('beranda') }}#tempat" target="_blank" rel="noopener">landing page</a>. Instansi yang diusulkan mahasiswa saat pengajuan berstatus usulan.</p>
    <section class="pk-panel">
        @if ($tempat->isEmpty())
            <div class="pk-empty"><i class="bi bi-building" aria-hidden="true"></i><h3>Belum ada tempat KPI</h3></div>
        @else
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Instansi</th><th>Lokasi</th><th>PIC</th><th class="text-end">Pengajuan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @foreach ($tempat as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->nama_instansi }}<span class="pk-sub">{{ $t->jenis_instansi }}</span></td>
                        <td>{{ $t->kota }}<span class="pk-sub">{{ $t->provinsi }}</span></td>
                        <td>{{ $t->nama_pic ?? '–' }}<span class="pk-sub">{{ $t->no_hp_pic }}</span></td>
                        <td class="text-end">{{ $t->pengajuan_count }}</td>
                        <td><x-status :value="$t->status" /></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.tempat.edit', $t) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form method="POST" action="{{ route('admin.tempat.destroy', $t) }}" class="d-inline" data-confirm="Hapus {{ $t->nama_instansi }}?">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus {{ $t->nama_instansi }}"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </section>
@endsection
