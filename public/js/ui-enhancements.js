/**
 * HD Tickets UI Enhancement Scripts
 * Provides improved interactivity and user experience enhancements
 */

class UIEnhancements {
    constructor() {
        this.init();
    }

    init() {
        this.setupLoadingStates();
        this.setupFormEnhancements();
        this.setupButtonHoverEffects();
        this.setupScrollEffects();
        this.setupKeyboardNavigation();
        this.setupTooltips();
        this.setupAnimations();
        
        console.log('HD Tickets UI Enhancements loaded');
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
        element.style.opacity = '0.7';
        element.style.pointerEvents = 'none';
        
        // Add loading spinner if it's a button
        if (element.tagName === 'BUTTON') {
            const spinner = document.createElement('span');
            spinner.className = 'loading-spinner inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2';
            element.prepend(spinner);
        }
        
        element.classList.add('loading');
    }

    removeLoadingState(element) {
        const spinner = element.querySelector('.loading-spinner');
        if (spinner) {
            spinner.remove();
        }
        
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
        element.classList.remove('loading');
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
     * Enhanced button hover effects
     */
    setupButtonHoverEffects() {
        const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-danger, button, .dashboard-card');
        
        buttons.forEach(button => {
            button.addEventListener('mouseenter', () => {
                if (!button.disabled && !button.classList.contains('loading')) {
                    button.style.transform = 'translateY(-1px)';
                    button.style.transition = 'all 0.2s ease';
                }
            });
            
            button.addEventListener('mouseleave', () => {
                button.style.transform = 'translateY(0)';
            });
        });
    }

    /**
     * Scroll-based animations and effects
     */
    setupScrollEffects() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            // Observe elements that should fade in
            document.querySelectorAll('.dashboard-card, .stat-card, .feature-card').forEach(el => {
                el.classList.add('fade-in-prepare');
                observer.observe(el);
            });
        }
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
     * CSS animations
     */
    setupAnimations() {
        // Add CSS for fade-in animations
        const style = document.createElement('style');
        style.textContent = `
            .fade-in-prepare {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.6s ease;
            }
            
            .fade-in {
                opacity: 1;
                transform: translateY(0);
            }
            
            .keyboard-navigation *:focus {
                outline: 2px solid #3b82f6 !important;
                outline-offset: 2px;
            }
            
            .tooltip.show {
                opacity: 1;
            }
            
            .loading-spinner {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            @media (prefers-reduced-motion: reduce) {
                .fade-in-prepare,
                .fade-in,
                .loading-spinner,
                * {
                    animation: none !important;
                    transition: none !important;
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
