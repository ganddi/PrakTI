@extends('layouts.app')
@section('judul', 'Progress KPI')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel">
            <div class="pk-empty">
                <i class="bi bi-bar-chart-steps" aria-hidden="true"></i>
                <h3>Progress belum bisa diperbarui</h3>
                <p>Progress KPI bisa dilaporkan setelah pengajuan Anda disetujui admin.</p>
                <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-outline-primary">Lihat status pengajuan</a>
            </div>
        </section>
    @else
        <div class="row g-4">
            @if ($pengajuan->status === 'berjalan')
                <div class="col-lg-5 order-lg-2">
                    <section class="pk-panel">
                        <h2 class="pk-panel-title mb-1">Laporkan progress</h2>
                        <p class="text-secondary small">Dikirim ke {{ $pengajuan->pembimbing->name ?? 'dosen pembimbing' }} untuk diverifikasi.</p>

                        <form method="POST" action="{{ route('mahasiswa.progress.store') }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            <x-input name="judul" label="Judul capaian" :required="true" hint="Contoh: Modul login selesai diuji." />

                            @php $minimal = $pengajuan->persentase; @endphp
                            <div class="mb-3">
                                <label for="persentase" class="form-label d-flex justify-content-between">
                                    <span>Persentase penyelesaian</span>
                                    <output id="nilai-persentase" class="fw-bold text-primary"></output>
                                </label>
                                <input type="range" class="form-range" id="persentase" name="persentase" step="5"
                                       min="{{ $minimal }}" max="100" value="{{ old('persentase', min(100, $minimal + 10)) }}"
                                       data-range-output="nilai-persentase">
                                <div class="form-text">Progress sebelumnya {{ $minimal }}%.</div>
                            </div>

                            <x-input name="deskripsi" label="Penjelasan" type="textarea" rows="5" :required="true"
                                     hint="Apa yang sudah selesai, apa yang sedang dikerjakan, dan kendalanya." />
                            <x-input name="lampiran" label="Lampiran" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" hint="Maksimal 5 MB." />

                            <button type="submit" class="btn btn-primary w-100">Kirim progress</button>
                        </form>
                    </section>
                </div>
            @endif

            <div class="{{ $pengajuan->status === 'berjalan' ? 'col-lg-7 order-lg-1' : 'col-12' }}">
                <section class="pk-panel mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h2 class="pk-panel-title">{{ $pengajuan->nama_instansi }}</h2>
                        <x-status :value="$pengajuan->status" />
                    </div>
                    <x-meter :nilai="$pengajuan->persentase" />
                </section>

                @include('shared.progress', ['daftarProgress' => $pengajuan->progress, 'mode' => 'mahasiswa'])
            </div>
        </div>
    @endif
@endsection
