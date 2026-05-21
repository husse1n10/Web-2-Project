@props([
    'label' => '',
    'value' => '0',
    'subtitle' => null,
    'icon' => 'bi-bar-chart',
    'color' => 'teal',
    'trend' => null,
    'trendDirection' => 'flat',
    'trendLabel' => 'vs previous period',
    'animate' => false,
    'valueRaw' => null,
    'valuePrefix' => '',
    'valueSuffix' => '',
    'valueDecimals' => 0,
])

@php
    $colorClass = match ($color) {
        'sky' => 'bg-sky',
        'amber' => 'bg-amber',
        'emerald' => 'bg-emerald',
        'rose' => 'bg-rose',
        'violet' => 'bg-violet',
        default => 'bg-teal',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card h-100 admin-kpi-card']) }}>
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <span class="d-block admin-stat-label mb-1">{{ $label }}</span>
                <h3
                    class="mb-1 admin-stat-value"
                    @if($animate && is_numeric($valueRaw))
                        data-counter
                        data-counter-target="{{ $valueRaw }}"
                        data-counter-prefix="{{ $valuePrefix }}"
                        data-counter-suffix="{{ $valueSuffix }}"
                        data-counter-decimals="{{ $valueDecimals }}"
                    @endif
                >{{ $value }}</h3>
                @if($subtitle)
                    <span class="admin-stat-sub">{!! $subtitle !!}</span>
                @endif
                @if($trend)
                    <div class="admin-kpi-trend-wrap">
                        <span class="admin-kpi-trend admin-kpi-trend-{{ $trendDirection }}">
                            @if($trendDirection === 'up')
                                <i class="bi bi-arrow-up-right"></i>
                            @elseif($trendDirection === 'down')
                                <i class="bi bi-arrow-down-right"></i>
                            @else
                                <i class="bi bi-dash"></i>
                            @endif
                            {{ $trend }}
                        </span>
                        <span class="admin-kpi-trend-label">{{ $trendLabel }}</span>
                    </div>
                @endif
            </div>
            <span class="stat-card-icon {{ $colorClass }}"><i class="bi {{ $icon }}"></i></span>
        </div>
    </div>
</div>
