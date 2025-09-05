@props([
    'show' => false,
    'title' => null,
    'size' => 'default', // sm, default, lg, xl, full
    'closable' => true,
    'centered' => true,
    'backdrop' => true,
    'backdropClose' => true,
    'keyboard' => true,
    'persistent' => false, // Modal can't be closed by clicking backdrop or pressing escape
    'scrollable' => false,
    'id' => null
])

@php
    $modalId = $id ?: 'modal-' . uniqid();
    
    // Modal classes using design system
    $modalClasses = collect([
        'hd-modal',
        $show ? 'hd-modal--show' : 'hd-modal--hidden',
        $centered ? 'hd-modal--centered' : '',
        $scrollable ? 'hd-modal--scrollable' : ''
    ])->filter()->implode(' ');
    
    // Modal content classes based on size
    $contentClasses = collect([
        'hd-modal__content',
        match($size) {
            'sm' => 'hd-modal__content--sm',
            'lg' => 'hd-modal__content--lg',
            'xl' => 'hd-modal__content--xl',
            'full' => 'hd-modal__content--full',
            default => ''
        }
    ])->filter()->implode(' ');
@endphp

<!-- Modal Backdrop and Container -->
<div 
    id="{{ $modalId }}"
    class="{{ $modalClasses }}"
    role="dialog"
    aria-modal="true"
    @if($title) aria-labelledby="{{ $modalId }}-title" @endif
    x-data="{ 
        show: @js($show),
        closable: @js($closable),
        backdropClose: @js($backdropClose),
        keyboard: @js($keyboard),
        persistent: @js($persistent),
        close() {
            if (this.persistent) return;
            this.show = false;
            $dispatch('modal-closed', { id: '{{ $modalId }}' });
        },
        open() {
            this.show = true;
            $dispatch('modal-opened', { id: '{{ $modalId }}' });
        }
    }"
    x-show="show"
    x-transition:enter="hd-modal-transition-enter"
    x-transition:enter-start="hd-modal-transition-enter-start"
    x-transition:enter-end="hd-modal-transition-enter-end"
    x-transition:leave="hd-modal-transition-leave"
    x-transition:leave-start="hd-modal-transition-leave-start"
    x-transition:leave-end="hd-modal-transition-leave-end"
    @keydown.escape.window="keyboard && close()"
    @modal-open.window="if ($event.detail.id === '{{ $modalId }}') open()"
    @modal-close.window="if ($event.detail.id === '{{ $modalId }}') close()"
    x-init="
        // Prevent body scroll when modal is open
        $watch('show', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
                // Focus management
                $nextTick(() => {
                    const firstFocusable = $el.querySelector('[autofocus], input, select, textarea, button, [tabindex]:not([tabindex=\\\"-1\\\"])');
                    if (firstFocusable) firstFocusable.focus();
                });
            } else {
                document.body.style.overflow = '';
            }
        });
    "
    style="display: none;"
>
    <!-- Backdrop -->
    @if($backdrop)
        <div 
            class="hd-modal__backdrop"
            @if($backdropClose && !$persistent) @click="close()" @endif
        ></div>
    @endif
    
    <!-- Modal Content Container -->
    <div class="hd-modal__container">
        <div class="{{ $contentClasses }}" @click.stop>
            <!-- Modal Header -->
            @if($title || $closable)
                <header class="hd-modal__header">
                    @if($title)
                        <h2 id="{{ $modalId }}-title" class="hd-modal__title">
                            {{ $title }}
                        </h2>
                    @endif
                    
                    @if($closable && !$persistent)
                        <button 
                            type="button"
                            class="hd-modal__close"
                            @click="close()"
                            aria-label="Close modal"
                        >
                            <svg class="hd-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </header>
            @endif
            
            <!-- Modal Body -->
            <main class="hd-modal__body {{ $scrollable ? 'hd-modal__body--scrollable' : '' }}">
                {{ $slot }}
            </main>
            
            <!-- Modal Footer (if provided) -->
            @isset($footer)
                <footer class="hd-modal__footer">
                    {{ $footer }}
                </footer>
            @endisset
        </div>
    </div>
</div>

<!-- Loading State Slot -->
@isset($loading)
    <div class="hd-modal__loading" x-show="show && loading">
        {{ $loading }}
    </div>
@endisset

@once
@push('styles')
<style>
/* Modal Base Styles */
.hd-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: var(--hd-z-modal, 1000);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--hd-spacing-4);
}

.hd-modal--hidden {
    display: none;
}

.hd-modal--centered {
    align-items: center;
}

.hd-modal--scrollable {
    align-items: flex-start;
    padding-top: var(--hd-spacing-8);
    padding-bottom: var(--hd-spacing-8);
}

/* Modal Backdrop */
.hd-modal__backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Modal Container */
.hd-modal__container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.hd-modal--scrollable .hd-modal__container {
    align-items: flex-start;
}

