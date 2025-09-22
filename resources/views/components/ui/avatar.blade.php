@props([
    'src' => null,
    'alt' => null,
    'name' => null,
    'size' => 'md', // xs, sm, md, lg, xl, 2xl
    'shape' => 'circle', // circle, square, rounded
    'status' => null, // online, offline, busy, away
    'statusPosition' => 'bottom-right', // top-right, top-left, bottom-right, bottom-left
    'fallback' => null,
    'href' => null,
    'clickable' => false,
    'loading' => 'lazy',
    'showTooltip' => true
])

@php
    $avatarId = 'avatar-' . uniqid();
    $element = $href ? 'a' : ($clickable ? 'button' : 'div');
    
    // Generate initials from name
    $initials = $name ? collect(explode(' ', $name))->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('') : null;
    
    $sizeClasses = [
        'xs' => 'hdt-avatar--xs',
        'sm' => 'hdt-avatar--sm',
        'md' => 'hdt-avatar--md',
        'lg' => 'hdt-avatar--lg',
        'xl' => 'hdt-avatar--xl',
        '2xl' => 'hdt-avatar--2xl'
    ];
    
    $shapeClasses = [
        'circle' => 'hdt-avatar--circle',
        'square' => 'hdt-avatar--square',
        'rounded' => 'hdt-avatar--rounded'
    ];
    
    $statusClasses = [
        'online' => 'hdt-avatar-status--online',
        'offline' => 'hdt-avatar-status--offline', 
        'busy' => 'hdt-avatar-status--busy',
        'away' => 'hdt-avatar-status--away'
    ];
    
    $statusPositionClasses = [
        'top-right' => 'hdt-avatar-status--top-right',
        'top-left' => 'hdt-avatar-status--top-left',
        'bottom-right' => 'hdt-avatar-status--bottom-right',
        'bottom-left' => 'hdt-avatar-status--bottom-left'
    ];
    
    $avatarClasses = [
        'hdt-avatar',
        'relative inline-flex items-center justify-center',
        'overflow-hidden',
        'transition-all duration-150',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $shapeClasses[$shape] ?? $shapeClasses['circle'],
        $href || $clickable ? 'hdt-avatar--interactive' : ''
    ];
@endphp

<{{ $element }}
    @if($href) href="{{ $href }}" @endif
    @if($clickable && $element === 'button') type="button" @endif
    id="{{ $avatarId }}"
    class="{{ implode(' ', array_filter($avatarClasses)) }}"
    @if($showTooltip && ($name || $alt)) 
        title="{{ $name ?: $alt }}"
        aria-label="{{ $name ?: $alt }}"
    @endif
    {{ $attributes->except(['class', 'id', 'href', 'type', 'title', 'aria-label']) }}
    x-data="{
        imageLoaded: false,
        imageError: false,
        handleImageLoad() {
            this.imageLoaded = true;
        },
        handleImageError() {
            this.imageError = true;
        }
    }">

    {{-- Avatar Image --}}
    @if($src)
        <img 
            src="{{ $src }}"
            alt="{{ $alt ?: ($name ? $name . ' avatar' : 'Avatar') }}"
            class="hdt-avatar__image"
            loading="{{ $loading }}"
            x-show="!imageError"
            @load="handleImageLoad()"
            @error="handleImageError()">
    @endif

    {{-- Fallback Content --}}
    <div class="hdt-avatar__fallback"
         x-show="{{ $src ? 'imageError || !imageLoaded' : 'true' }}">
        @if($fallback)
            {!! $fallback !!}
        @elseif($initials)
            <span class="hdt-avatar__initials">{{ $initials }}</span>
        @else
            {{-- Default user icon --}}
            <svg class="hdt-avatar__icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        @endif
    </div>

    {{-- Loading State --}}
    @if($src)
        <div class="hdt-avatar__loading" x-show="!imageLoaded && !imageError">
            <div class="hdt-avatar__spinner"></div>
        </div>
    @endif

    {{-- Status Indicator --}}
    @if($status)
        <div class="hdt-avatar__status {{ $statusClasses[$status] ?? '' }} {{ $statusPositionClasses[$statusPosition] ?? $statusPositionClasses['bottom-right'] }}"
             role="status"
             aria-label="{{ ucfirst($status) }}"
             title="{{ ucfirst($status) }}">
            <div class="hdt-avatar__status-dot {{ $status === 'online' ? 'hdt-avatar__status-dot--pulse' : '' }}"></div>
        </div>
    @endif

</{{ $element }}>

@pushOnce('styles')
<style>
/* Avatar Base Styles */
.hdt-avatar {
    background-color: var(--hdt-color-surface-tertiary);
    color: var(--hdt-color-text-secondary);
    font-family: var(--hdt-font-family-sans);
    font-weight: 600;
    user-select: none;
}

/* Avatar Sizes */
.hdt-avatar--xs {
    width: 1.5rem;
    height: 1.5rem;
    font-size: 0.625rem;
}

.hdt-avatar--sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.75rem;
}

.hdt-avatar--md {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 0.875rem;
}

.hdt-avatar--lg {
    width: 3rem;
    height: 3rem;
    font-size: 1rem;
}

.hdt-avatar--xl {
    width: 4rem;
    height: 4rem;
    font-size: 1.25rem;
}

.hdt-avatar--2xl {
    width: 5rem;
    height: 5rem;
    font-size: 1.5rem;
}

