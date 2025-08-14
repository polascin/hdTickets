/**
 * Enhanced Lazy Image Loader
 * Provides intelligent image loading with intersection observer, WebP support, and error handling
 */

class LazyImageLoader {
    constructor(options = {}) {
        this.config = {
            selector: '[data-lazy-src]',
            rootMargin: '50px 0px',
            threshold: 0.01,
            loadingClass: 'lazy-loading',
            loadedClass: 'lazy-loaded',
            errorClass: 'lazy-error',
            placeholderClass: 'lazy-placeholder',
            retryAttempts: 3,
            retryDelay: 1000,
            maxConcurrentLoads: 5,
            enableWebP: true,
            enablePlaceholder: true,
            enableBlur: true,
            enableFadeIn: true,
            enablePrefetch: true,
            fallbackImage: null,
            onLoad: null,
            onError: null,
            onProgress: null,
            ...options
        };
        
        // State management
        this.observer = null;
        this.supportsWebP = null;
        this.loadedImages = new Set();
        this.erroredImages = new Set();
        this.activeLoads = new Set();
        this.retryCount = new Map();
        this.loadingQueue = [];
        this.pausedQueue = [];
        
        // Statistics
        this.stats = {
            totalImages: 0,
            loadedImages: 0,
            erroredImages: 0,
            totalLoadTime: 0,
            averageLoadTime: 0
        };
        
        this.init();
    }
    
    /**
     * Initialize the lazy loader
     */
    async init() {
        try {
            // Check browser support
            if (!('IntersectionObserver' in window)) {
                console.warn('IntersectionObserver not supported, falling back to immediate loading');
                this.loadAllImages();
                return;
            }
            
            // Detect WebP support
            await this.detectWebPSupport();
            
            // Setup observer
            this.setupIntersectionObserver();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Process existing images
            this.processExistingImages();
            
            console.log('🖼️ Lazy Image Loader initialized successfully');
            
        } catch (error) {
            console.error('❌ Failed to initialize Lazy Image Loader:', error);
        }
    }
    
    /**
     * Detect WebP support
     */
    async detectWebPSupport() {
        if (this.supportsWebP !== null) return this.supportsWebP;
        
        return new Promise((resolve) => {
            const webP = new Image();
            webP.onload = webP.onerror = () => {
                this.supportsWebP = webP.height === 2;
                resolve(this.supportsWebP);
            };
            webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
        });
    }
    
