@props([
    'variant' => 'default', // default, bordered, elevated, flat
    'size' => 'md', // sm, md, lg
    'padding' => true,
    'hover' => false,
    'clickable' => false,
    'href' => null,
    'loading' => false
])

@php
$baseClasses = 'hd-card';
$variantClass = match($variant) {
    'default' => 'hd-card--default',
    'bordered' => 'hd-card--bordered',
    'elevated' => 'hd-card--elevated',
    'flat' => 'hd-card--flat',
    default => 'hd-card--default'
};

$sizeClass = match($size) {
    'sm' => 'hd-card--sm',
    'md' => 'hd-card--md',
    'lg' => 'hd-card--lg',
    default => 'hd-card--md'
};

$classes = collect([
    $baseClasses,
    $variantClass,
    $sizeClass,
    !$padding ? 'hd-card--no-padding' : '',
    $hover ? 'hd-card--hover' : '',
    $clickable ? 'hd-card--clickable' : '',
    $loading ? 'hd-card--loading' : ''
])->filter()->join(' ');
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($loading)
        <div class="hd-card__loading">
            <div class="hd-loading-skeleton hd-loading-skeleton--card"></div>
        </div>
    @else
        {{ $slot }}
    @endif
</a>
@else
<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($loading)
        <div class="hd-card__loading">
            <div class="hd-loading-skeleton hd-loading-skeleton--card"></div>
        </div>
    @else
        {{ $slot }}
    @endif
</div>
@endif
