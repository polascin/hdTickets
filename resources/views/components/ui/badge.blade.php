@props([
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg
    'dot' => false,
    'pill' => false,
    'removable' => false
])

@php
$variantClass = match($variant) {
    'default' => 'hd-badge--default',
    'primary' => 'hd-badge--primary',
    'success' => 'hd-badge--success',
    'warning' => 'hd-badge--warning',
    'error' => 'hd-badge--error',
    'info' => 'hd-badge--info',
    default => 'hd-badge--default'
};

$sizeClass = match($size) {
    'xs' => 'hd-badge--xs',
    'sm' => 'hd-badge--sm',
    'md' => 'hd-badge--md',
    'lg' => 'hd-badge--lg',
    default => 'hd-badge--md'
};

$classes = collect([
    'hd-badge',
    $variantClass,
    $sizeClass,
    $dot ? 'hd-badge--dot' : '',
    $pill ? 'hd-badge--pill' : '',
    $removable ? 'hd-badge--removable' : ''
])->filter()->join(' ');
@endphp

@if($removable)
<div 
    x-data="{ shown: true }"
    x-show="shown"
    x-transition
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($dot)
        <span class="hd-badge__dot"></span>
    @endif
    <span class="hd-badge__text">{{ $slot }}</span>
    <button @click="shown = false" class="hd-badge__remove">
        <svg class="hd-badge__remove-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
    </button>
</div>
@else
<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="hd-badge__dot"></span>
    @endif
    <span class="hd-badge__text">{{ $slot }}</span>
</span>
@endif
