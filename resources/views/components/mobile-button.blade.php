@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'default',
    'fullWidth' => false,
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left'
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 touch-manipulation select-none focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'bg-blue-600 hover:bg-blue-700 text-white border border-transparent focus:ring-blue-500 active:bg-blue-800',
    'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 focus:ring-blue-500 active:bg-gray-100',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white border border-transparent focus:ring-red-500 active:bg-red-800',
    'success' => 'bg-green-600 hover:bg-green-700 text-white border border-transparent focus:ring-green-500 active:bg-green-800',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white border border-transparent focus:ring-yellow-500 active:bg-yellow-800',
    'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 border border-transparent focus:ring-gray-500 active:bg-gray-200',
    'outline' => 'bg-transparent hover:bg-blue-50 text-blue-600 border border-blue-600 focus:ring-blue-500 active:bg-blue-100'
];

$sizeClasses = [
    'xs' => 'px-2.5 py-1.5 text-xs min-h-[32px]',
    'sm' => 'px-3 py-2 text-sm min-h-[36px]',
    'default' => 'px-4 py-2.5 text-sm min-h-[44px]',
    'lg' => 'px-6 py-3 text-base min-h-[48px]',
    'xl' => 'px-8 py-4 text-lg min-h-[56px]'
];

// Mobile-specific adjustments
$mobileClasses = 'mobile:w-full mobile:min-h-[48px] mobile:text-base mobile:py-3';

$classes = collect([
    $baseClasses,
    $variantClasses[$variant] ?? $variantClasses['primary'],
    $sizeClasses[$size] ?? $sizeClasses['default'],
    $fullWidth ? 'w-full' : '',
    // Add mobile-responsive classes only on mobile devices
    '@media (max-width: 640px)' => $mobileClasses,
])->filter()->implode(' ');

$iconSize = match($size) {
    'xs' => 'w-3 h-3',
    'sm' => 'w-4 h-4',
    'default' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
    'xl' => 'w-6 h-6',
    default => 'w-4 h-4'
};
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($disabled || $loading) disabled @endif
    @if($loading) aria-busy="true" @endif
>
    @if($loading)
        <!-- Loading Spinner -->
        <svg class="animate-spin {{ $iconSize }} {{ $iconPosition === 'right' && $slot->isNotEmpty() ? 'ml-2' : ($slot->isNotEmpty() ? 'mr-2' : '') }}" 
             fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <!-- Left Icon -->
        @if(is_string($icon))
            <i class="{{ $icon }} {{ $iconSize }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }}"></i>
        @else
            <span class="{{ $iconSize }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }}">
                {!! $icon !!}
            </span>
        @endif
    @endif
    
    @if($slot->isNotEmpty())
        <span>{{ $slot }}</span>
    @endif
    
    @if($icon && $iconPosition === 'right' && !$loading)
        <!-- Right Icon -->
        @if(is_string($icon))
            <i class="{{ $icon }} {{ $iconSize }} {{ $slot->isNotEmpty() ? 'ml-2' : '' }}"></i>
        @else
            <span class="{{ $iconSize }} {{ $slot->isNotEmpty() ? 'ml-2' : '' }}">
                {!! $icon !!}
            </span>
        @endif
    @endif
</button>

@once
@push('styles')
<style>
    /* Mobile-specific button enhancements */
    @media (max-width: 640px) {
        /* Full-width buttons on mobile for better touch targets */
        .mobile-button-group > button,
        .mobile-button-group > a[role="button"] {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .mobile-button-group > button:last-child,
        .mobile-button-group > a[role="button"]:last-child {
            margin-bottom: 0;
        }
        
        /* Better touch feedback */
        button:active,
        a[role="button"]:active {
            transform: scale(0.98);
        }
        
        /* Ensure minimum touch target size */
        button,
        a[role="button"] {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Improved spacing for button groups */
        .button-group-mobile {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .button-group-mobile.horizontal {
            flex-direction: row;
            gap: 0.5rem;
        }
        
        .button-group-mobile.horizontal > * {
            flex: 1;
        }
    }
    
    /* Touch manipulation for better performance */
    button,
    a[role="button"] {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }
    
    /* Focus improvements for accessibility */
    button:focus,
    a[role="button"]:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }
    
    /* High contrast mode improvements */
    @media (prefers-contrast: high) {
        button {
            border-width: 2px;
        }
    }
    
    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        button,
        a[role="button"] {
            transition: none;
        }
        
        button:active,
        a[role="button"]:active {
            transform: none;
        }
        
        .animate-spin {
            animation: none;
        }
    }
    
    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .dark-mode button[variant="secondary"] {
            background-color: rgb(55, 65, 81);
            border-color: rgb(75, 85, 99);
            color: rgb(243, 244, 246);
        }
        
        .dark-mode button[variant="secondary"]:hover {
            background-color: rgb(75, 85, 99);
        }
        
        .dark-mode button[variant="ghost"] {
            color: rgb(243, 244, 246);
        }
        
        .dark-mode button[variant="ghost"]:hover {
            background-color: rgb(55, 65, 81);
        }
    }
</style>
@endpush
@endonce
