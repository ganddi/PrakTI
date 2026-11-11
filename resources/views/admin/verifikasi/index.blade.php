@extends('layouts.app')
@section('judul', 'Verifikasi dokumen')

@section('content')
    <ul class="nav pk-tabs" role="tablist">
        @foreach (\App\Http\Controllers\Admin\VerifikasiDokumenController::KELOMPOK as $k => $g)
            <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('admin.verifikasi.index', ['tab' => $k]) }}">{{ $g['label'] }} @if ($jumlah[$k])<span class="pk-count-badge">{{ $jumlah[$k] }}</span>@endif</a></li>
        @endforeach
    </ul>
    <ul class="nav pk-filter mb-3">
        @foreach (['menunggu' => 'Menunggu', 'revisi' => 'Diminta perbaikan', 'diverifikasi' => 'Terverifikasi'] as $k => $l)
            <li class="nav-item"><a class="nav-link {{ $status === $k ? 'active' : '' }}" href="{{ route('admin.verifikasi.index', ['tab' => $tab, 'status' => $k]) }}">{{ $l }}</a></li>
        @endforeach
    </ul>
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
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-folder-check" aria-hidden="true"></i><h3>Tidak ada dokumen di sini</h3></div></section>
    @endforelse
    <div class="mt-3">{{ $dokumen->links() }}</div>
@endsection
