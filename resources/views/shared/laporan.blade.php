{{-- Riwayat versi laporan (SDD Tabel 3.12). $mode: mahasiswa | pembimbing | admin --}}
@if ($daftarLaporan->isEmpty())
    <div class="pk-panel"><div class="pk-empty"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i><h3>Belum ada laporan</h3>
        <p>{{ $mode === 'mahasiswa' ? 'Unggah laporan KPI saat sudah siap diperiksa dosen pembimbing.' : 'Laporan dari mahasiswa akan tampil di sini.' }}</p></div></div>
@else
    @foreach ($daftarLaporan as $lap)
        <section class="pk-panel {{ $loop->first ? '' : 'pk-panel--muted' }}" id="laporan-{{ $lap->id }}">
            <div class="pk-panel-head">
                <div>
                    <h3 class="pk-panel-title">Versi {{ $lap->versi }} @if ($loop->first)<span class="text-secondary fw-normal">(terbaru)</span>@endif</h3>
                    <span class="small text-secondary">Diunggah {{ $lap->tanggal_upload->translatedFormat('d M Y H:i') }}@if ($lap->diperiksa_at), diperiksa {{ $lap->diperiksa_at->translatedFormat('d M Y H:i') }}@endif</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-status :value="$lap->status" />
                    @if ($mode === 'pembimbing')
                        <a href="{{ route('pembimbing.laporan.show', $lap) }}" class="btn btn-sm {{ $loop->first && $lap->status === 'menunggu' ? 'btn-primary' : 'btn-outline-primary' }}">{{ $loop->first && $lap->status === 'menunggu' ? 'Periksa laporan' : 'Buka' }}</a>
                    @endif
                </div>
            </div>
            <x-berkas :href="route('laporan.berkas', $lap)" :nama="$lap->nama_file" :lihat="true" class="mb-3" />
            @if ($lap->komentar_dosen)
                <div class="pk-feedback mb-3"><span class="fw-semibold d-block mb-1">Komentar dosen pembimbing</span>{!! nl2br(e($lap->komentar_dosen)) !!}</div>
            @endif
            @if ($lap->tanda->isNotEmpty())
                <h4 class="pk-subtitle">Bagian yang ditandai ({{ $lap->tanda_terbuka }} belum diperbaiki)</h4>
                @include('shared.tanda', ['laporan' => $lap, 'bisaKelola' => false])
            @endif
        </section>
    @endforeach
@endif
