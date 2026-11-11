@extends('layouts.app')
@section('judul', 'Rekapitulasi nilai')
@section('subjudul', 'Nilai akhir KPI dan dokumen pengesahannya')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-award" aria-hidden="true"></i><h3>Nilai belum tersedia</h3></div></section>
    @else
        @php $na = $pengajuan->nilaiAkhir; $dok = $pengajuan->dokumenTerakhir('nilai_akhir'); @endphp
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="pk-panel h-100">
                    <div class="pk-panel-head"><h2 class="pk-panel-title">Rekap nilai</h2>
                        @if ($na)<span class="pk-final">{{ number_format($na->nilai_akhir, 2, ',', '.') }} <span class="pk-grade">{{ $na->predikat }}</span></span>@endif</div>
                    @include('shared.penilaian', ['pengajuan' => $pengajuan])
                    @if ($na)<p class="small text-secondary mt-2 mb-0">Dihitung otomatis pada {{ $na->tanggal_hitung->translatedFormat('d F Y') }} dengan bobot yang ditetapkan program studi. <x-status :value="$na->status" /></p>@endif
                </section>
            </div>
            <div class="col-lg-5">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-1">Dokumen nilai akhir</h2>
                    <p class="small text-secondary mb-3">Unduh dokumen, cetak, mintakan tanda tangan, lalu unggah kembali hasil pindainya.</p>
                    @if (! $na)<p class="text-secondary mb-0">Dokumen tersedia setelah ketiga nilai lengkap.</p>
                    @else
                        <a href="{{ route('ekspor.nilai-akhir', $pengajuan) }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-printer me-1"></i>Unduh / cetak dokumen nilai akhir</a>
                        @if ($dok)
                            <x-berkas :href="route('dokumen.unduh', $dok)" :nama="$dok->nama_file" :ukuran="$dok->ukuran_format" :lihat="true" class="mb-2" />
                            <p class="mb-2"><x-status :value="$dok->status_verifikasi" /></p>
                            @if ($dok->catatan)<div class="alert alert-danger py-2">{{ $dok->catatan }}</div>@endif
                        @endif
                        @if (! $dok || $dok->status_verifikasi === 'revisi')
                            <form method="POST" action="{{ route('mahasiswa.nilai.unggah') }}" enctype="multipart/form-data">@csrf
                                <x-input name="berkas" label="Dokumen bertanda tangan" type="file" accept=".pdf,.jpg,.jpeg,.png" :required="true" hint="PDF, JPG, atau PNG, maksimal 5 MB." />
                                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-upload me-1"></i>Unggah dokumen nilai akhir</button></form>
                        @elseif ($dok->status_verifikasi === 'diverifikasi')<div class="alert alert-success mb-0">Dokumen nilai akhir terverifikasi. Proses KPI Anda selesai.</div>@endif
                    @endif
                </section>
            </div>
        </div>
    @endif
@endsection
