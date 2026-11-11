@extends('layouts.app')
@section('judul', $tempat->exists ? 'Ubah tempat KPI' : 'Tambah tempat KPI')

@section('content')
    <a href="{{ route('admin.tempat.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar tempat KPI</a>
    <section class="pk-panel" style="max-width: 780px">
        <form method="POST" action="{{ $tempat->exists ? route('admin.tempat.update', $tempat) : route('admin.tempat.store') }}">
            @csrf @if ($tempat->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8"><x-input name="nama_instansi" label="Nama instansi" :value="$tempat->nama_instansi" :required="true" /></div>
                <div class="col-md-4"><x-input name="jenis_instansi" label="Jenis instansi" :value="$tempat->jenis_instansi" :required="true" list="jenis-instansi" /></div>
            </div>
            <datalist id="jenis-instansi">@foreach (config('prakti.jenis_instansi') as $j)<option value="{{ $j }}">@endforeach</datalist>
            <x-input name="alamat" label="Alamat" type="textarea" rows="2" :value="$tempat->alamat" :required="true" />
            <div class="row g-3">
                <div class="col-md-6"><x-input name="kota" label="Kota/kabupaten" :value="$tempat->kota" :required="true" /></div>
                <div class="col-md-6"><x-input name="provinsi" label="Provinsi" :value="$tempat->provinsi" :required="true" /></div>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><x-input name="nama_pic" label="Nama PIC" :value="$tempat->nama_pic" /></div>
                <div class="col-md-4"><x-input name="email_pic" label="Email PIC" type="email" :value="$tempat->email_pic" /></div>
                <div class="col-md-4"><x-input name="no_hp_pic" label="Nomor HP PIC" :value="$tempat->no_hp_pic" /></div>
            </div>
            <x-input name="deskripsi" label="Deskripsi" type="textarea" rows="3" :value="$tempat->deskripsi" />
            <div class="mb-4" style="max-width: 280px"><label for="status" class="form-label">Status</label><select id="status" name="status" class="form-select">
                @foreach (['aktif' => 'Aktif (tampil di landing page)', 'usulan' => 'Usulan mahasiswa', 'nonaktif' => 'Nonaktif'] as $k => $l)<option value="{{ $k }}" @selected(old('status', $tempat->status) === $k)>{{ $l }}</option>@endforeach</select></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.tempat.index') }}" class="btn btn-outline-secondary">Batal</a></div>
        </form>
    </section>
@endsection
