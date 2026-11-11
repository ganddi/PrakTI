@extends('layouts.app')
@section('judul', 'Dashboard')
@section('subjudul', 'Ringkasan kegiatan dan progres KPI Anda')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty">
            <i class="bi bi-send" aria-hidden="true"></i><h3>Anda belum mengajukan KPI</h3>
            <p>Pilih periode dan tempat KPI, siapkan dokumen persyaratan dalam format PDF, lalu kirim pengajuan.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary">Ajukan KPI</a>
                <a href="{{ route('beranda') }}#tempat" class="btn btn-outline-primary">Lihat tempat KPI</a>
            </div>
        </div></section>
    @else
        @if ($pengajuan->status === 'berjalan' && ! $sudahIsiHariIni && ! today()->isWeekend())
            <div class="pk-reminder"><i class="bi bi-pencil-square" aria-hidden="true"></i><div class="flex-grow-1">Logbook hari ini belum diisi.</div>
                <a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-sm btn-primary">Isi logbook hari ini</a></div>
        @endif

        <section class="pk-panel mb-4">
            <div class="pk-panel-head">
                <span class="small text-secondary"><i class="bi bi-calendar3 me-1"></i>{{ $pengajuan->periode->nama_periode }}: {{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</span>
                <x-status :value="$pengajuan->status" />
            </div>
            <x-tahapan :pengajuan="$pengajuan" />
            @if ($langkahBerikut)
                <div class="pk-next mt-4"><i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                    <p class="mb-0 flex-grow-1"><span class="fw-semibold">Langkah berikutnya:</span> {{ $langkahBerikut[0] }}</p>
                    @if ($langkahBerikut[1])<a href="{{ $langkahBerikut[1] }}" class="btn btn-sm btn-primary">{{ $langkahBerikut[2] }}</a>@endif
                </div>
            @endif
        </section>

        <div class="pk-stats pk-stats--4 mb-4">
            <x-kartu ikon="bi-send" label="Pengajuan KPI" :nilai="$pengajuan->nama_instansi" :ket="'Diajukan '.$pengajuan->tanggal_pengajuan->translatedFormat('d M Y')" :href="route('mahasiswa.pengajuan.index')" />
            <x-kartu ikon="bi-journal-text" label="Logbook harian" :nilai="$pengajuan->logbooks_count" ket="Hari kegiatan tercatat" warna="green" :href="route('mahasiswa.logbook.index')" />
            <x-kartu ikon="bi-file-earmark-richtext" label="Laporan KPI" :nilai="$pengajuan->laporan_count ? 'Versi '.$pengajuan->laporanTerakhir->versi : 'Belum ada'" :ket="$pengajuan->laporanTerakhir ? ucfirst($pengajuan->laporanTerakhir->status) : 'Belum diunggah'" warna="ink" :href="route('mahasiswa.laporan.index')" />
            <x-kartu ikon="bi-award" label="Nilai akhir" :nilai="$pengajuan->nilaiAkhir ? number_format($pengajuan->nilaiAkhir->nilai_akhir, 2, ',', '.') : '–'" :ket="$pengajuan->nilaiAkhir ? 'Predikat '.$pengajuan->nilaiAkhir->predikat : 'Belum tersedia'" warna="gold" :href="route('mahasiswa.nilai.index')" />
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-1"><i class="bi bi-people me-1"></i>Progres dalam satu tim</h2>
                    <p class="small text-secondary">Persentase tahapan KPI yang sudah selesai tiap anggota kelompok</p>
                    @if ($tim->isEmpty())<p class="text-secondary mb-0">Grafik tampil setelah Anda ditempatkan di kelompok KPI.</p>
                    @else<x-batang :data="$tim->pluck('persen', 'nama')->all()" satuan="%" :maks="100" :sorot="$tim->firstWhere('saya', true)['nama'] ?? null" />@endif
                </section>
            </div>
            <div class="col-lg-4">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3"><i class="bi bi-pie-chart me-1"></i>Progres Anda</h2>
                    @php $t = collect($pengajuan->tahapan()); @endphp
                    <x-donat :tengah="$pengajuan->persentase.'%'" ket="Total tahapan" :data="[
                        'Selesai' => [$t->filter(fn ($k) => $k === 'done')->count(), '#2E8B62'],
                        'Sedang berjalan' => [$t->filter(fn ($k) => in_array($k, ['current', 'failed']))->count(), '#1F3A5F'],
                        'Belum dimulai' => [$t->filter(fn ($k) => $k === '')->count(), '#DCE4EE'],
                    ]" />
                </section>
            </div>
        </div>

        @if ($catatanDosen->isNotEmpty())
            <section class="pk-panel mt-4">
                <h2 class="pk-panel-title mb-3"><i class="bi bi-chat-left-text me-1"></i>Catatan terbaru dari dosen pembimbing</h2>
                @foreach ($catatanDosen as $c)
                    <a href="{{ $c['url'] }}" class="d-block text-decoration-none text-body py-2 {{ $loop->first ? '' : 'border-top' }}">
                        <span class="d-block small text-secondary">{{ $c['judul'] }}{{ $c['waktu'] ? ', '.$c['waktu']->diffForHumans() : '' }}</span>
                        <span class="d-block">{{ \Illuminate\Support\Str::limit($c['isi'], 180) }}</span>
                    </a>
                @endforeach
            </section>
        @endif
    @endif
@endsection
