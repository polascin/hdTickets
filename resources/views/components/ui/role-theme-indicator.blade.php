@props([
    'size' => 'md',
    'showIcon' => true,
    'showLabel' => true,
    'variant' => 'badge'
])

@php
$sizeClasses = [
    'sm' => 'text-xs px-2 py-1',
    'md' => 'text-sm px-3 py-1.5',
    'lg' => 'text-base px-4 py-2'
];

$iconSizeClasses = [
    'sm' => 'w-3 h-3',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5'
];

$baseClasses = implode(' ', [
    'inline-flex items-center gap-2',
    'rounded-full font-medium',
    'transition-all duration-150',
    $sizeClasses[$size] ?? $sizeClasses['md']
]);
@endphp

<div x-data="roleThemeIndicator()" 
     class="{{ $variant === 'badge' ? $baseClasses : 'inline-flex items-center gap-2' }}">

    @if($showIcon)
        <!-- Role Icon -->
        <div class="flex-shrink-0 {{ $iconSizeClasses[$size] ?? $iconSizeClasses['md'] }}">
            <!-- Admin Icon -->
            <svg x-show="roleInfo.label === 'Admin'" 
                 class="w-full h-full text-role-primary" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>

            <!-- Agent Icon -->
            <svg x-show="roleInfo.label === 'Agent'" 
                 class="w-full h-full text-role-primary" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>

            <!-- Customer Icon -->
            <svg x-show="roleInfo.label === 'Customer'" 
                 class="w-full h-full text-role-primary" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>

            <!-- Scraper Icon -->
            <svg x-show="roleInfo.label === 'Scraper'" 
                 class="w-full h-full text-role-primary" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
    @endif

    @if($showLabel)
        <!-- Role Label -->
        <span x-text="roleInfo.label" 
              class="{{ $variant === 'badge' ? 'text-role-primary' : 'text-text-primary font-medium' }}">
        </span>
    @endif

    @if($variant === 'badge')
        <!-- Role-themed background -->
        <div class="absolute inset-0 rounded-full bg-role-surface opacity-20 -z-10"></div>
        
        <!-- Role-themed border -->
        <div class="absolute inset-0 rounded-full border border-role-primary opacity-30 -z-10"></div>
    @endif
</div>

@if($variant === 'badge')
    @pushOnce('styles')
    <style>
    /* Role theme indicator specific styles */
    .role-theme-indicator {
        position: relative;
    }

    /* Admin theme colors */
    .admin-layout .role-theme-indicator {
        --role-glow: var(--hdt-color-admin-primary);
    }

    /* Agent theme colors */
    .agent-layout .role-theme-indicator {
        --role-glow: var(--hdt-color-agent-primary);
    }

    /* Customer theme colors */
    .customer-layout .role-theme-indicator {
        --role-glow: var(--hdt-color-customer-primary);
    }

    /* Scraper theme colors */
    .scraper-layout .role-theme-indicator {
        --role-glow: var(--hdt-color-scraper-primary);
    }

    /* Subtle glow effect */
    .role-theme-indicator:hover::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        background: var(--role-glow, var(--hdt-color-primary-500));
        opacity: 0.1;
        z-index: -1;
        transition: opacity var(--hdt-duration-150) var(--hdt-ease-in-out);
    }

    .role-theme-indicator:hover::before {
        opacity: 0.2;
    }
    </style>
    @endPushOnce
@endif