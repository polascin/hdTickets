/**
 * HD Tickets - Responsive Design Manager
 * JavaScript utilities for responsive behavior, viewport detection, and breakpoint management
 * 
 * Features:
 * - Viewport size detection and breakpoint management
 * - Responsive component behavior adaptation
 * - Image lazy loading with intersection observer
 * - Touch device detection and optimization
 * - Orientation change handling
 * - Performance optimizations for mobile
 * 
 * @version 2.0.0
 * @author HD Tickets Development Team
 * @license MIT
 */

class ResponsiveDesignManager {
    constructor(options = {}) {
        this.options = {
            breakpoints: {
                xs: 320,
                sm: 640,
                md: 768,
                lg: 1024,
                xl: 1280,
                '2xl': 1536,
                '3xl': 1920
            },
            touchTargetMin: 44,
            debounceDelay: 250,
            lazyLoadOptions: {
                rootMargin: '50px 0px',
                threshold: 0.1
            },
            enableDebugMode: false,
            ...options
        };

        this.currentBreakpoint = null;
        this.previousBreakpoint = null;
        this.viewport = {
            width: 0,
            height: 0,
            ratio: 0
        };
        
        this.isTouchDevice = false;
        this.isRetina = false;
        this.orientation = null;
        
        this.observers = new Set();
        this.resizeListeners = new Set();
        this.orientationListeners = new Set();
        
        this.init();
    }

    /**
     * Initialize the responsive design manager
     */
    init() {
        this.detectDeviceCapabilities();
        this.updateViewport();
        this.detectBreakpoint();
        this.setupEventListeners();
        this.initializeLazyLoading();
        this.optimizeForDevice();
        this.enhanceExistingComponents();
        
        if (this.options.enableDebugMode) {
            this.setupDebugMode();
        }

        this.log('Responsive Design Manager initialized', {
            breakpoint: this.currentBreakpoint,
            viewport: this.viewport,
            touch: this.isTouchDevice,
            retina: this.isRetina
        });
    }

    /**
     * Detect device capabilities
     */
    detectDeviceCapabilities() {
        // Touch device detection
        this.isTouchDevice = (
            'ontouchstart' in window ||
            navigator.maxTouchPoints > 0 ||
            navigator.msMaxTouchPoints > 0
        );

        // Retina display detection
        this.isRetina = (
            window.devicePixelRatio > 1 ||
            (window.matchMedia && window.matchMedia('(-webkit-min-device-pixel-ratio: 1.5)').matches)
        );

        // Update body classes
        document.body.classList.toggle('touch-device', this.isTouchDevice);
        document.body.classList.toggle('no-touch', !this.isTouchDevice);
        document.body.classList.toggle('retina', this.isRetina);

        // Connection quality detection
        if ('connection' in navigator) {
            const connection = navigator.connection;
            const isSlowConnection = (
                connection.effectiveType === '2g' ||
                connection.effectiveType === 'slow-2g' ||
                connection.saveData
            );
            
            document.body.classList.toggle('slow-connection', isSlowConnection);
            
            if (isSlowConnection) {
                this.optimizeForSlowConnection();
            }
        }
    }

    /**
     * Update viewport dimensions
     */
    updateViewport() {
        this.viewport.width = window.innerWidth;
        this.viewport.height = window.innerHeight;
        this.viewport.ratio = this.viewport.width / this.viewport.height;
        
        // Update CSS custom properties
        document.documentElement.style.setProperty('--viewport-width', `${this.viewport.width}px`);
        document.documentElement.style.setProperty('--viewport-height', `${this.viewport.height}px`);
        document.documentElement.style.setProperty('--viewport-ratio', this.viewport.ratio);
        
        // Update orientation
        const newOrientation = this.viewport.width > this.viewport.height ? 'landscape' : 'portrait';
        if (this.orientation !== newOrientation) {
            this.orientation = newOrientation;
            document.body.classList.remove('orientation-landscape', 'orientation-portrait');
            document.body.classList.add(`orientation-${this.orientation}`);
            this.notifyOrientationChange();
        }
    }

