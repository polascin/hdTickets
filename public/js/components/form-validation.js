/**
 * Enhanced Form Validation Module
 * Provides real-time validation feedback for forms
 */

export class FormValidator {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            errorContainer: null,
            customMessages: {},
            validateOnInput: true,
            validateOnBlur: true,
            ...options
        };
        
        this.errors = new Map();
        this.isValid = true;
        
        this.init();
    }
    
    init() {
        // Add validation attributes and listeners
        this.setupValidation();
        
        if (this.options.validateOnInput) {
            this.form.addEventListener('input', this.handleInput.bind(this));
        }
        
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', this.handleBlur.bind(this), true);
        }
        
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }
    
    setupValidation() {
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Add real-time validation styling classes
            input.classList.add('validate-input');
            
            // Create error message container if it doesn't exist
            if (!input.parentNode.querySelector('.field-error-message')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error-message';
                errorDiv.style.display = 'none';
                input.parentNode.appendChild(errorDiv);
            }
        });
    }
    
    handleInput(event) {
        const input = event.target;
        if (this.shouldValidateField(input)) {
            this.validateField(input);
        }
    }
    
    handleBlur(event) {
        const input = event.target;
        if (this.shouldValidateField(input)) {
            this.validateField(input);
        }
    }
    
    handleSubmit(event) {
        if (!this.validateForm()) {
            event.preventDefault();
            this.focusFirstError();
        }
    }
    
    shouldValidateField(input) {
        return input.matches('input, textarea, select') && 
               input.type !== 'submit' && 
               input.type !== 'button';
    }
    
    validateField(input) {
        const fieldName = input.name;
        const value = input.value.trim();
        const rules = this.getValidationRules(input);
        
        // Clear previous errors for this field
        this.clearFieldError(fieldName);
        
        // Validate each rule
        for (const rule of rules) {
            const result = this.validateRule(value, rule, input);
            if (!result.valid) {
                this.setFieldError(fieldName, result.message, input);
                return false;
            }
        }
        
        this.setFieldValid(input);
        return true;
    }
    
    validateForm() {
        const inputs = this.form.querySelectorAll('input, textarea, select');
        let isValid = true;
        
        // Clear all previous errors
        this.errors.clear();
        
        inputs.forEach(input => {
            if (this.shouldValidateField(input)) {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            }
        });
        
        this.isValid = isValid;
        this.updateErrorContainer();
        
        return isValid;
    }
    
    getValidationRules(input) {
        const rules = [];
        
        // Required validation
        if (input.hasAttribute('required')) {
            rules.push({
                type: 'required',
                message: this.getCustomMessage(`${input.name}.required`, 'This field is required.')
            });
        }
        
        // Email validation
        if (input.type === 'email') {
            rules.push({
                type: 'email',
                message: this.getCustomMessage(`${input.name}.email`, 'Please enter a valid email address.')
            });
        }
        
        // Min/Max length
        if (input.hasAttribute('minlength')) {
            rules.push({
                type: 'minlength',
                value: parseInt(input.getAttribute('minlength')),
                message: this.getCustomMessage(`${input.name}.minlength`, `Minimum ${input.getAttribute('minlength')} characters required.`)
            });
        }
        
        if (input.hasAttribute('maxlength')) {
            rules.push({
                type: 'maxlength',
                value: parseInt(input.getAttribute('maxlength')),
                message: this.getCustomMessage(`${input.name}.maxlength`, `Maximum ${input.getAttribute('maxlength')} characters allowed.`)
            });
        }
        
        // Pattern validation
        if (input.hasAttribute('pattern')) {
            rules.push({
                type: 'pattern',
                value: new RegExp(input.getAttribute('pattern')),
                message: this.getCustomMessage(`${input.name}.pattern`, 'Please enter a valid format.')
            });
        }
        
        // Password strength (if class is present)
        if (input.classList.contains('password-strength')) {
            rules.push({
                type: 'password-strength',
                message: this.getCustomMessage(`${input.name}.password-strength`, 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.')
            });
        }
        
        // Password confirmation
        if (input.classList.contains('password-confirm')) {
            const passwordField = this.form.querySelector('input[type="password"]:not(.password-confirm)');
            rules.push({
                type: 'confirm',
                value: passwordField ? passwordField.value : '',
                message: this.getCustomMessage(`${input.name}.confirm`, 'Password confirmation does not match.')
            });
        }
        
        return rules;
    }
    
    validateRule(value, rule, input) {
        switch (rule.type) {
            case 'required':
                return {
                    valid: value.length > 0,
                    message: rule.message
                };
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return {
                    valid: value === '' || emailRegex.test(value),
                    message: rule.message
                };
                
            case 'minlength':
                return {
                    valid: value.length >= rule.value,
                    message: rule.message
                };
                
            case 'maxlength':
                return {
                    valid: value.length <= rule.value,
                    message: rule.message
                };
                
            case 'pattern':
                return {
                    valid: value === '' || rule.value.test(value),
                    message: rule.message
                };
                
            case 'password-strength':
                const hasUpper = /[A-Z]/.test(value);
                const hasLower = /[a-z]/.test(value);
                const hasNumber = /\d/.test(value);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                const hasMinLength = value.length >= 8;
                
                return {
                    valid: value === '' || (hasUpper && hasLower && hasNumber && hasSpecial && hasMinLength),
                    message: rule.message
                };
                
            case 'confirm':
                return {
                    valid: value === rule.value,
                    message: rule.message
                };
                
            default:
                return { valid: true, message: '' };
        }
    }
    
    getCustomMessage(key, defaultMessage) {
        return this.options.customMessages[key] || defaultMessage;
    }
    
    setFieldError(fieldName, message, input) {
        this.errors.set(fieldName, message);
        
        // Add error styling to input
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        // Show error message
        const errorElement = input.parentNode.querySelector('.field-error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    setFieldValid(input) {
        // Add valid styling to input
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        
        // Hide error message
        const errorElement = input.parentNode.querySelector('.field-error-message');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    clearFieldError(fieldName) {
        this.errors.delete(fieldName);
    }
    
    updateErrorContainer() {
        if (!this.options.errorContainer) return;
        
        const container = this.options.errorContainer;
        
        if (this.errors.size === 0) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }
        
        const errorList = Array.from(this.errors.values());
        container.innerHTML = `
            <div class="alert alert-danger">
                <ul class="mb-0">
                    ${errorList.map(error => `<li>${error}</li>`).join('')}
                </ul>
            </div>
        `;
        container.style.display = 'block';
    }
    
    focusFirstError() {
        const firstErrorInput = this.form.querySelector('.is-invalid');
        if (firstErrorInput) {
            firstErrorInput.focus();
            firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    // Public methods
    reset() {
        this.errors.clear();
        this.isValid = true;
        
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
            const errorElement = input.parentNode.querySelector('.field-error-message');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        });
        
        this.updateErrorContainer();
    }
    
    getErrors() {
        return Object.fromEntries(this.errors);
    }
    
    isFormValid() {
        return this.isValid;
    }
}

// Auto-initialize forms with data-validate-form attribute
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-validate-form]');
    forms.forEach(form => {
        new FormValidator(form);
    });
});
