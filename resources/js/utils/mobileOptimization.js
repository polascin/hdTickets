/**
 * Mobile Optimization Utilities
 * Provides mobile-specific optimizations and enhancements for the HD Tickets application
 */

class MobileOptimization {
    constructor() {
        this.isInitialized = false;
        this.touchStartY = 0;
        this.touchEndY = 0;
        this.activeSwipeHandlers = new Map();
        this.pullToRefreshEnabled = false;
        this.keyboardVisible = false;
        
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        // Wait for responsive utils to be available
        if (typeof window.responsiveUtils === 'undefined') {
            setTimeout(() => this.init(), 100);
            return;
        }

        this.setupMobileOptimizations();
        this.setupTouchHandling();
        this.setupKeyboardHandling();
        this.setupPullToRefresh();
        this.setupAccessibilityEnhancements();
        
        this.isInitialized = true;
        console.log('Mobile optimizations initialized');
    }

    /**
     * Setup mobile-specific optimizations
     */
    setupMobileOptimizations() {
        if (!window.responsiveUtils.isMobile()) return;

        // Prevent zoom on input focus (iOS Safari)
        this.preventInputZoom();
        
        // Add touch-friendly classes
        document.body.classList.add('mobile-optimized');
        
        // Optimize viewport for mobile
        this.optimizeViewport();
        
        // Setup mobile-friendly tables
        this.setupMobileTables();
        
        // Add safe area insets support
        this.setupSafeAreaInsets();
    }

    /**
     * Prevent zoom on input focus for iOS Safari
     */
    preventInputZoom() {
        const metaViewport = document.querySelector('meta[name="viewport"]');
        if (metaViewport) {
            const content = metaViewport.getAttribute('content');
            if (!content.includes('user-scalable=no')) {
                metaViewport.setAttribute('content', content + ', user-scalable=no');
            }
        }
    }

