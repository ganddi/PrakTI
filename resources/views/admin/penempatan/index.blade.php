@extends('layouts.app')
@section('judul', 'Penempatan dosen pembimbing')

@section('content')
    @if (! $periode)
        <section class="pk-panel"><div class="pk-empty"><i class="bi bi-collection" aria-hidden="true"></i><h3>Belum ada periode KPI</h3><a href="{{ route('admin.periode.index') }}" class="btn btn-primary">Buat periode</a></div></section>
    @else
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <p class="pk-lead mb-0">Satu penempatan mencakup satu kelompok dan satu tempat KPI. Bagikan ke dosen, dosen memilih, lalu Anda memverifikasi.</p>
            <form method="GET" class="d-flex gap-2 align-items-center"><label for="periode" class="small text-secondary">Periode</label>
                <select id="periode" name="periode" class="form-select" onchange="this.form.submit()">@foreach ($daftarPeriode as $pr)<option value="{{ $pr->id }}" @selected($pr->id === $periode->id)>{{ $pr->nama_periode }}</option>@endforeach</select></form>
        </div>
        @if ($menunggu > 0)<div class="pk-reminder"><i class="bi bi-hourglass-split" aria-hidden="true"></i><div>{{ $menunggu }} pilihan dosen menunggu verifikasi.</div></div>@endif

        @forelse ($kelompok as $k)
            @foreach ($k->daftarTempat() as $t)
                @php
                    $rows = $k->penempatan->where('tempat_kpi_id', $t->id)->sortBy(fn ($p) => array_search($p->status, ['diajukan', 'disetujui', 'perlu_perbaikan', 'ditawarkan', 'dibatalkan']));
                    $disetujui = $rows->firstWhere('status', 'disetujui');
                    $jumlahMhs = $k->anggotaAktif->filter(fn ($a) => $a->mahasiswa->pengajuanTerakhir?->tempat_kpi_id === $t->id)->count();
                @endphp
                <section class="pk-panel">
                    <div class="pk-panel-head">
                        <div><h2 class="pk-panel-title">{{ $k->nama_kelompok }}: {{ $t->nama_instansi }}</h2><span class="small text-secondary">{{ $jumlahMhs }} mahasiswa, {{ $t->kota }}</span></div>
                        @if ($disetujui)<span><x-status value="disetujui" /> <span class="fw-semibold ms-1">{{ $disetujui->dosen->nama_lengkap }}</span></span>@else<x-status value="belum" />@endif
                    </div>
                    @if ($rows->isNotEmpty())
                        <div class="table-responsive mb-3"><table class="table pk-table">
                            <thead><tr><th>Dosen</th><th>Status</th><th>Tanggal pengajuan</th><th class="text-end">Tindakan</th></tr></thead>
                            <tbody>
                            @foreach ($rows as $p)
                                <tr>
                                    <td class="fw-semibold">{{ $p->dosen->nama_lengkap }}</td>
                                    <td><x-status :value="$p->status" />@if ($p->catatan_admin)<span class="pk-sub mt-1">{{ $p->catatan_admin }}</span>@endif</td>
                                    <td class="text-nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                                    <td class="text-end">
                                        @if ($p->status === 'diajukan')
                                            <form method="POST" action="{{ route('admin.penempatan.verifikasi', $p) }}" class="pk-inline-verify">@csrf @method('PATCH')
                                                <input type="text" name="catatan_admin" class="form-control form-control-sm" placeholder="Catatan (wajib jika perbaikan)" aria-label="Catatan">
                                                <button class="btn btn-sm btn-primary" type="submit" name="keputusan" value="setujui">Setujui</button>
                                                <button class="btn btn-sm btn-outline-danger" type="submit" name="keputusan" value="perbaikan" data-perlu-catatan="catatan_admin">Perlu perbaikan</button></form>
                                        @elseif ($p->status === 'disetujui')
                                            <form method="POST" action="{{ route('admin.penempatan.batalkan', $p) }}" data-confirm="Batalkan penempatan {{ $p->dosen->nama_lengkap }}?">@csrf @method('PATCH')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Batalkan</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table></div>
                    @endif
                    @unless ($disetujui)
                        @php $sudah = $rows->where('status', '!=', 'dibatalkan')->pluck('dosen_id')->all(); @endphp
                        <form method="POST" action="{{ route('admin.penempatan.tawarkan', $k) }}" class="pk-offer">@csrf
                            <input type="hidden" name="tempat_kpi_id" value="{{ $t->id }}">
                            <span class="small fw-semibold">Bagikan ke dosen:</span>
                            <div class="pk-offer-list">
                                @foreach ($dosen as $d)
                                    @continue(in_array($d->id, $sudah, true))
                                    <label class="pk-chip"><input type="checkbox" name="dosen_id[]" value="{{ $d->id }}"><span>{{ $d->nama_lengkap }} <span class="text-secondary">({{ $d->jumlah_disetujui }})</span></span></label>
                                @endforeach
                            </div>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Bagikan</button>
                        </form>
                    @endunless
                </section>
            @endforeach
        @empty
            <section class="pk-panel"><div class="pk-empty"><i class="bi bi-diagram-3" aria-hidden="true"></i><h3>Belum ada kelompok di periode ini</h3><a href="{{ route('admin.periode.show', $periode) }}" class="btn btn-primary">Buka periode</a></div></section>
        @endforelse
    @endif
@endsection
