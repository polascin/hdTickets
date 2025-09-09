@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => '',
    'icon' => null,
    'help' => null,
    'error' => null
])

@php
    $inputId = $name . '-' . Str::random(8);
    $errorId = $inputId . '-error';
    $helpId = $inputId . '-help';
    
    $icons = [
        'envelope' => 'M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'
    ];
@endphp

<div class="space-y-1">
    <!-- Label -->
    @if($label)
    <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700">
        {{ $label }}
        @if($required)
        <span class="text-red-500" aria-label="required">*</span>
        @endif
    </label>
    @endif
    
    <!-- Input Container -->
    <div class="relative">
        <!-- Icon -->
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$icon] ?? $icons['user'] }}"/>
            </svg>
        </div>
        @endif
        
        <!-- Input Field -->
        <input 
            {{ $attributes->merge([
                'id' => $inputId,
                'name' => $name,
                'type' => $type,
                'class' => 'block w-full ' . ($icon ? 'pl-10' : 'pl-3') . ' pr-3 py-4 min-h-[48px] text-base border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-3 focus:ring-stadium-blue-500 focus:ring-opacity-50 focus:border-stadium-blue-500 transition-all duration-200 bg-gray-50/50 hover:bg-white hover:border-gray-400 touch-manipulation',
                'placeholder' => $placeholder,
                'autocomplete' => $autocomplete,
                'required' => $required,
                'autofocus' => $autofocus,
                'spellcheck' => $type === 'email' ? 'false' : 'true',
                'aria-describedby' => trim(($help ? $helpId : '') . ' ' . ($error ? $errorId : '') . ' ' . ($attributes->get('aria-describedby') ?? '')),
                // Enhanced mobile attributes
                'inputmode' => $type === 'email' ? 'email' : ($type === 'tel' ? 'tel' : null),
                'enterkeyhint' => $name === 'email' ? 'next' : ($name === 'password' ? 'go' : null),
                'pattern' => $type === 'email' ? '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$' : null,
                'title' => $type === 'email' ? 'Please enter a valid email address' : null,
                'minlength' => $name === 'password' ? '8' : null,
                // iOS Safari optimization
                'style' => 'font-size: 16px; -webkit-text-size-adjust: 100%;'
            ]) }}
            value="{{ old($name) }}"
        >
    </div>
    
    <!-- Help Text -->
    @if($help)
    <p id="{{ $helpId }}" class="text-xs text-gray-500">{{ $help }}</p>
    @endif
    
    <!-- Error Message -->
    @error($name)
    <div id="{{ $errorId }}" class="text-red-600 text-sm mt-1 flex items-center" role="alert">
        <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
    </div>
    @enderror
    
    <!-- Custom Error (from Alpine.js or other sources) -->
    @if($error)
    <div id="{{ $errorId }}" class="text-red-600 text-sm mt-1 flex items-center" role="alert">
        <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $error }}
    </div>
    @endif
    
    <!-- Slot for additional content -->
    {{ $slot }}
</div>
