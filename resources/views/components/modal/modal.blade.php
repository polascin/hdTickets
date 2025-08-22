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
    color: var(--hd-text-muted, #6b7280);\n    border-radius: var(--hd-radius);\n    cursor: pointer;\n    transition: all var(--hd-transition);\n}\n\n.hd-modal__close:hover {\n    background: var(--hd-bg-muted, #f3f4f6);\n    color: var(--hd-text-primary, #1f2937);\n}\n\n.hd-modal__close:focus {\n    outline: 2px solid var(--hd-primary, #3b82f6);\n    outline-offset: 2px;\n}\n\n.hd-modal__close .hd-icon {\n    width: var(--hd-spacing-5);\n    height: var(--hd-spacing-5);\n}\n\n/* Modal Body */\n.hd-modal__body {\n    padding: var(--hd-spacing-6);\n    flex: 1;\n    min-height: 0;\n}\n\n.hd-modal__body--scrollable {\n    overflow-y: auto;\n}\n\n/* Modal Footer */\n.hd-modal__footer {\n    padding: var(--hd-spacing-6);\n    border-top: 1px solid var(--hd-border-color, #e5e7eb);\n    flex-shrink: 0;\n    display: flex;\n    align-items: center;\n    justify-content: flex-end;\n    gap: var(--hd-spacing-3);\n}\n\n/* Transitions */\n.hd-modal-transition-enter {\n    transition-property: opacity;\n    transition-duration: 300ms;\n    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);\n}\n\n.hd-modal-transition-enter-start {\n    opacity: 0;\n}\n\n.hd-modal-transition-enter-end {\n    opacity: 1;\n}\n\n.hd-modal-transition-leave {\n    transition-property: opacity;\n    transition-duration: 200ms;\n    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);\n}\n\n.hd-modal-transition-leave-start {\n    opacity: 1;\n}\n\n.hd-modal-transition-leave-end {\n    opacity: 0;\n}\n\n/* Content Animation */\n.hd-modal__content {\n    transform: scale(0.95);\n    transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);\n}\n\n.hd-modal--show .hd-modal__content {\n    transform: scale(1);\n}\n\n/* Mobile Responsive */\n@media (max-width: 640px) {\n    .hd-modal {\n        padding: var(--hd-spacing-2);\n    }\n    \n    .hd-modal__content {\n        max-width: 100%;\n        margin: 0;\n        border-radius: var(--hd-radius);\n    }\n    \n    .hd-modal__content--full {\n        max-height: calc(100vh - var(--hd-spacing-2) * 2);\n        border-radius: var(--hd-radius);\n    }\n    \n    .hd-modal__header,\n    .hd-modal__body,\n    .hd-modal__footer {\n        padding: var(--hd-spacing-4);\n    }\n    \n    .hd-modal__title {\n        font-size: var(--hd-text-base);\n    }\n}\n\n/* Dark Mode */\n@media (prefers-color-scheme: dark) {\n    .hd-modal__content {\n        background: var(--hd-bg-surface-dark, #1f2937);\n        border-color: var(--hd-border-color-dark, #374151);\n    }\n    \n    .hd-modal__header {\n        border-bottom-color: var(--hd-border-color-dark, #374151);\n    }\n    \n    .hd-modal__footer {\n        border-top-color: var(--hd-border-color-dark, #374151);\n    }\n    \n    .hd-modal__title {\n        color: var(--hd-text-primary-dark, #f9fafb);\n    }\n    \n    .hd-modal__close {\n        color: var(--hd-text-muted-dark, #9ca3af);\n    }\n    \n    .hd-modal__close:hover {\n        background: var(--hd-bg-muted-dark, #374151);\n        color: var(--hd-text-primary-dark, #f9fafb);\n    }\n}\n\n/* High Contrast Mode */\n@media (prefers-contrast: high) {\n    .hd-modal__content {\n        border: 2px solid;\n    }\n    \n    .hd-modal__close:focus {\n        outline-width: 3px;\n    }\n}\n\n/* Reduced Motion */\n@media (prefers-reduced-motion: reduce) {\n    .hd-modal-transition-enter,\n    .hd-modal-transition-leave,\n    .hd-modal__content {\n        transition: none;\n    }\n    \n    .hd-modal__backdrop {\n        backdrop-filter: none;\n        -webkit-backdrop-filter: none;\n    }\n}\n</style>\n@endpush\n@endonce
