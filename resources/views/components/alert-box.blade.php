@props([
    'type' => 'info',
    'dismissible' => true
])

@php
    $map = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . ($map[$type] ?? 'alert-info') . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
