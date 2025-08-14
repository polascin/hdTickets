/**
 * HD Tickets - Comprehensive Accessibility JavaScript Module
 * Enhanced keyboard navigation, ARIA management, and screen reader support
 * =========================================================================
 */

class HDAccessibility {
    constructor(options = {}) {
        this.options = {
            enableSkipNavigation: true,
            enableKeyboardNavigation: true,
            enableFocusManagement: true,
            enableAriaLiveRegions: true,
            enableFormValidation: true,
            focusTrapSelector: '.hd-focus-trap',
            skipLinksContainer: 'body',
            ...options
        };

        this.focusableElements = [
            'a[href]:not([disabled])',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            '[tabindex]:not([tabindex="-1"]):not([disabled])',
            '[contenteditable]:not([contenteditable="false"])',
            'details',
            'summary',
            '[role="button"]:not([disabled])',
            '[role="link"]:not([disabled])',
            '[role="menuitem"]:not([disabled])',
            '[role="tab"]:not([disabled])'
        ].join(',');

        this.init();
    }

    /**
     * Initialize accessibility features
     */
    init() {
        console.log('🎯 HD Accessibility: Initializing comprehensive accessibility features...');
        
        this.setupSkipNavigation();
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupAriaLiveRegions();
        this.setupFormAccessibility();
        this.setupColorContrastMonitoring();
        this.setupReducedMotionSupport();
        this.setupHighContrastSupport();
        this.bindGlobalEvents();

        console.log('✅ HD Accessibility: All features initialized successfully');
    }

    /**
     * Setup skip navigation links
     */
    setupSkipNavigation() {
        if (!this.options.enableSkipNavigation) return;

        // Create skip navigation container if it doesn't exist
        let skipContainer = document.querySelector('.hd-skip-links');
        if (!skipContainer) {
            skipContainer = document.createElement('div');
            skipContainer.className = 'hd-skip-links';
            skipContainer.setAttribute('aria-label', 'Skip navigation links');
            document.body.insertBefore(skipContainer, document.body.firstChild);
        }

        // Define skip navigation links
        const skipLinks = [
            { text: 'Skip to main content', target: '#main-content, main, [role="main"]' },
            { text: 'Skip to navigation', target: '#main-navigation, nav[role="navigation"], .main-nav' },
            { text: 'Skip to search', target: '#search, [role="search"], .search-form' },
            { text: 'Skip to footer', target: '#footer, footer, [role="contentinfo"]' }
        ];

        // Create and append skip links
        skipLinks.forEach(link => {
            const target = document.querySelector(link.target);
            if (target) {
                const skipLink = document.createElement('a');
                skipLink.href = `#${target.id || this.generateId(target)}`;
                skipLink.className = 'hd-skip-nav';
                skipLink.textContent = link.text;
                skipLink.setAttribute('aria-label', `${link.text} (Skip navigation)`);
                
                // Ensure target has an ID
                if (!target.id) {
                    target.id = this.generateId(target);
                }

                // Make target focusable if needed
                if (!target.hasAttribute('tabindex')) {
                    target.setAttribute('tabindex', '-1');
                }

                skipContainer.appendChild(skipLink);
            }
        });

        console.log('✅ Skip navigation links created');
    }

    /**
     * Setup comprehensive keyboard navigation
     */
    setupKeyboardNavigation() {
        if (!this.options.enableKeyboardNavigation) return;

        // Tab navigation for dropdowns and menus
        this.setupDropdownNavigation();
        
        // Modal and dialog keyboard navigation
        this.setupModalNavigation();
        
        // Tab panels and accordions
        this.setupTabNavigation();
        
        // Form submission with Enter key
        this.setupFormKeyboardSupport();

        // Escape key handlers
        this.setupEscapeKeyHandlers();

        console.log('✅ Keyboard navigation enhanced');
    }

