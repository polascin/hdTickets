/**
 * Resource Hints Utility
 * Implements preconnect, prefetch, and preload for better performance
 */

class ResourceHints {
    constructor() {
        this.preloadedResources = new Set();
        this.prefetchedResources = new Set();
        this.preconnectedOrigins = new Set();
        
        // Configuration
        this.config = {
            maxPreloadSize: 50 * 1024 * 1024, // 50MB
            prefetchDelay: 2000, // 2 seconds
            preconnectTimeout: 5000, // 5 seconds
            enableAutomaticHints: true,
            enableIntersectionObserver: true,
            observerRootMargin: '100px',
            observerThreshold: 0.1
        };
        
        this.init();
    }
    
    /**
     * Initialize resource hints system
     */
    init() {
        console.log('🔗 Initializing Resource Hints system...');
        
        // Setup automatic resource discovery
        if (this.config.enableAutomaticHints) {
            this.setupAutomaticHints();
        }
        
        // Setup intersection observer for lazy preloading
        if (this.config.enableIntersectionObserver && 'IntersectionObserver' in window) {
            this.setupIntersectionObserver();
        }
        
        // Preconnect to critical origins
        this.preconnectCriticalOrigins();
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('✅ Resource Hints system initialized');
    }
    
    /**
     * Preconnect to external origins
     */
    preconnect(origin, crossorigin = false) {
        if (this.preconnectedOrigins.has(origin)) {
            return;
        }
        
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = origin;
        
        if (crossorigin) {
            link.crossOrigin = 'anonymous';
        }
        
        // Add timeout to avoid blocking
        const timeout = setTimeout(() => {
            console.warn(`Preconnect timeout for ${origin}`);
        }, this.config.preconnectTimeout);
        
        link.onload = link.onerror = () => {
            clearTimeout(timeout);
        };
        
        document.head.appendChild(link);
        this.preconnectedOrigins.add(origin);
        
        console.log(`🔗 Preconnected to: ${origin}${crossorigin ? ' (crossorigin)' : ''}`);\
    }
    
