{{-- Informasi berkas: nama, jenis, ukuran, unduh (Look & Feel #19) --}}
@props(['href', 'nama', 'ukuran' => null, 'lihat' => false])
@php $ext = strtoupper(pathinfo($nama, PATHINFO_EXTENSION)); @endphp
<div {{ $attributes->merge(['class' => 'pk-file']) }}>
    <span class="pk-file-icon" aria-hidden="true">{{ $ext ?: 'FILE' }}</span>
    <span class="pk-file-meta">
        <span class="pk-file-name">{{ $nama }}</span>
        @if ($ukuran)<span class="pk-file-size">{{ $ext }}, {{ $ukuran }}</span>@endif
    </span>
    <span class="pk-file-actions">
        @if ($lihat && $ext === 'PDF')
            <a href="{{ $href }}{{ str_contains($href, '?') ? '&' : '?' }}lihat=1" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Lihat</a>
        @endif
        <a href="{{ $href }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1" aria-hidden="true"></i>Unduh</a>
        {{ $slot }}
    </span>
</div>
