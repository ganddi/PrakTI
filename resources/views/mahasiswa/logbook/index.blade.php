@extends('layouts.app')
@section('judul', 'Logbook harian')
@section('subjudul', 'Catatan kegiatan KPI setiap hari kerja')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-journal" aria-hidden="true"></i><h3>Logbook belum bisa diisi</h3>
            <p>Logbook terbuka setelah Anda ditempatkan di kelompok KPI.</p><a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-primary">Lihat status pengajuan</a></div></section>
    @else
        @php $berjalan = $pengajuan->status === 'berjalan'; @endphp
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <p class="pk-lead mb-0">{{ $pengajuan->nama_instansi }}, {{ $pengajuan->periode->nama_periode }}. {{ $logbooks->total() }} hari tercatat.</p>
            <div class="d-flex flex-wrap gap-2">
                @if ($logbooks->total() > 0)
                    <div class="dropdown"><button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-box-arrow-up me-1"></i>Ekspor</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('ekspor.logbook.cetak', $pengajuan) }}" target="_blank" rel="noopener"><i class="bi bi-printer me-2"></i>Cetak atau simpan PDF</a></li>
                            <li><a class="dropdown-item" href="{{ route('ekspor.logbook.csv', $pengajuan) }}"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Unduh Excel (CSV)</a></li>
                        </ul></div>
                @endif
                @if ($berjalan)<a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Isi logbook</a>@endif
            </div>
        </div>
        @if ($perluIsiHariIni)<div class="pk-reminder"><i class="bi bi-pencil-square" aria-hidden="true"></i><div class="flex-grow-1">Logbook hari ini, {{ today()->translatedFormat('l d F') }}, belum diisi.</div><a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-sm btn-primary">Isi sekarang</a></div>@endif
        @unless ($berjalan)<div class="alert alert-secondary">KPI sudah selesai. Logbook tetap bisa dilihat sebagai arsip.</div>@endunless
        @include('shared.logbook', ['logbooks' => $logbooks, 'mode' => 'mahasiswa', 'bisaTambah' => $berjalan])
        <div class="mt-3">{{ $logbooks->links() }}</div>

        <section class="pk-panel mt-4">
            <h2 class="pk-panel-title mb-1">Dokumen logbook bertanda tangan</h2>
            <p class="small text-secondary mb-3">Cetak logbook lewat menu Ekspor, mintakan paraf pembimbing lapangan dan dosen, lalu unggah hasil pindainya.</p>
            <div class="row g-4">
                <div class="col-lg-6">
                    @forelse ($dokumenLogbook as $d)<x-berkas :href="route('dokumen.unduh', $d)" :nama="$d->nama_file" :ukuran="$d->ukuran_format" :lihat="true" class="mb-2" />
                    @empty<p class="text-secondary small">Belum ada dokumen logbook.</p>@endforelse
                    @foreach ($templateLogbook as $t)<p class="small mb-1 mt-3">Template logbook:</p><x-berkas :href="route('template.unduh', $t->kode)" :nama="$t->nama" :ukuran="$t->ukuran" />@endforeach
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('mahasiswa.logbook.dokumen') }}" enctype="multipart/form-data">@csrf
                        <x-input name="berkas" label="Berkas logbook" type="file" accept=".pdf,.jpg,.jpeg,.png" :required="true" hint="PDF, JPG, atau PNG, maksimal 10 MB." />
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-upload me-1"></i>Unggah dokumen logbook</button></form>
                </div>
            </div>
        </section>
    @endif
@endsection
