<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PrakTI | Platform Praktik Industri Terintegrasi</title>
    <meta name="description" content="Pengajuan, logbook, laporan, dan penilaian Kerja Praktik Industri mahasiswa {{ config('prakti.nama_prodi') }} dalam satu sistem.">
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/prakti.css') }}" rel="stylesheet">
</head>
<body class="pk-landing">
<header class="pk-land-nav">
    <div class="container d-flex align-items-center gap-3">
        <a href="{{ route('beranda') }}" class="pk-logo pk-logo--dark">
            <span class="pk-logo-mark"><i class="bi bi-journal-text"></i></span>PrakTI
        </a>
        <nav class="ms-auto d-none d-md-flex gap-4" aria-label="Navigasi halaman">
            <a href="#alur">Alur KPI</a>
            <a href="#tempat">Tempat KPI</a>
        </nav>
        <div class="d-flex gap-2 ms-auto ms-md-4">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Buka dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary d-none d-sm-inline-block">Daftar</a>
            @endauth
        </div>
    </div>
</header>

<main>
    <section class="pk-hero">
        <div class="container">
            <p class="pk-hero-kicker">{{ config('prakti.nama_prodi') }}</p>
            <h1 class="pk-hero-title">Kerja Praktik Industri, dari pengajuan sampai nilai akhir.</h1>
            <p class="pk-hero-lead">Ajukan KPI, isi logbook harian, kirim laporan untuk dibimbing, dan pantau penilaian di satu sistem. Dosen pembimbing dan admin program studi memantau prosesnya dari tempat yang sama.</p>
            <div class="d-flex flex-wrap gap-2">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Daftar sebagai mahasiswa</a>
                    <a href="#tempat" class="btn btn-outline-primary btn-lg">Lihat tempat KPI</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Buka dashboard</a>
                @endguest
            </div>
        </div>
    </section>

    <section class="pk-land-section" id="alur">
        <div class="container">
            <h2 class="pk-land-title">Alur KPI di PrakTI</h2>
            <ol class="pk-flow">
                <li><strong>Pengajuan</strong><span>Isi data instansi dan unggah dokumen persyaratan. Admin memverifikasi.</span></li>
                <li><strong>Surat balasan</strong><span>Unduh template surat permohonan, kirim ke instansi, unggah surat balasannya.</span></li>
                <li><strong>Penempatan</strong><span>Admin membentuk kelompok per instansi dan menetapkan dosen pembimbing.</span></li>
                <li><strong>Pelaksanaan</strong><span>Isi logbook setiap hari kerja dan laporkan progress secara berkala.</span></li>
                <li><strong>Laporan</strong><span>Unggah laporan, terima catatan revisi dari pembimbing, unggah perbaikan.</span></li>
                <li><strong>Penilaian</strong><span>Nilai pembimbing, penguji, dan pembimbing lapangan dihitung otomatis menjadi nilai akhir.</span></li>
            </ol>
        </div>
    </section>

    <section class="pk-land-section pk-land-section--alt" id="tempat">
        <div class="container">
            <h2 class="pk-land-title">Tempat KPI</h2>
            <p class="pk-lead mb-4">Rekomendasi instansi dari program studi. Anda tetap boleh mengajukan tempat lain yang sesuai bidang Informatika.</p>

            @if ($tempat->isEmpty())
                <div class="pk-panel"><div class="pk-empty"><i class="bi bi-building" aria-hidden="true"></i><h3>Informasi tempat KPI belum tersedia</h3><p>Program studi akan menampilkan rekomendasi tempat KPI di sini.</p></div></div>
            @else
                <div class="row g-3">
                    @foreach ($tempat as $t)
                        <div class="col-md-6 col-lg-4">
                            <article class="pk-place">
                                <h3 class="pk-place-name">{{ $t->nama_instansi }}</h3>
                                <p class="pk-place-field">{{ $t->jenis_instansi }}</p>
                                @if ($t->deskripsi)<p class="pk-place-desc">{{ \Illuminate\Support\Str::limit($t->deskripsi, 160) }}</p>@endif
                                <dl class="pk-place-meta">
                                    <div><dt><i class="bi bi-geo-alt" aria-hidden="true"></i><span class="visually-hidden">Alamat</span></dt><dd>{{ $t->alamat_lengkap }}</dd></div>
                                    @if ($t->nama_pic)<div><dt><i class="bi bi-person" aria-hidden="true"></i><span class="visually-hidden">Penanggung jawab</span></dt><dd>{{ $t->nama_pic }}</dd></div>@endif
                                    @if ($t->no_hp_pic || $t->email_pic)<div><dt><i class="bi bi-telephone" aria-hidden="true"></i><span class="visually-hidden">Kontak</span></dt><dd>{{ collect([$t->no_hp_pic, $t->email_pic])->filter()->implode(', ') }}</dd></div>@endif
                                </dl>
                                <div class="pk-place-foot"><span class="small text-secondary">{{ $t->pernah_count ? 'Pernah menerima '.$t->pernah_count.' mahasiswa' : 'Belum ada mahasiswa KPI di sini' }}</span></div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>

<footer class="pk-land-footer">
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span>PrakTI, Platform Praktik Industri Terintegrasi</span>
        <span>{{ config('prakti.nama_prodi') }}</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
