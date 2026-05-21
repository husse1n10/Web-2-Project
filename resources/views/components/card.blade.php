@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'bodyClass' => ''
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $actions)
        <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                @if($title)
                    <h6 class="card-title">{{ $title }}</h6>
                @endif
                @if($subtitle)
                    <small class="card-subtitle">{{ $subtitle }}</small>
                @endif
            </div>
            @if($actions)
                <div>{!! $actions !!}</div>
            @endif
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
