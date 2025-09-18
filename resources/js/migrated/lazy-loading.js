/**
 * HD Tickets Lazy Loading System
 * 
 * Advanced lazy loading implementation with intersection observer,
 * image optimization, and progressive content loading.
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 */

class LazyLoader {
    constructor(options = {}) {
        this.options = {
            // Intersection Observer options
            root: null,
            rootMargin: '50px',
            threshold: 0.1,
            
            // Lazy loading selectors
            imageSelector: '[data-lazy-src]',
            backgroundSelector: '[data-lazy-background]',
            iframeSelector: '[data-lazy-iframe]',
            contentSelector: '[data-lazy-content]',
            
            // CSS classes
            loadingClass: 'lazy-loading',
            loadedClass: 'lazy-loaded',
            errorClass: 'lazy-error',
            
            // Performance settings
            loadDelay: 0,
            retryAttempts: 3,
            retryDelay: 1000,
            
            // Placeholder settings
            placeholderColor: '#f3f4f6',
            useBlurredPlaceholders: true,
            generatePlaceholders: true,
            
            // Callbacks
            onLoad: null,
            onError: null,
            onProgress: null,
            
            // Debug mode
            debug: false,
            
            ...options
        };
        
        this.observer = null;
        this.loadedCount = 0;
        this.totalCount = 0;
        this.retryQueue = new Map();
        
        this.init();
    }
    
    /**
     * Initialize lazy loader
     */
    init() {
        if (!this.isIntersectionObserverSupported()) {
            console.warn('[LazyLoader] Intersection Observer not supported, falling back to immediate loading');
            this.fallbackLoad();
            return;
        }
        
        this.createObserver();
        this.observeElements();
        this.setupEventListeners();
        
        if (this.options.debug) {
            console.log('[LazyLoader] Initialized with options:', this.options);
        }
    }
    
    /**
     * Create intersection observer
     */
    createObserver() {
        this.observer = new IntersectionObserver(
            (entries) => this.handleIntersection(entries),
            {
                root: this.options.root,
                rootMargin: this.options.rootMargin,
                threshold: this.options.threshold
            }
        );
    }
    
    /**
     * Observe all lazy loading elements
     */
    observeElements() {
        const elements = this.getAllLazyElements();
        this.totalCount = elements.length;
        
        elements.forEach(element => {
            this.setupElement(element);
            this.observer.observe(element);
        });
        
        if (this.options.debug) {
            console.log(`[LazyLoader] Observing ${this.totalCount} elements`);
        }
    }
    
    /**
     * Get all lazy loading elements
     */
    getAllLazyElements() {
        const selectors = [
            this.options.imageSelector,
            this.options.backgroundSelector,
            this.options.iframeSelector,
            this.options.contentSelector
        ];
        
        return document.querySelectorAll(selectors.join(', '));
    }
    
    /**
     * Setup individual element for lazy loading
     */
    setupElement(element) {
        element.classList.add(this.options.loadingClass);
        
        if (this.options.generatePlaceholders) {
            this.generatePlaceholder(element);
        }
        
        // Store original attributes
        this.storeOriginalAttributes(element);
    }
    
    /**
     * Generate placeholder for element
     */
    generatePlaceholder(element) {
        if (element.hasAttribute('data-lazy-src') && element.tagName === 'IMG') {
            this.generateImagePlaceholder(element);
        } else if (element.hasAttribute('data-lazy-background')) {
            this.generateBackgroundPlaceholder(element);
        }
    }
    
    /**
     * Generate image placeholder
     */
    generateImagePlaceholder(img) {
        const width = img.getAttribute('width') || img.offsetWidth || 300;
        const height = img.getAttribute('height') || img.offsetHeight || 200;
        
        if (this.options.useBlurredPlaceholders && img.hasAttribute('data-lazy-placeholder')) {
            // Use provided low-quality placeholder
            img.src = img.getAttribute('data-lazy-placeholder');
        } else {
            // Generate SVG placeholder
            img.src = this.createSVGPlaceholder(width, height, 'Loading...');
        }
    }
    
    /**
     * Generate background placeholder
     */
    generateBackgroundPlaceholder(element) {
        element.style.backgroundColor = this.options.placeholderColor;
        element.style.backgroundImage = 'none';
    }
    
