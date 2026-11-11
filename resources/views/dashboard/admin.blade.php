@extends('layouts.app')
@section('judul', 'Dashboard')
@section('subjudul', 'Kelola dan pantau pelaksanaan Kerja Praktik Industri melalui PrakTI')

@section('content')
    <div class="pk-stats mb-4">
        <x-kartu ikon="bi-mortarboard" label="Total mahasiswa" :nilai="$kartu['mahasiswa']" ket="Mahasiswa terdaftar" :href="route('admin.pengguna.index')" />
        <x-kartu ikon="bi-person-badge" label="Total pembimbing" :nilai="$kartu['pembimbing']" ket="Pembimbing aktif" warna="ink" :href="route('admin.pengguna.index', ['peran' => 'pembimbing'])" />
        <x-kartu ikon="bi-building" label="Total instansi" :nilai="$kartu['instansi']" ket="Tempat KPI aktif" warna="gold" :href="route('admin.tempat.index')" />
    </div>

    @php $perlu = array_filter($tindakan); @endphp
    @if ($perlu)
        <div class="pk-reminder flex-column align-items-start">
            <div class="fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>Perlu ditindaklanjuti</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach (['pengajuan' => ['Pengajuan menunggu', route('admin.pengajuan.index')], 'surat' => ['Surat balasan', route('admin.verifikasi.index', ['tab' => 'surat'])], 'penempatan' => ['Pilihan penempatan dosen', route('admin.penempatan.index')], 'nilai' => ['Dokumen penilaian & nilai akhir', route('admin.verifikasi.index', ['tab' => 'penilaian'])], 'akun' => ['Akun menunggu aktivasi', route('admin.pengguna.index', ['peran' => 'pembimbing'])]] as $k => [$label, $url])
                    @if ($tindakan[$k])<a href="{{ $url }}" class="btn btn-sm btn-outline-primary">{{ $label }} <span class="pk-count-badge">{{ $tindakan[$k] }}</span></a>@endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <section class="pk-panel h-100">
                <div class="pk-panel-head">
                    <h2 class="pk-panel-title"><i class="bi bi-inbox me-1"></i>Pengajuan KPI terbaru</h2>
                    <a href="{{ route('admin.pengajuan.index', ['status' => 'semua']) }}" class="small fw-semibold">Lihat semua</a>
                </div>
                <form method="GET" class="mb-3" role="search"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau NIM mahasiswa" aria-label="Cari pengajuan"></form>
                @if ($pengajuanTerbaru->isEmpty())
                    <p class="text-secondary mb-0">Belum ada pengajuan.</p>
                @else
                    <div class="table-responsive"><table class="table pk-table">
                        <thead><tr><th>Nama mahasiswa</th><th>Instansi</th><th>Tanggal pengajuan</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($pengajuanTerbaru as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $p->mahasiswa->nim }}</span></td>
                                <td>{{ $p->nama_instansi }}</td>
                                <td class="text-nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                                <td><x-status :value="$p->status" /></td>
                                <td class="text-end"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-sm btn-outline-primary" aria-label="Detail {{ $p->mahasiswa->nama_lengkap }}"><i class="bi bi-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>
        </div>
        <div class="col-xl-4">
            <section class="pk-panel h-100">
                <h2 class="pk-panel-title mb-3"><i class="bi bi-pie-chart me-1"></i>Status KPI</h2>
                <x-donat ket="Total KPI" :data="[
                    'Sedang berjalan' => [$statusKpi['Sedang berjalan'], '#1F3A5F'],
                    'Selesai' => [$statusKpi['Selesai'], '#2E8B62'],
                    'Menunggu verifikasi' => [$statusKpi['Menunggu verifikasi'], '#E0A526'],
                    'Disetujui, belum ditempatkan' => [$statusKpi['Disetujui'], '#9DB1CB'],
                ]" />
            </section>
        </div>
    </div>

    <section class="pk-panel mt-4">
        <div class="pk-panel-head"><h2 class="pk-panel-title"><i class="bi bi-clock-history me-1"></i>Aktivitas terbaru</h2><a href="{{ route('admin.aktivitas') }}" class="small fw-semibold">Lihat semua</a></div>
        @include('shared.riwayat', ['aktivitas' => $aktivitas])
    </section>
@endsection
