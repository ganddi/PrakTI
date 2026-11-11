@extends('layouts.app')
@section('judul', 'Kelola pengguna')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <ul class="nav pk-filter">
            @foreach (\App\Models\User::PERAN as $k => $l)
                <li class="nav-item"><a class="nav-link {{ $peran === $k ? 'active' : '' }}" href="{{ route('admin.pengguna.index', ['peran' => $k]) }}">{{ $l }}<span class="pk-count">{{ $jumlah[$k] ?? 0 }}</span></a></li>
            @endforeach
        </ul>
        <a href="{{ route('admin.pengguna.create', ['peran' => $peran]) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah pengguna</a>
    </div>
    <section class="pk-panel">
        <form method="GET" class="pk-filterbar mb-3" role="search">
            <input type="hidden" name="peran" value="{{ $peran }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, username, email, NIM/NIDN" aria-label="Cari pengguna">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </form>
        @if ($pengguna->isEmpty())
            <div class="pk-empty"><i class="bi bi-people" aria-hidden="true"></i><h3>Tidak ada pengguna yang cocok</h3></div>
        @else
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Nama</th><th>{{ ['mahasiswa' => 'NIM', 'pembimbing' => 'NIDN', 'admin' => 'Username'][$peran] }}</th><th>Kontak</th><th>Login terakhir</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @foreach ($pengguna as $u)
                    <tr>
                        <td class="fw-semibold">{{ $u->name }}<span class="pk-sub">{{ $u->profil()?->program_studi ?? $u->peran_label }}</span></td>
                        <td>{{ $u->nomor_induk ?? $u->username }}</td>
                        <td>{{ $u->email }}<span class="pk-sub">{{ $u->no_hp }}</span></td>
                        <td class="text-nowrap">{{ $u->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</td>
                        <td><x-status :value="$u->status === 'menunggu' ? 'diajukan' : $u->status" /></td>
                        <td class="text-end text-nowrap">
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.pengguna.aktivasi', $u) }}" class="d-inline">@csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $u->status === 'aktif' ? 'btn-outline-secondary' : 'btn-primary' }}" type="submit">{{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            @endif
                            <a href="{{ route('admin.pengguna.edit', $u) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.pengguna.destroy', $u) }}" class="d-inline" data-confirm="Hapus akun {{ $u->name }}? Seluruh data KPI miliknya ikut terhapus.">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus {{ $u->name }}"><i class="bi bi-trash"></i></button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="mt-3">{{ $pengguna->links() }}</div>
        @endif
    </section>
@endsection
