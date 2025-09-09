@props([
    'label' => 'Password',
    'placeholder' => null,
    'hint' => null,
    'required' => true,
    'disabled' => false,
    'value' => null,
    'error' => null,
    'size' => 'md',
    'autocomplete' => 'new-password',
    'showStrengthMeter' => true,
    'showToggle' => true,
    'alpineModel' => null,
    'alpineValidation' => null,
    'strengthRules' => null, // Array of password requirements
])

@php
    $id = $attributes->get('id', 'password-' . str()->random(8));
    $name = $attributes->get('name', $id);
    
    // Default password requirements (matches Laravel Rules\Password::defaults())
    $defaultRules = [
        'minLength' => 8,
        'requireUppercase' => true,
        'requireLowercase' => true,
        'requireNumbers' => true,
        'requireSpecialChars' => true,
    ];
    
    $passwordRules = array_merge($defaultRules, $strengthRules ?? []);
@endphp

<div class="hd-password-field" x-data="{
    showPassword: false,
    password: '{{ $value }}',
    strength: 0,
    strengthLabel: '',
    strengthColor: 'bg-gray-300',
    feedback: [],
    
    init() {
        this.checkStrength();
    },
    
    togglePassword() {
        this.showPassword = !this.showPassword;
        this.$nextTick(() => {
            this.$refs.passwordInput.focus();
        });
    },
    
    checkStrength() {
        const password = this.password;
        let score = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= {{ $passwordRules['minLength'] }}) {
            score += 20;
        } else {
            feedback.push('At least {{ $passwordRules['minLength'] }} characters');
        }
        
        @if($passwordRules['requireLowercase'])
        // Lowercase check
        if (/[a-z]/.test(password)) {
            score += 20;
        } else {
            feedback.push('One lowercase letter');
        }
        @endif
        
        @if($passwordRules['requireUppercase'])
        // Uppercase check
        if (/[A-Z]/.test(password)) {
            score += 20;
        } else {
            feedback.push('One uppercase letter');
        }
        @endif
        
        @if($passwordRules['requireNumbers'])
        // Numbers check
        if (/[0-9]/.test(password)) {
            score += 20;
        } else {
            feedback.push('One number');
        }
        @endif
        
        @if($passwordRules['requireSpecialChars'])
        // Special characters check
        if (/[^A-Za-z0-9]/.test(password)) {
            score += 20;
        } else {
            feedback.push('One special character');
        }
        @endif
        
        // Update strength indicators
        this.strength = score;
        this.feedback = feedback;
        
        if (score < 40) {
            this.strengthLabel = 'Weak';
            this.strengthColor = 'bg-error-500';
        } else if (score < 80) {
            this.strengthLabel = 'Medium';
            this.strengthColor = 'bg-warning-500';
        } else {
            this.strengthLabel = 'Strong';
            this.strengthColor = 'bg-success-500';
        }
    }
}" @if($alpineModel) x-model="{{ $alpineModel }}" @endif>
    
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
        <input
            {{ $attributes->merge([
                'type' => 'password',
                'id' => $id,
                'name' => $name,
                'class' => 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 text-base min-h-[44px] pr-12 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 placeholder:text-gray-400 dark:placeholder:text-gray-500',
                'placeholder' => $placeholder,
                'value' => $value,
                'autocomplete' => $autocomplete,
                'required' => $required,
                'disabled' => $disabled,
                'aria-invalid' => $error ? 'true' : 'false',
                'aria-describedby' => "{$id}-description",
            ]) }}
            x-ref="passwordInput"
            x-bind:type="showPassword ? 'text' : 'password'"
            x-model="password"
            @if($alpineValidation) x-bind:class="{{ $alpineValidation }}" @endif
            x-on:input="checkStrength()"
        />
        
        @if($showToggle)
            <!-- Show/Hide Toggle Button -->
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:text-gray-600 dark:focus:text-gray-300"
                x-on:click="togglePassword()"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                :aria-pressed="showPassword.toString()"
                tabindex="-1"
            >
                <!-- Eye Icon (Hidden State) -->
                <svg 
                    x-show="!showPassword" 
                    class="w-5 h-5" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                
                <!-- Eye Slash Icon (Visible State) -->
                <svg 
                    x-show="showPassword" 
                    class="w-5 h-5" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
            </button>
        @endif
    </div>
    
    @if($showStrengthMeter)
        <!-- Password Strength Meter -->
        <div class="mt-3" x-show="password.length > 0">
            <!-- Strength Bar -->
            <div class="flex items-center space-x-2 mb-2">
                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div 
                        class="h-2 rounded-full transition-all duration-300"
                        x-bind:class="strengthColor"
                        x-bind:style="`width: ${strength}%`"
                        role="progressbar"
                        :aria-valuenow="strength"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :aria-label="`Password strength: ${strengthLabel}`"
                    ></div>
                </div>
                <span 
                    class="text-sm font-medium min-w-[60px]"
                    x-bind:class="{
                        'text-error-600 dark:text-error-400': strength < 40,
                        'text-warning-600 dark:text-warning-400': strength >= 40 && strength < 80,
                        'text-success-600 dark:text-success-400': strength >= 80
                    }"
                    x-text="strengthLabel"
                ></span>
            </div>
            
            <!-- Requirements List -->
            <div x-show="feedback.length > 0" class="text-sm text-gray-600 dark:text-gray-400">
                <p class="mb-1">Password needs:</p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                    <template x-for="requirement in feedback" :key="requirement">
                        <li x-text="requirement"></li>
                    </template>
                </ul>
            </div>
        </div>
    @endif
    
    <!-- Description/Error/Hint -->
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
</div>

@push('styles')
<style>
    .hd-password-field input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    @media (prefers-reduced-motion: reduce) {
        .hd-password-field * {
            transition: none !important;
        }
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .hd-password-field input {
            border-width: 2px;
        }
        
        .hd-password-field input:focus {
            outline: 2px solid;
            outline-offset: 2px;
        }
    }
</style>
@endpush
