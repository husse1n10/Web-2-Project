@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required) <span class="text-danger">*</span>@endif</label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')]) }}
    >

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if($help)
        <small class="text-muted">{{ $help }}</small>
    @endif
</div>
