/**
 * HD Tickets UI Enhancement Scripts
 * Provides improved interactivity and user experience enhancements
 */

class UIEnhancements {
    constructor() {
        this.animationSupport = {
            intersectionObserver: 'IntersectionObserver' in window,
            requestAnimationFrame: 'requestAnimationFrame' in window,
            cssTransitions: this.checkCSSTransitionSupport(),
            reducedMotion: this.checkReducedMotionPreference()
        };
        
        this.init();
    }

    init() {
        try {
            this.setupLoadingStates();
            this.setupFormEnhancements();
            this.setupButtonHoverEffects();
            this.setupScrollEffects();
            this.setupKeyboardNavigation();
            this.setupTooltips();
            this.setupAnimations();
            
            console.log('HD Tickets UI Enhancements loaded successfully');
        } catch (error) {
            console.error('Error initializing UI enhancements:', error);
            this.initFallbacks();
        }
    }

    checkCSSTransitionSupport() {
        const el = document.createElement('div');
        const transitions = {
            'transition': 'transitionend',
            'OTransition': 'oTransitionEnd',
            'MozTransition': 'transitionend',
            'WebkitTransition': 'webkitTransitionEnd'
        };
        
        for (let t in transitions) {
            if (el.style[t] !== undefined) {
                return true;
            }
        }
        return false;
    }

    checkReducedMotionPreference() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    initFallbacks() {
        console.log('Initializing fallback UI enhancements');
        // Basic functionality without animations
        this.setupBasicInteractions();
    }

    setupBasicInteractions() {
        // Minimal functionality for unsupported browsers
        const interactiveElements = document.querySelectorAll('.btn-primary, .btn-secondary, button');
        interactiveElements.forEach(element => {
            element.addEventListener('click', () => {
                element.style.opacity = '0.8';
                setTimeout(() => {
                    element.style.opacity = '1';
                }, 150);
            });
        });
    }

