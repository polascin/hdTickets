/**
 * Render-Blocking Resource Optimization
 * Minimizes render-blocking resources and optimizes critical rendering path
 */

class RenderOptimization {
    constructor(options = {}) {
        this.config = {
            enableCriticalCSS: true,
            enableFontOptimization: true,
            enableScriptOptimization: true,
            enableResourceHints: true,
            criticalViewportHeight: 600,
            deferNonCriticalCSS: true,
            preloadCriticalFonts: true,
            asyncNonCriticalJS: true,
            ...options
        };
        
        this.criticalResources = new Set();
        this.deferredResources = new Set();
        this.loadedResources = new Set();
        this.fontLoadingTasks = new Map();
        
        this.init();
    }
    
    /**
     * Initialize render optimization
     */
    init() {
        console.log('🚀 Initializing render optimization...');
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.optimizeRenderPath();
            });
        } else {
            this.optimizeRenderPath();
        }
        
        // Setup performance observers
        this.setupPerformanceObservers();
        
        // Setup font loading optimization
        if (this.config.enableFontOptimization) {
            this.optimizeFontLoading();
        }
        
        console.log('✅ Render optimization initialized');
    }
    
    /**
     * Optimize the critical rendering path
     */
    optimizeRenderPath() {
        try {
            // Identify and optimize CSS
            if (this.config.enableCriticalCSS) {
                this.optimizeCSSLoading();
            }
            
            // Optimize script loading
            if (this.config.enableScriptOptimization) {
                this.optimizeScriptLoading();
            }
            
            // Add resource hints
            if (this.config.enableResourceHints) {
                this.addResourceHints();
            }
            
            // Optimize images in viewport
            this.optimizeAboveFoldImages();
            
            // Remove unused CSS (if supported)
            this.removeUnusedCSS();
            
            console.log('⚡ Critical rendering path optimized');
        } catch (error) {
            console.error('❌ Failed to optimize render path:', error);
        }
    }
    
    /**
     * Optimize CSS loading to reduce render blocking
     */
    optimizeCSSLoading() {
        const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
        
        stylesheets.forEach((link, index) => {
            const href = link.href;
            
            // Skip if already processed
            if (link.dataset.optimized === 'true') return;
            
            // Determine if CSS is critical
            const isCritical = this.isCriticalCSS(href, link);
            
            if (isCritical) {
                this.markAsCritical(link);
                this.criticalResources.add(href);
            } else if (this.config.deferNonCriticalCSS) {
                this.deferCSS(link);
                this.deferredResources.add(href);
            }
            
            link.dataset.optimized = 'true';
        });\n        \n        console.log(`📊 CSS optimization: ${this.criticalResources.size} critical, ${this.deferredResources.size} deferred`);\n    }\n    \n    /**\n     * Determine if CSS file is critical\n     */\n    isCriticalCSS(href, linkElement) {\n        // Check for critical CSS indicators\n        const criticalPatterns = [\n            /app\\.css/,\n            /critical\\.css/,\n            /above-fold\\.css/,\n            /layout\\.css/,\n            /base\\.css/\n        ];\n        \n        // Check if marked as critical\n        if (linkElement.dataset.critical === 'true') {\n            return true;\n        }\n        \n        // Check file patterns\n        return criticalPatterns.some(pattern => pattern.test(href));\n    }\n    \n    /**\n     * Mark CSS as critical (high priority)\n     */\n    markAsCritical(linkElement) {\n        // Add high priority hint\n        linkElement.setAttribute('importance', 'high');\n        \n        // Move to document head if not already there\n        if (linkElement.parentNode !== document.head) {\n            document.head.appendChild(linkElement);\n        }\n        \n        console.log(`⚡ Marked as critical: ${linkElement.href}`);\n    }\n    \n    /**\n     * Defer non-critical CSS loading\n     */\n    deferCSS(linkElement) {\n        const href = linkElement.href;\n        const media = linkElement.media || 'all';\n        \n        // Create deferred loading\n        linkElement.media = 'print';\n        linkElement.onload = function() {\n            this.media = media;\n            this.onload = null;\n        };\n        \n        // Fallback for browsers that don't support onload\n        setTimeout(() => {\n            if (linkElement.media === 'print') {\n                linkElement.media = media;\n            }\n        }, 3000);\n        \n        // Add low priority hint\n        linkElement.setAttribute('importance', 'low');\n        \n        console.log(`📦 Deferred CSS loading: ${href}`);\n    }\n    \n    /**\n     * Optimize script loading\n     */\n    optimizeScriptLoading() {\n        const scripts = document.querySelectorAll('script[src]');\n        \n        scripts.forEach(script => {\n            const src = script.src;\n            \n            // Skip if already optimized\n            if (script.dataset.optimized === 'true') return;\n            \n            // Determine if script is critical\n            const isCritical = this.isCriticalScript(src, script);\n            \n            if (!isCritical && this.config.asyncNonCriticalJS) {\n                // Make non-critical scripts async/defer\n                if (!script.async && !script.defer) {\n                    script.defer = true;\n                    console.log(`🔄 Made script defer: ${src}`);\n                }\n            }\n            \n            script.dataset.optimized = 'true';\n        });\n    }\n    \n    /**\n     * Determine if script is critical\n     */\n    isCriticalScript(src, scriptElement) {\n        const criticalPatterns = [\n            /app\\.js$/,\n            /critical\\.js$/,\n            /polyfill/,\n            /bootstrap/\n        ];\n        \n        // Check if marked as critical\n        if (scriptElement.dataset.critical === 'true') {\n            return true;\n        }\n        \n        // Check if in document head (usually critical)\n        if (scriptElement.parentNode === document.head) {\n            return true;\n        }\n        \n        return criticalPatterns.some(pattern => pattern.test(src));\n    }\n    \n    /**\n     * Add resource hints for better loading\n     */\n    addResourceHints() {\n        // Preconnect to external domains\n        const externalDomains = this.findExternalDomains();\n        externalDomains.forEach(domain => {\n            this.addPreconnect(domain);\n        });\n        \n        // Prefetch next likely resources\n        this.addPrefetchHints();\n        \n        // Preload critical fonts\n        if (this.config.preloadCriticalFonts) {\n            this.preloadFonts();\n        }\n    }\n    \n    /**\n     * Find external domains used by the page\n     */\n    findExternalDomains() {\n        const domains = new Set();\n        const currentDomain = window.location.origin;\n        \n        // Check stylesheets\n        document.querySelectorAll('link[href]').forEach(link => {\n            try {\n                const url = new URL(link.href);\n                if (url.origin !== currentDomain) {\n                    domains.add(url.origin);\n                }\n            } catch (e) {\n                // Invalid URL, skip\n            }\n        });\n        \n        // Check scripts\n        document.querySelectorAll('script[src]').forEach(script => {\n            try {\n                const url = new URL(script.src);\n                if (url.origin !== currentDomain) {\n                    domains.add(url.origin);\n                }\n            } catch (e) {\n                // Invalid URL, skip\n            }\n        });\n        \n        return Array.from(domains);\n    }\n    \n    /**\n     * Add preconnect hint\n     */\n    addPreconnect(origin) {\n        // Check if preconnect already exists\n        const existing = document.querySelector(`link[rel=\"preconnect\"][href=\"${origin}\"]`);\n        if (existing) return;\n        \n        const link = document.createElement('link');\n        link.rel = 'preconnect';\n        link.href = origin;\n        link.crossOrigin = 'anonymous';\n        document.head.appendChild(link);\n        \n        console.log(`🔗 Added preconnect: ${origin}`);\n    }\n    \n    /**\n     * Add prefetch hints for likely next resources\n     */\n    addPrefetchHints() {\n        // Prefetch likely next pages based on current page\n        const currentPath = window.location.pathname;\n        const prefetchCandidates = this.getPrefetchCandidates(currentPath);\n        \n        prefetchCandidates.forEach(url => {\n            this.addPrefetch(url);\n        });\n    }\n    \n    /**\n     * Get prefetch candidates based on current page\n     */\n    getPrefetchCandidates(currentPath) {\n        const candidates = [];\n        \n        // Dashboard → likely to go to tickets or analytics\n        if (currentPath === '/' || currentPath.includes('dashboard')) {\n            candidates.push('/tickets', '/analytics', '/events');\n        }\n        \n        // Tickets → likely to go to individual ticket or dashboard\n        if (currentPath.includes('tickets')) {\n            candidates.push('/dashboard', '/events');\n        }\n        \n        // Admin → likely to navigate between admin sections\n        if (currentPath.includes('admin')) {\n            candidates.push('/admin/users', '/admin/settings', '/admin/analytics');\n        }\n        \n        return candidates;\n    }\n    \n    /**\n     * Add prefetch hint\n     */\n    addPrefetch(url) {\n        // Check if prefetch already exists\n        const existing = document.querySelector(`link[rel=\"prefetch\"][href=\"${url}\"]`);\n        if (existing) return;\n        \n        const link = document.createElement('link');\n        link.rel = 'prefetch';\n        link.href = url;\n        document.head.appendChild(link);\n        \n        console.log(`📦 Added prefetch: ${url}`);\n    }\n    \n    /**\n     * Preload critical fonts\n     */\n    preloadFonts() {\n        const criticalFonts = [\n            '/fonts/inter-var-latin.woff2',\n            '/fonts/roboto-regular.woff2',\n            '/build/assets/fonts/app-font.woff2'\n        ];\n        \n        criticalFonts.forEach(fontUrl => {\n            // Check if font exists first\n            this.checkAndPreloadFont(fontUrl);\n        });\n    }\n    \n    /**\n     * Check if font exists and preload it\n     */\n    async checkAndPreloadFont(fontUrl) {\n        try {\n            const response = await fetch(fontUrl, { method: 'HEAD' });\n            if (response.ok) {\n                const link = document.createElement('link');\n                link.rel = 'preload';\n                link.href = fontUrl;\n                link.as = 'font';\n                link.type = 'font/woff2';\n                link.crossOrigin = 'anonymous';\n                document.head.appendChild(link);\n                \n                console.log(`🔤 Preloaded font: ${fontUrl}`);\n            }\n        } catch (error) {\n            console.warn(`⚠️ Font not found: ${fontUrl}`);\n        }\n    }\n    \n    /**\n     * Optimize above-the-fold images\n     */\n    optimizeAboveFoldImages() {\n        const images = document.querySelectorAll('img');\n        \n        images.forEach((img, index) => {\n            const rect = img.getBoundingClientRect();\n            const isAboveFold = rect.top < this.config.criticalViewportHeight;\n            \n            if (isAboveFold) {\n                // Prioritize above-fold images\n                if (img.loading !== 'eager') {\n                    img.loading = 'eager';\n                }\n                \n                // Add fetchpriority if supported\n                if ('fetchPriority' in HTMLImageElement.prototype) {\n                    img.fetchPriority = 'high';\n                }\n                \n                // Preload the first few critical images\n                if (index < 3 && img.src) {\n                    this.preloadImage(img.src);\n                }\n            } else {\n                // Lazy load below-fold images\n                if (img.loading !== 'lazy') {\n                    img.loading = 'lazy';\n                }\n                \n                if ('fetchPriority' in HTMLImageElement.prototype) {\n                    img.fetchPriority = 'low';\n                }\n            }\n        });\n    }\n    \n    /**\n     * Preload critical image\n     */\n    preloadImage(src) {\n        const link = document.createElement('link');\n        link.rel = 'preload';\n        link.href = src;\n        link.as = 'image';\n        document.head.appendChild(link);\n        \n        console.log(`🖼️ Preloaded image: ${src}`);\n    }\n    \n    /**\n     * Remove unused CSS (experimental)\n     */\n    removeUnusedCSS() {\n        // This is a simplified version - in production you'd use tools like PurgeCSS\n        if (!this.config.enableUnusedCSSRemoval) return;\n        \n        console.log('🧹 Unused CSS removal is experimental and disabled by default');\n        \n        // You could implement unused CSS detection here\n        // by checking which CSS rules are actually used\n    }\n    \n    /**\n     * Optimize font loading\n     */\n    optimizeFontLoading() {\n        // Use font-display: swap for better perceived performance\n        this.addFontDisplaySwap();\n        \n        // Preload critical fonts\n        this.setupFontLoadingEvents();\n    }\n    \n    /**\n     * Add font-display: swap to font faces\n     */\n    addFontDisplaySwap() {\n        const style = document.createElement('style');\n        style.textContent = `\n            @font-face {\n                font-display: swap;\n            }\n        `;\n        document.head.appendChild(style);\n    }\n    \n    /**\n     * Setup font loading event monitoring\n     */\n    setupFontLoadingEvents() {\n        if ('fonts' in document) {\n            document.fonts.ready.then(() => {\n                console.log('🔤 All fonts loaded');\n                \n                // Mark font loading as complete\n                document.documentElement.classList.add('fonts-loaded');\n                \n                // Trigger any font-dependent layout updates\n                this.onFontsLoaded();\n            });\n        }\n    }\n    \n    /**\n     * Handle font loading completion\n     */\n    onFontsLoaded() {\n        // Recalculate layouts that depend on fonts\n        window.dispatchEvent(new CustomEvent('fonts-loaded'));\n        \n        // Update performance metrics\n        if (window.performanceMonitoring) {\n            window.performanceMonitoring.mark('fonts-loaded');\n        }\n    }\n    \n    /**\n     * Setup performance observers\n     */\n    setupPerformanceObservers() {\n        // Observe Largest Contentful Paint\n        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {\n            const observer = new PerformanceObserver((list) => {\n                const entries = list.getEntries();\n                const lastEntry = entries[entries.length - 1];\n                \n                console.log(`📊 LCP: ${lastEntry.startTime.toFixed(2)}ms`);\n                \n                // If LCP is poor, log optimization suggestions\n                if (lastEntry.startTime > 2500) {\n                    this.suggestLCPOptimizations(lastEntry);\n                }\n            });\n            \n            observer.observe({ entryTypes: ['largest-contentful-paint'] });\n        }\n        \n        // Observe First Input Delay\n        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('first-input')) {\n            const observer = new PerformanceObserver((list) => {\n                const entries = list.getEntries();\n                entries.forEach(entry => {\n                    const fid = entry.processingStart - entry.startTime;\n                    console.log(`📊 FID: ${fid.toFixed(2)}ms`);\n                    \n                    if (fid > 100) {\n                        this.suggestFIDOptimizations();\n                    }\n                });\n            });\n            \n            observer.observe({ entryTypes: ['first-input'], buffered: true });\n        }\n    }\n    \n    /**\n     * Suggest LCP optimizations\n     */\n    suggestLCPOptimizations(entry) {\n        console.group('🔍 LCP Optimization Suggestions:');\n        console.log('• Optimize image loading (use WebP, proper sizing)');\n        console.log('• Reduce server response time');\n        console.log('• Eliminate render-blocking resources');\n        console.log('• Preload LCP element:', entry.element);\n        console.groupEnd();\n    }\n    \n    /**\n     * Suggest FID optimizations\n     */\n    suggestFIDOptimizations() {\n        console.group('🔍 FID Optimization Suggestions:');\n        console.log('• Break up long-running tasks');\n        console.log('• Use code splitting');\n        console.log('• Defer non-essential JavaScript');\n        console.log('• Use web workers for heavy computations');\n        console.groupEnd();\n    }\n    \n    /**\n     * Get optimization statistics\n     */\n    getStats() {\n        return {\n            criticalResources: this.criticalResources.size,\n            deferredResources: this.deferredResources.size,\n            loadedResources: this.loadedResources.size,\n            optimizationTime: Date.now()\n        };\n    }\n    \n    /**\n     * Manual optimization trigger\n     */\n    optimize() {\n        this.optimizeRenderPath();\n        console.log('🔧 Manual optimization completed');\n    }\n}\n\n// Auto-initialize if in browser environment\nif (typeof window !== 'undefined') {\n    // Initialize with delay to avoid blocking initial render\n    setTimeout(() => {\n        window.renderOptimization = new RenderOptimization();\n        \n        // Expose optimization methods globally\n        window.optimizeRender = () => window.renderOptimization.optimize();\n        window.getRenderStats = () => window.renderOptimization.getStats();\n    }, 100);\n}\n\nexport default RenderOptimization;
