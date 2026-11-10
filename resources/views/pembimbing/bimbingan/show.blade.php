@extends('layouts.app')
@section('judul', 'Bimbingan: '.$pengajuan->mahasiswa->name)

@section('content')
    <a href="{{ route('pembimbing.bimbingan.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke mahasiswa bimbingan</a>

    @include('shared.detail-kpi', ['pengajuan' => $pengajuan, 'mode' => 'pembimbing'])
@endsection
