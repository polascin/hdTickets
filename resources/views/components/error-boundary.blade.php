{{-- Error Boundary Component for HD Tickets --}}
{{-- Reusable component for consistent error handling throughout the application --}}

@props([
    'type' => 'general',
    'title' => null,
    'message' => null,
    'showRetry' => true,
    'showSupport' => true,
    'retryAction' => null,
    'supportUrl' => '/support',
    'fullPage' => false,
    'class' => '',
    'id' => null
])

@php
    $errorConfig = [
        'network' => [
            'title' => 'Connection Problem',
            'message' => 'Unable to connect to our servers. Please check your internet connection and try again.',
            'icon' => 'exclamation-triangle'
        ],
        'permission' => [
            'title' => 'Access Denied',
            'message' => 'You don\'t have permission to access this resource. Please contact support if you believe this is an error.',
            'icon' => 'lock-closed'
        ],
        'not-found' => [
            'title' => 'Not Found',
            'message' => 'The requested resource could not be found. It may have been moved or deleted.',
            'icon' => 'question-mark-circle'
        ],
        'validation' => [
            'title' => 'Invalid Data',
            'message' => 'The provided data is invalid. Please check your input and try again.',
            'icon' => 'exclamation-circle'
        ],
        'subscription' => [
            'title' => 'Subscription Required',
            'message' => 'An active subscription is required to access this feature. Please upgrade your account or contact support.',
            'icon' => 'credit-card'
        ],
        'maintenance' => [
            'title' => 'Maintenance Mode',
            'message' => 'The system is currently under maintenance. Please try again later.',
            'icon' => 'wrench'
        ],
        'general' => [
            'title' => 'Something went wrong',
            'message' => 'An unexpected error occurred. Please try again or contact support if the problem persists.',
            'icon' => 'exclamation-circle'
        ]
    ];

    $config = $errorConfig[$type] ?? $errorConfig['general'];
    $errorTitle = $title ?? $config['title'];
    $errorMessage = $message ?? $config['message'];
    $iconName = $config['icon'];
    $errorId = $id ?? 'error-boundary-' . uniqid();
@endphp

<div 
    class="error-boundary error-boundary--{{ $type }} {{ $fullPage ? 'error-boundary--full-page' : '' }} {{ $class }}"
    id="{{ $errorId }}"
    role="alert"
    aria-labelledby="{{ $errorId }}-title"
    aria-describedby="{{ $errorId }}-message"
>
    <div class="error-content">
        {{-- Error Icon --}}
        <div class="error-icon" aria-hidden="true">
            @switch($iconName)
                @case('exclamation-triangle')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    @break
                
                @case('lock-closed')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    @break
                
                @case('question-mark-circle')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @break
                
                @case('credit-card')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    @break
                
                @case('wrench')
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    @break
                
                @default
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
            @endswitch
        </div>

        {{-- Error Title --}}
        <h3 class="error-title" id="{{ $errorId }}-title">
            {{ $errorTitle }}
        </h3>

        {{-- Error Message --}}
        <p class="error-message" id="{{ $errorId }}-message">
            {{ $errorMessage }}
        </p>

        {{-- Additional Content --}}
        @if($slot->isNotEmpty())
            <div class="error-additional-content">
                {{ $slot }}
            </div>
        @endif

        {{-- Error Actions --}}
        @if($showRetry || $showSupport)
            <div class="error-actions">
                @if($showRetry)
                    @if($retryAction)
                        <button 
                            type="button" 
                            class="btn btn--primary"
                            onclick="{{ $retryAction }}"
                            aria-describedby="{{ $errorId }}-retry-help"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Try Again
                        </button>
                        <div id="{{ $errorId }}-retry-help" class="sr-only">
                            Click to retry the failed operation
                        </div>
                    @else
                        <button 
                            type="button" 
                            class="btn btn--primary"
                            onclick="window.location.reload()"
                            aria-describedby="{{ $errorId }}-reload-help"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reload Page
                        </button>
                        <div id="{{ $errorId }}-reload-help" class="sr-only">
                            Click to reload the current page
                        </div>
                    @endif
                @endif

                @if($showSupport)
                    <a 
                        href="{{ $supportUrl }}" 
                        class="btn btn--secondary"
                        aria-describedby="{{ $errorId }}-support-help"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Contact Support
                    </a>
                    <div id="{{ $errorId }}-support-help" class="sr-only">
                        Click to contact our support team for assistance
                    </div>
                @endif
            </div>
        @endif

        {{-- Error Details (for debugging in development) --}}
        @if(app()->environment('local') && isset($exception))
            <details class="error-debug-details mt-4">
                <summary class="text-sm font-medium text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                    Debug Information
                </summary>
                <div class="mt-2 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-mono text-gray-700 dark:text-gray-300 overflow-auto max-h-48">
                    <div class="mb-2">
                        <strong>Exception:</strong> {{ get_class($exception) }}
                    </div>
                    <div class="mb-2">
                        <strong>Message:</strong> {{ $exception->getMessage() }}
                    </div>
                    <div class="mb-2">
                        <strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}
                    </div>
                    @if(method_exists($exception, 'getTraceAsString'))
                        <div>
                            <strong>Stack Trace:</strong>
                            <pre class="mt-1 whitespace-pre-wrap">{{ $exception->getTraceAsString() }}</pre>
                        </div>
                    @endif
                </div>
            </details>
        @endif
    </div>
