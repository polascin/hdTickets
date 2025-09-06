@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'floating' => false,
    'help' => null,
    'error' => null,
    'success' => null,
    'warning' => null,
    'info' => null,
    'size' => 'medium',
    'layout' => 'vertical',
    'icon' => null,
    'iconPosition' => 'left',
    'prefix' => null,
    'suffix' => null
])

@php
    $fieldId = $name ?? 'field-' . uniqid();
    $classes = 'form-field';
    
    // Layout variations
    if ($layout === 'horizontal') {
        $classes .= ' form-field--horizontal';
    }
    
    // Floating label
    if ($floating) {
        $classes .= ' form-field--floating';
    }
    
    // Size variations
    if ($size === 'small') {
        $classes .= ' form-field--small';
    } elseif ($size === 'large') {
        $classes .= ' form-field--large';
    }
    
    // State variations
    if ($error) {
        $classes .= ' form-field--error';
    } elseif ($success) {
        $classes .= ' form-field--success';
    } elseif ($warning) {
        $classes .= ' form-field--warning';
    } elseif ($info) {
        $classes .= ' form-field--info';
    }
    
    // Icon variations
    if ($icon) {
        $classes .= ' form-field--with-icon';
        if ($iconPosition === 'right') {
            $classes .= ' form-field--icon-right';
        }
    }
    
    // Prefix/suffix
    if ($prefix || $suffix) {
        $classes .= ' form-field--with-addons';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Label --}}
    @if($label)
        <div class="form-field__label">
            <x-input-label 
                :for="$fieldId" 
                :required="$required" 
                :floating="$floating" 
                :size="$size"
                :value="$label" 
            />
        </div>
    @endif
    
    {{-- Input wrapper --}}
    <div class="form-field__input">
        @if($prefix || $suffix || $icon)
            <div class="form-field__input-group">
                {{-- Prefix --}}
                @if($prefix)
                    <div class="form-field__addon form-field__addon--prefix">
                        {{ $prefix }}
                    </div>
                @endif
                
                {{-- Left icon --}}
                @if($icon && $iconPosition === 'left')
                    <div class="form-field__icon form-field__icon--left">
                        @if(str_contains($icon, '<svg'))
                            {!! $icon !!}
                        @else
                            <i class="{{ $icon }}"></i>
                        @endif
                    </div>
                @endif
                
                {{-- Input slot --}}
                <div class="form-field__input-element">
                    {{ $slot }}
                </div>
                
                {{-- Right icon --}}
                @if($icon && $iconPosition === 'right')
                    <div class="form-field__icon form-field__icon--right">
                        @if(str_contains($icon, '<svg'))
                            {!! $icon !!}
                        @else
                            <i class="{{ $icon }}"></i>
                        @endif
                    </div>
                @endif
                
                {{-- Suffix --}}
                @if($suffix)
                    <div class="form-field__addon form-field__addon--suffix">
                        {{ $suffix }}
                    </div>
                @endif
            </div>
        @else
            {{-- Simple input without addons --}}
            {{ $slot }}
        @endif
    </div>
    
    {{-- Messages --}}
    <div class="form-field__messages">
        {{-- Error message --}}
        @if($error)
            <x-input-error 
                :messages="$error" 
                type="error" 
                :field="$fieldId"
                icon
            />
        @endif
        
        {{-- Success message --}}
        @if($success)
            <x-input-error 
                :messages="$success" 
                type="success" 
                :field="$fieldId"
                icon
            />
        @endif
        
        {{-- Warning message --}}
        @if($warning)
            <x-input-error 
                :messages="$warning" 
                type="warning" 
                :field="$fieldId"
                icon
            />
        @endif
        
        {{-- Info message --}}
        @if($info)
            <x-input-error 
                :messages="$info" 
                type="info" 
                :field="$fieldId"
                icon
            />
        @endif
        
        {{-- Help text --}}
        @if($help)
            <div class="form-field__help">
                <span class="form-help-text">{{ $help }}</span>
            </div>
        @endif
    </div>
</div>
