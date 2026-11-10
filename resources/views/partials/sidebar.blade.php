@php
    $user = auth()->user();
    $menu = match ($user->role) {
        'admin' => [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'ikon' => 'bi-grid', 'label' => 'Dashboard'],
            ['route' => 'admin.pengajuan.index', 'aktif' => 'admin.pengajuan.*', 'ikon' => 'bi-inbox', 'label' => 'Pengajuan KPI',
                'badge' => \App\Models\PengajuanKpi::where('status', 'diajukan')->count()],
            ['route' => 'admin.monitoring', 'aktif' => 'admin.monitoring', 'ikon' => 'bi-activity', 'label' => 'Monitoring KPI'],
            ['route' => 'admin.pengguna.index', 'aktif' => 'admin.pengguna.*', 'ikon' => 'bi-people', 'label' => 'Kelola pengguna',
                'badge' => \App\Models\User::where('role', 'pembimbing')->where('is_active', false)->count()],
        ],
        'pembimbing' => [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'ikon' => 'bi-grid', 'label' => 'Dashboard'],
            ['route' => 'pembimbing.bimbingan.index', 'aktif' => 'pembimbing.bimbingan.*', 'ikon' => 'bi-mortarboard', 'label' => 'Mahasiswa bimbingan'],
        ],
        default => [
            ['route' => 'dashboard', 'aktif' => 'dashboard', 'ikon' => 'bi-grid', 'label' => 'Dashboard'],
            ['route' => 'mahasiswa.pengajuan.index', 'aktif' => 'mahasiswa.pengajuan.*', 'ikon' => 'bi-send', 'label' => 'Pengajuan KPI'],
            ['route' => 'mahasiswa.logbook.index', 'aktif' => 'mahasiswa.logbook.*', 'ikon' => 'bi-journal-text', 'label' => 'Logbook harian'],
            ['route' => 'mahasiswa.progress.index', 'aktif' => 'mahasiswa.progress.*', 'ikon' => 'bi-bar-chart-steps', 'label' => 'Progress KPI'],
        ],
    };
@endphp
<div class="pk-sidebar-inner">
    <div class="pk-brand">
        <a href="{{ route('dashboard') }}" class="pk-logo">
            <span class="pk-logo-mark"><i class="bi bi-journal-text"></i></span>PrakTI
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none ms-auto"
                data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
    </div>

    <nav class="pk-nav">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}" class="pk-nav-link {{ request()->routeIs($item['aktif']) ? 'active' : '' }}"
               @if (request()->routeIs($item['aktif'])) aria-current="page" @endif>
                <i class="bi {{ $item['ikon'] }}" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
                @if (! empty($item['badge']))
                    <span class="pk-nav-badge" title="Perlu ditindaklanjuti">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <p class="pk-sidebar-foot">Program Studi Informatika<br>Platform Praktik Industri Terintegrasi</p>
</div>
