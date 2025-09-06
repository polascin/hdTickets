/**
 * Asset Optimizer for HD Tickets
 * Dynamic asset loading, lazy loading, and performance optimization utilities
 */

class AssetOptimizer {
    constructor() {
        this.loadedAssets = new Set();
        this.loadingPromises = new Map();
        this.performanceObserver = null;
        this.intersectionObserver = null;
        this.connectionType = null;
        this.init();
    }

    init() {
        this.detectConnection();
        this.setupPerformanceMonitoring();
        this.setupLazyLoading();
        this.optimizeExistingAssets();
        this.bindEvents();
    }

    /**
     * Detect network connection type for adaptive loading
     */
    detectConnection() {
        if ('connection' in navigator) {
            this.connectionType = navigator.connection;
            
            navigator.connection.addEventListener('change', () => {
                this.handleConnectionChange();
            });
            
            console.log('Connection detected:', {
                effectiveType: this.connectionType.effectiveType,
                downlink: this.connectionType.downlink,
                rtt: this.connectionType.rtt,
                saveData: this.connectionType.saveData
            });
        }
    }

    /**
     * Handle connection type changes
     */
    handleConnectionChange() {
        const connection = this.connectionType;
        
        if (connection.saveData) {
            // Data saver mode: reduce asset loading
            this.enableDataSaverMode();
        } else if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
            // Slow connection: prioritize critical assets only
            this.enableSlowConnectionMode();
        } else if (connection.effectiveType === '4g' || connection.effectiveType === 'wifi') {
            // Fast connection: load additional assets
            this.enableFastConnectionMode();
        }
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        if ('PerformanceObserver' in window) {
            // Monitor resource loading performance
            this.performanceObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    this.trackResourcePerformance(entry);
                }
            });
            
            this.performanceObserver.observe({ 
                entryTypes: ['resource', 'navigation', 'paint'] 
            });
        }
        
        // Monitor Core Web Vitals
        this.monitorWebVitals();
    }

    /**
     * Monitor Core Web Vitals
     */
    monitorWebVitals() {
        // Largest Contentful Paint (LCP)
        if ('PerformanceObserver' in window) {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                console.log('LCP:', lastEntry.startTime, 'ms');
                
                // Track LCP for analytics
                this.trackWebVital('LCP', lastEntry.startTime);
            });
            
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        }
        
        // First Input Delay (FID)
        if ('PerformanceObserver' in window) {
            const fidObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    console.log('FID:', entry.processingStart - entry.startTime, 'ms');
                    this.trackWebVital('FID', entry.processingStart - entry.startTime);
                }
            });
            
            fidObserver.observe({ entryTypes: ['first-input'] });
        }
        
        // Cumulative Layout Shift (CLS)
        let clsValue = 0;
        if ('PerformanceObserver' in window) {
            const clsObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                }
                
                console.log('CLS:', clsValue);
                this.trackWebVital('CLS', clsValue);
            });
            
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }
    }

    /**
     * Track Web Vital metrics
     */
    trackWebVital(name, value) {
        // Send to analytics
        if (window.gtag) {
            gtag('event', 'web_vital', {
                name: name,
                value: Math.round(value),
                custom_map: {
                    custom_parameter_1: window.location.pathname
                }
            });
        }
        
        // Log performance issues
        if (name === 'LCP' && value > 2500) {
            console.warn('Poor LCP detected:', value, 'ms');
        } else if (name === 'FID' && value > 100) {
            console.warn('Poor FID detected:', value, 'ms');
        } else if (name === 'CLS' && value > 0.1) {
            console.warn('Poor CLS detected:', value);
        }
    }

    /**
     * Setup intersection observer for lazy loading
     */
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.intersectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.handleLazyLoad(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });
            
            // Observe lazy-loadable elements
            this.observeLazyElements();
        }
    }

    /**
     * Observe elements for lazy loading
     */
    observeLazyElements() {
        // Observe images with data-src
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.intersectionObserver.observe(img);
        });
        
        // Observe elements with data-lazy-load
        document.querySelectorAll('[data-lazy-load]').forEach(element => {
            this.intersectionObserver.observe(element);
        });
        
        // Observe CSS/JS assets with data-lazy-css or data-lazy-js
        document.querySelectorAll('[data-lazy-css], [data-lazy-js]').forEach(element => {
            this.intersectionObserver.observe(element);
        });
    }

    /**
     * Handle lazy loading of elements
     */
    handleLazyLoad(element) {
        if (element.tagName === 'IMG') {
            this.lazyLoadImage(element);
        } else if (element.dataset.lazyCss) {
            this.lazyLoadCSS(element.dataset.lazyCss);
        } else if (element.dataset.lazyJs) {
            this.lazyLoadJS(element.dataset.lazyJs);
        } else if (element.dataset.lazyLoad) {
            this.lazyLoadGeneric(element);
        }
        
        this.intersectionObserver.unobserve(element);
    }

    /**
     * Lazy load images
     */
    lazyLoadImage(img) {
        const src = img.dataset.src;
        const srcset = img.dataset.srcset;
        
        if (src) {
            img.src = src;
        }
        if (srcset) {
            img.srcset = srcset;
        }
        
        img.classList.add('loading');
        
        img.onload = () => {
            img.classList.remove('loading');
            img.classList.add('loaded');
        };
        
        img.onerror = () => {
            img.classList.remove('loading');
            img.classList.add('error');
            console.error('Failed to load image:', src);
        };
    }

    /**
     * Lazy load CSS files
     */
    async lazyLoadCSS(href) {
        if (this.loadedAssets.has(href)) {
            return Promise.resolve();
        }
        
        if (this.loadingPromises.has(href)) {
            return this.loadingPromises.get(href);
        }
        
        const promise = new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            
            link.onload = () => {
                this.loadedAssets.add(href);
                console.log('Lazy loaded CSS:', href);
                resolve();
            };
            
            link.onerror = () => {
                console.error('Failed to load CSS:', href);
                reject(new Error(`Failed to load CSS: ${href}`));
            };
            
            document.head.appendChild(link);
        });
        
        this.loadingPromises.set(href, promise);
        return promise;
    }

    /**
     * Lazy load JavaScript files
     */
    async lazyLoadJS(src, isModule = false) {
        if (this.loadedAssets.has(src)) {
            return Promise.resolve();
        }
        
        if (this.loadingPromises.has(src)) {
            return this.loadingPromises.get(src);
        }
        
        const promise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            
            if (isModule) {
                script.type = 'module';
            }
            
            script.onload = () => {
                this.loadedAssets.add(src);
                console.log('Lazy loaded JS:', src);
                resolve();
            };
            
            script.onerror = () => {
                console.error('Failed to load JS:', src);
                reject(new Error(`Failed to load JS: ${src}`));
            };
            
            document.head.appendChild(script);
        });
        
        this.loadingPromises.set(src, promise);
        return promise;
    }

    /**
     * Generic lazy loading handler
     */
    lazyLoadGeneric(element) {
        const loadFunction = element.dataset.lazyLoad;
        
        if (window[loadFunction] && typeof window[loadFunction] === 'function') {
            try {
                window[loadFunction](element);
            } catch (error) {
                console.error('Error in lazy load function:', loadFunction, error);
            }
        }
    }

    /**
     * Optimize existing assets
     */
    optimizeExistingAssets() {
        // Add loading attributes to images
        document.querySelectorAll('img:not([loading])').forEach(img => {
            // Skip images that are above the fold
            const rect = img.getBoundingClientRect();
            if (rect.top > window.innerHeight) {
                img.loading = 'lazy';
            }
        });
        
        // Optimize iframes
        document.querySelectorAll('iframe:not([loading])').forEach(iframe => {
            iframe.loading = 'lazy';
        });
        
        // Add async to non-critical scripts
        document.querySelectorAll('script[src]:not([async]):not([defer])').forEach(script => {
            if (!script.src.includes('critical') && !script.src.includes('alpine')) {
                script.async = true;
            }
        });
    }

    /**
     * Enable data saver mode
     */
    enableDataSaverMode() {
        console.log('Data saver mode enabled');
        
        // Remove non-essential prefetch links
        document.querySelectorAll('link[rel="prefetch"]:not([data-essential])').forEach(link => {
            link.remove();
        });
        
        // Disable image lazy loading for better perceived performance
        document.querySelectorAll('img[data-src]').forEach(img => {
            if (!img.dataset.essential) {
                img.style.display = 'none';
            }
        });
        
        // Add data-saver class to body
        document.body.classList.add('data-saver-mode');
    }

    /**
     * Enable slow connection mode
     */
    enableSlowConnectionMode() {
        console.log('Slow connection mode enabled');
        
        // Increase intersection observer root margin for earlier loading
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
            this.intersectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.handleLazyLoad(entry.target);
                    }
                });
            }, {
                rootMargin: '200px 0px', // Load earlier
                threshold: 0.1
            });
            this.observeLazyElements();
        }
        
        document.body.classList.add('slow-connection');
    }

    /**
     * Enable fast connection mode
     */
    enableFastConnectionMode() {
        console.log('Fast connection mode enabled');
        
        // Preload additional resources
        const additionalResources = [
            { href: '/css/dashboard.css', as: 'style' },
            { href: '/js/charts.js', as: 'script' },
            { href: '/css/components.css', as: 'style' }
        ];
        
        additionalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = resource.href;
            link.as = resource.as;
            document.head.appendChild(link);
        });
        
        document.body.classList.add('fast-connection');
    }

    /**
     * Track resource performance
     */
    trackResourcePerformance(entry) {
        if (entry.entryType === 'resource') {
            const duration = entry.responseEnd - entry.startTime;
            
            // Log slow resources
            if (duration > 1000) {
                console.warn('Slow resource detected:', {
                    name: entry.name,
                    duration: Math.round(duration),
                    type: entry.initiatorType
                });
            }
            
            // Track to analytics
            if (window.gtag && duration > 500) {
                gtag('event', 'slow_resource', {
                    resource_name: entry.name,
                    resource_duration: Math.round(duration),
                    resource_type: entry.initiatorType
                });
            }
        }
    }

    /**
     * Preload critical resources
     */
    preloadCritical(resources) {
        resources.forEach(resource => {
            if (this.loadedAssets.has(resource.href)) {
                return;
            }
            
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as;
            
            if (resource.type) {
                link.type = resource.type;
            }
            
            if (resource.crossorigin) {
                link.crossOrigin = resource.crossorigin;
            }
            
            document.head.appendChild(link);
        });
    }

    /**
     * Load assets dynamically based on user interaction
     */
    async loadOnInteraction(assetUrl, eventType = 'click') {
        return new Promise((resolve) => {
            const loadAsset = () => {
                if (assetUrl.endsWith('.css')) {
                    this.lazyLoadCSS(assetUrl).then(resolve);
                } else if (assetUrl.endsWith('.js')) {
                    this.lazyLoadJS(assetUrl).then(resolve);
                }
                
                // Remove event listeners after first interaction
                document.removeEventListener(eventType, loadAsset);
                document.removeEventListener('touchstart', loadAsset);
            };
            
            document.addEventListener(eventType, loadAsset, { once: true });
            document.addEventListener('touchstart', loadAsset, { once: true });
        });
    }

    /**
     * Bind events
     */
    bindEvents() {
        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.onPageVisible();
            }
        });
        
        // Before page unload
        window.addEventListener('beforeunload', () => {
            this.onPageUnload();
        });
        
        // Page loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.onPageLoaded();
            });
        } else {
            this.onPageLoaded();
        }
    }

    /**
     * Handle page visibility
     */
    onPageVisible() {
        // Resume resource loading if paused
        this.observeLazyElements();
    }

    /**
     * Handle page unload
     */
    onPageUnload() {
        // Send performance data
        if ('sendBeacon' in navigator && window.gtag) {
            const performanceData = {
                loadTime: performance.now(),
                loadedAssets: this.loadedAssets.size,
                url: window.location.href
            };
            
            navigator.sendBeacon('/api/performance', JSON.stringify(performanceData));
        }
    }

    /**
     * Handle page loaded
     */
    onPageLoaded() {
        // Mark critical path as loaded
        document.body.classList.add('assets-optimized');
        
        // Start observing new elements that might be added dynamically
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        this.optimizeNewElement(node);
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Optimize newly added elements
     */
    optimizeNewElement(element) {
        // Add loading attributes to new images
        if (element.tagName === 'IMG' && !element.loading) {
            element.loading = 'lazy';
        }
        
        // Observe new lazy-loadable elements
        if (element.dataset?.src || element.dataset?.lazyLoad) {
            this.intersectionObserver?.observe(element);
        }
        
        // Find and optimize child elements
        element.querySelectorAll?.('img:not([loading])').forEach(img => {
            img.loading = 'lazy';
        });
        
        element.querySelectorAll?.('[data-src], [data-lazy-load]').forEach(el => {
            this.intersectionObserver?.observe(el);
        });
    }

    /**
     * Public API methods
     */
    getLoadedAssets() {
        return Array.from(this.loadedAssets);
    }

    getPerformanceMetrics() {
        return {
            navigation: performance.getEntriesByType('navigation')[0],
            resources: performance.getEntriesByType('resource'),
            paint: performance.getEntriesByType('paint')
        };
    }

    clearCache() {
        this.loadedAssets.clear();
        this.loadingPromises.clear();
    }
}

// Initialize asset optimizer
const assetOptimizer = new AssetOptimizer();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AssetOptimizer;
}

// Make available globally
window.AssetOptimizer = AssetOptimizer;
window.assetOptimizer = assetOptimizer;

export default AssetOptimizer;
