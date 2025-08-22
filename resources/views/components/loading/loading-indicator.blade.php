@props([
    'type' => 'spinner', // spinner, dots, pulse, skeleton, bar
    'size' => 'default', // xs, sm, default, lg, xl
    'color' => 'primary', // primary, secondary, success, warning, error, gray
    'text' => null,
    'overlay' => false,
    'centered' => false,
    'fullHeight' => false
])

@php
    // Base loading classes using design system
    $loadingClasses = collect([
        'hd-loading',
        "hd-loading--{$type}",
        "hd-loading--{$size}",
        "hd-loading--{$color}",
        $overlay ? 'hd-loading--overlay' : '',
        $centered ? 'hd-loading--centered' : '',
        $fullHeight ? 'hd-loading--full-height' : ''
    ])->filter()->implode(' ');
    
    $textId = $text ? 'loading-text-' . uniqid() : null;
@endphp

<div 
    class="{{ $loadingClasses }}" 
    role="status" 
    aria-live="polite"
    @if($textId) aria-describedby="{{ $textId }}" @endif
    {{ $attributes }}
>
    @if($type === 'spinner')
        <div class="hd-loading__spinner" aria-hidden="true">
            <svg class="hd-loading__icon" viewBox="0 0 24 24" fill="none">
                <circle class="hd-loading__track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="hd-loading__path" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
    @elseif($type === 'dots')
        <div class="hd-loading__dots" aria-hidden="true">
            <div class="hd-loading__dot"></div>
            <div class="hd-loading__dot"></div>
            <div class="hd-loading__dot"></div>
        </div>
        
    @elseif($type === 'pulse')
        <div class="hd-loading__pulse" aria-hidden="true">
            <div class="hd-loading__pulse-circle"></div>
            <div class="hd-loading__pulse-circle"></div>
        </div>
        
    @elseif($type === 'skeleton')
        <div class="hd-loading__skeleton" aria-hidden="true">
            {{ $slot->isNotEmpty() ? $slot : '<div class="hd-loading__skeleton-line"></div>' }}
        </div>
        
    @elseif($type === 'bar')
        <div class="hd-loading__bar" aria-hidden="true">
            <div class="hd-loading__bar-fill"></div>
        </div>
    @endif
    
    @if($text)
        <div id="{{ $textId }}" class="hd-loading__text">
            {{ $text }}
        </div>
    @endif
    
    <!-- Screen reader only text -->
    <span class="sr-only">{{ $text ?: 'Loading...' }}</span>
</div>

@once
@push('styles')
<style>
/* Loading Base Styles */
.hd-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--hd-spacing-2);
}

.hd-loading--centered {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.hd-loading--full-height {
    min-height: 200px;
}

.hd-loading--overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(2px);
    z-index: var(--hd-z-loading, 999);
}

/* Spinner Loading */
.hd-loading__spinner {
    display: flex;
    align-items: center;
    justify-content: center;
}

.hd-loading__icon {
    animation: hd-spin 1s linear infinite;
}

.hd-loading__track {
    opacity: 0.25;
}

.hd-loading__path {
    opacity: 0.75;
}

/* Size Variants for Spinner */
.hd-loading--xs .hd-loading__icon { width: 16px; height: 16px; }
.hd-loading--sm .hd-loading__icon { width: 20px; height: 20px; }
.hd-loading .hd-loading__icon { width: 24px; height: 24px; }
.hd-loading--lg .hd-loading__icon { width: 32px; height: 32px; }
.hd-loading--xl .hd-loading__icon { width: 48px; height: 48px; }

/* Dots Loading */
.hd-loading__dots {
    display: flex;
    gap: var(--hd-spacing-1);
}

.hd-loading__dot {
    border-radius: 50%;
    animation: hd-dots-bounce 1.4s ease-in-out infinite both;
}

.hd-loading__dot:nth-child(1) { animation-delay: -0.32s; }
.hd-loading__dot:nth-child(2) { animation-delay: -0.16s; }

