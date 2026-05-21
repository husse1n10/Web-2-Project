@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'type' => 'button'
])

@php
    $map = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-outline-secondary',
        'danger' => 'btn-danger',
        'success' => 'btn-success',
    ];

    $classes = 'btn ' . ($map[$variant] ?? $map['primary']) . ($size ? ' btn-' . $size : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
