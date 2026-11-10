{{-- Detail lengkap satu KPI. Dipakai admin (mode 'admin') dan pembimbing (mode 'pembimbing'). --}}
@php
    $jumlahMenunggu = $pengajuan->logbooks->where('status', 'menunggu')->count();
@endphp

<section class="pk-panel mb-4">
    <div class="pk-panel-head">
        <div>
            <h2 class="pk-panel-title fs-5">{{ $pengajuan->mahasiswa->name }}</h2>
            <span class="text-secondary">{{ $pengajuan->mahasiswa->nomor_induk }}, {{ $pengajuan->mahasiswa->email }}@if ($pengajuan->mahasiswa->no_hp), {{ $pengajuan->mahasiswa->no_hp }}@endif</span>
        </div>
        <x-status :value="$pengajuan->status" />
    </div>

    <dl class="pk-info mb-3">
        <div><dt>Instansi</dt><dd>{{ $pengajuan->nama_instansi }}</dd></div>
        <div><dt>Bidang kerja</dt><dd>{{ $pengajuan->bidang_kerja }}</dd></div>
        <div><dt>Periode</dt><dd>{{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</dd></div>
        <div><dt>Dosen pembimbing</dt><dd>{{ $pengajuan->pembimbing->name ?? 'Belum ditetapkan' }}</dd></div>
        <div><dt>Pembimbing lapangan</dt><dd>{{ $pengajuan->pembimbing_lapangan ?: '–' }}</dd></div>
        <div><dt>Kontak instansi</dt><dd>{{ $pengajuan->kontak_instansi ?: '–' }}</dd></div>
        <div><dt>Alamat instansi</dt><dd class="fw-normal">{{ $pengajuan->alamat_instansi }}</dd></div>
        @if ($pengajuan->deskripsi)
            <div><dt>Rencana kegiatan</dt><dd class="fw-normal">{{ $pengajuan->deskripsi }}</dd></div>
        @endif
        @if ($pengajuan->catatan_admin)
            <div><dt>Catatan admin</dt><dd class="fw-normal">{{ $pengajuan->catatan_admin }}</dd></div>
        @endif
    </dl>

    @if (in_array($pengajuan->status, ['berjalan', 'selesai']))
        <div style="max-width: 420px">
            <div class="small text-secondary mb-1">Progress terbaru{{ $pengajuan->status === 'berjalan' ? ', minggu ke-'.$pengajuan->minggu_ke : '' }}</div>
            <x-meter :nilai="$pengajuan->persentase" />
        </div>
    @endif
    @if ($pengajuan->verifikator)
        <p class="small text-secondary mt-3 mb-0">Diverifikasi oleh {{ $pengajuan->verifikator->name }}, {{ $pengajuan->diverifikasi_pada?->translatedFormat('d F Y H:i') }}</p>
    @endif
</section>

<ul class="nav pk-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $pengajuan->status === 'diajukan' ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#tab-logbook" type="button" role="tab">
            Logbook <span class="text-secondary fw-normal">{{ $pengajuan->logbooks->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-progress" type="button" role="tab">
            Progress <span class="text-secondary fw-normal">{{ $pengajuan->progress->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $pengajuan->status === 'diajukan' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-dokumen" type="button" role="tab">
            Dokumen <span class="text-secondary fw-normal">{{ $pengajuan->dokumen->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade {{ $pengajuan->status === 'diajukan' ? '' : 'show active' }}" id="tab-logbook" role="tabpanel">
        @if ($mode === 'pembimbing' && $jumlahMenunggu > 0)
            <form method="POST" action="{{ route('pembimbing.logbook.setujui-semua', $pengajuan) }}" class="mb-3"
                  data-confirm="Setujui {{ $jumlahMenunggu }} logbook yang menunggu diperiksa?">
                @csrf @method('PATCH')
                <button class="btn btn-outline-primary btn-sm" type="submit">
                    <i class="bi bi-check2-all me-1"></i>Setujui semua yang menunggu ({{ $jumlahMenunggu }})
                </button>
            </form>
        @endif
        @include('shared.logbook', ['logbooks' => $pengajuan->logbooks, 'mode' => $mode])
    </div>

    <div class="tab-pane fade" id="tab-progress" role="tabpanel">
        @include('shared.progress', ['daftarProgress' => $pengajuan->progress, 'mode' => $mode])
    </div>

    <div class="tab-pane fade {{ $pengajuan->status === 'diajukan' ? 'show active' : '' }}" id="tab-dokumen" role="tabpanel">
        @include('shared.dokumen', ['dokumen' => $pengajuan->dokumen])
    </div>
</div>