    /**
     * Create SVG placeholder
     */
    createSVGPlaceholder(width, height, text = '') {
        const svg = `
            <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="${this.options.placeholderColor}"/>
                <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="14" 
                      fill="#9ca3af" text-anchor="middle" dominant-baseline="central">
                    ${text}
                </text>
            </svg>
        `;
        return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
    }
    
    /**
     * Store original attributes for retry functionality
     */
    storeOriginalAttributes(element) {
        const attributes = ['data-lazy-src', 'data-lazy-background', 'data-lazy-iframe', 'data-lazy-content'];
        
        attributes.forEach(attr => {
            if (element.hasAttribute(attr)) {
                element.dataset.originalSource = element.getAttribute(attr);
                element.dataset.lazyType = attr.replace('data-lazy-', '');
            }
        });
    }
    
    /**
     * Handle intersection observer entries
     */
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadElement(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    /**
     * Load individual element
     */
    async loadElement(element) {
        const delay = this.options.loadDelay;
        
        if (delay > 0) {
            await this.wait(delay);
        }
        
        try {
            await this.performLoad(element);
            this.handleLoadSuccess(element);
        } catch (error) {
            this.handleLoadError(element, error);
        }
    }
    
    /**
     * Perform the actual loading based on element type
     */
    performLoad(element) {
        const type = element.dataset.lazyType;
        
        switch (type) {
            case 'src':
                return this.loadImage(element);
            case 'background':
                return this.loadBackground(element);
            case 'iframe':
                return this.loadIframe(element);
            case 'content':
                return this.loadContent(element);
            default:
                throw new Error(`Unknown lazy loading type: ${type}`);
        }
    }
    
    /**
     * Load image element
     */
    loadImage(img) {
        return new Promise((resolve, reject) => {
            const newImg = new Image();
            
            newImg.onload = () => {
                img.src = newImg.src;
                
                // Handle srcset if present
                if (img.hasAttribute('data-lazy-srcset')) {
                    img.srcset = img.getAttribute('data-lazy-srcset');
                    img.removeAttribute('data-lazy-srcset');
                }
                
                resolve();
            };
            
            newImg.onerror = () => {
                reject(new Error(`Failed to load image: ${img.dataset.originalSource}`));
            };
            
            newImg.src = img.dataset.originalSource;
        });
    }
    
    /**
     * Load background image
     */
    loadBackground(element) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            
            img.onload = () => {
                element.style.backgroundImage = `url(${img.src})`;
                resolve();
            };
            
            img.onerror = () => {
                reject(new Error(`Failed to load background image: ${element.dataset.originalSource}`));
            };
            
            img.src = element.dataset.originalSource;
        });
    }
    
    /**
     * Load iframe
     */
    loadIframe(iframe) {
        return new Promise((resolve, reject) => {
            iframe.onload = () => resolve();
            iframe.onerror = () => reject(new Error(`Failed to load iframe: ${iframe.dataset.originalSource}`));
            
            iframe.src = iframe.dataset.originalSource;
        });
    }
    
    /**
     * Load content via AJAX
     */
    async loadContent(element) {
        const url = element.dataset.originalSource;
        
        try {
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const content = await response.text();
            element.innerHTML = content;
            
            // Process any new lazy elements in the loaded content
            this.observeNewElements(element);
            
        } catch (error) {
            throw new Error(`Failed to load content: ${error.message}`);
        }
    }
    
    /**
     * Handle successful load
     */
    handleLoadSuccess(element) {
        element.classList.remove(this.options.loadingClass);
        element.classList.add(this.options.loadedClass);
        
        // Remove data attributes to clean up DOM
        this.cleanupElement(element);
        
        this.loadedCount++;
        this.updateProgress();
        
        if (this.options.onLoad) {
            this.options.onLoad(element);
        }
        
        if (this.options.debug) {
            console.log(`[LazyLoader] Successfully loaded:`, element);
        }
    }
    
    /**
     * Handle load error
     */
    handleLoadError(element, error) {
        const retryKey = this.getElementKey(element);
        const currentAttempts = this.retryQueue.get(retryKey) || 0;
        
        if (currentAttempts < this.options.retryAttempts) {
            this.retryQueue.set(retryKey, currentAttempts + 1);
            
            setTimeout(() => {
                if (this.options.debug) {
                    console.log(`[LazyLoader] Retrying load (attempt ${currentAttempts + 1}):`, element);
                }
                this.loadElement(element);
            }, this.options.retryDelay * (currentAttempts + 1));
            
        } else {
            element.classList.remove(this.options.loadingClass);
            element.classList.add(this.options.errorClass);
            
            this.loadedCount++;
            this.updateProgress();
            
            if (this.options.onError) {
                this.options.onError(element, error);
            }
            
            if (this.options.debug) {
                console.error(`[LazyLoader] Failed to load after ${this.options.retryAttempts} attempts:`, element, error);
            }
        }
    }
    
    /**
     * Update loading progress
     */
    updateProgress() {
        const progress = (this.loadedCount / this.totalCount) * 100;
        
        if (this.options.onProgress) {
            this.options.onProgress(progress, this.loadedCount, this.totalCount);
        }
        
        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('lazyload:progress', {
            detail: { progress, loaded: this.loadedCount, total: this.totalCount }
        }));
        
        if (this.loadedCount === this.totalCount) {
            document.dispatchEvent(new CustomEvent('lazyload:complete'));
        }
    }
    
    /**
     * Cleanup element after loading
     */
    cleanupElement(element) {
        const attributesToRemove = [
            'data-lazy-src',
            'data-lazy-background',
            'data-lazy-iframe',
            'data-lazy-content',
            'data-lazy-srcset',
            'data-lazy-placeholder',
            'data-original-source',
            'data-lazy-type'
        ];
        
        attributesToRemove.forEach(attr => {
            element.removeAttribute(attr);
        });
    }
    
    /**
     * Observe new elements added to the DOM
     */
    observeNewElements(container) {
        const newElements = container.querySelectorAll(
            `${this.options.imageSelector}, ${this.options.backgroundSelector}, ${this.options.iframeSelector}, ${this.options.contentSelector}`
        );
        
        newElements.forEach(element => {
            if (!element.classList.contains(this.options.loadedClass)) {
                this.setupElement(element);
                this.observer.observe(element);
                this.totalCount++;
            }
        });
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle dynamic content
        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        this.observeNewElements(node);
                    }
                });
            });
        });
        
        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.resumeLoading();
            }
        });
    }
    
    /**
     * Resume loading when page becomes visible
     */
    resumeLoading() {
        const stillLoading = document.querySelectorAll(`.${this.options.loadingClass}`);
        stillLoading.forEach(element => {
            if (this.isElementInViewport(element)) {
                this.loadElement(element);
                this.observer.unobserve(element);
            }
        });
    }
    
    /**
     * Check if element is in viewport (fallback)
     */
    isElementInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    /**
     * Check if Intersection Observer is supported
     */
    isIntersectionObserverSupported() {
        return 'IntersectionObserver' in window;
    }
    
    /**
     * Fallback for browsers without Intersection Observer
     */
    fallbackLoad() {
        const elements = this.getAllLazyElements();
        elements.forEach(element => this.loadElement(element));
    }
    
    /**
     * Get unique key for element
     */
    getElementKey(element) {
        return element.dataset.originalSource || element.outerHTML.substring(0, 100);
    }
    
    /**
     * Utility: Wait for specified time
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Public API: Load all remaining elements immediately
     */
    loadAll() {
        const stillLoading = document.querySelectorAll(`.${this.options.loadingClass}`);
        stillLoading.forEach(element => {
            this.loadElement(element);
            this.observer.unobserve(element);
        });
    }
    
    /**
     * Public API: Refresh and observe new elements
     */
    refresh() {
        this.observeElements();
    }
    
    /**
     * Public API: Get loading statistics
     */
    getStats() {
        return {
            total: this.totalCount,
            loaded: this.loadedCount,
            pending: this.totalCount - this.loadedCount,
            progress: (this.loadedCount / this.totalCount) * 100,
            retryQueue: this.retryQueue.size
        };
    }
    
    /**
     * Public API: Destroy lazy loader
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        // Remove loading classes
        const loadingElements = document.querySelectorAll(`.${this.options.loadingClass}`);
        loadingElements.forEach(element => {
            element.classList.remove(this.options.loadingClass);
        });
        
        if (this.options.debug) {
            console.log('[LazyLoader] Destroyed');
        }
    }
}

/**
 * Auto-initialize lazy loader when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialize with default settings
    window.lazyLoader = new LazyLoader({
        debug: localStorage.getItem('debug') === 'true'
    });
    
    // Expose global methods for easy access
    window.lazyLoad = {
        loadAll: () => window.lazyLoader.loadAll(),
        refresh: () => window.lazyLoader.refresh(),
        getStats: () => window.lazyLoader.getStats()
    };
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LazyLoader;
}
