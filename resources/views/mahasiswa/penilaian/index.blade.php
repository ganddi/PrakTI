@extends('layouts.app')
@section('judul', 'Penilaian KPI')
@section('subjudul', 'Nilai dosen pembimbing, dosen penguji, dan pembimbing lapangan')

@section('content')
    @if (! $pengajuan)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-clipboard-check" aria-hidden="true"></i><h3>Penilaian belum tersedia</h3><p>Penilaian dibuka setelah laporan disetujui dosen pembimbing.</p></div></section>
    @else
        @php
            $pPenguji = $pengajuan->penilaianJenis('dosen_penguji'); $pLapangan = $pengajuan->penilaianJenis('pembimbing_lapangan');
            $dokPenguji = $pengajuan->dokumenTerakhir('penilaian_penguji'); $dokLapangan = $pengajuan->dokumenTerakhir('penilaian_lapangan');
            $laporanOk = $pengajuan->laporanTerakhir?->status === 'disetujui'; $pb = $pengajuan->penilaianJenis('dosen_pembimbing');
        @endphp
        <section class="pk-panel mb-4">
            <div class="pk-panel-head"><h2 class="pk-panel-title">Komponen nilai</h2><a href="{{ route('mahasiswa.nilai.index') }}" class="small fw-semibold">Rekapitulasi nilai</a></div>
            @include('shared.penilaian', ['pengajuan' => $pengajuan])
            @if ($pb?->status === 'final' && $pb->rincian)
                <details class="mt-3"><summary class="small fw-semibold">Rincian nilai dosen pembimbing</summary>
                    <table class="table pk-table mt-2 mb-0"><tbody>
                        @foreach (config('prakti.kriteria_pembimbing') as $kunci => $k)<tr><td>{{ $k['label'] }} <span class="text-secondary">({{ $k['bobot'] }}%)</span></td><td class="text-end">{{ number_format($pb->rincian[$kunci] ?? 0, 2, ',', '.') }}</td></tr>@endforeach
                    </tbody></table>
                    @if ($pb->catatan_teks)<p class="small mt-2 mb-0"><span class="fw-semibold">Catatan:</span> {{ $pb->catatan_teks }}</p>@endif
                </details>
            @endif
        </section>
        <section class="pk-panel">
            <h2 class="pk-panel-title mb-1">Input nilai dosen penguji dan pembimbing lapangan</h2>
            <p class="small text-secondary mb-3">Masukkan nilai sesuai dokumen penilaian yang Anda terima, lalu unggah dokumennya untuk diverifikasi admin.</p>
            @if ($template->isNotEmpty())<div class="mb-3"><div class="small fw-semibold mb-1">Template penilaian</div>@foreach ($template as $t)<x-berkas :href="route('template.unduh', $t->kode)" :nama="$t->nama" :ukuran="$t->ukuran" class="mb-2" />@endforeach</div>@endif
            @foreach ([[$dokPenguji, 'Dokumen penguji'], [$dokLapangan, 'Dokumen pembimbing lapangan']] as [$dok, $label])
                @if ($dok)<div class="d-flex flex-wrap align-items-center gap-2 small mb-2"><span class="fw-semibold">{{ $label }}:</span><x-status :value="$dok->status_verifikasi" />@if ($dok->catatan)<span class="text-danger">{{ $dok->catatan }}</span>@endif</div>@endif
            @endforeach
            @if (! $laporanOk && $pengajuan->status === 'berjalan')
                <div class="alert alert-secondary mb-0">Form ini dibuka setelah laporan KPI Anda disetujui dosen pembimbing.</div>
            @elseif ($bisaUbah)
                <form method="POST" action="{{ route('mahasiswa.penilaian.eksternal') }}" enctype="multipart/form-data" data-confirm="Simpan nilai dan kirim dokumen penilaian untuk diverifikasi admin?">@csrf
                    <div class="pk-form-section"><h2>Dosen penguji</h2>
                        <div class="row g-3"><div class="col-md-8"><x-input name="nama_penguji" label="Nama dosen penguji" :value="$pPenguji?->nama_penilai" :required="true" /></div>
                            <div class="col-md-4"><x-input name="nilai_penguji" label="Nilai (0–100)" type="number" step="0.01" min="0" max="100" :value="$pPenguji?->nilai" :required="true" /></div></div>
                        <x-input name="dokumen_penguji" label="Dokumen penilaian penguji" type="file" accept=".pdf,.jpg,.jpeg,.png" :required="! $dokPenguji" :hint="$dokPenguji ? 'Sudah diunggah. Unggah lagi hanya jika mengganti.' : 'PDF, JPG, atau PNG, maksimal 5 MB.'" /></div>
                    <div class="pk-form-section"><h2>Pembimbing lapangan</h2>
                        <div class="row g-3"><div class="col-md-8"><x-input name="nama_pembimbing_lapangan" label="Nama pembimbing lapangan" :value="$pLapangan?->nama_penilai ?? $pengajuan->pembimbing_lapangan" :required="true" /></div>
                            <div class="col-md-4"><x-input name="nilai_lapangan" label="Nilai (0–100)" type="number" step="0.01" min="0" max="100" :value="$pLapangan?->nilai" :required="true" /></div></div>
                        <x-input name="dokumen_lapangan" label="Dokumen penilaian pembimbing lapangan" type="file" accept=".pdf,.jpg,.jpeg,.png" :required="! $dokLapangan" :hint="$dokLapangan ? 'Sudah diunggah. Unggah lagi hanya jika mengganti.' : 'PDF, JPG, atau PNG, maksimal 5 MB.'" /></div>
                    <button class="btn btn-primary" type="submit">Simpan nilai dan dokumen</button>
                </form>
            @else
                <p class="text-secondary mb-0">Nilai eksternal sudah diverifikasi admin dan terkunci.</p>
            @endif
        </section>
    @endif
@endsection
