/**
 * Mobile Form Optimizer for HD Tickets
 * Optimizes forms for mobile devices with keyboard handling, input improvements, and accessibility
 */

class MobileFormOptimizer {
    constructor() {
        this.isInitialized = false;
        this.keyboardVisible = false;
        this.activeInput = null;
        this.originalViewportHeight = window.innerHeight;
        this.formElements = new WeakMap();
        
        // Mobile keyboard configurations
        this.keyboardTypes = {
            email: {
                inputMode: 'email',
                autocomplete: 'email',
                autocapitalize: 'none',
                autocorrect: 'off',
                spellcheck: false,
                pattern: '[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$'
            },
            tel: {
                inputMode: 'tel',
                autocomplete: 'tel',
                pattern: '[0-9\\s\\-\\+\\(\\)]+'
            },
            url: {
                inputMode: 'url',
                autocomplete: 'url',
                autocapitalize: 'none',
                autocorrect: 'off',
                spellcheck: false
            },
            numeric: {
                inputMode: 'numeric',
                pattern: '[0-9]*'
            },
            decimal: {
                inputMode: 'decimal',
                pattern: '[0-9]*\\.[0-9]*'
            },
            search: {
                inputMode: 'search',
                autocomplete: 'off',
                role: 'searchbox'
            }
        };
        
        this.init();
    }
    
    /**
     * Initialize mobile form optimizer
     */
    init() {
        if (this.isInitialized) return;
        
        // Only initialize on mobile devices
        if (!this.isMobileDevice()) {
            console.log('Mobile Form Optimizer: Not a mobile device, skipping initialization');
            return;
        }
        
        this.setupKeyboardDetection();
        this.setupFormEnhancements();
        this.setupInputFocusHandling();
        this.setupFormValidation();
        this.injectMobileFormStyles();
        
        this.isInitialized = true;
        console.log('📱 Mobile Form Optimizer initialized');
    }
    
