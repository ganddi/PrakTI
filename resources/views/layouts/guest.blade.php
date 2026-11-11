<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') | PrakTI</title>
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/prakti.css') }}" rel="stylesheet">
</head>
<body>
<div class="pk-guest">
    <aside class="pk-guest-aside">
        <a href="{{ route('beranda') }}" class="pk-logo">
            <span class="pk-logo-mark"><i class="bi bi-journal-text"></i></span>PrakTI
        </a>
        <p class="pk-guest-title">Kerja Praktik Industri, tercatat rapi setiap hari.</p>
        <p class="pk-guest-desc">Ajukan KPI, isi logbook harian, perbarui progress, dan terima arahan dosen pembimbing di satu tempat.</p>
        <p class="pk-guest-foot">Program Studi Informatika</p>
    </aside>

    <main class="pk-guest-main">
        <div class="pk-guest-form">
            @include('partials.alert')
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/prakti.js') }}"></script>
</body>
</html>
