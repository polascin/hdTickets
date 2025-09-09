@props([
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => 'Enter your password',
    'required' => false,
    'autocomplete' => 'current-password',
    'showToggle' => true,
    'help' => null,
    'error' => null
])

@php
    $inputId = $name . '-' . Str::random(8);
    $toggleId = $inputId . '-toggle';
    $errorId = $inputId . '-error';
    $helpId = $inputId . '-help';
@endphp

<div class="space-y-1" x-data="{ showPassword: false }">
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
        <!-- Lock Icon -->
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        
        <!-- Password Input -->
        <input 
            {{ $attributes->merge([
                'id' => $inputId,
                'name' => $name,
                'type' => 'password',
                'class' => 'block w-full pl-10 ' . ($showToggle ? 'pr-14' : 'pr-3') . ' py-4 min-h-[48px] text-base border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-3 focus:ring-stadium-blue-500 focus:ring-opacity-50 focus:border-stadium-blue-500 transition-all duration-200 bg-gray-50/50 hover:bg-white hover:border-gray-400 touch-manipulation',
                'placeholder' => $placeholder,
                'autocomplete' => $autocomplete,
                'required' => $required,
                'aria-describedby' => trim(($help ? $helpId : '') . ' ' . ($error ? $errorId : '') . ' ' . ($attributes->get('aria-describedby') ?? '')),
                // Enhanced mobile attributes
                'enterkeyhint' => 'go',
                'minlength' => '8',
                'title' => 'Password must be at least 8 characters long',
                // iOS Safari optimization
                'style' => 'font-size: 16px; -webkit-text-size-adjust: 100%;'
            ]) }}
            value="{{ old($name) }}"
            x-bind:type="showPassword ? 'text' : 'password'"
        >
        
        <!-- Password Toggle Button -->
        @if($showToggle)
        <button type="button" 
                id="{{ $toggleId }}"
                class="absolute inset-y-0 right-0 w-12 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 focus:ring-2 focus:ring-stadium-blue-500 focus:ring-opacity-50 rounded-r-xl transition-all duration-200 hover:bg-gray-50 active:bg-gray-100 touch-manipulation min-h-[44px]"
                @click="showPassword = !showPassword"
                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                tabindex="0">
            
            <!-- Show Password Icon (Eye Open) -->
            <svg x-show="!showPassword" 
                 class="h-5 w-5" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            
            <!-- Hide Password Icon (Eye Closed) -->
            <svg x-show="showPassword" 
                 class="h-5 w-5" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true"
                 style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l3.122 3.122M12 12l4.242-4.242"/>
            </svg>
        </button>
        @endif
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
    
    <!-- Custom Error -->
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
