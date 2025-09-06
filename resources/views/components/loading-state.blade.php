{{-- Loading State Component for HD Tickets --}}
{{-- Reusable component for consistent loading states throughout the application --}}

@props([
    'type' => 'spinner',
    'message' => 'Loading...',
    'size' => 'md',
    'overlay' => false,
    'fullPage' => false,
    'progress' => null,
    'showProgress' => false,
    'skeleton' => 'default',
    'class' => '',
    'id' => null
])

@php
    $loadingId = $id ?? 'loading-' . uniqid();
    $sizeClass = match($size) {
        'sm' => 'spinner--sm',
        'lg' => 'spinner--lg',
        'xl' => 'spinner--xl',
        default => ''
    };
@endphp

@if($type === 'skeleton')
    {{-- Skeleton Loading --}}
    <div 
        class="skeleton-container {{ $class }}"
        id="{{ $loadingId }}"
        role="progressbar"
        aria-label="{{ $message }}"
        aria-busy="true"
    >
        @switch($skeleton)
            @case('stats')
                <div class="skeleton-stats-grid">
                    @for($i = 0; $i < 4; $i++)
                        <div class="skeleton-stat-card">
                            <div class="skeleton skeleton--title"></div>
                            <div class="skeleton skeleton--value"></div>
                            <div class="skeleton skeleton--change"></div>
                        </div>
                    @endfor
                </div>
                @break

            @case('tickets')
                <div class="skeleton-ticket-list">
                    @for($i = 0; $i < 5; $i++)
                        <div class="skeleton-ticket-item">
                            <div class="skeleton skeleton--image"></div>
                            <div class="skeleton-ticket-content">
                                <div class="skeleton skeleton--title"></div>
                                <div class="skeleton skeleton--text"></div>
                                <div class="skeleton skeleton--text"></div>
                            </div>
                            <div class="skeleton-ticket-price">
                                <div class="skeleton skeleton--price"></div>
                                <div class="skeleton skeleton--button"></div>
                            </div>
                        </div>
                    @endfor
                </div>
                @break

            @case('chart')
                <div class="skeleton-widget">
                    <div class="skeleton-widget-header">
                        <div class="skeleton skeleton--title"></div>
                        <div class="skeleton skeleton--button"></div>
                    </div>
                    <div class="skeleton skeleton--chart"></div>
                </div>
                @break

            @case('table')
                <table class="skeleton-table">
                    <thead>
                        <tr>
                            @for($i = 0; $i < 4; $i++)
                                <th><div class="skeleton skeleton--table-header"></div></th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 8; $i++)
                            <tr>
                                @for($j = 0; $j < 4; $j++)
                                    <td><div class="skeleton skeleton--table-row"></div></td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
                @break

            @case('profile')
                <div class="skeleton-profile">
                    <div class="skeleton skeleton--avatar-lg"></div>
                    <div class="skeleton-profile-content">
                        <div class="skeleton skeleton--title"></div>
                        <div class="skeleton skeleton--subtitle"></div>
                        <div class="skeleton skeleton--paragraph"></div>
                        <div class="skeleton skeleton--paragraph"></div>
                    </div>
                </div>
                @break

            @case('card')
                <div class="skeleton-card-container">
                    @for($i = 0; $i < 3; $i++)
                        <div class="skeleton-card">
                            <div class="skeleton skeleton--image"></div>
                            <div class="skeleton-card-content">
                                <div class="skeleton skeleton--title"></div>
                                <div class="skeleton skeleton--paragraph"></div>
                                <div class="skeleton skeleton--paragraph"></div>
                                <div class="skeleton skeleton--button"></div>
                            </div>
                        </div>
                    @endfor
                </div>
                @break

            @default
                <div class="skeleton-widget">
                    <div class="skeleton skeleton--title"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                </div>
        @endswitch

        {{-- Screen reader announcement --}}
        <div class="sr-loading-announcement" aria-live="polite">
            {{ $message }}
        </div>
    </div>

@elseif($overlay || $fullPage)
    {{-- Overlay Loading --}}
    <div 
        class="loading-overlay {{ $fullPage ? '' : 'loading-overlay--component' }} {{ $class }}"
        id="{{ $loadingId }}"
        role="progressbar"
        aria-label="{{ $message }}"
        aria-busy="true"
    >
        <div class="loading-content">
            {{-- Spinner --}}
            @switch($type)
                @case('dots')
                    <div class="spinner-dots">
                        <div></div><div></div><div></div><div></div>
                    </div>
                    @break
                
                @case('pulse')
                    <div class="spinner-pulse"></div>
                    @break
                
                @default
                    <div class="spinner {{ $sizeClass }}"></div>
            @endswitch

            {{-- Loading message --}}
            <h3>{{ $message }}</h3>

            {{-- Progress bar --}}
            @if($showProgress)
                <div class="progress-bar" role="progressbar" aria-valuenow="{{ $progress ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div 
                        class="progress-bar__fill {{ $progress === null ? 'progress-bar__fill--indeterminate' : '' }}"
                        style="width: {{ $progress ?? 30 }}%"
                    ></div>
                </div>
                @if($progress !== null)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        {{ $progress }}% complete
                    </p>
                @endif
            @endif

            {{-- Additional content --}}
            @if($slot->isNotEmpty())
                <div class="loading-additional-content">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>

