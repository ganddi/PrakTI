{{-- Bagian laporan yang ditandai dosen (kolom halaman_revisi, FR-15) --}}
@if ($laporan->tanda->isEmpty())
    <p class="text-secondary mb-0">Belum ada bagian yang ditandai.</p>
@else
    <ol class="pk-notes">
        @foreach ($laporan->tanda as $t)
            @php $selesai = ($t->status ?? 'terbuka') === 'selesai'; @endphp
            <li class="pk-note {{ $selesai ? 'is-done' : '' }}">
                <div class="pk-note-head">
                    <span class="pk-note-page">{{ ! empty($t->halaman) ? 'Halaman '.$t->halaman : 'Umum' }}</span>
                    <x-status :value="$selesai ? 'diperbaiki' : 'terbuka'" />
                </div>
                @if (! empty($t->kutipan))<blockquote class="pk-note-quote"><mark>{{ $t->kutipan }}</mark></blockquote>@endif
                <p class="pk-note-text">{!! nl2br(e($t->catatan)) !!}</p>
                @if ($bisaKelola ?? false)
                    <div class="pk-note-foot">
                        <span class="small text-secondary">{{ isset($t->waktu) ? \Illuminate\Support\Carbon::parse($t->waktu)->translatedFormat('d M Y H:i') : '' }}</span>
                        <form method="POST" action="{{ route('pembimbing.laporan.tanda.ubah', [$laporan, $t->indeks]) }}" class="d-inline">@csrf @method('PATCH')
                            <button class="btn btn-sm btn-link p-0" type="submit">{{ $selesai ? 'Buka kembali' : 'Tandai sudah diperbaiki' }}</button></form>
                        <form method="POST" action="{{ route('pembimbing.laporan.tanda.hapus', [$laporan, $t->indeks]) }}" class="d-inline" data-confirm="Hapus tanda ini?">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0 ms-2" type="submit">Hapus</button></form>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
@endif
