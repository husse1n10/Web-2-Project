@props([
    'icon' => 'bi-inbox',
    'title' => 'No data found',
    'message' => '',
    'actionUrl' => null,
    'actionLabel' => 'Get Started',
])

<div {{ $attributes->merge(['class' => 'es-empty-state']) }}>
    <div class="es-empty-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div class="es-empty-title">{{ $title }}</div>
    @if($message)
        <div class="es-empty-copy">{{ $message }}</div>
    @endif
    @if($actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-primary es-empty-action">{{ $actionLabel }}</a>
    @endif
</div>
