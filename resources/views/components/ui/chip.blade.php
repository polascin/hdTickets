@props([
    'variant' => 'default', // default, primary, secondary, success, warning, danger, info
    'size' => 'md', // sm, md, lg
    'selected' => false,
    'disabled' => false,
    'clickable' => false,
    'removable' => false,
    'icon' => null,
    'avatar' => null,
    'href' => null,
    'value' => null,
    'ariaLabel' => null
])

@php
    $chipId = 'chip-' . uniqid();
    $element = $href ? 'a' : ($clickable ? 'button' : 'span');
    
    $sizeClasses = [
        'sm' => 'hdt-chip--sm',
        'md' => 'hdt-chip--md',
        'lg' => 'hdt-chip--lg'
    ];
    
    $variantClasses = [
        'default' => 'hdt-chip--default',
        'primary' => 'hdt-chip--primary',
        'secondary' => 'hdt-chip--secondary',
        'success' => 'hdt-chip--success',
        'warning' => 'hdt-chip--warning',
        'danger' => 'hdt-chip--danger',
        'info' => 'hdt-chip--info'
    ];
    
    $chipClasses = [
        'hdt-chip',
        'inline-flex items-center justify-center',
        'font-medium text-center rounded-full',
        'transition-all duration-150',
        'border border-transparent',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $variantClasses[$variant] ?? $variantClasses['default'],
        $selected ? 'hdt-chip--selected' : '',
        $disabled ? 'hdt-chip--disabled' : '',
        $clickable || $href ? 'hdt-chip--interactive' : '',
        $removable ? 'hdt-chip--removable' : ''
    ];
@endphp

<{{ $element }}
    @if($href) href="{{ $href }}" @endif
    @if($clickable && $element === 'button') type="button" @endif
    @if($disabled) disabled @endif
    id="{{ $chipId }}"
    class="{{ implode(' ', array_filter($chipClasses)) }}"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if($selected) aria-pressed="true" @endif
    @if($value) data-value="{{ $value }}" @endif
    {{ $attributes->except(['class', 'id', 'href', 'type', 'disabled', 'aria-label', 'aria-pressed', 'data-value']) }}
    x-data="{
        selected: {{ $selected ? 'true' : 'false' }},
        removed: false,
        toggleSelected() {
            if (!{{ $disabled ? 'true' : 'false' }}) {
                this.selected = !this.selected;
                this.$dispatch('chip-toggle', { 
                    id: '{{ $chipId }}', 
                    selected: this.selected,
                    value: '{{ $value }}'
                });
            }
        },
        handleRemove() {
            this.removed = true;
            this.$dispatch('chip-removed', { 
                id: '{{ $chipId }}',
                value: '{{ $value }}'
            });
        }
    }"
    x-show="!removed"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-75"
    @if($clickable) @click="toggleSelected()" @endif>

    {{-- Avatar --}}
    @if($avatar)
        <div class="hdt-chip__avatar">
            @if(is_string($avatar) && filter_var($avatar, FILTER_VALIDATE_URL))
                <img src="{{ $avatar }}" 
                     alt=""
                     class="hdt-chip__avatar-image"
                     loading="lazy">
            @else
                <div class="hdt-chip__avatar-text">
                    {{ $avatar }}
                </div>
            @endif
        </div>
    @endif

    {{-- Icon --}}
    @if($icon && !$avatar)
        <span class="hdt-chip__icon" aria-hidden="true">
            {!! $icon !!}
        </span>
    @endif

    {{-- Content --}}
    <span class="hdt-chip__content">
        {{ $slot }}
    </span>

    {{-- Remove Button --}}
    @if($removable)
        <button type="button" 
                class="hdt-chip__remove"
                @click.stop="handleRemove()"
                aria-label="Remove {{ $ariaLabel ?: 'chip' }}"
                tabindex="0">
            <svg class="hdt-chip__remove-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif

</{{ $element }}>

