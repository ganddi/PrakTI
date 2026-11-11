@extends('layouts.app')
@section('judul', $kelompok->nama_kelompok)
@section('subjudul', $kelompok->kode_kelompok.', '.$kelompok->periode->nama_periode)

@section('content')
    <a href="{{ route('admin.periode.show', $kelompok->periode) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke {{ $kelompok->periode->nama_periode }}</a>
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="pk-panel">
                <div class="pk-panel-head"><h2 class="pk-panel-title">Anggota kelompok</h2><x-status :value="$kelompok->status" /></div>
                @if ($kelompok->anggota->isEmpty())
                    <p class="text-secondary">Belum ada anggota.</p>
                @else
                    <div class="table-responsive"><table class="table pk-table">
                        <thead><tr><th>Mahasiswa</th><th>Tempat KPI</th><th>Masuk</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($kelompok->anggota as $a)
                            @php $p = $a->mahasiswa->pengajuanTerakhir; @endphp
                            <tr>
                                <td class="fw-semibold">@if ($p)<a href="{{ route('admin.pengajuan.show', $p) }}" class="text-body">{{ $a->mahasiswa->nama_lengkap }}</a>@else{{ $a->mahasiswa->nama_lengkap }}@endif<span class="pk-sub">{{ $a->mahasiswa->nim }}</span></td>
                                <td>{{ $p?->tempatKpi?->nama_instansi ?? '–' }}</td>
                                <td class="text-nowrap">{{ $a->tanggal_masuk->translatedFormat('d M Y') }}</td>
                                <td><x-status :value="$a->status" /></td>
                                <td class="text-end">@if ($a->status === 'aktif')
                                    <form method="POST" action="{{ route('admin.kelompok.anggota.hapus', $a) }}" data-confirm="Keluarkan {{ $a->mahasiswa->nama_lengkap }} dari kelompok?">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Keluarkan</button></form>@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>
            <section class="pk-panel">
                <h3 class="pk-panel-title mb-1">Tambah anggota</h3>
                <p class="small text-secondary">Mahasiswa periode ini dengan pengajuan disetujui dan surat balasan terverifikasi. Tempat KPI yang sama dengan kelompok ditampilkan paling atas.</p>
                @if ($kandidat->isEmpty())<p class="text-secondary mb-0">Tidak ada mahasiswa yang siap ditempatkan.</p>
                @else
                    <form method="POST" action="{{ route('admin.kelompok.anggota.tambah', $kelompok) }}">@csrf
                        <div class="pk-checklist mb-3">
                            @foreach ($kandidat as $c)
                                @php $sama = $tempatKelompok->contains($c->tempat_kpi_id); @endphp
                                <label class="pk-check-row"><input class="form-check-input" type="checkbox" name="pengajuan_id[]" value="{{ $c->id }}" @checked($sama || $tempatKelompok->isEmpty() && $loop->first)>
                                    <span><span class="fw-semibold">{{ $c->mahasiswa->nama_lengkap }}</span> <span class="text-secondary">{{ $c->mahasiswa->nim }}</span>
                                        <span class="d-block small {{ $sama ? 'text-success' : 'text-secondary' }}">{{ $c->nama_instansi }}{{ $sama ? ', tempat sama' : '' }}</span></span></label>
                            @endforeach
                        </div>
                        <button class="btn btn-primary" type="submit">Tambahkan ke {{ $kelompok->nama_kelompok }}</button>
                    </form>
                @endif
            </section>
        </div>
        <div class="col-lg-4">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Penempatan dosen</h2>
                @forelse ($kelompok->penempatan as $pn)
                    <div class="small py-1">{{ $pn->dosen->nama_lengkap }} <span class="text-secondary">untuk {{ $pn->tempatKpi->nama_instansi }}</span> <x-status :value="$pn->status" /></div>
                @empty<p class="small text-secondary mb-0">Belum dibagikan ke dosen.</p>@endforelse
                <a href="{{ route('admin.penempatan.index', ['periode' => $kelompok->periode_id]) }}" class="btn btn-sm btn-outline-primary mt-2">Atur penempatan</a>
            </section>
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Ubah kelompok</h2>
                <form method="POST" action="{{ route('admin.kelompok.update', $kelompok) }}">@csrf @method('PUT')
                    @include('admin.kelompok._form', ['k' => $kelompok])
                    <div class="mb-3"><label for="status-kelompok" class="form-label">Status</label><select id="status-kelompok" name="status" class="form-select">
                        @foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai'] as $sk => $sl)<option value="{{ $sk }}" @selected($kelompok->status === $sk)>{{ $sl }}</option>@endforeach</select></div>
                    <button class="btn btn-primary w-100" type="submit">Simpan</button>
                </form>
                <form method="POST" action="{{ route('admin.kelompok.destroy', $kelompok) }}" class="mt-2" data-confirm="Hapus {{ $kelompok->nama_kelompok }}?">@csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100" type="submit">Hapus kelompok</button></form>
            </section>
        </div>
    </div>
@endsection
