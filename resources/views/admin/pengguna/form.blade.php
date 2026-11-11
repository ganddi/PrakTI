@extends('layouts.app')
@section('judul', $pengguna->exists ? 'Ubah pengguna' : 'Tambah pengguna')

@section('content')
    @php $peran = old('role', $pengguna->role); $diri = $pengguna->exists && $pengguna->id === auth()->id(); @endphp
    <a href="{{ route('admin.pengguna.index', ['peran' => $pengguna->role]) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar pengguna</a>
    <section class="pk-panel" style="max-width: 760px">
        <form method="POST" action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" novalidate>
            @csrf @if ($pengguna->exists) @method('PUT') @endif
            <div class="mb-3">
                <label for="role" class="form-label">Peran</label>
                <select id="role" name="role" class="form-select" data-peran-select @disabled($pengguna->exists)>
                    @foreach (\App\Models\User::PERAN as $k => $l)<option value="{{ $k }}" @selected($peran === $k)>{{ $l }}</option>@endforeach
                </select>
                @if ($pengguna->exists)<div class="form-text">Peran tidak dapat diubah setelah akun dibuat.</div>@endif
            </div>
            <div data-hanya="admin" @if ($peran !== 'admin') hidden @endif><x-input name="username" label="Username" :value="$pengguna->username" :required="true" /></div>
            <div data-hanya="mahasiswa pembimbing" @if ($peran === 'admin') hidden @endif>
                <x-input name="nama_lengkap" label="Nama lengkap" :value="$profil?->nama_lengkap" :required="true" />
                <div class="mb-3"><label for="nomor_induk" class="form-label" data-label-nomor>NIM</label>
                    <input id="nomor_induk" name="nomor_induk" class="form-control" value="{{ old('nomor_induk', $pengguna->nomor_induk) }}"></div>
                <div class="row g-3">
                    <div class="col-sm-6"><x-input name="program_studi" label="Program studi" :value="$profil?->program_studi ?? config('prakti.program_studi')" :required="true" /></div>
                    <div class="col-sm-6" data-hanya="mahasiswa"><x-input name="fakultas" label="Fakultas" :value="$profil?->fakultas ?? config('prakti.fakultas')" :required="true" /></div>
                    <div class="col-sm-6" data-hanya="pembimbing"><x-input name="jabatan" label="Jabatan" :value="$profil?->jabatan" /></div>
                </div>
                <div data-hanya="mahasiswa"><x-input name="alamat" label="Alamat" type="textarea" rows="2" :value="$profil?->alamat" /></div>
            </div>
            <div class="row g-3">
                <div class="col-sm-6"><x-input name="email" label="Email" type="email" :value="$pengguna->email" :required="true" /></div>
                <div class="col-sm-6" data-hanya="mahasiswa pembimbing"><x-input name="no_hp" label="Nomor HP" type="tel" :value="$profil?->no_hp" /></div>
            </div>
            <div class="row g-3">
                <div class="col-sm-6"><x-input name="password" label="Kata sandi" type="password" :required="! $pengguna->exists" :hint="$pengguna->exists ? 'Kosongkan jika tidak diganti.' : 'Minimal 8 karakter.'" autocomplete="new-password" /></div>
                <div class="col-sm-6"><x-input name="password_confirmation" label="Ulangi kata sandi" type="password" :required="! $pengguna->exists" autocomplete="new-password" /></div>
            </div>
            <div class="mb-4" style="max-width: 260px">
                <label for="status" class="form-label">Status akun</label>
                <select id="status" name="status" class="form-select" @disabled($diri)>
                    @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'menunggu' => 'Menunggu aktivasi'] as $k => $l)<option value="{{ $k }}" @selected(old('status', $pengguna->status) === $k)>{{ $l }}</option>@endforeach
                </select>
                @if ($diri)<input type="hidden" name="status" value="aktif">@endif
            </div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.pengguna.index', ['peran' => $pengguna->role]) }}" class="btn btn-outline-secondary">Batal</a></div>
        </form>
    </section>
@endsection
