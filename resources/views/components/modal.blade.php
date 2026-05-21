@props([
    'id',
    'title' => 'Modal Title',
    'size' => null,
    'footer' => null,
    'static' => false
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" @if($static) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
    <div class="modal-dialog {{ $size ? 'modal-' . $size : '' }} modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer">
                    {!! $footer !!}
                </div>
            @endif
        </div>
    </div>
</div>
