@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'error' => null,
    'helpText' => null,
    'size' => 'md',
    'variant' => 'default',
    'indeterminate' => false,
    'labelPosition' => 'right'
])

@php
    $checkboxId = $id ?? ($name ? $name . '-' . uniqid() : 'checkbox-' . uniqid());
    $labelId = $checkboxId . '-label';
    $errorId = $checkboxId . '-error';
    $helpId = $checkboxId . '-help';
    
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

    $checkboxClasses = [
        'hdt-checkbox__input',
        'rounded border transition-colors duration-150',
        'bg-surface-secondary',
        'focus:outline-none focus:ring-2 focus:ring-opacity-50',
        'disabled:bg-surface-tertiary disabled:cursor-not-allowed',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $error ? $variantClasses['error'] : $variantClasses['default']
    ];

    $isChecked = old($name) ? (is_array(old($name)) ? in_array($value, old($name)) : old($name) == $value) : $checked;
@endphp

<div class="hdt-form-group" 
     x-data="{
        checked: {{ $isChecked ? 'true' : 'false' }},
        indeterminate: {{ $indeterminate ? 'true' : 'false' }},
        init() {
            if (this.indeterminate) {
                this.$refs.checkbox.indeterminate = true;
            }
        }
     }">
    
    <div class="flex items-start {{ $labelPosition === 'left' ? 'flex-row-reverse' : '' }}">
        {{-- Checkbox Input --}}
        <div class="flex items-center {{ $labelPosition === 'left' ? 'ml-2' : 'mr-3' }}">
            <input 
                type="checkbox"
                id="{{ $checkboxId }}"
                name="{{ $name }}"
                value="{{ $value }}"
                class="{{ implode(' ', $checkboxClasses) }}"
                @if($isChecked) checked @endif
                @if($disabled) disabled aria-disabled="true" @endif
                @if($required) required aria-required="true" @endif
                @if($error) aria-invalid="true" @endif
                @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
                @if($label) aria-labelledby="{{ $labelId }}" @endif
                x-model="checked"
                x-ref="checkbox"
                @change="if (indeterminate) { indeterminate = false; $refs.checkbox.indeterminate = false; }"
                {{ $attributes->except(['class', 'id', 'name', 'value', 'checked', 'disabled', 'required']) }}
            />
        </div>

        {{-- Label and Content --}}
        @if($label)
            <div class="flex-1 min-w-0">
                <label for="{{ $checkboxId }}" 
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
/* Accessible checkbox styles */
.hdt-checkbox__input {
    font-family: var(--hdt-font-family-sans);
    min-width: 20px;
    min-height: 20px; /* WCAG minimum touch target size */
    cursor: pointer;
    position: relative;
}

.hdt-checkbox__input:focus {
    box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
}

/* Custom checkbox styling */
.hdt-checkbox__input:checked {
    background-color: currentColor;
    border-color: currentColor;
    background-size: 100% 100%;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='m13.854 3.646-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 9.586l6.646-6.647a.5.5 0 0 1 .708.707z'/%3e%3c/svg%3e");
}

/* Indeterminate state */
.hdt-checkbox__input:indeterminate {
    background-color: currentColor;
    border-color: currentColor;
    background-size: 100% 100%;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 16 16'%3e%3cpath d='M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z'/%3e%3c/svg%3e");
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .hdt-checkbox__input {
        border-width: 2px;
    }
    
    .hdt-checkbox__input:focus {
        border-width: 3px;
    }
    
    .hdt-checkbox__input[aria-invalid="true"] {
        border-width: 3px;
    }

    .hdt-checkbox__input:checked,
    .hdt-checkbox__input:indeterminate {
        background-color: var(--hdt-color-text-primary);
        border-color: var(--hdt-color-text-primary);
    }

    .hdt-checkbox__input:checked {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='white' stroke-width='2' d='m13.854 3.646-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 9.586l6.646-6.647a.5.5 0 0 1 .708.707z'/%3e%3c/svg%3e");
    }
}

/* Reduced motion support */
.hdt-reduced-motion .hdt-checkbox__input {
    transition: none;
}

/* Focus visible for keyboard users */
.hdt-checkbox__input:focus-visible {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Disabled state */
.hdt-checkbox__input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.hdt-checkbox__input:disabled + label {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Error state styling */
.hdt-checkbox__input[aria-invalid="true"] {
    border-color: var(--hdt-color-danger-600);
}

/* Size variants */
.hdt-checkbox__input.w-4 {
    min-width: 16px;
    min-height: 16px;
}

.hdt-checkbox__input.w-5 {
    min-width: 20px;
    min-height: 20px;
}

.hdt-checkbox__input.w-6 {
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
    .hdt-checkbox__input:hover:not(:disabled) {
        border-color: var(--hdt-color-primary-400);
    }
    
    .hdt-checkbox__input:hover:not(:disabled):not(:checked):not(:indeterminate) {
        background-color: var(--hdt-color-primary-50);
    }
}

/* Dark mode adjustments */
.hdt-theme-dark .hdt-checkbox__input:hover:not(:disabled):not(:checked):not(:indeterminate) {
    background-color: var(--hdt-color-primary-900);
}

/* Group checkbox support */
.hdt-checkbox-group .hdt-form-group {
    margin-bottom: var(--hdt-spacing-3);
}

.hdt-checkbox-group .hdt-form-group:last-child {
    margin-bottom: 0;
}

/* Custom checkbox appearance for different themes */
.hdt-theme-organizer .hdt-checkbox__input:checked,
.hdt-theme-organizer .hdt-checkbox__input:indeterminate {
    color: var(--hdt-color-organizer-600);
}

.hdt-theme-attendee .hdt-checkbox__input:checked,
.hdt-theme-attendee .hdt-checkbox__input:indeterminate {
    color: var(--hdt-color-attendee-600);
}

.hdt-theme-vendor .hdt-checkbox__input:checked,
.hdt-theme-vendor .hdt-checkbox__input:indeterminate {
    color: var(--hdt-color-vendor-600);
}

/* Animation for state changes */
.hdt-checkbox__input {
    transition: all 150ms ease-in-out;
}

.hdt-reduced-motion .hdt-checkbox__input {
    transition: none;
}

/* Screen reader enhancements */
.hdt-checkbox__input:focus + label::after {
    content: ' (focused)';
    position: absolute;
    left: -10000px;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
</style>
@endPushOnce