@extends('layouts.app')
@section('judul', 'Nilai mahasiswa')
@section('subjudul', 'Daftar mahasiswa bimbingan dan status penilaian')

@section('content')
    <section class="pk-panel">
        <form method="GET" class="pk-filterbar mb-3" role="search"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau NIM" aria-label="Cari mahasiswa"><button class="btn btn-outline-primary" type="submit">Cari</button></form>
        <div class="table-responsive"><table class="table pk-table">
            <thead><tr><th>No</th><th>Nama mahasiswa</th><th>NIM</th><th>Instansi</th><th>Nilai dosen</th><th>Nilai akhir</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse ($daftar as $i => $p)
                @php $pb = $p->penilaianJenis('dosen_pembimbing'); $bisa = $p->laporanTerakhir?->status === 'disetujui'; @endphp
                <tr>
                    <td>{{ $daftar->firstItem() + $i }}</td>
                    <td class="fw-semibold">{{ $p->mahasiswa->nama_lengkap }}</td><td>{{ $p->mahasiswa->nim }}</td><td>{{ $p->nama_instansi }}</td>
                    <td>{{ $pb ? number_format($pb->nilai, 2, ',', '.') : '–' }} @if ($pb)<x-status :value="$pb->status" />@endif</td>
                    <td class="text-nowrap">{{ $p->nilaiAkhir ? number_format($p->nilaiAkhir->nilai_akhir, 2, ',', '.').' ('.$p->nilaiAkhir->predikat.')' : '–' }}</td>
                    <td><x-status :value="$p->status" /></td>
                    <td class="text-end">
                        @if ($pb?->status === 'final')<span class="small text-secondary">Sudah ditetapkan</span>
                        @elseif ($bisa)<a href="{{ route('pembimbing.penilaian.edit', $p) }}" class="btn btn-sm btn-primary">Input nilai</a>
                        @else<span class="small text-secondary" title="Penilaian dibuka setelah laporan disetujui">Menunggu laporan</span>@endif
                    </td>
                </tr>
            @empty<tr><td colspan="8" class="text-secondary">Belum ada mahasiswa bimbingan.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $daftar->links() }}</div>
    </section>
@endsection
