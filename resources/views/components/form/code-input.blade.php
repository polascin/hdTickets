@props([
    'length' => 6,
    'label' => 'Verification Code',
    'hint' => null,
    'required' => true,
    'disabled' => false,
    'error' => null,
    'alpineModel' => null,
    'alpineEvents' => null,
])

@php
    $id = $attributes->get('id', 'code-input-' . str()->random(8));
    $name = $attributes->get('name', $id);
    
    // Alpine.js attributes
    $alpineAttributes = [];
    if ($alpineModel) {
        $alpineAttributes['x-model'] = $alpineModel;
    }
    if ($alpineEvents) {
        foreach ($alpineEvents as $event => $handler) {
            $alpineAttributes["x-on:{$event}"] = $handler;
        }
    }
@endphp

<div class="hd-code-input" x-data="{
    codes: Array({{ $length }}).fill(''),
    currentIndex: 0,
    
    init() {
        this.$nextTick(() => {
            this.focusInput(0);
        });
    },
    
    onInput(index, value) {
        // Only allow numbers
        const numericValue = value.replace(/\D/g, '');
        
        if (numericValue) {
            this.codes[index] = numericValue.charAt(numericValue.length - 1);
            
            // Move to next input if not at the end
            if (index < {{ $length }} - 1) {
                this.focusInput(index + 1);
            }
        } else {
            this.codes[index] = '';
        }
        
        this.updateHiddenInput();
        this.updateCurrentIndex();
    },
    
    onKeydown(index, event) {
        // Backspace handling
        if (event.key === 'Backspace') {
            if (this.codes[index] === '' && index > 0) {
                // Move to previous input and clear it
                this.focusInput(index - 1);
                this.codes[index - 1] = '';
                this.updateHiddenInput();
            } else {
                // Clear current input
                this.codes[index] = '';
                this.updateHiddenInput();
            }
        }
        // Arrow key navigation
        else if (event.key === 'ArrowLeft' && index > 0) {
            this.focusInput(index - 1);
        }
        else if (event.key === 'ArrowRight' && index < {{ $length }} - 1) {
            this.focusInput(index + 1);
        }
        // Delete key
        else if (event.key === 'Delete') {
            this.codes[index] = '';
            this.updateHiddenInput();
        }
    },
    
    onPaste(event) {
        event.preventDefault();
        const paste = (event.clipboardData || window.clipboardData).getData('text');
        const pasteNumbers = paste.replace(/\D/g, '').slice(0, {{ $length }});
        
        for (let i = 0; i < pasteNumbers.length && i < {{ $length }}; i++) {
            this.codes[i] = pasteNumbers[i];
        }
        
        this.updateHiddenInput();
        this.focusInput(Math.min(pasteNumbers.length, {{ $length }} - 1));
    },
    
    onFocus(index) {
        this.currentIndex = index;
        this.$refs['input' + index].select();
    },
    
    focusInput(index) {
        this.currentIndex = index;
        this.$nextTick(() => {
            if (this.$refs['input' + index]) {
                this.$refs['input' + index].focus();
            }
        });
    },
    
    updateHiddenInput() {
        const value = this.codes.join('');
        this.$refs.hiddenInput.value = value;
        
        // Dispatch input event for external listeners
        this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        
        @if($alpineModel)
        // Update Alpine model if provided
        this.{{ $alpineModel }} = value;
        @endif
    },
    
    updateCurrentIndex() {
        // Find the first empty input or the last one
        let nextIndex = this.codes.findIndex(code => code === '');
        if (nextIndex === -1) nextIndex = {{ $length }} - 1;
        this.currentIndex = nextIndex;
    },
    
    clear() {
        this.codes = Array({{ $length }}).fill('');
        this.updateHiddenInput();
        this.focusInput(0);
    },
    
    get value() {
        return this.codes.join('');
    },
    
    get isComplete() {
        return this.codes.every(code => code !== '') && this.codes.join('').length === {{ $length }};
    }
}">
    
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
    
    <!-- Hint -->
    @if($hint)
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $hint }}</p>
    @endif
    
    <!-- Code Input Fields -->
    <div class="flex items-center justify-center space-x-2 sm:space-x-3">
        @for($i = 0; $i < $length; $i++)
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                autocomplete="one-time-code"
                x-ref="input{{ $i }}"
                x-model="codes[{{ $i }}]"
                x-on:input="onInput({{ $i }}, $event.target.value)"
                x-on:keydown="onKeydown({{ $i }}, $event)"
                x-on:paste="onPaste($event)"
                x-on:focus="onFocus({{ $i }})"
                :disabled="{{ $disabled ? 'true' : 'false' }}"
                class="
                    w-12 h-12 sm:w-14 sm:h-14 text-center text-xl sm:text-2xl font-mono font-bold
                    border-2 rounded-lg transition-all duration-200
                    focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                    {{ $error ? 'border-error-300 bg-error-50 text-error-900' : 'border-gray-300 bg-white text-gray-900' }}
                    {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-gray-400' }}
                    dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100
                    dark:hover:border-gray-500 dark:focus:border-primary-400
                "
                :class="{
                    'border-primary-500 ring-2 ring-primary-500/20': currentIndex === {{ $i }} && !{{ $error ? 'true' : 'false' }},
                    'border-success-500 bg-success-50 text-success-900 dark:bg-success-900/20 dark:border-success-600': codes[{{ $i }}] && !{{ $error ? 'true' : 'false' }}
                }"
                aria-label="Digit {{ $i + 1 }}"
            />
        @endfor
    </div>
    
    <!-- Hidden input for form submission -->
    <input
        type="hidden"
        id="{{ $id }}"
        name="{{ $name }}"
        x-ref="hiddenInput"
        {{ $required ? 'required' : '' }}
        @foreach($alpineAttributes as $attr => $val)
            {{ $attr }}="{{ $val }}"
        @endforeach
    />
    
    <!-- Completion Indicator -->
    <div class="mt-3 flex items-center justify-center">
        <div class="flex items-center space-x-1">
            @for($i = 0; $i < $length; $i++)
                <div 
                    class="w-2 h-2 rounded-full transition-colors duration-200"
                    :class="codes[{{ $i }}] ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600'"
                ></div>
            @endfor
        </div>
    </div>
    
    <!-- Error Message -->
    @if($error)
        <div class="mt-3 text-center">
            <p class="text-sm text-error-600 dark:text-error-400 flex items-center justify-center">
                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $error }}
            </p>
        </div>
    @endif
    
    <!-- Actions -->
    <div class="mt-4 flex justify-center">
        <button
            type="button"
            x-on:click="clear()"
            class="
                text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 
                focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 rounded-md px-2 py-1
                transition-colors duration-200
            "
        >
            Clear all
        </button>
    </div>
</div>

@push('styles')
<style>
    .hd-code-input input::-webkit-outer-spin-button,
    .hd-code-input input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .hd-code-input input[type=number] {
        -moz-appearance: textfield;
    }
    
    @media (prefers-reduced-motion: reduce) {
        .hd-code-input * {
            transition: none !important;
        }
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .hd-code-input input {
            border-width: 3px;
        }
        
        .hd-code-input input:focus {
            outline: 3px solid;
            outline-offset: 2px;
        }
    }
    
    /* Focus visible for keyboard navigation */
    .hd-code-input input:focus:not(:focus-visible) {
        outline: none;
        box-shadow: none;
    }
    
    .hd-code-input input:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }
</style>
@endpush
