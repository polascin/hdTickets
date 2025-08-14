@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'color' => 'blue',
    'variant' => 'default', // default, compact, feature, stats
    'shadow' => 'md',
    'hoverable' => false,
    'loading' => false,
    'headerActions' => null,
    'footerActions' => null,
    'padding' => 'default' // none, sm, default, lg
])

@php
    $colorClasses = [
        'blue' => [
            'icon' => 'text-blue-600 bg-blue-50',
            'border' => 'border-blue-200',
            'header' => 'bg-gradient-to-r from-blue-50 to-indigo-50'
        ],
        'green' => [
            'icon' => 'text-green-600 bg-green-50',
            'border' => 'border-green-200',
            'header' => 'bg-gradient-to-r from-green-50 to-emerald-50'
        ],
        'purple' => [
            'icon' => 'text-purple-600 bg-purple-50',
            'border' => 'border-purple-200',
            'header' => 'bg-gradient-to-r from-purple-50 to-violet-50'
        ],
        'orange' => [
            'icon' => 'text-orange-600 bg-orange-50',
            'border' => 'border-orange-200',
            'header' => 'bg-gradient-to-r from-orange-50 to-amber-50'
        ],
        'red' => [
            'icon' => 'text-red-600 bg-red-50',
            'border' => 'border-red-200',
            'header' => 'bg-gradient-to-r from-red-50 to-pink-50'
        ],
        'gray' => [
            'icon' => 'text-gray-600 bg-gray-50',
            'border' => 'border-gray-200',
            'header' => 'bg-gradient-to-r from-gray-50 to-slate-50'
        ]
    ];

    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow-md',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl'
    ];

    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8'
    ];

    $colors = $colorClasses[$color] ?? $colorClasses['blue'];
    $shadowClass = $shadowClasses[$shadow] ?? $shadowClasses['md'];
    $paddingClass = $paddingClasses[$padding] ?? $paddingClasses['default'];
@endphp

<div class="profile-card bg-white rounded-xl border border-gray-200 {{ $shadowClass }} transition-all duration-200 
            {{ $hoverable ? 'hover:shadow-lg hover:-translate-y-1 cursor-pointer' : '' }}
            {{ $variant === 'feature' ? 'relative overflow-hidden' : '' }}
            {{ $loading ? 'animate-pulse' : '' }}"
     @if($hoverable) 
        role="button" 
        tabindex="0"
        @keydown.enter="$el.click()"
        @keydown.space="$el.click()"
     @endif>

    @if($variant === 'feature')
        <!-- Feature Card Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"none\" fill-rule=\"evenodd\"><g fill=\"%23{{ $color === 'blue' ? '3b82f6' : ($color === 'green' ? '10b981' : '6b7280') }}\" fill-opacity=\"0.1\"><circle cx=\"30\" cy=\"30\" r=\"2\"/></g></g></svg>');"></div>
        </div>
    @endif

    @if($loading)
        <!-- Loading State -->
        <div class="animate-pulse {{ $paddingClass }}">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-gray-200 rounded-lg mr-3"></div>
                <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                <div class="h-4 bg-gray-200 rounded w-4/6"></div>
            </div>
        </div>
    @else
        @if($title || $icon || $headerActions)
            <!-- Card Header -->
            <div class="card-header {{ $variant === 'feature' ? $colors['header'] : '' }} 
                        {{ $variant === 'compact' ? 'pb-3' : 'pb-4' }} 
                        {{ $paddingClass }} 
                        {{ ($title || $icon || $headerActions) && $slot->isNotEmpty() ? 'border-b border-gray-100' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        @if($icon)
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 {{ $colors['icon'] }} rounded-lg flex items-center justify-center">
                                    <x-profile-icon name="{{ $icon }}" class="w-5 h-5" />
                                </div>
                            </div>
                        @endif
                        
                        <div class="min-w-0 flex-1">
                            @if($title)
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ $title }}
                                </h3>
                            @endif
                            
                            @if($subtitle)
                                <p class="text-sm text-gray-500 mt-1 truncate">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    @if($headerActions)
                        <div class="flex-shrink-0 ml-4">
                            {{ $headerActions }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($slot->isNotEmpty())
            <!-- Card Content -->
            <div class="card-content {{ $variant === 'compact' ? 'py-3' : 'py-4' }} 
                        {{ $paddingClass }} 
                        {{ ($title || $icon || $headerActions) ? 'pt-0' : '' }}
                        {{ $footerActions ? 'pb-0' : '' }}">
                {{ $slot }}
            </div>
        @endif

        @if($footerActions)
            <!-- Card Footer -->
            <div class="card-footer border-t border-gray-100 {{ $paddingClass }} pt-4">
                <div class="flex items-center justify-between">
                    {{ $footerActions }}
                </div>
            </div>
        @endif
    @endif
</div>

@if($variant === 'stats')
    @push('styles')
    <style>
    .profile-card.stats-variant {
        background: linear-gradient(135deg, {{ $color === 'blue' ? '#3b82f6, #1d4ed8' : ($color === 'green' ? '#10b981, #047857' : '#6b7280, #374151') }});
        color: white;
    }
    
    .profile-card.stats-variant .card-header,
    .profile-card.stats-variant .card-content {
        color: white;
    }
    
    .profile-card.stats-variant .text-gray-500,
    .profile-card.stats-variant .text-gray-600 {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    
    .profile-card.stats-variant .border-gray-100 {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    </style>
    @endpush
@endif
