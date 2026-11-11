{{-- Grafik batang vertikal CSS. $data: [label => nilai]; $sorot: label yang ditonjolkan --}}
@props(['data', 'satuan' => '', 'sorot' => null, 'maks' => null])
@php $maks = $maks ?? max(max(array_values($data ?: [0])), 1); @endphp
<div {{ $attributes->merge(['class' => 'pk-vbars']) }} role="img" aria-label="{{ collect($data)->map(fn ($v, $k) => $k.' '.$v.$satuan)->implode(', ') }}">
    @foreach ($data as $label => $nilai)
        <div class="pk-vbar {{ $sorot === $label ? 'is-highlight' : '' }}">
            <span class="pk-vbar-value">{{ $nilai }}{{ $satuan }}</span>
            <span class="pk-vbar-track"><span class="pk-vbar-fill" style="height: {{ $nilai / $maks * 100 }}%"></span></span>
            <span class="pk-vbar-label">{{ $label }}</span>
        </div>
    @endforeach
</div>
