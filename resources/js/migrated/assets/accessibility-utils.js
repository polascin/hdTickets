/**
 * HD Tickets Accessibility Enhancement Utilities
 * 
 * Provides JavaScript enhancements for accessibility including:
 * - Live region announcements
 * - Focus management
 * - Keyboard navigation helpers
 * - ARIA state management
 * - Skip link functionality
 */

(function(window, document) {
    'use strict';

    const AccessibilityUtils = {
        
        // Initialize accessibility utilities
        init: function() {
            this.setupLiveRegions();
            this.setupSkipLinks();
            this.setupFocusManagement();
            this.setupKeyboardNavigation();
            this.setupAriaManagement();
            this.setupErrorHandling();
            this.setupDebugMode();
            this.initializeA11yFeatures();
        },

        // Live Regions for Screen Reader Announcements
        setupLiveRegions: function() {
            // Create polite live region if it doesn't exist
            if (!document.getElementById('hd-live-polite')) {
                const politeRegion = document.createElement('div');
                politeRegion.id = 'hd-live-polite';
                politeRegion.className = 'hd-live-region';
                politeRegion.setAttribute('aria-live', 'polite');
                politeRegion.setAttribute('aria-atomic', 'true');
                document.body.appendChild(politeRegion);
            }

            // Create assertive live region if it doesn't exist
            if (!document.getElementById('hd-live-assertive')) {
                const assertiveRegion = document.createElement('div');
                assertiveRegion.id = 'hd-live-assertive';
                assertiveRegion.className = 'hd-live-region';
                assertiveRegion.setAttribute('aria-live', 'assertive');
                assertiveRegion.setAttribute('aria-atomic', 'true');
                document.body.appendChild(assertiveRegion);
            }

            // Create status region for form validation
            if (!document.getElementById('hd-status-region')) {
                const statusRegion = document.createElement('div');
                statusRegion.id = 'hd-status-region';
                statusRegion.className = 'hd-live-region';
                statusRegion.setAttribute('role', 'status');
                statusRegion.setAttribute('aria-live', 'polite');
                document.body.appendChild(statusRegion);
            }
        },

        // Announce messages to screen readers
        announce: function(message, priority = 'polite', delay = 100) {
            if (!message) return;

            const regionId = priority === 'assertive' ? 'hd-live-assertive' : 'hd-live-polite';
            const region = document.getElementById(regionId);
            
            if (region) {
                // Clear previous message first
                region.textContent = '';
                
                // Add new message after a short delay to ensure screen readers pick it up
                setTimeout(() => {
                    region.textContent = message;
                    
                    // Clear the message after 10 seconds to avoid repetition
                    setTimeout(() => {
                        region.textContent = '';
                    }, 10000);
                }, delay);
            }
        },

        // Announce status messages (for forms, loading states, etc.)
        announceStatus: function(message, delay = 100) {
            const statusRegion = document.getElementById('hd-status-region');
            
            if (statusRegion && message) {
                statusRegion.textContent = '';
                
                setTimeout(() => {
                    statusRegion.textContent = message;
                    
                    setTimeout(() => {
                        statusRegion.textContent = '';
                    }, 8000);
                }, delay);
            }
        },

        // Setup skip links functionality
        setupSkipLinks: function() {
            const skipLinks = document.querySelectorAll('.hd-skip-link, .hd-skip-to-content');
            
            skipLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const targetId = link.getAttribute('href');
                    if (targetId && targetId.startsWith('#')) {
                        const target = document.querySelector(targetId);
                        
                        if (target) {
                            e.preventDefault();
                            this.focusElement(target);
                            
                            // Announce skip action
                            this.announce(`Skipped to ${target.textContent || target.getAttribute('aria-label') || 'content'}`);
                        }
                    }
                });
            });
        },

        // Enhanced focus management
        setupFocusManagement: function() {
            // Store the last focused element before modal opens
            let lastFocusedElement = null;

            // Modal focus trap
            document.addEventListener('keydown', (e) => {
                const modal = document.querySelector('.hd-modal:not(.hd-modal--hidden)');
                if (modal && e.key === 'Tab') {
                    this.trapFocus(e, modal);
                }
            });

            // Handle modal opening
            this.onModalOpen = (modal) => {
                lastFocusedElement = document.activeElement;
                const firstFocusable = this.getFirstFocusableElement(modal);
                if (firstFocusable) {
                    firstFocusable.focus();
                }
                
                this.announce('Dialog opened');
            };

            // Handle modal closing
            this.onModalClose = () => {
                if (lastFocusedElement) {
                    lastFocusedElement.focus();
                    lastFocusedElement = null;
                }
                
                this.announce('Dialog closed');
            };

            // Focus visible detection for better UX
            this.setupFocusVisible();
        },

        // Focus visible implementation
        setupFocusVisible: function() {
            let hadKeyboardEvent = true;
            const keyboardThrottleTimeout = 100;

            const focusTriggersKeyboardModality = (e) => {
                if (['Tab', 'Shift', 'Meta', 'Alt', 'Control'].includes(e.key)) {
                    return;
                }
                hadKeyboardEvent = true;
            };

            const focusTriggersPointerModality = () => {
                hadKeyboardEvent = false;
            };

            const onFocus = (e) => {
                if (hadKeyboardEvent || e.target.matches(':focus-visible')) {
                    e.target.classList.add('focus-visible');
                }
            };

            const onBlur = (e) => {
                e.target.classList.remove('focus-visible');
            };

            document.addEventListener('keydown', focusTriggersKeyboardModality, true);
            document.addEventListener('mousedown', focusTriggersPointerModality, true);
            document.addEventListener('pointerdown', focusTriggersPointerModality, true);
            document.addEventListener('touchstart', focusTriggersPointerModality, true);
            document.addEventListener('focus', onFocus, true);
            document.addEventListener('blur', onBlur, true);

            document.documentElement.classList.add('js-focus-visible');
        },

        // Focus element with accessibility considerations
        focusElement: function(element, options = {}) {
            if (!element) return;

            // Make element focusable if it isn't already
            if (!element.hasAttribute('tabindex') && !this.isFocusable(element)) {
                element.setAttribute('tabindex', '-1');
            }

            // Focus with optional scroll behavior
            element.focus(options);

            // Scroll into view if needed
            if (options.scrollIntoView !== false) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest'
                });
            }
        },

        // Focus trap for modals and dialogs
        trapFocus: function(e, container) {
            const focusableElements = this.getFocusableElements(container);
            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        },

        // Get all focusable elements in a container
        getFocusableElements: function(container) {
            const focusableSelectors = [
                'a[href]',
                'button:not([disabled])',
                'textarea:not([disabled])',
                'input:not([disabled])',
                'select:not([disabled])',
                '[tabindex]:not([tabindex="-1"])',
                '[contenteditable]',
                'summary'
            ];

            return Array.from(container.querySelectorAll(focusableSelectors.join(',')))
                .filter(el => this.isVisible(el));
        },

        // Get first focusable element
        getFirstFocusableElement: function(container) {
            const focusableElements = this.getFocusableElements(container);
            return focusableElements[0] || null;
        },

        // Check if element is focusable
        isFocusable: function(element) {
            const focusableElements = [
                'A', 'BUTTON', 'INPUT', 'TEXTAREA', 'SELECT', 'DETAILS', 'SUMMARY'
            ];
            
            return focusableElements.includes(element.tagName) ||
                   element.hasAttribute('tabindex') ||
                   element.hasAttribute('contenteditable');
        },

        // Check if element is visible
        isVisible: function(element) {
            return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
        },

        // Keyboard navigation helpers
        setupKeyboardNavigation: function() {
            // Arrow key navigation for tab panels, menus, etc.
            document.addEventListener('keydown', (e) => {
                const activeElement = document.activeElement;
                const parent = activeElement.closest('[role="tablist"], [role="menu"], [role="menubar"]');
                
                if (parent && ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key)) {
                    this.handleArrowNavigation(e, parent);
                }
            });

            // Escape key handling
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.handleEscapeKey(e);
                }
            });

            // Enter/Space key handling for custom controls
            document.addEventListener('keydown', (e) => {
                if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[role="button"]')) {
                    e.preventDefault();
                    e.target.click();
                }
            });
        },

        // Handle arrow key navigation
        handleArrowNavigation: function(e, container) {
            const role = container.getAttribute('role');
            const currentElement = document.activeElement;
            let siblings = [];
            let currentIndex = -1;

            if (role === 'tablist') {
                siblings = Array.from(container.querySelectorAll('[role="tab"]'));
            } else if (role === 'menu' || role === 'menubar') {
                siblings = Array.from(container.querySelectorAll('[role="menuitem"]'));
            }

            currentIndex = siblings.indexOf(currentElement);
            if (currentIndex === -1) return;

            e.preventDefault();

            let nextIndex;
            const isHorizontal = role === 'tablist' || role === 'menubar';

            switch (e.key) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    nextIndex = currentIndex > 0 ? currentIndex - 1 : siblings.length - 1;
                    break;
                case 'ArrowRight':
                case 'ArrowDown':
                    nextIndex = currentIndex < siblings.length - 1 ? currentIndex + 1 : 0;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = siblings.length - 1;
                    break;
                default:
                    return;
            }

            if (siblings[nextIndex]) {
                siblings[nextIndex].focus();
                
                // Auto-activate tabs
                if (role === 'tablist' && siblings[nextIndex].getAttribute('role') === 'tab') {
                    siblings[nextIndex].click();
                }
            }
        },

        // Handle escape key
        handleEscapeKey: function(e) {
            const modal = document.querySelector('.hd-modal:not(.hd-modal--hidden)');
            if (modal) {
                const closeButton = modal.querySelector('.hd-modal__close, [data-dismiss="modal"]');
                if (closeButton) {
                    closeButton.click();
                }
                return;
            }

            // Close dropdown menus
            const openDropdown = document.querySelector('[aria-expanded="true"]');
            if (openDropdown) {
                openDropdown.click();
                openDropdown.focus();
                return;
            }

            // Clear search inputs
            const searchInput = document.activeElement;
            if (searchInput && searchInput.matches('input[type="search"], .hd-search-input')) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            }
        },

        // ARIA state management
        setupAriaManagement: function() {
            // Auto-manage aria-expanded for dropdowns
            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-toggle="dropdown"], [aria-haspopup]');
                if (trigger) {
                    const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                    trigger.setAttribute('aria-expanded', !isExpanded);
                    
                    const targetId = trigger.getAttribute('aria-controls') || trigger.getAttribute('data-target');
                    if (targetId) {
                        const target = document.getElementById(targetId);
                        if (target) {
                            target.setAttribute('aria-hidden', isExpanded);
                        }
                    }
                }
            });

            // Auto-manage form validation states
            document.addEventListener('input', (e) => {
                if (e.target.matches('.hd-input, input, textarea, select')) {
                    this.updateFieldValidation(e.target);
                }
            });

            // Auto-manage loading states
            document.addEventListener('submit', (e) => {
                if (e.target.matches('form')) {
                    this.announceStatus('Form submitted, processing...');
                    
                    const submitButton = e.target.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitButton) {
                        submitButton.setAttribute('aria-busy', 'true');
                        submitButton.setAttribute('aria-disabled', 'true');
                    }
                }
            });
        },

        // Update field validation states
        updateFieldValidation: function(field) {
            const isValid = field.checkValidity();
            const errorMessage = field.validationMessage;
            
            field.setAttribute('aria-invalid', !isValid);
            
            // Find or create error message element
            let errorElement = document.getElementById(field.id + '-error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = field.id + '-error';
                errorElement.className = 'hd-error-message';
                errorElement.setAttribute('role', 'alert');
                field.parentNode.appendChild(errorElement);
            }
            
            if (!isValid && errorMessage) {
                errorElement.textContent = errorMessage;
                field.setAttribute('aria-describedby', errorElement.id);
            } else {
                errorElement.textContent = '';
                field.removeAttribute('aria-describedby');
            }
        },

        // Error handling with announcements
        setupErrorHandling: function() {
            // Global error handler
            window.addEventListener('error', (e) => {
                this.announce('An error occurred. Please try again or contact support.', 'assertive');
            });

            // Network error handling
            window.addEventListener('offline', () => {
                this.announce('You are now offline. Some features may not work.', 'assertive');
            });

            window.addEventListener('online', () => {
                this.announce('Connection restored.', 'polite');
            });

            // Form error handling
            document.addEventListener('invalid', (e) => {
                e.preventDefault();
                const field = e.target;
                const message = field.validationMessage || 'Please correct this field.';
                
                this.focusElement(field);
                this.announce(`Error in ${field.labels?.[0]?.textContent || field.name || 'form field'}: ${message}`, 'assertive');
            });
        },

        // Debug mode for accessibility testing
        setupDebugMode: function() {
            // Check for debug parameters
            const urlParams = new URLSearchParams(window.location.search);
            const debug = urlParams.get('debug');

            if (debug === 'a11y' || debug === 'accessibility') {
                document.body.setAttribute('data-debug', 'accessibility');
                this.enableAccessibilityDebug();
            }

            if (debug === 'headings') {
                document.body.setAttribute('data-debug', 'headings');
            }

            if (debug === 'tabindex') {
                document.body.setAttribute('data-debug', 'tabindex');
            }

            // Keyboard shortcut to toggle debug mode (Ctrl+Shift+A)
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.shiftKey && e.key === 'A') {
                    e.preventDefault();
                    this.toggleAccessibilityDebug();
                }
            });
        },

        // Enable accessibility debug mode
        enableAccessibilityDebug: function() {
            console.log('🔍 Accessibility Debug Mode Enabled');
            
            // Highlight focusable elements
            const style = document.createElement('style');
            style.textContent = `
                [tabindex], button, input, select, textarea, a[href] {
                    outline: 2px dashed #ff6b6b !important;
                    outline-offset: 2px !important;
                }
                [aria-label], [aria-labelledby], [aria-describedby] {
                    background: rgba(255, 107, 107, 0.1) !important;
                }
                [role] {
                    border: 1px dotted #4ecdc4 !important;
                }
            `;
            document.head.appendChild(style);
            
            this.announce('Accessibility debug mode enabled', 'polite');
        },

        // Toggle accessibility debug mode
        toggleAccessibilityDebug: function() {
            const isEnabled = document.body.hasAttribute('data-debug');
            
            if (isEnabled) {
                document.body.removeAttribute('data-debug');
                this.announce('Accessibility debug mode disabled', 'polite');
            } else {
                this.enableAccessibilityDebug();
            }
        },

        // Initialize additional accessibility features
        initializeA11yFeatures: function() {
            // Add role attributes where missing
            this.enhanceSemantics();
            
            // Setup custom controls
            this.setupCustomControls();
            
            // Initialize toast notifications
            this.setupToastNotifications();
        },

        // Enhance semantic markup
        enhanceSemantics: function() {
            // Add main landmark if missing
            const main = document.querySelector('main');
            if (main && !main.hasAttribute('role')) {
                main.setAttribute('role', 'main');
            }

            // Enhance navigation landmarks
            const navs = document.querySelectorAll('nav');
            navs.forEach((nav, index) => {
                if (!nav.hasAttribute('role')) {
                    nav.setAttribute('role', 'navigation');
                }
                if (!nav.hasAttribute('aria-label')) {
                    nav.setAttribute('aria-label', index === 0 ? 'Primary navigation' : `Navigation ${index + 1}`);
                }
            });

            // Enhance buttons without proper labels
            const buttons = document.querySelectorAll('button, [role="button"]');
            buttons.forEach(button => {
                if (!button.textContent.trim() && !button.getAttribute('aria-label') && !button.getAttribute('aria-labelledby')) {
                    const icon = button.querySelector('i[class*="fa-"], svg');
                    if (icon) {
                        button.setAttribute('aria-label', 'Button');
                        console.warn('Button without accessible label:', button);
                    }
                }
            });
        },

        // Setup custom controls
        setupCustomControls: function() {
            // Custom dropdown controls
            const dropdowns = document.querySelectorAll('[data-dropdown]');
            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('[data-dropdown-trigger]');
                const menu = dropdown.querySelector('[data-dropdown-menu]');
                
                if (trigger && menu) {
                    trigger.setAttribute('aria-haspopup', 'true');
                    trigger.setAttribute('aria-expanded', 'false');
                    menu.setAttribute('role', 'menu');
                    
                    const items = menu.querySelectorAll('a, button');
                    items.forEach(item => {
                        item.setAttribute('role', 'menuitem');
                    });
                }
            });
        },

        // Setup toast notifications with proper announcements
        setupToastNotifications: function() {
            // Observe for new toast elements
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            const toasts = node.matches?.('.toast, .notification, .alert') 
                                ? [node] 
                                : node.querySelectorAll?.('.toast, .notification, .alert') || [];
                            
                            toasts.forEach(toast => {
                                const message = toast.textContent.trim();
                                if (message) {
                                    const priority = toast.classList.contains('error') || 
                                                   toast.classList.contains('danger') ? 'assertive' : 'polite';
                                    this.announce(message, priority);
                                }
                            });
                        }
                    });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }
    };

    // Utility functions for common accessibility tasks
    const A11yHelpers = {
        // Show loading state with announcement
        showLoading: function(element, message = 'Loading...') {
            if (element) {
                element.setAttribute('aria-busy', 'true');
                element.setAttribute('aria-live', 'polite');
                AccessibilityUtils.announce(message);
            }
        },

        // Hide loading state
        hideLoading: function(element, successMessage = null) {
            if (element) {
                element.removeAttribute('aria-busy');
                element.removeAttribute('aria-live');
                
                if (successMessage) {
                    AccessibilityUtils.announce(successMessage);
                }
            }
        },

        // Create accessible tooltip
        createTooltip: function(trigger, content) {
            const tooltipId = 'tooltip-' + Date.now();
            const tooltip = document.createElement('div');
            tooltip.id = tooltipId;
            tooltip.className = 'hd-tooltip';
            tooltip.setAttribute('role', 'tooltip');
            tooltip.textContent = content;
            
            document.body.appendChild(tooltip);
            
            trigger.setAttribute('aria-describedby', tooltipId);
            
            return tooltip;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            AccessibilityUtils.init();
        });
    } else {
        AccessibilityUtils.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.AccessibilityUtils = AccessibilityUtils;
    window.HDTickets.A11yHelpers = A11yHelpers;

})(window, document);
