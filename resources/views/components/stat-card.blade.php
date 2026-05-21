@props([
    'label' => '',
    'value' => '0',
    'icon' => 'bi-bar-chart',
    'color' => 'teal',
    'subtitle' => null,
])

<div class="card h-100" {{ $attributes }}>
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <span class="d-block text-muted mb-1" style="font-size:.76rem;">{{ $label }}</span>
                <h3 class="mb-1" style="font-size:1.5rem; font-weight:800;">{{ $value }}</h3>
                @if($subtitle)
                    <span class="text-muted" style="font-size:.72rem;">{!! $subtitle !!}</span>
                @endif
            </div>
            <span class="stat-card-icon bg-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
        </div>
    </div>
</div>
