@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'action' => null,
    'variant' => 'default', // default, elevated, interactive, outline, ticket, stat
    'hover' => true,
    'loading' => false,
    'size' => 'default', // compact, default, large
    'border' => true,
    'shadow' => 'sm'
])

@php
    // Use design system classes for consistent styling
    $classes = collect([
        'hd-card',
        // Variant classes
        match($variant) {
            'elevated' => 'hd-card--elevated',
            'interactive' => 'hd-card--interactive',
            'outline' => 'hd-card--outline',
            'ticket' => 'hd-ticket-card',
            'stat' => 'hd-stat-card',
            default => ''
        },
        // Size classes - following design system mobile-first approach
        match($size) {
            'compact' => 'hd-card--compact',
            'large' => 'hd-card--large',
            default => ''
        },
        // Hover effects
        $hover ? 'hd-card--hover' : '',
        // Additional Tailwind classes for dark mode compatibility
        'dark:bg-slate-800 dark:border-slate-700'
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($loading)
        <!-- Loading State -->
        <div class="animate-pulse">
            <div class="flex items-center space-x-4">
                @if($icon)
                    <div class="w-12 h-12 bg-gray-200 dark:bg-slate-700 rounded-xl"></div>
                @endif
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-1/2"></div>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded"></div>
                <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-5/6"></div>
            </div>
        </div>
    @else
        @if($title || $subtitle || $icon || $action)
            <!-- Card Header -->
            <div class="flex items-start justify-between {{ $compact ? 'mb-3' : 'mb-4' }}">
                <div class="flex items-center space-x-3">
                    @if($icon)
                        <div class="flex-shrink-0">
                            @if(is_string($icon))
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="{{ $icon }} text-white text-lg"></i>
                                </div>
                            @else
                                {{ $icon }}
                            @endif
                        </div>
                    @endif
                    
                    @if($title || $subtitle)
                        <div class="flex-1 min-w-0">
                            @if($title)
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $title }}
                                </h3>
                            @endif
                            @if($subtitle)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
                
                @if($action)
                    <div class="flex-shrink-0 ml-4">
                        {{ $action }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Card Content -->
        <div class="card-content">
            {{ $slot }}
        </div>

        <!-- Card Footer (if defined) -->
        @isset($footer)
            <div class="card-footer mt-4 pt-4 border-t border-gray-100 dark:border-slate-700">
                {{ $footer }}
            </div>
        @endisset
    @endif
</div>

@push('styles')
<style>
    .modern-card {
        transform: translateZ(0);
        backface-visibility: hidden;
    }
    
    .modern-card:hover {
        transform: translateY(-8px) translateZ(0);
    }
    
    .dark-mode .modern-card {
        background-color: var(--bg-card, #1e293b);
        border-color: var(--border-color, #475569);
    }
    
    .modern-card .card-content {
        position: relative;
        z-index: 1;
    }
    
    /* Accessibility improvements */
    @media (prefers-reduced-motion: reduce) {
        .modern-card {
            transition: none;
        }
        
        .modern-card:hover {
            transform: none;
        }
    }
    
    /* High contrast mode */
    .high-contrast .modern-card {
        border: 2px solid #000;
        background: #fff;
        color: #000;
    }
    
    .high-contrast .dark-mode .modern-card {
        border: 2px solid #fff;
        background: #000;
        color: #fff;
    }
</style>
@endpush
