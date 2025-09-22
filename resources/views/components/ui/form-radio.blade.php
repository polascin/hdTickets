@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'error' => null,
    'helpText' => null,
    'size' => 'md',
    'variant' => 'default',
    'labelPosition' => 'right'
])

@php
    $radioId = $id ?? ($name ? $name . '-' . $value . '-' . uniqid() : 'radio-' . uniqid());
    $labelId = $radioId . '-label';
    $errorId = $radioId . '-error';
    $helpId = $radioId . '-help';
    
    $ariaDescribedBy = collect([
        $helpText ? $helpId : null,
        $error ? $errorId : null
    ])->filter()->implode(' ');

    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6'
    ];

    $variantClasses = [
        'default' => 'border-border-primary text-hd-primary-600 focus:border-hd-primary-600 focus:ring-hd-primary-600',
        'error' => 'border-hd-danger-600 text-hd-danger-600 focus:border-hd-danger-600 focus:ring-hd-danger-600'
    ];

    $radioClasses = [
        'hdt-radio__input',
        'rounded-full border transition-colors duration-150',
        'bg-surface-secondary',
        'focus:outline-none focus:ring-2 focus:ring-opacity-50',
        'disabled:bg-surface-tertiary disabled:cursor-not-allowed',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $error ? $variantClasses['error'] : $variantClasses['default']
    ];

    $isChecked = old($name) ? old($name) == $value : $checked;
@endphp

<div class="hdt-form-group" 
     x-data="{
        checked: '{{ old($name, $isChecked ? $value : '') }}'
     }">
    
    <div class="flex items-start {{ $labelPosition === 'left' ? 'flex-row-reverse' : '' }}">
        {{-- Radio Input --}}
        <div class="flex items-center {{ $labelPosition === 'left' ? 'ml-2' : 'mr-3' }}">
            <input 
                type="radio"
                id="{{ $radioId }}"
                name="{{ $name }}"
                value="{{ $value }}"
                class="{{ implode(' ', $radioClasses) }}"
                @if($isChecked) checked @endif
                @if($disabled) disabled aria-disabled="true" @endif
                @if($required) required aria-required="true" @endif
                @if($error) aria-invalid="true" @endif
                @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
                @if($label) aria-labelledby="{{ $labelId }}" @endif
                x-model="checked"
                {{ $attributes->except(['class', 'id', 'name', 'value', 'checked', 'disabled', 'required']) }}
            />
        </div>

        {{-- Label and Content --}}
        @if($label)
            <div class="flex-1 min-w-0">
                <label for="{{ $radioId }}" 
                       id="{{ $labelId }}"
                       class="block text-sm font-medium text-text-primary cursor-pointer {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }}">
                    {{ $label }}
                    @if($required)
                        <span class="text-hd-danger-600 ml-1" aria-label="required">*</span>
                    @endif
                </label>

                {{-- Help Text --}}
                @if($helpText)
                    <div id="{{ $helpId }}" 
                         class="mt-1 text-sm text-text-tertiary"
                         role="note">
                        {{ $helpText }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Error Message --}}
    @if($error)
        <div id="{{ $errorId }}" 
             class="mt-2 text-sm text-hd-danger-600"
             role="alert"
             aria-live="polite">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </div>
    @endif

    {{-- Laravel Validation Errors --}}
    @error($name)
        <div id="{{ $errorId }}" 
             class="mt-2 text-sm text-hd-danger-600"
             role="alert"
             aria-live="polite">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
        </div>
    @enderror
</div>

@pushOnce('styles')
<style>
/* Accessible radio button styles */
.hdt-radio__input {
    font-family: var(--hdt-font-family-sans);
    min-width: 20px;
    min-height: 20px; /* WCAG minimum touch target size */
    cursor: pointer;
    position: relative;
}

.hdt-radio__input:focus {
    box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
}

/* Custom radio button styling */
.hdt-radio__input:checked {
    background-color: currentColor;
    border-color: currentColor;
    background-size: 100% 100%;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .hdt-radio__input {
        border-width: 2px;
    }
    
    .hdt-radio__input:focus {
        border-width: 3px;
    }
    
    .hdt-radio__input[aria-invalid="true"] {
        border-width: 3px;
    }

    .hdt-radio__input:checked {
        background-color: var(--hdt-color-text-primary);
        border-color: var(--hdt-color-text-primary);
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='4' stroke='white' stroke-width='1'/%3e%3c/svg%3e");
    }
}

