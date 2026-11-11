{{-- Ringkasan nilai (SDD Tabel 3.13 dan 3.14) --}}
@php
    $na = $pengajuan->nilaiAkhir;
    $bobot = $na ? ['dosen_pembimbing' => $na->bobot_dosen_pembimbing, 'dosen_penguji' => $na->bobot_dosen_penguji, 'pembimbing_lapangan' => $na->bobot_pembimbing_lapangan]
        : ['dosen_pembimbing' => config('prakti.bobot_nilai_akhir.pembimbing'), 'dosen_penguji' => config('prakti.bobot_nilai_akhir.penguji'), 'pembimbing_lapangan' => config('prakti.bobot_nilai_akhir.lapangan')];
    $f = fn ($n) => $n === null ? '–' : number_format($n, 2, ',', '.');
@endphp
<div class="table-responsive">
    <table class="table pk-table pk-table--nilai">
        <thead><tr><th>Komponen</th><th>Penilai</th><th>Status</th><th class="text-end">Bobot</th><th class="text-end">Nilai</th></tr></thead>
        <tbody>
        @foreach (\App\Models\Penilaian::JENIS as $jenis => $label)
            @php $p = $pengajuan->penilaianJenis($jenis); $tampil = $p && ($jenis !== 'dosen_pembimbing' || $p->status === 'final' || ($tampilkanDraf ?? false)); @endphp
            <tr>
                <td class="fw-semibold">{{ $label }}</td>
                <td>{{ $p?->nama_penilai ?? ($jenis === 'dosen_pembimbing' ? $pengajuan->pembimbing?->nama_lengkap : null) ?? '–' }}</td>
                <td>@if ($p)<x-status :value="$p->status" />@else<x-status value="belum" />@endif</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($bobot[$jenis], 2, ',', ''), '0'), ',') }}%</td>
                <td class="text-end">{{ $tampil ? $f($p->nilai) : '–' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><th colspan="4">Nilai akhir</th><th class="text-end">
            @if ($na)<span class="pk-final">{{ $f($na->nilai_akhir) }} <span class="pk-grade">{{ $na->predikat }}</span></span>
            @else<span class="text-secondary fw-normal">Belum dapat dihitung</span>@endif
        </th></tr></tfoot>
    </table>
</div>
@unless ($na)
    @php $kurang = \App\Models\NilaiAkhir::belumLengkap($pengajuan->mahasiswa_id); @endphp
    @if ($kurang)<p class="small text-secondary mb-0">Masih menunggu: {{ implode(', ', $kurang) }}.</p>@endif
@endunless
