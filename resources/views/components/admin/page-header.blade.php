@props([
    'title' => '',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'admin-page-head mb-3']) }}>
    <div>
        <h5 class="admin-page-title">{{ $title }}</h5>
        @if($subtitle)
            <p class="admin-page-sub">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
