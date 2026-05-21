@props([
    'title' => '',
])

<div {{ $attributes->merge(['class' => 'd-flex justify-content-between align-items-center flex-wrap gap-2 mb-4']) }}>
    <h4 class="mb-0 fw-bold">{{ $title }}</h4>
    @if(isset($actions))
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