/* Reduced motion support */
.hdt-reduced-motion .hdt-radio__input {
    transition: none;
}

/* Focus visible for keyboard users */
.hdt-radio__input:focus-visible {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Disabled state */
.hdt-radio__input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.hdt-radio__input:disabled + label {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Error state styling */
.hdt-radio__input[aria-invalid="true"] {
    border-color: var(--hdt-color-danger-600);
}

/* Size variants */
.hdt-radio__input.w-4 {
    min-width: 16px;
    min-height: 16px;
}

.hdt-radio__input.w-5 {
    min-width: 20px;
    min-height: 20px;
}

.hdt-radio__input.w-6 {
    min-width: 24px;
    min-height: 24px;
}

/* Label cursor interaction */
.hdt-form-group label[for] {
    cursor: pointer;
}

.hdt-form-group label[for]:has(+ input:disabled),
.hdt-form-group input:disabled + label {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Hover states for non-touch devices */
@media (hover: hover) {
    .hdt-radio__input:hover:not(:disabled) {
        border-color: var(--hdt-color-primary-400);
    }
    
    .hdt-radio__input:hover:not(:disabled):not(:checked) {
        background-color: var(--hdt-color-primary-50);
    }
}

/* Dark mode adjustments */
.hdt-theme-dark .hdt-radio__input:hover:not(:disabled):not(:checked) {
    background-color: var(--hdt-color-primary-900);
}

/* Group radio support */
.hdt-radio-group .hdt-form-group {
    margin-bottom: var(--hdt-spacing-3);
}

.hdt-radio-group .hdt-form-group:last-child {
    margin-bottom: 0;
}

/* Radio group fieldset styling */
.hdt-radio-group {
    border: none;
    padding: 0;
    margin: 0;
}

.hdt-radio-group legend {
    font-weight: 600;
    font-size: var(--hdt-font-size-sm);
    color: var(--hdt-color-text-primary);
    margin-bottom: var(--hdt-spacing-3);
    padding: 0;
}

.hdt-radio-group legend.required::after {
    content: ' *';
    color: var(--hdt-color-danger-600);
}

/* Custom radio appearance for different themes */
.hdt-theme-organizer .hdt-radio__input:checked {
    color: var(--hdt-color-organizer-600);
}

.hdt-theme-attendee .hdt-radio__input:checked {
    color: var(--hdt-color-attendee-600);
}

.hdt-theme-vendor .hdt-radio__input:checked {
    color: var(--hdt-color-vendor-600);
}

/* Animation for state changes */
.hdt-radio__input {
    transition: all 150ms ease-in-out;
}

.hdt-reduced-motion .hdt-radio__input {
    transition: none;
}

/* Screen reader enhancements */
.hdt-radio__input:focus + label::after {
    content: ' (selected)';
    position: absolute;
    left: -10000px;
    width: 1px;
    height: 1px;
    overflow: hidden;
}

/* Keyboard navigation improvements */
.hdt-radio-group .hdt-radio__input {
    position: relative;
}

/* Arrow key navigation support */
.hdt-radio-group {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Radio group error state */
.hdt-radio-group.has-error .hdt-radio__input {
    border-color: var(--hdt-color-danger-600);
}

.hdt-radio-group.has-error legend {
    color: var(--hdt-color-danger-600);
}

/* Radio group help text */
.hdt-radio-group .hdt-radio-group__help {
    margin-top: var(--hdt-spacing-2);
    font-size: var(--hdt-font-size-sm);
    color: var(--hdt-color-text-tertiary);
}

/* Radio group error message */
.hdt-radio-group .hdt-radio-group__error {
    margin-top: var(--hdt-spacing-2);
    font-size: var(--hdt-font-size-sm);
    color: var(--hdt-color-danger-600);
    display: flex;
    align-items: center;
}

.hdt-radio-group .hdt-radio-group__error svg {
    margin-right: var(--hdt-spacing-1);
    flex-shrink: 0;
}

/* Focus management for radio groups */
.hdt-radio-group:focus-within {
    outline: none;
}

.hdt-radio-group .hdt-radio__input:focus {
    z-index: 10;
}
</style>
@endPushOnce