/**
 * Enhanced Form Validation System
 * Provides comprehensive client-side validation with user-friendly error messages
 */

export class FormValidator {
    constructor(form, options = {}) {
        this.form = typeof form === 'string' ? document.querySelector(form) : form;
        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showSuccessStates: true,
            highlightErrors: true,
            autoFocus: true,
            submitButton: null,
            errorContainer: null,
            successContainer: null,
            customRules: {},
            customMessages: {},
            ...options
        };

        this.validators = new Map();
        this.errors = new Map();
        this.isValid = true;
        this.submitAttempted = false;

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('FormValidator: Form element not found');
            return;
        }

        this.setupDefaultValidators();
        this.setupCustomValidators();
        this.bindEvents();
        this.setupUI();
    }

    setupDefaultValidators() {
        // Required field validation
        this.validators.set('required', {
            validate: (value, element) => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    return element.checked;
                }
                return value !== null && value !== undefined && value.toString().trim() !== '';
            },
            message: 'This field is required'
        });

        // Email validation
        this.validators.set('email', {
            validate: (value) => {
                if (!value) return true; // Allow empty unless required
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value);
            },
            message: 'Please enter a valid email address'
        });

        // URL validation
        this.validators.set('url', {
            validate: (value) => {
                if (!value) return true;
                try {
                    new URL(value);
                    return true;
                } catch {
                    return false;
                }
            },
            message: 'Please enter a valid URL'
        });

        // Minimum length validation
        this.validators.set('minlength', {
            validate: (value, element, params) => {
                if (!value) return true;
                const minLength = parseInt(params) || 0;
                return value.length >= minLength;
            },
            message: (params) => `Must be at least ${params} characters long`
        });

        // Maximum length validation
        this.validators.set('maxlength', {
            validate: (value, element, params) => {
                if (!value) return true;
                const maxLength = parseInt(params) || Infinity;
                return value.length <= maxLength;
            },
            message: (params) => `Must be no more than ${params} characters long`
        });

        // Pattern validation
        this.validators.set('pattern', {
            validate: (value, element, params) => {
                if (!value) return true;
                const pattern = new RegExp(params);
                return pattern.test(value);
            },
            message: 'Please match the requested format'
        });

        // Number validation
        this.validators.set('number', {
            validate: (value) => {
                if (!value) return true;
                return !isNaN(value) && isFinite(value);
            },
            message: 'Please enter a valid number'
        });

        // Integer validation
        this.validators.set('integer', {
            validate: (value) => {
                if (!value) return true;
                return Number.isInteger(parseFloat(value));
            },
            message: 'Please enter a whole number'
        });

        // Min value validation
        this.validators.set('min', {
            validate: (value, element, params) => {
                if (!value) return true;
                const minValue = parseFloat(params);
                return parseFloat(value) >= minValue;
            },
            message: (params) => `Must be at least ${params}`
        });

        // Max value validation
        this.validators.set('max', {
            validate: (value, element, params) => {
                if (!value) return true;
                const maxValue = parseFloat(params);
                return parseFloat(value) <= maxValue;
            },
            message: (params) => `Must be no more than ${params}`
        });

        // Password strength validation
        this.validators.set('password-strength', {
            validate: (value) => {
                if (!value) return true;
                return value.length >= 8 && 
                       /[a-z]/.test(value) && 
                       /[A-Z]/.test(value) && 
                       /\d/.test(value) && 
                       /[!@#$%^&*(),.?":{}|<>]/.test(value);
            },
            message: 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
        });

        // Confirmation field validation
        this.validators.set('confirm', {
            validate: (value, element, params) => {
                const targetField = this.form.querySelector(`[name="${params}"]`);
                if (!targetField) return false;
                return value === targetField.value;
            },
            message: 'Fields do not match'
        });

        // File validation
        this.validators.set('file', {
            validate: (value, element, params) => {
                if (!element.files || element.files.length === 0) {
                    return !element.hasAttribute('required');
                }

                const file = element.files[0];
                const rules = params ? params.split('|') : [];

                for (const rule of rules) {
                    const [type, param] = rule.split(':');
                    
                    switch (type) {
                        case 'maxsize':
                            const maxSize = parseInt(param) * 1024 * 1024; // MB to bytes
                            if (file.size > maxSize) {
                                this.setCustomError(element, `File size must be less than ${param}MB`);
                                return false;
                            }
                            break;
                        case 'types':
                            const allowedTypes = param.split(',');
                            const fileExtension = file.name.split('.').pop().toLowerCase();
                            if (!allowedTypes.includes(fileExtension)) {
                                this.setCustomError(element, `Only ${allowedTypes.join(', ').toUpperCase()} files are allowed`);
                                return false;
                            }
                            break;
                    }
                }
                return true;
            },
            message: 'Invalid file'
        });

        // Credit card validation
        this.validators.set('creditcard', {
            validate: (value) => {
                if (!value) return true;
                // Luhn algorithm
                const digits = value.replace(/\D/g, '');
                let sum = 0;
                let alternate = false;
                
                for (let i = digits.length - 1; i >= 0; i--) {
                    let n = parseInt(digits.charAt(i));
                    if (alternate) {
                        n *= 2;
                        if (n > 9) n = (n % 10) + 1;
                    }
                    sum += n;
                    alternate = !alternate;
                }
                
                return sum % 10 === 0;
            },
            message: 'Please enter a valid credit card number'
        });
    }

    setupCustomValidators() {
        Object.entries(this.options.customRules).forEach(([name, rule]) => {
            this.validators.set(name, rule);
        });
    }

    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            this.submitAttempted = true;
            if (!this.validateForm()) {
                e.preventDefault();
                this.focusFirstError();
                this.showFormErrors();
            }
        });

        // Input validation
        if (this.options.validateOnInput) {
            this.form.addEventListener('input', (e) => {
                if (this.submitAttempted || e.target.dataset.validated) {
                    this.validateField(e.target);
                }
            });
        }

        // Blur validation
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => {
                if (this.shouldValidateField(e.target)) {
                    e.target.dataset.validated = 'true';
                    this.validateField(e.target);
                }
            }, true);
        }

        // Real-time password confirmation
        const passwordConfirm = this.form.querySelector('[data-confirm]');
        if (passwordConfirm) {
            const targetName = passwordConfirm.dataset.confirm;
            const targetField = this.form.querySelector(`[name="${targetName}"]`);
            
            if (targetField) {
                targetField.addEventListener('input', () => {
                    if (passwordConfirm.value) {
                        this.validateField(passwordConfirm);
                    }
                });
            }
        }
    }

    setupUI() {
        // Add CSS classes
        this.form.classList.add('form-validator');
        
        // Create or identify submit button
        if (this.options.submitButton) {
            this.submitButton = typeof this.options.submitButton === 'string' 
                ? this.form.querySelector(this.options.submitButton)
                : this.options.submitButton;
        } else {
            this.submitButton = this.form.querySelector('[type="submit"]');
        }

        // Create error containers for fields
        this.form.querySelectorAll('[data-validate]').forEach(field => {
            this.createFieldErrorContainer(field);
        });
    }

    createFieldErrorContainer(field) {
        const container = document.createElement('div');
        container.className = 'field-error-message';
        container.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        `;
        
        // Insert after the field
        const parent = field.parentNode;
        const nextSibling = field.nextSibling;
        
        if (nextSibling) {
            parent.insertBefore(container, nextSibling);
        } else {
            parent.appendChild(container);
        }
        
        field.errorContainer = container;
    }

    shouldValidateField(field) {
        return field.hasAttribute('data-validate') || 
               field.hasAttribute('required') ||
               field.type === 'email' ||
               field.type === 'url' ||
               field.hasAttribute('pattern') ||
               field.hasAttribute('minlength') ||
               field.hasAttribute('maxlength');
    }

    validateField(field) {
        if (!this.shouldValidateField(field)) return true;

        const value = this.getFieldValue(field);
        const rules = this.getFieldRules(field);
        const errors = [];

        // Clear previous state
        this.clearFieldState(field);

        for (const rule of rules) {
            const [ruleName, params] = rule.split(':');
            const validator = this.validators.get(ruleName);

            if (validator && !validator.validate(value, field, params)) {
                const message = this.getErrorMessage(ruleName, params, field);
                errors.push(message);
                break; // Stop at first error
            }
        }

        if (errors.length > 0) {
            this.setFieldError(field, errors[0]);
            this.errors.set(field.name || field.id, errors);
            return false;
        } else {
            this.setFieldSuccess(field);
            this.errors.delete(field.name || field.id);
            return true;
        }
    }

    getFieldValue(field) {
        switch (field.type) {
            case 'checkbox':
                return field.checked;
            case 'radio':
                const radioGroup = this.form.querySelectorAll(`[name="${field.name}"]`);
                const checked = Array.from(radioGroup).find(radio => radio.checked);
                return checked ? checked.value : '';
            case 'file':
                return field.files && field.files.length > 0 ? field.files[0] : null;
            default:
                return field.value;
        }
    }

    getFieldRules(field) {
        const rules = [];
        
        // Data attribute rules
        if (field.dataset.validate) {
            rules.push(...field.dataset.validate.split('|'));
        }

        // HTML5 validation attributes
        if (field.hasAttribute('required')) rules.push('required');
        if (field.type === 'email') rules.push('email');
        if (field.type === 'url') rules.push('url');
        if (field.type === 'number') rules.push('number');
        if (field.hasAttribute('pattern')) rules.push(`pattern:${field.pattern}`);
        if (field.hasAttribute('minlength')) rules.push(`minlength:${field.minLength}`);
        if (field.hasAttribute('maxlength')) rules.push(`maxlength:${field.maxLength}`);
        if (field.hasAttribute('min')) rules.push(`min:${field.min}`);
        if (field.hasAttribute('max')) rules.push(`max:${field.max}`);

        // Custom validation rules
        if (field.dataset.confirm) rules.push(`confirm:${field.dataset.confirm}`);
        if (field.dataset.passwordStrength) rules.push('password-strength');

        return rules;
    }

    getErrorMessage(ruleName, params, field) {
        // Check for custom field message
        const fieldName = field.name || field.id;
        const customKey = `${fieldName}.${ruleName}`;
        
        if (this.options.customMessages[customKey]) {
            return this.options.customMessages[customKey];
        }

        if (this.options.customMessages[ruleName]) {
            return this.options.customMessages[ruleName];
        }

        const validator = this.validators.get(ruleName);
        if (validator) {
            const message = validator.message;
            return typeof message === 'function' ? message(params) : message;
        }

        return 'This field is invalid';
    }

    setFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');

        if (field.errorContainer) {
            field.errorContainer.textContent = message;
            field.errorContainer.style.display = 'block';
        }

        // Add ARIA attributes for accessibility
        field.setAttribute('aria-invalid', 'true');
        if (field.errorContainer) {
            field.setAttribute('aria-describedby', 
                (field.getAttribute('aria-describedby') || '') + ` ${field.errorContainer.id || 'error'}`);
        }
    }

    setFieldSuccess(field) {
        if (this.options.showSuccessStates) {
            field.classList.add('is-valid');
        }
        field.classList.remove('is-invalid');

        if (field.errorContainer) {
            field.errorContainer.style.display = 'none';
        }

        // Remove ARIA attributes
        field.removeAttribute('aria-invalid');
        const ariaDescribedBy = field.getAttribute('aria-describedby');
        if (ariaDescribedBy) {
            field.setAttribute('aria-describedby', 
                ariaDescribedBy.replace(/\s*error\s*/, '').trim());
        }
    }

    clearFieldState(field) {
        field.classList.remove('is-valid', 'is-invalid');
        if (field.errorContainer) {
            field.errorContainer.style.display = 'none';
        }
    }

    setCustomError(field, message) {
        const fieldName = field.name || field.id;
        this.errors.set(fieldName, [message]);
        this.setFieldError(field, message);
    }

    clearCustomError(field) {
        const fieldName = field.name || field.id;
        this.errors.delete(fieldName);
        this.clearFieldState(field);
    }

    validateForm() {
        let isValid = true;
        this.errors.clear();

        const fields = this.form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        this.isValid = isValid;
        return isValid;
    }

    focusFirstError() {
        if (!this.options.autoFocus) return;

        const firstErrorField = this.form.querySelector('.is-invalid');
        if (firstErrorField) {
            firstErrorField.focus();
            
            // Scroll to field if not visible
            firstErrorField.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    }

    showFormErrors() {
        if (this.options.errorContainer && this.errors.size > 0) {
            const container = typeof this.options.errorContainer === 'string'
                ? document.querySelector(this.options.errorContainer)
                : this.options.errorContainer;

            if (container) {
                const errorList = Array.from(this.errors.values()).flat();
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Please correct the following errors:</strong>
                        <ul class="mt-2 mb-0">
                            ${errorList.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                `;
                container.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }

    getErrors() {
        return Object.fromEntries(this.errors);
    }

    hasErrors() {
        return this.errors.size > 0;
    }

    reset() {
        this.errors.clear();
        this.isValid = true;
        this.submitAttempted = false;

        this.form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
            this.clearFieldState(field);
        });

        if (this.options.errorContainer) {
            const container = typeof this.options.errorContainer === 'string'
                ? document.querySelector(this.options.errorContainer)
                : this.options.errorContainer;
            
            if (container) {
                container.innerHTML = '';
            }
        }
    }

    destroy() {
        // Remove event listeners and clean up
        this.form.classList.remove('form-validator');
        
        this.form.querySelectorAll('[data-validated]').forEach(field => {
            field.removeAttribute('data-validated');
            this.clearFieldState(field);
        });
    }
}

// Auto-initialize forms with data-validate-form attribute
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-validate-form]').forEach(form => {
        const options = form.dataset.validateOptions ? JSON.parse(form.dataset.validateOptions) : {};
        new FormValidator(form, options);
    });
});

export default FormValidator;
