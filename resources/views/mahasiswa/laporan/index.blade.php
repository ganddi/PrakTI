@extends('layouts.app')
@section('judul', 'Laporan KPI')
@section('subjudul', 'Unggah laporan dan lihat catatan revisi dosen pembimbing')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i><h3>Laporan belum bisa diunggah</h3><p>Laporan dapat diunggah setelah KPI berjalan.</p></div></section>
    @else
        @php $akhir = $pengajuan->laporanTerakhir; $bisa = $pengajuan->status === 'berjalan' && ! in_array($akhir?->status, ['disetujui', 'menunggu'], true); @endphp
        <div class="row g-4 mb-2">
            <div class="col-lg-7">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Status laporan</h2>
                    @if (! $akhir)<p class="mb-0">Belum ada laporan. Gunakan template laporan program studi.</p>
                    @elseif ($akhir->status === 'revisi')
                        <div class="alert alert-danger mb-0"><div class="fw-semibold">Versi {{ $akhir->versi }} perlu direvisi</div>
                            {{ $akhir->tanda_terbuka }} bagian ditandai <mark>kuning</mark> oleh {{ $pengajuan->pembimbing?->nama_lengkap ?? 'dosen pembimbing' }}. Perbaiki lalu unggah versi baru.</div>
                    @elseif ($akhir->status === 'menunggu')<div class="alert alert-info mb-0">Versi {{ $akhir->versi }} sedang diperiksa dosen pembimbing.</div>
                    @else<div class="alert alert-success mb-0">Versi {{ $akhir->versi }} disetujui. Lanjutkan ke <a href="{{ route('mahasiswa.penilaian.index') }}" class="alert-link">Penilaian KPI</a>.</div>@endif
                    @if ($template->isNotEmpty())
                        <h3 class="pk-subtitle mt-4">Template laporan</h3>
                        @foreach ($template as $t)<x-berkas :href="route('template.unduh', $t->kode)" :nama="$t->nama" :ukuran="$t->ukuran" class="mb-2" />@endforeach
                    @endif
                </section>
            </div>
            <div class="col-lg-5">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">{{ $akhir ? 'Unggah versi perbaikan' : 'Unggah laporan' }}</h2>
                    @if ($bisa)
                        <form method="POST" action="{{ route('mahasiswa.laporan.store') }}" enctype="multipart/form-data">@csrf
                            <x-input name="laporan" label="Berkas laporan" type="file" accept=".pdf,.doc,.docx" :required="true" hint="Disarankan PDF agar dosen bisa membaca langsung di browser. Maksimal 20 MB." />
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-upload me-1"></i>Unggah versi {{ (int) ($akhir?->versi ?? 0) + 1 }}</button></form>
                    @else
                        <p class="text-secondary mb-0">{{ match ($akhir?->status) { 'disetujui' => 'Laporan sudah disetujui.', 'menunggu' => 'Tunggu hasil pemeriksaan versi terakhir.', default => 'KPI sudah tidak berjalan.' } }}</p>
                    @endif
                </section>
            </div>
        </div>
        <h2 class="pk-section-title mt-4">Riwayat versi dan bagian yang ditandai</h2>
        @include('shared.laporan', ['daftarLaporan' => $pengajuan->laporan, 'mode' => 'mahasiswa'])
    @endif
@endsection
