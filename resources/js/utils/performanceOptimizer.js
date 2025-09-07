/**
 * HD Tickets - Performance Optimization Utilities
 * 
 * Advanced performance optimization utilities for the responsive design system,
 * including lazy loading, intersection observers, and rendering optimizations.
 * 
 * Features:
 * - Lazy loading for images and components
 * - Intersection observers for performance
 * - Debounced event handlers
 * - Memory leak prevention
 * - Frame rate optimization
 * - Resource preloading strategies
 */

class HDPerformanceOptimizer {
    constructor() {
        this.observers = new Set();
        this.lazyLoadQueue = new Set();
        this.resizeHandlers = new Set();
        this.scrollHandlers = new Set();
        this.config = {
            intersectionThreshold: 0.1,
            rootMargin: '50px',
            lazyLoadDelay: 100,
            debounceDelay: 16, // ~60fps
            maxObservers: 50
        };

        this.init();
    }

    /**
     * Initialize the performance optimization system
     */
    init() {
        this.setupIntersectionObservers();
        this.setupLazyLoading();
        this.setupOptimizedEventHandlers();
        this.setupMemoryManagement();
        this.preloadCriticalResources();
        
        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.optimizeInitialRender();
            });
        } else {
            this.optimizeInitialRender();
        }
    }

    /**
     * Setup intersection observers for visibility-based optimizations
     */
    setupIntersectionObservers() {
        if (!('IntersectionObserver' in window)) {
            console.warn('IntersectionObserver not supported, falling back to scroll events');
            this.setupScrollFallback();
            return;
        }

        // Main visibility observer
        this.visibilityObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const element = entry.target;
                
                if (entry.isIntersecting) {
                    this.handleElementVisible(element);
                } else {
                    this.handleElementHidden(element);
                }
            });
        }, {
            threshold: this.config.intersectionThreshold,
            rootMargin: this.config.rootMargin
        });

        this.observers.add(this.visibilityObserver);

        // Lazy load observer (smaller threshold for earlier loading)
        this.lazyLoadObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.lazyLoadElement(entry.target);
                    this.lazyLoadObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.01,
            rootMargin: '100px'
        });

        this.observers.add(this.lazyLoadObserver);
    }

    /**
     * Setup lazy loading for images and components
     */
    setupLazyLoading() {
        // Find all lazy load candidates
        const lazyImages = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        const lazyComponents = document.querySelectorAll('[data-lazy-load]');
        const lazyIframes = document.querySelectorAll('iframe[data-src]');

        // Observe images
        lazyImages.forEach(img => {
            if (this.lazyLoadObserver) {
                this.lazyLoadObserver.observe(img);
            }
            this.lazyLoadQueue.add(img);
        });

        // Observe components
        lazyComponents.forEach(component => {
            if (this.lazyLoadObserver) {
                this.lazyLoadObserver.observe(component);
            }
            this.lazyLoadQueue.add(component);
        });

        // Observe iframes
        lazyIframes.forEach(iframe => {
            if (this.lazyLoadObserver) {
                this.lazyLoadObserver.observe(iframe);
            }
            this.lazyLoadQueue.add(iframe);
        });
    }

    /**
     * Handle element becoming visible
     */
    handleElementVisible(element) {
        // Add visible class for CSS animations
        element.classList.add('hd-visible');
        
        // Initialize components that need visibility
        if (element.hasAttribute('data-init-on-visible')) {
            this.initializeComponent(element);
        }

        // Start animations if needed
        if (element.hasAttribute('data-animate-on-visible')) {
            this.startAnimation(element);
        }

        // Dispatch custom event
        element.dispatchEvent(new CustomEvent('hd:visible', {
            detail: { element, observer: this.visibilityObserver }
        }));
    }

    /**
     * Handle element becoming hidden
     */
    handleElementHidden(element) {
        // Remove visible class
        element.classList.remove('hd-visible');
        
        // Pause heavy operations if configured
        if (element.hasAttribute('data-pause-on-hidden')) {
            this.pauseComponent(element);
        }

        // Dispatch custom event
        element.dispatchEvent(new CustomEvent('hd:hidden', {
            detail: { element, observer: this.visibilityObserver }
        }));
    }

    /**
     * Lazy load individual element
     */
    lazyLoadElement(element) {
        const tagName = element.tagName.toLowerCase();
        
        switch (tagName) {
            case 'img':
                this.lazyLoadImage(element);
                break;
            case 'iframe':
                this.lazyLoadIframe(element);
                break;
            default:
                this.lazyLoadComponent(element);
        }
    }

    /**
     * Lazy load image with loading states
     */
    lazyLoadImage(img) {
        const src = img.getAttribute('data-src') || img.getAttribute('src');
        if (!src) return;

        // Add loading state
        img.classList.add('hd-loading');
        
        // Create new image to preload
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            // Image loaded successfully
            img.src = src;
            img.classList.remove('hd-loading');
            img.classList.add('hd-loaded');
            
            // Remove data-src to mark as loaded
            img.removeAttribute('data-src');
            
            // Fade in animation
            this.fadeInElement(img);
            
            // Dispatch load event
            img.dispatchEvent(new CustomEvent('hd:imageLoaded', {
                detail: { src, element: img }
            }));
        };
        
        imageLoader.onerror = () => {
            // Handle image load error
            img.classList.remove('hd-loading');
            img.classList.add('hd-error');
            
            // Set fallback image if available
            const fallback = img.getAttribute('data-fallback');
            if (fallback) {
                img.src = fallback;
            }
            
            // Dispatch error event
            img.dispatchEvent(new CustomEvent('hd:imageError', {
                detail: { src, element: img }
            }));
        };
        
        // Start loading
        imageLoader.src = src;
    }

    /**
     * Lazy load iframe
     */
    lazyLoadIframe(iframe) {
        const src = iframe.getAttribute('data-src');
        if (!src) return;

        iframe.classList.add('hd-loading');
        iframe.src = src;
        iframe.removeAttribute('data-src');
        
        iframe.onload = () => {
            iframe.classList.remove('hd-loading');
            iframe.classList.add('hd-loaded');
            this.fadeInElement(iframe);
        };
    }

    /**
     * Lazy load component
     */
    lazyLoadComponent(element) {
        const componentType = element.getAttribute('data-lazy-load');
        
        switch (componentType) {
            case 'chart':
                this.loadChart(element);
                break;
            case 'map':
                this.loadMap(element);
                break;
            case 'widget':
                this.loadWidget(element);
                break;
            default:
                this.loadGenericComponent(element);
        }
    }

    /**
     * Setup optimized event handlers with debouncing
     */
    setupOptimizedEventHandlers() {
        // Optimized resize handler
        let resizeTimeout;
        const debouncedResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleOptimizedResize();
            }, this.config.debounceDelay);
        };
        
        window.addEventListener('resize', debouncedResize, { passive: true });

        // Optimized scroll handler
        let scrollTimeout;
        let isScrolling = false;
        
        const debouncedScroll = () => {
            if (!isScrolling) {
                window.requestAnimationFrame(() => {
                    this.handleOptimizedScroll();
                    isScrolling = false;
                });
                isScrolling = true;
            }
            
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.handleScrollEnd();
            }, 150);
        };
        
        window.addEventListener('scroll', debouncedScroll, { passive: true });

        // Optimized orientation change
        window.addEventListener('orientationchange', () => {
            // Small delay to account for browser reflow
            setTimeout(() => {
                this.handleOrientationChange();
            }, 100);
        }, { passive: true });
    }

    /**
     * Handle optimized resize events
     */
    handleOptimizedResize() {
        // Update container queries
        if (window.hdContainerQueries) {
            window.hdContainerQueries.updateAllContainers();
        }

        // Update grid layouts
        if (window.hdGridLayout) {
            window.hdGridLayout.updateAllGrids();
        }

        // Notify resize handlers
        this.resizeHandlers.forEach(handler => {
            try {
                handler();
            } catch (error) {
                console.error('Resize handler error:', error);
            }
        });

        // Dispatch optimized resize event
        window.dispatchEvent(new CustomEvent('hd:optimizedResize', {
            detail: { 
                viewport: { 
                    width: window.innerWidth, 
                    height: window.innerHeight 
                }
            }
        }));
    }

    /**
     * Handle optimized scroll events
     */
    handleOptimizedScroll() {
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;

        // Notify scroll handlers
        this.scrollHandlers.forEach(handler => {
            try {
                handler(scrollX, scrollY);
            } catch (error) {
                console.error('Scroll handler error:', error);
            }
        });

        // Update scroll-based animations
        this.updateScrollAnimations(scrollY);
    }

    /**
     * Handle scroll end
     */
    handleScrollEnd() {
        // Trigger scroll end optimizations
        this.optimizeAfterScroll();
        
        // Dispatch scroll end event
        window.dispatchEvent(new CustomEvent('hd:scrollEnd', {
            detail: { scrollY: window.scrollY, scrollX: window.scrollX }
        }));
    }

    /**
     * Setup memory management
     */
    setupMemoryManagement() {
        // Clean up observers on page unload
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // Clean up on page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseNonCriticalOperations();
            } else {
                this.resumeOperations();
            }
        });

        // Periodic cleanup
        setInterval(() => {
            this.performPeriodicCleanup();
        }, 60000); // Every minute
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        // Preload critical CSS
        this.preloadCSS([
            '/build/assets/css/app.css',
            '/build/assets/css/components.css'
        ]);

        // Preload critical JavaScript
        this.preloadJS([
            '/build/assets/js/app.js'
        ]);

        // Preload critical images
        this.preloadImages([
            // Add critical images here
        ]);
    }

    /**
     * Optimize initial render
     */
    optimizeInitialRender() {
        // Use requestAnimationFrame for non-critical initialization
        requestAnimationFrame(() => {
            this.initializeNonCriticalComponents();
        });

        // Defer heavy operations
        setTimeout(() => {
            this.initializeHeavyComponents();
        }, 100);

        // Mark first render complete
        document.body.classList.add('hd-initial-render-complete');
    }

    /**
     * Public API methods
     */

    /**
     * Register a resize handler
     */
    onResize(handler) {
        this.resizeHandlers.add(handler);
        return () => this.resizeHandlers.delete(handler);
    }

    /**
     * Register a scroll handler
     */
    onScroll(handler) {
        this.scrollHandlers.add(handler);
        return () => this.scrollHandlers.delete(handler);
    }

    /**
     * Observe element for visibility
     */
    observeVisibility(element) {
        if (this.visibilityObserver && element) {
            this.visibilityObserver.observe(element);
        }
    }

    /**
     * Stop observing element
     */
    unobserveVisibility(element) {
        if (this.visibilityObserver && element) {
            this.visibilityObserver.unobserve(element);
        }
    }

    /**
     * Force lazy load an element
     */
    forceLazyLoad(element) {
        this.lazyLoadElement(element);
    }

    /**
     * Get performance metrics
     */
    getMetrics() {
        return {
            observers: this.observers.size,
            lazyLoadQueue: this.lazyLoadQueue.size,
            resizeHandlers: this.resizeHandlers.size,
            scrollHandlers: this.scrollHandlers.size,
            memoryUsage: this.estimateMemoryUsage()
        };
    }

    /**
     * Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
    }

    /**
     * Cleanup all observers and handlers
     */
    cleanup() {
        // Disconnect all observers
        this.observers.forEach(observer => {
            observer.disconnect?.();
        });

        // Clear all sets
        this.observers.clear();
        this.lazyLoadQueue.clear();
        this.resizeHandlers.clear();
        this.scrollHandlers.clear();
    }

    /**
     * Helper methods (implementation details)
     */

    fadeInElement(element) {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.3s ease-in-out';
        
        requestAnimationFrame(() => {
            element.style.opacity = '1';
        });
    }

    initializeComponent(element) {
        const componentType = element.getAttribute('data-component');
        // Component initialization logic
    }

    startAnimation(element) {
        const animationType = element.getAttribute('data-animation');
        // Animation logic
    }

    pauseComponent(element) {
        // Pause logic for heavy components
    }

    loadChart(element) {
        // Chart loading logic
    }

    loadMap(element) {
        // Map loading logic
    }

    loadWidget(element) {
        // Widget loading logic
    }

    loadGenericComponent(element) {
        // Generic component loading
    }

    preloadCSS(urls) {
        urls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'style';
            link.href = url;
            document.head.appendChild(link);
        });
    }

    preloadJS(urls) {
        urls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'script';
            link.href = url;
            document.head.appendChild(link);
        });
    }

    preloadImages(urls) {
        urls.forEach(url => {
            const img = new Image();
            img.src = url;
        });
    }

    initializeNonCriticalComponents() {
        // Non-critical component initialization
    }

    initializeHeavyComponents() {
        // Heavy component initialization
    }

    updateScrollAnimations(scrollY) {
        // Scroll-based animation updates
    }

    optimizeAfterScroll() {
        // Post-scroll optimizations
    }

    pauseNonCriticalOperations() {
        // Pause non-critical operations when page is hidden
    }

    resumeOperations() {
        // Resume operations when page becomes visible
    }

    performPeriodicCleanup() {
        // Periodic cleanup tasks
    }

    estimateMemoryUsage() {
        // Estimate current memory usage
        return {
            observers: this.observers.size * 1000, // Rough estimate
            handlers: (this.resizeHandlers.size + this.scrollHandlers.size) * 100,
            lazyQueue: this.lazyLoadQueue.size * 50
        };
    }

    setupScrollFallback() {
        // Fallback for browsers without IntersectionObserver
        let scrollTimeout;
        
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.checkVisibilityFallback();
            }, 100);
        }, { passive: true });
    }

    checkVisibilityFallback() {
        // Fallback visibility checking
        this.lazyLoadQueue.forEach(element => {
            if (this.isElementVisible(element)) {
                this.lazyLoadElement(element);
                this.lazyLoadQueue.delete(element);
            }
        });
    }

    isElementVisible(element) {
        const rect = element.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    }

    handleOrientationChange() {
        // Handle device orientation changes
        this.handleOptimizedResize();
        
        // Additional mobile-specific optimizations
        if ('ontouchstart' in window) {
            this.optimizeForMobile();
        }
    }

    optimizeForMobile() {
        // Mobile-specific optimizations
        document.body.classList.add('hd-mobile-optimized');
    }
}

// Initialize performance optimizer
let hdPerformanceOptimizer;

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        hdPerformanceOptimizer = new HDPerformanceOptimizer();
        window.hdPerformanceOptimizer = hdPerformanceOptimizer;
    });
} else {
    hdPerformanceOptimizer = new HDPerformanceOptimizer();
    window.hdPerformanceOptimizer = hdPerformanceOptimizer;
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HDPerformanceOptimizer;
}

export default HDPerformanceOptimizer;
