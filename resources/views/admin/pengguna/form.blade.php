@extends('layouts.app')
@section('judul', $pengguna->exists ? 'Ubah pengguna' : 'Tambah pengguna')

@section('content')
    <a href="{{ route('admin.pengguna.index', ['peran' => $pengguna->role]) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar pengguna</a>

    <section class="pk-panel" style="max-width: 720px">
        <form method="POST" action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" novalidate>
            @csrf
            @if ($pengguna->exists) @method('PUT') @endif

            @php $akunSendiri = $pengguna->exists && $pengguna->id === auth()->id(); @endphp

            <div class="mb-3">
                <label for="role" class="form-label">Peran</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" data-peran-select @disabled($akunSendiri)>
                    @foreach (\App\Models\User::PERAN as $kunci => $label)
                        <option value="{{ $kunci }}" @selected(old('role', $pengguna->role) === $kunci)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($akunSendiri)
                    <input type="hidden" name="role" value="admin">
                    <div class="form-text">Anda tidak dapat mengubah peran akun sendiri.</div>
                @endif
            </div>

            <x-input name="name" label="Nama lengkap" :value="$pengguna->name" :required="true" />

            <div class="mb-3">
                <label for="nomor_induk" class="form-label" data-label-nomor>NIM</label>
                <input id="nomor_induk" name="nomor_induk" type="text" value="{{ old('nomor_induk', $pengguna->nomor_induk) }}"
                       class="form-control @error('nomor_induk') is-invalid @enderror">
            </div>

            <div class="row g-3">
                <div class="col-sm-6"><x-input name="email" label="Email" type="email" :value="$pengguna->email" :required="true" /></div>
                <div class="col-sm-6"><x-input name="no_hp" label="Nomor HP" type="tel" :value="$pengguna->no_hp" /></div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <x-input name="password" label="Kata sandi" type="password" :required="! $pengguna->exists"
                             :hint="$pengguna->exists ? 'Kosongkan jika tidak ingin mengganti kata sandi.' : 'Minimal 8 karakter.'" autocomplete="new-password" />
                </div>
                <div class="col-sm-6">
                    <x-input name="password_confirmation" label="Ulangi kata sandi" type="password" :required="! $pengguna->exists" autocomplete="new-password" />
                </div>
            </div>

            <div class="form-check mb-4">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                       @checked(old('is_active', $pengguna->is_active)) @disabled($akunSendiri)>
                <label class="form-check-label" for="is_active">Akun aktif dan dapat digunakan untuk masuk</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $pengguna->exists ? 'Simpan perubahan' : 'Tambah pengguna' }}</button>
                <a href="{{ route('admin.pengguna.index', ['peran' => $pengguna->role]) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