/* Modal Content */
.hd-modal__content {
    position: relative;
    background: var(--hd-bg-surface, #ffffff);
    border-radius: var(--hd-radius-lg);
    box-shadow: var(--hd-shadow-xl);
    border: 1px solid var(--hd-border-color, #e5e7eb);
    pointer-events: all;
    width: 100%;
    max-width: 32rem; /* 512px - default size */
    max-height: calc(100vh - var(--hd-spacing-8) * 2);
    display: flex;
    flex-direction: column;
}

/* Size Variants */
.hd-modal__content--sm {
    max-width: 24rem; /* 384px */
}

.hd-modal__content--lg {
    max-width: 48rem; /* 768px */
}

.hd-modal__content--xl {
    max-width: 64rem; /* 1024px */
}

.hd-modal__content--full {
    max-width: calc(100vw - var(--hd-spacing-8) * 2);
    max-height: calc(100vh - var(--hd-spacing-4) * 2);
}

/* Modal Header */
.hd-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--hd-spacing-6);
    border-bottom: 1px solid var(--hd-border-color, #e5e7eb);
    flex-shrink: 0;
}

.hd-modal__title {
    font-size: var(--hd-text-lg);
    font-weight: var(--hd-font-semibold);
    color: var(--hd-text-primary, #1f2937);
    margin: 0;
    line-height: 1.2;
}

.hd-modal__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: var(--hd-spacing-8);
    height: var(--hd-spacing-8);
    border: none;
    background: transparent;
    color: var(--hd-text-muted, #6b7280);
    border-radius: var(--hd-radius);
    cursor: pointer;
    transition: all var(--hd-transition);
}

.hd-modal__close:hover {
    background: var(--hd-bg-muted, #f3f4f6);
    color: var(--hd-text-primary, #1f2937);
}

.hd-modal__close:focus {
    outline: 2px solid var(--hd-primary, #3b82f6);
    outline-offset: 2px;
}

.hd-modal__close .hd-icon {
    width: var(--hd-spacing-5);
    height: var(--hd-spacing-5);
}

/* Modal Body */
.hd-modal__body {
    padding: var(--hd-spacing-6);
    flex: 1;
    min-height: 0;
}

.hd-modal__body--scrollable {
    overflow-y: auto;
}

/* Modal Footer */
.hd-modal__footer {
    padding: var(--hd-spacing-6);
    border-top: 1px solid var(--hd-border-color, #e5e7eb);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: var(--hd-spacing-3);
}

/* Transitions */
.hd-modal-transition-enter {
    transition-property: opacity;
    transition-duration: 300ms;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.hd-modal-transition-enter-start {
    opacity: 0;
}

.hd-modal-transition-enter-end {
    opacity: 1;
}

.hd-modal-transition-leave {
    transition-property: opacity;
    transition-duration: 200ms;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.hd-modal-transition-leave-start {
    opacity: 1;
}

.hd-modal-transition-leave-end {
    opacity: 0;
}

/* Content Animation */
.hd-modal__content {
    transform: scale(0.95);
    transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.hd-modal--show .hd-modal__content {
    transform: scale(1);
}

/* Mobile Responsive */
@media (max-width: 640px) {
    .hd-modal {
        padding: var(--hd-spacing-2);
    }
    
    .hd-modal__content {
        max-width: 100%;
        margin: 0;
        border-radius: var(--hd-radius);
    }
    
    .hd-modal__content--full {
        max-height: calc(100vh - var(--hd-spacing-2) * 2);
        border-radius: var(--hd-radius);
    }
    
    .hd-modal__header,
    .hd-modal__body,
    .hd-modal__footer {
        padding: var(--hd-spacing-4);
    }
    
    .hd-modal__title {
        font-size: var(--hd-text-base);
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .hd-modal__content {
        background: var(--hd-bg-surface-dark, #1f2937);
        border-color: var(--hd-border-color-dark, #374151);
    }
    
    .hd-modal__header {
        border-bottom-color: var(--hd-border-color-dark, #374151);
    }
    
    .hd-modal__footer {
        border-top-color: var(--hd-border-color-dark, #374151);
    }
    
    .hd-modal__title {
        color: var(--hd-text-primary-dark, #f9fafb);
    }
    
    .hd-modal__close {
        color: var(--hd-text-muted-dark, #9ca3af);
    }
    
    .hd-modal__close:hover {
        background: var(--hd-bg-muted-dark, #374151);
        color: var(--hd-text-primary-dark, #f9fafb);
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hd-modal__content {
        border: 2px solid;
    }
    
    .hd-modal__close:focus {
        outline-width: 3px;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .hd-modal-transition-enter,
    .hd-modal-transition-leave,
    .hd-modal__content {
        transition: none;
    }
    
    .hd-modal__backdrop {
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }
}
</style>
@endpush
@endonce
