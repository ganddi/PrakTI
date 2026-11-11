{{-- Indikator tahapan proses KPI (FR-25, Look & Feel #14) --}}
@props(['pengajuan'])
<ol {{ $attributes->merge(['class' => 'pk-steps']) }} aria-label="Tahapan KPI">
    @foreach ($pengajuan->tahapan() as $label => $keadaan)
        <li class="pk-step {{ $keadaan ? 'is-'.$keadaan : '' }}" @if ($keadaan === 'current') aria-current="step" @endif>{{ $label }}</li>
    @endforeach
</ol>
