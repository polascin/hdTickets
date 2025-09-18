/**
 * HD Tickets Lazy Loading System
 * 
 * Provides efficient lazy loading for:
 * - Images with placeholder and fade-in effects
 * - Heavy JavaScript components
 * - CSS resources for above-the-fold optimization
 * - Dynamic content sections
 */

(function(window, document) {
    'use strict';

    const LazyLoader = {
        // Configuration
        config: {
            imageSelector: 'img[data-src], img[loading="lazy"]',
            componentSelector: '[data-lazy-component]',
            rootMargin: '50px',
            threshold: 0.1,
            placeholderColor: '#e5e7eb',
            fadeInDuration: 300,
            enableLogging: true
        },

        // State management
        imageObserver: null,
        componentObserver: null,
        loadedImages: new Set(),
        loadedComponents: new Set(),
        loadingComponents: new Map(),

        // Initialize lazy loading system
        init: function() {
            if (this.config.enableLogging) {
                console.log('🖼️ HD Tickets Lazy Loading initialized');
            }

            this.setupIntersectionObserver();
            this.setupImageLazyLoading();
            this.setupComponentLazyLoading();
            this.setupCriticalResourceHints();
            this.handleInitialLoad();

            // Re-scan for new elements after dynamic content changes
            this.setupMutationObserver();
        },

        // Setup Intersection Observer for efficient viewport detection
        setupIntersectionObserver: function() {
            if (!('IntersectionObserver' in window)) {
                console.warn('IntersectionObserver not supported, using fallback');
                this.setupFallbackLoading();
                return;
            }

            // Observer for images
            this.imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.imageObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: this.config.rootMargin,
                threshold: this.config.threshold
            });

            // Observer for components
            this.componentObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadComponent(entry.target);
                        this.componentObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: this.config.rootMargin,
                threshold: this.config.threshold
            });
        },

        // Setup image lazy loading
        setupImageLazyLoading: function() {
            const images = document.querySelectorAll(this.config.imageSelector);
            
            images.forEach(img => {
                if (this.loadedImages.has(img)) return;

                // Set up placeholder
                this.setupImagePlaceholder(img);

                // Add to observer
                if (this.imageObserver) {
                    this.imageObserver.observe(img);
                } else {
                    // Fallback for older browsers
                    this.loadImage(img);
                }
            });
        },

        // Setup component lazy loading
        setupComponentLazyLoading: function() {
            const components = document.querySelectorAll(this.config.componentSelector);
            
            components.forEach(component => {
                if (this.loadedComponents.has(component)) return;

                // Set up loading placeholder
                this.setupComponentPlaceholder(component);

                // Add to observer
                if (this.componentObserver) {
                    this.componentObserver.observe(component);
                } else {
                    // Fallback for older browsers
                    this.loadComponent(component);
                }
            });
        },

        // Set up image placeholder
        setupImagePlaceholder: function(img) {
            const width = img.getAttribute('width') || img.dataset.width;
            const height = img.getAttribute('height') || img.dataset.height;
            const aspectRatio = img.dataset.aspectRatio;

            // Create placeholder dimensions
            if (width && height) {
                img.style.width = width + (width.includes('%') ? '' : 'px');
                img.style.height = height + (height.includes('%') ? '' : 'px');
            } else if (aspectRatio) {
                img.style.aspectRatio = aspectRatio;
            }

            // Set placeholder background
            if (!img.src || img.src === window.location.href) {
                img.style.backgroundColor = this.config.placeholderColor;
                img.style.backgroundImage = this.generatePlaceholderPattern();
                img.style.backgroundSize = 'cover';
                img.style.backgroundPosition = 'center';
            }

            // Add loading class
            img.classList.add('lazy-loading');
        },

        // Set up component placeholder
        setupComponentPlaceholder: function(component) {
            const placeholderType = component.dataset.placeholderType || 'skeleton';
            const minHeight = component.dataset.minHeight || '200px';

            component.style.minHeight = minHeight;
            component.classList.add('lazy-component-loading');

            if (placeholderType === 'skeleton') {
                component.innerHTML = this.createSkeletonPlaceholder(component);
            } else if (placeholderType === 'spinner') {
                component.innerHTML = this.createSpinnerPlaceholder();
            } else {
                component.innerHTML = '<div class="lazy-placeholder">Loading...</div>';
            }
        },

        // Load image with error handling and performance tracking
        loadImage: function(img) {
            if (this.loadedImages.has(img)) return;

            const startTime = performance.now();
            const src = img.dataset.src || img.src;
            const srcset = img.dataset.srcset;

            if (!src) return;

            // Create new image for preloading
            const imageLoader = new Image();
            
            imageLoader.onload = () => {
                // Set actual source
                img.src = src;
                if (srcset) {
                    img.srcset = srcset;
                }

                // Remove placeholder styling
                img.style.backgroundColor = '';
                img.style.backgroundImage = '';
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');

                // Fade in effect
                this.fadeInImage(img);

                // Track loading time
                const loadTime = performance.now() - startTime;
                if (this.config.enableLogging && loadTime > 500) {
                    console.log(`Image loaded in ${loadTime.toFixed(0)}ms:`, src);
                }

                this.loadedImages.add(img);

                // Dispatch load event
                img.dispatchEvent(new CustomEvent('lazyloaded', {
                    detail: { loadTime: loadTime }
                }));
            };

            imageLoader.onerror = () => {
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-error');
                
                // Set error placeholder
                img.alt = img.alt || 'Failed to load image';
                img.style.backgroundColor = '#fee2e2';
                img.style.backgroundImage = 'none';
                
                console.warn('Failed to load image:', src);
            };

            // Start loading
            imageLoader.src = src;
            if (srcset) {
                imageLoader.srcset = srcset;
            }
        },

        // Load component dynamically
        loadComponent: function(component) {
            if (this.loadedComponents.has(component)) return;
            if (this.loadingComponents.has(component)) return;

            const componentName = component.dataset.lazyComponent;
            const componentScript = component.dataset.componentScript;
            const componentStyle = component.dataset.componentStyle;
            const componentData = component.dataset.componentData;

            if (!componentName) return;

            this.loadingComponents.set(component, Date.now());

            // Load component script and styles
            Promise.all([
                componentScript ? this.loadScript(componentScript) : Promise.resolve(),
                componentStyle ? this.loadStylesheet(componentStyle) : Promise.resolve()
            ]).then(() => {
                return this.initializeComponent(component, componentName, componentData);
            }).then(() => {
                component.classList.remove('lazy-component-loading');
                component.classList.add('lazy-component-loaded');
                
                this.loadedComponents.add(component);
                this.loadingComponents.delete(component);

                // Dispatch load event
                component.dispatchEvent(new CustomEvent('componentloaded', {
                    detail: { componentName: componentName }
                }));

                if (this.config.enableLogging) {
                    console.log(`Component loaded: ${componentName}`);
                }
            }).catch(error => {
                component.classList.remove('lazy-component-loading');
                component.classList.add('lazy-component-error');
                component.innerHTML = '<div class="error-placeholder">Failed to load component</div>';
                
                this.loadingComponents.delete(component);
                console.error(`Failed to load component ${componentName}:`, error);
            });
        },

        // Initialize component after loading
        initializeComponent: function(element, componentName, componentData) {
            return new Promise((resolve, reject) => {
                try {
                    let data = {};
                    if (componentData) {
                        try {
                            data = JSON.parse(componentData);
                        } catch (e) {
                            console.warn('Invalid component data JSON:', componentData);
                        }
                    }

                    // Check for various component initialization patterns
                    if (window.HDTickets && window.HDTickets.Components && window.HDTickets.Components[componentName]) {
                        // HD Tickets component system
                        const componentInstance = new window.HDTickets.Components[componentName](element, data);
                        resolve(componentInstance);
                    } else if (window[componentName]) {
                        // Global component function
                        window[componentName](element, data);
                        resolve();
                    } else if (element.dataset.alpineData) {
                        // Alpine.js component
                        const alpineData = element.dataset.alpineData;
                        element.setAttribute('x-data', alpineData);
                        if (window.Alpine) {
                            window.Alpine.initTree(element);
                        }
                        resolve();
                    } else {
                        // Custom initialization
                        const initEvent = new CustomEvent('initComponent', {
                            detail: { componentName, data, element }
                        });
                        document.dispatchEvent(initEvent);
                        resolve();
                    }
                } catch (error) {
                    reject(error);
                }
            });
        },

        // Load script dynamically
        loadScript: function(src) {
            return new Promise((resolve, reject) => {
                // Check if script already loaded
                if (document.querySelector(`script[src="${src}"]`)) {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                
                script.onload = () => resolve();
                script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
                
                document.head.appendChild(script);
            });
        },

        // Load stylesheet dynamically
        loadStylesheet: function(href) {
            return new Promise((resolve, reject) => {
                // Check if stylesheet already loaded
                if (document.querySelector(`link[href="${href}"]`)) {
                    resolve();
                    return;
                }

                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                
                link.onload = () => resolve();
                link.onerror = () => reject(new Error(`Failed to load stylesheet: ${href}`));
                
                document.head.appendChild(link);
            });
        },

        // Fade in image with smooth animation
        fadeInImage: function(img) {
            img.style.opacity = '0';
            img.style.transition = `opacity ${this.config.fadeInDuration}ms ease-in-out`;
            
            // Force reflow
            img.offsetHeight;
            
            img.style.opacity = '1';
            
            // Clean up transition after animation
            setTimeout(() => {
                img.style.transition = '';
            }, this.config.fadeInDuration + 100);
        },

        // Generate placeholder pattern
        generatePlaceholderPattern: function() {
            return `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cg fill='%23d1d5db' fill-opacity='0.4'%3E%3Cpolygon points='2.5,0 0,2.5 5,7.5 0,12.5 0,17.5 2.5,20 7.5,15 12.5,20 17.5,20 20,17.5 15,12.5 20,7.5 20,2.5 17.5,0 12.5,5 7.5,0'/%3E%3C/g%3E%3C/svg%3E")`;
        },

        // Create skeleton placeholder
        createSkeletonPlaceholder: function(component) {
            const type = component.dataset.skeletonType || 'default';
            
            switch (type) {
                case 'card':
                    return `
                        <div class="lazy-skeleton">
                            <div class="lazy-skeleton-image"></div>
                            <div class="lazy-skeleton-content">
                                <div class="lazy-skeleton-line lazy-skeleton-title"></div>
                                <div class="lazy-skeleton-line"></div>
                                <div class="lazy-skeleton-line lazy-skeleton-short"></div>
                            </div>
                        </div>
                    `;
                case 'list':
                    return `
                        <div class="lazy-skeleton">
                            ${Array(3).fill().map(() => `
                                <div class="lazy-skeleton-item">
                                    <div class="lazy-skeleton-avatar"></div>
                                    <div class="lazy-skeleton-content">
                                        <div class="lazy-skeleton-line lazy-skeleton-title"></div>
                                        <div class="lazy-skeleton-line lazy-skeleton-short"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                case 'table':
                    return `
                        <div class="lazy-skeleton lazy-skeleton-table">
                            <div class="lazy-skeleton-row lazy-skeleton-header">
                                <div class="lazy-skeleton-cell"></div>
                                <div class="lazy-skeleton-cell"></div>
                                <div class="lazy-skeleton-cell"></div>
                            </div>
                            ${Array(5).fill().map(() => `
                                <div class="lazy-skeleton-row">
                                    <div class="lazy-skeleton-cell"></div>
                                    <div class="lazy-skeleton-cell"></div>
                                    <div class="lazy-skeleton-cell"></div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                default:
                    return `
                        <div class="lazy-skeleton">
                            <div class="lazy-skeleton-line lazy-skeleton-title"></div>
                            <div class="lazy-skeleton-line"></div>
                            <div class="lazy-skeleton-line"></div>
                            <div class="lazy-skeleton-line lazy-skeleton-short"></div>
                        </div>
                    `;
            }
        },

        // Create spinner placeholder
        createSpinnerPlaceholder: function() {
            return `
                <div class="lazy-spinner-container">
                    <div class="lazy-spinner"></div>
                    <p>Loading...</p>
                </div>
            `;
        },

        // Setup critical resource hints
        setupCriticalResourceHints: function() {
            // Preload critical fonts
            const criticalFonts = [
                'https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap'
            ];

            criticalFonts.forEach(font => {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.href = font;
                link.as = 'style';
                link.crossOrigin = 'anonymous';
                document.head.appendChild(link);
            });

            // Prefetch likely resources
            this.prefetchLikelyResources();
        },

        // Prefetch resources that are likely to be needed
        prefetchLikelyResources: function() {
            // Prefetch next page if pagination exists
            const nextPageLink = document.querySelector('a[rel="next"]');
            if (nextPageLink) {
                this.prefetchResource(nextPageLink.href);
            }

            // Prefetch important dashboard assets
            const importantPages = ['/dashboard', '/tickets', '/reports'];
            const currentPath = window.location.pathname;
            
            importantPages.forEach(page => {
                if (page !== currentPath) {
                    this.prefetchResource(page);
                }
            });
        },

        // Prefetch a resource
        prefetchResource: function(href) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = href;
            document.head.appendChild(link);
        },

        // Handle initial load optimizations
        handleInitialLoad: function() {
            // Load above-the-fold images immediately
            const aboveFoldImages = document.querySelectorAll('img[data-priority="high"]');
            aboveFoldImages.forEach(img => {
                this.loadImage(img);
            });

            // Defer below-the-fold components
            setTimeout(() => {
                this.setupComponentLazyLoading();
            }, 100);
        },

        // Setup mutation observer for dynamic content
        setupMutationObserver: function() {
            if (!('MutationObserver' in window)) return;

            const mutationObserver = new MutationObserver((mutations) => {
                let hasNewContent = false;

                mutations.forEach(mutation => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        hasNewContent = true;
                    }
                });

                if (hasNewContent) {
                    // Debounce rescanning
                    clearTimeout(this.rescanTimeout);
                    this.rescanTimeout = setTimeout(() => {
                        this.rescanForNewElements();
                    }, 100);
                }
            });

            mutationObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
        },

        // Rescan for new elements after dynamic content changes
        rescanForNewElements: function() {
            this.setupImageLazyLoading();
            this.setupComponentLazyLoading();
        },

        // Fallback loading for browsers without IntersectionObserver
        setupFallbackLoading: function() {
            const loadOnScroll = () => {
                const images = document.querySelectorAll(this.config.imageSelector);
                const components = document.querySelectorAll(this.config.componentSelector);

                [...images, ...components].forEach(element => {
                    if (this.isInViewport(element)) {
                        if (element.tagName === 'IMG') {
                            this.loadImage(element);
                        } else {
                            this.loadComponent(element);
                        }
                    }
                });
            };

            // Load on scroll with throttling
            let ticking = false;
            const scrollHandler = () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        loadOnScroll();
                        ticking = false;
                    });
                    ticking = true;
                }
            };

            window.addEventListener('scroll', scrollHandler, { passive: true });
            window.addEventListener('resize', scrollHandler, { passive: true });
            
            // Initial load
            loadOnScroll();
        },

        // Check if element is in viewport (fallback method)
        isInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;
            const windowWidth = window.innerWidth || document.documentElement.clientWidth;

            return (
                rect.top >= -50 &&
                rect.left >= -50 &&
                rect.bottom <= windowHeight + 50 &&
                rect.right <= windowWidth + 50
            );
        },

        // Load all remaining lazy elements (useful for print or SEO)
        loadAll: function() {
            const images = document.querySelectorAll(this.config.imageSelector);
            const components = document.querySelectorAll(this.config.componentSelector);

            images.forEach(img => this.loadImage(img));
            components.forEach(component => this.loadComponent(component));
        },

        // Clean up observers
        destroy: function() {
            if (this.imageObserver) {
                this.imageObserver.disconnect();
            }
            if (this.componentObserver) {
                this.componentObserver.disconnect();
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            LazyLoader.init();
        });
    } else {
        LazyLoader.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.LazyLoader = LazyLoader;

    // Add CSS for lazy loading effects
    const lazyStyles = document.createElement('style');
    lazyStyles.textContent = `
        /* Lazy Loading Styles */
        .lazy-loading {
            filter: blur(2px);
            transition: filter 300ms ease-in-out;
        }
        
        .lazy-loaded {
            filter: blur(0);
        }
        
        .lazy-error {
            opacity: 0.5;
            filter: grayscale(100%);
        }
        
        /* Component Loading States */
        .lazy-component-loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .lazy-component-loaded {
            opacity: 1;
            pointer-events: auto;
            animation: fadeIn 300ms ease-in-out;
        }
        
        .lazy-component-error {
            opacity: 0.5;
            border: 1px dashed #ef4444;
        }
        
        /* Skeleton Loading Animation */
        .lazy-skeleton {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .lazy-skeleton-line {
            height: 1rem;
            background-color: #e5e7eb;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .lazy-skeleton-title {
            height: 1.25rem;
            width: 60%;
        }
        
        .lazy-skeleton-short {
            width: 40%;
        }
        
        .lazy-skeleton-image {
            width: 100%;
            height: 200px;
            background-color: #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .lazy-skeleton-avatar {
            width: 3rem;
            height: 3rem;
            background-color: #e5e7eb;
            border-radius: 50%;
            margin-right: 1rem;
        }
        
        .lazy-skeleton-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .lazy-skeleton-content {
            flex: 1;
        }
        
        .lazy-skeleton-table {
            width: 100%;
        }
        
        .lazy-skeleton-row {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .lazy-skeleton-cell {
            flex: 1;
            height: 1rem;
            background-color: #e5e7eb;
            margin-right: 1rem;
            border-radius: 4px;
        }
        
        .lazy-skeleton-header .lazy-skeleton-cell {
            height: 1.25rem;
            background-color: #d1d5db;
        }
        
        /* Spinner */
        .lazy-spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .lazy-spinner {
            width: 2rem;
            height: 2rem;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .lazy-loading, .lazy-component-loading, .lazy-component-loaded {
                transition: none !important;
                animation: none !important;
            }
            
            .lazy-skeleton {
                animation: none !important;
                opacity: 0.7;
            }
            
            .lazy-spinner {
                animation: none !important;
                border-top-color: transparent;
            }
        }
    `;
    
    document.head.appendChild(lazyStyles);

})(window, document);
