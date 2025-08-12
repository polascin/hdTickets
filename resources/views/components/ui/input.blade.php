@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'helpText' => null,
    'size' => 'md', // sm, md, lg
    'variant' => 'default', // default, search
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'value' => null
])

@php
$inputId = $name ?? 'input-' . uniqid();
$hasError = $error || $errors->has($name);
$errorMessage = $error ?? $errors->first($name);

$inputClasses = collect([
    'hd-input',
    'hd-input--' . $size,
    'hd-input--' . $variant,
    $hasError ? 'hd-input--error' : '',
    $disabled ? 'hd-input--disabled' : '',
    $readonly ? 'hd-input--readonly' : '',
    $icon ? 'hd-input--with-icon hd-input--icon-' . $iconPosition : ''
])->filter()->join(' ');
@endphp

<div {{ $attributes->merge(['class' => 'hd-input-group']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="hd-label {{ $required ? 'hd-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif
    
    <div class="hd-input-wrapper">
        @if($icon && $iconPosition === 'left')
            <div class="hd-input-icon hd-input-icon--left">
                {!! $icon !!}
            </div>
        @endif
        
        @if($type === 'textarea')
            <textarea
                id="{{ $inputId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                rows="4">{{ old($name, $value) }}</textarea>
        @else
            <input
                type="{{ $type }}"
                id="{{ $inputId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                placeholder="{{ $placeholder }}"
                value="{{ old($name, $value) }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}>
        @endif
        
        @if($icon && $iconPosition === 'right')
            <div class="hd-input-icon hd-input-icon--right">
                {!! $icon !!}
            </div>
        @endif
    </div>
    
    @if($hasError)
        <p class="hd-input-error" role="alert">
            <svg class="hd-input-error__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif($helpText)
        <p class="hd-input-help">{{ $helpText }}</p>
    @endif
</div>
