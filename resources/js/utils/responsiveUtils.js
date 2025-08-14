/**
 * Mobile-First Responsive Design Utilities
 * Provides viewport detection, breakpoint management, and responsive behavior helpers
 */

class ResponsiveUtilities {
    constructor() {
        // Tailwind CSS breakpoints (mobile-first)
        this.breakpoints = {
            xs: 475,
            sm: 640,
            md: 768,
            lg: 1024,
            xl: 1280,
            '2xl': 1536,
            '3xl': 1600
        };

        // Current viewport state
        this.viewport = {
            width: window.innerWidth,
            height: window.innerHeight,
            orientation: this.getOrientation(),
            breakpoint: this.getCurrentBreakpoint(),
            isMobile: this.isMobile(),
            isTablet: this.isTablet(),
            isDesktop: this.isDesktop(),
            supportsTouch: this.supportsTouch(),
            devicePixelRatio: window.devicePixelRatio || 1
        };

        // Event listeners and callbacks
        this.resizeListeners = new Map();
        this.orientationListeners = new Map();
        this.breakpointListeners = new Map();

        // Debounce timer for resize events
        this.resizeTimer = null;
        this.debounceDelay = 150; // ms

        // Initialize event listeners
        this.initializeEventListeners();
        
        // Add CSS custom properties for viewport dimensions
        this.updateCSSCustomProperties();

        console.log('ResponsiveUtilities initialized:', this.viewport);
    }

