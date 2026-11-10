@props(['nilai' => 0])
@php $nilai = max(0, min(100, (int) $nilai)); @endphp
<div {{ $attributes->merge(['class' => 'pk-meter']) }}>
    <div class="pk-meter-track" role="progressbar" aria-label="Progress KPI"
         aria-valuenow="{{ $nilai }}" aria-valuemin="0" aria-valuemax="100">
        <span class="pk-meter-fill" style="width: {{ $nilai }}%"></span>
    </div>
    <span class="pk-meter-value">{{ $nilai }}%</span>
</div>
