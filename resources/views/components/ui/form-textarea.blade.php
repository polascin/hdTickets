@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'helpText' => null,
    'placeholder' => null,
    'value' => null,
    'rows' => 4,
    'maxlength' => null,
    'showCounter' => false,
    'size' => 'md',
    'variant' => 'default',
    'resize' => 'vertical'
])

@php
    $textareaId = $id ?? ($name ? $name . '-' . uniqid() : 'textarea-' . uniqid());
    $labelId = $textareaId . '-label';
    $errorId = $textareaId . '-error';
    $helpId = $textareaId . '-help';
    $counterId = $textareaId . '-counter';
    
    $ariaDescribedBy = collect([
        $helpText ? $helpId : null,
        $error ? $errorId : null,
        $showCounter ? $counterId : null
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

    $resizeClasses = [
        'none' => 'resize-none',
        'vertical' => 'resize-y',
        'horizontal' => 'resize-x',
        'both' => 'resize'
    ];

    $textareaClasses = [
        'hdt-textarea__field',
        'block w-full rounded-md border transition-colors duration-150',
        'bg-surface-secondary text-text-primary placeholder-text-quaternary',
        'focus:outline-none focus:ring-2 focus:ring-opacity-50',
        'disabled:bg-surface-tertiary disabled:text-text-quaternary disabled:cursor-not-allowed',
        'readonly:bg-surface-tertiary readonly:cursor-default',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $error ? $variantClasses['error'] : $variantClasses['default'],
        $resizeClasses[$resize] ?? $resizeClasses['vertical']
    ];
@endphp

<div class="hdt-form-group" 
     x-data="{
        content: '{{ old($name, $value) }}',
        maxLength: {{ $maxlength ?? 'null' }},
        showCounter: {{ $showCounter ? 'true' : 'false' }},
        get charCount() {
            return this.content ? this.content.length : 0;
        },
        get isNearLimit() {
            return this.maxLength && this.charCount >= (this.maxLength * 0.9);
        },
        get isOverLimit() {
            return this.maxLength && this.charCount > this.maxLength;
        }
     }">
    
    {{-- Label --}}
    @if($label)
        <label for="{{ $textareaId }}" 
               id="{{ $labelId }}"
               class="block text-sm font-medium text-text-primary mb-2">
            {{ $label }}
            @if($required)
                <span class="text-hd-danger-600 ml-1" aria-label="required">*</span>
            @endif
        </label>
    @endif

    {{-- Textarea Field --}}
    <textarea 
        id="{{ $textareaId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        class="{{ implode(' ', $textareaClasses) }}"
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($required) required aria-required="true" @endif
        @if($disabled) disabled aria-disabled="true" @endif
        @if($readonly) readonly @endif
        @if($error) aria-invalid="true" @endif
        @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
        @if($label) aria-labelledby="{{ $labelId }}" @endif
        x-model="content"
        {{ $attributes->except(['class', 'id', 'name', 'rows', 'maxlength', 'placeholder', 'required', 'disabled', 'readonly']) }}
    >{{ old($name, $value) }}</textarea>

    {{-- Character Counter --}}
    @if($showCounter)
        <div id="{{ $counterId }}" 
             class="mt-2 text-sm text-right"
             :class="{
                'text-text-tertiary': !isNearLimit,
                'text-hd-warning-600': isNearLimit && !isOverLimit,
                'text-hd-danger-600': isOverLimit
             }"
             role="status"
             aria-live="polite"
             x-show="showCounter">
            <span x-text="charCount"></span>
            @if($maxlength)
                <span class="text-text-quaternary">/ {{ $maxlength }}</span>
            @endif
            <span class="sr-only">characters</span>
        </div>
    @endif

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
/* Accessible form textarea styles */
.hdt-textarea__field {
    font-family: var(--hdt-font-family-sans);
    font-size: var(--hdt-font-size-base);
    line-height: var(--hdt-line-height-normal);
    min-height: 88px; /* WCAG minimum touch target size for textarea */
}

.hdt-textarea__field:focus {
    box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .hdt-textarea__field {
        border-width: 2px;
    }
    
    .hdt-textarea__field:focus {
        border-width: 3px;
    }
    
    .hdt-textarea__field[aria-invalid="true"] {
        border-width: 3px;
    }
}

/* Reduced motion support */
.hdt-reduced-motion .hdt-textarea__field {
    transition: none;
}

/* Focus visible for keyboard users */
.hdt-textarea__field:focus-visible {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Error state styling */
.hdt-textarea__field[aria-invalid="true"] {
    border-color: var(--hdt-color-danger-600);
}

/* Resize handle styling */
.hdt-textarea__field:not(.resize-none) {
    resize: vertical;
}

.hdt-textarea__field.resize-none {
    resize: none;
}

.hdt-textarea__field.resize-x {
    resize: horizontal;
}

.hdt-textarea__field.resize {
    resize: both;
}

/* Custom resize handle for better visibility */
.hdt-textarea__field:not(.resize-none)::after {
    content: '';
    position: absolute;
    right: 0;
    bottom: 0;
    width: 16px;
    height: 16px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%236b7280' viewBox='0 0 16 16'%3e%3cpath d='M16 16V10l-2 2-2-2-1 1 2 2-2 2v3h3zm0-6V7l-2 2-2-2-1 1 2 2-2 2 2 2 2-2 1 1zm0-3V4l-2 2-2-2-1 1 2 2-2 2 2 2 2-2z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 12px;
    pointer-events: none;
}

/* Dark mode resize handle */
.hdt-theme-dark .hdt-textarea__field:not(.resize-none)::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%239ca3af' viewBox='0 0 16 16'%3e%3cpath d='M16 16V10l-2 2-2-2-1 1 2 2-2 2v3h3zm0-6V7l-2 2-2-2-1 1 2 2-2 2 2 2 2-2 1 1zm0-3V4l-2 2-2-2-1 1 2 2-2 2 2 2 2-2z'/%3e%3c/svg%3e");
}

/* Disabled state */
.hdt-textarea__field:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    resize: none;
}

/* Character counter styling */
.hdt-textarea__counter {
    font-variant-numeric: tabular-nums;
    font-feature-settings: 'tnum';
}

/* Auto-expand functionality */
.hdt-textarea__field.auto-expand {
    min-height: 88px;
    max-height: 400px;
    overflow-y: auto;
}

/* Screen reader only class */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Focus within container styling */
.hdt-form-group:focus-within .hdt-textarea__field {
    --tw-ring-color: var(--hdt-color-focus-ring);
}

/* Placeholder styling improvements */
.hdt-textarea__field::placeholder {
    opacity: 0.7;
    font-style: italic;
}

.hdt-textarea__field:focus::placeholder {
    opacity: 0.5;
}

/* Selection styling */
.hdt-textarea__field::selection {
    background-color: var(--hdt-color-primary-100);
    color: var(--hdt-color-primary-900);
}

.hdt-theme-dark .hdt-textarea__field::selection {
    background-color: var(--hdt-color-primary-800);
    color: var(--hdt-color-primary-100);
}
</style>
@endPushOnce