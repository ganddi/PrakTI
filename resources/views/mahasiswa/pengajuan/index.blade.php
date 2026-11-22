@extends('layouts.app')
@section('judul', 'Pengajuan KPI')
@section('subjudul', 'Ajukan KPI sesuai periode dan instansi yang ditetapkan')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-send" aria-hidden="true"></i><h3>Belum ada pengajuan KPI</h3>
            <p>Pilih periode dan tempat KPI, lalu unggah dokumen persyaratan dalam format PDF.</p><a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary">Ajukan KPI</a></div></section>
    @else
        @php $surat = $pengajuan->dokumenTerakhir('surat_balasan'); @endphp
        <section class="pk-panel mb-4">
            <div class="pk-panel-head">
                <div><h2 class="pk-panel-title fs-5">{{ $pengajuan->nama_instansi }}</h2><span class="text-secondary">Diajukan {{ $pengajuan->tanggal_pengajuan->translatedFormat('d F Y') }}</span></div>
                <x-status :value="$pengajuan->status" />
            </div>
            @if ($pengajuan->status === 'diajukan')<div class="alert alert-info">Pengajuan sedang diperiksa admin program studi.</div>
            @elseif ($pengajuan->status === 'revisi')
                <div class="alert alert-danger"><div class="fw-semibold mb-1">Pengajuan perlu diperbaiki</div><p class="mb-2">{{ $pengajuan->catatan_admin }}</p>
                    <a href="{{ route('mahasiswa.pengajuan.edit', $pengajuan) }}" class="btn btn-sm btn-danger">Perbaiki dan kirim ulang</a></div>
            @elseif ($pengajuan->catatan_admin)<div class="alert alert-secondary"><span class="fw-semibold">Catatan admin:</span> {{ $pengajuan->catatan_admin }}</div>@endif
            <dl class="pk-info">
                <div><dt>Periode</dt><dd>{{ $pengajuan->periode->nama_periode }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</span></dd></div>
                <div><dt>Jenis instansi</dt><dd>{{ $pengajuan->bidang_kerja }}</dd></div>
                <div><dt>Alamat instansi</dt><dd class="fw-normal">{{ $pengajuan->alamat_instansi }}</dd></div>
                <div><dt>Kontak instansi</dt><dd>{{ $pengajuan->pembimbing_lapangan ?: '–' }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->kontak_instansi }}</span></dd></div>
            </dl>
        </section>

        @if (in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']))
            @php $suratPermohonan = $pengajuan->suratPermohonan; @endphp
            <section class="pk-panel pk-letter-panel mb-4" id="surat">
                <h2 class="pk-panel-title mb-1">Surat KPI</h2>
                <p class="small text-secondary mb-3">Isi data surat permohonan untuk mengunduh surat siap cetak, kirimkan ke instansi, lalu unggah surat balasan yang Anda terima.</p>
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h3 class="pk-subtitle">1. Siapkan surat permohonan</h3>
                        @if ($suratPermohonan)
                            <div class="alert alert-success py-2 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <span>Nomor surat Anda: <strong>{{ $suratPermohonan->nomor_surat }}</strong></span>
                                <a href="{{ route('mahasiswa.pengajuan.surat-permohonan.lihat', $pengajuan) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye me-1" aria-hidden="true"></i>Lihat overview surat</a>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('mahasiswa.pengajuan.surat-permohonan', $pengajuan) }}" class="pk-letter-form">
                            @csrf
                            <x-input name="penerima" label="Penerima surat" :value="$suratPermohonan?->penerima ?? 'Pimpinan'" :required="true" hint="Contoh: Kepala Dinas atau Pimpinan perusahaan." />
                            <div class="row g-3">
                                <div class="col-md-7"><x-input name="nama_instansi" label="Nama instansi" :value="$suratPermohonan?->nama_instansi ?? $pengajuan->nama_instansi" :required="true" /></div>
                                <div class="col-md-5"><x-input name="kota_instansi" label="Kota/kabupaten instansi" :value="$suratPermohonan?->kota_instansi ?? $pengajuan->tempatKpi->kota" :required="true" /></div>
                            </div>
                            <p class="small text-secondary mb-3">Periode KPI: {{ ($suratPermohonan?->tanggal_mulai ?? $pengajuan->tanggal_mulai)->translatedFormat('d F Y') }} – {{ ($suratPermohonan?->tanggal_selesai ?? $pengajuan->tanggal_selesai)->translatedFormat('d F Y') }}. Admin dapat menyesuaikan tanggal dan data surat bila diperlukan.</p>
                            @include('partials.surat-anggota-fields', ['pengajuan' => $pengajuan, 'surat' => $suratPermohonan, 'kunciAnggotaPertama' => true])
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="submit" name="aksi" value="unduh" class="btn btn-primary"><i class="bi bi-file-earmark-word me-1" aria-hidden="true"></i>{{ $suratPermohonan ? 'Simpan dan unduh DOCX' : 'Buat dan unduh DOCX' }}</button>
                                <button type="submit" name="aksi" value="pratinjau" formtarget="_blank" class="btn btn-outline-primary"><i class="bi bi-eye me-1" aria-hidden="true"></i>Pratinjau surat</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <h3 class="pk-subtitle">2. Surat balasan dari instansi</h3>
                        @if ($surat)
                            <x-berkas :href="route('dokumen.unduh', $surat)" :nama="$surat->nama_file" :ukuran="$surat->ukuran_format" :lihat="true" class="mb-2" />
                            <p class="mb-2"><x-status :value="$surat->status_verifikasi" /> <span class="small text-secondary ms-1">diunggah {{ $surat->uploaded_at->translatedFormat('d M Y H:i') }}</span></p>
                            @if ($surat->catatan)<div class="alert {{ $surat->status_verifikasi === 'revisi' ? 'alert-danger' : 'alert-secondary' }} py-2">{{ $surat->catatan }}</div>@endif
                        @endif
                        @if ($pengajuan->status === 'disetujui' && (! $surat || $surat->status_verifikasi === 'revisi'))
                            <form method="POST" action="{{ route('mahasiswa.pengajuan.surat-balasan', $pengajuan) }}" enctype="multipart/form-data">@csrf
                                <x-input name="surat_balasan" :label="$surat ? 'Unggah ulang surat balasan' : 'Berkas surat balasan'" type="file" accept=".pdf,.jpg,.jpeg,.png" :required="true" hint="PDF, JPG, atau PNG, maksimal 5 MB." />
                                <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1" aria-hidden="true"></i>Unggah surat balasan</button></form>
                        @elseif ($surat?->status_verifikasi === 'diverifikasi' && ! $pengajuan->kelompok)
                            <p class="small text-secondary mb-0">Surat terverifikasi. Tunggu admin menempatkan Anda di kelompok KPI.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <section class="mb-4">
            <div class="pk-panel-head"><h2 class="pk-panel-title">Semua dokumen</h2></div>
            @include('shared.dokumen', ['dokumen' => $pengajuan->dokumen, 'pengajuan' => $pengajuan])
            @if ($pengajuan->status !== 'selesai')
                <details class="pk-panel mt-3"><summary class="fw-semibold">Unggah dokumen pendukung lainnya</summary>
                    <form method="POST" action="{{ route('mahasiswa.pengajuan.dokumen', $pengajuan) }}" enctype="multipart/form-data" class="mt-2">@csrf
                        <x-input name="berkas" label="Berkas" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" :required="true" hint="Maksimal 5 MB." />
                        <button class="btn btn-outline-primary" type="submit">Unggah</button></form>
                </details>
            @endif
        </section>
    @endif
@endsection
