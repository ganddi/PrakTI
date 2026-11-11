{{-- Riwayat aktivitas (SDD Tabel 3.16) --}}
@if ($aktivitas->isEmpty())
    <p class="text-secondary mb-0">Belum ada aktivitas yang tercatat.</p>
@else
    <ol class="pk-history">
        @foreach ($aktivitas as $a)
            <li>
                <span class="pk-history-time">{{ $a->created_at->translatedFormat('d M Y, H:i') }}</span>
                <span class="pk-history-text">
                    @if (isset($namaMahasiswa) && isset($namaMahasiswa[$a->referensi_id]))<span class="fw-semibold">{{ $namaMahasiswa[$a->referensi_id] }}:</span>@endif
                    {{ $a->aktivitas }}@if ($a->deskripsi)<span class="text-secondary"> ({{ $a->deskripsi }})</span>@endif
                    <span class="text-secondary"> oleh {{ $a->user?->name ?? 'sistem' }}</span>
                </span>
            </li>
        @endforeach
    </ol>
@endif
