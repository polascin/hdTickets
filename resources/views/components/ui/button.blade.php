@props([
    'variant' => 'primary', // primary, secondary, success, warning, error, ghost, outline
    'size' => 'md', // xs, sm, md, lg, xl
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'fullWidth' => false
])

@php
$baseClasses = 'hd-button';
$variantClass = match($variant) {
    'primary' => 'hd-button--primary',
    'secondary' => 'hd-button--secondary', 
    'success' => 'hd-button--success',
    'warning' => 'hd-button--warning',
    'error' => 'hd-button--error',
    'ghost' => 'hd-button--ghost',
    'outline' => 'hd-button--outline',
    default => 'hd-button--primary'
};

$sizeClass = match($size) {
    'xs' => 'hd-button--xs',
    'sm' => 'hd-button--sm',
    'md' => 'hd-button--md',
    'lg' => 'hd-button--lg',
    'xl' => 'hd-button--xl',
    default => 'hd-button--md'
};

$classes = collect([
    $baseClasses,
    $variantClass,
    $sizeClass,
    $fullWidth ? 'hd-button--full' : '',
    $loading ? 'hd-button--loading' : '',
    $disabled ? 'hd-button--disabled' : ''
])->filter()->join(' ');
@endphp

@if($href)
<a href="{{ $href }}" 
   {{ $attributes->merge(['class' => $classes]) }}
   @if($disabled) aria-disabled="true" @endif>
    @if($loading)
        <svg class="hd-button__spinner" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
            <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <span class="hd-button__icon hd-button__icon--left">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="hd-button__text">{{ $slot }}</span>
    
    @if($icon && $iconPosition === 'right')
        <span class="hd-button__icon hd-button__icon--right">
            {!! $icon !!}
        </span>
    @endif
</a>
@else
<button type="{{ $type }}" 
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif>
    @if($loading)
        <svg class="hd-button__spinner" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
            <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <span class="hd-button__icon hd-button__icon--left">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="hd-button__text">{{ $slot }}</span>
    
    @if($icon && $iconPosition === 'right')
        <span class="hd-button__icon hd-button__icon--right">
            {!! $icon !!}
        </span>
    @endif
</button>
@endif
