@props([
    'type' => 'button',
    'variant' => 'primary', // primary, outline, red, danger
    'icon' => null,
])

@php
    $classes = 'btn';
    if ($variant === 'primary') $classes .= ' btn-primary';
    elseif ($variant === 'outline') $classes .= ' btn-outline';
    elseif ($variant === 'red') $classes .= ' btn-red';
    elseif ($variant === 'danger') $classes .= ' btn-danger';
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>
