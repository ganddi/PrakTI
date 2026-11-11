@extends('layouts.app')
@section('judul', $pengajuan->exists ? 'Perbaiki pengajuan KPI' : 'Pengajuan KPI')
@section('subjudul', 'Ajukan KPI sesuai periode dan instansi yang ditetapkan')

@section('content')
    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke pengajuan</a>
    @if ($periode->isEmpty())
        <div class="alert alert-warning" style="max-width: 820px">Belum ada periode KPI yang dibuka. Hubungi admin program studi.</div>
    @else
        @if ($pengajuan->exists && $pengajuan->catatan_admin)<div class="alert alert-warning" style="max-width: 820px"><div class="fw-semibold mb-1">Catatan perbaikan dari admin</div>{{ $pengajuan->catatan_admin }}</div>@endif
        <div class="alert alert-secondary" style="max-width: 820px"><div class="fw-semibold mb-1">Siapkan dokumen berikut sebelum mengirim</div>
            <ul class="mb-0 ps-3">@foreach (config('prakti.persyaratan_pengajuan') as $s)<li>{{ $s }}</li>@endforeach</ul></div>

        @php $pilihan = old('tempat_kpi_id', $pengajuan->tempatKpi?->status === 'aktif' ? $pengajuan->tempat_kpi_id : ''); $usulan = $pengajuan->tempatKpi?->status === 'usulan' ? $pengajuan->tempatKpi : null; @endphp
        <section class="pk-panel" style="max-width: 820px">
            <form method="POST" enctype="multipart/form-data" novalidate action="{{ $pengajuan->exists ? route('mahasiswa.pengajuan.update', $pengajuan) : route('mahasiswa.pengajuan.store') }}"
                  data-confirm="{{ $pengajuan->exists ? 'Kirim ulang pengajuan yang sudah diperbaiki?' : 'Kirim pengajuan KPI sekarang?' }}">
                @csrf @if ($pengajuan->exists) @method('PUT') @endif
                <div class="pk-form-section">
                    <h2>Periode KPI</h2>
                    <div class="mb-3"><label for="periode_id" class="form-label">Periode</label>
                        <select id="periode_id" name="periode_id" class="form-select @error('periode_id') is-invalid @enderror" required>
                            @foreach ($periode as $pr)<option value="{{ $pr->id }}" @selected((string) old('periode_id', $pengajuan->periode_id) === (string) $pr->id)>{{ $pr->nama_periode }} ({{ $pr->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pr->tanggal_selesai->translatedFormat('d M Y') }})</option>@endforeach
                        </select></div>
                </div>
                <div class="pk-form-section">
                    <h2>Instansi tempat KPI</h2>
                    <p>Pilih dari daftar tempat KPI program studi, atau usulkan instansi lain.</p>
                    <div class="mb-3"><label for="tempat_kpi_id" class="form-label">Tempat KPI</label>
                        <select id="tempat_kpi_id" name="tempat_kpi_id" class="form-select" data-tempat-select>
                            @foreach ($tempat as $t)<option value="{{ $t->id }}" @selected((string) $pilihan === (string) $t->id)>{{ $t->nama_instansi }}, {{ $t->kota }}</option>@endforeach
                            <option value="" @selected($pilihan === '' || $pilihan === null)>Instansi lain (usulkan baru)</option>
                        </select></div>
                    <div data-tempat-baru>
                        <div class="row g-3">
                            <div class="col-md-8"><x-input name="nama_instansi" label="Nama instansi / perusahaan" :value="$usulan?->nama_instansi" :required="true" /></div>
                            <div class="col-md-4"><x-input name="jenis_instansi" label="Jenis instansi" :value="$usulan?->jenis_instansi" :required="true" list="jenis-instansi" /></div>
                        </div>
                        <datalist id="jenis-instansi">@foreach (config('prakti.jenis_instansi') as $j)<option value="{{ $j }}">@endforeach</datalist>
                        <x-input name="alamat" label="Alamat lengkap instansi" type="textarea" rows="2" :value="$usulan?->alamat" :required="true" />
                        <div class="row g-3">
                            <div class="col-md-6"><x-input name="kota" label="Kota/kabupaten" :value="$usulan?->kota" :required="true" /></div>
                            <div class="col-md-6"><x-input name="provinsi" label="Provinsi" :value="$usulan?->provinsi ?? 'Sulawesi Barat'" :required="true" /></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4"><x-input name="nama_pic" label="Nama penanggung jawab" :value="$usulan?->nama_pic" /></div>
                            <div class="col-md-4"><x-input name="no_hp_pic" label="Nomor telepon" :value="$usulan?->no_hp_pic" /></div>
                            <div class="col-md-4"><x-input name="email_pic" label="Email" type="email" :value="$usulan?->email_pic" /></div>
                        </div>
                    </div>
                </div>
                <div class="pk-form-section">
                    <h2>Keperluan</h2>
                    <x-input name="keperluan" label="Tujuan atau rencana kegiatan KPI" type="textarea" rows="3" :value="$pengajuan->keperluan" :required="true" hint="Minimal 20 karakter." />
                </div>
                <div class="pk-form-section">
                    <h2>Dokumen persyaratan</h2>
                    <p>{{ $pengajuan->exists ? 'Unggah berkas baru hanya jika diminta admin. Berkas lama tetap tersimpan.' : 'Ukuran setiap berkas maksimal 5 MB.' }}</p>
                    <x-input name="persyaratan" label="Dokumen persyaratan (PDF)" type="file" accept=".pdf" :required="! $pengajuan->exists" />
                    <x-input name="proposal" label="Proposal KPI (PDF atau Word)" type="file" accept=".pdf,.doc,.docx" />
                </div>
                <div class="d-flex gap-2 pt-2"><button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>{{ $pengajuan->exists ? 'Kirim ulang pengajuan' : 'Kirim pengajuan' }}</button>
                    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-secondary">Batal</a></div>
            </form>
        </section>
    @endif
@endsection