/* Avatar Shapes */
.hdt-avatar--circle {
    border-radius: 50%;
}

.hdt-avatar--square {
    border-radius: 0;
}

.hdt-avatar--rounded {
    border-radius: var(--hdt-border-radius-md);
}

/* Avatar Image */
.hdt-avatar__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Avatar Fallback */
.hdt-avatar__fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--hdt-color-surface-tertiary);
    color: var(--hdt-color-text-secondary);
}

.hdt-avatar__initials {
    font-weight: 600;
    text-transform: uppercase;
}

.hdt-avatar__icon {
    width: 60%;
    height: 60%;
}

/* Loading State */
.hdt-avatar__loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--hdt-color-surface-tertiary);
}

.hdt-avatar__spinner {
    width: 50%;
    height: 50%;
    border: 2px solid var(--hdt-color-border-secondary);
    border-top-color: var(--hdt-color-primary-600);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.hdt-reduced-motion .hdt-avatar__spinner {
    animation: none;
}

/* Status Indicator */
.hdt-avatar__status {
    position: absolute;
    z-index: 10;
}

/* Status Positions */
.hdt-avatar__status--top-right {
    top: 0;
    right: 0;
    transform: translate(25%, -25%);
}

.hdt-avatar__status--top-left {
    top: 0;
    left: 0;
    transform: translate(-25%, -25%);
}

.hdt-avatar__status--bottom-right {
    bottom: 0;
    right: 0;
    transform: translate(25%, 25%);
}

.hdt-avatar__status--bottom-left {
    bottom: 0;
    left: 0;
    transform: translate(-25%, 25%);
}

/* Status Dot */
.hdt-avatar__status-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    border: 2px solid var(--hdt-color-surface-primary);
}

/* Status Colors */
.hdt-avatar-status--online .hdt-avatar__status-dot {
    background-color: var(--hdt-color-success-500);
}

.hdt-avatar-status--offline .hdt-avatar__status-dot {
    background-color: var(--hdt-color-text-quaternary);
}

.hdt-avatar-status--busy .hdt-avatar__status-dot {
    background-color: var(--hdt-color-danger-500);
}

.hdt-avatar-status--away .hdt-avatar__status-dot {
    background-color: var(--hdt-color-warning-500);
}

/* Status Pulse Animation */
.hdt-avatar__status-dot--pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.hdt-reduced-motion .hdt-avatar__status-dot--pulse {
    animation: none;
}

/* Interactive States */
.hdt-avatar--interactive {
    cursor: pointer;
}

.hdt-avatar--interactive:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.hdt-avatar--interactive:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-avatar--interactive:active {
    transform: scale(1.02);
}

.hdt-reduced-motion .hdt-avatar--interactive:hover,
.hdt-reduced-motion .hdt-avatar--interactive:active {
    transform: none;
}

/* Size-specific status dots */
.hdt-avatar--xs .hdt-avatar__status-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-width: 1px;
}

.hdt-avatar--sm .hdt-avatar__status-dot {
    width: 0.625rem;
    height: 0.625rem;
    border-width: 1.5px;
}

.hdt-avatar--md .hdt-avatar__status-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-width: 2px;
}

.hdt-avatar--lg .hdt-avatar__status-dot {
    width: 0.875rem;
    height: 0.875rem;
    border-width: 2px;
}

.hdt-avatar--xl .hdt-avatar__status-dot {
    width: 1rem;
    height: 1rem;
    border-width: 2.5px;
}

.hdt-avatar--2xl .hdt-avatar__status-dot {
    width: 1.25rem;
    height: 1.25rem;
    border-width: 3px;
}

/* Dark Mode Adjustments */
.hdt-theme-dark .hdt-avatar__fallback {
    background-color: var(--hdt-color-surface-quaternary);
    color: var(--hdt-color-text-tertiary);
}

.hdt-theme-dark .hdt-avatar__loading {
    background-color: var(--hdt-color-surface-quaternary);
}

.hdt-theme-dark .hdt-avatar__status-dot {
    border-color: var(--hdt-color-surface-primary);
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-avatar {
        border: 2px solid var(--hdt-color-border-primary);
    }
    
    .hdt-avatar__status-dot {
        border-width: 3px;
    }
}

/* Print Styles */
@media print {
    .hdt-avatar {
        border: 1px solid black;
        background: white;
        color: black;
    }
    
    .hdt-avatar__status {
        display: none;
    }
}

/* Touch Device Optimizations */
@media (pointer: coarse) {
    .hdt-avatar--interactive {
        min-width: 44px;
        min-height: 44px;
    }
}

/* Avatar Groups */
.hdt-avatar-group {
    display: flex;
    align-items: center;
}

.hdt-avatar-group .hdt-avatar {
    margin-left: -0.5rem;
    border: 2px solid var(--hdt-color-surface-primary);
    z-index: 1;
}

.hdt-avatar-group .hdt-avatar:first-child {
    margin-left: 0;
}

.hdt-avatar-group .hdt-avatar:hover {
    z-index: 10;
}

/* Accessibility Enhancements */
.hdt-avatar[role="img"] {
    speak: literal-punctuation;
}

/* Error State */
.hdt-avatar--error {
    background-color: var(--hdt-color-danger-100);
    color: var(--hdt-color-danger-600);
}

.hdt-theme-dark .hdt-avatar--error {
    background-color: var(--hdt-color-danger-900);
    color: var(--hdt-color-danger-300);
}
</style>
@endPushOnce