    /**
     * Setup IntersectionObserver
     */
    setupIntersectionObserver() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.queueImageLoad(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: this.config.threshold,
            rootMargin: this.config.rootMargin
        });
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle dynamic content
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        this.processNewImages(node);
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Handle window load for critical images
        window.addEventListener('load', () => {
            this.prefetchCriticalImages();
        });
        
        // Handle visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseLoading();
            } else {
                this.resumeLoading();
            }
        });
    }
    
    /**
     * Process existing images in the document
     */
    processExistingImages() {
        const images = document.querySelectorAll(this.config.selector);
        this.stats.totalImages = images.length;
        
        images.forEach((img) => {
            this.setupImage(img);
        });
        
        console.log(`📊 Found ${images.length} lazy images to process`);
    }
    
    /**
     * Process new images added to DOM
     */
    processNewImages(container) {
        const images = container.querySelectorAll ? 
            container.querySelectorAll(this.config.selector) : 
            (container.matches && container.matches(this.config.selector) ? [container] : []);
        
        images.forEach((img) => {
            this.setupImage(img);
            this.stats.totalImages++;
        });
    }
    
    /**
     * Setup individual image for lazy loading
     */
    setupImage(img) {
        if (img.dataset.lazySetup) return; // Already setup
        
        img.dataset.lazySetup = 'true';
        
        // Add loading state
        img.classList.add(this.config.loadingClass);
        
        // Setup placeholder
        if (this.config.enablePlaceholder) {
            this.setupPlaceholder(img);
        }
        
        // Setup blur effect
        if (this.config.enableBlur) {
            img.style.filter = 'blur(5px)';
            img.style.transition = 'filter 0.3s ease';
        }
        
        // Observe for intersection
        this.observer.observe(img);
        
        // Store original dimensions
        if (img.dataset.width && img.dataset.height) {
            img.style.width = img.dataset.width + 'px';
            img.style.height = img.dataset.height + 'px';
        }
    }
    
    /**
     * Setup placeholder for image
     */
    setupPlaceholder(img) {
        const placeholder = this.generatePlaceholder(img);
        
        if (placeholder) {
            img.src = placeholder;
            img.classList.add(this.config.placeholderClass);
        }
    }
    
    /**
     * Generate placeholder based on image dimensions
     */
    generatePlaceholder(img) {
        const width = img.dataset.width || img.getAttribute('width') || 300;
        const height = img.dataset.height || img.getAttribute('height') || 200;
        
        // Generate SVG placeholder
        const svg = `
            <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#f0f0f0"/>
                <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="14" fill="#999" text-anchor="middle" dominant-baseline="middle">
                    Loading...
                </text>
            </svg>
        `;
        
        return `data:image/svg+xml;base64,${btoa(svg)}`;
    }
    
    /**
     * Queue image for loading
     */
    queueImageLoad(img) {
        if (this.loadedImages.has(img) || this.activeLoads.has(img)) {
            return;
        }
        
        this.loadingQueue.push(img);
        this.processLoadingQueue();
    }
    
    /**
     * Process the loading queue
     */
    async processLoadingQueue() {
        while (this.loadingQueue.length > 0 && this.activeLoads.size < this.config.maxConcurrentLoads) {
            const img = this.loadingQueue.shift();
            
            if (!this.loadedImages.has(img) && !this.activeLoads.has(img)) {
                this.loadImage(img);
            }
        }
    }
    
    /**
     * Load individual image
     */
    async loadImage(img) {
        if (this.activeLoads.has(img)) return;
        
        this.activeLoads.add(img);
        const startTime = performance.now();
        
        try {
            const imageSrc = await this.getOptimizedImageSrc(img);
            
            // Preload the image
            const imageElement = new Image();
            
            await new Promise((resolve, reject) => {
                imageElement.onload = () => resolve(imageElement);
                imageElement.onerror = () => reject(new Error('Image load failed'));
                imageElement.src = imageSrc;
            });
            
            // Apply the loaded image
            await this.applyLoadedImage(img, imageSrc, imageElement);
            
            // Track success
            const loadTime = performance.now() - startTime;
            this.trackLoadSuccess(img, loadTime);
            
        } catch (error) {
            console.warn('Failed to load image:', img.dataset.lazySrc, error);
            await this.handleLoadError(img, error);
            
        } finally {
            this.activeLoads.delete(img);
            this.processLoadingQueue(); // Process next in queue
        }
    }
    
    /**
     * Get optimized image source
     */
    async getOptimizedImageSrc(img) {
        let src = img.dataset.lazySrc;
        
        // Add WebP support if available
        if (this.config.enableWebP && this.supportsWebP) {
            const webpSrc = img.dataset.lazySrcWebp || this.convertToWebP(src);
            if (webpSrc && webpSrc !== src) {
                // Test if WebP version exists
                if (await this.imageExists(webpSrc)) {
                    src = webpSrc;
                }
            }
        }
        
        // Add responsive image support
        if (img.dataset.lazySrcset) {
            img.srcset = img.dataset.lazySrcset;
        }
        
        return src;
    }
    
    /**
     * Convert image path to WebP equivalent
     */
    convertToWebP(src) {
        if (!src.match(/\.(jpg|jpeg|png)$/i)) return src;
        return src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
    }
    
    /**
     * Check if image exists
     */
    async imageExists(src) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = src;
        });
    }
    
    /**
     * Apply loaded image to DOM element
     */
    async applyLoadedImage(img, src, loadedImage) {
        // Set the new source
        img.src = src;
        
        // Remove loading state
        img.classList.remove(this.config.loadingClass, this.config.placeholderClass);
        img.classList.add(this.config.loadedClass);
        
        // Remove blur effect
        if (this.config.enableBlur) {
            img.style.filter = 'none';
        }
        
        // Add fade in effect
        if (this.config.enableFadeIn) {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.3s ease';
            
            // Trigger fade in
            requestAnimationFrame(() => {
                img.style.opacity = '1';
            });
        }
        
        // Call success callback
        if (this.config.onLoad) {
            this.config.onLoad(img, src);
        }
        
        console.log(`✅ Loaded: ${src}`);
    }
    
    /**
     * Handle image load error
     */
    async handleLoadError(img, error) {
        const retryCount = this.retryCount.get(img) || 0;
        
        if (retryCount < this.config.retryAttempts) {
            // Retry after delay
            this.retryCount.set(img, retryCount + 1);
            
            await new Promise(resolve => setTimeout(resolve, this.config.retryDelay));
            
            console.log(`🔄 Retrying image load (${retryCount + 1}/${this.config.retryAttempts}): ${img.dataset.lazySrc}`);
            
            return this.loadImage(img);
        }
        
        // Apply fallback
        img.classList.remove(this.config.loadingClass, this.config.placeholderClass);
        img.classList.add(this.config.errorClass);
        
        if (this.config.fallbackImage) {
            img.src = this.config.fallbackImage;
        }
        
        this.erroredImages.add(img);
        this.stats.erroredImages++;
        
        // Call error callback
        if (this.config.onError) {
            this.config.onError(img, error);
        }
        
        console.error(`❌ Failed to load image after ${this.config.retryAttempts} attempts:`, img.dataset.lazySrc, error);
    }
    
    /**
     * Track successful image load
     */
    trackLoadSuccess(img, loadTime) {
        this.loadedImages.add(img);
        this.stats.loadedImages++;
        this.stats.totalLoadTime += loadTime;
        this.stats.averageLoadTime = this.stats.totalLoadTime / this.stats.loadedImages;
        
        if (this.config.onProgress) {
            this.config.onProgress({
                loaded: this.stats.loadedImages,
                total: this.stats.totalImages,
                percentage: (this.stats.loadedImages / this.stats.totalImages) * 100
            });
        }
    }
    
    /**
     * Prefetch critical images
     */
    prefetchCriticalImages() {
        if (!this.config.enablePrefetch) return;
        
        const criticalImages = document.querySelectorAll('[data-lazy-src][data-critical="true"]');
        
        criticalImages.forEach((img) => {
            if (!this.loadedImages.has(img)) {
                this.queueImageLoad(img);
            }
        });
        
        console.log(`🚀 Prefetching ${criticalImages.length} critical images`);
    }
    
    /**
     * Pause loading (when tab is hidden)
     */
    pauseLoading() {
        this.pausedQueue = [...this.loadingQueue];
        this.loadingQueue = [];
        console.log('⏸️ Image loading paused');
    }
    
    /**
     * Resume loading (when tab becomes visible)
     */
    resumeLoading() {
        if (this.pausedQueue.length > 0) {
            this.loadingQueue.push(...this.pausedQueue);
            this.pausedQueue = [];
            this.processLoadingQueue();
            console.log('▶️ Image loading resumed');
        }
    }
    
    /**
     * Load all images immediately (fallback)
     */
    loadAllImages() {
        const images = document.querySelectorAll(this.config.selector);
        
        images.forEach((img) => {
            if (img.dataset.lazySrc) {
                img.src = img.dataset.lazySrc;
                img.classList.add(this.config.loadedClass);
            }
        });
        
        console.log(`🖼️ Loaded ${images.length} images immediately (fallback mode)`);
    }
    
    /**
     * Get loading statistics
     */
    getStats() {
        return {
            ...this.stats,
            successRate: this.stats.totalImages > 0 ? 
                (this.stats.loadedImages / this.stats.totalImages) * 100 : 0,
            errorRate: this.stats.totalImages > 0 ? 
                (this.stats.erroredImages / this.stats.totalImages) * 100 : 0,
            queueSize: this.loadingQueue.length,
            activeLoads: this.activeLoads.size
        };
    }
    
    /**
     * Destroy the lazy loader
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        this.activeLoads.clear();
        this.loadingQueue = [];
        
        console.log('🧹 Lazy image loader destroyed');
    }
}

// Auto-initialize if DOM is ready
if (typeof window !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.lazyImageLoader = new LazyImageLoader();
        });
    } else {
        window.lazyImageLoader = new LazyImageLoader();
    }
}

export default LazyImageLoader;
