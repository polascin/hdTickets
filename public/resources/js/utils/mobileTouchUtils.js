// HD Tickets - Mobile Touch Utilities
// ===================================

/**
 * Mobile Touch Utilities for HD Tickets
 * Handles touch interactions, gestures, and mobile-specific enhancements
 */

class MobileTouchUtils {
    constructor() {
        this.isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        this.isAndroid = /Android/.test(navigator.userAgent);
        this.viewportHeight = window.innerHeight;
        
        this.init();
    }

    init() {
        this.setupViewportFix();
        this.setupTouchTargets();
        this.setupSwipeGestures();
        this.setupIOSFixes();
        this.setupAndroidFixes();
        this.setupHapticFeedback();
        this.setupPullToRefresh();
        
        console.log('📱 Mobile Touch Utils initialized');
    }

    /**
     * Fix viewport height issues on mobile browsers
     */
    setupViewportFix() {
        // Set CSS custom property for real viewport height
        const setVH = () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        };

        setVH();
        
        // Update on resize and orientation change
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', () => {
            setTimeout(setVH, 500); // Delay for orientation change
        });

        // Prevent viewport zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (event) => {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    }

    /**
     * Enhance touch targets with proper feedback
     */
    setupTouchTargets() {
        // Add touch feedback to all touch targets
        document.addEventListener('touchstart', (e) => {
            if (e.target.closest('.touch-target')) {
                e.target.closest('.touch-target').classList.add('touch-active');
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            setTimeout(() => {
                document.querySelectorAll('.touch-active').forEach(el => {
                    el.classList.remove('touch-active');
                });
            }, 150);
        }, { passive: true });

        // Improve tap targets for small elements
        this.improveTouchTargets();
    }

    /**
     * Improve touch targets for elements that are too small
     */
    improveTouchTargets() {
        const minTouchTarget = 44; // 44px minimum touch target
        
        // Find elements that are too small and add padding
        document.querySelectorAll('button, a, input[type="checkbox"], input[type="radio"], .clickable').forEach(el => {
            const rect = el.getBoundingClientRect();
            
            if (rect.width < minTouchTarget || rect.height < minTouchTarget) {
                el.classList.add('touch-target-enhanced');
                
                // Calculate needed padding
                const widthDiff = Math.max(0, minTouchTarget - rect.width);
                const heightDiff = Math.max(0, minTouchTarget - rect.height);
                
                el.style.padding = `${heightDiff/2}px ${widthDiff/2}px`;
            }
        });
    }

    /**
     * Setup swipe gesture recognition
     */
    setupSwipeGestures() {
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;

        document.addEventListener('touchstart', (e) => {
            if (e.target.closest('.swipe-container, .swipe-actions-container')) {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            const container = e.target.closest('.swipe-container, .swipe-actions-container');
            if (container) {
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                this.handleSwipe(container, touchStartX, touchStartY, touchEndX, touchEndY);
            }
        }, { passive: true });
    }

    /**
     * Handle swipe gestures
     */
    handleSwipe(element, startX, startY, endX, endY) {
        const diffX = startX - endX;
        const diffY = startY - endY;
        const threshold = 50;

        // Determine if it's a horizontal or vertical swipe
        if (Math.abs(diffX) > Math.abs(diffY)) {
            // Horizontal swipe
            if (Math.abs(diffX) > threshold) {
                if (diffX > 0) {
                    // Swipe left
                    this.triggerSwipeEvent(element, 'swipe-left');
                } else {
                    // Swipe right
                    this.triggerSwipeEvent(element, 'swipe-right');
                }
            }
        } else {
            // Vertical swipe
            if (Math.abs(diffY) > threshold) {
                if (diffY > 0) {
                    // Swipe up
                    this.triggerSwipeEvent(element, 'swipe-up');
                } else {
                    // Swipe down
                    this.triggerSwipeEvent(element, 'swipe-down');
                }
            }
        }
    }

    /**
     * Trigger custom swipe events
     */
    triggerSwipeEvent(element, direction) {
        const event = new CustomEvent(direction, {
            bubbles: true,
            detail: { element }
        });
        element.dispatchEvent(event);
    }

    /**
     * iOS-specific fixes
     */
    setupIOSFixes() {
        if (!this.isIOS) return;

        // Prevent bounce scrolling
        document.body.addEventListener('touchmove', (e) => {
            if (e.target === document.body) {
                e.preventDefault();
            }
        }, { passive: false });

        // Fix form input zoom on focus
        document.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.style.fontSize !== '16px') {
                input.style.fontSize = '16px';
            }
        });

        // Handle safe area insets
        this.handleSafeArea();
    }

    /**
     * Android-specific fixes
     */
    setupAndroidFixes() {
        if (!this.isAndroid) return;

        // Handle keyboard open/close
        let initialViewportHeight = window.innerHeight;
        
        window.addEventListener('resize', () => {
            if (window.innerHeight < initialViewportHeight * 0.75) {
                document.body.classList.add('keyboard-open');
            } else {
                document.body.classList.remove('keyboard-open');
            }
        });
    }

    /**
     * Handle iOS safe area insets
     */
    handleSafeArea() {
        // Apply safe area classes
        document.querySelectorAll('.safe-top, .safe-bottom, .safe-left, .safe-right').forEach(el => {
            const classes = el.classList;
            
            if (classes.contains('safe-top')) {
                el.style.paddingTop = `max(${getComputedStyle(el).paddingTop}, env(safe-area-inset-top))`;
            }
            if (classes.contains('safe-bottom')) {
                el.style.paddingBottom = `max(${getComputedStyle(el).paddingBottom}, env(safe-area-inset-bottom))`;
            }
            if (classes.contains('safe-left')) {
                el.style.paddingLeft = `max(${getComputedStyle(el).paddingLeft}, env(safe-area-inset-left))`;
            }
            if (classes.contains('safe-right')) {
                el.style.paddingRight = `max(${getComputedStyle(el).paddingRight}, env(safe-area-inset-right))`;
            }
        });
    }

    /**
     * Setup haptic feedback for supported devices
     */
    setupHapticFeedback() {
        // Check if device supports haptic feedback
        this.supportsHaptic = 'vibrate' in navigator;
        
        // Add haptic feedback to buttons
        document.addEventListener('touchstart', (e) => {
            const element = e.target;
            if (element.matches('button, .btn, .touch-target') && this.supportsHaptic) {
                this.triggerHaptic('light');
            }
        }, { passive: true });
    }

    /**
     * Trigger haptic feedback
     */
    triggerHaptic(type = 'light') {
        if (!this.supportsHaptic) return;

        const patterns = {
            light: [10],
            medium: [20],
            heavy: [50],
            success: [10, 100, 10],
            error: [100, 50, 100],
            selection: [5]
        };

        if (patterns[type]) {
            navigator.vibrate(patterns[type]);
        }
    }

    /**
     * Setup pull-to-refresh functionality
     */
    setupPullToRefresh() {
        let startY = 0;
        let currentY = 0;
        let pullDistance = 0;
        let isPulling = false;

        document.addEventListener('touchstart', (e) => {
            const container = e.target.closest('.pull-to-refresh-container');
            if (container && window.scrollY === 0) {
                startY = e.touches[0].clientY;
                isPulling = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (isPulling && window.scrollY === 0) {
                currentY = e.touches[0].clientY;
                pullDistance = currentY - startY;

                if (pullDistance > 0) {
                    const container = e.target.closest('.pull-to-refresh-container');
                    const indicator = container?.querySelector('.pull-to-refresh-indicator');
                    
                    if (indicator) {
                        const maxPull = 80;
                        const adjustedDistance = Math.min(pullDistance, maxPull);
                        
                        indicator.style.transform = `translateY(${adjustedDistance}px)`;
                        
                        if (pullDistance > 60) {
                            indicator.classList.add('ready-to-refresh');
                        } else {
                            indicator.classList.remove('ready-to-refresh');
                        }
                    }
                }
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (isPulling) {
                const container = e.target.closest('.pull-to-refresh-container');
                const indicator = container?.querySelector('.pull-to-refresh-indicator');
                
                if (pullDistance > 60) {
                    this.triggerRefresh(container);
                } else if (indicator) {
                    indicator.style.transform = 'translateY(0)';
                    indicator.classList.remove('ready-to-refresh');
                }
                
                isPulling = false;
                pullDistance = 0;
            }
        }, { passive: true });
    }

    /**
     * Trigger refresh action
     */
    triggerRefresh(container) {
        const event = new CustomEvent('pull-to-refresh', {
            bubbles: true,
            detail: { container }
        });
        container.dispatchEvent(event);
        
        // Reset indicator after animation
        setTimeout(() => {
            const indicator = container.querySelector('.pull-to-refresh-indicator');
            if (indicator) {
                indicator.style.transform = 'translateY(0)';
                indicator.classList.remove('ready-to-refresh', 'refreshing');
            }
        }, 1000);
    }

    /**
     * Handle mobile navigation menu
     */
    setupMobileNavigation() {
        const hamburger = document.querySelector('.hd-mobile-hamburger');
        const overlay = document.querySelector('.hd-mobile-nav-overlay');
        const drawer = document.querySelector('.hd-mobile-nav-drawer');

        if (hamburger && overlay && drawer) {
            hamburger.addEventListener('click', () => {
                this.toggleMobileNav();
            });

            overlay.addEventListener('click', () => {
                this.closeMobileNav();
            });

            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && drawer.classList.contains('hd-mobile-nav-drawer--open')) {
                    this.closeMobileNav();
                }
            });
        }
    }

    /**
     * Toggle mobile navigation
     */
    toggleMobileNav() {
        const hamburger = document.querySelector('.hd-mobile-hamburger');
        const overlay = document.querySelector('.hd-mobile-nav-overlay');
        const drawer = document.querySelector('.hd-mobile-nav-drawer');

        if (drawer.classList.contains('hd-mobile-nav-drawer--open')) {
            this.closeMobileNav();
        } else {
            this.openMobileNav();
        }
    }

    /**
     * Open mobile navigation
     */
    openMobileNav() {
        const hamburger = document.querySelector('.hd-mobile-hamburger');
        const overlay = document.querySelector('.hd-mobile-nav-overlay');
        const drawer = document.querySelector('.hd-mobile-nav-drawer');

        hamburger?.classList.add('hd-mobile-hamburger--open');
        overlay?.classList.add('hd-mobile-nav-overlay--active');
        drawer?.classList.add('hd-mobile-nav-drawer--open');
        
        document.body.style.overflow = 'hidden';
        this.triggerHaptic('light');
    }

    /**
     * Close mobile navigation
     */
    closeMobileNav() {
        const hamburger = document.querySelector('.hd-mobile-hamburger');
        const overlay = document.querySelector('.hd-mobile-nav-overlay');
        const drawer = document.querySelector('.hd-mobile-nav-drawer');

        hamburger?.classList.remove('hd-mobile-hamburger--open');
        overlay?.classList.remove('hd-mobile-nav-overlay--active');
        drawer?.classList.remove('hd-mobile-nav-drawer--open');
        
        document.body.style.overflow = '';
        this.triggerHaptic('light');
    }

    /**
     * Add touch ripple effect to elements
     */
    addTouchRipple(element) {
        element.addEventListener('touchstart', (e) => {
            const ripple = document.createElement('span');
            const rect = element.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.touches[0].clientX - rect.left - size / 2;
            const y = e.touches[0].clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            element.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        }, { passive: true });
    }

    /**
     * Get device type
     */
    getDeviceType() {
        const width = window.innerWidth;
        
        if (width <= 480) return 'mobile-small';
        if (width <= 768) return 'mobile';
        if (width <= 1024) return 'tablet';
        return 'desktop';
    }

    /**
     * Check if device is in portrait mode
     */
    isPortrait() {
        return window.innerHeight > window.innerWidth;
    }

    /**
     * Get orientation
     */
    getOrientation() {
        return this.isPortrait() ? 'portrait' : 'landscape';
    }

    /**
     * Smooth scroll to element with error handling
     */
    smoothScrollToElement(element, offset = 0) {
        if (!element) return;
        
        const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - offset;
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;
        
        const animation = (currentTime) => {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / 500, 1); // 500ms duration
            
            window.scrollTo(0, startPosition + (distance * this.easeInOutQuad(progress)));
            
            if (timeElapsed < 500) {
                requestAnimationFrame(animation);
            }
        };
        
        requestAnimationFrame(animation);
    }
    
    /**
     * Easing function for smooth animations
     */
    easeInOutQuad(t) {
        return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    }
    
    /**
     * Scroll to form errors with haptic feedback
     */
    scrollToFormErrors() {
        const firstError = document.querySelector('.is-invalid, .error, .mobile-form-error, [aria-invalid="true"]');
        if (firstError) {
            this.smoothScrollToElement(firstError, 60); // 60px offset for mobile header
            this.triggerHaptic('error');
            
            // Focus the error element after scrolling
            setTimeout(() => {
                firstError.focus();
            }, 500);
        }
    }
    
    /**
     * Enhanced form validation with mobile optimization
     */
    enhanceFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                // Check for validation errors
                const errors = form.querySelectorAll('.is-invalid, .error, [aria-invalid="true"]');
                
                if (errors.length > 0) {
                    e.preventDefault();
                    this.scrollToFormErrors();
                    return false;
                }
            });
            
            // Real-time validation feedback
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateInput(input);
                });
                
                input.addEventListener('input', () => {
                    // Clear error state on input
                    input.classList.remove('is-invalid', 'error');
                    input.setAttribute('aria-invalid', 'false');
                });
            });
        });
    }
    
    /**
     * Validate individual input with haptic feedback
     */
    validateInput(input) {
        const isValid = input.checkValidity();
        
        if (!isValid) {
            input.classList.add('is-invalid', 'error');
            input.setAttribute('aria-invalid', 'true');
            this.triggerHaptic('error');
        } else {
            input.classList.remove('is-invalid', 'error');
            input.classList.add('is-valid', 'success');
            input.setAttribute('aria-invalid', 'false');
            this.triggerHaptic('success');
        }
    }
    
    /**
     * Connection status monitoring
     */
    setupConnectionMonitoring() {
        const updateConnectionStatus = () => {
            const body = document.body;
            
            if (navigator.onLine) {
                body.classList.remove('offline');
                body.classList.add('online');
                
                // Show online notification
                this.showMobileNotification('Back online', 'success');
            } else {
                body.classList.remove('online');
                body.classList.add('offline');
                
                // Show offline notification
                this.showMobileNotification('You are offline', 'warning');
                this.triggerHaptic('error');
            }
        };
        
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Initial check
        updateConnectionStatus();
    }
    
    /**
     * Show mobile-optimized notifications
     */
    showMobileNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `mobile-notification ${type}`;
        notification.innerHTML = `
            <div class="mobile-notification-content">
                <div class="mobile-notification-message">${message}</div>
            </div>
            <button class="mobile-notification-close" aria-label="Close notification">
                ×
            </button>
        `;
        
        // Add to notifications container or create one
        let container = document.querySelector('.mobile-notifications');
        if (!container) {
            container = document.createElement('div');
            container.className = 'mobile-notifications';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Show notification with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto-remove notification
        setTimeout(() => {
            this.removeMobileNotification(notification);
        }, duration);
        
        // Close button functionality
        const closeBtn = notification.querySelector('.mobile-notification-close');
        closeBtn.addEventListener('click', () => {
            this.removeMobileNotification(notification);
        });
    }
    
    /**
     * Remove mobile notification with animation
     */
    removeMobileNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    /**
     * Setup progressive enhancement features
     */
    setupProgressiveEnhancement() {
        // Add 'js-enabled' class for CSS targeting
        document.documentElement.classList.add('js-enabled');
        
        // Add touch/no-touch classes
        if (this.isTouch) {
            document.documentElement.classList.add('touch');
        } else {
            document.documentElement.classList.add('no-touch');
        }
        
        // Add device type classes
        document.documentElement.classList.add(this.getDeviceType());
        
        // Add orientation class
        document.documentElement.classList.add(this.getOrientation());
        
        // Update orientation on change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                document.documentElement.classList.remove('portrait', 'landscape');
                document.documentElement.classList.add(this.getOrientation());
            }, 100);
        });
    }
    
    /**
     * Optimize images for mobile
     */
    optimizeImagesForMobile() {
        const images = document.querySelectorAll('img');
        
        images.forEach(img => {
            // Add loading="lazy" for performance
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Add mobile-optimized classes
            img.classList.add('img-responsive');
            
            // Handle image load errors
            img.addEventListener('error', () => {
                img.classList.add('img-error');
                console.warn('Failed to load image:', img.src);
            });
        });
    }
    
    /**
     * Setup mobile-specific keyboard handling
     */
    setupMobileKeyboard() {
        let initialViewportHeight = window.innerHeight;
        
        // Detect virtual keyboard
        window.addEventListener('resize', () => {
            const currentHeight = window.innerHeight;
            
            if (currentHeight < initialViewportHeight * 0.75) {
                // Virtual keyboard is likely open
                document.body.classList.add('keyboard-visible');
                document.body.classList.remove('keyboard-hidden');
                
                // Scroll active element into view
                const activeElement = document.activeElement;
                if (activeElement && activeElement.tagName && 
                    ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName)) {
                    setTimeout(() => {
                        this.smoothScrollToElement(activeElement, 60);
                    }, 300);
                }
            } else {
                // Virtual keyboard is likely closed
                document.body.classList.add('keyboard-hidden');
                document.body.classList.remove('keyboard-visible');
            }
        });
        
        // Handle input focus for better mobile experience
        document.addEventListener('focusin', (e) => {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                e.target.classList.add('input-focused');
                
                // Scroll to focused element on mobile
                if (this.isMobile) {
                    setTimeout(() => {
                        this.smoothScrollToElement(e.target, 60);
                    }, 300);
                }
            }
        });
        
        document.addEventListener('focusout', (e) => {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                e.target.classList.remove('input-focused');
            }
        });
    }
    
    /**
     * Enhanced initialization with all mobile optimizations
     */
    initializeAllFeatures() {
        this.init();
        this.setupProgressiveEnhancement();
        this.enhanceFormValidation();
        this.setupConnectionMonitoring();
        this.optimizeImagesForMobile();
        this.setupMobileKeyboard();
        
        // Performance optimization: defer non-critical enhancements
        setTimeout(() => {
            this.setupMobileNavigation();
        }, 100);
        
        console.log('📱 All mobile enhancements initialized successfully');
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.mobileTouchUtils = new MobileTouchUtils();
    });
} else {
    window.mobileTouchUtils = new MobileTouchUtils();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileTouchUtils;
}
