/**
 * Lazy Loading and Code Splitting Utilities
 * HD Tickets - Sports Event Monitoring System
 * 
 * Provides optimized lazy loading functions with proper error handling,
 * loading states, and retry mechanisms for better user experience.
 */

/**
 * Enhanced async component loader with error handling and retry logic
 * @param {Function} importFunction - The dynamic import function
 * @param {Object} options - Loading options
 * @returns {Object} Vue async component definition
 */
export function lazyComponent(importFunction, options = {}) {
    const {
        loading = null, // Loading component
        error = null,   // Error component
        delay = 200,    // Delay before showing loading component (ms)
        timeout = 10000, // Timeout for loading (ms)
        retries = 3,    // Number of retry attempts
        retryDelay = 1000, // Delay between retries (ms)
    } = options;

    return () => ({
        component: importFunction().catch(async (error) => {
            console.warn('Component loading failed, retrying...', error);
            
            // Retry logic with exponential backoff
            for (let i = 0; i < retries; i++) {
                try {
                    await new Promise(resolve => setTimeout(resolve, retryDelay * Math.pow(2, i)));
                    return await importFunction();
                } catch (retryError) {
                    if (i === retries - 1) {
                        console.error('Component loading failed after retries:', retryError);
                        throw retryError;
                    }
                }
            }
        }),
        loading,
        error,
        delay,
        timeout,
    });
}

/**
 * Route-based lazy loading for Vue Router
 * Automatically splits routes into separate chunks
 * @param {string} componentPath - Path to the component
 * @param {string} chunkName - Custom chunk name (optional)
 * @returns {Function} Dynamic import function
 */
export function lazyRoute(componentPath, chunkName = null) {
    const actualChunkName = chunkName || componentPath.split('/').pop().replace('.vue', '');
    
    return () => import(
        /* webpackChunkName: "[request]" */
        /* vite: { chunkName: 'route-[request]' } */
        componentPath
    ).then(module => {
        // Add timestamp for cache busting on components that may change frequently
        const timestamp = window.__CSS_TIMESTAMP__ || Date.now();
        
        if (module.default && typeof module.default === 'object') {
            module.default._timestamp = timestamp;
        }
        
        return module;
    }).catch(error => {
        console.error(`Failed to load route component: ${componentPath}`, error);
        // Return a fallback error component
        return import('@/components/ErrorFallback.vue').catch(() => ({
            default: {
                template: '<div class="p-4 text-center text-red-600">Failed to load component</div>'
            }
        }));
    });
}

/**
 * Module-based lazy loading for feature modules
 * Groups related components into the same chunk
 * @param {string} moduleName - Name of the module/feature
 * @param {string} componentPath - Path to the component
 * @returns {Function} Dynamic import function
 */
export function lazyModule(moduleName, componentPath) {
    return () => import(
        /* webpackChunkName: "module-[request]" */
        /* vite: { chunkName: 'module-[request]' } */
        componentPath
    );
}

/**
 * Vendor library lazy loading
 * Loads heavy third-party libraries only when needed
 * @param {Function} importFunction - The dynamic import function
 * @param {string} libraryName - Name of the library for chunk naming
 * @returns {Promise} Promise that resolves to the library
 */
export function lazyVendor(importFunction, libraryName) {
    return importFunction().then(module => {
        console.log(`Lazy loaded vendor library: ${libraryName}`);
        return module;
    }).catch(error => {
        console.error(`Failed to lazy load vendor library: ${libraryName}`, error);
        throw error;
    });
}

/**
 * Image lazy loading with progressive enhancement
 * @param {string} src - Image source URL
 * @param {Object} options - Loading options
 * @returns {Promise} Promise that resolves when image is loaded
 */
export function lazyImage(src, options = {}) {
    const { 
        placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNGM0Y0RjYiLz48L3N2Zz4=',
        fallback = '/images/placeholder.png'
    } = options;

    return new Promise((resolve, reject) => {
        const img = new Image();
        
        img.onload = () => resolve(src);
        img.onerror = () => {
            console.warn(`Failed to load image: ${src}, using fallback`);
            resolve(fallback);
        };
        
        // Add timestamp for cache busting
        const timestamp = window.__CSS_TIMESTAMP__ || Date.now();
        img.src = src.includes('?') ? `${src}&t=${timestamp}` : `${src}?t=${timestamp}`;
    });
}

/**
 * CSS lazy loading utility
 * Dynamically loads CSS files when needed
 * @param {string} href - CSS file URL
 * @param {string} id - Unique identifier for the link element
 * @returns {Promise} Promise that resolves when CSS is loaded
 */
export function lazyCSS(href, id) {
    return new Promise((resolve, reject) => {
        // Check if CSS is already loaded
        if (document.getElementById(id)) {
            resolve();
            return;
        }

        const link = document.createElement('link');
        link.id = id;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        
        // Add timestamp for cache busting as per requirements
        const timestamp = window.__CSS_TIMESTAMP__ || Date.now();
        link.href = href.includes('?') ? `${href}&t=${timestamp}` : `${href}?t=${timestamp}`;
        
        link.onload = () => {
            console.log(`Lazy loaded CSS: ${href}`);
            resolve();
        };
        
        link.onerror = () => {
            console.error(`Failed to load CSS: ${href}`);
            reject(new Error(`Failed to load CSS: ${href}`));
        };

        document.head.appendChild(link);
    });
}

/**
 * Preload critical resources
 * @param {Array} resources - Array of resource URLs to preload
 * @param {string} type - Resource type ('script', 'style', 'image', etc.)
 */
export function preloadResources(resources, type = 'script') {
    resources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource;
        link.as = type;
        
        // Add timestamp for cache busting
        const timestamp = window.__CSS_TIMESTAMP__ || Date.now();
        if (type === 'style') {
            link.href = resource.includes('?') ? `${resource}&t=${timestamp}` : `${resource}?t=${timestamp}`;
        }
        
        document.head.appendChild(link);
    });
}

/**
 * Intersection Observer based lazy loading
 * @param {string} selector - CSS selector for elements to observe
 * @param {Function} callback - Callback function when element intersects
 * @param {Object} options - Intersection Observer options
 */
export function lazyObserver(selector, callback, options = {}) {
    const defaultOptions = {
        root: null,
        rootMargin: '50px',
        threshold: 0.1,
        ...options
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                callback(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, defaultOptions);

    // Observe existing elements
    document.querySelectorAll(selector).forEach(el => {
        observer.observe(el);
    });

    return observer;
}

// Export default object with all utilities
export default {
    lazyComponent,
    lazyRoute,
    lazyModule,
    lazyVendor,
    lazyImage,
    lazyCSS,
    preloadResources,
    lazyObserver
};
