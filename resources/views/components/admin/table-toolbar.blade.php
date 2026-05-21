@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'admin-table-toolbar']) }}>
    <div class="admin-table-toolbar-copy">
        @if($title)
            <div class="admin-table-toolbar-title">{{ $title }}</div>
        @endif
        @if($subtitle)
            <div class="admin-table-toolbar-sub">{{ $subtitle }}</div>
        @endif
    </div>
    @if(isset($actions))
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