    /**
     * Setup dropdown keyboard navigation
     */
    setupDropdownNavigation() {
        const dropdowns = document.querySelectorAll('[role="menu"], [role="menubar"], .dropdown-menu');
        
        dropdowns.forEach(dropdown => {
            const trigger = dropdown.previousElementSibling || document.querySelector(`[aria-controls="${dropdown.id}"]`);
            const menuItems = dropdown.querySelectorAll('[role="menuitem"], a, button');
            let currentIndex = -1;

            if (trigger) {
                trigger.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.showDropdown(dropdown);
                        if (menuItems.length > 0) {
                            currentIndex = 0;
                            menuItems[0].focus();
                        }
                    }
                });
            }

            dropdown.addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        currentIndex = (currentIndex + 1) % menuItems.length;
                        menuItems[currentIndex].focus();
                        break;
                        
                    case 'ArrowUp':
                        e.preventDefault();
                        currentIndex = currentIndex <= 0 ? menuItems.length - 1 : currentIndex - 1;
                        menuItems[currentIndex].focus();
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        currentIndex = 0;
                        menuItems[0].focus();
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        currentIndex = menuItems.length - 1;
                        menuItems[currentIndex].focus();
                        break;
                        
                    case 'Escape':
                        e.preventDefault();
                        this.hideDropdown(dropdown);
                        if (trigger) trigger.focus();
                        break;
                        
