@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'helpText' => null,
    'placeholder' => null,
    'value' => null,
    'size' => 'md',
    'variant' => 'default'
])

@php
    $inputId = $id ?? ($name ? $name . '-' . uniqid() : 'input-' . uniqid());
    $labelId = $inputId . '-label';
    $errorId = $inputId . '-error';
    $helpId = $inputId . '-help';
    
    $ariaDescribedBy = collect([
        $helpText ? $helpId : null,
        $error ? $errorId : null
    ])->filter()->implode(' ');

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-4 py-3 text-lg'
    ];

    $variantClasses = [
        'default' => 'border-border-primary focus:border-hd-primary-600 focus:ring-hd-primary-600',
        'error' => 'border-hd-danger-600 focus:border-hd-danger-600 focus:ring-hd-danger-600'
    ];

    $inputClasses = [
        'hdt-input__field',
        'block w-full rounded-md border transition-colors duration-150',
        'bg-surface-secondary text-text-primary placeholder-text-quaternary',
        'focus:outline-none focus:ring-2 focus:ring-opacity-50',
        'disabled:bg-surface-tertiary disabled:text-text-quaternary disabled:cursor-not-allowed',
        'readonly:bg-surface-tertiary readonly:cursor-default',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $error ? $variantClasses['error'] : $variantClasses['default']
    ];
@endphp

<div class="hdt-form-group">
    {{-- Label --}}
    @if($label)
        <label for="{{ $inputId }}" 
               id="{{ $labelId }}"
               class="block text-sm font-medium text-text-primary mb-2">
            {{ $label }}
            @if($required)
                <span class="text-hd-danger-600 ml-1" aria-label="required">*</span>
            @endif
        </label>
    @endif

    {{-- Input Field --}}
    <input 
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        class="{{ implode(' ', $inputClasses) }}"
        @if($required) required aria-required="true" @endif
        @if($disabled) disabled aria-disabled="true" @endif
        @if($readonly) readonly @endif
        @if($error) aria-invalid="true" @endif
        @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
        @if($label) aria-labelledby="{{ $labelId }}" @endif
        {{ $attributes->except(['class', 'id', 'name', 'type', 'value', 'placeholder', 'required', 'disabled', 'readonly']) }}
    />

    {{-- Help Text --}}
    @if($helpText)
        <div id="{{ $helpId }}" 
             class="mt-2 text-sm text-text-tertiary"
             role="note">
            {{ $helpText }}
        </div>
    @endif

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
/* Accessible form input styles */
.hdt-form-group {
    margin-bottom: var(--hdt-spacing-6);
}

.hdt-input__field {
    font-family: var(--hdt-font-family-sans);
    font-size: var(--hdt-font-size-base);
    line-height: var(--hdt-line-height-normal);
    min-height: 44px; /* WCAG minimum touch target size */
}

.hdt-input__field:focus {
    box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .hdt-input__field {
        border-width: 2px;
    }
    
    .hdt-input__field:focus {
        border-width: 3px;
    }
    
    .hdt-input__field[aria-invalid="true"] {
        border-width: 3px;
    }
}

/* Reduced motion support */
.hdt-reduced-motion .hdt-input__field {
    transition: none;
}

/* Focus visible for keyboard users */
.hdt-input__field:focus-visible {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Error state styling */
.hdt-input__field[aria-invalid="true"] {
    border-color: var(--hdt-color-danger-600);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc2626'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc2626' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

/* Remove error icon for password fields for security */
.hdt-input__field[type="password"][aria-invalid="true"] {
    background-image: none;
    padding-right: var(--hdt-spacing-4);
}

/* Required field indicator */
[aria-required="true"] + .hdt-required-indicator::after,
[required] + .hdt-required-indicator::after {
    content: " *";
    color: var(--hdt-color-danger-600);
}

/* Screen reader enhancements */
.sr-describe {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}
</style>
@endPushOnce