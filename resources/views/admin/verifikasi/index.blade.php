@extends('layouts.app')
@section('judul', 'Verifikasi dokumen')

@section('content')
    <section class="pk-verifikasi-toolbar" aria-label="Filter verifikasi dokumen">
        <div class="pk-verifikasi-heading">
            <div>
                <h2>Dokumen yang diverifikasi</h2>
                <p>Pilih jenis dokumen dan status, atau cari mahasiswa berdasarkan nama atau NIM.</p>
            </div>
            <span class="pk-verifikasi-total"><i class="bi bi-files" aria-hidden="true"></i>{{ $dokumen->total() }} dokumen</span>
        </div>
        <ul class="nav pk-verifikasi-tabs" aria-label="Jenis dokumen">
            @foreach (\App\Http\Controllers\Admin\VerifikasiDokumenController::KELOMPOK as $k => $g)
                @php
                    $ikonDokumen = match ($k) {
                        'surat' => 'bi-envelope-paper',
                        'penilaian' => 'bi-clipboard-check',
                        'nilai_akhir' => 'bi-award',
                    };
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $tab === $k ? 'active' : '' }}"
                       href="{{ route('admin.verifikasi.index', ['tab' => $k, 'status' => $status, 'q' => $pencarian ?: null]) }}"
                       @if ($tab === $k) aria-current="page" @endif>
                        <span class="pk-verifikasi-tab-icon"><i class="bi {{ $ikonDokumen }}" aria-hidden="true"></i></span>
                        <span class="pk-verifikasi-tab-label">{{ $g['label'] }}</span>
                        @if ($jumlah[$k])<span class="pk-count-badge">{{ $jumlah[$k] }}</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="pk-verifikasi-status">
            <span class="pk-verifikasi-status-label">Status</span>
            <ul class="nav pk-verifikasi-status-list" aria-label="Status verifikasi">
                @foreach (['menunggu' => 'Menunggu', 'revisi' => 'Diminta perbaikan', 'diverifikasi' => 'Terverifikasi'] as $k => $l)
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $k ? 'active' : '' }}"
                           href="{{ route('admin.verifikasi.index', ['tab' => $tab, 'status' => $k, 'q' => $pencarian ?: null]) }}"
                           @if ($status === $k) aria-current="page" @endif>{{ $l }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <form method="GET" action="{{ route('admin.verifikasi.index') }}" class="pk-verifikasi-search" role="search">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="visually-hidden" for="cari-mahasiswa">Cari nama atau NIM mahasiswa</label>
            <div class="pk-verifikasi-search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="cari-mahasiswa" name="q" value="{{ $pencarian }}" class="form-control" placeholder="Cari nama atau NIM mahasiswa...">
            </div>
            <button class="btn btn-primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i><span>Cari</span></button>
            @if ($pencarian !== '')
                <a class="btn btn-outline-secondary pk-verifikasi-reset" href="{{ route('admin.verifikasi.index', ['tab' => $tab, 'status' => $status]) }}" aria-label="Hapus pencarian" title="Hapus pencarian">
                    <i class="bi bi-x-lg" aria-hidden="true"></i><span>Reset</span>
                </a>
            @endif
        </form>
    </section>
    @forelse ($dokumen as $d)
        @php $p = $d->pengajuan; $jenisNilai = ['penilaian_penguji' => 'dosen_penguji', 'penilaian_lapangan' => 'pembimbing_lapangan'][$d->jenis_dokumen] ?? null; $nilai = $jenisNilai ? $p->penilaianJenis($jenisNilai) : null; @endphp
        <section class="pk-panel">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="small text-secondary">{{ $d->jenis_label }}</div>
                    <h3 class="pk-panel-title"><a href="{{ route('admin.pengajuan.show', $p) }}#tab-dokumen" class="text-body">{{ $p->mahasiswa->nama_lengkap }}</a></h3>
                    <div class="small text-secondary mb-2">{{ $p->mahasiswa->nim }}, {{ $p->nama_instansi }}</div>
                    <x-berkas :href="route('dokumen.unduh', $d)" :nama="$d->nama_file" :ukuran="$d->ukuran_format" :lihat="true" />
                    <div class="small text-secondary mt-2">Diunggah {{ $d->uploaded_at->translatedFormat('d M Y H:i') }}</div>
                    @if ($nilai)<p class="mt-2 mb-0 small">Nilai diinput mahasiswa: <strong>{{ number_format($nilai->nilai, 2, ',', '.') }}</strong> oleh {{ $nilai->nama_penilai }}. Cocokkan dengan dokumen.</p>@endif
                    @if ($d->jenis_dokumen === 'nilai_akhir' && $p->nilaiAkhir)<p class="mt-2 mb-0 small">Nilai akhir di sistem: <strong>{{ number_format($p->nilaiAkhir->nilai_akhir, 2, ',', '.') }} ({{ $p->nilaiAkhir->predikat }})</strong>. <a href="{{ route('ekspor.nilai-akhir', $p) }}" target="_blank" rel="noopener">Dokumen dari sistem</a></p>@endif
                </div>
                <div class="col-lg-7">
                    @if ($status === 'menunggu')
                        <form method="POST" action="{{ route('admin.verifikasi.update', $d) }}">@csrf @method('PATCH')
                            <label for="catatan-{{ $d->id }}" class="form-label">Catatan <span class="text-secondary fw-normal">(wajib jika minta perbaikan)</span></label>
                            <textarea id="catatan-{{ $d->id }}" name="catatan" rows="2" class="form-control mb-2" maxlength="1000"></textarea>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-primary" type="submit" name="keputusan" value="diverifikasi">Verifikasi dokumen</button>
                                <button class="btn btn-outline-danger" type="submit" name="keputusan" value="revisi" data-perlu-catatan>Minta perbaikan</button>
                            </div>
                        </form>
                    @else
                        <x-status :value="$d->status_verifikasi" /><p class="small text-secondary mt-2 mb-1">{{ $d->verified_at?->translatedFormat('d M Y H:i') }}</p>
                        @if ($d->catatan)<p class="mb-0">{{ $d->catatan }}</p>@endif
                    @endif
                </div>
            </div>
        </section>
    @empty
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-folder-check" aria-hidden="true"></i><h3>{{ $pencarian !== '' ? 'Tidak ada dokumen untuk mahasiswa tersebut' : 'Tidak ada dokumen di sini' }}</h3></div></section>
    @endforelse
    <div class="mt-3">{{ $dokumen->links() }}</div>
@endsection
