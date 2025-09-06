@props([
    'value' => null,
    'required' => false,
    'floating' => false,
    'for' => null,
    'size' => 'medium'
])

@php
    $classes = 'form-label';
    
    if ($floating) {
        $classes .= ' form-label--floating';
    }
    
    if ($required) {
        $classes .= ' form-label--required';
    }
    
    if ($size === 'small') {
        $classes .= ' form-label--small';
    } elseif ($size === 'large') {
        $classes .= ' form-label--large';
    }
    
    $attributes = $attributes->merge(['class' => $classes]);
    
    if ($for) {
        $attributes = $attributes->merge(['for' => $for]);
    }
@endphp

<label {{ $attributes }}>
    <span class="form-label__text">
        {{ $value ?? $slot }}
        @if($required)
            <span class="form-label__required" aria-label="required">*</span>
        @endif
    </span>
</label>
