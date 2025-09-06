@props([
    'disabled' => false,
    'type' => 'text',
    'floating' => false,
    'mask' => null,
    'validate' => null,
    'required' => false,
    'placeholder' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'size' => 'medium',
    'value' => null
])

@php
    $classes = 'form-input';
    
    // Size variations
    if ($size === 'small') {
        $classes .= ' form-input--small';
    } elseif ($size === 'large') {
        $classes .= ' form-input--large';
    }
    
    // Icon variations
    if ($icon) {
        $classes .= ' form-input--with-icon';
        if ($iconPosition === 'right') {
            $classes .= ' form-input--icon-right';
        }
    }
    
    // Mask variations
    if ($mask) {
        $classes .= ' form-input--formatted';
        if ($mask === 'phone') {
            $classes .= ' form-input--phone';
        } elseif ($mask === 'email' || $type === 'email') {
            $classes .= ' form-input--email';
        } elseif ($mask === 'currency') {
            $classes .= ' form-input--currency';
        }
    }
    
    $attributes = $attributes->merge([
        'class' => $classes,
        'type' => $type
    ]);
    
    if ($mask) {
        $attributes = $attributes->merge(['data-mask' => $mask]);
    }
    
    if ($validate) {
        $attributes = $attributes->merge(['data-validate' => $validate]);
    }
    
    if ($required) {
        $attributes = $attributes->merge(['required' => true]);
    }
    
    if ($value !== null) {
        $attributes = $attributes->merge(['value' => $value]);
    }
    
    if ($placeholder) {
        $attributes = $attributes->merge(['placeholder' => $placeholder]);
    }
@endphp

<div class="form-input-wrapper {{ $floating ? 'form-field--floating' : '' }}">
    @if($icon && $iconPosition === 'left')
        <div class="form-input__icon form-input__icon--left">
            @if(str_contains($icon, '<svg'))
                {!! $icon !!}
            @else
                <i class="{{ $icon }}"></i>
            @endif
        </div>
    @endif
    
    <input @disabled($disabled) {{ $attributes }}>
    
    @if($icon && $iconPosition === 'right')
        <div class="form-input__icon form-input__icon--right">
            @if(str_contains($icon, '<svg'))
                {!! $icon !!}
            @else
                <i class="{{ $icon }}"></i>
            @endif
        </div>
    @endif
    
    {{-- Floating label placeholder for floating labels --}}
    @if($floating && $placeholder)
        <div class="form-input__placeholder">{{ $placeholder }}</div>
    @endif
</div>
