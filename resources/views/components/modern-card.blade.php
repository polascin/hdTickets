@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'action' => null,
    'gradient' => false,
    'hover' => true,
    'loading' => false,
    'compact' => false,
    'border' => true,
    'shadow' => 'sm'
])

@php
    $classes = collect([
        'modern-card',
        'bg-white dark:bg-slate-800',
        'rounded-2xl',
        'transition-all duration-300',
        $border ? 'border border-gray-100 dark:border-slate-700' : '',
        $hover ? 'hover:shadow-xl hover:-translate-y-2' : '',
        $gradient ? 'bg-gradient-to-br from-white to-gray-50 dark:from-slate-800 dark:to-slate-900' : '',
        $compact ? 'p-4' : 'p-6',
        match($shadow) {
            'none' => '',
            'sm' => 'shadow-sm',
            'md' => 'shadow-md',
            'lg' => 'shadow-lg',
            'xl' => 'shadow-xl',
            default => 'shadow-sm'
        }
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
