/**
 * Enhanced Form UX Component for HD Tickets
 * Provides comprehensive form enhancements including:
 * - Password visibility toggle
 * - Real-time validation
 * - Loading states
 * - Enhanced accessibility
 * - Smooth animations and transitions
 */

export class EnhancedFormUX {
    constructor(formSelector, options = {}) {
        this.form = typeof formSelector === 'string' 
            ? document.querySelector(formSelector) 
            : formSelector;
        
        this.options = {
            enablePasswordToggle: true,
            enableRealTimeValidation: true,
            enableLoadingStates: true,
            enableProgressIndicator: true,
            enableFloatingLabels: false, // Using placeholder text instead
            showValidationIcons: true,
            validationDelay: 300, // ms
            ...options
        };

        // Validation rules
        this.validationRules = {
            email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            password: {
                minLength: 8,
                message: 'Password must be at least 8 characters long'
            },
            required: {
                message: 'This field is required'
            }
        };

        this.validationTimeouts = new Map();
        this.isSubmitting = false;

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Form not found');
            return;
        }

        this.enhanceFormFields();
        this.setupPasswordToggles();
        this.setupValidation();
        this.setupLoadingStates();
        this.setupRememberMeEnhancement();
        this.setupFormSubmission();
        this.setupAccessibility();
        this.addCustomStyles();
    }

    enhanceFormFields() {
        const inputs = this.form.querySelectorAll('input[type="email"], input[type="password"], input[type="text"]');
        
        inputs.forEach(input => {
            this.enhanceInput(input);
        });
    }

    enhanceInput(input) {
        const wrapper = input.parentElement;
        
        // Add focus/blur handlers for enhanced styling
        input.addEventListener('focus', () => {
            wrapper.classList.add('hd-input-focused');
            this.showFieldHelper(input);
        });

        input.addEventListener('blur', () => {
            wrapper.classList.remove('hd-input-focused');
            this.hideFieldHelper(input);
        });

        // Add input event for real-time feedback
        input.addEventListener('input', (e) => {
            this.handleInputChange(e);
        });

        // Add paste event handling
        input.addEventListener('paste', (e) => {
            setTimeout(() => this.handleInputChange(e), 10);
        });
    }

    setupPasswordToggles() {
        if (!this.options.enablePasswordToggle) return;

        const passwordFields = this.form.querySelectorAll('input[type="password"]');
        
        passwordFields.forEach(field => {
            this.addPasswordToggle(field);
        });
    }

    addPasswordToggle(passwordField) {
        const wrapper = passwordField.parentElement;
        
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'hd-password-toggle';
        toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
        toggleBtn.innerHTML = this.getEyeIcon(false);

        // Position toggle button
        toggleBtn.style.cssText = `
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            z-index: 10;
        `;

        // Add hover effects
        toggleBtn.addEventListener('mouseenter', () => {
            toggleBtn.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
        });

        toggleBtn.addEventListener('mouseleave', () => {
            toggleBtn.style.backgroundColor = 'transparent';
        });

        // Toggle functionality
        let isVisible = false;
        toggleBtn.addEventListener('click', () => {
            isVisible = !isVisible;
            passwordField.type = isVisible ? 'text' : 'password';
            toggleBtn.innerHTML = this.getEyeIcon(isVisible);
            toggleBtn.setAttribute('aria-label', 
                isVisible ? 'Hide password' : 'Show password'
            );
            
            // Add animation class
            toggleBtn.classList.add('hd-toggle-clicked');
            setTimeout(() => toggleBtn.classList.remove('hd-toggle-clicked'), 200);
        });

        // Append to wrapper
        wrapper.style.position = 'relative';
        wrapper.appendChild(toggleBtn);

        // Adjust input padding to accommodate toggle
        passwordField.style.paddingRight = '45px';
    }

    setupValidation() {
        if (!this.options.enableRealTimeValidation) return;

        const inputs = this.form.querySelectorAll('input[required], input[type="email"], input[type="password"]');
        
        inputs.forEach(input => {
            this.addValidationToField(input);
        });
    }

    addValidationToField(field) {
        const wrapper = field.parentElement;
        
        // Create validation message container
        if (!wrapper.querySelector('.hd-validation-message')) {
            const messageElement = document.createElement('div');
            messageElement.className = 'hd-validation-message';
            messageElement.style.cssText = `
                display: none;
                font-size: 0.75rem;
                margin-top: 0.25rem;
                padding: 0.25rem 0.5rem;
                border-radius: 0.25rem;
                transition: all 0.3s ease;
                opacity: 0;
                transform: translateY(-5px);
            `;
            wrapper.appendChild(messageElement);
        }
    }

    handleInputChange(event) {
        const field = event.target;
        const fieldName = field.name || field.id;
        
        // Clear existing timeout
        if (this.validationTimeouts.has(fieldName)) {
            clearTimeout(this.validationTimeouts.get(fieldName));
        }

        // Set new timeout for validation
        const timeout = setTimeout(() => {
            this.validateField(field);
        }, this.options.validationDelay);

        this.validationTimeouts.set(fieldName, timeout);
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldType = field.type;
        const isRequired = field.hasAttribute('required');
        const wrapper = field.parentElement;
        const messageElement = wrapper.querySelector('.hd-validation-message');

        let isValid = true;
        let message = '';

        // Required field validation
        if (isRequired && !value) {
            isValid = false;
            message = this.validationRules.required.message;
        }
        // Email validation
        else if (fieldType === 'email' && value && !this.validationRules.email.pattern.test(value)) {
            isValid = false;
            message = this.validationRules.email.message;
        }
        // Password validation
        else if (fieldType === 'password' && value && value.length < this.validationRules.password.minLength) {
            isValid = false;
            message = this.validationRules.password.message;
        }

        this.updateFieldValidationState(field, isValid, message);
    }

    updateFieldValidationState(field, isValid, message) {
        const wrapper = field.parentElement;
        const messageElement = wrapper.querySelector('.hd-validation-message');
        const iconContainer = wrapper.querySelector('.hd-field-icon');

        // Update field styling
        field.classList.remove('hd-field-valid', 'hd-field-invalid');
        
        if (field.value.trim()) {
            if (isValid) {
                field.classList.add('hd-field-valid');
                this.showValidationIcon(wrapper, 'success');
            } else {
                field.classList.add('hd-field-invalid');
                this.showValidationIcon(wrapper, 'error');
            }
        } else {
            this.hideValidationIcon(wrapper);
        }

        // Update message
        if (messageElement) {
            if (!isValid && message) {
                messageElement.textContent = message;
                messageElement.className = 'hd-validation-message hd-validation-error';
                this.showValidationMessage(messageElement);
            } else if (isValid && field.value.trim()) {
                messageElement.textContent = 'Looks good!';
                messageElement.className = 'hd-validation-message hd-validation-success';
                this.showValidationMessage(messageElement);
            } else {
                this.hideValidationMessage(messageElement);
            }
        }
    }

    showValidationIcon(wrapper, type) {
        if (!this.options.showValidationIcons) return;

        let iconContainer = wrapper.querySelector('.hd-field-icon');
        
        if (!iconContainer) {
            iconContainer = document.createElement('div');
            iconContainer.className = 'hd-field-icon';
            iconContainer.style.cssText = `
                position: absolute;
                right: 45px; /* Account for password toggle */
                top: 50%;
                transform: translateY(-50%);
                pointer-events: none;
                transition: all 0.3s ease;
            `;
            wrapper.appendChild(iconContainer);
        }

        iconContainer.innerHTML = type === 'success' 
            ? this.getCheckIcon()
            : this.getErrorIcon();
        
        iconContainer.style.opacity = '1';
        iconContainer.style.transform = 'translateY(-50%) scale(1)';
    }

    hideValidationIcon(wrapper) {
        const iconContainer = wrapper.querySelector('.hd-field-icon');
        if (iconContainer) {
            iconContainer.style.opacity = '0';
            iconContainer.style.transform = 'translateY(-50%) scale(0.8)';
        }
    }

    showValidationMessage(messageElement) {
        messageElement.style.display = 'block';
        setTimeout(() => {
            messageElement.style.opacity = '1';
            messageElement.style.transform = 'translateY(0)';
        }, 10);
    }

    hideValidationMessage(messageElement) {
        messageElement.style.opacity = '0';
        messageElement.style.transform = 'translateY(-5px)';
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 300);
    }

    setupLoadingStates() {
        if (!this.options.enableLoadingStates) return;

        const submitButton = this.form.querySelector('button[type="submit"]');
        if (submitButton) {
            this.enhanceSubmitButton(submitButton);
        }
    }

    enhanceSubmitButton(button) {
        const originalContent = button.innerHTML;
        
        // Store original content
        button.dataset.originalContent = originalContent;
        
        // Add loading spinner HTML (hidden initially)
        if (!button.querySelector('.hd-loading-spinner')) {
            const spinner = document.createElement('span');
            spinner.className = 'hd-loading-spinner';
            spinner.style.cssText = `
                display: none;
                margin-right: 8px;
                width: 16px;
                height: 16px;
            `;
            spinner.innerHTML = this.getSpinnerIcon();
            button.insertBefore(spinner, button.firstChild);
        }
    }

    setupRememberMeEnhancement() {
        const checkbox = this.form.querySelector('input[type="checkbox"]');
        if (checkbox) {
            this.enhanceCheckbox(checkbox);
        }
    }

    enhanceCheckbox(checkbox) {
        const wrapper = checkbox.parentElement;
        
        // Add custom styling class
        wrapper.classList.add('hd-enhanced-checkbox');
        
        // Add animation on change
        checkbox.addEventListener('change', () => {
            wrapper.classList.add('hd-checkbox-animate');
            setTimeout(() => wrapper.classList.remove('hd-checkbox-animate'), 300);
        });
    }

    setupFormSubmission() {
        this.form.addEventListener('submit', (e) => {
            this.handleFormSubmission(e);
        });
    }

    handleFormSubmission(event) {
        if (this.isSubmitting) {
            event.preventDefault();
            return;
        }

        // Validate all fields before submission
        const isFormValid = this.validateForm();
        
        if (!isFormValid) {
            event.preventDefault();
            this.focusFirstInvalidField();
            return;
        }

        this.setLoadingState(true);
        
        // If using AJAX, you would handle it here
        // For now, we'll let the form submit naturally
        
        // Reset loading state after a delay (in case of errors)
        setTimeout(() => {
            if (this.form) {
                this.setLoadingState(false);
            }
        }, 5000);
    }

    validateForm() {
        const inputs = this.form.querySelectorAll('input[required], input[type="email"], input[type="password"]');
        let isValid = true;

        inputs.forEach(field => {
            this.validateField(field);
            if (field.classList.contains('hd-field-invalid') || 
                (field.hasAttribute('required') && !field.value.trim())) {
                isValid = false;
            }
        });

        return isValid;
    }

    focusFirstInvalidField() {
        const firstInvalid = this.form.querySelector('.hd-field-invalid, input[required]:invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    setLoadingState(loading) {
        this.isSubmitting = loading;
        const submitButton = this.form.querySelector('button[type="submit"]');
        const spinner = submitButton?.querySelector('.hd-loading-spinner');
        const buttonText = submitButton?.querySelector('span:last-child') || submitButton;

        if (!submitButton) return;

        if (loading) {
            submitButton.disabled = true;
            submitButton.classList.add('hd-loading');
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            if (buttonText) {
                buttonText.textContent = 'Signing In...';
            }
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove('hd-loading');
            if (spinner) {
                spinner.style.display = 'none';
            }
            if (buttonText && submitButton.dataset.originalContent) {
                submitButton.innerHTML = submitButton.dataset.originalContent;
            }
        }
    }

    setupAccessibility() {
        // Add ARIA labels and descriptions
        const inputs = this.form.querySelectorAll('input');
        
        inputs.forEach(input => {
            const label = this.form.querySelector(`label[for="${input.id}"]`);
            if (label && !input.hasAttribute('aria-labelledby')) {
                input.setAttribute('aria-labelledby', label.id || `${input.id}-label`);
            }
            
            // Add describedby for validation messages
            const messageElement = input.parentElement.querySelector('.hd-validation-message');
            if (messageElement) {
                const messageId = `${input.id}-message`;
                messageElement.id = messageId;
                input.setAttribute('aria-describedby', messageId);
            }
        });
    }

    showFieldHelper(input) {
        // Show helpful text for specific field types
        if (input.type === 'password' && !input.value) {
            this.showTemporaryHint(input, 'Password should be at least 8 characters long');
        }
    }

    hideFieldHelper(input) {
        // Hide temporary hints when focus is lost
        const hint = input.parentElement.querySelector('.hd-field-hint');
        if (hint) {
            hint.remove();
        }
    }

    showTemporaryHint(input, text) {
        const wrapper = input.parentElement;
        let hint = wrapper.querySelector('.hd-field-hint');
        
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'hd-field-hint';
            hint.style.cssText = `
                font-size: 0.75rem;
                color: var(--hd-text-muted);
                margin-top: 0.25rem;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            wrapper.appendChild(hint);
        }
        
        hint.textContent = text;
        setTimeout(() => hint.style.opacity = '1', 10);
    }

    // Icon generation methods
    getEyeIcon(isVisible) {
        if (isVisible) {
            return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>`;
        }
        return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>`;
    }

    getCheckIcon() {
        return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
            <polyline points="20,6 9,17 4,12"/>
        </svg>`;
    }

    getErrorIcon() {
        return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>`;
    }

    getSpinnerIcon() {
        return `<svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>`;
    }

    addCustomStyles() {
        if (document.querySelector('#enhanced-form-styles')) return;

        const style = document.createElement('style');
        style.id = 'enhanced-form-styles';
        style.textContent = `
            /* Enhanced Form UX Styles */
            .hd-input-focused {
                transform: translateY(-1px);
                transition: transform 0.2s ease;
            }

            .hd-field-valid {
                border-color: var(--hd-success, #22c55e) !important;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1) !important;
            }

            .hd-field-invalid {
                border-color: var(--hd-error, #ef4444) !important;
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
            }

            .hd-validation-message {
                font-size: 0.75rem;
                margin-top: 0.25rem;
                padding: 0.25rem 0.5rem;
                border-radius: 0.25rem;
                transition: all 0.3s ease;
            }

            .hd-validation-error {
                background-color: rgba(239, 68, 68, 0.1);
                color: var(--hd-error, #ef4444);
                border-left: 3px solid var(--hd-error, #ef4444);
            }

            .hd-validation-success {
                background-color: rgba(34, 197, 94, 0.1);
                color: var(--hd-success, #22c55e);
                border-left: 3px solid var(--hd-success, #22c55e);
            }

            .hd-password-toggle:hover {
                background-color: rgba(0, 0, 0, 0.05) !important;
                border-radius: 4px;
            }

            .hd-toggle-clicked {
                transform: translateY(-50%) scale(0.95) !important;
                transition: transform 0.1s ease !important;
            }

            .hd-loading-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            .hd-enhanced-checkbox {
                position: relative;
            }

            .hd-checkbox-animate {
                animation: checkboxPulse 0.3s ease;
            }

            @keyframes checkboxPulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }

            .hd-loading {
                opacity: 0.7;
                cursor: not-allowed !important;
            }

            /* Focus visible for better accessibility */
            .hd-password-toggle:focus-visible {
                outline: 2px solid var(--hd-primary, #3b82f6);
                outline-offset: 2px;
                border-radius: 4px;
            }

            /* High contrast mode support */
            @media (prefers-contrast: high) {
                .hd-field-valid {
                    border-width: 2px !important;
                }
                
                .hd-field-invalid {
                    border-width: 2px !important;
                }
            }

            /* Reduced motion support */
            @media (prefers-reduced-motion: reduce) {
                .hd-input-focused,
                .hd-toggle-clicked,
                .hd-validation-message,
                .hd-checkbox-animate {
                    animation: none !important;
                    transition: none !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    }

    // Public methods for external use
    destroy() {
        // Clean up event listeners and timeouts
        this.validationTimeouts.forEach(timeout => clearTimeout(timeout));
        this.validationTimeouts.clear();
        
        // Remove custom styles
        const styles = document.querySelector('#enhanced-form-styles');
        if (styles) styles.remove();
    }

    resetValidation() {
        const inputs = this.form.querySelectorAll('input');
        inputs.forEach(input => {
            input.classList.remove('hd-field-valid', 'hd-field-invalid');
            const messageElement = input.parentElement.querySelector('.hd-validation-message');
            if (messageElement) {
                this.hideValidationMessage(messageElement);
            }
            this.hideValidationIcon(input.parentElement);
        });
    }
}

// Auto-initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize for login form
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        new EnhancedFormUX(loginForm);
    }

    // Initialize for other forms with enhanced-form class
    const enhancedForms = document.querySelectorAll('.enhanced-form');
    enhancedForms.forEach(form => {
        new EnhancedFormUX(form);
    });
});

export default EnhancedFormUX;
