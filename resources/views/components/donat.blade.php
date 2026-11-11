{{-- Grafik donat CSS. $data: [label => [nilai, warna]] --}}
@props(['data', 'tengah' => null, 'ket' => null])
@php
    $total = max(array_sum(array_column($data, 0)), 1);
    $posisi = 0; $irisan = [];
    foreach ($data as [$n, $w]) { $akhir = $posisi + $n / $total * 100; $irisan[] = "{$w} {$posisi}% {$akhir}%"; $posisi = $akhir; }
@endphp
<div {{ $attributes->merge(['class' => 'pk-donut-wrap']) }}>
    <div class="pk-donut" style="background: conic-gradient({{ implode(', ', $irisan) ?: 'var(--pk-ink-soft) 0 100%' }})" role="img"
         aria-label="{{ collect($data)->map(fn ($d, $l) => $l.' '.$d[0])->implode(', ') }}">
        <span class="pk-donut-center"><strong>{{ $tengah ?? array_sum(array_column($data, 0)) }}</strong>@if ($ket)<small>{{ $ket }}</small>@endif</span>
    </div>
    <ul class="pk-legend">
        @foreach ($data as $label => [$n, $w])
            <li><span class="pk-legend-dot" style="background: {{ $w }}"></span><span class="flex-grow-1">{{ $label }}</span><strong>{{ $n }}</strong></li>
        @endforeach
    </ul>
</div>
