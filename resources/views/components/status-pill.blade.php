@props(['status' => 'pending'])

@php
    $normalized = strtolower(str_replace([' ', '-'], '_', $status));
    $validStates = [
        'pending', 'in_review', 'approved', 'completed', 'rejected',
        'missing_documents', 'paid', 'unpaid', 'confirmed', 'scheduled', 'cancelled',
    ];
    $cls = in_array($normalized, $validStates) ? 'es-pill-' . $normalized : 'es-pill-secondary';
    $label = ucwords(str_replace('_', ' ', $normalized));
@endphp

<span {{ $attributes->merge(['class' => 'es-pill ' . $cls]) }}>{{ $label }}</span>
