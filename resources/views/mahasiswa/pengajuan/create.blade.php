@extends('layouts.app')
@section('judul', 'Ajukan KPI')

@section('content')
    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke pengajuan</a>

    @if ($sebelumnya)
        <div class="alert alert-warning">
            <div class="fw-semibold mb-1">Catatan admin pada pengajuan sebelumnya</div>
            <p class="mb-1">{{ $sebelumnya->catatan_admin }}</p>
            <small>Isian dari pengajuan sebelumnya sudah dimasukkan ke form. Perbaiki sesuai catatan sebelum mengirim.</small>
        </div>
    @endif

    <section class="pk-panel" style="max-width: 820px">
        <form method="POST" action="{{ route('mahasiswa.pengajuan.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="pk-form-section">
                <h2>Instansi tempat KPI</h2>
                <p>Data ini dipakai admin untuk memverifikasi dan dicantumkan pada surat pengantar.</p>
                <x-input name="nama_instansi" label="Nama instansi / perusahaan" :value="$sebelumnya?->nama_instansi" :required="true" />
                <x-input name="alamat_instansi" label="Alamat instansi" type="textarea" rows="2" :value="$sebelumnya?->alamat_instansi" :required="true" />
                <x-input name="bidang_kerja" label="Bidang / posisi kerja" :value="$sebelumnya?->bidang_kerja" :required="true"
                         hint="Contoh: Pengembangan aplikasi web, administrasi jaringan, analisis data." />
                <div class="row g-3">
                    <div class="col-md-6"><x-input name="pembimbing_lapangan" label="Nama pembimbing lapangan" :value="$sebelumnya?->pembimbing_lapangan" /></div>
                    <div class="col-md-6"><x-input name="kontak_instansi" label="Telepon / email instansi" :value="$sebelumnya?->kontak_instansi" /></div>
                </div>
            </div>

            <div class="pk-form-section">
                <h2>Periode dan rencana kegiatan</h2>
                <p>Sesuaikan dengan surat balasan atau kesepakatan dengan instansi.</p>
                <div class="row g-3">
                    <div class="col-md-6"><x-input name="tanggal_mulai" label="Tanggal mulai" type="date" :required="true" /></div>
                    <div class="col-md-6"><x-input name="tanggal_selesai" label="Tanggal selesai" type="date" :required="true" /></div>
                </div>
                <x-input name="deskripsi" label="Rencana kegiatan" type="textarea" rows="3" :value="$sebelumnya?->deskripsi"
                         hint="Gambarkan singkat pekerjaan yang akan Anda lakukan selama KPI." />
            </div>

            <div class="pk-form-section">
                <h2>Dokumen persyaratan</h2>
                <p>Ukuran setiap berkas maksimal 5 MB.</p>
                <x-input name="surat_permohonan" label="Surat permohonan KPI (PDF)" type="file" accept=".pdf" :required="true" />
                <x-input name="proposal" label="Proposal KPI (PDF atau Word)" type="file" accept=".pdf,.doc,.docx" />
            </div>

            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary">Kirim pengajuan</button>
                <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