    /**
     * Check if device is mobile
     */
    isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               window.innerWidth <= 768;
    }
    
    /**
     * Setup keyboard visibility detection
     */
    setupKeyboardDetection() {
        let resizeTimer = null;
        
        const handleResize = () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.detectKeyboardVisibility();
            }, 100);
        };
        
        window.addEventListener('resize', handleResize);
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.originalViewportHeight = window.innerHeight;
                this.detectKeyboardVisibility();
            }, 500);
        });
    }
    
    /**
     * Detect keyboard visibility based on viewport height changes
     */
    detectKeyboardVisibility() {
        const currentHeight = window.innerHeight;
        const heightDifference = this.originalViewportHeight - currentHeight;
        const keyboardThreshold = 150; // Minimum height difference to consider keyboard visible
        
        const wasKeyboardVisible = this.keyboardVisible;
        this.keyboardVisible = heightDifference > keyboardThreshold;
        
        if (this.keyboardVisible !== wasKeyboardVisible) {
            this.handleKeyboardToggle();
        }
    }
    
    /**
     * Handle keyboard show/hide
     */
    handleKeyboardToggle() {
        document.body.classList.toggle('keyboard-visible', this.keyboardVisible);
        
        if (this.keyboardVisible) {
            this.handleKeyboardShow();
        } else {
            this.handleKeyboardHide();
        }
        
        // Emit custom events
        const eventName = this.keyboardVisible ? 'mobile:keyboard:show' : 'mobile:keyboard:hide';
        document.dispatchEvent(new CustomEvent(eventName, {
            detail: {
                keyboardVisible: this.keyboardVisible,
                activeInput: this.activeInput
            }
        }));
    }
    
    /**
     * Handle keyboard show
     */
    handleKeyboardShow() {
        if (this.activeInput) {
            // Scroll active input into view with a delay to ensure keyboard is fully visible
            setTimeout(() => {
                this.scrollInputIntoView(this.activeInput);
            }, 300);
            
            // Add keyboard-focused class to form
            const form = this.activeInput.closest('form, .mobile-form');
            if (form) {
                form.classList.add('keyboard-focused');
            }
        }
        
        // Hide non-essential UI elements
        this.hideNonEssentialElements();
    }
    
    /**
     * Handle keyboard hide
     */
    handleKeyboardHide() {
        // Remove keyboard-focused class from all forms
        document.querySelectorAll('.keyboard-focused').forEach(form => {
            form.classList.remove('keyboard-focused');
        });
        
        // Show non-essential UI elements
        this.showNonEssentialElements();
    }
    
    /**
     * Hide non-essential elements when keyboard is visible
     */
    hideNonEssentialElements() {
        const elementsToHide = [
            '.mobile-fab',
            '.mobile-bottom-nav',
            '.header-secondary-actions',
            '[data-hide-on-keyboard]'
        ];
        
        elementsToHide.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                if (!element.hasAttribute('data-keyboard-hidden')) {
                    element.setAttribute('data-keyboard-hidden', 'true');
                    element.style.setProperty('--original-transform', getComputedStyle(element).transform);
                    element.classList.add('keyboard-hidden');
                }
            });
        });
    }
    
    /**
     * Show non-essential elements when keyboard is hidden
     */
    showNonEssentialElements() {
        const elements = document.querySelectorAll('[data-keyboard-hidden]');
        elements.forEach(element => {
            element.removeAttribute('data-keyboard-hidden');
            element.classList.remove('keyboard-hidden');
            const originalTransform = element.style.getPropertyValue('--original-transform');
            if (originalTransform) {
                element.style.transform = originalTransform;
                element.style.removeProperty('--original-transform');
            }
        });
    }
    
    /**
     * Scroll input into view
     */
    scrollInputIntoView(input) {
        if (!input) return;
        
        const rect = input.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const keyboardHeight = this.originalViewportHeight - viewportHeight;
        const availableHeight = viewportHeight - keyboardHeight - 100; // 100px padding
        
        if (rect.top > availableHeight || rect.bottom > availableHeight) {
            input.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
    
    /**
     * Setup form enhancements
     */
    setupFormEnhancements() {
        // Enhance existing forms
        this.enhanceExistingForms();
        
        // Observer for dynamically added forms
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.matches('form, .mobile-form')) {
                            this.enhanceForm(node);
                        } else {
                            const forms = node.querySelectorAll('form, .mobile-form');
                            forms.forEach(form => this.enhanceForm(form));
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    /**
     * Enhance existing forms on page load
     */
    enhanceExistingForms() {
        const forms = document.querySelectorAll('form, .mobile-form');
        forms.forEach(form => this.enhanceForm(form));
    }
    
    /**
     * Enhance a specific form for mobile
     */
    enhanceForm(form) {
        if (this.formElements.has(form)) return; // Already enhanced
        
        this.formElements.set(form, {
            enhanced: true,
            inputs: []
        });
        
        // Add mobile form class
        form.classList.add('mobile-optimized-form');
        
        // Enhance all inputs in the form
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => this.enhanceInput(input));
        
        // Add form-level enhancements
        this.addFormValidationFeedback(form);
        this.addSubmitOptimizations(form);
    }
    
    /**
     * Enhance individual input for mobile
     */
    enhanceInput(input) {
        // Set minimum font size to prevent zoom on iOS
        if (!input.style.fontSize) {
            input.style.fontSize = '16px';
        }
        
        // Apply mobile-specific attributes based on input type
        this.applyMobileInputAttributes(input);
        
        // Add touch-friendly styling
        input.classList.add('mobile-optimized-input');
        
        // Setup focus/blur handlers
        this.setupInputFocusHandlers(input);
        
        // Add input validation enhancements
        this.addInputValidation(input);
        
        // Add input length feedback for textareas
        if (input.tagName.toLowerCase() === 'textarea') {
            this.addTextareaEnhancements(input);
        }
    }
    
    /**
     * Apply mobile-specific attributes to inputs
     */
    applyMobileInputAttributes(input) {
        const type = input.type || input.dataset.mobileType;
        const config = this.keyboardTypes[type];
        
        if (config) {
            Object.entries(config).forEach(([attr, value]) => {
                if (attr === 'inputMode') {
                    input.inputMode = value;
                } else if (attr === 'autocomplete' && !input.hasAttribute('autocomplete')) {
                    input.autocomplete = value;
                } else if (attr === 'autocapitalize' && !input.hasAttribute('autocapitalize')) {
                    input.autocapitalize = value;
                } else if (attr === 'autocorrect' && !input.hasAttribute('autocorrect')) {
                    input.autocorrect = value;
                } else if (attr === 'spellcheck' && !input.hasAttribute('spellcheck')) {
                    input.spellcheck = value;
                } else if (attr === 'pattern' && !input.hasAttribute('pattern')) {
                    input.pattern = value;
                } else if (attr === 'role' && !input.hasAttribute('role')) {
                    input.role = value;
                }
            });
        }
        
        // Special handling for number inputs
        if (input.type === 'number' || input.dataset.mobileType === 'numeric') {
            input.inputMode = 'numeric';
            input.pattern = '[0-9]*';
        }
        
        // Add touch-action for better scrolling
        if (!input.style.touchAction) {
            input.style.touchAction = 'manipulation';
        }
    }
    
    /**
     * Setup input focus handling
     */
    setupInputFocusHandling() {
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.handleInputFocus(e.target);
            }
        });
        
        document.addEventListener('focusout', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.handleInputBlur(e.target);
            }
        });
    }
    
    /**
     * Setup input focus handlers for specific input
     */
    setupInputFocusHandlers(input) {
        input.addEventListener('focus', () => {
            this.activeInput = input;
            input.classList.add('input-focused');
            
            // Add focused class to form group
            const formGroup = input.closest('.form-group, .mobile-form-group');
            if (formGroup) {
                formGroup.classList.add('input-group-focused');
            }
        });
        
        input.addEventListener('blur', () => {
            input.classList.remove('input-focused');
            
            // Remove focused class from form group
            const formGroup = input.closest('.form-group, .mobile-form-group');
            if (formGroup) {
                formGroup.classList.remove('input-group-focused');
            }
            
            if (this.activeInput === input) {
                setTimeout(() => {
                    this.activeInput = null;
                }, 100);
            }
        });
    }
    
    /**
     * Handle input focus
     */
    handleInputFocus(input) {
        this.activeInput = input;
        
        // Prevent zoom on iOS by ensuring font size is at least 16px
        if (parseFloat(getComputedStyle(input).fontSize) < 16) {
            input.style.fontSize = '16px';
        }
        
        // Handle different input types
        if (input.type === 'date' || input.type === 'datetime-local') {
            // iOS date picker handling
            this.handleDateInput(input);
        }
    }
    
    /**
     * Handle input blur
     */
    handleInputBlur(input) {
        // Validate input on blur
        this.validateInput(input);
    }
    
    /**
     * Handle date input specifics
     */
    handleDateInput(input) {
        // Add date picker enhancements for mobile
        if (!input.dataset.mobileEnhanced) {
            input.dataset.mobileEnhanced = 'true';
            
            // For iOS, ensure the date picker opens properly
            if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                input.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    input.focus();
                    input.click();
                });
            }
        }
    }
    
    /**
     * Add textarea enhancements
     */
    addTextareaEnhancements(textarea) {
        const maxLength = textarea.maxLength;
        if (maxLength && maxLength > 0) {
            const counter = this.createCharacterCounter(textarea, maxLength);
            textarea.parentNode.appendChild(counter);
            
            textarea.addEventListener('input', () => {
                this.updateCharacterCounter(textarea, counter, maxLength);
            });
        }
        
        // Auto-resize textarea
        this.addAutoResize(textarea);
    }
    
    /**
     * Create character counter element
     */
    createCharacterCounter(textarea, maxLength) {
        const counter = document.createElement('div');
        counter.className = 'character-counter mobile-text-xs';
        counter.style.cssText = `
            text-align: right;
            margin-top: 4px;
            color: var(--hd-text-muted);
            font-size: 12px;
        `;
        
        this.updateCharacterCounter(textarea, counter, maxLength);
        return counter;
    }
    
    /**
     * Update character counter
     */
    updateCharacterCounter(textarea, counter, maxLength) {
        const currentLength = textarea.value.length;
        const remaining = maxLength - currentLength;
        
        counter.textContent = `${currentLength}/${maxLength}`;
        
        if (remaining < 20) {
            counter.style.color = 'var(--hd-error)';
        } else if (remaining < 50) {
            counter.style.color = 'var(--hd-warning)';
        } else {
            counter.style.color = 'var(--hd-text-muted)';
        }
    }
    
    /**
     * Add auto-resize functionality to textarea
     */
    addAutoResize(textarea) {
        if (textarea.dataset.autoResize === 'false') return;
        
        const resize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 300) + 'px';
        };
        
        textarea.addEventListener('input', resize);
        textarea.addEventListener('paste', () => setTimeout(resize, 10));
        
        // Initial resize
        resize();
    }
    
    /**
     * Setup form validation
     */
    setupFormValidation() {
        document.addEventListener('invalid', (e) => {
            e.preventDefault();
            this.handleInvalidInput(e.target);
        }, true);
        
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.matches('form, .mobile-form')) {
                this.handleFormSubmit(form, e);
            }
        });
    }
    
    /**
     * Add input validation
     */
    addInputValidation(input) {
        input.addEventListener('blur', () => {
            if (input.value) {
                this.validateInput(input);
            }
        });
        
        input.addEventListener('input', () => {
            if (input.classList.contains('invalid')) {
                this.validateInput(input);
            }
        });
    }
    
    /**
     * Validate individual input
     */
    validateInput(input) {
        const isValid = input.checkValidity();
        const formGroup = input.closest('.form-group, .mobile-form-group');
        
        if (isValid) {
            input.classList.remove('invalid');
            input.classList.add('valid');
            if (formGroup) {
                formGroup.classList.remove('has-error');
                formGroup.classList.add('has-success');
            }
            this.removeErrorMessage(input);
        } else {
            input.classList.remove('valid');
            input.classList.add('invalid');
            if (formGroup) {
                formGroup.classList.remove('has-success');
                formGroup.classList.add('has-error');
            }
            this.showErrorMessage(input);
        }
    }
    
    /**
     * Handle invalid input
     */
    handleInvalidInput(input) {
        this.validateInput(input);
        
        // Scroll to first invalid input
        if (!document.querySelector('.invalid')) {
            this.scrollInputIntoView(input);
            input.focus();
        }
    }
    
    /**
     * Show error message for input
     */
    showErrorMessage(input) {
        this.removeErrorMessage(input); // Remove existing message first
        
        const errorMessage = input.validationMessage;
        const errorElement = document.createElement('div');
        errorElement.className = 'input-error-message mobile-text-xs';
        errorElement.textContent = errorMessage;
        errorElement.style.cssText = `
            color: var(--hd-error);
            font-size: 12px;
            margin-top: 4px;
            animation: slideDown 0.2s ease-out;
        `;
        
        const formGroup = input.closest('.form-group, .mobile-form-group');
        if (formGroup) {
            formGroup.appendChild(errorElement);
        } else {
            input.parentNode.insertBefore(errorElement, input.nextSibling);
        }
    }
    
    /**
     * Remove error message for input
     */
    removeErrorMessage(input) {
        const formGroup = input.closest('.form-group, .mobile-form-group') || input.parentNode;
        const existingError = formGroup.querySelector('.input-error-message');
        if (existingError) {
            existingError.remove();
        }
    }
    
    /**
     * Add form validation feedback
     */
    addFormValidationFeedback(form) {
        if (form.dataset.validationEnhanced) return;
        form.dataset.validationEnhanced = 'true';
        
        // Add form validation summary
        const summary = document.createElement('div');
        summary.className = 'form-validation-summary';
        summary.style.display = 'none';
        form.insertBefore(summary, form.firstChild);
    }
    
    /**
     * Add submit optimizations
     */
    addSubmitOptimizations(form) {
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        
        submitButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Add loading state
                this.addLoadingState(button);
                
                // Prevent double submission
                setTimeout(() => {
                    button.disabled = true;
                    setTimeout(() => {
                        button.disabled = false;
                        this.removeLoadingState(button);
                    }, 2000);
                }, 100);
            });
        });
    }
    
    /**
     * Handle form submit
     */
    handleFormSubmit(form, event) {
        // Validate all inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        let hasErrors = false;
        let firstInvalidInput = null;
        
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                hasErrors = true;
                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
                this.validateInput(input);
            }
        });
        
        if (hasErrors) {
            event.preventDefault();
            if (firstInvalidInput) {
                this.scrollInputIntoView(firstInvalidInput);
                firstInvalidInput.focus();
            }
            
            // Show validation summary
            this.showValidationSummary(form);
        }
    }
    
    /**
     * Show validation summary
     */
    showValidationSummary(form) {
        const summary = form.querySelector('.form-validation-summary');
        if (summary) {
            const errors = form.querySelectorAll('.invalid');
            if (errors.length > 0) {
                summary.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Please correct the following errors:</strong>
                        <ul>
                            ${Array.from(errors).map(input => 
                                `<li>${this.getFieldLabel(input)}: ${input.validationMessage}</li>`
                            ).join('')}
                        </ul>
                    </div>
                `;
                summary.style.display = 'block';
            }
        }
    }
    
    /**
     * Get field label for validation messages
     */
    getFieldLabel(input) {
        const label = input.labels && input.labels[0];
        if (label) {
            return label.textContent.trim();
        }
        
        const placeholder = input.placeholder;
        if (placeholder) {
            return placeholder;
        }
        
        return input.name || 'Field';
    }
    
    /**
     * Add loading state to button
     */
    addLoadingState(button) {
        if (button.dataset.originalText) return; // Already has loading state
        
        button.dataset.originalText = button.textContent;
        button.textContent = 'Loading...';
        button.classList.add('loading');
    }
    
    /**
     * Remove loading state from button
     */
    removeLoadingState(button) {
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
            delete button.dataset.originalText;
            button.classList.remove('loading');
        }
    }
    
    /**
     * Inject mobile form styles
     */
    injectMobileFormStyles() {
        if (document.getElementById('mobile-form-optimizer-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'mobile-form-optimizer-styles';
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .mobile-optimized-form {
                padding-bottom: 100px; /* Extra padding for keyboard */
            }
            
            .keyboard-visible .mobile-optimized-form {
                padding-bottom: 20px;
            }
            
            .mobile-optimized-input {
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            
            .mobile-optimized-input.input-focused {
                border-color: var(--hd-primary);
                box-shadow: 0 0 0 3px var(--hd-primary-100);
            }
            
            .mobile-optimized-input.valid {
                border-color: var(--hd-success);
            }
            
            .mobile-optimized-input.invalid {
                border-color: var(--hd-error);
                box-shadow: 0 0 0 3px var(--hd-error-100);
            }
            
            .input-group-focused {
                position: relative;
                z-index: 10;
            }
            
            .has-error .mobile-optimized-input {
                border-color: var(--hd-error);
            }
            
            .has-success .mobile-optimized-input {
                border-color: var(--hd-success);
            }
            
            .character-counter {
                font-size: 12px;
                color: var(--hd-text-muted);
                text-align: right;
                margin-top: 4px;
            }
            
            .input-error-message {
                color: var(--hd-error);
                font-size: 12px;
                margin-top: 4px;
                animation: slideDown 0.2s ease-out;
            }
            
            .form-validation-summary {
                margin-bottom: 20px;
            }
            
            .keyboard-hidden {
                transform: translateY(100px) !important;
                opacity: 0 !important;
                transition: all 0.3s ease !important;
            }
            
            .keyboard-visible .mobile-fab,
            .keyboard-visible .mobile-bottom-nav {
                transform: translateY(100px);
                opacity: 0;
                transition: all 0.3s ease;
            }
            
            button.loading {
                opacity: 0.7;
                pointer-events: none;
            }
            
            @media (max-width: 480px) {
                .mobile-optimized-form {
                    padding-left: 12px;
                    padding-right: 12px;
                }
                
                .mobile-optimized-input {
                    font-size: 16px !important; /* Prevent zoom on iOS */
                }
            }
            
            @media (prefers-reduced-motion: reduce) {
                .mobile-optimized-input,
                .input-error-message,
                .keyboard-hidden {
                    transition: none !important;
                    animation: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Get optimizer status and statistics
     */
    getStatus() {
        return {
            initialized: this.isInitialized,
            keyboardVisible: this.keyboardVisible,
            activeInput: this.activeInput ? this.activeInput.tagName.toLowerCase() : null,
            enhancedForms: this.formElements.size,
            isMobile: this.isMobileDevice()
        };
    }
    
    /**
     * Destroy mobile form optimizer
     */
    destroy() {
        // Remove event listeners would go here
        // Clear form elements map
        this.formElements = new WeakMap();
        
        // Remove injected styles
        const styles = document.getElementById('mobile-form-optimizer-styles');
        if (styles) {
            styles.remove();
        }
        
        this.isInitialized = false;
        console.log('Mobile Form Optimizer destroyed');
    }
}

// Auto-initialize on mobile devices
document.addEventListener('DOMContentLoaded', () => {
    window.mobileFormOptimizer = new MobileFormOptimizer();
});

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileFormOptimizer;
}

export default MobileFormOptimizer;