    /**
     * Add loading states to buttons and links
     */
    setupLoadingStates() {
        const buttons = document.querySelectorAll('button[type="submit"], .btn-primary, .btn-secondary');
        const links = document.querySelectorAll('a[href]:not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])');

        // Add loading state to form submit buttons
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                if (!button.disabled) {
                    this.addLoadingState(button);
                    
                    // Auto-remove loading state after 3 seconds as fallback
                    setTimeout(() => {
                        this.removeLoadingState(button);
                    }, 3000);
                }
            });
        });

        // Add loading state to navigation links
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                if (!link.classList.contains('no-loading')) {
                    this.addLoadingState(link);
                }
            });
        });
    }

    addLoadingState(element) {
        const originalText = element.textContent.trim();
        element.dataset.originalText = originalText;
        element.style.transition = 'opacity 0.3s ease-in-out, transform 0.3s ease-in-out';
        element.style.opacity = '0.7';
        element.style.pointerEvents = 'none';
        element.style.transform = 'scale(0.98)';
        
        // Add loading spinner if it's a button
        if (element.tagName === 'BUTTON') {
            const spinner = document.createElement('span');
            spinner.className = 'loading-spinner inline-block w-4 h-4 mr-2';
            spinner.innerHTML = `
                <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                        <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416;0 31.416" repeatCount="indefinite"/>
                        <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416;-31.416" repeatCount="indefinite"/>
                    </circle>
                </svg>
            `;
            element.prepend(spinner);
        }
        
        element.classList.add('loading');
        element.setAttribute('aria-busy', 'true');
        
        // Add ARIA label for accessibility
        const originalAriaLabel = element.getAttribute('aria-label') || element.textContent;
        element.setAttribute('data-original-aria-label', originalAriaLabel);
        element.setAttribute('aria-label', `Loading ${originalAriaLabel}`);
    }

    removeLoadingState(element) {
        const spinner = element.querySelector('.loading-spinner');
        if (spinner) {
            // Fade out spinner before removing
            spinner.style.opacity = '0';
            spinner.style.transform = 'scale(0)';
            setTimeout(() => {
                if (spinner.parentNode) {
                    spinner.remove();
                }
            }, 200);
        }
        
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
        element.style.transform = 'scale(1)';
        element.classList.remove('loading');
        element.removeAttribute('aria-busy');
        
        // Restore original aria-label
        const originalAriaLabel = element.getAttribute('data-original-aria-label');
        if (originalAriaLabel) {
            element.setAttribute('aria-label', originalAriaLabel);
            element.removeAttribute('data-original-aria-label');
        }
    }

    /**
     * Enhance form interactions
     */
    setupFormEnhancements() {
        // Auto-focus first input on forms
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const firstInput = form.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput && !firstInput.hasAttribute('autofocus')) {
                setTimeout(() => {
                    if (window.innerWidth > 768) { // Only on desktop
                        firstInput.focus();
                    }
                }, 100);
            }
        });

        // Real-time validation feedback
        const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });

            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateInput(input);
                }
            });
        });

        // Enhanced password strength indicator
        const passwordFields = document.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            this.addPasswordStrengthIndicator(field);
        });
    }

    validateInput(input) {
        const isValid = input.checkValidity();
        const errorElement = document.getElementById(input.getAttribute('aria-describedby'));
        
        if (isValid) {
            input.classList.remove('error', 'border-red-500');
            input.classList.add('valid', 'border-green-500');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        } else {
            input.classList.remove('valid', 'border-green-500');
            input.classList.add('error', 'border-red-500');
            if (errorElement) {
                errorElement.style.display = 'block';
            }
        }
    }

    addPasswordStrengthIndicator(passwordField) {
        if (passwordField.name !== 'password' || passwordField.dataset.strengthAdded) return;
        
        passwordField.dataset.strengthAdded = 'true';
        
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength mt-1 hidden';
        strengthIndicator.innerHTML = `
            <div class="flex space-x-1 mb-1">
                <div class="strength-bar h-2 flex-1 bg-gray-200 rounded"></div>
                <div class="strength-bar h-2 flex-1 bg-gray-200 rounded"></div>
                <div class="strength-bar h-2 flex-1 bg-gray-200 rounded"></div>
                <div class="strength-bar h-2 flex-1 bg-gray-200 rounded"></div>
            </div>
            <div class="strength-text text-xs text-gray-500"></div>
        `;
        
        passwordField.parentNode.insertBefore(strengthIndicator, passwordField.nextSibling);
        
        passwordField.addEventListener('input', () => {
            this.updatePasswordStrength(passwordField, strengthIndicator);
        });
    }

    updatePasswordStrength(field, indicator) {
        const password = field.value;
        const strength = this.calculatePasswordStrength(password);
        const bars = indicator.querySelectorAll('.strength-bar');
        const textElement = indicator.querySelector('.strength-text');
        
        if (password.length === 0) {
            indicator.classList.add('hidden');
            return;
        }
        
        indicator.classList.remove('hidden');
        
        // Reset bars
        bars.forEach(bar => {
            bar.className = 'strength-bar h-2 flex-1 bg-gray-200 rounded';
        });
        
        // Update bars based on strength
        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        
        for (let i = 0; i < strength; i++) {
            bars[i].classList.remove('bg-gray-200');
            bars[i].classList.add(colors[strength - 1]);
        }
        
        textElement.textContent = labels[strength - 1] || '';
        textElement.className = `strength-text text-xs ${strength < 2 ? 'text-red-600' : strength < 3 ? 'text-yellow-600' : 'text-green-600'}`;
    }

    calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        return Math.min(4, Math.max(1, strength - 1));
    }

    /**
     * Enhanced button hover effects with modern interactions
     */
    setupButtonHoverEffects() {
        const interactiveElements = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-danger, button, .dashboard-card, .ticket-card, .modern-card');
        
        interactiveElements.forEach(element => {
            element.style.position = 'relative';
            element.style.overflow = 'hidden';

            element.addEventListener('mouseenter', (e) => {
                if (!element.disabled && !element.classList.contains('loading')) {
                    const isCard = element.classList.contains('dashboard-card') || 
                                  element.classList.contains('ticket-card') || 
                                  element.classList.contains('modern-card');
                    
                    if (isCard) {
                        element.style.transform = 'translateY(-4px) scale(1.02)';
                        element.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        element.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
                    } else {
                        element.style.transform = 'translateY(-2px)';
                        element.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)';
                    }
                }
            });
            
            element.addEventListener('mouseleave', () => {
                element.style.transform = 'translateY(0) scale(1)';
                element.style.boxShadow = '';
            });

            element.addEventListener('click', (e) => {
                if (!element.disabled && !element.classList.contains('loading')) {
                    this.createRippleEffect(e, element);
                }
            });
        });
    }

    createRippleEffect(event, element) {
        const circle = document.createElement('span');
        const diameter = Math.max(element.clientWidth, element.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        const rect = element.getBoundingClientRect();
        circle.style.left = `${event.clientX - rect.left - radius}px`;
        circle.style.top = `${event.clientY - rect.top - radius}px`;
        circle.classList.add('ripple');

        const existingRipple = element.querySelector('.ripple');
        if (existingRipple) {
            existingRipple.remove();
        }

        element.appendChild(circle);

        setTimeout(() => {
            circle.remove();
        }, 600);
    }

    /**
     * Scroll-based animations and effects
     */
    setupScrollEffects() {
        try {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        // Check if smooth scroll is supported
                        if ('scrollBehavior' in document.documentElement.style) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        } else {
                            // Fallback for browsers without smooth scroll support
                            target.scrollIntoView();
                        }
                    }
                });
            });

            // Setup fade-in animations based on browser support
            this.setupFadeInAnimations();
        } catch (error) {
            console.error('Error setting up scroll effects:', error);
            this.setupFallbackScrollEffects();
        }
    }

    setupFadeInAnimations() {
        const animatableElements = document.querySelectorAll('.dashboard-card, .stat-card, .feature-card, .ticket-card');
        
        // Skip if reduced motion is preferred
        if (this.animationSupport.reducedMotion) {
            animatableElements.forEach(el => {
                el.classList.add('fade-in-visible');
            });
            return;
        }

        // Use Intersection Observer if available
        if (this.animationSupport.intersectionObserver) {
            try {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const delay = Math.min((parseInt(entry.target.dataset.staggerIndex || '0')) * 100, 1000);
                            
                            if (this.animationSupport.requestAnimationFrame) {
                                requestAnimationFrame(() => {
                                    setTimeout(() => {
                                        entry.target.classList.add('fade-in-visible');
                                    }, delay);
                                });
                            } else {
                                setTimeout(() => {
                                    entry.target.classList.add('fade-in-visible');
                                }, delay);
                            }
                            
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });

                // Observe elements with staggered indices
                animatableElements.forEach((el, index) => {
                    el.classList.add('fade-in-prepare');
                    el.dataset.staggerIndex = Math.min(index, 10).toString(); // Cap at 10 for performance
                    observer.observe(el);
                });
                
            } catch (error) {
                console.error('Error with Intersection Observer:', error);
                this.setupFallbackFadeIn(animatableElements);
            }
        } else {
            this.setupFallbackFadeIn(animatableElements);
        }
    }

    setupFallbackFadeIn(elements) {
        // Fallback: Add visible class with timeout to simulate staggered animation
        elements.forEach((el, index) => {
            const delay = Math.min(index * 100, 1000);
            setTimeout(() => {
                el.classList.add('fade-in-visible');
            }, delay);
        });
    }

    setupFallbackScrollEffects() {
        // Basic scroll functionality without animations
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView();
                }
            });
        });
    }

    /**
     * Enhanced keyboard navigation
     */
    setupKeyboardNavigation() {
        // Add focus indicators
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });

        // Escape key to close modals/dropdowns
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close any open dropdowns
                document.querySelectorAll('[x-show]').forEach(el => {
                    if (el.style.display !== 'none') {
                        // Trigger Alpine.js close event
                        el.dispatchEvent(new CustomEvent('close'));
                    }
                });
            }
        });
    }

    /**
     * Simple tooltip system
     */
    setupTooltips() {
        const elements = document.querySelectorAll('[data-tooltip]');
        
        elements.forEach(element => {
            let tooltip = null;
            
            element.addEventListener('mouseenter', () => {
                const text = element.getAttribute('data-tooltip');
                tooltip = this.createTooltip(text);
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
                
                setTimeout(() => tooltip.classList.add('show'), 10);
            });
            
            element.addEventListener('mouseleave', () => {
                if (tooltip) {
                    tooltip.classList.remove('show');
                    setTimeout(() => {
                        if (tooltip.parentNode) {
                            tooltip.parentNode.removeChild(tooltip);
                        }
                    }, 200);
                }
            });
        });
    }

    createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip fixed z-50 px-2 py-1 text-xs text-white bg-gray-800 rounded shadow-lg opacity-0 transition-opacity duration-200 pointer-events-none';
        tooltip.textContent = text;
        return tooltip;
    }

    /**
     * CSS animations with modern effects
     */
    setupAnimations() {
        // Add CSS for modern animations and effects
        const style = document.createElement('style');
        style.textContent = `
            /* Modern fade-in animations with staggered effect */
            .fade-in-prepare {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
                transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .fade-in-visible {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            
            /* Legacy support for existing fade-in class */
            .fade-in {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            /* Ripple effect for button clicks */
            .ripple {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }

            /* Enhanced hover effects for cards and buttons */
            .btn-primary, .btn-secondary, .btn-danger, button {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform, box-shadow;
            }

            .dashboard-card, .ticket-card, .modern-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform, box-shadow;
            }
            
            /* Loading states with smooth transitions */
            .loading {
                transition: opacity 0.3s ease-in-out;
            }
            
            /* Keyboard navigation focus styles */
            .keyboard-navigation *:focus {
                outline: 2px solid #3b82f6 !important;
                outline-offset: 2px;
                transition: outline 0.15s ease-in-out;
            }
            
            /* Tooltip styles */
            .tooltip {
                z-index: 9999;
                backdrop-filter: blur(4px);
            }
            
            .tooltip.show {
                opacity: 1;
            }
            
            /* Loading spinner with smooth animation */
            .loading-spinner {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            /* Performance optimizations */
            .fade-in-prepare,
            .fade-in-visible,
            .ripple {
                transform: translateZ(0);
                backface-visibility: hidden;
                perspective: 1000px;
            }

            /* Dark theme compatibility for ripple effect */
            .dark .ripple,
            [data-theme="dark"] .ripple {
                background-color: rgba(255, 255, 255, 0.2);
            }

            /* Reduced motion accessibility */
            @media (prefers-reduced-motion: reduce) {
                .fade-in-prepare,
                .fade-in-visible,
                .fade-in,
                .loading-spinner,
                .ripple,
                .btn-primary,
                .btn-secondary,
                .btn-danger,
                button,
                .dashboard-card,
                .ticket-card,
                .modern-card {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
                
                .ripple {
                    display: none;
                }
            }

            /* High contrast mode support */
            @media (prefers-contrast: high) {
                .ripple {
                    background-color: currentColor;
                    opacity: 0.3;
                }
                
                .tooltip {
                    border: 1px solid currentColor;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new UIEnhancements();
    });
} else {
    new UIEnhancements();
}

// Export for module usage
window.UIEnhancements = UIEnhancements;
