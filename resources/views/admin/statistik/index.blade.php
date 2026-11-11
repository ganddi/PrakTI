@extends('layouts.app')
@section('judul', 'Statistik dan riwayat institusi')

@section('content')
    <form method="GET" class="pk-filterbar mb-3">
        <label for="tahun" class="small text-secondary">Tahun pelaksanaan</label>
        <select id="tahun" name="tahun" class="form-select" onchange="this.form.submit()">
            <option value="">Semua tahun</option>
            @foreach ($daftarTahun as $t)<option value="{{ $t }}" @selected((string) $tahun === (string) $t)>{{ $t }}</option>@endforeach
        </select>
    </form>

    @if ($total === 0)
        <section class="pk-panel"><div class="pk-empty">
            <i class="bi bi-bar-chart" aria-hidden="true"></i>
            <h3>Data statistik belum tersedia</h3>
            <p>Statistik dihitung dari KPI yang sedang berjalan atau sudah selesai.</p>
        </div></section>
    @else
        <div class="pk-summary mb-4">
            <div class="pk-summary-item"><span class="pk-summary-value">{{ $total }}</span><span class="pk-summary-label">Mahasiswa melaksanakan KPI</span></div>
            <div class="pk-summary-item"><span class="pk-summary-value">{{ $selesai }}</span><span class="pk-summary-label">Sudah selesai</span></div>
            <div class="pk-summary-item"><span class="pk-summary-value">{{ $institusi->count() }}</span><span class="pk-summary-label">Institusi digunakan</span></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Jumlah mahasiswa per tahun</h2>
                    @php $maks = max($perTahun->max() ?: 1, 1); @endphp
                    <div class="pk-bars">
                        @foreach ($perTahun as $th => $jml)
                            <div class="pk-bar-row"><span class="pk-bar-label">{{ $th }}</span><span class="pk-bar-track"><span class="pk-bar-fill" style="width: {{ $jml / $maks * 100 }}%"></span></span><span class="pk-bar-value">{{ $jml }}</span></div>
                        @endforeach
                    </div>
                    <h2 class="pk-panel-title mt-4 mb-3">Jumlah mahasiswa per periode</h2>
                    @php $maksB = max($perPeriode->max('total') ?: 1, 1); @endphp
                    <div class="pk-bars">
                        @foreach ($perPeriode as $b)
                            <div class="pk-bar-row"><span class="pk-bar-label">{{ $b['nama'] }}<span class="d-block small text-secondary">{{ $b['tahun'] }}</span></span><span class="pk-bar-track"><span class="pk-bar-fill" style="width: {{ $b['total'] / $maksB * 100 }}%"></span></span><span class="pk-bar-value">{{ $b['total'] }}</span></div>
                        @endforeach
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="pk-panel h-100">
                    <h2 class="pk-panel-title mb-3">Distribusi mahasiswa per institusi{{ $tahun ? ' ('.$tahun.')' : '' }}</h2>
                    @php $maksI = max($institusi->max('total') ?: 1, 1); @endphp
                    <div class="pk-bars">
                        @foreach ($institusi->take(10) as $i)
                            <div class="pk-bar-row"><span class="pk-bar-label">{{ $i['nama'] }}</span><span class="pk-bar-track"><span class="pk-bar-fill pk-bar-fill--accent" style="width: {{ $i['total'] / $maksI * 100 }}%"></span></span><span class="pk-bar-value">{{ $i['total'] }}</span></div>
                        @endforeach
                    </div>
                    @if ($institusi->count() > 10)<p class="small text-secondary mt-2 mb-0">Menampilkan 10 institusi teratas. Daftar lengkap ada di tabel riwayat.</p>@endif
                </section>
            </div>
        </div>

        <section class="pk-panel mt-4">
            <h2 class="pk-panel-title mb-3">Riwayat institusi KPI</h2>
            <div class="table-responsive">
                <table class="table pk-table">
                    <thead><tr><th>Institusi</th><th class="text-end">Total mahasiswa</th><th>Tahun digunakan</th><th>Terakhir digunakan</th></tr></thead>
                    <tbody>
                    @foreach ($institusi as $i)
                        <tr>
                            <td class="fw-semibold">{{ $i['nama'] }}<span class="pk-sub">{{ $i['kota'] }}</span></td>
                            <td class="text-end">{{ $i['total'] }}</td>
                            <td>{{ $i['tahun']->implode(', ') }}</td>
                            <td class="text-nowrap">{{ $i['terakhir']->translatedFormat('F Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
