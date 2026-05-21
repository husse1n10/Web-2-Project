@php
$width = $width ?? '25';
@endphp

<span class="text-primary">
    <img
        src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}"
        alt="CedarGov icon"
        width="{{ $width }}"
        height="{{ $width }}"
        style="width:{{ $width }}px;height:{{ $width }}px;object-fit:cover;border-radius:6px;display:block;"
    >
</span>