/* Size Variants for Dots */
.hd-loading--xs .hd-loading__dot { width: 4px; height: 4px; }
.hd-loading--sm .hd-loading__dot { width: 6px; height: 6px; }
.hd-loading .hd-loading__dot { width: 8px; height: 8px; }
.hd-loading--lg .hd-loading__dot { width: 10px; height: 10px; }
.hd-loading--xl .hd-loading__dot { width: 12px; height: 12px; }

/* Pulse Loading */
.hd-loading__pulse {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hd-loading__pulse-circle {
    position: absolute;
    border-radius: 50%;
    animation: hd-pulse 2s ease-in-out infinite;
    border: 2px solid currentColor;
}

.hd-loading__pulse-circle:nth-child(2) {
    animation-delay: 1s;
}

/* Size Variants for Pulse */
.hd-loading--xs .hd-loading__pulse-circle { width: 20px; height: 20px; }
.hd-loading--sm .hd-loading__pulse-circle { width: 24px; height: 24px; }
.hd-loading .hd-loading__pulse-circle { width: 32px; height: 32px; }
.hd-loading--lg .hd-loading__pulse-circle { width: 40px; height: 40px; }
.hd-loading--xl .hd-loading__pulse-circle { width: 48px; height: 48px; }

/* Skeleton Loading */
.hd-loading__skeleton {
    width: 100%;
}

.hd-loading__skeleton-line {
    height: 1rem;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: hd-shimmer 1.5s infinite;
    border-radius: var(--hd-radius-sm);
    margin-bottom: var(--hd-spacing-2);
}

.hd-loading__skeleton-line:last-child {
    margin-bottom: 0;
    width: 60%;
}

/* Bar Loading */
.hd-loading__bar {
    width: 100%;
    height: 4px;
    background: var(--hd-gray-200);
    border-radius: var(--hd-radius-sm);
    overflow: hidden;
}

.hd-loading__bar-fill {
    height: 100%;
    background: currentColor;
    border-radius: var(--hd-radius-sm);
    animation: hd-bar-progress 2s ease-in-out infinite;
}

/* Loading Text */
.hd-loading__text {
    font-size: var(--hd-text-sm);
    color: var(--hd-text-muted);
    text-align: center;
}

.hd-loading--xs .hd-loading__text { font-size: var(--hd-text-xs); }
.hd-loading--lg .hd-loading__text,
.hd-loading--xl .hd-loading__text { font-size: var(--hd-text-base); }

/* Color Variants */
.hd-loading--primary { color: var(--hd-primary); }
.hd-loading--secondary { color: var(--hd-secondary); }
.hd-loading--success { color: var(--hd-success); }
.hd-loading--warning { color: var(--hd-warning); }
.hd-loading--error { color: var(--hd-error); }
.hd-loading--gray { color: var(--hd-gray-500); }

/* Dots color variants */
.hd-loading--primary .hd-loading__dot { background: var(--hd-primary); }
.hd-loading--secondary .hd-loading__dot { background: var(--hd-secondary); }
.hd-loading--success .hd-loading__dot { background: var(--hd-success); }
.hd-loading--warning .hd-loading__dot { background: var(--hd-warning); }
.hd-loading--error .hd-loading__dot { background: var(--hd-error); }
.hd-loading--gray .hd-loading__dot { background: var(--hd-gray-500); }

/* Animations */
@keyframes hd-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes hd-dots-bounce {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes hd-pulse {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

@keyframes hd-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes hd-bar-progress {
    0% {
        transform: translateX(-100%);
    }
    50% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .hd-loading--overlay {
        background: rgba(0, 0, 0, 0.8);
    }
    
    .hd-loading__skeleton-line {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
        background-size: 200% 100%;
    }
    
    .hd-loading__bar {
        background: var(--hd-gray-700);
    }
    
    .hd-loading__text {
        color: var(--hd-gray-400);
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .hd-loading__icon,
    .hd-loading__dot,
    .hd-loading__pulse-circle,
    .hd-loading__skeleton-line,
    .hd-loading__bar-fill {
        animation: none;
    }
    
    .hd-loading__skeleton-line {
        background: var(--hd-gray-200);
    }
    
    .hd-loading--overlay {
        backdrop-filter: none;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hd-loading__track {
        opacity: 0.5;
    }
    
    .hd-loading__path {
        opacity: 1;
    }
}
</style>
@endpush
@endonce
