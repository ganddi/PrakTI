@extends('layouts.app')
@section('judul', 'Dokumen')
@section('subjudul', 'Dokumen KPI mahasiswa bimbingan')

@section('content')
    <form method="GET" class="pk-filterbar mb-3"><select name="jenis" class="form-select" aria-label="Jenis dokumen" onchange="this.form.submit()"><option value="">Semua jenis</option>
        @foreach (\App\Models\DokumenKpi::JENIS as $k => $l)<option value="{{ $k }}" @selected(request('jenis') === $k)>{{ $l }}</option>@endforeach</select></form>
    <section class="pk-panel">
        <div class="table-responsive"><table class="table pk-table">
            <thead><tr><th>Mahasiswa</th><th>Jenis</th><th>Berkas</th><th>Diunggah</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($dokumen as $d)
                <tr><td class="fw-semibold">{{ $d->pengajuan->mahasiswa->nama_lengkap }}<span class="pk-sub">{{ $d->pengajuan->mahasiswa->nim }}</span></td><td>{{ $d->jenis_label }}</td>
                    <td class="text-break">{{ $d->nama_file }}<span class="pk-sub">{{ strtoupper($d->format_file) }}, {{ $d->ukuran_format }}</span></td>
                    <td class="text-nowrap">{{ $d->uploaded_at->translatedFormat('d M Y') }}</td><td><x-status :value="$d->status_verifikasi" /></td>
                    <td class="text-end"><a href="{{ route('dokumen.unduh', $d) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a></td></tr>
            @empty<tr><td colspan="6" class="text-secondary">Belum ada dokumen.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $dokumen->links() }}</div>
    </section>
@endsection
