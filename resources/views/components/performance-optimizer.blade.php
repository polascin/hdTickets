{{-- Performance Optimizer Component --}}
{{-- Comprehensive performance optimizations including lazy loading, critical CSS, and resource hints --}}

<div x-data="performanceOptimizer()" x-init="init()" class="performance-optimizer">
    {{-- Critical CSS Inline Styles --}}
    <style id="critical-css">
        /* Critical CSS - Above the fold styles */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading-shimmer 1.5s infinite;
        }
        
        @keyframes loading-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        /* Critical layout styles */
        .app-shell { min-height: 100vh; }
        .header { position: sticky; top: 0; z-index: 40; }
        .sidebar { width: 256px; transition: transform 0.3s ease; }
        .main-content { flex: 1; min-height: 0; }
        
        /* Loading states */
        .lazy-loading { opacity: 0; transition: opacity 0.3s ease; }
        .lazy-loaded { opacity: 1; }
        
        /* Performance hints */
        .performance-hint {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 9999;
            display: none;
        }
        
        .performance-hint.show {
            display: block;
        }
    </style>

    {{-- Resource Hints --}}
    <template x-if="resourceHints.length > 0">
        <div>
            <template x-for="hint in resourceHints" :key="hint.href">
                <link :rel="hint.rel" :href="hint.href" :as="hint.as" :crossorigin="hint.crossorigin">
            </template>
        </div>
    </template>

    {{-- Virtual Scroll Container Template --}}
    <template x-if="false" id="virtual-scroll-template">
        <div class="virtual-scroll-container" :style="{ height: containerHeight + 'px' }">
            <div class="virtual-scroll-spacer" :style="{ height: offsetY + 'px' }"></div>
            <div class="virtual-scroll-content">
                <!-- Virtual scroll items will be inserted here -->
            </div>
            <div class="virtual-scroll-spacer" :style="{ height: (totalHeight - offsetY - viewportHeight) + 'px' }"></div>
        </div>
    </template>

    {{-- Performance Metrics Display (Development) --}}
    <div 
        x-show="showMetrics && isDevelopment"
        class="fixed top-4 left-4 bg-black text-green-400 p-4 rounded-lg font-mono text-xs z-50 max-w-sm"
        style="font-family: 'Courier New', monospace;"
    >
        <h3 class="text-white font-bold mb-2">⚡ Performance Metrics</h3>
        <div class="space-y-1">
            <div>FCP: <span x-text="metrics.fcp + 'ms'" class="text-yellow-400"></span></div>
            <div>LCP: <span x-text="metrics.lcp + 'ms'" class="text-yellow-400"></span></div>
            <div>FID: <span x-text="metrics.fid + 'ms'" class="text-yellow-400"></span></div>
            <div>CLS: <span x-text="metrics.cls" class="text-yellow-400"></span></div>
            <div>TTFB: <span x-text="metrics.ttfb + 'ms'" class="text-yellow-400"></span></div>
            <div>Bundle: <span x-text="bundleSize" class="text-blue-400"></span></div>
            <div>Images: <span x-text="lazyImagesCount" class="text-purple-400"></span></div>
        </div>
        <button 
            @click="showMetrics = false"
            class="mt-2 text-xs bg-gray-700 text-white px-2 py-1 rounded hover:bg-gray-600"
        >
            Hide
        </button>
    </div>

    {{-- Performance Hint Notifications --}}
    <div 
        id="performance-hint"
        class="performance-hint"
        x-show="currentHint"
        x-text="currentHint"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>
</div>

