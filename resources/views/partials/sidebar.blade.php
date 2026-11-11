{{-- Menu navigasi mengikuti mockup SDD Gambar 4.12–4.18 --}}
@php
    $user = auth()->user();
    $menu = match ($user->role) {
        'admin' => [
            'Utama' => [['dashboard', 'dashboard', 'bi-grid', 'Dashboard']],
            'Data' => [
                ['admin.periode.index', 'admin.periode.*|admin.kelompok.*', 'bi-collection', 'Kerja Praktik Industri'],
                ['admin.pengguna.index', 'admin.pengguna.*', 'bi-people', 'Pengguna', \App\Models\User::where('status', 'menunggu')->count()],
                ['admin.tempat.index', 'admin.tempat.*', 'bi-building', 'Tempat KPI', \App\Models\TempatKpi::where('status', 'usulan')->count()],
            ],
            'Praktik industri' => [
                ['admin.pengajuan.index', 'admin.pengajuan.*', 'bi-inbox', 'Pengajuan KPI', \App\Models\PengajuanKpi::where('status', 'diajukan')->count()],
                ['admin.penempatan.index', 'admin.penempatan.*', 'bi-person-workspace', 'Penempatan dosen', \App\Models\PenempatanDosen::where('status', 'diajukan')->count()],
                ['admin.monitoring', 'admin.monitoring', 'bi-activity', 'Monitoring KPI'],
                ['admin.verifikasi.index', 'admin.verifikasi.*', 'bi-folder-check', 'Dokumen', \App\Models\DokumenKpi::where('status_verifikasi', 'menunggu')->count()],
            ],
            'Penilaian' => [
                ['admin.rekap.index', 'admin.rekap.*', 'bi-table', 'Rekap nilai'],
                ['admin.statistik', 'admin.statistik', 'bi-bar-chart', 'Statistik'],
            ],
            'Lainnya' => [
                ['admin.template.index', 'admin.template.*', 'bi-file-earmark-text', 'Template dokumen'],
                ['admin.aktivitas', 'admin.aktivitas', 'bi-clock-history', 'Aktivitas sistem'],
                ['pengaturan', 'pengaturan*', 'bi-gear', 'Pengaturan'],
            ],
        ],
        'pembimbing' => [
            'Utama' => [['dashboard', 'dashboard', 'bi-grid', 'Dashboard']],
            'Data' => [
                ['pembimbing.bimbingan.index', 'pembimbing.bimbingan.*', 'bi-mortarboard', 'Kerja Praktik Industri'],
                ['pembimbing.penempatan.index', 'pembimbing.penempatan.*', 'bi-geo-alt', 'Pilihan penempatan', $user->dosen?->penempatan()->whereIn('status', ['ditawarkan', 'perlu_perbaikan'])->count()],
            ],
            'Praktik industri' => [
                ['pembimbing.logbook.index', 'pembimbing.logbook.*', 'bi-journal-text', 'Daftar logbook mahasiswa'],
                ['pembimbing.laporan.index', 'pembimbing.laporan.*', 'bi-file-earmark-richtext', 'Laporan mahasiswa'],
                ['pembimbing.dokumen', 'pembimbing.dokumen', 'bi-folder2-open', 'Dokumen'],
                ['pembimbing.riwayat', 'pembimbing.riwayat', 'bi-clock-history', 'Riwayat aktivitas mahasiswa'],
            ],
            'Penilaian' => [['pembimbing.penilaian.index', 'pembimbing.penilaian.*', 'bi-award', 'Nilai mahasiswa']],
            'Lainnya' => [['pengaturan', 'pengaturan*', 'bi-gear', 'Pengaturan']],
        ],
        default => [
            'Utama' => [['dashboard', 'dashboard', 'bi-grid', 'Dashboard']],
            'Pengajuan' => [['mahasiswa.pengajuan.index', 'mahasiswa.pengajuan.*', 'bi-send', 'Pengajuan KPI']],
            'Pelaksanaan' => [
                ['mahasiswa.informasi', 'mahasiswa.informasi', 'bi-building', 'Kerja Praktik Industri'],
                ['mahasiswa.logbook.index', 'mahasiswa.logbook.*', 'bi-journal-text', 'Logbook harian'],
                ['mahasiswa.laporan.index', 'mahasiswa.laporan.*', 'bi-file-earmark-richtext', 'Laporan'],
                ['mahasiswa.progress', 'mahasiswa.progress', 'bi-graph-up', 'Progress'],
            ],
            'Penilaian' => [
                ['mahasiswa.penilaian.index', 'mahasiswa.penilaian.*', 'bi-clipboard-check', 'Penilaian KPI'],
                ['mahasiswa.nilai.index', 'mahasiswa.nilai.*', 'bi-award', 'Rekapitulasi nilai'],
            ],
            'Lainnya' => [['pengaturan', 'pengaturan*', 'bi-gear', 'Pengaturan']],
        ],
    };
@endphp
<div class="pk-sidebar-inner">
    <div class="pk-brand">
        <a href="{{ route('dashboard') }}" class="pk-logo">
            <span class="pk-logo-mark"><i class="bi bi-journal-text"></i></span>
            <span class="d-flex flex-column lh-1">PrakTI<small class="pk-logo-sub">Platform Kerja Praktik Industri</small></span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
    </div>
    <nav class="pk-nav">
        @foreach ($menu as $judul => $item)
            <div class="pk-nav-heading">{{ $judul }}</div>
            @foreach ($item as $m)
                @php $aktif = collect(explode('|', $m[1]))->contains(fn ($pola) => request()->routeIs($pola)); @endphp
                <a href="{{ route($m[0]) }}" class="pk-nav-link {{ $aktif ? 'active' : '' }}" @if ($aktif) aria-current="page" @endif>
                    <i class="bi {{ $m[2] }}" aria-hidden="true"></i><span>{{ $m[3] }}</span>
                    @if (! empty($m[4]))<span class="pk-nav-badge" title="Perlu ditindaklanjuti">{{ $m[4] }}</span>@endif
                </a>
            @endforeach
        @endforeach
    </nav>
    <p class="pk-sidebar-foot">{{ config('prakti.nama_prodi') }}</p>
</div>
