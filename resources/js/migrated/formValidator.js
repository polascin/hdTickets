/**
 * Enhanced Form Validation and Interaction Manager for HD Tickets
 * Real-time validation, input masking, floating labels, and autosave functionality
 */

class FormValidator {
    constructor() {
        this.forms = new Map();
        this.rules = new Map();
        this.masks = new Map();
        this.autosaveTimers = new Map();
        this.config = {
            validateOnType: true,
            validateOnBlur: true,
            validateOnSubmit: true,
            debounceTime: 300,
            autosaveDelay: 2000,
            showSuccessMessages: true
        };
        
        this.init();
    }

    init() {
        this.setupDefaultRules();
        this.setupDefaultMasks();
        this.initializeExistingForms();
        this.bindGlobalEvents();
    }

    /**
     * Setup default validation rules
     */
    setupDefaultRules() {
        // Email validation
        this.addRule('email', {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address',
            required: true
        });

        // Phone number validation
        this.addRule('phone', {
            pattern: /^\+?[\d\s\-()]{10,}$/,
            message: 'Please enter a valid phone number',
            minLength: 10
        });

        // Password validation
        this.addRule('password', {
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
            message: 'Password must be at least 8 characters with uppercase, lowercase, number, and special character',
            minLength: 8
        });

        // Required field validation
        this.addRule('required', {
            validate: (value) => value && value.trim().length > 0,
            message: 'This field is required'
        });

        // URL validation
        this.addRule('url', {
            pattern: /^https?:\/\/.+\..+/,
            message: 'Please enter a valid URL'
        });

        // Credit card validation (basic)
        this.addRule('creditcard', {
            validate: (value) => this.validateCreditCard(value.replace(/\s/g, '')),
            message: 'Please enter a valid credit card number'
        });

        // Date validation
        this.addRule('date', {
            validate: (value) => !isNaN(Date.parse(value)),
            message: 'Please enter a valid date'
        });

        // Number validation
        this.addRule('number', {
            pattern: /^\d+(\.\d+)?$/,
            message: 'Please enter a valid number'
        });

        // Postal code validation
        this.addRule('postalcode', {
            pattern: /^\d{5}(-\d{4})?$/,
            message: 'Please enter a valid postal code'
        });
    }

