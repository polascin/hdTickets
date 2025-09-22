{{--
    Accessible Form Components for HD Tickets
    
    WCAG 2.1 AA compliant form components with:
    - Proper label associations
    - Error state management with aria-live
    - Help text with aria-describedby
    - Required field indicators
    - Validation feedback
    - High contrast support
--}}

{{-- Form Container --}}
@php
    $formId = $attributes->get('id', 'form-' . uniqid());
    $ariaLabelledBy = $attributes->get('aria-labelledby');
    $ariaLabel = $attributes->get('aria-label');
@endphp

<form {{ $attributes->merge([
    'id' => $formId,
    'novalidate' => true,
    'role' => 'form'
]) }}>
    @if($ariaLabelledBy || $ariaLabel)
        {{-- Form has proper labeling --}}
    @else
        {{-- Provide default aria-label if none specified --}}
        {{ $attributes->merge(['aria-label' => 'Form']) }}
    @endif
    
    {{-- Form content --}}
    {{ $slot }}
    
    {{-- Global form errors region --}}
    <div id="{{ $formId }}-errors" 
         aria-live="polite" 
         aria-atomic="true" 
         role="alert"
         class="sr-only"
         x-data="formErrorAnnouncer()"
         x-init="init('{{ $formId }}')">
    </div>
</form>

{{-- Form Group Component --}}
@component('components.ui.form-group')
@endcomponent

{{-- Input Component with Accessibility --}}
@component('components.ui.form-input')
@endcomponent

{{-- Textarea Component with Accessibility --}}
@component('components.ui.form-textarea')
@endcomponent

{{-- Select Component with Accessibility --}}
@component('components.ui.form-select')
@endcomponent

{{-- Checkbox Component with Accessibility --}}
@component('components.ui.form-checkbox')
@endcomponent

{{-- Radio Group Component with Accessibility --}}
@component('components.ui.form-radio-group')
@endcomponent

@pushOnce('scripts')
<script>
// Form Error Announcer Alpine Component
Alpine.data('formErrorAnnouncer', () => ({
    formId: '',
    errorContainer: null,
    
    init(formId) {
        this.formId = formId;
        this.errorContainer = this.$el;
        
        // Listen for form validation events
        this.setupErrorAnnouncement();
    },
    
    setupErrorAnnouncement() {
        const form = document.getElementById(this.formId);
        if (!form) return;
        
        // Listen for form submission errors
        form.addEventListener('submit', (e) => {
            this.checkAndAnnounceErrors(form);
        });
        
        // Listen for field validation changes
        form.addEventListener('invalid', (e) => {
            this.announceFieldError(e.target);
        }, true);
        
        // Listen for successful validation
        form.addEventListener('input', (e) => {
            if (e.target.checkValidity() && e.target.hasAttribute('aria-invalid')) {
                this.clearFieldError(e.target);
            }
        });
    },
    
    checkAndAnnounceErrors(form) {
        const invalidFields = form.querySelectorAll(':invalid');
        if (invalidFields.length > 0) {
            const errorCount = invalidFields.length;
            const message = errorCount === 1 
                ? 'There is 1 error in the form. Please review and correct it.'
                : `There are ${errorCount} errors in the form. Please review and correct them.`;
            
            this.announceError(message);
            
            // Focus first invalid field
            invalidFields[0].focus();
        }
    },
    
    announceFieldError(field) {
        const label = this.getFieldLabel(field);
        const validationMessage = field.validationMessage;
        const message = label 
            ? `${label}: ${validationMessage}`
            : validationMessage;
        
        this.announceError(message);
        
        // Mark field as invalid
        field.setAttribute('aria-invalid', 'true');
    },
    
    clearFieldError(field) {
        field.removeAttribute('aria-invalid');
    },
    
    announceError(message) {
        this.errorContainer.textContent = '';
        setTimeout(() => {
            this.errorContainer.textContent = message;
        }, 100);
        
        // Clear message after announcement
        setTimeout(() => {
            this.errorContainer.textContent = '';
        }, 5000);
    },
    
    getFieldLabel(field) {
        // Try to find associated label
        const labelId = field.getAttribute('aria-labelledby');
        if (labelId) {
            const label = document.getElementById(labelId);
            return label ? label.textContent.trim() : null;
        }
        
        // Try to find label by for attribute
        const label = document.querySelector(`label[for="${field.id}"]`);
        if (label) {
            return label.textContent.trim();
        }
        
        // Try placeholder or name
        return field.placeholder || field.name || null;
    }
}));

// Form Validation Helpers
Alpine.data('formValidation', () => ({
    errors: {},
    touched: {},
    
    validateField(fieldName, value, rules = []) {
        const errors = [];
        
        for (const rule of rules) {
            if (rule.required && (!value || value.trim() === '')) {
                errors.push(rule.message || 'This field is required');
                continue;
            }
            
            if (value && rule.minLength && value.length < rule.minLength) {
                errors.push(rule.message || `Must be at least ${rule.minLength} characters`);
            }
            
            if (value && rule.maxLength && value.length > rule.maxLength) {
                errors.push(rule.message || `Must be no more than ${rule.maxLength} characters`);
            }
            
            if (value && rule.pattern && !rule.pattern.test(value)) {
                errors.push(rule.message || 'Invalid format');
            }
            
            if (value && rule.email && !this.isValidEmail(value)) {
                errors.push(rule.message || 'Please enter a valid email address');
            }
        }
        
        this.errors[fieldName] = errors;
        return errors.length === 0;
    },
    
    isValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    },
    
    markTouched(fieldName) {
        this.touched[fieldName] = true;
    },
    
    hasError(fieldName) {
        return this.touched[fieldName] && this.errors[fieldName] && this.errors[fieldName].length > 0;
    },
    
    getErrors(fieldName) {
        return this.errors[fieldName] || [];
    }
}));
</script>
@endPushOnce