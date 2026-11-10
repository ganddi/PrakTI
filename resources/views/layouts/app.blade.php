<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Dashboard') | PrakTI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <h1 class="pk-page-title">@yield('judul', 'Dashboard')</h1>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block lh-sm">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <small class="text-secondary">{{ auth()->user()->peran_label }}</small>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i>Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="pk-content">
            @include('partials.alert')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/prakti.js') }}"></script>
@stack('scripts')
</body>
</html>
