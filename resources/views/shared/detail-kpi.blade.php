{{-- Detail satu KPI, dipakai admin (mode admin) dan pembimbing (mode pembimbing). --}}
@php
    $mhs = $pengajuan->mahasiswa;
    $menunggu = $pengajuan->logbooks->where('status', 'menunggu')->count();
    $awalDokumen = in_array($pengajuan->status, ['diajukan', 'revisi', 'disetujui']);
@endphp
<section class="pk-panel mb-4">
    <div class="pk-panel-head">
        <div>
            <h2 class="pk-panel-title fs-5">{{ $mhs->nama_lengkap }}</h2>
            <span class="text-secondary">{{ $mhs->nim }}, {{ $mhs->program_studi }}, {{ $mhs->email }}@if ($mhs->no_hp), {{ $mhs->no_hp }}@endif</span>
        </div>
        <x-status :value="$pengajuan->status" />
    </div>
    <x-tahapan :pengajuan="$pengajuan" class="mb-4" />
    <dl class="pk-info mb-3">
        <div><dt>Tempat KPI</dt><dd>{{ $pengajuan->nama_instansi }} @if ($pengajuan->tempatKpi->status === 'usulan')<x-status value="usulan" class="ms-1" />@endif</dd></div>
        <div><dt>Jenis instansi</dt><dd>{{ $pengajuan->bidang_kerja }}</dd></div>
        <div><dt>Periode</dt><dd>{{ $pengajuan->periode->nama_periode }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} – {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}</span></dd></div>
        <div><dt>Kelompok</dt><dd>@if ($pengajuan->kelompok)@if ($mode === 'admin')<a href="{{ route('admin.kelompok.show', $pengajuan->kelompok) }}">{{ $pengajuan->kelompok->nama_kelompok }}</a>@else{{ $pengajuan->kelompok->nama_kelompok }}@endif @else Belum ditetapkan @endif</dd></div>
        <div><dt>Dosen pembimbing</dt><dd>{{ $pengajuan->pembimbing?->nama_lengkap ?? 'Belum ditetapkan' }}</dd></div>
        <div><dt>PIC instansi</dt><dd>{{ $pengajuan->pembimbing_lapangan ?: '–' }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->kontak_instansi }}</span></dd></div>
        <div><dt>Alamat instansi</dt><dd class="fw-normal">{{ $pengajuan->alamat_instansi }}</dd></div>
        <div><dt>Keperluan</dt><dd class="fw-normal">{{ $pengajuan->keperluan }}</dd></div>
        @if ($pengajuan->catatan_admin)<div><dt>Catatan admin</dt><dd class="fw-normal">{{ $pengajuan->catatan_admin }}</dd></div>@endif
    </dl>
    <div style="max-width: 420px"><div class="small text-secondary mb-1">Progres tahapan KPI</div><x-meter :nilai="$pengajuan->persentase" /></div>
</section>

<ul class="nav pk-tabs" role="tablist">
    @foreach (['logbook' => ['Logbook', $pengajuan->logbooks->count()], 'laporan' => ['Laporan', $pengajuan->laporan->count()], 'penilaian' => ['Penilaian', null], 'dokumen' => ['Dokumen', $pengajuan->dokumen->count()], 'riwayat' => ['Riwayat', null]] as $k => [$label, $n])
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($awalDokumen ? $k === 'dokumen' : $k === 'logbook') ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ $k }}" type="button" role="tab">
                {{ $label }} @if ($n !== null)<span class="text-secondary fw-normal">{{ $n }}</span>@endif
            </button>
        </li>
    @endforeach
</ul>
<div class="tab-content">
    <div class="tab-pane fade {{ $awalDokumen ? '' : 'show active' }}" id="tab-logbook" role="tabpanel">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if ($mode === 'pembimbing' && $menunggu > 0)
                <form method="POST" action="{{ route('pembimbing.logbook.setujui-semua', $pengajuan) }}" data-confirm="Setujui {{ $menunggu }} logbook yang menunggu diperiksa?">@csrf @method('PATCH')
                    <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-check2-all me-1"></i>Setujui semua yang menunggu ({{ $menunggu }})</button></form>
            @endif
            @if ($pengajuan->logbooks->isNotEmpty())
                <a href="{{ route('ekspor.logbook.cetak', $pengajuan) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Lihat dokumen logbook</a>
                <a href="{{ route('ekspor.logbook.csv', $pengajuan) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
            @endif
        </div>
        @include('shared.logbook', ['logbooks' => $pengajuan->logbooks, 'mode' => $mode])
    </div>
    <div class="tab-pane fade" id="tab-laporan" role="tabpanel">@include('shared.laporan', ['daftarLaporan' => $pengajuan->laporan, 'mode' => $mode])</div>
    <div class="tab-pane fade" id="tab-penilaian" role="tabpanel">
        <section class="pk-panel">
            <div class="pk-panel-head">
                <h3 class="pk-panel-title">Penilaian KPI</h3>
                <div class="d-flex gap-2">
                    @if ($mode === 'pembimbing' && $pengajuan->laporanTerakhir?->status === 'disetujui' && $pengajuan->penilaianJenis('dosen_pembimbing')?->status !== 'final')
                        <a href="{{ route('pembimbing.penilaian.edit', $pengajuan) }}" class="btn btn-sm btn-primary">Input nilai</a>
                    @endif
                    @if ($pengajuan->nilaiAkhir)<a href="{{ route('ekspor.nilai-akhir', $pengajuan) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>Dokumen nilai akhir</a>@endif
                </div>
            </div>
            @include('shared.penilaian', ['pengajuan' => $pengajuan, 'tampilkanDraf' => $mode === 'pembimbing'])
        </section>
    </div>
    <div class="tab-pane fade {{ $awalDokumen ? 'show active' : '' }}" id="tab-dokumen" role="tabpanel">@include('shared.dokumen', ['dokumen' => $pengajuan->dokumen])</div>
    <div class="tab-pane fade" id="tab-riwayat" role="tabpanel"><section class="pk-panel">@include('shared.riwayat', ['aktivitas' => $pengajuan->riwayat])</section></div>
</div>
