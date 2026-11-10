@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}@unless ($required) <span class="text-secondary fw-normal">(opsional)</span>@endunless
    </label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" @required($required)
            {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : ''), 'rows' => 4]) }}>{{ old($name, $value) }}</textarea>
    @elseif (in_array($type, ['password', 'file'], true))
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @required($required)
            {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required)
            {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}>
    @endif
    @if ($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif
</div>