    /**
     * Initialize event listeners for responsive behavior
     */
    initializeEventListeners() {
        // Resize listener with debouncing
        window.addEventListener('resize', () => {
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(() => {
                this.handleViewportChange();
            }, this.debounceDelay);
        });

        // Orientation change listener
        window.addEventListener('orientationchange', () => {
            // Use a longer delay for orientation changes
            setTimeout(() => {
                this.handleViewportChange();
            }, 250);
        });

        // Visibility change listener (for PWA/mobile optimization)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Re-check viewport when page becomes visible again
                this.handleViewportChange();
            }
        });

        // Load event to ensure accurate initial measurements
        window.addEventListener('load', () => {
            this.handleViewportChange();
        });
    }

    /**
     * Handle viewport changes and trigger callbacks
     */
    handleViewportChange() {
        const oldViewport = { ...this.viewport };
        
        // Update viewport state
        this.viewport.width = window.innerWidth;
        this.viewport.height = window.innerHeight;
        this.viewport.orientation = this.getOrientation();
        this.viewport.breakpoint = this.getCurrentBreakpoint();
        this.viewport.isMobile = this.isMobile();
        this.viewport.isTablet = this.isTablet();
        this.viewport.isDesktop = this.isDesktop();
        this.viewport.devicePixelRatio = window.devicePixelRatio || 1;

        // Update CSS custom properties
        this.updateCSSCustomProperties();

        // Trigger resize callbacks
        this.resizeListeners.forEach((callback, id) => {
            try {
                callback(this.viewport, oldViewport);
            } catch (error) {
                console.error(`Error in resize listener ${id}:`, error);
            }
        });

        // Trigger orientation callbacks if orientation changed
        if (oldViewport.orientation !== this.viewport.orientation) {
            this.orientationListeners.forEach((callback, id) => {
                try {
                    callback(this.viewport.orientation, oldViewport.orientation);
                } catch (error) {
                    console.error(`Error in orientation listener ${id}:`, error);
                }
            });
        }

        // Trigger breakpoint callbacks if breakpoint changed
        if (oldViewport.breakpoint !== this.viewport.breakpoint) {
            this.breakpointListeners.forEach((callback, id) => {
                try {
                    callback(this.viewport.breakpoint, oldViewport.breakpoint);
                } catch (error) {
                    console.error(`Error in breakpoint listener ${id}:`, error);
                }
            });
        }

        // Emit global events
        window.dispatchEvent(new CustomEvent('viewport:change', { 
            detail: { current: this.viewport, previous: oldViewport }
        }));

        if (oldViewport.breakpoint !== this.viewport.breakpoint) {
            window.dispatchEvent(new CustomEvent('viewport:breakpoint', { 
                detail: { current: this.viewport.breakpoint, previous: oldViewport.breakpoint }
            }));
        }
    }

    /**
     * Get current device orientation
     */
    getOrientation() {
        if (window.screen && window.screen.orientation) {
            return window.screen.orientation.angle === 0 || window.screen.orientation.angle === 180 
                ? 'portrait' : 'landscape';
        }
        
        return window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
    }

    /**
     * Get current breakpoint based on viewport width
     */
    getCurrentBreakpoint() {
        const width = window.innerWidth;
        
        // Check from largest to smallest (mobile-first approach)
        if (width >= this.breakpoints['3xl']) return '3xl';
        if (width >= this.breakpoints['2xl']) return '2xl';
        if (width >= this.breakpoints.xl) return 'xl';
        if (width >= this.breakpoints.lg) return 'lg';
        if (width >= this.breakpoints.md) return 'md';
        if (width >= this.breakpoints.sm) return 'sm';
        if (width >= this.breakpoints.xs) return 'xs';
        
        return 'mobile'; // Below xs breakpoint
    }

    /**
     * Check if current viewport is mobile
     */
    isMobile() {
        return window.innerWidth < this.breakpoints.md;
    }

    /**
     * Check if current viewport is tablet
     */
    isTablet() {
        return window.innerWidth >= this.breakpoints.md && window.innerWidth < this.breakpoints.lg;
    }

    /**
     * Check if current viewport is desktop
     */
    isDesktop() {
        return window.innerWidth >= this.breakpoints.lg;
    }

    /**
     * Check if device supports touch
     */
    supportsTouch() {
        return ('ontouchstart' in window) || 
               (navigator.maxTouchPoints > 0) || 
               (navigator.msMaxTouchPoints > 0);
    }

    /**
     * Update CSS custom properties with current viewport values
     */
    updateCSSCustomProperties() {
        const root = document.documentElement;
        
        root.style.setProperty('--viewport-width', `${this.viewport.width}px`);
        root.style.setProperty('--viewport-height', `${this.viewport.height}px`);
        root.style.setProperty('--viewport-vmin', `${Math.min(this.viewport.width, this.viewport.height)}px`);
        root.style.setProperty('--viewport-vmax', `${Math.max(this.viewport.width, this.viewport.height)}px`);
        root.style.setProperty('--device-pixel-ratio', this.viewport.devicePixelRatio);
        
        // Add data attributes to body for CSS targeting
        document.body.setAttribute('data-breakpoint', this.viewport.breakpoint);
        document.body.setAttribute('data-orientation', this.viewport.orientation);
        document.body.setAttribute('data-device-type', 
            this.viewport.isMobile ? 'mobile' : 
            this.viewport.isTablet ? 'tablet' : 'desktop'
        );

        // Add viewport height fix for mobile browsers
        root.style.setProperty('--vh', `${this.viewport.height * 0.01}px`);
    }

    /**
     * Add resize event listener
     */
    onResize(callback, id = null) {
        const listenerId = id || `resize_${Date.now()}_${Math.random()}`;
        this.resizeListeners.set(listenerId, callback);
        return listenerId;
    }

    /**
     * Add orientation change event listener
     */
    onOrientationChange(callback, id = null) {
        const listenerId = id || `orientation_${Date.now()}_${Math.random()}`;
        this.orientationListeners.set(listenerId, callback);
        return listenerId;
    }

    /**
     * Add breakpoint change event listener
     */
    onBreakpointChange(callback, id = null) {
        const listenerId = id || `breakpoint_${Date.now()}_${Math.random()}`;
        this.breakpointListeners.set(listenerId, callback);
        return listenerId;
    }

    /**
     * Remove event listeners
     */
    removeListener(type, id) {
        switch (type) {
            case 'resize':
                return this.resizeListeners.delete(id);
            case 'orientation':
                return this.orientationListeners.delete(id);
            case 'breakpoint':
                return this.breakpointListeners.delete(id);
            default:
                return false;
        }
    }

    /**
     * Check if viewport matches a specific breakpoint or range
     */
    matches(query) {
        if (typeof query === 'string') {
            // Single breakpoint check
            if (query === 'mobile') {
                return this.viewport.breakpoint === 'mobile';
            }
            return this.viewport.breakpoint === query;
        }

        if (typeof query === 'object') {
            const { min, max, orientation, device } = query;
            
            let matches = true;
            
            if (min) {
                const minWidth = this.breakpoints[min] || min;
                matches = matches && this.viewport.width >= minWidth;
            }
            
            if (max) {
                const maxWidth = this.breakpoints[max] || max;
                matches = matches && this.viewport.width <= maxWidth;
            }
            
            if (orientation) {
                matches = matches && this.viewport.orientation === orientation;
            }
            
            if (device) {
                matches = matches && (
                    (device === 'mobile' && this.viewport.isMobile) ||
                    (device === 'tablet' && this.viewport.isTablet) ||
                    (device === 'desktop' && this.viewport.isDesktop)
                );
            }
            
            return matches;
        }

        return false;
    }

    /**
     * Get responsive value based on current breakpoint
     */
    getResponsiveValue(values) {
        // Support object format: { mobile: value, sm: value, md: value, ... }
        if (typeof values === 'object' && !Array.isArray(values)) {
            const breakpoints = ['mobile', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];
            const currentIndex = breakpoints.indexOf(this.viewport.breakpoint);
            
            // Find the appropriate value by walking backwards through breakpoints
            for (let i = currentIndex; i >= 0; i--) {
                const breakpoint = breakpoints[i];
                if (values.hasOwnProperty(breakpoint)) {
                    return values[breakpoint];
                }
            }
            
            // If no match found, return the first available value
            return Object.values(values)[0];
        }

        // Support array format: [mobile, tablet, desktop]
        if (Array.isArray(values)) {
            if (this.viewport.isMobile) return values[0];
            if (this.viewport.isTablet) return values[1] || values[0];
            return values[2] || values[1] || values[0];
        }

        // Return single value as-is
        return values;
    }

    /**
     * Load different images based on screen size and pixel density
     */
    getResponsiveImageSrc(images) {
        const pixelRatio = this.viewport.devicePixelRatio;
        const breakpoint = this.viewport.breakpoint;
        
        if (typeof images === 'string') {
            return images;
        }

        if (typeof images === 'object') {
            // Check for high-DPI images first
            if (pixelRatio > 1) {
                const highDpiKey = `${breakpoint}@2x`;
                if (images[highDpiKey]) {
                    return images[highDpiKey];
                }
            }

            // Fall back to regular resolution
            if (images[breakpoint]) {
                return images[breakpoint];
            }

            // Try to find the best match
            const breakpoints = ['mobile', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];
            const currentIndex = breakpoints.indexOf(breakpoint);
            
            for (let i = currentIndex; i >= 0; i--) {
                const bp = breakpoints[i];
                if (images[bp]) {
                    return images[bp];
                }
            }

            // Return the first available image
            return Object.values(images)[0];
        }

        return images;
    }

    /**
     * Create media query string
     */
    createMediaQuery(breakpoint, type = 'min') {
        const width = this.breakpoints[breakpoint];
        if (!width) return '';
        
        return type === 'min' 
            ? `(min-width: ${width}px)`
            : `(max-width: ${width - 1}px)`;
    }

    /**
     * Get current viewport information
     */
    getViewport() {
        return { ...this.viewport };
    }

    /**
     * Get available breakpoints
     */
    getBreakpoints() {
        return { ...this.breakpoints };
    }

    /**
     * Debounced function utility
     */
    debounce(func, delay = this.debounceDelay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    /**
     * Throttled function utility
     */
    throttle(func, limit = 100) {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Export singleton instance
const responsiveUtils = new ResponsiveUtilities();

// Make globally available
window.responsiveUtils = responsiveUtils;

// Global helper functions
window.isMobile = () => responsiveUtils.isMobile();
window.isTablet = () => responsiveUtils.isTablet();
window.isDesktop = () => responsiveUtils.isDesktop();
window.getCurrentBreakpoint = () => responsiveUtils.getCurrentBreakpoint();
window.matchesBreakpoint = (query) => responsiveUtils.matches(query);

// Mobile-specific utilities
window.mobileUtils = {
    // Check if device has touch capability
    hasTouch: () => responsiveUtils.supportsTouch(),
    
    // Get safe area insets for devices with notches
    getSafeAreaInsets() {
        const style = getComputedStyle(document.documentElement);
        return {
            top: style.getPropertyValue('env(safe-area-inset-top)') || '0px',
            right: style.getPropertyValue('env(safe-area-inset-right)') || '0px',
            bottom: style.getPropertyValue('env(safe-area-inset-bottom)') || '0px',
            left: style.getPropertyValue('env(safe-area-inset-left)') || '0px'
        };
    },
    
    // Optimize for mobile input zoom prevention
    preventInputZoom() {
        // Add meta tag to prevent zoom on input focus (iOS Safari)
        const metaViewport = document.querySelector('meta[name="viewport"]');
        if (metaViewport && responsiveUtils.isMobile()) {
            const content = metaViewport.getAttribute('content');
            if (!content.includes('user-scalable=no')) {
                metaViewport.setAttribute('content', content + ', user-scalable=no');
            }
        }
    },
    
    // Enable mobile-friendly table layouts
    enableMobileTables() {
        if (responsiveUtils.isMobile()) {
            document.querySelectorAll('table:not(.table-mobile-enabled)').forEach(table => {
                table.classList.add('table-mobile-enabled');
                
                // Add data labels for mobile card view
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                table.querySelectorAll('tbody tr').forEach(row => {
                    Array.from(row.children).forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });
                
                // Apply mobile card styling
                table.classList.add('table-mobile-cards');
            });
        }
    },
    
    // Enable swipe gestures for mobile
    enableSwipeGestures(element, callbacks = {}) {
        if (!responsiveUtils.supportsTouch()) return;
        
        let startX = 0;
        let startY = 0;
        let threshold = 50; // minimum distance for swipe
        
        element.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        element.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            
            const deltaX = endX - startX;
            const deltaY = endY - startY;
            
            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                // Horizontal swipe
                if (Math.abs(deltaX) > threshold) {
                    if (deltaX > 0 && callbacks.swipeRight) {
                        callbacks.swipeRight(e);
                    } else if (deltaX < 0 && callbacks.swipeLeft) {
                        callbacks.swipeLeft(e);
                    }
                }
            } else {
                // Vertical swipe
                if (Math.abs(deltaY) > threshold) {
                    if (deltaY > 0 && callbacks.swipeDown) {
                        callbacks.swipeDown(e);
                    } else if (deltaY < 0 && callbacks.swipeUp) {
                        callbacks.swipeUp(e);
                    }
                }
            }
        }, { passive: true });
    },
    
    // Handle mobile keyboard visibility
    handleMobileKeyboard() {
        if (!responsiveUtils.isMobile()) return;
        
        let initialViewportHeight = window.innerHeight;
        
        window.addEventListener('resize', () => {
            const currentHeight = window.innerHeight;
            const heightDifference = initialViewportHeight - currentHeight;
            
            // Assume keyboard is open if height decreased by more than 150px
            if (heightDifference > 150) {
                document.body.classList.add('keyboard-open');
                document.dispatchEvent(new CustomEvent('mobile:keyboard:open', {
                    detail: { heightDifference }
                }));
            } else {
                document.body.classList.remove('keyboard-open');
                document.dispatchEvent(new CustomEvent('mobile:keyboard:close'));
            }
        });
    },
    
    // Optimize modal positioning for mobile
    optimizeModals() {
        if (responsiveUtils.isMobile()) {
            document.querySelectorAll('[x-data*="show"]').forEach(modal => {
                // Add mobile-friendly classes
                modal.classList.add('mobile-optimized-modal');
            });
        }
    },
    
    // Enable pull-to-refresh on mobile
    enablePullToRefresh(element, callback) {
        if (!responsiveUtils.supportsTouch()) return;
        
        let startY = 0;
        let pullDistance = 0;
        let threshold = 80;
        let isRefreshing = false;
        
        element.addEventListener('touchstart', (e) => {
            if (element.scrollTop === 0) {
                startY = e.touches[0].pageY;
            }
        }, { passive: true });
        
        element.addEventListener('touchmove', (e) => {
            if (element.scrollTop === 0 && !isRefreshing) {
                pullDistance = e.touches[0].pageY - startY;
                
                if (pullDistance > 0) {
                    // Visual feedback for pull-to-refresh
                    const opacity = Math.min(pullDistance / threshold, 1);
                    element.style.transform = `translateY(${pullDistance * 0.3}px)`;
                    element.style.opacity = 1 - (opacity * 0.2);
                }
            }
        }, { passive: true });
        
        element.addEventListener('touchend', () => {
            if (pullDistance > threshold && !isRefreshing) {
                isRefreshing = true;
                callback().finally(() => {
                    isRefreshing = false;
                    element.style.transform = '';
                    element.style.opacity = '';
                    pullDistance = 0;
                });
            } else {
                element.style.transform = '';
                element.style.opacity = '';
                pullDistance = 0;
            }
        }, { passive: true });
    }
};

// Auto-initialize mobile optimizations
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        if (responsiveUtils.isMobile()) {
            // Auto-enable mobile table layouts
            mobileUtils.enableMobileTables();
            
            // Handle mobile keyboard
            mobileUtils.handleMobileKeyboard();
            
            // Optimize modals
            mobileUtils.optimizeModals();
            
            // Add mobile-specific body class
            document.body.classList.add('mobile-device');
            
            console.log('Mobile optimizations enabled');
        }
        
        if (responsiveUtils.supportsTouch()) {
            document.body.classList.add('touch-device');
        }
    });
    
    // Handle orientation changes
    window.addEventListener('orientationchange', () => {
        // Re-run mobile optimizations after orientation change
        setTimeout(() => {
            if (responsiveUtils.isMobile()) {
                mobileUtils.enableMobileTables();
            }
        }, 250);
    });
}

export default responsiveUtils;
