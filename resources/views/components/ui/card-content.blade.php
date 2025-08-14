@props([
    'padding' => true
])

@php
$classes = collect([
    'hd-card__content',
    !$padding ? 'hd-card__content--no-padding' : ''
])->filter()->join(' ');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
