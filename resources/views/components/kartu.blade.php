{{-- Kartu ringkasan (Look & Feel #13) --}}
@props(['ikon', 'label', 'nilai', 'ket' => null, 'warna' => 'blue', 'href' => null])
@php $isi = '<span class="pk-stat-icon"><i class="bi '.e($ikon).'" aria-hidden="true"></i></span><span class="pk-stat-body"><span class="pk-stat-label">'.e($label).'</span><span class="pk-stat-value">'.e($nilai).'</span>'.($ket ? '<span class="pk-stat-ket">'.e($ket).'</span>' : '').'</span>'; @endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "pk-stat pk-stat--{$warna}"]) }}>{!! $isi !!}</a>
@else
    <div {{ $attributes->merge(['class' => "pk-stat pk-stat--{$warna}"]) }}>{!! $isi !!}</div>
@endif