@pushOnce('styles')
<style>
/* Chip Base Styles */
.hdt-chip {
    --hdt-chip-bg: var(--hdt-color-surface-secondary);
    --hdt-chip-text: var(--hdt-color-text-primary);
    --hdt-chip-border: var(--hdt-color-border-primary);
    --hdt-chip-hover: var(--hdt-color-surface-tertiary);
    --hdt-chip-selected: var(--hdt-color-primary-100);
    --hdt-chip-selected-text: var(--hdt-color-primary-800);
    
    background-color: var(--hdt-chip-bg);
    color: var(--hdt-chip-text);
    border-color: var(--hdt-chip-border);
    text-decoration: none;
    font-family: var(--hdt-font-family-sans);
    user-select: none;
    position: relative;
    overflow: hidden;
    max-width: 100%;
}

/* Chip Sizes */
.hdt-chip--sm {
    padding: 0.25rem 0.75rem;
    font-size: var(--hdt-font-size-xs);
    line-height: 1.25;
    gap: 0.25rem;
    min-height: 24px;
}

.hdt-chip--sm .hdt-chip__avatar {
    width: 1rem;
    height: 1rem;
    margin-left: -0.25rem;
}

.hdt-chip--sm .hdt-chip__icon {
    width: 0.875rem;
    height: 0.875rem;
}

.hdt-chip--md {
    padding: 0.375rem 1rem;
    font-size: var(--hdt-font-size-sm);
    line-height: 1.25;
    gap: 0.375rem;
    min-height: 32px;
}

.hdt-chip--md .hdt-chip__avatar {
    width: 1.25rem;
    height: 1.25rem;
    margin-left: -0.375rem;
}

.hdt-chip--md .hdt-chip__icon {
    width: 1rem;
    height: 1rem;
}

.hdt-chip--lg {
    padding: 0.5rem 1.25rem;
    font-size: var(--hdt-font-size-base);
    line-height: 1.5;
    gap: 0.5rem;
    min-height: 40px;
}

.hdt-chip--lg .hdt-chip__avatar {
    width: 1.5rem;
    height: 1.5rem;
    margin-left: -0.5rem;
}

.hdt-chip--lg .hdt-chip__icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Chip Variants */
.hdt-chip--default {
    --hdt-chip-bg: var(--hdt-color-surface-secondary);
    --hdt-chip-text: var(--hdt-color-text-primary);
    --hdt-chip-border: var(--hdt-color-border-primary);
}

.hdt-chip--primary {
    --hdt-chip-bg: var(--hdt-color-primary-100);
    --hdt-chip-text: var(--hdt-color-primary-800);
    --hdt-chip-border: var(--hdt-color-primary-200);
    --hdt-chip-hover: var(--hdt-color-primary-200);
    --hdt-chip-selected: var(--hdt-color-primary-600);
    --hdt-chip-selected-text: white;
}

.hdt-chip--secondary {
    --hdt-chip-bg: var(--hdt-color-surface-tertiary);
    --hdt-chip-text: var(--hdt-color-text-secondary);
    --hdt-chip-border: var(--hdt-color-border-secondary);
}

.hdt-chip--success {
    --hdt-chip-bg: var(--hdt-color-success-100);
    --hdt-chip-text: var(--hdt-color-success-800);
    --hdt-chip-border: var(--hdt-color-success-200);
    --hdt-chip-hover: var(--hdt-color-success-200);
    --hdt-chip-selected: var(--hdt-color-success-600);
    --hdt-chip-selected-text: white;
}

.hdt-chip--warning {
    --hdt-chip-bg: var(--hdt-color-warning-100);
    --hdt-chip-text: var(--hdt-color-warning-800);
    --hdt-chip-border: var(--hdt-color-warning-200);
    --hdt-chip-hover: var(--hdt-color-warning-200);
    --hdt-chip-selected: var(--hdt-color-warning-600);
    --hdt-chip-selected-text: white;
}

.hdt-chip--danger {
    --hdt-chip-bg: var(--hdt-color-danger-100);
    --hdt-chip-text: var(--hdt-color-danger-800);
    --hdt-chip-border: var(--hdt-color-danger-200);
    --hdt-chip-hover: var(--hdt-color-danger-200);
    --hdt-chip-selected: var(--hdt-color-danger-600);
    --hdt-chip-selected-text: white;
}