<script>
function performanceOptimizer() {
    return {
        // State
        isInitialized: false,
        isDevelopment: window.location.hostname === 'localhost' || window.location.hostname.includes('dev'),
        showMetrics: false,
        currentHint: '',
        
        // Performance metrics
        metrics: {
            fcp: 0,
            lcp: 0,
            fid: 0,
            cls: 0,
            ttfb: 0
        },
        
        // Resource management
        resourceHints: [],
        lazyImagesCount: 0,
        bundleSize: '0KB',
        
        // Virtual scrolling
        virtualScrollContainers: new Map(),
        
        // Lazy loading
        lazyLoadObserver: null,
        imageLoadQueue: [],
        
        // Performance monitoring
        performanceObserver: null,
        navigationObserver: null,
        
        init() {
            if (this.isInitialized) return;
            
            this.setupResourceHints();
            this.setupLazyLoading();
            this.setupVirtualScrolling();
            this.setupPerformanceMonitoring();
            this.setupCriticalResourceOptimization();
            this.setupServiceWorkerOptimizations();
            this.optimizeInitialLoad();
            
            if (this.isDevelopment) {
                this.setupDevelopmentTools();
            }
            
            this.isInitialized = true;
            console.log('[Perf] Performance optimizer initialized');
        },
        
        setupResourceHints() {
            // Preload critical resources
            this.addResourceHint('preload', '/css/app.css', 'style');
            this.addResourceHint('preload', '/js/app.js', 'script');
            
            // Prefetch likely next pages
            this.addResourceHint('prefetch', '/discover');
            this.addResourceHint('prefetch', '/dashboard');
            this.addResourceHint('prefetch', '/alerts');
            
            // DNS prefetch for external resources
            this.addResourceHint('dns-prefetch', 'https://fonts.googleapis.com');
            this.addResourceHint('dns-prefetch', 'https://cdn.jsdelivr.net');
            this.addResourceHint('dns-prefetch', 'https://api.stripe.com');
            
            // Preconnect to critical origins
            this.addResourceHint('preconnect', 'https://fonts.gstatic.com', null, 'crossorigin');
        },
        
        addResourceHint(rel, href, as = null, crossorigin = null) {
            this.resourceHints.push({
                rel,
                href,
                as,
                crossorigin
            });
        },
        
        setupLazyLoading() {
            // Set up intersection observer for lazy loading
            if ('IntersectionObserver' in window) {
                this.lazyLoadObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadLazyElement(entry.target);
                            this.lazyLoadObserver.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.01
                });
                
                // Observe all lazy elements
                this.observeLazyElements();
            } else {
                // Fallback for older browsers
                this.loadAllLazyElements();
            }
        },
        
        observeLazyElements() {
            // Images with data-src attribute
            const lazyImages = document.querySelectorAll('img[data-src]:not([src])');
            lazyImages.forEach(img => {
                this.lazyLoadObserver.observe(img);
                this.lazyImagesCount++;
            });
            
            // Lazy load components
            const lazyComponents = document.querySelectorAll('[data-lazy-component]');
            lazyComponents.forEach(component => {
                this.lazyLoadObserver.observe(component);
            });
            
            // Background images
            const lazyBackgrounds = document.querySelectorAll('[data-bg-src]');
            lazyBackgrounds.forEach(element => {
                this.lazyLoadObserver.observe(element);
            });
        },
        
        loadLazyElement(element) {
            if (element.tagName === 'IMG' && element.dataset.src) {
                this.loadLazyImage(element);
            } else if (element.dataset.lazyComponent) {
                this.loadLazyComponent(element);
            } else if (element.dataset.bgSrc) {
                this.loadLazyBackground(element);
            }
        },
        
        loadLazyImage(img) {
            const src = img.dataset.src;
            const srcset = img.dataset.srcset;
            
            img.classList.add('lazy-loading');
            
            const tempImage = new Image();
            tempImage.onload = () => {
                img.src = src;
                if (srcset) {
                    img.srcset = srcset;
                }
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                
                // Remove data attributes
                delete img.dataset.src;
                delete img.dataset.srcset;
                
                this.lazyImagesCount--;
            };
            
            tempImage.onerror = () => {
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-error');
                console.warn('[Perf] Failed to load lazy image:', src);
            };
            
            tempImage.src = src;
        },
        
        loadLazyComponent(element) {
            const componentName = element.dataset.lazyComponent;
            const componentData = element.dataset.componentData || '{}';
            
            element.classList.add('lazy-loading');
            
            // Simulate component loading (replace with actual component loading logic)
            import(`./components/${componentName}.js`)
                .then(module => {
                    const Component = module.default;
                    const data = JSON.parse(componentData);
                    
                    // Initialize component
                    const componentInstance = new Component(element, data);
                    componentInstance.render();
                    
                    element.classList.remove('lazy-loading');
                    element.classList.add('lazy-loaded');
                    
                    delete element.dataset.lazyComponent;
                })
                .catch(error => {
                    console.error('[Perf] Failed to load lazy component:', componentName, error);
                    element.classList.remove('lazy-loading');
                    element.classList.add('lazy-error');
                });
        },
        
        loadLazyBackground(element) {
            const bgSrc = element.dataset.bgSrc;
            
            element.classList.add('lazy-loading');
            
            const tempImage = new Image();
            tempImage.onload = () => {
                element.style.backgroundImage = `url(${bgSrc})`;
                element.classList.remove('lazy-loading');
                element.classList.add('lazy-loaded');
                
                delete element.dataset.bgSrc;
            };
            
            tempImage.src = bgSrc;
        },
        
        loadAllLazyElements() {
            // Fallback for browsers without IntersectionObserver
            const lazyElements = document.querySelectorAll('[data-src], [data-lazy-component], [data-bg-src]');
            lazyElements.forEach(element => this.loadLazyElement(element));
        },
        
        setupVirtualScrolling() {
            const virtualScrollElements = document.querySelectorAll('[data-virtual-scroll]');
            
            virtualScrollElements.forEach(container => {
                this.initializeVirtualScroll(container);
            });
        },
        
        initializeVirtualScroll(container) {
            const itemHeight = parseInt(container.dataset.itemHeight) || 50;
            const bufferSize = parseInt(container.dataset.bufferSize) || 5;
            const totalItems = parseInt(container.dataset.totalItems) || 0;
            
            if (totalItems === 0) return;
            
            const virtualScroller = {
                container,
                itemHeight,
                bufferSize,
                totalItems,
                viewportHeight: container.clientHeight,
                scrollTop: 0,
                startIndex: 0,
                endIndex: 0,
                visibleItems: [],
                itemRenderer: this.getItemRenderer(container)
            };
            
            this.virtualScrollContainers.set(container, virtualScroller);
            
            // Set up scroll listener
            container.addEventListener('scroll', () => {
                this.updateVirtualScroll(virtualScroller);
            });
            
            // Initial render
            this.updateVirtualScroll(virtualScroller);
        },
        
        updateVirtualScroll(scroller) {
            const { container, itemHeight, bufferSize, totalItems, viewportHeight } = scroller;
            
            scroller.scrollTop = container.scrollTop;
            scroller.startIndex = Math.max(0, Math.floor(scroller.scrollTop / itemHeight) - bufferSize);
            scroller.endIndex = Math.min(totalItems - 1, scroller.startIndex + Math.ceil(viewportHeight / itemHeight) + bufferSize * 2);
            
            // Update visible items
            scroller.visibleItems = [];
            for (let i = scroller.startIndex; i <= scroller.endIndex; i++) {
                scroller.visibleItems.push(i);
            }
            
            // Render visible items
            this.renderVirtualItems(scroller);
        },
        
        renderVirtualItems(scroller) {
            const { container, visibleItems, itemHeight, itemRenderer } = scroller;
            
            // Clear existing content
            const contentArea = container.querySelector('.virtual-scroll-content') || container;
            contentArea.innerHTML = '';
            
            // Set container height
            const totalHeight = scroller.totalItems * itemHeight;
            container.style.position = 'relative';
            container.style.height = totalHeight + 'px';
            
            // Render visible items
            visibleItems.forEach((index, i) => {
                const item = itemRenderer(index);
                item.style.position = 'absolute';
                item.style.top = (index * itemHeight) + 'px';
                item.style.height = itemHeight + 'px';
                item.style.width = '100%';
                
                contentArea.appendChild(item);
            });
        },
        
        getItemRenderer(container) {
            const templateSelector = container.dataset.itemTemplate;
            const template = document.querySelector(templateSelector);
            
            if (!template) {
                return (index) => {
                    const div = document.createElement('div');
                    div.textContent = `Item ${index}`;
                    div.className = 'virtual-scroll-item p-4 border-b border-gray-200';
                    return div;
                };
            }
            
            return (index) => {
                const item = template.content.cloneNode(true);
                // Replace placeholders with actual data
                item.textContent = item.textContent.replace(/\{\{index\}\}/g, index);
                return item;
            };
        },
        
        setupPerformanceMonitoring() {
            // Core Web Vitals monitoring
            if ('PerformanceObserver' in window) {
                // Largest Contentful Paint
                new PerformanceObserver((entryList) => {
                    const entries = entryList.getEntries();
                    const lastEntry = entries[entries.length - 1];
                    this.metrics.lcp = Math.round(lastEntry.startTime);
                }).observe({ entryTypes: ['largest-contentful-paint'] });
                
                // First Input Delay
                new PerformanceObserver((entryList) => {
                    const entries = entryList.getEntries();
                    entries.forEach(entry => {
                        this.metrics.fid = Math.round(entry.processingStart - entry.startTime);
                    });
                }).observe({ entryTypes: ['first-input'] });
                
                // Cumulative Layout Shift
                new PerformanceObserver((entryList) => {
                    let clsValue = 0;
                    entryList.getEntries().forEach(entry => {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                        }
                    });
                    this.metrics.cls = Math.round(clsValue * 1000) / 1000;
                }).observe({ entryTypes: ['layout-shift'] });
            }
            
            // Navigation timing
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const navigation = performance.getEntriesByType('navigation')[0];
                    if (navigation) {
                        this.metrics.fcp = Math.round(navigation.responseStart - navigation.fetchStart);
                        this.metrics.ttfb = Math.round(navigation.responseStart - navigation.requestStart);
                    }
                }, 0);
            });
        },
        
        setupCriticalResourceOptimization() {
            // Optimize font loading
            if ('fonts' in document) {
                document.fonts.ready.then(() => {
                    this.showHint('Fonts loaded', 1000);
                });
            }
            
            // Optimize image loading
            this.optimizeImageLoading();
            
            // Bundle size estimation
            this.estimateBundleSize();
        },
        
        optimizeImageLoading() {
            // Add loading="lazy" to images that don't have it
            const images = document.querySelectorAll('img:not([loading])');
            images.forEach(img => {
                // Don't add lazy loading to above-the-fold images
                const rect = img.getBoundingClientRect();
                if (rect.top > window.innerHeight) {
                    img.loading = 'lazy';
                }
            });
            
            // Implement progressive image enhancement
            const progressiveImages = document.querySelectorAll('img[data-progressive]');
            progressiveImages.forEach(img => {
                this.loadProgressiveImage(img);
            });
        },
        
        loadProgressiveImage(img) {
            const lowRes = img.dataset.progressive;
            const highRes = img.src;
            
            // Load low-res first
            img.src = lowRes;
            img.classList.add('progressive-loading');
            
            // Load high-res in background
            const highResImg = new Image();
            highResImg.onload = () => {
                img.src = highRes;
                img.classList.remove('progressive-loading');
                img.classList.add('progressive-loaded');
            };
            highResImg.src = highRes;
        },
        
        estimateBundleSize() {
            // Rough estimation of bundle size
            const scripts = document.querySelectorAll('script[src]');
            const styles = document.querySelectorAll('link[rel="stylesheet"]');
            
            let estimatedSize = 0;
            
            // Estimate based on typical sizes
            scripts.forEach(script => {
                if (script.src.includes('app.js')) estimatedSize += 150; // KB
                else if (script.src.includes('vendor')) estimatedSize += 200;
                else estimatedSize += 50;
            });
            
            styles.forEach(style => {
                if (style.href.includes('app.css')) estimatedSize += 80;
                else estimatedSize += 20;
            });
            
            this.bundleSize = estimatedSize > 1024 ? 
                `${Math.round(estimatedSize / 1024 * 10) / 10}MB` : 
                `${estimatedSize}KB`;
        },
        
        setupServiceWorkerOptimizations() {
            if ('serviceWorker' in navigator) {
                // Listen for service worker updates
                navigator.serviceWorker.addEventListener('message', event => {
                    if (event.data && event.data.type === 'CACHE_UPDATED') {
                        this.showHint('App updated in background', 2000);
                    }
                });
            }
        },
        
        optimizeInitialLoad() {
            // Remove non-critical CSS after page load
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.loadNonCriticalCSS();
                }, 1000);
            });
            
            // Preload next likely page
            this.preloadLikelyNextPage();
        },
        
        loadNonCriticalCSS() {
            const nonCriticalCSS = [
                '/css/non-critical.css',
                '/css/print.css'
            ];
            
            nonCriticalCSS.forEach(href => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                link.media = 'all';
                document.head.appendChild(link);
            });
        },
        
        preloadLikelyNextPage() {
            // Determine likely next page based on current page
            const currentPage = window.location.pathname;
            let nextPage = '';
            
            if (currentPage === '/') nextPage = '/discover';
            else if (currentPage.includes('/discover')) nextPage = '/alerts';
            else if (currentPage.includes('/dashboard')) nextPage = '/discover';
            
            if (nextPage) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = nextPage;
                document.head.appendChild(link);
            }
        },
        
        setupDevelopmentTools() {
            // Show performance metrics in development
            if (this.isDevelopment) {
                // Toggle metrics with Ctrl+Shift+P
                document.addEventListener('keydown', (e) => {
                    if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                        e.preventDefault();
                        this.showMetrics = !this.showMetrics;
                    }
                });
                
                // Performance audit button
                this.createPerformanceAuditButton();
            }
        },
        
        createPerformanceAuditButton() {
            const button = document.createElement('button');
            button.textContent = '⚡ Audit';
            button.className = 'fixed bottom-4 left-4 bg-yellow-500 text-black px-3 py-2 rounded-lg text-sm font-bold hover:bg-yellow-400 z-50';
            button.onclick = () => this.runPerformanceAudit();
            document.body.appendChild(button);
        },
        
        runPerformanceAudit() {
            const audit = {
                images: this.auditImages(),
                scripts: this.auditScripts(),
                styles: this.auditStyles(),
                accessibility: this.auditAccessibility(),
                performance: this.auditPerformance()
            };
            
            console.group('🔍 Performance Audit Results');
            Object.entries(audit).forEach(([category, results]) => {
                console.group(`📊 ${category.charAt(0).toUpperCase() + category.slice(1)}`);
                results.forEach(result => {
                    const icon = result.status === 'pass' ? '✅' : result.status === 'warning' ? '⚠️' : '❌';
                    console.log(`${icon} ${result.message}`);
                });
                console.groupEnd();
            });
            console.groupEnd();
            
            this.showHint('Performance audit complete - check console', 3000);
        },
        
        auditImages() {
            const results = [];
            const images = document.querySelectorAll('img');
            
            images.forEach((img, index) => {
                if (!img.alt && !img.getAttribute('aria-hidden')) {
                    results.push({
                        status: 'fail',
                        message: `Image ${index + 1} missing alt text`
                    });
                }
                
                if (!img.loading && img.getBoundingClientRect().top > window.innerHeight) {
                    results.push({
                        status: 'warning',
                        message: `Image ${index + 1} could benefit from lazy loading`
                    });
                }
                
                if (img.naturalWidth > 1920 || img.naturalHeight > 1080) {
                    results.push({
                        status: 'warning',
                        message: `Image ${index + 1} is very large (${img.naturalWidth}x${img.naturalHeight})`
                    });
                }
            });
            
            if (results.length === 0) {
                results.push({ status: 'pass', message: 'All images optimized' });
            }
            
            return results;
        },
        
        auditScripts() {
            const results = [];
            const scripts = document.querySelectorAll('script[src]');
            
            scripts.forEach((script, index) => {
                if (!script.async && !script.defer) {
                    results.push({
                        status: 'warning',
                        message: `Script ${index + 1} is render-blocking`
                    });
                }
            });
            
            return results;
        },
        
        auditStyles() {
            const results = [];
            const styles = document.querySelectorAll('link[rel="stylesheet"]');
            
            if (styles.length > 5) {
                results.push({
                    status: 'warning',
                    message: `${styles.length} CSS files found - consider bundling`
                });
            }
            
            return results;
        },
        
        auditAccessibility() {
            const results = [];
            
            // Check for skip links
            const skipLinks = document.querySelectorAll('a[href^="#"]');
            if (skipLinks.length === 0) {
                results.push({
                    status: 'fail',
                    message: 'No skip navigation links found'
                });
            }
            
            // Check for heading hierarchy
            const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
            if (headings.length === 0) {
                results.push({
                    status: 'fail',
                    message: 'No heading structure found'
                });
            }
            
            return results;
        },
        
        auditPerformance() {
            const results = [];
            
            if (this.metrics.lcp > 2500) {
                results.push({
                    status: 'fail',
                    message: `LCP too slow: ${this.metrics.lcp}ms (should be < 2500ms)`
                });
            } else if (this.metrics.lcp > 0) {
                results.push({
                    status: 'pass',
                    message: `LCP good: ${this.metrics.lcp}ms`
                });
            }
            
            if (this.metrics.fid > 100) {
                results.push({
                    status: 'fail',
                    message: `FID too slow: ${this.metrics.fid}ms (should be < 100ms)`
                });
            }
            
            if (this.metrics.cls > 0.1) {
                results.push({
                    status: 'fail',
                    message: `CLS too high: ${this.metrics.cls} (should be < 0.1)`
                });
            }
            
            return results;
        },
        
        showHint(message, duration = 2000) {
            this.currentHint = message;
            
            setTimeout(() => {
                this.currentHint = '';
            }, duration);
        },
        
        // Public API methods
        addLazyImage(img) {
            if (this.lazyLoadObserver) {
                this.lazyLoadObserver.observe(img);
                this.lazyImagesCount++;
            } else {
                this.loadLazyImage(img);
            }
        },
        
        preloadResource(url, as = 'fetch') {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = url;
            link.as = as;
            document.head.appendChild(link);
        },
        
        prefetchPage(url) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        },
        
        getPerformanceMetrics() {
            return { ...this.metrics };
        }
    };
}
</script>