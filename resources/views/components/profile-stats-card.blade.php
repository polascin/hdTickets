@props([
    'title' => '',
    'value' => '',
    'subtitle' => '',
    'icon' => 'chart',
    'color' => 'blue',
    'trend' => null, // 'up', 'down', 'neutral'
    'change' => null,
    'compact' => false
])

@php
    $colorClasses = [
        'blue' => 'from-blue-500 to-blue-600',
        'green' => 'from-green-500 to-green-600', 
        'red' => 'from-red-500 to-red-600',
        'purple' => 'from-purple-500 to-purple-600',
        'orange' => 'from-orange-500 to-orange-600',
        'indigo' => 'from-indigo-500 to-indigo-600'
    ];
    
    $trendIcons = [
        'up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'down' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6', 
        'neutral' => 'M8 12h8'
    ];
    
    $trendColors = [
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        'neutral' => 'text-gray-600'
    ];
    
    $gradientClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="profile-stats-card bg-gradient-to-r {{ $gradientClass }} rounded-xl shadow-lg text-white overflow-hidden {{ $compact ? 'p-4' : 'p-6' }}">
    <div class="relative">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"%23ffffff\" fill-opacity=\"0.3\"><circle cx=\"20\" cy=\"20\" r=\"1.5\"/></g></svg>');"></div>
        </div>
        
        <div class="relative flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                        <x-profile-icon name="{{ $icon }}" class="w-4 h-4 text-white" />
                    </div>
                    <h3 class="text-sm font-medium text-white opacity-90 truncate">{{ $title }}</h3>
                </div>
                
                <div class="flex items-baseline space-x-3">
                    <p class="text-2xl font-bold text-white {{ $compact ? 'text-xl' : 'text-3xl' }}">{{ $value }}</p>
                    
                    @if($trend && $change)
                        <div class="flex items-center text-xs bg-white bg-opacity-20 rounded-full px-2 py-1">
                            <svg class="w-3 h-3 mr-1 {{ $trendColors[$trend] ?? 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trendIcons[$trend] ?? $trendIcons['neutral'] }}"></path>
                            </svg>
                            <span class="{{ $trendColors[$trend] ?? 'text-gray-600' }}">{{ $change }}</span>
                        </div>
                    @endif
                </div>
                
                @if($subtitle)
                    <p class="text-xs text-white opacity-75 mt-2">{{ $subtitle }}</p>
                @endif
            </div>
            
            <!-- Decorative Element -->
            <div class="hidden sm:block">
                <div class="w-16 h-16 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                    <x-profile-icon name="{{ $icon }}" class="w-8 h-8 text-white opacity-60" />
                </div>
            </div>
        </div>
    </div>
</div>
