@extends('layouts.app')
@section('judul', 'Laporan '.$pengajuan->mahasiswa->nama_lengkap.', versi '.$laporan->versi)

@section('content')
    @php $terbaru = $semuaVersi->first()?->id === $laporan->id; @endphp
    <a href="{{ route('pembimbing.bimbingan.show', $pengajuan) }}#tab-laporan" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke {{ $pengajuan->mahasiswa->nama_lengkap }}</a>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <x-status :value="$laporan->status" /><span class="small text-secondary">Diunggah {{ $laporan->tanggal_upload->translatedFormat('d M Y H:i') }}</span>
        @if ($semuaVersi->count() > 1)
            <div class="ms-auto d-flex align-items-center gap-2"><label for="pilih-versi" class="small text-secondary">Versi</label>
                <select id="pilih-versi" class="form-select form-select-sm" onchange="window.location = this.value">@foreach ($semuaVersi as $v)<option value="{{ route('pembimbing.laporan.show', $v) }}" @selected($v->id === $laporan->id)>Versi {{ $v->versi }} ({{ $v->status }})</option>@endforeach</select></div>
        @endif
    </div>
    <div class="row g-4">
        <div class="col-xl-7">
            <section class="pk-panel pk-reader">
                <x-berkas :href="route('laporan.berkas', $laporan)" :nama="$laporan->nama_file" :lihat="true" class="mb-3" />
                @if ($laporan->is_pdf)
                    <iframe src="{{ route('laporan.berkas', $laporan) }}?lihat=1#view=FitH" title="Laporan versi {{ $laporan->versi }}" class="pk-reader-frame"></iframe>
                    <p class="small text-secondary mt-2 mb-0">Untuk menandai bagian laporan: blok teks di dokumen, salin, lalu tempel ke kolom <em>Kata atau kalimat yang disorot</em> beserta nomor halamannya.</p>
                @else<p class="text-secondary mb-0">Berkas Word tidak bisa ditampilkan di browser. Unduh untuk membacanya.</p>@endif
            </section>
        </div>
        <div class="col-xl-5">
            @if ($sebelumnya && $sebelumnya->tanda->isNotEmpty())
                <section class="pk-panel"><h2 class="pk-panel-title mb-1">Periksa revisi dari versi {{ $sebelumnya->versi }}</h2>
                    <p class="small text-secondary mb-3">Tandai bagian yang sudah diperbaiki mahasiswa.</p>@include('shared.tanda', ['laporan' => $sebelumnya, 'bisaKelola' => true])</section>
            @endif
            <section class="pk-panel" id="tanda">
                <h2 class="pk-panel-title mb-3">Bagian yang ditandai pada versi {{ $laporan->versi }}</h2>
                @include('shared.tanda', ['laporan' => $laporan, 'bisaKelola' => true])
                @if ($terbaru && $laporan->status !== 'disetujui')
                    <form method="POST" action="{{ route('pembimbing.laporan.tanda', $laporan) }}" class="pk-note-form mt-3">@csrf
                        <div class="row g-2">
                            <div class="col-4"><x-input name="halaman" label="Halaman" type="number" min="1" /></div>
                            <div class="col-12"><x-input name="kutipan" label="Kata atau kalimat yang disorot" type="textarea" rows="2" hint="Tampil dengan sorotan kuning bagi mahasiswa." /></div>
                            <div class="col-12"><x-input name="catatan" label="Catatan perbaikan" type="textarea" rows="3" :required="true" /></div>
                        </div>
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>Tandai bagian ini</button>
                    </form>
                @endif
            </section>
            @if ($terbaru && $laporan->status === 'menunggu')
                <section class="pk-panel pk-panel--decision">
                    <h2 class="pk-panel-title mb-1">Keputusan</h2>
                    <p class="small text-secondary mb-3">{{ $laporan->tanda_terbuka }} bagian belum diperbaiki.</p>
                    <form method="POST" action="{{ route('pembimbing.laporan.putuskan', $laporan) }}" data-confirm="Kirim keputusan pemeriksaan laporan ini ke mahasiswa?">@csrf @method('PATCH')
                        <x-input name="komentar_dosen" label="Komentar atau feedback umum" type="textarea" rows="3" :value="$laporan->komentar_dosen" />
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-danger" type="submit" name="keputusan" value="revisi">Minta revisi</button>
                            <button class="btn btn-primary" type="submit" name="keputusan" value="disetujui">Setujui laporan</button>
                        </div>
                    </form>
                </section>
            @elseif ($laporan->status === 'disetujui')
                <div class="alert alert-success">Laporan sudah disetujui. <a href="{{ route('pembimbing.penilaian.edit', $pengajuan) }}" class="alert-link">Input nilai</a></div>
            @endif
        </div>
    </div>
@endsection
