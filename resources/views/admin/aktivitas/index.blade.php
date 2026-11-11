@extends('layouts.app')
@section('judul', 'Aktivitas sistem')

@section('content')
    <form method="GET" class="pk-filterbar mb-3" role="search">
        <select name="modul" class="form-select" aria-label="Modul"><option value="">Semua modul</option>@foreach ($daftarModul as $m)<option value="{{ $m }}" @selected(request('modul') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>@endforeach</select>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari aktivitas atau username" aria-label="Cari">
        <button class="btn btn-outline-primary" type="submit">Cari</button>
    </form>
    <section class="pk-panel">
        <div class="table-responsive"><table class="table pk-table">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Modul</th><th>Aktivitas</th><th>Alamat IP</th></tr></thead>
            <tbody>
            @forelse ($aktivitas as $a)
                <tr>
                    <td class="text-nowrap">{{ $a->created_at->translatedFormat('d M Y H:i') }}</td>
                    <td>{{ $a->user?->name ?? '–' }}<span class="pk-sub">{{ $a->user?->peran_label }}</span></td>
                    <td><span class="pk-status pk-status--grey">{{ str_replace('_', ' ', $a->modul) }}</span></td>
                    <td>{{ $a->aktivitas }}@if ($a->deskripsi)<span class="pk-sub">{{ $a->deskripsi }}</span>@endif</td>
                    <td class="text-nowrap" title="{{ $a->user_agent }}">{{ $a->ip_address ?? '–' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-secondary">Belum ada aktivitas.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $aktivitas->links() }}</div>
    </section>
@endsection
