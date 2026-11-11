@extends('layouts.app')
@section('judul', 'Pilihan penempatan')
@section('subjudul', 'Pilih tempat KPI yang dibagikan admin untuk Anda bimbing')

@section('content')
    <section class="pk-panel">
        <h2 class="pk-panel-title mb-3">Tersedia untuk dipilih</h2>
        @if ($tersedia->isEmpty())
            <div class="pk-empty"><i class="bi bi-geo-alt" aria-hidden="true"></i><h3>Belum ada tempat KPI yang dibagikan</h3><p>Anda akan menerima notifikasi saat admin membagikan tempat KPI.</p></div>
        @else
            <form method="POST" action="{{ route('pembimbing.penempatan.ajukan') }}" data-confirm="Kirim pilihan tempat KPI ke admin untuk diverifikasi?">@csrf
                <div class="pk-checklist mb-3">
                    @foreach ($tersedia as $p)
                        <label class="pk-check-row"><input class="form-check-input" type="checkbox" name="penempatan_id[]" value="{{ $p->id }}">
                            <span class="flex-grow-1"><span class="fw-semibold">{{ $p->tempatKpi->nama_instansi }}</span>
                                <span class="d-block small text-secondary">{{ $p->kelompok->nama_kelompok }}, {{ $p->kelompok->periode->nama_periode }}, {{ $p->jumlah_mahasiswa }} mahasiswa</span>
                                <span class="d-block small text-secondary"><i class="bi bi-geo-alt"></i> {{ $p->tempatKpi->alamat_lengkap }}</span>
                                @if ($p->status === 'perlu_perbaikan')<span class="d-block small text-danger mt-1"><x-status value="perlu_perbaikan" /> {{ $p->catatan_admin }}</span>@endif
                            </span></label>
                    @endforeach
                </div>
                <button class="btn btn-primary" type="submit">Kirim pilihan ke admin</button>
            </form>
        @endif
    </section>
    @if ($lainnya->isNotEmpty())
        <section class="pk-panel">
            <h2 class="pk-panel-title mb-3">Pilihan yang sudah dikirim</h2>
            <div class="table-responsive"><table class="table pk-table">
                <thead><tr><th>Tempat KPI</th><th>Kelompok</th><th>Tanggal pengajuan</th><th>Status</th><th>Catatan admin</th></tr></thead>
                <tbody>
                @foreach ($lainnya as $p)
                    <tr><td class="fw-semibold">{{ $p->tempatKpi->nama_instansi }}</td><td>{{ $p->kelompok->nama_kelompok }}<span class="pk-sub">{{ $p->jumlah_mahasiswa }} mahasiswa</span></td>
                        <td class="text-nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td><td><x-status :value="$p->status" /></td><td>{{ $p->catatan_admin ?: '–' }}</td></tr>
                @endforeach
                </tbody>
            </table></div>
        </section>
    @endif
@endsection
