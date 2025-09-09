@props([
    'type' => 'text',
    'label' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'value' => null,
    'error' => null,
    'size' => 'md', // xs, sm, md, lg, xl
    'variant' => 'default', // default, success, error, warning
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'autocomplete' => null,
    'maxlength' => null,
    'pattern' => null,
    'readonly' => false,
    'alpineModel' => null, // x-model attribute
    'alpineValidation' => null, // x-bind:class for validation
    'alpineEvents' => null, // additional x-on events
])

@php
    $id = $attributes->get('id', 'input-' . str()->random(8));
    $name = $attributes->get('name', $id);
    
    // Size classes
    $sizeClasses = [
        'xs' => 'px-3 py-1.5 text-xs min-h-[32px]',
        'sm' => 'px-3 py-2 text-sm min-h-[36px]',
        'md' => 'px-4 py-3 text-base min-h-[44px]',
        'lg' => 'px-4 py-3.5 text-lg min-h-[48px]',
        'xl' => 'px-5 py-4 text-xl min-h-[52px]',
    ][$size] ?? $sizeClasses['md'];
    
    // Variant classes
    $baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 placeholder:text-gray-400 dark:placeholder:text-gray-500';
    
    $variantClasses = [
        'default' => 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500',
        'success' => 'border-success-300 dark:border-success-600 bg-success-50 dark:bg-success-900/20 text-gray-900 dark:text-gray-100 focus:border-success-500 focus:ring-success-500',
        'error' => 'border-error-300 dark:border-error-600 bg-error-50 dark:bg-error-900/20 text-gray-900 dark:text-gray-100 focus:border-error-500 focus:ring-error-500',
        'warning' => 'border-warning-300 dark:border-warning-600 bg-warning-50 dark:bg-warning-900/20 text-gray-900 dark:text-gray-100 focus:border-warning-500 focus:ring-warning-500',
    ][$variant] ?? $variantClasses['default'];
    
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
    $readonlyClasses = $readonly ? 'bg-gray-100 dark:bg-gray-700 cursor-default' : '';
    
    $inputClasses = trim("{$baseClasses} {$sizeClasses} {$variantClasses} {$disabledClasses} {$readonlyClasses}");
    
    // Icon classes
    $iconWrapperClasses = $iconPosition === 'left' ? 'left-3' : 'right-3';
    $iconPaddingClasses = $icon ? ($iconPosition === 'left' ? 'pl-10' : 'pr-10') : '';
    
    // Alpine.js attributes
    $alpineAttributes = [];
    if ($alpineModel) {
        $alpineAttributes['x-model'] = $alpineModel;
    }
    if ($alpineValidation) {
        $alpineAttributes['x-bind:class'] = $alpineValidation;
    }
    if ($alpineEvents) {
        foreach ($alpineEvents as $event => $handler) {
            $alpineAttributes["x-on:{$event}"] = $handler;
        }
    }
@endphp

<div class="hd-form-group">
    <!-- Label -->
    @if($label)
        <label 
            for="{{ $id }}"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
        >
            {{ $label }}
            @if($required)
                <span class="text-error-500 ml-1" aria-label="Required">*</span>
            @endif
        </label>
    @endif
    
    <!-- Input Container -->
    <div class="relative">
        <!-- Input Field -->
        <input
            {{ $attributes->merge([
                'type' => $type,
                'id' => $id,
                'name' => $name,
                'class' => $inputClasses . ($icon ? " {$iconPaddingClasses}" : ''),
                'placeholder' => $placeholder,
                'value' => $value,
                'autocomplete' => $autocomplete,
                'maxlength' => $maxlength,
                'pattern' => $pattern,
                'required' => $required,
                'disabled' => $disabled,
                'readonly' => $readonly,
                'aria-invalid' => $error ? 'true' : 'false',
                'aria-describedby' => $hint || $error ? "{$id}-description" : null,
            ]) }}
            @foreach($alpineAttributes as $attr => $val)
                {{ $attr }}="{{ $val }}"
            @endforeach
        />
        
        <!-- Icon -->
        @if($icon)
            <div class="absolute inset-y-0 {{ $iconWrapperClasses }} flex items-center pointer-events-none">
                <div class="w-5 h-5 text-gray-400 dark:text-gray-500">
                    {!! $icon !!}
                </div>
            </div>
        @endif
        
        <!-- Success/Error Icon -->
        @if($variant === 'success')
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        @elseif($variant === 'error')
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        @endif
    </div>
    
    <!-- Hint/Error Message -->
    @if($hint || $error)
        <div id="{{ $id }}-description" class="mt-2 text-sm">
            @if($error)
                <p class="text-error-600 dark:text-error-400 flex items-start">
                    <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $error }}
                </p>
            @elseif($hint)
                <p class="text-gray-500 dark:text-gray-400">
                    {{ $hint }}
                </p>
            @endif
        </div>
    @endif
</div>

@push('styles')
<style>
    .hd-form-group input:focus {
        /* Enhanced focus styles for better accessibility */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .hd-form-group input:disabled {
        background-image: none;
    }
    
    @media (prefers-reduced-motion: reduce) {
        .hd-form-group input {
            transition: none !important;
        }
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .hd-form-group input {
            border-width: 2px;
        }
        
        .hd-form-group input:focus {
            outline: 2px solid;
            outline-offset: 2px;
        }
    }
</style>
@endpush
