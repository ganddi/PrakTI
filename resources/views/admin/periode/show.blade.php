@extends('layouts.app')
@section('judul', $periode->nama_periode)
@section('subjudul', $periode->kode_periode.', '.$periode->tanggal_mulai->translatedFormat('d M Y').' – '.$periode->tanggal_selesai->translatedFormat('d M Y'))

@section('content')
    <a href="{{ route('admin.periode.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar periode</a>
    @if ($siap->isNotEmpty())
        <div class="pk-reminder"><i class="bi bi-people" aria-hidden="true"></i>
            <div class="flex-grow-1">{{ $siap->count() }} mahasiswa sudah terverifikasi surat balasannya dan siap dikelompokkan.</div>
            <form method="POST" action="{{ route('admin.kelompok.otomatis', $periode) }}" data-confirm="Bentuk kelompok otomatis untuk {{ $siap->count() }} mahasiswa berdasarkan tempat KPI?">@csrf
                <button class="btn btn-sm btn-primary" type="submit">Kelompokkan otomatis per tempat KPI</button></form>
        </div>
    @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="pk-panel">
                <div class="pk-panel-head">
                    <h2 class="pk-panel-title">Kelompok KPI</h2>
                    <a href="{{ route('admin.penempatan.index', ['periode' => $periode->id]) }}" class="btn btn-sm btn-outline-primary">Atur penempatan dosen</a>
                </div>
                @if ($periode->kelompok->isEmpty())
                    <div class="pk-empty"><i class="bi bi-diagram-3" aria-hidden="true"></i><h3>Belum ada kelompok</h3><p>Buat kelompok di samping atau kelompokkan otomatis.</p></div>
                @else
                    <div class="table-responsive"><table class="table pk-table">
                        <thead><tr><th>Kelompok</th><th>Tempat KPI anggota</th><th class="text-end">Anggota</th><th>Dosen pembimbing</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($periode->kelompok as $k)
                            @php $dosen = $k->penempatan->where('status', 'disetujui')->pluck('dosen.nama_lengkap')->unique(); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $k->nama_kelompok }}<span class="pk-sub">{{ $k->kode_kelompok }}</span></td>
                                <td>{{ $k->nama_instansi }}</td>
                                <td class="text-end">{{ $k->anggota_aktif_count }}</td>
                                <td>{!! $dosen->isNotEmpty() ? e($dosen->implode(', ')) : '<span class="text-secondary">Belum ditetapkan</span>' !!}</td>
                                <td class="text-end"><a href="{{ route('admin.kelompok.show', $k) }}" class="btn btn-sm btn-outline-primary">Kelola</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>
            <details class="pk-panel mt-4"><summary class="fw-semibold">Ubah atau hapus periode</summary>
                <form method="POST" action="{{ route('admin.periode.update', $periode) }}" class="mt-3">@csrf @method('PUT')
                    @include('admin.periode._form', ['pr' => $periode])<button class="btn btn-primary" type="submit">Simpan perubahan</button></form>
                <form method="POST" action="{{ route('admin.periode.destroy', $periode) }}" class="mt-2" data-confirm="Hapus periode ini?">@csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">Hapus periode</button></form>
            </details>
        </div>
        <div class="col-lg-4">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Buat kelompok</h2>
                <form method="POST" action="{{ route('admin.kelompok.store', $periode) }}">@csrf
                    @php $n = $periode->kelompok->count() + 1; @endphp
                    @include('admin.kelompok._form', ['k' => new \App\Models\KelompokKpi(['kode_kelompok' => $periode->kode_periode.'-K'.str_pad($n, 2, '0', STR_PAD_LEFT), 'nama_kelompok' => 'Kelompok '.$n])])
                    <button class="btn btn-primary w-100" type="submit">Buat kelompok</button>
                </form>
            </section>
        </div>
    </div>
@endsection
