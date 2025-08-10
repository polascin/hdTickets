@props([
    'user' => null,
    'position' => 'header', // 'header', 'sidebar', 'dropdown'
    'showLabel' => true,
    'size' => 'sm' // 'xs', 'sm', 'md', 'lg'
])

@php
    $user = $user ?? Auth::user();
    $completion = $user->getProfileCompletion();
    
    // Status colors and icons
    $statusConfig = [
        'excellent' => [
            'color' => 'text-green-600 bg-green-100',
            'ring' => 'ring-green-500',
            'icon' => 'check-circle',
            'text' => 'Excellent'
        ],
        'good' => [
            'color' => 'text-blue-600 bg-blue-100',
            'ring' => 'ring-blue-500',
            'icon' => 'check-circle',
            'text' => 'Good'
        ],
        'fair' => [
            'color' => 'text-yellow-600 bg-yellow-100',
            'ring' => 'ring-yellow-500',
            'icon' => 'exclamation-circle',
            'text' => 'Fair'
        ],
        'incomplete' => [
            'color' => 'text-red-600 bg-red-100',
            'ring' => 'ring-red-500',
            'icon' => 'x-circle',
            'text' => 'Incomplete'
        ]
    ];
    
    $config = $statusConfig[$completion['status']];
    
    // Size configurations
    $sizeConfig = [
        'xs' => [
            'progress' => 'w-6 h-6',
            'text' => 'text-xs',
            'icon' => 'w-3 h-3',
            'badge' => 'px-1.5 py-0.5 text-xs'
        ],
        'sm' => [
            'progress' => 'w-8 h-8',
            'text' => 'text-sm',
            'icon' => 'w-4 h-4',
            'badge' => 'px-2 py-1 text-xs'
        ],
        'md' => [
            'progress' => 'w-10 h-10',
            'text' => 'text-base',
            'icon' => 'w-5 h-5',
            'badge' => 'px-2.5 py-1 text-sm'
        ],
        'lg' => [
            'progress' => 'w-12 h-12',
            'text' => 'text-lg',
            'icon' => 'w-6 h-6',
            'badge' => 'px-3 py-1.5 text-sm'
        ]
    ];
    
    $sizes = $sizeConfig[$size];
@endphp

<div class="profile-completion-indicator flex items-center space-x-2" 
     x-data="{ showTooltip: false }"
     @mouseenter="showTooltip = true"
     @mouseleave="showTooltip = false">
    
    <!-- Circular Progress Indicator -->
    <div class="relative {{ $sizes['progress'] }}">
        <!-- Background Circle -->
        <svg class="transform -rotate-90 {{ $sizes['progress'] }}" viewBox="0 0 36 36">
            <path class="text-gray-300"
                  stroke="currentColor"
                  stroke-width="3"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
        </svg>
        
        <!-- Progress Circle -->
        <svg class="absolute inset-0 transform -rotate-90 {{ $sizes['progress'] }}" viewBox="0 0 36 36">
            <path class="{{ str_replace('bg-', 'text-', explode(' ', $config['color'])[1]) }}"
                  stroke="currentColor"
                  stroke-width="3"
                  fill="none"
                  stroke-dasharray="{{ $completion['percentage'] }}, 100"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
        </svg>
        
        <!-- Center Content -->
        <div class="absolute inset-0 flex items-center justify-center">
            @if($completion['percentage'] >= 90)
                <!-- Checkmark icon for complete profiles -->
                <svg class="{{ $sizes['icon'] }} {{ explode(' ', $config['color'])[0] }}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            @else
                <!-- Percentage for incomplete profiles -->
                <span class="{{ $sizes['text'] }} font-medium {{ explode(' ', $config['color'])[0] }}">
                    {{ $completion['percentage'] }}%
                </span>
            @endif
        </div>
    </div>
    
    <!-- Label and Status (if enabled) -->
    @if($showLabel)
        <div class="flex flex-col">
            <div class="flex items-center space-x-1">
                <span class="{{ $sizes['text'] }} font-medium text-gray-700">Profile</span>
                @if($position === 'dropdown')
                    <span class="inline-flex items-center {{ $sizes['badge'] }} {{ $config['color'] }} rounded-full font-medium">
                        {{ $config['text'] }}
                    </span>
                @endif
            </div>
            @if($position === 'sidebar')
                <span class="text-xs text-gray-500">{{ $completion['completed_count'] }}/{{ $completion['total_fields'] }} completed</span>
            @endif
        </div>
    @endif
    
    <!-- Tooltip -->
    <div x-show="showTooltip"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 w-64 p-3 bg-white rounded-lg shadow-lg border border-gray-200 {{ $position === 'header' ? 'top-full mt-2 right-0' : 'left-full ml-2 top-0' }}"
         x-cloak>
        
        <!-- Tooltip Header -->
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-semibold text-gray-900">Profile Completion</h4>
            <span class="inline-flex items-center px-2 py-1 text-xs font-medium {{ $config['color'] }} rounded-full">
                {{ $completion['percentage'] }}%
            </span>
        </div>
        
        <!-- Progress Bar -->
        <div class="mb-3">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 {{ str_replace('text-', 'bg-', explode(' ', $config['color'])[0]) }} rounded-full transition-all duration-500"
                     style="width: {{ $completion['percentage'] }}%"></div>
            </div>
        </div>
        
        <!-- Status Message -->
        <div class="mb-3">
            @if($completion['is_complete'])
                <p class="text-sm text-green-700 flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Your profile is complete!
                </p>
            @else
                <p class="text-sm text-gray-600">
                    Complete your profile to unlock all features.
                </p>
            @endif
        </div>
        
        <!-- Missing Fields (if any) -->
        @if(!empty($completion['missing_fields']) && count($completion['missing_fields']) <= 4)
            <div class="mb-3">
                <p class="text-xs font-medium text-gray-500 mb-1">Missing:</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($completion['missing_fields'] as $field)
                        <span class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">
                            {{ ucfirst(str_replace('_', ' ', $field)) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Action Button -->
        <div class="pt-2 border-t border-gray-100">
            <a href="{{ route('profile.edit') }}" 
               class="inline-flex items-center w-full justify-center px-3 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Complete Profile
            </a>
        </div>
    </div>
</div>