    /**
     * Setup default input masks
     */
    setupDefaultMasks() {
        // Phone number mask
        this.addMask('phone', {
            pattern: '(###) ###-####',
            placeholder: '(555) 123-4567',
            transform: (value) => value.replace(/\D/g, '')
        });

        // Credit card mask
        this.addMask('creditcard', {
            pattern: '#### #### #### ####',
            placeholder: '1234 5678 9012 3456',
            transform: (value) => value.replace(/\D/g, '')
        });

        // Date mask
        this.addMask('date', {
            pattern: '##/##/####',
            placeholder: 'MM/DD/YYYY',
            transform: (value) => value.replace(/\D/g, '')
        });

        // SSN mask
        this.addMask('ssn', {
            pattern: '###-##-####',
            placeholder: '123-45-6789',
            transform: (value) => value.replace(/\D/g, '')
        });

        // Currency mask
        this.addMask('currency', {
            pattern: '$#,###.##',
            placeholder: '$1,234.56',
            transform: (value) => value.replace(/[^\d.]/g, ''),
            format: (value) => {
                const num = parseFloat(value);
                return isNaN(num) ? '' : num.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                });
            }
        });

        // Time mask
        this.addMask('time', {
            pattern: '##:##',
            placeholder: '12:34',
            transform: (value) => value.replace(/\D/g, '')
        });
    }

    /**
     * Initialize existing forms on the page
     */
    initializeExistingForms() {
        document.querySelectorAll('form').forEach(form => {
            this.initializeForm(form);
        });
    }

    /**
     * Initialize a specific form
     */
    initializeForm(form) {
        const formId = form.id || `form-${Date.now()}`;
        if (!form.id) form.id = formId;

        const formData = {
            element: form,
            fields: new Map(),
            isValid: false,
            autosave: form.dataset.autosave === 'true',
            multiStep: form.dataset.multiStep === 'true',
            currentStep: 0
        };

        this.forms.set(formId, formData);
        this.initializeFields(form, formData);
        this.setupFormEvents(form, formData);

        // Initialize multi-step functionality
        if (formData.multiStep) {
            this.initializeMultiStep(form, formData);
        }

        // Initialize autosave if enabled
        if (formData.autosave) {
            this.setupAutosave(form, formData);
        }
    }

    /**
     * Initialize form fields
     */
    initializeFields(form, formData) {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            const fieldData = {
                element: input,
                rules: [],
                isValid: true,
                lastValue: input.value,
                mask: null
            };

            // Extract validation rules from attributes
            this.extractValidationRules(input, fieldData);

            // Setup input mask if specified
            this.setupInputMask(input, fieldData);

            // Setup floating labels
            this.setupFloatingLabel(input);

            // Setup field events
            this.setupFieldEvents(input, fieldData, formData);

            formData.fields.set(input.name || input.id, fieldData);
        });
    }

    /**
     * Extract validation rules from input attributes
     */
    extractValidationRules(input, fieldData) {
        // Required validation
        if (input.hasAttribute('required') || input.dataset.required === 'true') {
            fieldData.rules.push('required');
        }

        // Email validation
        if (input.type === 'email' || input.dataset.validate === 'email') {
            fieldData.rules.push('email');
        }

        // Phone validation
        if (input.type === 'tel' || input.dataset.validate === 'phone') {
            fieldData.rules.push('phone');
        }

        // Password validation
        if (input.type === 'password' || input.dataset.validate === 'password') {
            fieldData.rules.push('password');
        }

        // URL validation
        if (input.type === 'url' || input.dataset.validate === 'url') {
            fieldData.rules.push('url');
        }

        // Number validation
        if (input.type === 'number' || input.dataset.validate === 'number') {
            fieldData.rules.push('number');
        }

        // Custom validation rules
        if (input.dataset.validate) {
            const customRules = input.dataset.validate.split(',');
            fieldData.rules.push(...customRules);
        }

        // Min/Max length
        if (input.minLength) {
            fieldData.minLength = parseInt(input.minLength);
        }
        if (input.maxLength) {
            fieldData.maxLength = parseInt(input.maxLength);
        }
    }

    /**
     * Setup input mask for field
     */
    setupInputMask(input, fieldData) {
        const maskType = input.dataset.mask;
        if (maskType && this.masks.has(maskType)) {
            fieldData.mask = this.masks.get(maskType);
            
            // Set placeholder if not already set
            if (!input.placeholder && fieldData.mask.placeholder) {
                input.placeholder = fieldData.mask.placeholder;
            }

            // Add CSS class for styling
            input.classList.add('form-input--formatted');
            
            if (maskType === 'phone') {
                input.classList.add('form-input--phone');
            } else if (maskType === 'email' || input.type === 'email') {
                input.classList.add('form-input--email');
            } else if (maskType === 'currency') {
                input.classList.add('form-input--currency');
            }
        }
    }

    /**
     * Setup floating label functionality
     */
    setupFloatingLabel(input) {
        const formField = input.closest('.form-field');
        if (!formField || !formField.classList.contains('form-field--floating')) return;

        const updateFloatingLabel = () => {
            if (input.value.trim() || input === document.activeElement) {
                input.classList.add('has-value');
            } else {
                input.classList.remove('has-value');
            }
        };

        input.addEventListener('focus', updateFloatingLabel);
        input.addEventListener('blur', updateFloatingLabel);
        input.addEventListener('input', updateFloatingLabel);

        // Initial state
        updateFloatingLabel();
    }

    /**
     * Setup form-level events
     */
    setupFormEvents(form, formData) {
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form.id)) {
                e.preventDefault();
                this.handleFormSubmitError(form, formData);
            } else {
                this.handleFormSubmitSuccess(form, formData);
            }
        });

        // Form reset handler
        form.addEventListener('reset', () => {
            this.resetForm(form.id);
        });
    }

    /**
     * Setup field-level events
     */
    setupFieldEvents(input, fieldData, formData) {
        let validationTimer;

        // Real-time validation on input
        if (this.config.validateOnType) {
            input.addEventListener('input', (e) => {
                clearTimeout(validationTimer);
                
                // Apply input mask
                this.applyInputMask(input, fieldData);
                
                // Debounced validation
                validationTimer = setTimeout(() => {
                    this.validateField(input.name || input.id, formData.element.id);
                }, this.config.debounceTime);

                // Trigger autosave
                if (formData.autosave) {
                    this.triggerAutosave(formData.element.id);
                }
            });
        }

        // Validation on blur
        if (this.config.validateOnBlur) {
            input.addEventListener('blur', () => {
                clearTimeout(validationTimer);
                this.validateField(input.name || input.id, formData.element.id);
            });
        }

        // Handle paste events for masked inputs
        if (fieldData.mask) {
            input.addEventListener('paste', (e) => {
                setTimeout(() => {
                    this.applyInputMask(input, fieldData);
                }, 10);
            });
        }

        // Accessibility improvements
        input.addEventListener('focus', () => {
            const messageElement = this.getMessageElement(input);
            if (messageElement && messageElement.textContent.trim()) {
                input.setAttribute('aria-describedby', messageElement.id);
            }
        });
    }

    /**
     * Apply input mask to field value
     */
    applyInputMask(input, fieldData) {
        if (!fieldData.mask) return;

        const mask = fieldData.mask;
        let value = input.value;

        // Transform value
        if (mask.transform) {
            value = mask.transform(value);
        }

        // Apply pattern
        if (mask.pattern) {
            let formatted = '';
            let valueIndex = 0;

            for (let i = 0; i < mask.pattern.length && valueIndex < value.length; i++) {
                const char = mask.pattern[i];
                if (char === '#') {
                    formatted += value[valueIndex];
                    valueIndex++;
                } else {
                    formatted += char;
                }
            }

            value = formatted;
        }

        // Apply custom formatting
        if (mask.format) {
            value = mask.format(value);
        }

        // Update input value if changed
        if (input.value !== value) {
            const cursorPos = input.selectionStart;
            input.value = value;
            
            // Restore cursor position
            const newPos = Math.min(cursorPos, value.length);
            input.setSelectionRange(newPos, newPos);
        }
    }

    /**
     * Validate a specific field
     */
    validateField(fieldName, formId) {
        const formData = this.forms.get(formId);
        const fieldData = formData.fields.get(fieldName);
        
        if (!fieldData) return true;

        const input = fieldData.element;
        const value = input.value.trim();
        const errors = [];

        // Apply validation rules
        fieldData.rules.forEach(ruleName => {
            const rule = this.rules.get(ruleName);
            if (rule) {
                let isValid = false;

                if (rule.validate) {
                    isValid = rule.validate(value, input);
                } else if (rule.pattern) {
                    isValid = rule.pattern.test(value);
                }

                // Check required fields
                if (ruleName === 'required' && !value) {
                    isValid = false;
                }

                // Skip validation for empty optional fields
                if (!value && ruleName !== 'required') {
                    isValid = true;
                }

                if (!isValid) {
                    errors.push(rule.message);
                }
            }
        });

        // Length validation
        if (fieldData.minLength && value.length < fieldData.minLength) {
            errors.push(`Must be at least ${fieldData.minLength} characters`);
        }

        if (fieldData.maxLength && value.length > fieldData.maxLength) {
            errors.push(`Must be no more than ${fieldData.maxLength} characters`);
        }

        // Update field state
        fieldData.isValid = errors.length === 0;
        fieldData.errors = errors;

        // Update UI
        this.updateFieldUI(input, fieldData);

        return fieldData.isValid;
    }

    /**
     * Update field UI based on validation state
     */
    updateFieldUI(input, fieldData) {
        // Remove existing validation classes
        input.classList.remove('form-input--success', 'form-input--error', 'form-input--warning');
        
        const formField = input.closest('.form-field');
        if (formField) {
            formField.classList.remove('form-field--success', 'form-field--error', 'form-field--warning');
        }

        // Get or create message element
        let messageElement = this.getMessageElement(input);
        if (!messageElement) {
            messageElement = this.createMessageElement(input);
        }

        if (fieldData.isValid && input.value.trim()) {
            // Success state
            input.classList.add('form-input--success');
            if (formField) formField.classList.add('form-field--success');
            
            if (this.config.showSuccessMessages && input.value.trim()) {
                this.showMessage(messageElement, 'Looks good!', 'success');
            } else {
                this.hideMessage(messageElement);
            }
        } else if (!fieldData.isValid) {
            // Error state
            input.classList.add('form-input--error');
            if (formField) formField.classList.add('form-field--error');
            
            this.showMessage(messageElement, fieldData.errors[0], 'error');
        } else {
            // Default state
            this.hideMessage(messageElement);
        }
    }

    /**
     * Get message element for input
     */
    getMessageElement(input) {
        return input.parentNode.querySelector('.form-message') ||
               input.parentNode.querySelector(`[data-field="${input.name || input.id}"]`);
    }

    /**
     * Create message element for input
     */
    createMessageElement(input) {
        const messageElement = document.createElement('div');
        messageElement.className = 'form-message form-message--realtime';
        messageElement.id = `${input.id || input.name}-message`;
        
        const iconElement = document.createElement('span');
        iconElement.className = 'form-message__icon';
        messageElement.appendChild(iconElement);
        
        const textElement = document.createElement('span');
        textElement.className = 'form-message__text';
        messageElement.appendChild(textElement);
        
        input.parentNode.appendChild(messageElement);
        return messageElement;
    }

    /**
     * Show validation message
     */
    showMessage(messageElement, message, type) {
        const iconElement = messageElement.querySelector('.form-message__icon');
        const textElement = messageElement.querySelector('.form-message__text');
        
        // Update content
        textElement.textContent = message;
        
        // Update icon
        if (iconElement) {
            const icons = {
                success: '✓',
                error: '⚠',
                warning: '!',
                info: 'i'
            };
            iconElement.textContent = icons[type] || icons.info;
        }
        
        // Update classes
        messageElement.className = `form-message form-message--realtime form-message--${type} show`;
        
        // Set ARIA attributes
        messageElement.setAttribute('role', type === 'error' ? 'alert' : 'status');
        messageElement.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    }

    /**
     * Hide validation message
     */
    hideMessage(messageElement) {
        messageElement.classList.remove('show');
        setTimeout(() => {
            messageElement.textContent = '';
        }, 200);
    }

    /**
     * Validate entire form
     */
    validateForm(formId) {
        const formData = this.forms.get(formId);
        if (!formData) return false;

        let isValid = true;

        formData.fields.forEach((fieldData, fieldName) => {
            if (!this.validateField(fieldName, formId)) {
                isValid = false;
            }
        });

        formData.isValid = isValid;
        this.updateFormUI(formData.element, formData);

        return isValid;
    }

    /**
     * Update form UI based on validation state
     */
    updateFormUI(form, formData) {
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        
        if (submitButton) {
            if (formData.isValid) {
                submitButton.removeAttribute('disabled');
                submitButton.classList.remove('form-button--disabled');
            } else {
                // Don't disable submit button to allow showing validation errors
                // submitButton.setAttribute('disabled', 'true');
                // submitButton.classList.add('form-button--disabled');
            }
        }

        // Update form progress if multi-step
        if (formData.multiStep) {
            this.updateFormProgress(form, formData);
        }
    }

    /**
     * Initialize multi-step form functionality
     */
    initializeMultiStep(form, formData) {
        const steps = form.querySelectorAll('.form-step');
        formData.totalSteps = steps.length;
        formData.currentStep = 0;

        // Hide all steps except first
        steps.forEach((step, index) => {
            if (index !== 0) {
                step.style.display = 'none';
            }
        });

        // Setup navigation buttons
        this.setupStepNavigation(form, formData);
        this.updateFormProgress(form, formData);
    }

    /**
     * Setup step navigation
     */
    setupStepNavigation(form, formData) {
        const nextButtons = form.querySelectorAll('[data-form-next]');
        const prevButtons = form.querySelectorAll('[data-form-prev]');

        nextButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.nextStep(form.id);
            });
        });

        prevButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.previousStep(form.id);
            });
        });
    }

    /**
     * Move to next form step
     */
    nextStep(formId) {
        const formData = this.forms.get(formId);
        if (!formData || formData.currentStep >= formData.totalSteps - 1) return;

        // Validate current step
        if (!this.validateCurrentStep(formId)) {
            return;
        }

        formData.currentStep++;
        this.showStep(formData.element, formData.currentStep);
        this.updateFormProgress(formData.element, formData);

        // Trigger step change event
        formData.element.dispatchEvent(new CustomEvent('stepChanged', {
            detail: { currentStep: formData.currentStep, totalSteps: formData.totalSteps }
        }));
    }

    /**
     * Move to previous form step
     */
    previousStep(formId) {
        const formData = this.forms.get(formId);
        if (!formData || formData.currentStep <= 0) return;

        formData.currentStep--;
        this.showStep(formData.element, formData.currentStep);
        this.updateFormProgress(formData.element, formData);

        // Trigger step change event
        formData.element.dispatchEvent(new CustomEvent('stepChanged', {
            detail: { currentStep: formData.currentStep, totalSteps: formData.totalSteps }
        }));
    }

    /**
     * Show specific form step
     */
    showStep(form, stepIndex) {
        const steps = form.querySelectorAll('.form-step');
        
        steps.forEach((step, index) => {
            if (index === stepIndex) {
                step.style.display = 'block';
                step.classList.add('form-step--active');
            } else {
                step.style.display = 'none';
                step.classList.remove('form-step--active');
            }
        });

        // Focus first input in new step
        const activeStep = steps[stepIndex];
        const firstInput = activeStep.querySelector('input, textarea, select');
        if (firstInput) {
            firstInput.focus();
        }
    }

    /**
     * Update form progress indicator
     */
    updateFormProgress(form, formData) {
        const progressBar = form.querySelector('.form-progress__bar');
        const stepIndicators = form.querySelectorAll('.form-step__number');

        if (progressBar) {
            const progress = ((formData.currentStep + 1) / formData.totalSteps) * 100;
            progressBar.style.width = `${progress}%`;
        }

        if (stepIndicators) {
            stepIndicators.forEach((indicator, index) => {
                const step = indicator.closest('.form-step');
                
                if (index < formData.currentStep) {
                    step.classList.add('form-step--completed');
                    step.classList.remove('form-step--active');
                } else if (index === formData.currentStep) {
                    step.classList.add('form-step--active');
                    step.classList.remove('form-step--completed');
                } else {
                    step.classList.remove('form-step--active', 'form-step--completed');
                }
            });
        }
    }

    /**
     * Validate current step only
     */
    validateCurrentStep(formId) {
        const formData = this.forms.get(formId);
        if (!formData) return false;

        const form = formData.element;
        const steps = form.querySelectorAll('.form-step');
        const currentStepElement = steps[formData.currentStep];
        
        if (!currentStepElement) return true;

        const stepInputs = currentStepElement.querySelectorAll('input, textarea, select');
        let isValid = true;

        stepInputs.forEach(input => {
            const fieldName = input.name || input.id;
            if (fieldName && !this.validateField(fieldName, formId)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Setup autosave functionality
     */
    setupAutosave(form, formData) {
        const autosaveKey = `autosave_${form.id}`;
        
        // Load saved data
        this.loadAutosavedData(form, autosaveKey);
        
        // Setup autosave trigger
        formData.autosaveKey = autosaveKey;
    }

    /**
     * Trigger autosave with debouncing
     */
    triggerAutosave(formId) {
        const formData = this.forms.get(formId);
        if (!formData || !formData.autosave) return;

        clearTimeout(this.autosaveTimers.get(formId));
        
        this.autosaveTimers.set(formId, setTimeout(() => {
            this.saveFormData(formData.element, formData.autosaveKey);
        }, this.config.autosaveDelay));
    }

    /**
     * Save form data to localStorage
     */
    saveFormData(form, key) {
        const formData = new FormData(form);
        const data = {};
        
        for (const [name, value] of formData.entries()) {
            data[name] = value;
        }

        try {
            localStorage.setItem(key, JSON.stringify({
                data: data,
                timestamp: Date.now()
            }));
            
            this.showAutosaveIndicator(form);
        } catch (error) {
            console.warn('Autosave failed:', error);
        }
    }

    /**
     * Load autosaved data
     */
    loadAutosavedData(form, key) {
        try {
            const saved = localStorage.getItem(key);
            if (!saved) return;

            const { data, timestamp } = JSON.parse(saved);
            
            // Check if data is not too old (24 hours)
            if (Date.now() - timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem(key);
                return;
            }

            // Populate form fields
            Object.entries(data).forEach(([name, value]) => {
                const input = form.querySelector(`[name="${name}"]`);
                if (input && !input.value) {
                    input.value = value;
                    
                    // Trigger events to update floating labels and validation
                    input.dispatchEvent(new Event('input'));
                    input.dispatchEvent(new Event('change'));
                }
            });

            this.showAutosaveRestoreNotification(form);
        } catch (error) {
            console.warn('Failed to load autosaved data:', error);
        }
    }

    /**
     * Show autosave indicator
     */
    showAutosaveIndicator(form) {
        let indicator = form.querySelector('.autosave-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'autosave-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--form-success);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                font-size: 0.875rem;
                z-index: 1000;
                transform: translateY(-100px);
                transition: transform 0.3s ease;
            `;
            document.body.appendChild(indicator);
        }

        indicator.textContent = 'Draft saved';
        indicator.style.transform = 'translateY(0)';

        setTimeout(() => {
            indicator.style.transform = 'translateY(-100px)';
        }, 2000);
    }

    /**
     * Show autosave restore notification
     */
    showAutosaveRestoreNotification(form) {
        const notification = document.createElement('div');
        notification.className = 'autosave-restore-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--form-info);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            z-index: 1000;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            <div>Your draft has been restored</div>
            <button onclick="this.parentElement.remove()" style="
                background: transparent; 
                border: 1px solid white; 
                color: white; 
                padding: 0.25rem 0.5rem; 
                border-radius: 4px; 
                font-size: 0.75rem; 
                margin-top: 0.5rem;
                cursor: pointer;
            ">Dismiss</button>
        `;
        
        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Handle form submit success
     */
    handleFormSubmitSuccess(form, formData) {
        // Clear autosaved data on successful submit
        if (formData.autosave && formData.autosaveKey) {
            localStorage.removeItem(formData.autosaveKey);
        }

        // Track analytics
        if (window.gtag) {
            gtag('event', 'form_submit_success', {
                form_id: form.id,
                form_type: form.dataset.type || 'unknown'
            });
        }
    }

    /**
     * Handle form submit error
     */
    handleFormSubmitError(form, formData) {
        // Focus first invalid field
        const firstInvalidField = form.querySelector('.form-input--error');
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Track analytics
        if (window.gtag) {
            gtag('event', 'form_submit_error', {
                form_id: form.id,
                form_type: form.dataset.type || 'unknown'
            });
        }
    }

    /**
     * Reset form to initial state
     */
    resetForm(formId) {
        const formData = this.forms.get(formId);
        if (!formData) return;

        formData.fields.forEach((fieldData, fieldName) => {
            const input = fieldData.element;
            
            // Reset validation state
            fieldData.isValid = true;
            fieldData.errors = [];
            
            // Reset UI
            input.classList.remove('form-input--success', 'form-input--error', 'form-input--warning', 'has-value');
            
            const formField = input.closest('.form-field');
            if (formField) {
                formField.classList.remove('form-field--success', 'form-field--error', 'form-field--warning');
            }
            
            // Hide messages
            const messageElement = this.getMessageElement(input);
            if (messageElement) {
                this.hideMessage(messageElement);
            }
        });

        // Reset multi-step if applicable
        if (formData.multiStep) {
            formData.currentStep = 0;
            this.showStep(formData.element, 0);
            this.updateFormProgress(formData.element, formData);
        }

        // Clear autosaved data
        if (formData.autosave && formData.autosaveKey) {
            localStorage.removeItem(formData.autosaveKey);
        }
    }

    /**
     * Credit card validation using Luhn algorithm
     */
    validateCreditCard(cardNumber) {
        if (!cardNumber || cardNumber.length < 13 || cardNumber.length > 19) {
            return false;
        }

        let sum = 0;
        let alternate = false;

        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let n = parseInt(cardNumber.charAt(i));

            if (alternate) {
                n *= 2;
                if (n > 9) {
                    n = (n % 10) + 1;
                }
            }

            sum += n;
            alternate = !alternate;
        }

        return sum % 10 === 0;
    }

    /**
     * Add custom validation rule
     */
    addRule(name, rule) {
        this.rules.set(name, rule);
    }

    /**
     * Add custom input mask
     */
    addMask(name, mask) {
        this.masks.set(name, mask);
    }

    /**
     * Get form data as JSON
     */
    getFormData(formId) {
        const formData = this.forms.get(formId);
        if (!formData) return null;

        const data = {};
        const formElement = formData.element;
        const formDataObj = new FormData(formElement);

        for (const [name, value] of formDataObj.entries()) {
            data[name] = value;
        }

        return data;
    }

    /**
     * Bind global events
     */
    bindGlobalEvents() {
        // Handle dynamically added forms
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'FORM') {
                                this.initializeForm(node);
                            }
                            
                            const forms = node.querySelectorAll('form');
                            forms.forEach(form => this.initializeForm(form));
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }

    /**
     * Public API for Alpine.js integration
     */
    getAlpineData() {
        return {
            validateField: this.validateField.bind(this),
            validateForm: this.validateForm.bind(this),
            resetForm: this.resetForm.bind(this),
            nextStep: this.nextStep.bind(this),
            previousStep: this.previousStep.bind(this),
            getFormData: this.getFormData.bind(this)
        };
    }
}

// Initialize form validator
const formValidator = new FormValidator();

// Alpine.js integration
if (window.Alpine) {
    document.addEventListener('alpine:init', () => {
        Alpine.data('formValidator', () => formValidator.getAlpineData());
    });
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}

// Make available globally
window.FormValidator = FormValidator;
window.formValidator = formValidator;