    /**
     * Detect current breakpoint
     */
    detectBreakpoint() {
        this.previousBreakpoint = this.currentBreakpoint;
        
        const width = this.viewport.width;
        
        if (width >= this.options.breakpoints['3xl']) {
            this.currentBreakpoint = '3xl';
        } else if (width >= this.options.breakpoints['2xl']) {
            this.currentBreakpoint = '2xl';
        } else if (width >= this.options.breakpoints.xl) {
            this.currentBreakpoint = 'xl';
        } else if (width >= this.options.breakpoints.lg) {
            this.currentBreakpoint = 'lg';
        } else if (width >= this.options.breakpoints.md) {
            this.currentBreakpoint = 'md';
        } else if (width >= this.options.breakpoints.sm) {
            this.currentBreakpoint = 'sm';
        } else {
            this.currentBreakpoint = 'xs';
        }

        // Update body class
        if (this.previousBreakpoint) {
            document.body.classList.remove(`breakpoint-${this.previousBreakpoint}`);
        }
        document.body.classList.add(`breakpoint-${this.currentBreakpoint}`);

        // Notify breakpoint change
        if (this.previousBreakpoint !== this.currentBreakpoint) {
            this.notifyBreakpointChange();
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Debounced resize handler
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.updateViewport();
                this.detectBreakpoint();
                this.notifyResize();
            }, this.options.debounceDelay);
        });

        // Orientation change handler
        if ('orientation' in screen) {
            screen.orientation?.addEventListener('change', () => {
                setTimeout(() => {
                    this.updateViewport();
                    this.detectBreakpoint();
                    this.handleOrientationChange();
                }, 100); // Small delay to ensure dimensions are updated
            });
        }

        // Load event to ensure accurate initial measurements
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.updateViewport();
                this.detectBreakpoint();
            }, 100);
        });

        // Visibility change handler
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Re-check dimensions when page becomes visible
                setTimeout(() => {
                    this.updateViewport();
                    this.detectBreakpoint();
                }, 100);
            }
        });
    }

    /**
     * Initialize lazy loading
     */
    initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.lazyImageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadLazyImage(entry.target);
                        this.lazyImageObserver.unobserve(entry.target);
                    }
                });
            }, this.options.lazyLoadOptions);

            // Observe existing lazy images
            this.observeLazyImages();

            // Observe for new lazy images
            this.observeForNewImages();
        } else {
            // Fallback for older browsers
            this.loadAllLazyImages();
        }
    }

    /**
     * Observe lazy images
     */
    observeLazyImages() {
        const lazyImages = document.querySelectorAll('[data-lazy-src], [data-lazy-background]');
        lazyImages.forEach(img => this.lazyImageObserver.observe(img));
    }

    /**
     * Load lazy image
     */
    loadLazyImage(element) {
        const lazySrc = element.dataset.lazySrc;
        const lazyBackground = element.dataset.lazyBackground;
        const lazySrcset = element.dataset.lazySrcset;

        if (lazySrc) {
            element.src = lazySrc;
            element.removeAttribute('data-lazy-src');
        }

        if (lazySrcset) {
            element.srcset = lazySrcset;
            element.removeAttribute('data-lazy-srcset');
        }

        if (lazyBackground) {
            element.style.backgroundImage = `url(${lazyBackground})`;
            element.removeAttribute('data-lazy-background');
        }

        element.classList.add('lazy-loaded');
        element.classList.remove('lazy-loading');

        // Trigger load event
        element.dispatchEvent(new CustomEvent('lazyLoaded'));
    }

    /**
     * Load all lazy images (fallback)
     */
    loadAllLazyImages() {
        const lazyImages = document.querySelectorAll('[data-lazy-src], [data-lazy-background]');
        lazyImages.forEach(img => this.loadLazyImage(img));
    }

    /**
     * Observe for new images
     */
    observeForNewImages() {
        if ('MutationObserver' in window) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            if (node.hasAttribute('data-lazy-src') || node.hasAttribute('data-lazy-background')) {
                                this.lazyImageObserver.observe(node);
                            }
                            
                            const lazyElements = node.querySelectorAll('[data-lazy-src], [data-lazy-background]');
                            lazyElements.forEach(el => this.lazyImageObserver.observe(el));
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            this.observers.add(observer);
        }
    }

    /**
     * Optimize for device type
     */
    optimizeForDevice() {
        if (this.isTouchDevice) {
            this.optimizeForTouch();
        }

        if (this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm') {
            this.optimizeForMobile();
        }
    }

    /**
     * Optimize for touch devices
     */
    optimizeForTouch() {
        // Ensure minimum touch target sizes
        const interactiveElements = document.querySelectorAll(
            'button, [role="button"], a, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );

        interactiveElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.width < this.options.touchTargetMin || rect.height < this.options.touchTargetMin) {
                element.style.minWidth = `${this.options.touchTargetMin}px`;
                element.style.minHeight = `${this.options.touchTargetMin}px`;
                element.classList.add('touch-optimized');
            }
        });

        // Add touch-friendly hover states
        document.documentElement.style.setProperty('--hover-enabled', '0');
    }

    /**
     * Optimize for mobile devices
     */
    optimizeForMobile() {
        // Disable smooth scrolling on mobile for better performance
        document.documentElement.style.scrollBehavior = 'auto';
        
        // Enable momentum scrolling on iOS
        document.body.style.webkitOverflowScrolling = 'touch';
        
        // Optimize form inputs for mobile
        const inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            // Prevent zoom on focus for certain input types
            if (input.type === 'email' || input.type === 'number' || input.type === 'tel') {
                if (!input.hasAttribute('data-allow-zoom')) {
                    input.style.fontSize = '16px'; // Prevent zoom on iOS
                }
            }
        });
    }

    /**
     * Optimize for slow connections
     */
    optimizeForSlowConnection() {
        // Disable non-essential animations
        document.body.classList.add('reduce-animations');
        
        // Lazy load more aggressively
        this.options.lazyLoadOptions.rootMargin = '20px 0px';
        
        // Disable autoplay videos
        const videos = document.querySelectorAll('video[autoplay]');
        videos.forEach(video => {
            video.removeAttribute('autoplay');
            video.setAttribute('data-autoplay-disabled', 'true');
        });
    }

    /**
     * Enhance existing components
     */
    enhanceExistingComponents() {
        this.enhanceTables();
        this.enhanceImages();
        this.enhanceForms();
        this.enhanceCards();
    }

    /**
     * Enhance tables for mobile
     */
    enhanceTables() {
        const tables = document.querySelectorAll('table:not(.table-enhanced)');
        
        tables.forEach(table => {
            table.classList.add('table-enhanced');
            
            // Add responsive wrapper if not present
            if (!table.closest('.table-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }

            // Add data attributes for mobile stacking
            if (this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm') {
                this.convertTableForMobile(table);
            }
        });
    }

    /**
     * Convert table for mobile display
     */
    convertTableForMobile(table) {
        const headers = table.querySelectorAll('thead th');
        const headerTexts = Array.from(headers).map(th => th.textContent.trim());
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headerTexts[index]) {
                    cell.setAttribute('data-label', headerTexts[index]);
                }
            });
        });

        table.classList.add('table-responsive-stack');
    }

    /**
     * Enhance images for responsive display
     */
    enhanceImages() {
        const images = document.querySelectorAll('img:not(.img-enhanced)');
        
        images.forEach(img => {
            img.classList.add('img-enhanced');
            
            // Add loading attribute if not present
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Add responsive image wrapper if needed
            if (!img.closest('.img-responsive') && !img.closest('figure')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'img-responsive';
                img.parentNode.insertBefore(wrapper, img);
                wrapper.appendChild(img);
            }
        });
    }

    /**
     * Enhance forms for responsive display
     */
    enhanceForms() {
        const forms = document.querySelectorAll('form:not(.form-enhanced)');
        
        forms.forEach(form => {
            form.classList.add('form-enhanced');
            
            // Convert horizontal layouts to vertical on mobile
            if (this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm') {
                const rows = form.querySelectorAll('.form-row');
                rows.forEach(row => {
                    row.classList.add('mobile-flex-col');
                });
            }
        });
    }

    /**
     * Enhance cards for responsive display
     */
    enhanceCards() {
        const cards = document.querySelectorAll('.card:not(.card-enhanced)');
        
        cards.forEach(card => {
            card.classList.add('card-enhanced');
            
            // Add container query support if available
            if (CSS.supports('container-type: inline-size')) {
                card.style.containerType = 'inline-size';
            }
        });
    }

    /**
     * Handle orientation change
     */
    handleOrientationChange() {
        // Re-evaluate touch optimizations
        setTimeout(() => {
            this.optimizeForDevice();
        }, 300); // Wait for orientation change to complete
        
        // Force re-layout of certain components
        const responsiveComponents = document.querySelectorAll('.dashboard-grid, .grid');
        responsiveComponents.forEach(component => {
            component.style.display = 'none';
            component.offsetHeight; // Trigger reflow
            component.style.display = '';
        });
    }

    /**
     * Notify breakpoint change
     */
    notifyBreakpointChange() {
        const event = new CustomEvent('breakpointChange', {
            detail: {
                current: this.currentBreakpoint,
                previous: this.previousBreakpoint,
                viewport: this.viewport
            }
        });
        
        document.dispatchEvent(event);
        
        // Notify registered listeners
        this.resizeListeners.forEach(callback => {
            if (typeof callback === 'function') {
                callback({
                    breakpoint: this.currentBreakpoint,
                    previousBreakpoint: this.previousBreakpoint,
                    viewport: this.viewport
                });
            }
        });

        this.log('Breakpoint changed', {
            from: this.previousBreakpoint,
            to: this.currentBreakpoint
        });
    }

    /**
     * Notify resize
     */
    notifyResize() {
        const event = new CustomEvent('viewportResize', {
            detail: {
                viewport: this.viewport,
                breakpoint: this.currentBreakpoint
            }
        });
        
        document.dispatchEvent(event);
    }

    /**
     * Notify orientation change
     */
    notifyOrientationChange() {
        this.orientationListeners.forEach(callback => {
            if (typeof callback === 'function') {
                callback(this.orientation);
            }
        });

        const event = new CustomEvent('orientationChange', {
            detail: {
                orientation: this.orientation,
                viewport: this.viewport
            }
        });
        
        document.dispatchEvent(event);
    }

    /**
     * Setup debug mode
     */
    setupDebugMode() {
        // Create debug panel
        const debugPanel = document.createElement('div');
        debugPanel.className = 'responsive-debug-panel';
        debugPanel.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            z-index: 10000;
            min-width: 200px;
        `;
        
        debugPanel.innerHTML = `
            <div><strong>Responsive Debug</strong></div>
            <div>Breakpoint: <span id="debug-breakpoint">${this.currentBreakpoint}</span></div>
            <div>Viewport: <span id="debug-viewport">${this.viewport.width}×${this.viewport.height}</span></div>
            <div>Touch: <span id="debug-touch">${this.isTouchDevice ? 'Yes' : 'No'}</span></div>
            <div>Retina: <span id="debug-retina">${this.isRetina ? 'Yes' : 'No'}</span></div>
            <div>Orientation: <span id="debug-orientation">${this.orientation}</span></div>
        `;
        
        document.body.appendChild(debugPanel);

        // Update debug info on changes
        document.addEventListener('breakpointChange', () => {
            document.getElementById('debug-breakpoint').textContent = this.currentBreakpoint;
            document.getElementById('debug-viewport').textContent = `${this.viewport.width}×${this.viewport.height}`;
            document.getElementById('debug-orientation').textContent = this.orientation;
        });

        // Add breakpoint indicator overlay
        const breakpointIndicator = document.createElement('div');
        breakpointIndicator.style.cssText = `
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
            z-index: 10000;
        `;
        breakpointIndicator.textContent = this.currentBreakpoint.toUpperCase();
        document.body.appendChild(breakpointIndicator);

        // Update indicator on breakpoint change
        document.addEventListener('breakpointChange', () => {
            breakpointIndicator.textContent = this.currentBreakpoint.toUpperCase();
        });

        console.log('Responsive Debug Mode enabled');
    }

    /**
     * Public API methods
     */

    // Get current breakpoint
    getBreakpoint() {
        return this.currentBreakpoint;
    }

    // Get viewport info
    getViewport() {
        return { ...this.viewport };
    }

    // Check if current breakpoint matches
    isBreakpoint(breakpoint) {
        if (Array.isArray(breakpoint)) {
            return breakpoint.includes(this.currentBreakpoint);
        }
        return this.currentBreakpoint === breakpoint;
    }

    // Check if screen is mobile size
    isMobile() {
        return this.isBreakpoint(['xs', 'sm']);
    }

    // Check if screen is tablet size
    isTablet() {
        return this.isBreakpoint('md');
    }

    // Check if screen is desktop size
    isDesktop() {
        return this.isBreakpoint(['lg', 'xl', '2xl', '3xl']);
    }

    // Add resize listener
    onResize(callback) {
        this.resizeListeners.add(callback);
        return () => this.resizeListeners.delete(callback);
    }

    // Add orientation change listener
    onOrientationChange(callback) {
        this.orientationListeners.add(callback);
        return () => this.orientationListeners.delete(callback);
    }

    // Force refresh
    refresh() {
        this.updateViewport();
        this.detectBreakpoint();
        this.optimizeForDevice();
        this.enhanceExistingComponents();
    }

    // Clean up
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
        this.resizeListeners.clear();
        this.orientationListeners.clear();
        
        if (this.lazyImageObserver) {
            this.lazyImageObserver.disconnect();
        }
    }

    /**
     * Utility methods
     */
    log(...args) {
        if (this.options.enableDebugMode) {
            console.log('[ResponsiveDesign]', ...args);
        }
    }

    error(...args) {
        console.error('[ResponsiveDesign Error]', ...args);
    }
}

// Auto-initialize
let responsiveManager;

if (typeof window !== 'undefined') {
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            responsiveManager = new ResponsiveDesignManager();
            window.responsiveManager = responsiveManager;
        });
    } else {
        responsiveManager = new ResponsiveDesignManager();
        window.responsiveManager = responsiveManager;
    }

    // Alpine.js integration
    if (window.Alpine) {
        document.addEventListener('alpine:init', () => {
            Alpine.data('responsiveDesign', () => ({
                breakpoint: responsiveManager?.getBreakpoint() || 'lg',
                viewport: responsiveManager?.getViewport() || { width: 1024, height: 768 },
                isMobile: () => responsiveManager?.isMobile() || false,
                isTablet: () => responsiveManager?.isTablet() || false,
                isDesktop: () => responsiveManager?.isDesktop() || true,
                isBreakpoint: (bp) => responsiveManager?.isBreakpoint(bp) || false
            }));
        });
    }
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ResponsiveDesignManager;
}

// Make available globally
window.ResponsiveDesignManager = ResponsiveDesignManager;