.hdt-chip--info {
    --hdt-chip-bg: var(--hdt-color-info-100);
    --hdt-chip-text: var(--hdt-color-info-800);
    --hdt-chip-border: var(--hdt-color-info-200);
    --hdt-chip-hover: var(--hdt-color-info-200);
    --hdt-chip-selected: var(--hdt-color-info-600);
    --hdt-chip-selected-text: white;
}

/* Interactive States */
.hdt-chip--interactive {
    cursor: pointer;
}

.hdt-chip--interactive:hover:not(.hdt-chip--disabled) {
    background-color: var(--hdt-chip-hover);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hdt-chip--interactive:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-chip--interactive:active:not(.hdt-chip--disabled) {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Selected State */
.hdt-chip--selected {
    background-color: var(--hdt-chip-selected);
    color: var(--hdt-chip-selected-text);
    border-color: var(--hdt-chip-selected);
}

/* Disabled State */
.hdt-chip--disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

/* Avatar Styles */
.hdt-chip__avatar {
    border-radius: 50%;
    overflow: hidden;
    background: var(--hdt-color-surface-tertiary);
    flex-shrink: 0;
}

.hdt-chip__avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hdt-chip__avatar-text {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--hdt-color-text-secondary);
}

/* Icon Styles */
.hdt-chip__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.hdt-chip__icon svg {
    width: 100%;
    height: 100%;
}

/* Content */
.hdt-chip__content {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Remove Button */
.hdt-chip__remove {
    margin-left: auto;
    margin-right: -0.25rem;
    padding: 0.125rem;
    border: none;
    background: none;
    color: currentColor;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 150ms ease;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.hdt-chip__remove:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.hdt-chip__remove:focus {
    outline: 1px solid currentColor;
    outline-offset: 1px;
}

.hdt-chip__remove-icon {
    width: 0.75rem;
    height: 0.75rem;
}

/* Dark Mode Adjustments */
.hdt-theme-dark .hdt-chip {
    --hdt-chip-selected: var(--hdt-color-primary-800);
    --hdt-chip-selected-text: var(--hdt-color-primary-100);
}

.hdt-theme-dark .hdt-chip__remove:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Role-Specific Theming */
.hdt-theme-organizer .hdt-chip--primary {
    --hdt-chip-bg: var(--hdt-color-organizer-100);
    --hdt-chip-text: var(--hdt-color-organizer-800);
    --hdt-chip-border: var(--hdt-color-organizer-200);
    --hdt-chip-selected: var(--hdt-color-organizer-600);
}

.hdt-theme-attendee .hdt-chip--primary {
    --hdt-chip-bg: var(--hdt-color-attendee-100);
    --hdt-chip-text: var(--hdt-color-attendee-800);
    --hdt-chip-border: var(--hdt-color-attendee-200);
    --hdt-chip-selected: var(--hdt-color-attendee-600);
}

.hdt-theme-vendor .hdt-chip--primary {
    --hdt-chip-bg: var(--hdt-color-vendor-100);
    --hdt-chip-text: var(--hdt-color-vendor-800);
    --hdt-chip-border: var(--hdt-color-vendor-200);
    --hdt-chip-selected: var(--hdt-color-vendor-600);
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-chip {
        border-width: 2px;
    }
    
    .hdt-chip--selected {
        border-width: 3px;
        font-weight: 600;
    }
}

/* Reduced Motion */
.hdt-reduced-motion .hdt-chip {
    transition: none;
}

.hdt-reduced-motion .hdt-chip--interactive:hover {
    transform: none;
}

/* Print Styles */
@media print {
    .hdt-chip {
        background: white !important;
        color: black !important;
        border: 1px solid black !important;
    }
    
    .hdt-chip__remove {
        display: none;
    }
}

/* Touch Device Optimizations */
@media (pointer: coarse) {
    .hdt-chip--sm {
        min-height: 32px;
        padding: 0.375rem 0.875rem;
    }
    
    .hdt-chip--md {
        min-height: 40px;
        padding: 0.5rem 1.125rem;
    }
    
    .hdt-chip--lg {
        min-height: 48px;
        padding: 0.625rem 1.375rem;
    }
}
</style>
@endPushOnce