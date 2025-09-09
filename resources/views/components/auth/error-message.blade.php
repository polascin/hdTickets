@props([
    'type' => 'error',
    'title' => null,
    'message' => '',
    'suggestions' => [],
    'showIcon' => true,
    'dismissible' => false,
    'actions' => []
])

@php
    $config = match($type) {
        'error' => [
            'bgClass' => 'bg-red-50',
            'borderClass' => 'border-red-200',
            'textClass' => 'text-red-800',
            'iconClass' => 'text-red-600',
            'iconPath' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'defaultTitle' => 'Error'
        ],
        'warning' => [
            'bgClass' => 'bg-yellow-50',
            'borderClass' => 'border-yellow-200',
            'textClass' => 'text-yellow-800',
            'iconClass' => 'text-yellow-600',
            'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            'defaultTitle' => 'Warning'
        ],
        'info' => [
            'bgClass' => 'bg-blue-50',
            'borderClass' => 'border-blue-200',
            'textClass' => 'text-blue-800',
            'iconClass' => 'text-blue-600',
            'iconPath' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'defaultTitle' => 'Information'
        ],
        'success' => [
            'bgClass' => 'bg-green-50',
            'borderClass' => 'border-green-200',
            'textClass' => 'text-green-800',
            'iconClass' => 'text-green-600',
            'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'defaultTitle' => 'Success'
        ]
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 transition-all duration-300 {$config['bgClass']} {$config['borderClass']}"]) }}
     role="alert"
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95 translate-y-2"
     x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
     x-transition:leave-end="opacity-0 transform scale-95 translate-y-2">
    
    <div class="flex items-start">
        <!-- Error Icon -->
        @if($showIcon)
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $config['iconClass'] }}" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="{{ $config['iconPath'] }}"/>
            </svg>
        </div>
        @endif
        
        <!-- Message Content -->
        <div class="flex-1 {{ $showIcon ? 'ml-3' : '' }}">
            @if($title)
            <div class="text-sm font-semibold {{ $config['textClass'] }} mb-1">
                {{ $title }}
            </div>
            @endif
            
            <div class="text-sm {{ $config['textClass'] }}">
                {{ $message }}
                {{ $slot }}
            </div>
            
            <!-- Suggestions -->
            @if(!empty($suggestions))
            <div class="mt-3">
                <div class="text-xs font-medium {{ $config['textClass'] }} mb-2">
                    What you can do:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs {{ $config['textClass'] }} opacity-90">
                    @foreach($suggestions as $suggestion)
                    <li>{{ $suggestion }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Action Buttons -->
            @if(!empty($actions))
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($actions as $action)
                <a href="{{ $action['url'] ?? '#' }}" 
                   class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-lg border-2 border-current transition-all duration-200 min-h-[44px] touch-manipulation
                          {{ $config['textClass'] }} hover:bg-current hover:bg-opacity-10 hover:shadow-md hover:-translate-y-0.5
                          focus:outline-none focus:ring-3 focus:ring-offset-2 focus:ring-current focus:ring-opacity-50
                          active:translate-y-0 active:shadow-sm">
                    @if(isset($action['icon']))
                    <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/>
                    </svg>
                    @endif
                    <span>{{ $action['text'] }}</span>
                </a>
                @endforeach
            </div>
            @endif
            
            <!-- Quick Recovery Tips -->
            @if($type === 'error' && !empty($message))
            <div class="mt-4 p-3 bg-white bg-opacity-50 rounded-lg border border-current border-opacity-20">
                <div class="text-xs font-medium {{ $config['textClass'] }} mb-2 flex items-center">
                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span>Quick Tips</span>
                </div>
                <ul class="space-y-1 text-xs {{ $config['textClass'] }} opacity-90">
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Double-check your email address and password</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Ensure Caps Lock is not enabled</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Try refreshing the page if issues persist</span>
                    </li>
                </ul>
            </div>
            @endif
        </div>
        
        <!-- Dismiss Button -->
        @if($dismissible)
        <div class="ml-auto pl-3">
            <button type="button" 
                    @click="show = false"
                    class="inline-flex {{ $config['iconClass'] }} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-current rounded-sm transition-opacity duration-200"
                    aria-label="Dismiss notification">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif
    </div>
</div>
