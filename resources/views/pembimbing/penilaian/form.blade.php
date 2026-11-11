@extends('layouts.app')
@section('judul', 'Input nilai: '.$pengajuan->mahasiswa->nama_lengkap)

@section('content')
    <a href="{{ route('pembimbing.penilaian.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke nilai mahasiswa</a>
    <div class="row g-4">
        <div class="col-lg-8">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-1">Template penilaian dosen pembimbing</h2>
                <p class="small text-secondary mb-3">Isi nilai 0–100 per kriteria. Nilai dosen dihitung dari rata-rata tertimbang dan menyumbang {{ config('prakti.bobot_nilai_akhir.pembimbing') }}% nilai akhir.</p>
                <form method="POST" action="{{ route('pembimbing.penilaian.update', $pengajuan) }}" data-hitung-nilai>@csrf @method('PUT')
                    <div class="table-responsive"><table class="table pk-table align-middle">
                        <thead><tr><th>Kriteria</th><th class="text-end">Bobot</th><th style="width: 140px">Nilai</th></tr></thead>
                        <tbody>
                        @foreach (config('prakti.kriteria_pembimbing') as $kunci => $k)
                            <tr><td><label for="nilai-{{ $kunci }}" class="fw-semibold">{{ $k['label'] }}</label></td><td class="text-end">{{ $k['bobot'] }}%</td>
                                <td><input type="number" id="nilai-{{ $kunci }}" name="nilai[{{ $kunci }}]" min="0" max="100" step="0.01" required class="form-control text-end" data-bobot="{{ $k['bobot'] }}" value="{{ old('nilai.'.$kunci, $penilaian?->rincian[$kunci] ?? '') }}"></td></tr>
                        @endforeach
                        </tbody>
                        <tfoot><tr><th colspan="2">Nilai dosen pembimbing</th><th class="text-end"><output data-hasil class="pk-final">–</output></th></tr></tfoot>
                    </table></div>
                    <x-input name="catatan" label="Catatan untuk mahasiswa" type="textarea" rows="3" :value="$penilaian?->catatan_teks" />
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary" type="submit" name="aksi" value="draf">Simpan draf</button>
                        <button class="btn btn-primary" type="submit" name="aksi" value="tetapkan" data-konfirmasi="Tetapkan nilai? Setelah ditetapkan, nilai tampil ke mahasiswa dan tidak dapat diubah.">Tetapkan nilai</button>
                    </div>
                </form>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Ringkasan mahasiswa</h2>
                <dl class="pk-info pk-info--stack">
                    <div><dt>Mahasiswa</dt><dd>{{ $pengajuan->mahasiswa->nama_lengkap }}<span class="d-block fw-normal small text-secondary">{{ $pengajuan->mahasiswa->nim }}</span></dd></div>
                    <div><dt>Instansi</dt><dd>{{ $pengajuan->nama_instansi }}</dd></div>
                    <div><dt>Logbook</dt><dd>{{ $pengajuan->logbooks_count }} hari tercatat</dd></div>
                    <div><dt>Laporan</dt><dd>Versi {{ $pengajuan->laporanTerakhir->versi }} disetujui</dd></div>
                    @if ($penilaian)<div><dt>Status nilai</dt><dd><x-status :value="$penilaian->status" /></dd></div>@endif
                </dl>
            </section>
        </div>
    </div>
@endsection
