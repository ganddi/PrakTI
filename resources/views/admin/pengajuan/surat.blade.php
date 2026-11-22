@extends('layouts.app')
@section('judul', 'Edit surat permohonan KPI')
@section('subjudul', 'Perbarui nomor, penerima, periode, penandatangan, dan anggota yang tercantum pada surat')

@section('content')
    <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke detail pengajuan</a>
    <section class="pk-panel pk-letter-panel" style="max-width: 900px">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div><h2 class="pk-panel-title mb-1">Nomor surat saat ini</h2><p class="mb-0 fw-semibold">{{ $surat->nomor_surat }}</p></div>
            <div class="d-flex gap-2"><a href="{{ route('admin.pengajuan.surat.lihat', $pengajuan) }}" class="btn btn-outline-secondary"><i class="bi bi-eye me-1"></i>Lihat pratinjau</a><a href="{{ route('admin.pengajuan.surat.unduh', $pengajuan) }}" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Unduh DOCX</a></div>
        </div>
        <form method="POST" action="{{ route('admin.pengajuan.surat.update', $pengajuan) }}">
            @csrf @method('PUT')
            <h3 class="pk-subtitle">Nomor dan tujuan</h3>
            @error('nomor_surat')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            <div class="row g-3">
                <div class="col-md-3"><x-input name="nomor_urut" label="Nomor urut" type="number" :value="$surat->nomor_urut" :required="true" min="1" max="999999" /></div>
                <div class="col-md-6"><x-input name="kode_nomor" label="Kode surat" :value="$surat->kode_nomor" :required="true" hint="Contoh: UN55.F4/KM.22" /></div>
                <div class="col-md-3"><x-input name="tahun_nomor" label="Tahun nomor" type="number" :value="$surat->tahun_nomor" :required="true" min="2000" max="2200" /></div>
                <div class="col-md-6"><x-input name="penerima" label="Penerima surat" :value="$surat->penerima" :required="true" /></div>
                <div class="col-md-6"><x-input name="nama_instansi" label="Nama instansi" :value="$surat->nama_instansi" :required="true" /></div>
                <div class="col-md-6"><x-input name="kota_instansi" label="Kota/kabupaten instansi" :value="$surat->kota_instansi" :required="true" /></div>
            </div>

            <h3 class="pk-subtitle mt-3">Tanggal dan penandatangan</h3>
            <div class="row g-3">
                <div class="col-md-4"><x-input name="tanggal_mulai" label="Tanggal mulai KPI" type="date" :value="$surat->tanggal_mulai->format('Y-m-d')" :required="true" /></div>
                <div class="col-md-4"><x-input name="tanggal_selesai" label="Tanggal selesai KPI" type="date" :value="$surat->tanggal_selesai->format('Y-m-d')" :required="true" /></div>
                <div class="col-md-4"><x-input name="tanggal_surat" label="Tanggal surat" type="date" :value="$surat->tanggal_surat->format('Y-m-d')" :required="true" /></div>
                <div class="col-md-6"><x-input name="kota_surat" label="Kota pada surat" :value="$surat->kota_surat" :required="true" /></div>
                <div class="col-md-6"><x-input name="nama_penandatangan" label="Nama wakil dekan" :value="$surat->nama_penandatangan" :required="true" /></div>
                <div class="col-md-6"><x-input name="nip_penandatangan" label="NIP" :value="$surat->nip_penandatangan" :required="true" /></div>
                <div class="col-md-6"><x-input name="pangkat_golongan" label="Pangkat/golongan" :value="$surat->pangkat_golongan" :required="true" /></div>
                <div class="col-md-6"><x-input name="jabatan_penandatangan" label="Jabatan penandatangan" :value="$surat->jabatan_penandatangan" :required="true" /></div>
            </div>

            <h3 class="pk-subtitle mt-3">Anggota yang tercantum</h3>
            @include('partials.surat-anggota-fields', ['pengajuan' => $pengajuan, 'surat' => $surat, 'kunciAnggotaPertama' => false])
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Simpan perubahan</button>
                <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
