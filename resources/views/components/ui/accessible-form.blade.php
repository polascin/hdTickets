@props([
    'action' => null,
    'method' => 'POST',
    'csrf' => true,
    'files' => false,
    'title' => null,
    'description' => null,
    'submitText' => 'Submit',
    'submitVariant' => 'primary',
    'submitSize' => 'md',
    'resetText' => null,
    'cancelText' => null,
    'cancelUrl' => null,
    'disabled' => false,
    'loading' => false,
    'validate' => true,
    'errorSummary' => true,
    'id' => null
])

@php
    $formId = $id ?? 'form-' . uniqid();
    $titleId = $formId . '-title';
    $descriptionId = $formId . '-description';
    $errorSummaryId = $formId . '-error-summary';
    
    $ariaDescribedBy = collect([
        $description ? $descriptionId : null,
        $errorSummary && $errors->any() ? $errorSummaryId : null
    ])->filter()->implode(' ');
@endphp

<div class="hdt-accessible-form" 
     x-data="{
        isSubmitting: {{ $loading ? 'true' : 'false' }},
        hasErrors: {{ $errors->any() ? 'true' : 'false' }},
        validate: {{ $validate ? 'true' : 'false' }},
        init() {
            this.setupValidation();
            this.handleFormErrors();
        },
        setupValidation() {
            if (!this.validate) return;
            
            // Enhanced form validation
            const form = this.$refs.form;
            if (!form) return;
            
            // Real-time validation
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('blur', (e) => {
                    this.validateField(e.target);
                });
                
                field.addEventListener('input', (e) => {
                    // Clear errors on input for better UX
                    const errorElement = document.getElementById(e.target.id + '-error');
                    if (errorElement) {
                        errorElement.style.display = 'none';
                        e.target.setAttribute('aria-invalid', 'false');
                    }
                });
            });
        },
        validateField(field) {
            let isValid = true;
            let errorMessage = '';
            
            // Required field validation
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                errorMessage = 'This field is required.';
            }
            
            // Email validation
            if (field.type === 'email' && field.value && !this.isValidEmail(field.value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }
            
            // URL validation
            if (field.type === 'url' && field.value && !this.isValidUrl(field.value)) {
                isValid = false;
                errorMessage = 'Please enter a valid URL.';
            }
            
            // Min/max length validation
            if (field.hasAttribute('minlength') && field.value.length < parseInt(field.getAttribute('minlength'))) {
                isValid = false;
                errorMessage = `Minimum ${field.getAttribute('minlength')} characters required.`;
            }
            
            if (field.hasAttribute('maxlength') && field.value.length > parseInt(field.getAttribute('maxlength'))) {
                isValid = false;
                errorMessage = `Maximum ${field.getAttribute('maxlength')} characters allowed.`;
            }
            
            this.showFieldValidation(field, isValid, errorMessage);
            return isValid;
        },
        showFieldValidation(field, isValid, errorMessage) {
            field.setAttribute('aria-invalid', isValid ? 'false' : 'true');
            
            let errorElement = document.getElementById(field.id + '-error');
            if (!errorElement && !isValid) {
                errorElement = document.createElement('div');
                errorElement.id = field.id + '-error';
                errorElement.className = 'mt-2 text-sm text-hd-danger-600';
                errorElement.setAttribute('role', 'alert');
                errorElement.setAttribute('aria-live', 'polite');
                field.parentNode.appendChild(errorElement);
            }
            
            if (errorElement) {
                if (isValid) {
                    errorElement.style.display = 'none';
                } else {
                    errorElement.textContent = errorMessage;
                    errorElement.style.display = 'block';
                }
            }
        },
        isValidEmail(email) {
            return /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email);
        },
        isValidUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },
        handleFormErrors() {
            if (!this.hasErrors) return;
            
            // Focus first error field
            this.$nextTick(() => {
                const firstErrorField = this.$refs.form.querySelector('[aria-invalid=\"true\"]');
                if (firstErrorField) {
                    firstErrorField.focus();
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        },
        async handleSubmit(event) {
            if (this.isSubmitting) {
                event.preventDefault();
                return false;
            }
            
            if (!this.validate) return true;
            
            // Validate all fields before submission
            const form = this.$refs.form;
            let isFormValid = true;
            
            form.querySelectorAll('input, textarea, select').forEach(field => {
                if (!this.validateField(field)) {
                    isFormValid = false;
                }
            });
            
            if (!isFormValid) {
                event.preventDefault();
                
                // Announce validation errors
                this.announceErrors();
                
                // Focus first invalid field
                const firstInvalidField = form.querySelector('[aria-invalid=\"true\"]');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                return false;
            }
            
            this.isSubmitting = true;
            return true;
        },
        announceErrors() {
            // Create or update live region for error announcement
            let announcer = document.getElementById('form-error-announcer');
            if (!announcer) {
                announcer = document.createElement('div');
                announcer.id = 'form-error-announcer';
                announcer.setAttribute('aria-live', 'assertive');
                announcer.setAttribute('aria-atomic', 'true');
                announcer.className = 'sr-only';
                document.body.appendChild(announcer);
            }
            
            announcer.textContent = 'Form contains errors. Please review and correct the highlighted fields.';
        }
     }">

    {{-- Form Title --}}
    @if($title)
        <h2 id="{{ $titleId }}" class="text-lg font-semibold text-text-primary mb-4">
            {{ $title }}
        </h2>
    @endif

    {{-- Form Description --}}
    @if($description)
        <div id="{{ $descriptionId }}" class="text-sm text-text-secondary mb-6" role="note">
            {{ $description }}
        </div>
    @endif

    {{-- Error Summary --}}
    @if($errorSummary && $errors->any())
        <div id="{{ $errorSummaryId }}" 
             class="mb-6 p-4 bg-hd-danger-50 border border-hd-danger-200 rounded-md"
             role="alert"
             aria-live="polite">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-hd-danger-600 mt-0.5 mr-3 flex-shrink-0" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24" 
                     aria-hidden="true">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-hd-danger-800 mb-2">
                        {{ trans_choice('There was :count error|There were :count errors', $errors->count()) }}
                    </h3>
                    <ul class="list-disc list-inside text-sm text-hd-danger-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Element --}}
    <form 
        @if($action) action="{{ $action }}" @endif
        method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
        @if($files) enctype="multipart/form-data" @endif
        id="{{ $formId }}"
        class="hdt-form"
        @if($title) aria-labelledby="{{ $titleId }}" @endif
        @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
        @if($disabled) aria-disabled="true" @endif
        novalidate
        x-ref="form"
        @submit="handleSubmit($event)"
        {{ $attributes->except(['class', 'id', 'action', 'method', 'enctype', 'novalidate']) }}
    >
        {{-- CSRF Token --}}
        @if($csrf && strtoupper($method) !== 'GET')
            @csrf
        @endif

        {{-- Method Spoofing --}}
        @if($method && !in_array(strtoupper($method), ['GET', 'POST']))
            @method($method)
        @endif

        {{-- Form Content --}}
        <div class="space-y-6">
            {{ $slot }}
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-border-primary">
            {{-- Cancel Button --}}
            @if($cancelText && $cancelUrl)
                <x-ui.button 
                    variant="ghost" 
                    href="{{ $cancelUrl }}"
                    :size="$submitSize">
                    {{ $cancelText }}
                </x-ui.button>
            @endif

            {{-- Reset Button --}}
            @if($resetText)
                <x-ui.button 
                    type="reset" 
                    variant="secondary"
                    :size="$submitSize"
                    @click="isSubmitting = false">
                    {{ $resetText }}
                </x-ui.button>
            @endif

            {{-- Submit Button --}}
            <x-ui.button 
                type="submit"
                :variant="$submitVariant"
                :size="$submitSize"
                :disabled="$disabled"
                x-bind:loading="isSubmitting"
                loading-text="Submitting...">
                {{ $submitText }}
            </x-ui.button>
        </div>
    </form>
</div>

@pushOnce('styles')
<style>
/* Accessible form container styles */
.hdt-accessible-form {
    max-width: 100%;
}

.hdt-form {
    background: var(--hdt-color-surface-primary);
    border-radius: var(--hdt-border-radius-lg);
    padding: var(--hdt-spacing-8);
    border: 1px solid var(--hdt-color-border-primary);
}

/* Form spacing */
.hdt-form .space-y-6 > * + * {
    margin-top: var(--hdt-spacing-6);
}

/* Error summary styling */
.hdt-accessible-form .bg-hd-danger-50 {
    background-color: var(--hdt-color-danger-50);
}

.hdt-theme-dark .hdt-accessible-form .bg-hd-danger-50 {
    background-color: var(--hdt-color-danger-900);
    border-color: var(--hdt-color-danger-700);
}

.hdt-theme-dark .hdt-accessible-form .text-hd-danger-800 {
    color: var(--hdt-color-danger-200);
}

.hdt-theme-dark .hdt-accessible-form .text-hd-danger-700 {
    color: var(--hdt-color-danger-300);
}

/* Screen reader only class */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Form focus management */
.hdt-form:focus-within {
    outline: none;
}

/* Enhanced focus indicators */
.hdt-form input:focus,
.hdt-form textarea:focus,
.hdt-form select:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Loading state for form */
.hdt-form[aria-busy="true"] {
    cursor: wait;
    pointer-events: none;
    opacity: 0.7;
}

.hdt-form[aria-busy="true"]::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.hdt-theme-dark .hdt-form[aria-busy="true"]::after {
    background: rgba(0, 0, 0, 0.8);
}

/* Print styles */
@media print {
    .hdt-accessible-form {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .hdt-form button[type="submit"],
    .hdt-form button[type="reset"],
    .hdt-form .hd-button {
        display: none;
    }
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
    .hdt-form {
        padding: var(--hdt-spacing-6);
    }
    
    .hdt-form .flex {
        flex-direction: column;
        align-items: stretch;
    }
    
    .hdt-form .space-x-4 > * + * {
        margin-left: 0;
        margin-top: var(--hdt-spacing-3);
    }
}
</style>
@endPushOnce