</div>

{{-- Alpine.js component integration --}}
@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('errorBoundary', (config = {}) => ({
        visible: true,
        retryCount: 0,
        maxRetries: config.maxRetries || 3,
        
        init() {
            // Auto-hide after specified timeout
            if (config.autoHideTimeout) {
                setTimeout(() => {
                    this.hide();
                }, config.autoHideTimeout);
            }
            
            // Track error for analytics
            this.trackError();
        },
        
        hide() {
            this.visible = false;
            this.$el.style.display = 'none';
            
            // Emit event for parent components
            this.$dispatch('error-boundary-dismissed', {
                type: '{{ $type }}',
                retryCount: this.retryCount
            });
        },
        
        retry() {
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                
                // Emit retry event
                this.$dispatch('error-boundary-retry', {
                    type: '{{ $type }}',
                    retryCount: this.retryCount
                });
                
                // Hide current error
                this.hide();
            } else {
                // Show support option if max retries reached
                this.showSupport();
            }
        },
        
        showSupport() {
            this.$dispatch('error-boundary-support-needed', {
                type: '{{ $type }}',
                retryCount: this.retryCount
            });
        },
        
        trackError() {
            // Track error occurrence for analytics
            if (window.gtag) {
                gtag('event', 'error_boundary_shown', {
                    error_type: '{{ $type }}',
                    error_title: '{{ $errorTitle }}',
                    custom_map: {
                        custom_parameter_1: '{{ request()->url() }}'
                    }
                });
            }
            
            // Log to console for debugging
            console.warn('Error boundary displayed:', {
                type: '{{ $type }}',
                title: '{{ $errorTitle }}',
                message: '{{ $errorMessage }}',
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            });
        }
    }));
});
</script>
@endPushOnce

{{-- Keyboard navigation support --}}
@pushOnce('styles')
<style>
.error-boundary {
    /* Focus management */
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}

.error-boundary:focus-within .error-actions .btn {
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .error-boundary {
        border-width: 2px;
        border-style: solid;
    }
    
    .error-boundary--network {
        border-color: #f59e0b;
    }
    
    .error-boundary--permission {
        border-color: #ef4444;
    }
    
    .error-boundary--not-found {
        border-color: #6b7280;
    }
    
    .error-icon svg {
        stroke-width: 3;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .error-boundary {
        transition: none;
    }
    
    .error-actions .btn {
        transition: none;
    }
}

/* Print styles */
@media print {
    .error-boundary {
        border: 2px solid #000;
        background: #fff !important;
        color: #000 !important;
        page-break-inside: avoid;
    }
    
    .error-actions {
        display: none;
    }
    
    .error-debug-details {
        display: none;
    }
}
</style>
@endPushOnce