                    case 'Tab':
                        this.hideDropdown(dropdown);
                        break;
                }
            });
        });
    }

    /**
     * Setup modal keyboard navigation and focus trapping
     */
    setupModalNavigation() {
        const modals = document.querySelectorAll('[role="dialog"], .modal, .hd-modal');
        
        modals.forEach(modal => {
            const focusableElements = modal.querySelectorAll(this.focusableElements);
            
            modal.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeModal(modal);
                } else if (e.key === 'Tab') {
                    this.trapFocus(e, focusableElements);
                }
            });
        });
    }

    /**
     * Setup tab navigation (ARIA tabs)
     */
    setupTabNavigation() {
        const tabLists = document.querySelectorAll('[role="tablist"]');
        
        tabLists.forEach(tabList => {
            const tabs = tabList.querySelectorAll('[role="tab"]');
            let currentTab = 0;

            tabs.forEach((tab, index) => {
                tab.addEventListener('keydown', (e) => {
                    switch (e.key) {
                        case 'ArrowRight':
                        case 'ArrowDown':
                            e.preventDefault();
                            currentTab = (index + 1) % tabs.length;
                            this.activateTab(tabs[currentTab]);
                            break;
                            
                        case 'ArrowLeft':
                        case 'ArrowUp':
                            e.preventDefault();
                            currentTab = index === 0 ? tabs.length - 1 : index - 1;
                            this.activateTab(tabs[currentTab]);
                            break;
                            
                        case 'Home':
                            e.preventDefault();
                            currentTab = 0;
                            this.activateTab(tabs[0]);
                            break;
                            
                        case 'End':
                            e.preventDefault();
                            currentTab = tabs.length - 1;
                            this.activateTab(tabs[currentTab]);
                            break;
                    }
                });

                tab.addEventListener('click', () => {
                    this.activateTab(tab);
                });
            });
        });
    }

    /**
     * Setup form keyboard support
     */
    setupFormKeyboardSupport() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Enter key submission for single-line inputs
            const inputs = form.querySelectorAll('input:not([type="textarea"]), select');
            inputs.forEach(input => {
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
                        if (submitButton && !submitButton.disabled) {
                            submitButton.click();
                        }
                    }
                });
            });

            // Escape key to clear form errors
            form.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.clearFormErrors(form);
                }
            });
        });
    }

    /**
     * Setup escape key handlers
     */
    setupEscapeKeyHandlers() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close open dropdowns
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show, [aria-expanded="true"]');
                openDropdowns.forEach(dropdown => {
                    this.hideDropdown(dropdown);
                });

                // Clear focus from search inputs
                const activeElement = document.activeElement;
                if (activeElement && activeElement.type === 'search') {
                    activeElement.value = '';
                    activeElement.blur();
                }
            }
        });
    }

    /**
     * Setup focus management
     */
    setupFocusManagement() {
        if (!this.options.enableFocusManagement) return;

        // Enhanced focus indicators
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });

        // Focus restoration for dynamic content
        this.setupFocusRestoration();

        // Focus management for single-page app navigation
        this.setupSPAFocusManagement();

        console.log('✅ Focus management enhanced');
    }

    /**
     * Setup focus restoration
     */
    setupFocusRestoration() {
        this.focusHistory = [];

        document.addEventListener('focusin', (e) => {
            this.focusHistory.push(e.target);
            if (this.focusHistory.length > 10) {
                this.focusHistory.shift();
            }
        });
    }

    /**
     * Setup SPA focus management
     */
    setupSPAFocusManagement() {
        // Monitor for route changes and focus management
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Focus management for new content
                            const heading = node.querySelector('h1, h2, [role="heading"]');
                            if (heading && !heading.hasAttribute('tabindex')) {
                                heading.setAttribute('tabindex', '-1');
                            }
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Setup ARIA live regions
     */
    setupAriaLiveRegions() {
        if (!this.options.enableAriaLiveRegions) return;

        // Create global live regions if they don't exist
        this.createLiveRegion('polite', 'hd-status-region');
        this.createLiveRegion('assertive', 'hd-alert-region');

        // Monitor form validation for live announcements
        this.monitorFormValidation();

        // Monitor dynamic content updates
        this.monitorContentUpdates();

        console.log('✅ ARIA live regions configured');
    }

    /**
     * Create an ARIA live region
     */
    createLiveRegion(politeness, id) {
        if (!document.getElementById(id)) {
            const liveRegion = document.createElement('div');
            liveRegion.id = id;
            liveRegion.className = 'hd-sr-live-region';
            liveRegion.setAttribute('aria-live', politeness);
            liveRegion.setAttribute('aria-atomic', 'true');
            document.body.appendChild(liveRegion);
        }
    }

    /**
     * Monitor form validation for screen reader announcements
     */
    monitorFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('invalid', (e) => {
                    const fieldName = input.getAttribute('aria-label') || 
                                    input.getAttribute('placeholder') || 
                                    input.name || 
                                    'Field';
                    
                    this.announceToScreenReader(`${fieldName} has an error: ${input.validationMessage}`, 'assertive');
                });

                input.addEventListener('input', () => {
                    if (input.checkValidity() && input.getAttribute('aria-invalid') === 'true') {
                        input.setAttribute('aria-invalid', 'false');
                        const fieldName = input.getAttribute('aria-label') || 
                                        input.getAttribute('placeholder') || 
                                        input.name || 
                                        'Field';
                        this.announceToScreenReader(`${fieldName} is now valid`, 'polite');
                    }
                });
            });
        });
    }

    /**
     * Monitor dynamic content updates
     */
    monitorContentUpdates() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Announce new content additions
                            if (node.hasAttribute('data-announce')) {
                                const message = node.getAttribute('data-announce');
                                this.announceToScreenReader(message, 'polite');
                            }

                            // Auto-focus new error messages
                            const errorMessages = node.querySelectorAll('[role="alert"], .hd-error-message');
                            errorMessages.forEach(error => {
                                this.announceToScreenReader(error.textContent, 'assertive');
                            });
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Setup form accessibility enhancements
     */
    setupFormAccessibility() {
        if (!this.options.enableFormValidation) return;

        this.enhanceFormLabels();
        this.setupFormValidation();
        this.enhanceFormErrors();

        console.log('✅ Form accessibility enhanced');
    }

    /**
     * Enhance form labels and descriptions
     */
    enhanceFormLabels() {
        const inputs = document.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`) || 
                         input.closest('.hd-form-group')?.querySelector('label');
            
            if (label && !input.getAttribute('aria-labelledby')) {
                if (!label.id) {
                    label.id = this.generateId(label);
                }
                input.setAttribute('aria-labelledby', label.id);
            }

            // Link field descriptions
            const description = input.nextElementSibling?.classList.contains('hd-field-description') 
                              ? input.nextElementSibling 
                              : input.closest('.hd-form-group')?.querySelector('.hd-field-description');
            
            if (description) {
                if (!description.id) {
                    description.id = this.generateId(description);
                }
                const describedBy = input.getAttribute('aria-describedby') || '';
                input.setAttribute('aria-describedby', `${describedBy} ${description.id}`.trim());
            }
        });
    }

    /**
     * Setup real-time form validation
     */
    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });

                input.addEventListener('input', () => {
                    // Clear previous validation state
                    if (input.getAttribute('aria-invalid') === 'true') {
                        this.clearFieldError(input);
                    }
                });
            });

            form.addEventListener('submit', (e) => {
                let hasErrors = false;
                
                inputs.forEach(input => {
                    if (!this.validateField(input)) {
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    const firstError = form.querySelector('[aria-invalid="true"]');
                    if (firstError) {
                        firstError.focus();
                        this.announceToScreenReader('Form has errors. Please check the fields and try again.', 'assertive');
                    }
                }
            });
        });
    }

    /**
     * Enhance form error handling
     */
    enhanceFormErrors() {
        const errorMessages = document.querySelectorAll('.hd-error-message, [role="alert"]');
        
        errorMessages.forEach(error => {
            if (!error.id) {
                error.id = this.generateId(error);
            }

            // Find related input field
            const input = error.previousElementSibling?.tagName === 'INPUT' 
                        ? error.previousElementSibling 
                        : error.closest('.hd-form-group')?.querySelector('input, textarea, select');
            
            if (input) {
                const describedBy = input.getAttribute('aria-describedby') || '';
                input.setAttribute('aria-describedby', `${describedBy} ${error.id}`.trim());
                input.setAttribute('aria-invalid', 'true');
            }
        });
    }

    /**
     * Setup color contrast monitoring
     */
    setupColorContrastMonitoring() {
        // Check for color contrast issues and provide warnings
        if (window.getComputedStyle) {
            const textElements = document.querySelectorAll('p, span, div, a, button, label, h1, h2, h3, h4, h5, h6');
            
            textElements.forEach(element => {
                const styles = window.getComputedStyle(element);
                const textColor = styles.color;
                const backgroundColor = styles.backgroundColor;
                
                // Only check if we have both colors and they're not transparent
                if (textColor && backgroundColor && backgroundColor !== 'rgba(0, 0, 0, 0)') {
                    const contrast = this.calculateContrast(textColor, backgroundColor);
                    
                    if (contrast < 4.5) {
                        console.warn('⚠️ Low contrast detected:', element, `Contrast: ${contrast.toFixed(2)}`);
                        element.setAttribute('data-low-contrast', 'true');
                    }
                }
            });
        }

        console.log('✅ Color contrast monitoring active');
    }

    /**
     * Setup reduced motion support
     */
    setupReducedMotionSupport() {
        if (window.matchMedia) {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            
            if (prefersReducedMotion.matches) {
                document.body.classList.add('reduced-motion');
                console.log('✅ Reduced motion preferences detected and applied');
            }

            prefersReducedMotion.addEventListener('change', (e) => {
                if (e.matches) {
                    document.body.classList.add('reduced-motion');
                } else {
                    document.body.classList.remove('reduced-motion');
                }
            });
        }
    }

    /**
     * Setup high contrast support
     */
    setupHighContrastSupport() {
        if (window.matchMedia) {
            const prefersHighContrast = window.matchMedia('(prefers-contrast: high)');
            
            if (prefersHighContrast.matches) {
                document.body.classList.add('high-contrast');
                console.log('✅ High contrast preferences detected and applied');
            }

            prefersHighContrast.addEventListener('change', (e) => {
                if (e.matches) {
                    document.body.classList.add('high-contrast');
                } else {
                    document.body.classList.remove('high-contrast');
                }
            });
        }
    }

    /**
     * Bind global accessibility events
     */
    bindGlobalEvents() {
        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt + 1: Skip to main content
            if (e.altKey && e.key === '1') {
                e.preventDefault();
                const mainContent = document.querySelector('#main-content, main, [role="main"]');
                if (mainContent) {
                    mainContent.focus();
                    this.announceToScreenReader('Jumped to main content', 'polite');
                }
            }
            
            // Alt + 2: Skip to navigation
            if (e.altKey && e.key === '2') {
                e.preventDefault();
                const navigation = document.querySelector('#main-navigation, nav[role="navigation"], .main-nav');
                if (navigation) {
                    navigation.focus();
                    this.announceToScreenReader('Jumped to navigation', 'polite');
                }
            }
        });

        // Monitor for focus changes and announce context
        document.addEventListener('focusin', (e) => {
            const element = e.target;
            
            // Announce form context when entering form fields
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                const form = element.closest('form');
                const formTitle = form?.querySelector('h1, h2, .form-title')?.textContent;
                
                if (formTitle && !element.hasAttribute('data-context-announced')) {
                    element.setAttribute('data-context-announced', 'true');
                    setTimeout(() => {
                        this.announceToScreenReader(`Entering ${formTitle} form`, 'polite');
                    }, 100);
                }
            }
        });
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Announce message to screen reader
     */
    announceToScreenReader(message, priority = 'polite') {
        const regionId = priority === 'assertive' ? 'hd-alert-region' : 'hd-status-region';
        const liveRegion = document.getElementById(regionId);
        
        if (liveRegion) {
            liveRegion.textContent = message;
            
            // Clear after announcement
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }

    /**
     * Validate a form field
     */
    validateField(input) {
        const isValid = input.checkValidity();
        
        if (isValid) {
            input.setAttribute('aria-invalid', 'false');
            this.clearFieldError(input);
            return true;
        } else {
            input.setAttribute('aria-invalid', 'true');
            this.showFieldError(input, input.validationMessage);
            return false;
        }
    }

    /**
     * Show field error
     */
    showFieldError(input, message) {
        this.clearFieldError(input);
        
        const errorId = `error-${input.id || this.generateId(input)}`;
        const errorElement = document.createElement('div');
        errorElement.id = errorId;
        errorElement.className = 'hd-error-message';
        errorElement.setAttribute('role', 'alert');
        errorElement.textContent = message;
        
        input.parentNode.insertBefore(errorElement, input.nextSibling);
        
        const describedBy = input.getAttribute('aria-describedby') || '';
        input.setAttribute('aria-describedby', `${describedBy} ${errorId}`.trim());
        
        input.classList.add('hd-field-invalid');
    }

    /**
     * Clear field error
     */
    clearFieldError(input) {
        const errorElement = input.parentNode.querySelector('.hd-error-message');
        if (errorElement) {
            errorElement.remove();
        }
        
        input.classList.remove('hd-field-invalid');
        input.classList.add('hd-field-valid');
        
        // Clean up aria-describedby
        const describedBy = input.getAttribute('aria-describedby');
        if (describedBy) {
            const cleanDescribedBy = describedBy.replace(/error-\w+/g, '').trim();
            if (cleanDescribedBy) {
                input.setAttribute('aria-describedby', cleanDescribedBy);
            } else {
                input.removeAttribute('aria-describedby');
            }
        }
    }

    /**
     * Clear form errors
     */
    clearFormErrors(form) {
        const errorMessages = form.querySelectorAll('.hd-error-message');
        errorMessages.forEach(error => error.remove());
        
        const invalidFields = form.querySelectorAll('[aria-invalid="true"]');
        invalidFields.forEach(field => {
            field.setAttribute('aria-invalid', 'false');
            field.classList.remove('hd-field-invalid');
        });
        
        this.announceToScreenReader('Form errors cleared', 'polite');
    }

    /**
     * Trap focus within a set of elements
     */
    trapFocus(event, focusableElements) {
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        if (event.shiftKey) {
            if (document.activeElement === firstFocusable) {
                event.preventDefault();
                lastFocusable.focus();
            }
        } else {
            if (document.activeElement === lastFocusable) {
                event.preventDefault();
                firstFocusable.focus();
            }
        }
    }

    /**
     * Activate a tab
     */
    activateTab(tab) {
        const tabList = tab.closest('[role="tablist"]');
        const tabs = tabList.querySelectorAll('[role="tab"]');
        const panelId = tab.getAttribute('aria-controls');
        const panel = document.getElementById(panelId);
        
        // Deactivate all tabs
        tabs.forEach(t => {
            t.setAttribute('aria-selected', 'false');
            t.setAttribute('tabindex', '-1');
        });
        
        // Hide all panels
        tabs.forEach(t => {
            const p = document.getElementById(t.getAttribute('aria-controls'));
            if (p) {
                p.hidden = true;
                p.setAttribute('aria-hidden', 'true');
            }
        });
        
        // Activate current tab
        tab.setAttribute('aria-selected', 'true');
        tab.setAttribute('tabindex', '0');
        tab.focus();
        
        // Show current panel
        if (panel) {
            panel.hidden = false;
            panel.setAttribute('aria-hidden', 'false');
        }
        
        this.announceToScreenReader(`${tab.textContent} tab activated`, 'polite');
    }

    /**
     * Show dropdown
     */
    showDropdown(dropdown) {
        dropdown.classList.add('show');
        dropdown.setAttribute('aria-hidden', 'false');
        
        const trigger = document.querySelector(`[aria-controls="${dropdown.id}"]`);
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
    }

    /**
     * Hide dropdown
     */
    hideDropdown(dropdown) {
        dropdown.classList.remove('show');
        dropdown.setAttribute('aria-hidden', 'true');
        
        const trigger = document.querySelector(`[aria-controls="${dropdown.id}"]`);
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
    }

    /**
     * Close modal
     */
    closeModal(modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        
        // Restore focus to trigger element
        const trigger = document.querySelector(`[aria-controls="${modal.id}"]`);
        if (trigger) {
            trigger.focus();
        } else if (this.focusHistory.length > 0) {
            const lastFocused = this.focusHistory.pop();
            if (lastFocused && document.contains(lastFocused)) {
                lastFocused.focus();
            }
        }
        
        this.announceToScreenReader('Dialog closed', 'polite');
    }

    /**
     * Calculate color contrast ratio
     */
    calculateContrast(color1, color2) {
        const rgb1 = this.parseColor(color1);
        const rgb2 = this.parseColor(color2);
        
        if (!rgb1 || !rgb2) return 21; // Return max contrast if we can't parse
        
        const l1 = this.relativeLuminance(rgb1);
        const l2 = this.relativeLuminance(rgb2);
        
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        
        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Parse color string to RGB values
     */
    parseColor(colorStr) {
        const div = document.createElement('div');
        div.style.color = colorStr;
        document.body.appendChild(div);
        const computedColor = window.getComputedStyle(div).color;
        document.body.removeChild(div);
        
        const match = computedColor.match(/rgb\((\d+), (\d+), (\d+)\)/);
        return match ? [parseInt(match[1]), parseInt(match[2]), parseInt(match[3])] : null;
    }

    /**
     * Calculate relative luminance
     */
    relativeLuminance([r, g, b]) {
        const rsRGB = r / 255;
        const gsRGB = g / 255;
        const bsRGB = b / 255;
        
        const rLin = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4);
        const gLin = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4);
        const bLin = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4);
        
        return 0.2126 * rLin + 0.7152 * gLin + 0.0722 * bLin;
    }

    /**
     * Generate unique ID
     */
    generateId(element) {
        const prefix = element.tagName.toLowerCase();
        const random = Math.random().toString(36).substr(2, 9);
        return `${prefix}-${random}`;
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.hdAccessibility = new HDAccessibility();
    });
} else {
    window.hdAccessibility = new HDAccessibility();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HDAccessibility;
}

console.log('🎯 HD Accessibility module loaded successfully');
