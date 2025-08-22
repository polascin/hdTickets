@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'size' => 'default', // sm, default, lg
    'variant' => 'default', // default, filled, outline
    'icon' => null,
    'iconPosition' => 'left', // left, right
])

@php
    $fieldId = $name ?: 'field_' . uniqid();
    $hasError = !empty($error) || $errors->has($name);
    $errorMessage = $error ?: ($errors->has($name) ? $errors->first($name) : null);
    
    // Form field container classes
    $containerClasses = collect([
        'hd-form-field',
        match($size) {
            'sm' => 'hd-form-field--sm',
            'lg' => 'hd-form-field--lg',
            default => ''
        }
    ])->filter()->implode(' ');
    
    // Input classes using design system
    $inputClasses = collect([
        'hd-input',
        match($variant) {
            'filled' => 'hd-input--filled',
            'outline' => 'hd-input--outline',
            default => ''
        },
        match($size) {
            'sm' => 'hd-input--sm',
            'lg' => 'hd-input--lg',
            default => ''
        },
        $hasError ? 'hd-input--error' : '',
        $icon ? ($iconPosition === 'left' ? 'hd-input--icon-left' : 'hd-input--icon-right') : '',
        $disabled ? 'hd-input--disabled' : '',
        $readonly ? 'hd-input--readonly' : ''
    ])->filter()->implode(' ');
@endphp

<div class="{{ $containerClasses }}">
    @if($label)
        <label for="{{ $fieldId }}" class="hd-label {{ $required ? 'hd-label--required' : '' }}">
            {{ $label }}
            @if($required)
                <span class="hd-label__required" aria-label="Required">*</span>
            @endif
        </label>
    @endif
    
    <div class="hd-input-wrapper {{ $icon ? 'hd-input-wrapper--has-icon' : '' }}">
        @if($icon && $iconPosition === 'left')
            <div class="hd-input-icon hd-input-icon--left">
                @if(is_string($icon))
                    <i class="{{ $icon }}" aria-hidden="true"></i>
                @else
                    {{ $icon }}
                @endif
            </div>
        @endif
        
        @if($type === 'textarea')
            <textarea
                id="{{ $fieldId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                @if($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
                @if($help) aria-describedby="{{ $fieldId }}-help" @endif
                {{ $attributes }}
            >{{ old($name, $value) }}</textarea>
        @elseif($type === 'select')
            <select
                id="{{ $fieldId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                @if($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
                @if($help) aria-describedby="{{ $fieldId }}-help" @endif
                {{ $attributes }}
            >
                @if($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif
                {{ $slot }}
            </select>
        @else
            <input
                type="{{ $type }}"
                id="{{ $fieldId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                @if($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
                @if($help) aria-describedby="{{ $fieldId }}-help" @endif
                {{ $attributes }}
            />
        @endif
        
        @if($icon && $iconPosition === 'right')
            <div class="hd-input-icon hd-input-icon--right">
                @if(is_string($icon))
                    <i class="{{ $icon }}" aria-hidden="true"></i>
                @else
                    {{ $icon }}
                @endif
            </div>
        @endif
    </div>
    
    @if($help)
        <div id="{{ $fieldId }}-help" class="hd-form-help">
            {{ $help }}
        </div>
    @endif
    
    @if($hasError)
        <div id="{{ $fieldId }}-error" class="hd-form-error" role="alert">
            {{ $errorMessage }}
        </div>
    @endif
</div>
