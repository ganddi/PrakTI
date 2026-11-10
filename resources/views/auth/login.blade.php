@extends('layouts.guest')
@section('judul', 'Masuk')

@section('content')
    <h1>Masuk ke PrakTI</h1>
    <p class="text-secondary mb-4">Gunakan email yang terdaftar di program studi.</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <x-input name="email" label="Email" type="email" :required="true" autocomplete="email" autofocus />
        <x-input name="password" label="Kata sandi" type="password" :required="true" autocomplete="current-password" />

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember">Tetap masuk di perangkat ini</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
    </form>

    <p class="text-center text-secondary mt-4 mb-0">
        Belum punya akun? <a href="{{ route('register') }}" class="fw-semibold">Daftar di sini</a>
    </p>
@endsection