    /**
     * Preload critical resources
     */
    preload(href, as, options = {}) {
        if (this.preloadedResources.has(href)) {
            return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = href;
            link.as = as;
            
            // Optional attributes
            if (options.type) link.type = options.type;
            if (options.crossorigin) link.crossOrigin = options.crossorigin;
            if (options.integrity) link.integrity = options.integrity;
            if (options.media) link.media = options.media;
            
            // Event handlers
            link.onload = () => {
                this.preloadedResources.add(href);
                console.log(`⚡ Preloaded: ${href} (${as})`);\
                resolve(link);
            };
            
            link.onerror = (error) => {
                console.error(`❌ Failed to preload: ${href}`, error);
                reject(error);
            };
            
            document.head.appendChild(link);
        });
    }
    
    /**
     * Prefetch resources for future navigation
     */
    prefetch(href, options = {}) {
        if (this.prefetchedResources.has(href)) {
            return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            // Delay prefetch to avoid competing with critical resources
            const delay = options.delay || this.config.prefetchDelay;
            
            setTimeout(() => {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = href;
                
                if (options.crossorigin) link.crossOrigin = options.crossorigin;
                if (options.integrity) link.integrity = options.integrity;
                
                link.onload = () => {
                    this.prefetchedResources.add(href);
                    console.log(`📦 Prefetched: ${href}`);\
                    resolve(link);
                };
                
                link.onerror = (error) => {
                    console.error(`❌ Failed to prefetch: ${href}`, error);
                    reject(error);
                };
                
                document.head.appendChild(link);
            }, delay);
        });
    }
    
    /**
     * Preload images with WebP detection
     */
    async preloadImage(src, options = {}) {
        try {
            // Convert to WebP if supported
            const optimizedSrc = await this.getOptimizedImageSrc(src);
            
            return this.preload(optimizedSrc, 'image', {
                crossorigin: options.crossorigin || 'anonymous',
                ...options
            });
        } catch (error) {
            console.error('Failed to preload image:', src, error);
            throw error;
        }
    }
    
    /**
     * Preload fonts
     */
    preloadFont(href, options = {}) {
        return this.preload(href, 'font', {
            type: options.type || 'font/woff2',
            crossorigin: 'anonymous',
            ...options
        });
    }
    
    /**
     * Preload CSS
     */
    preloadCSS(href, options = {}) {
        return this.preload(href, 'style', {
            type: 'text/css',
            ...options
        });
    }
    
    /**
     * Preload JavaScript
     */
    preloadScript(href, options = {}) {
        return this.preload(href, 'script', {
            type: 'application/javascript',
            ...options
        });
    }
    
    /**
     * Get optimized image source (WebP if supported)
     */
    async getOptimizedImageSrc(src) {
        // Check if WebP is supported
        const supportsWebP = await this.detectWebPSupport();
        
        if (supportsWebP && !src.includes('.webp')) {
            const webpSrc = src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
            
            // Check if WebP version exists
            const webpExists = await this.checkImageExists(webpSrc);
            if (webpExists) {
                return webpSrc;
            }
        }
        
        return src;
    }
    
    /**
     * Detect WebP support
     */
    detectWebPSupport() {
        if (this.webpSupport !== undefined) {
            return Promise.resolve(this.webpSupport);
        }
        
        return new Promise((resolve) => {
            const webP = new Image();
            webP.onload = webP.onerror = () => {
                this.webpSupport = webP.height === 2;
                resolve(this.webpSupport);
            };
            webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
        });
    }
    
    /**
     * Check if image exists
     */
    checkImageExists(src) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = src;
        });
    }
    
    /**
     * Setup automatic resource hints
     */
    setupAutomaticHints() {
        // Preconnect to common external origins
        const commonOrigins = [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://api.hdtickets.local' // API server
        ];
        
        commonOrigins.forEach(origin => this.preconnect(origin, true));
        
        // Preload critical fonts on idle
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(() => {
                this.preloadCriticalFonts();
            });
        }
        
        // Prefetch next likely pages
        this.setupIntelligentPrefetching();
    }
    
    /**
     * Preload critical fonts
     */
    preloadCriticalFonts() {
        const criticalFonts = [
            '/fonts/inter-var-latin.woff2',
            '/fonts/roboto-regular-latin.woff2'
        ];
        
        criticalFonts.forEach(font => {
            this.preloadFont(font).catch(err => {
                console.warn(`Font preload failed: ${font}`, err);
            });
        });
    }
    
    /**
     * Setup intelligent prefetching based on user behavior
     */
    setupIntelligentPrefetching() {
        // Track link hovers for prefetching
        document.addEventListener('mouseover', (event) => {
            if (event.target.tagName === 'A' && event.target.href) {
                const href = event.target.href;
                
                // Only prefetch internal links
                if (this.isInternalLink(href)) {
                    // Debounce hover events
                    clearTimeout(this.hoverTimeout);
                    this.hoverTimeout = setTimeout(() => {
                        this.prefetch(href, { delay: 100 });
                    }, 200);
                }
            }
        }, { passive: true });
        
        // Track focus for keyboard navigation
        document.addEventListener('focusin', (event) => {
            if (event.target.tagName === 'A' && event.target.href) {
                const href = event.target.href;
                
                if (this.isInternalLink(href)) {
                    this.prefetch(href, { delay: 500 });
                }
            }
        }, { passive: true });
    }
    
    /**
     * Setup intersection observer for lazy preloading
     */
    setupIntersectionObserver() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    
                    // Preload images
                    if (element.tagName === 'IMG' && element.dataset.preload) {
                        const src = element.dataset.preload;
                        this.preloadImage(src);
                        this.observer.unobserve(element);
                    }
                    
                    // Preload links
                    if (element.tagName === 'A' && element.dataset.preload) {
                        const href = element.href || element.dataset.preload;
                        if (this.isInternalLink(href)) {
                            this.prefetch(href);
                        }
                        this.observer.unobserve(element);
                    }
                }
            });
        }, {
            rootMargin: this.config.observerRootMargin,
            threshold: this.config.observerThreshold
        });
        
        // Observe elements with preload data attributes
        this.observePreloadElements();
    }
    
    /**
     * Observe elements for lazy preloading
     */
    observePreloadElements() {
        const elements = document.querySelectorAll('[data-preload]');
        elements.forEach(element => this.observer.observe(element));
    }
    
    /**
     * Preconnect to critical origins
     */
    preconnectCriticalOrigins() {
        // Get origins from current page
        const links = document.querySelectorAll('link[href], script[src], img[src]');
        const origins = new Set();
        
        links.forEach(element => {
            const url = element.href || element.src;
            if (url && !this.isInternalLink(url)) {
                try {
                    const origin = new URL(url).origin;
                    origins.add(origin);
                } catch (error) {
                    // Invalid URL, ignore
                }
            }
        });
        
        // Preconnect to external origins
        origins.forEach(origin => this.preconnect(origin, true));
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle dynamic content
        const observer = new MutationObserver((mutations) => {
            let needsReobservation = false;
            
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check for new preload elements
                        const preloadElements = node.querySelectorAll('[data-preload]');
                        if (preloadElements.length > 0) {
                            preloadElements.forEach(el => this.observer?.observe(el));
                            needsReobservation = true;
                        }
                        
                        // Check if the node itself has preload attribute
                        if (node.dataset && node.dataset.preload && this.observer) {
                            this.observer.observe(node);
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Pause prefetching when tab is hidden
                console.log('📱 Page hidden - pausing resource hints');
            } else {
                // Resume prefetching when tab is visible
                console.log('📱 Page visible - resuming resource hints');
            }
        });
    }
    
    /**
     * Check if link is internal
     */
    isInternalLink(href) {
        try {
            const url = new URL(href, window.location.origin);
            return url.origin === window.location.origin;
        } catch (error) {
            return false;
        }
    }
    
    /**
     * Preload route-specific resources
     */
    preloadRouteResources(routeName) {
        const routeResources = {
            dashboard: [
                '/css/dashboard.css',
                '/js/charts.js',
                '/images/dashboard-bg.webp'
            ],
            analytics: [
                '/css/analytics.css',
                '/js/analytics-charts.js'
            ],
            admin: [
                '/css/admin.css',
                '/js/admin-tools.js'
            ]
        };
        
        const resources = routeResources[routeName] || [];
        
        resources.forEach(async (resource) => {
            try {
                if (resource.endsWith('.css')) {
                    await this.preloadCSS(resource);
                } else if (resource.endsWith('.js')) {
                    await this.preloadScript(resource);
                } else if (resource.match(/\.(jpg|jpeg|png|webp)$/)) {
                    await this.preloadImage(resource);
                }
            } catch (error) {
                console.warn(`Failed to preload resource: ${resource}`, error);
            }
        });
    }
    
    /**
     * Get statistics
     */
    getStats() {
        return {
            preloadedResources: this.preloadedResources.size,
            prefetchedResources: this.prefetchedResources.size,
            preconnectedOrigins: this.preconnectedOrigins.size,
            webpSupported: this.webpSupport
        };
    }
    
    /**
     * Clear all resource hints
     */
    clear() {
        // Remove link elements
        const linkElements = document.querySelectorAll('link[rel="preload"], link[rel="prefetch"], link[rel="preconnect"]');
        linkElements.forEach(link => link.remove());
        
        // Clear sets
        this.preloadedResources.clear();
        this.prefetchedResources.clear();
        this.preconnectedOrigins.clear();
        
        // Disconnect observer
        if (this.observer) {
            this.observer.disconnect();
        }
        
        console.log('🧹 Resource hints cleared');
    }
}

// Initialize global instance
if (typeof window !== 'undefined') {
    window.resourceHints = new ResourceHints();
    
    // Add utility methods to global namespace
    window.preconnect = (origin, crossorigin) => window.resourceHints.preconnect(origin, crossorigin);
    window.preload = (href, as, options) => window.resourceHints.preload(href, as, options);
    window.prefetch = (href, options) => window.resourceHints.prefetch(href, options);
}

export default ResourceHints;