@else
    {{-- Inline Loading --}}
    <div 
        class="inline-loading {{ $class }}"
        id="{{ $loadingId }}"
        role="progressbar"
        aria-label="{{ $message }}"
        aria-busy="true"
    >
        {{-- Spinner --}}
        @switch($type)
            @case('dots')
                <div class="spinner-dots">
                    <div></div><div></div><div></div><div></div>
                </div>
                @break
            
            @case('pulse')
                <div class="spinner-pulse"></div>
                @break
            
            @default
                <div class="spinner {{ $sizeClass }}"></div>
        @endswitch

        {{-- Loading message --}}
        @if($message)
            <span class="loading-message">{{ $message }}</span>
        @endif

        {{-- Progress indicator --}}
        @if($showProgress && $progress !== null)
            <span class="loading-progress">({{ $progress }}%)</span>
        @endif

        {{-- Additional content --}}
        {{ $slot }}
    </div>
@endif

{{-- Alpine.js component integration --}}
@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loadingState', (config = {}) => ({
        visible: true,
        progress: config.progress || 0,
        message: config.message || 'Loading...',
        startTime: Date.now(),
        
        init() {
            // Auto-hide after timeout
            if (config.timeout) {
                setTimeout(() => {
                    this.hide();
                }, config.timeout);
            }
            
            // Simulate progress for indeterminate loaders
            if (config.simulateProgress && this.progress === 0) {
                this.simulateProgress();
            }
            
            // Track loading start
            this.trackLoadingStart();
        },
        
        hide() {
            this.visible = false;
            this.$el.style.display = 'none';
            
            // Track loading duration
            const duration = Date.now() - this.startTime;
            this.trackLoadingEnd(duration);
            
            // Emit completion event
            this.$dispatch('loading-complete', {
                duration: duration,
                loadingId: '{{ $loadingId }}'
            });
        },
        
        show() {
            this.visible = true;
            this.$el.style.display = '';
            this.startTime = Date.now();
            this.trackLoadingStart();
        },
        
        updateProgress(newProgress, newMessage = null) {
            this.progress = Math.max(0, Math.min(100, newProgress));
            if (newMessage) {
                this.message = newMessage;
            }
            
            // Auto-hide when complete
            if (this.progress >= 100) {
                setTimeout(() => this.hide(), 500);
            }
            
            // Update DOM elements
            this.updateProgressElements();
        },
        
        updateMessage(newMessage) {
            this.message = newMessage;
            const messageEl = this.$el.querySelector('.loading-message, h3');
            if (messageEl) {
                messageEl.textContent = newMessage;
            }
        },
        
        simulateProgress() {
            const increment = Math.random() * 3 + 1;
            this.progress = Math.min(90, this.progress + increment);
            
            this.updateProgressElements();
            
            if (this.progress < 90) {
                setTimeout(() => this.simulateProgress(), 200 + Math.random() * 300);
            }
        },
        
        updateProgressElements() {
            // Update progress bar
            const progressFill = this.$el.querySelector('.progress-bar__fill');
            if (progressFill) {
                progressFill.style.width = `${this.progress}%`;
                const progressBar = progressFill.parentElement;
                if (progressBar) {
                    progressBar.setAttribute('aria-valuenow', this.progress);
                }
            }
            
            // Update progress text
            const progressText = this.$el.querySelector('.loading-progress');
            if (progressText) {
                progressText.textContent = `(${Math.round(this.progress)}%)`;
            }
            
            const progressComplete = this.$el.querySelector('.progress-complete-text');
            if (progressComplete) {
                progressComplete.textContent = `${Math.round(this.progress)}% complete`;
            }
        },
        
        trackLoadingStart() {
            // Track loading start for analytics
            if (window.gtag) {
                gtag('event', 'loading_start', {
                    loading_type: '{{ $type }}',
                    loading_message: this.message,
                    custom_map: {
                        custom_parameter_1: window.location.pathname
                    }
                });
            }
            
            console.debug('Loading started:', {
                type: '{{ $type }}',
                message: this.message,
                loadingId: '{{ $loadingId }}',
                timestamp: new Date().toISOString()
            });
        },
        
        trackLoadingEnd(duration) {
            // Track loading completion for analytics
            if (window.gtag) {
                gtag('event', 'loading_complete', {
                    loading_type: '{{ $type }}',
                    loading_duration: duration,
                    custom_map: {
                        custom_parameter_1: window.location.pathname
                    }
                });
            }
            
            console.debug('Loading completed:', {
                type: '{{ $type }}',
                duration: duration,
                loadingId: '{{ $loadingId }}',
                timestamp: new Date().toISOString()
            });
        }
    }));
});
</script>
@endPushOnce

{{-- Component-specific styles --}}
@pushOnce('styles')
<style>
/* Inline loading styles */
.inline-loading {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.loading-message {
    margin-left: 0.5rem;
}

.loading-progress {
    font-size: 0.8125rem;
    color: var(--text-tertiary);
}

/* Skeleton profile layout */
.skeleton-profile {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.skeleton-profile-content {
    flex: 1;
}

/* Skeleton card container */
.skeleton-card-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.skeleton-card {
    background: var(--bg-elevated);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border-primary);
}

.skeleton-card-content {
    padding: 1rem;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .inline-loading .spinner,
    .inline-loading .spinner-dots,
    .inline-loading .spinner-pulse {
        animation: none;
    }
    
    .inline-loading .spinner {
        border-color: var(--color-primary);
        border-top-color: transparent;
    }
}

/* Focus management */
.loading-overlay:focus,
.skeleton-container:focus,
.inline-loading:focus {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .inline-loading {
        border: 1px solid currentColor;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .skeleton-card-container {
        grid-template-columns: 1fr;
    }
    
    .skeleton-profile {
        flex-direction: column;
        text-align: center;
    }
    
    .inline-loading {
        font-size: 0.8125rem;
        gap: 0.375rem;
    }
}

/* Print styles */
@media print {
    .loading-overlay,
    .skeleton-container,
    .inline-loading {
        display: none !important;
    }
}
</style>
@endPushOnce
