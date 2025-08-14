/**
 * Form Handler Alpine.js Component
 * Handles form validation, submission, and user feedback
 */
export default function formHandler() {
    return {
        // Form state
        submitting: false,
        submitted: false,
        errors: {},
        originalData: {},
        isDirty: false,
        
        // Configuration
        config: {
            validateOnBlur: true,
            validateOnInput: false,
            resetAfterSubmit: false,
            showSuccessMessage: true,
            autoSave: false,
            autoSaveDelay: 2000
        },
        
        init() {
            // Store original form data for dirty checking
            this.captureOriginalData();
            
            // Setup form watchers
            this.setupWatchers();
            
            // Setup auto-save if enabled
            if (this.config.autoSave) {
                this.setupAutoSave();
            }
            
            // Prevent form submission on Enter in non-textarea fields
            this.$el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    this.submit();
                }
            });
        },
        
        captureOriginalData() {
            const formData = new FormData(this.$el);
            this.originalData = Object.fromEntries(formData.entries());
        },
        
        setupWatchers() {
            // Watch for changes in form inputs
            this.$el.addEventListener('input', (e) => {
                this.checkDirtyState();
                
                if (this.config.validateOnInput) {
                    this.validateField(e.target);
                }
            });
            
            this.$el.addEventListener('blur', (e) => {
                if (this.config.validateOnBlur && e.target.matches('input, select, textarea')) {
                    this.validateField(e.target);
                }
            });
        },
        
        setupAutoSave() {
            let autoSaveTimeout;
            
            this.$el.addEventListener('input', () => {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    if (this.isDirty && !this.submitting) {
                        this.autoSave();
                    }
                }, this.config.autoSaveDelay);
            });
        },
        
        checkDirtyState() {
            const currentData = Object.fromEntries(new FormData(this.$el).entries());
            this.isDirty = JSON.stringify(currentData) !== JSON.stringify(this.originalData);
        },
        
        validateField(field) {
            // Clear existing error
            delete this.errors[field.name];
            
            // Required field validation
            if (field.hasAttribute('required') && !field.value.trim()) {
                this.setFieldError(field.name, `${this.getFieldLabel(field)} is required`);
                return false;
            }
            
            // Email validation
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    this.setFieldError(field.name, 'Please enter a valid email address');
                    return false;
                }
            }
            
            // Min/Max length validation
            if (field.hasAttribute('minlength') && field.value.length < field.getAttribute('minlength')) {
                this.setFieldError(field.name, `${this.getFieldLabel(field)} must be at least ${field.getAttribute('minlength')} characters`);
                return false;
            }
            
            if (field.hasAttribute('maxlength') && field.value.length > field.getAttribute('maxlength')) {
                this.setFieldError(field.name, `${this.getFieldLabel(field)} must not exceed ${field.getAttribute('maxlength')} characters`);
                return false;
            }
            
            // Pattern validation
            if (field.hasAttribute('pattern') && field.value) {
                const pattern = new RegExp(field.getAttribute('pattern'));
                if (!pattern.test(field.value)) {
                    this.setFieldError(field.name, `${this.getFieldLabel(field)} format is invalid`);
                    return false;
                }
            }
            
            return true;
        },
        
        validateForm() {
            this.errors = {};
            let isValid = true;
            
            // Validate all form fields
            const fields = this.$el.querySelectorAll('input, select, textarea');
            fields.forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        async submit() {
            if (this.submitting) return;
            
            // Validate form
            if (!this.validateForm()) {
                this.focusFirstError();
                return;
            }
            
            this.submitting = true;
            
            try {
                const formData = new FormData(this.$el);
                const method = this.$el.method || 'POST';
                const url = this.$el.action || window.location.href;
                
                const response = await fetch(url, {
                    method: method.toUpperCase(),
                    body: method.toUpperCase() === 'GET' ? null : formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.handleSuccess(await response.json());
                } else {
                    const errorData = await response.json();
                    this.handleError(errorData);
                }
            } catch (_error) {
                this.handleError({ message: 'Network error occurred' });
            } finally {
                this.submitting = false;
            }
        },
        
        async autoSave() {
            if (this.submitting) return;
            
            try {
                const formData = new FormData(this.$el);
                formData.append('_auto_save', '1');
                
                const response = await fetch(this.$el.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.showAutoSaveIndicator();
                    this.captureOriginalData(); // Update original data after successful save
                    this.isDirty = false;
                }
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        },
        
        handleSuccess(data) {
            this.submitted = true;
            this.errors = {};
            
            if (this.config.showSuccessMessage) {
                this.showSuccessMessage(data.message || 'Form submitted successfully');
            }
            
            if (this.config.resetAfterSubmit) {
                this.reset();
            } else {
                this.captureOriginalData();
                this.isDirty = false;
            }
            
            // Emit success event
            this.$dispatch('form-success', data);
            
            // Redirect if specified
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1000);
            }
        },
        
        handleError(errorData) {
            if (errorData.errors) {
                this.errors = errorData.errors;
            } else if (errorData.message) {
                this.showErrorMessage(errorData.message);
            }
            
            this.focusFirstError();
            this.$dispatch('form-error', errorData);
        },
        
        setFieldError(fieldName, message) {
            this.errors[fieldName] = Array.isArray(this.errors[fieldName]) 
                ? [...this.errors[fieldName], message]
                : [message];
        },
        
        getFieldError(fieldName) {
            return this.errors[fieldName]?.[0] || '';
        },
        
        hasFieldError(fieldName) {
            return !!this.errors[fieldName]?.length;
        },
        
        getFieldLabel(field) {
            const label = this.$el.querySelector(`label[for="${field.id}"]`);
            return label?.textContent.replace('*', '').trim() || field.name;
        },
        
        focusFirstError() {
            const firstErrorField = this.$el.querySelector('[name="' + Object.keys(this.errors)[0] + '"]');
            if (firstErrorField) {
                firstErrorField.focus();
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        
        reset() {
            this.$el.reset();
            this.errors = {};
            this.submitted = false;
            this.isDirty = false;
            this.captureOriginalData();
        },
        
        showSuccessMessage(message) {
            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').notify('Success', message, 'success');
            } else {
                alert(message);
            }
        },
        
        showErrorMessage(message) {
            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').notify('Error', message, 'error');
            } else {
                alert(message);
            }
        },
        
        showAutoSaveIndicator() {
            // Show a subtle indicator that auto-save occurred
            const indicator = document.createElement('div');
            indicator.className = 'auto-save-indicator';
            indicator.textContent = 'Saved';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 14px;
                z-index: 9999;
                transition: opacity 0.3s;
            `;
            
            document.body.appendChild(indicator);
            
            setTimeout(() => {
                indicator.style.opacity = '0';
                setTimeout(() => document.body.removeChild(indicator), 300);
            }, 2000);
        },
        
        // Utility methods
        getFieldValue(fieldName) {
            const field = this.$el.querySelector(`[name="${fieldName}"]`);
            return field?.value || '';
        },
        
        setFieldValue(fieldName, value) {
            const field = this.$el.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.value = value;
                this.checkDirtyState();
            }
        },
        
        disableField(fieldName) {
            const field = this.$el.querySelector(`[name="${fieldName}"]`);
            if (field) field.disabled = true;
        },
        
        enableField(fieldName) {
            const field = this.$el.querySelector(`[name="${fieldName}"]`);
            if (field) field.disabled = false;
        }
    };
}
