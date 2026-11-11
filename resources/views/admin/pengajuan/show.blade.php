@extends('layouts.app')
@section('judul', 'Detail pengajuan KPI')

@section('content')
    <a href="{{ route('admin.pengajuan.index', ['status' => $pengajuan->status]) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar pengajuan</a>
    @if ($pengajuan->status === 'diajukan')
        <section class="pk-panel mb-4">
            <h2 class="pk-panel-title mb-1">Verifikasi pengajuan</h2>
            <p class="pk-lead mb-3">Periksa data dan dokumen persyaratan di tab Dokumen, lalu tentukan hasil verifikasi.
                @if ($pengajuan->tempatKpi->status === 'usulan') Instansi ini usulan baru dari mahasiswa; aktifkan di menu <a href="{{ route('admin.tempat.edit', $pengajuan->tempatKpi) }}">Tempat KPI</a> jika ingin ditampilkan di landing page.@endif</p>
            <form method="POST" action="{{ route('admin.pengajuan.verifikasi', $pengajuan) }}" data-verifikasi data-confirm="Kirim hasil verifikasi untuk {{ $pengajuan->mahasiswa->nama_lengkap }}?">
                @csrf @method('PATCH')
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="pk-choice"><input class="form-check-input" type="radio" name="keputusan" value="setujui" @checked(old('keputusan') === 'setujui')>
                        <span class="fw-semibold">Setujui pengajuan<small>Mahasiswa dapat mengunduh template surat permohonan.</small></span></label></div>
                    <div class="col-md-6"><label class="pk-choice"><input class="form-check-input" type="radio" name="keputusan" value="revisi" @checked(old('keputusan') === 'revisi')>
                        <span class="fw-semibold">Minta perbaikan<small>Mahasiswa memperbaiki lalu mengirim ulang.</small></span></label></div>
                </div>
                <div class="mb-3"><label for="catatan_admin" class="form-label" data-label-catatan>Catatan untuk mahasiswa (opsional)</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3" class="form-control">{{ old('catatan_admin') }}</textarea></div>
                <button type="submit" class="btn btn-primary" disabled>Pilih hasil verifikasi</button>
            </form>
        </section>
    @endif
    @include('shared.detail-kpi', ['pengajuan' => $pengajuan, 'mode' => 'admin'])
@endsection
