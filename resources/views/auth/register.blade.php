@extends('layouts.guest')
@section('judul', 'Daftar akun')

@section('content')
    <h1>Daftar akun</h1>
    <p class="text-secondary mb-4">Akun dosen pembimbing aktif setelah disetujui admin program studi.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="role" class="form-label">Saya mendaftar sebagai</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" data-peran-select required>
                <option value="mahasiswa" @selected(old('role', 'mahasiswa') === 'mahasiswa')>Mahasiswa</option>
                <option value="pembimbing" @selected(old('role') === 'pembimbing')>Dosen pembimbing</option>
            </select>
        </div>

        <x-input name="name" label="Nama lengkap" :required="true" autocomplete="name" />

        <div class="mb-3">
            <label for="nomor_induk" class="form-label" data-label-nomor>NIM</label>
            <input id="nomor_induk" name="nomor_induk" type="text" value="{{ old('nomor_induk') }}" required
                   class="form-control @error('nomor_induk') is-invalid @enderror">
        </div>

        <div class="row g-3">
            <div class="col-sm-6"><x-input name="email" label="Email" type="email" :required="true" autocomplete="email" /></div>
            <div class="col-sm-6"><x-input name="no_hp" label="Nomor HP" type="tel" autocomplete="tel" /></div>
        </div>
        <div class="row g-3">
            <div class="col-sm-6">
                <x-input name="password" label="Kata sandi" type="password" :required="true" autocomplete="new-password" hint="Minimal 8 karakter." />
            </div>
            <div class="col-sm-6">
                <x-input name="password_confirmation" label="Ulangi kata sandi" type="password" :required="true" autocomplete="new-password" />
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Buat akun</button>
    </form>

    <p class="text-center text-secondary mt-4 mb-0">
        Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold">Masuk</a>
    </p>
@endsection
