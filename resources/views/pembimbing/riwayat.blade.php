@extends('layouts.app')
@section('judul', 'Riwayat aktivitas mahasiswa')

@section('content')
    <section class="pk-panel">@include('shared.riwayat', ['aktivitas' => $aktivitas, 'namaMahasiswa' => $namaMahasiswa])<div class="mt-3">{{ $aktivitas->links() }}</div></section>
@endsection
