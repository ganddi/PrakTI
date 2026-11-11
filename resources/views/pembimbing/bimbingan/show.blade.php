@extends('layouts.app')
@section('judul', $pengajuan->mahasiswa->nama_lengkap)
@section('subjudul', 'Detail bimbingan KPI')

@section('content')
    <a href="{{ route('pembimbing.bimbingan.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar mahasiswa</a>
    @include('shared.detail-kpi', ['pengajuan' => $pengajuan, 'mode' => 'pembimbing'])
@endsection
