<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Dashboard') | PrakTI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/prakti.css') }}" rel="stylesheet">
</head>
<body>
<div class="pk-app">
    <aside class="pk-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Menu utama">
        @include('partials.sidebar')
    </aside>

    <div class="pk-main">
        <header class="pk-topbar">
            <button class="btn btn-link d-lg-none p-0 me-1 text-body" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="pk-page-head"><h1 class="pk-page-title">@yield('judul', 'Dashboard')</h1>@hasSection('subjudul')<p class="pk-page-sub">@yield('subjudul')</p>@endif</div>

            <div class="ms-auto d-flex align-items-center gap-3">
                @include('partials.notifikasi')
                <div class="dropdown pk-account-dropdown">
                    <button class="pk-account-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun {{ auth()->user()->name }}">
                        <span class="pk-account-avatar" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
                        <span class="pk-account-name">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down pk-account-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end pk-account-menu">
                        <div class="pk-account-menu-heading">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->peran_label }}</small>
                        </div>
                        <a class="dropdown-item" href="{{ route('pengaturan') }}"><i class="bi bi-person-gear me-2"></i>Profil dan pengaturan</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="pk-content">
            @include('partials.alert')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/prakti.js') }}?v={{ filemtime(public_path('js/prakti.js')) }}"></script>
@stack('scripts')
</body>
</html>
