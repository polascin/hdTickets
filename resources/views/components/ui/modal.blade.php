@props([
    'name' => 'modal',
    'show' => false,
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl, full
    'closeable' => true,
    'backdrop' => true
])

@php
$maxWidthClass = match($maxWidth) {
    'sm' => 'hd-modal--sm',
    'md' => 'hd-modal--md', 
    'lg' => 'hd-modal--lg',
    'xl' => 'hd-modal--xl',
    '2xl' => 'hd-modal--2xl',
    'full' => 'hd-modal--full',
    default => 'hd-modal--md'
};
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-hidden');
            {{ $closeable ? '$nextTick(() => firstFocusable().focus());' : '' }}
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="hd-modal-overlay"
    style="display: none;"
    {{ $attributes }}
>
    <div x-show="show" class="hd-modal-backdrop {{ $backdrop ? '' : 'hd-modal-backdrop--transparent' }}"
         x-on:click="show = false" 
         x-transition:enter="hd-modal-backdrop--enter"
         x-transition:enter-start="hd-modal-backdrop--enter-start"
         x-transition:enter-end="hd-modal-backdrop--enter-end"
         x-transition:leave="hd-modal-backdrop--leave"
         x-transition:leave-start="hd-modal-backdrop--leave-start"
         x-transition:leave-end="hd-modal-backdrop--leave-end">
    </div>

    <div x-show="show" class="hd-modal-container">
        <div x-show="show"
             x-transition:enter="hd-modal--enter"
             x-transition:enter-start="hd-modal--enter-start" 
             x-transition:enter-end="hd-modal--enter-end"
             x-transition:leave="hd-modal--leave"
             x-transition:leave-start="hd-modal--leave-start"
             x-transition:leave-end="hd-modal--leave-end"
             x-on:click.stop
             class="hd-modal {{ $maxWidthClass }}">
            @if ($closeable)
                <div class="hd-modal__close">
                    <button 
                        x-on:click="show = false" 
                        type="button" 
                        class="hd-modal__close-button"
                        aria-label="Close modal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
