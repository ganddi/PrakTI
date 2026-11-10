@extends('layouts.app')
@section('judul', 'Kelola pengguna')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <ul class="nav pk-filter">
            @foreach (\App\Models\User::PERAN as $kunci => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $peran === $kunci ? 'active' : '' }}" href="{{ route('admin.pengguna.index', ['peran' => $kunci]) }}">
                        {{ $label }}<span class="pk-count">{{ $jumlahPerPeran[$kunci] ?? 0 }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('admin.pengguna.create', ['peran' => $peran]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah pengguna
        </a>
    </div>

    <section class="pk-panel">
        <form method="GET" class="row g-2 mb-3" role="search">
            <input type="hidden" name="peran" value="{{ $peran }}">
            <div class="col-sm-8 col-md-6">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, email, atau NIM/NIDN" aria-label="Cari pengguna">
            </div>
            <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Cari</button></div>
        </form>

        @if ($pengguna->isEmpty())
            <div class="pk-empty">
                <i class="bi bi-people" aria-hidden="true"></i>
                <h3>Tidak ada pengguna yang cocok</h3>
                <p>Ubah kata kunci pencarian atau tambahkan pengguna baru.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>{{ $peran === 'pembimbing' ? 'NIDN / NIP' : ($peran === 'mahasiswa' ? 'NIM' : 'Nomor induk') }}</th>
                        <th>Nomor HP</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($pengguna as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}<span class="pk-sub">{{ $u->email }}</span></td>
                            <td>{{ $u->nomor_induk ?? '–' }}</td>
                            <td>{{ $u->no_hp ?? '–' }}</td>
                            <td><x-status :value="$u->is_active ? 'aktif' : 'nonaktif'" /></td>
                            <td class="text-end text-nowrap">
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.pengguna.aktivasi', $u) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $u->is_active ? 'btn-outline-secondary' : 'btn-primary' }}" type="submit">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.pengguna.edit', $u) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.pengguna.destroy', $u) }}" class="d-inline"
                                          data-confirm="Hapus akun {{ $u->name }}? Semua data KPI milik akun ini ikut terhapus.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus {{ $u->name }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $pengguna->links() }}</div>
        @endif
    </section>
@endsection