    /**
     * Optimize viewport settings for mobile
     */
    optimizeViewport() {
        // Set viewport height custom property for mobile browsers
        const setVH = () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        };
        
        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', () => {
            setTimeout(setVH, 100);
        });
    }

    /**
     * Setup mobile-friendly table layouts
     */
    setupMobileTables() {
        const tables = document.querySelectorAll('table:not(.mobile-optimized)');
        
        tables.forEach(table => {
            table.classList.add('mobile-optimized');
            
            // Add data labels for mobile card view
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            
            table.querySelectorAll('tbody tr').forEach(row => {
                Array.from(row.children).forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index]);
                    }
                });
            });
            
            // Wrap table in responsive container
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    /**
     * Setup safe area insets for devices with notches
     */
    setupSafeAreaInsets() {
        const root = document.documentElement;
        
        // Set CSS custom properties for safe areas
        const safeAreaInsets = {
            top: 'env(safe-area-inset-top, 0px)',
            right: 'env(safe-area-inset-right, 0px)',
            bottom: 'env(safe-area-inset-bottom, 0px)',
            left: 'env(safe-area-inset-left, 0px)'
        };
        
        Object.entries(safeAreaInsets).forEach(([key, value]) => {
            root.style.setProperty(`--safe-area-inset-${key}`, value);
        });
    }

    /**
     * Setup touch handling and gestures
     */
    setupTouchHandling() {
        // Add touch classes for better touch feedback
        document.addEventListener('touchstart', (e) => {
            const target = e.target.closest('button, a, [role="button"]');
            if (target) {
                target.classList.add('touch-active');
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            const target = e.target.closest('button, a, [role="button"]');
            if (target) {
                setTimeout(() => {
                    target.classList.remove('touch-active');
                }, 150);
            }
        }, { passive: true });

        // Setup swipe gestures
        this.setupSwipeGestures();
    }

    /**
     * Setup swipe gesture handling
     */
    setupSwipeGestures() {
        document.addEventListener('touchstart', (e) => {
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            this.touchEndY = e.changedTouches[0].clientY;
            this.handleSwipeGesture(e);
        }, { passive: true });
    }

    /**
     * Handle swipe gestures
     */
    handleSwipeGesture(e) {
        const swipeThreshold = 50;
        const swipeDistance = this.touchStartY - this.touchEndY;

        if (Math.abs(swipeDistance) > swipeThreshold) {
            const direction = swipeDistance > 0 ? 'up' : 'down';
            const target = e.target.closest('[data-swipe-enabled]');
            
            if (target) {
                const swipeHandler = this.activeSwipeHandlers.get(target);
                if (swipeHandler && swipeHandler[`swipe${direction.charAt(0).toUpperCase() + direction.slice(1)}`]) {
                    swipeHandler[`swipe${direction.charAt(0).toUpperCase() + direction.slice(1)}`](e);
                }
            }

            // Emit global swipe event
            window.dispatchEvent(new CustomEvent('swipe', {
                detail: { direction, distance: Math.abs(swipeDistance), originalEvent: e }
            }));
        }
    }

    /**
     * Enable swipe gestures for an element
     */
    enableSwipeForElement(element, handlers = {}) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return;

        element.setAttribute('data-swipe-enabled', 'true');
        this.activeSwipeHandlers.set(element, handlers);

        return () => {
            element.removeAttribute('data-swipe-enabled');
            this.activeSwipeHandlers.delete(element);
        };
    }

    /**
     * Setup keyboard visibility handling
     */
    setupKeyboardHandling() {
        if (!window.responsiveUtils.isMobile()) return;

        let initialViewportHeight = window.innerHeight;
        
        window.addEventListener('resize', () => {
            const currentHeight = window.innerHeight;
            const heightDifference = initialViewportHeight - currentHeight;
            
            // Assume keyboard is open if height decreased by more than 150px
            if (heightDifference > 150) {
                if (!this.keyboardVisible) {
                    this.keyboardVisible = true;
                    document.body.classList.add('keyboard-visible');
                    window.dispatchEvent(new CustomEvent('keyboard:show', {
                        detail: { heightDifference }
                    }));
                }
            } else {
                if (this.keyboardVisible) {
                    this.keyboardVisible = false;
                    document.body.classList.remove('keyboard-visible');
                    window.dispatchEvent(new CustomEvent('keyboard:hide'));
                }
            }
        });

        // Handle input focus/blur for better keyboard handling
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('input, textarea, select')) {
                setTimeout(() => {
                    e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    }

    /**
     * Setup pull-to-refresh functionality
     */
    setupPullToRefresh() {
        if (!window.responsiveUtils.isMobile()) return;

        const pullToRefreshElements = document.querySelectorAll('[data-pull-to-refresh]');
        
        pullToRefreshElements.forEach(element => {
            this.enablePullToRefresh(element);
        });
    }

    /**
     * Enable pull-to-refresh for an element
     */
    enablePullToRefresh(element, callback) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return;

        let startY = 0;
        let pullDistance = 0;
        const threshold = 60;
        let isRefreshing = false;
        let pullIndicator = null;

        // Create pull indicator
        const createPullIndicator = () => {
            pullIndicator = document.createElement('div');
            pullIndicator.className = 'pull-to-refresh-indicator';
            pullIndicator.innerHTML = `
                <div class="pull-to-refresh-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="pull-to-refresh-text">Pull to refresh</div>
            `;
            element.parentNode.insertBefore(pullIndicator, element);
            pullIndicator.style.height = '0px';
            pullIndicator.style.overflow = 'hidden';
            pullIndicator.style.transition = 'height 0.3s ease';
        };

        createPullIndicator();

        element.addEventListener('touchstart', (e) => {
            if (element.scrollTop === 0) {
                startY = e.touches[0].pageY;
            }
        }, { passive: true });

        element.addEventListener('touchmove', (e) => {
            if (element.scrollTop === 0 && !isRefreshing) {
                pullDistance = e.touches[0].pageY - startY;
                
                if (pullDistance > 0) {
                    e.preventDefault();
                    const height = Math.min(pullDistance * 0.5, threshold);
                    pullIndicator.style.height = `${height}px`;
                    
                    if (pullDistance > threshold) {
                        pullIndicator.querySelector('.pull-to-refresh-text').textContent = 'Release to refresh';
                        pullIndicator.classList.add('ready-to-refresh');
                    } else {
                        pullIndicator.querySelector('.pull-to-refresh-text').textContent = 'Pull to refresh';
                        pullIndicator.classList.remove('ready-to-refresh');
                    }
                }
            }
        }, { passive: false });

        element.addEventListener('touchend', () => {
            if (pullDistance > threshold && !isRefreshing) {
                isRefreshing = true;
                pullIndicator.style.height = `${threshold}px`;
                pullIndicator.querySelector('.pull-to-refresh-text').textContent = 'Refreshing...';
                pullIndicator.classList.add('refreshing');

                // Call callback or default refresh behavior
                const refreshPromise = callback ? callback() : this.defaultRefreshBehavior();
                
                Promise.resolve(refreshPromise).finally(() => {
                    setTimeout(() => {
                        isRefreshing = false;
                        pullIndicator.style.height = '0px';
                        pullIndicator.classList.remove('ready-to-refresh', 'refreshing');
                        pullDistance = 0;
                    }, 300);
                });
            } else {
                pullIndicator.style.height = '0px';
                pullIndicator.classList.remove('ready-to-refresh');
                pullDistance = 0;
            }
        }, { passive: true });

        return () => {
            if (pullIndicator) {
                pullIndicator.remove();
            }
        };
    }

    /**
     * Default refresh behavior
     */
    defaultRefreshBehavior() {
        return new Promise((resolve) => {
            // Simulate refresh delay
            setTimeout(() => {
                location.reload();
                resolve();
            }, 1000);
        });
    }

    /**
     * Setup accessibility enhancements for mobile
     */
    setupAccessibilityEnhancements() {
        // Increase touch target size for small elements
        const smallElements = document.querySelectorAll('button, a, input[type="checkbox"], input[type="radio"]');
        
        smallElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.width < 44 || rect.height < 44) {
                element.classList.add('touch-target-enhanced');
            }
        });

        // Add focus visible support for keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    /**
     * Optimize modal positioning for mobile
     */
    optimizeModalsForMobile() {
        const modals = document.querySelectorAll('[x-data*="show"], .modal');
        
        modals.forEach(modal => {
            if (window.responsiveUtils.isMobile()) {
                modal.classList.add('mobile-modal');
                
                // Add mobile-specific modal behavior
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            const isVisible = modal.style.display !== 'none' && modal.style.display !== '';
                            if (isVisible) {
                                document.body.style.overflow = 'hidden';
                                modal.scrollTop = 0;
                            } else {
                                document.body.style.overflow = '';
                            }
                        }
                    });
                });
                
                observer.observe(modal, { attributes: true });
            }
        });
    }

    /**
     * Create mobile-friendly button loading states
     */
    createMobileLoadingState(button, options = {}) {
        if (typeof button === 'string') {
            button = document.querySelector(button);
        }

        if (!button) return;

        const config = {
            text: 'Loading...',
            spinner: true,
            ...options
        };

        const originalContent = button.innerHTML;
        const originalDisabled = button.disabled;

        button.disabled = true;
        button.classList.add('loading');

        if (config.spinner) {
            button.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                ${config.text}
            `;
        } else {
            button.textContent = config.text;
        }

        return () => {
            button.disabled = originalDisabled;
            button.classList.remove('loading');
            button.innerHTML = originalContent;
        };
    }

    /**
     * Handle mobile navigation improvements
     */
    enhanceMobileNavigation() {
        if (!window.responsiveUtils.isMobile()) return;

        // Add touch-friendly navigation
        const navLinks = document.querySelectorAll('nav a, .nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            }, { passive: true });

            link.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 150);
            }, { passive: true });
        });

        // Add mobile menu improvements
        const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = document.querySelector('[data-mobile-menu]');
                if (menu) {
                    menu.classList.toggle('show');
                    document.body.classList.toggle('mobile-menu-open');
                }
            });
        }
    }

    /**
     * Get mobile-specific utilities
     */
    getUtils() {
        return {
            enableSwipe: this.enableSwipeForElement.bind(this),
            enablePullToRefresh: this.enablePullToRefresh.bind(this),
            createLoadingState: this.createMobileLoadingState.bind(this),
            optimizeModals: this.optimizeModalsForMobile.bind(this),
            isKeyboardVisible: () => this.keyboardVisible,
            isMobile: () => window.responsiveUtils.isMobile(),
            hasTouch: () => window.responsiveUtils.supportsTouch()
        };
    }
}

// Auto-initialize
const mobileOptimization = new MobileOptimization();

// Make globally available
window.mobileOptimization = mobileOptimization;
window.mobileUtils = mobileOptimization.getUtils();

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileOptimization;
}

export default mobileOptimization;
