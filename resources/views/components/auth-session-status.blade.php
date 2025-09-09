@props(['status', 'type' => 'info'])

@php
    // Determine the status type based on content if not explicitly provided
    if (!$status) {
        return;
    }
    
    $statusLower = strtolower($status);
    $autoType = match(true) {
        str_contains($statusLower, 'error') || str_contains($statusLower, 'failed') || str_contains($statusLower, 'invalid') => 'error',
        str_contains($statusLower, 'success') || str_contains($statusLower, 'sent') || str_contains($statusLower, 'verified') => 'success',
        str_contains($statusLower, 'warning') || str_contains($statusLower, 'locked') || str_contains($statusLower, 'attempt') => 'warning',
        default => $type
    };
    
    $classes = match($autoType) {
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    $iconPath = match($autoType) {
        'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
        default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    };
    
    $iconClass = match($autoType) {
        'success' => 'text-green-600',
        'error' => 'text-red-600',
        'warning' => 'text-yellow-600',
        default => 'text-blue-600'
    };
@endphp

@if($status)
<div {{ $attributes->merge(['class' => "rounded-lg border p-4 mb-4 transition-all duration-300 {$classes}"]) }}
     role="alert"
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2">
    
    <div class="flex items-start">
        <!-- Status Icon -->
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $iconClass }}" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
            </svg>
        </div>
        
        <!-- Status Message -->
        <div class="ml-3 flex-1">
            <div class="text-sm font-medium">
                {{ $status }}
            </div>
        </div>
        
        <!-- Dismiss Button for non-error messages -->
        @if($autoType !== 'error')
        <div class="ml-auto pl-3">
            <button type="button" 
                    @click="show = false"
                    class="inline-flex text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-blue-500 rounded-sm transition-colors duration-200"
                    aria-label="Dismiss notification">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif
    </div>
</div>
@endif
