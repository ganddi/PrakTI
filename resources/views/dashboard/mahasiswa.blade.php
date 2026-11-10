@extends('layouts.app')
@section('judul', 'Dashboard')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel">
            <div class="pk-empty">
                <i class="bi bi-send" aria-hidden="true"></i>
                <h3>Anda belum mengajukan KPI</h3>
                <p>Siapkan data instansi tempat KPI dan surat permohonan dalam format PDF, lalu kirim pengajuan untuk diverifikasi admin.</p>
                <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary">Ajukan KPI</a>
            </div>
        </section>
    @else
        @php
            $tahap = match ($pengajuan->status) {
                'diajukan' => [['done', 'Diajukan'], ['current', 'Menunggu verifikasi'], ['', 'Berjalan'], ['', 'Selesai']],
                'ditolak' => [['done', 'Diajukan'], ['failed', 'Ditolak'], ['', 'Berjalan'], ['', 'Selesai']],
                'berjalan' => [['done', 'Diajukan'], ['done', 'Disetujui'], ['current', 'Sedang berjalan'], ['', 'Selesai']],
                default => [['done', 'Diajukan'], ['done', 'Disetujui'], ['done', 'Berjalan'], ['done', 'Selesai']],
            };
        @endphp

        @if ($pengajuan->status === 'berjalan' && ! $sudahIsiHariIni && ! today()->isWeekend())
            <div class="pk-reminder">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <div class="flex-grow-1">Logbook hari ini belum diisi.</div>
                <a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-sm btn-primary">Isi logbook hari ini</a>
            </div>
        @endif

        <section class="pk-panel">
            <div class="pk-panel-head">
                <h2 class="pk-panel-title">Status KPI</h2>
                <x-status :value="$pengajuan->status" />
            </div>
            <ol class="pk-steps mb-4" aria-label="Tahapan KPI">
                @foreach ($tahap as [$kelas, $label])
                    <li class="pk-step {{ $kelas ? 'is-'.$kelas : '' }}" @if ($kelas === 'current') aria-current="step" @endif>{{ $label }}</li>
                @endforeach
            </ol>

            @if ($pengajuan->status === 'ditolak')
                <div class="alert alert-danger mb-0">
                    <div class="fw-semibold mb-1">Catatan admin</div>
                    <p class="mb-2">{{ $pengajuan->catatan_admin }}</p>
                    <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-sm btn-danger">Ajukan ulang</a>
                </div>
            @else
                <dl class="pk-info">
                    <div><dt>Instansi</dt><dd>{{ $pengajuan->nama_instansi }}</dd></div>
                    <div><dt>Periode</dt><dd>{{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</dd></div>
                    <div><dt>Dosen pembimbing</dt><dd>{{ $pengajuan->pembimbing->name ?? 'Ditetapkan setelah verifikasi' }}</dd></div>
                    @if (in_array($pengajuan->status, ['berjalan', 'selesai']))
                        <div><dt>Progress</dt><dd><x-meter :nilai="$pengajuan->persentase" /></dd></div>
                    @endif
                </dl>
            @endif
        </section>

        @if (in_array($pengajuan->status, ['berjalan', 'selesai']))
            <div class="row g-4 mt-0">
                <div class="col-lg-5">
                    <section class="pk-panel h-100">
                        <h2 class="pk-panel-title mb-3">Logbook</h2>
                        <p class="mb-1"><span class="fs-4 fw-bold text-primary">{{ $pengajuan->logbooks_count }}</span> hari tercatat</p>
                        @if ($pengajuan->logbook_revisi_count > 0)
                            <p class="mb-3"><x-status value="revisi" /> {{ $pengajuan->logbook_revisi_count }} logbook perlu diperbaiki</p>
                        @else
                            <p class="text-secondary mb-3">Tidak ada logbook yang perlu direvisi.</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('mahasiswa.logbook.index') }}" class="btn btn-outline-primary btn-sm">Buka logbook</a>
                            @if ($pengajuan->status === 'berjalan')
                                <a href="{{ route('mahasiswa.progress.index') }}" class="btn btn-outline-primary btn-sm">Perbarui progress</a>
                            @endif
                        </div>
                    </section>
                </div>
                <div class="col-lg-7">
                    <section class="pk-panel h-100">
                        <h2 class="pk-panel-title mb-3">Feedback terbaru dari pembimbing</h2>
                        @forelse ($feedbackTerbaru as $k)
                            @php
                                $diLogbook = $k->commentable instanceof \App\Models\Logbook;
                                $tautan = $diLogbook
                                    ? route('mahasiswa.logbook.index').'#logbook-'.$k->commentable_id
                                    : route('mahasiswa.progress.index').'#progress-'.$k->commentable_id;
                            @endphp
                            <a href="{{ $tautan }}" class="d-block text-decoration-none text-body py-2 {{ $loop->first ? '' : 'border-top' }}">
                                <div class="small text-secondary">
                                    {{ $diLogbook ? 'Logbook '.$k->commentable?->tanggal?->translatedFormat('d M') : 'Progress: '.$k->commentable?->judul }},
                                    {{ $k->created_at->diffForHumans() }}
                                </div>
                                <div>{{ \Illuminate\Support\Str::limit($k->isi, 160) }}</div>
                            </a>
                        @empty
                            <p class="text-secondary mb-0">Belum ada feedback. Komentar pembimbing pada logbook dan progress akan tampil di sini.</p>
                        @endforelse
                    </section>
                </div>
            </div>
        @endif
    @endif
@endsection
