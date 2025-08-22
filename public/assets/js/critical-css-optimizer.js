/**
 * HD Tickets Critical CSS Optimizer
 * 
 * Optimizes CSS loading and prevents render-blocking:
 * - Extracts critical above-the-fold CSS
 * - Defers non-critical CSS loading
 * - Prevents FOUC (Flash of Unstyled Content)
 * - Optimizes font loading
 */

(function(window, document) {
    'use strict';

    const CriticalCSSOptimizer = {
        // Configuration
        config: {
            criticalViewportHeight: 600, // Consider first 600px as critical
            fontDisplaySwap: true,
            enableLogging: true,
            deferredLoadDelay: 100, // Delay for loading non-critical CSS
            criticalCSSId: 'critical-css',
            stylesheetSelector: 'link[rel="stylesheet"]'
        },

        // State management
        criticalStyles: new Map(),
        deferredStylesheets: [],
        fontsLoading: new Set(),
        isOptimized: false,

        // Initialize critical CSS optimization
        init: function() {
            if (this.config.enableLogging) {
                console.log('⚡ Critical CSS Optimizer initialized');
            }

            this.analyzePage();
            this.optimizeFontLoading();
            this.setupDeferredLoading();
            this.preventLayoutShift();
            this.setupMediaQueryOptimization();
            
            this.isOptimized = true;
        },

        // Analyze page to identify critical styles
        analyzePage: function() {
            const startTime = performance.now();
            
            // Get all visible elements in the critical viewport
            const criticalElements = this.getCriticalViewportElements();
            
            // Extract critical CSS rules
            this.extractCriticalCSS(criticalElements);
            
            const analysisTime = performance.now() - startTime;
            if (this.config.enableLogging) {
                console.log(`Page analysis completed in ${analysisTime.toFixed(2)}ms`);
                console.log(`Found ${criticalElements.length} critical elements`);
                console.log(`Extracted ${this.criticalStyles.size} critical style rules`);
            }
        },

        // Get all elements in the critical viewport
        getCriticalViewportElements: function() {
            const elements = document.querySelectorAll('*');
            const criticalElements = [];
            const viewportHeight = this.config.criticalViewportHeight;

            for (const element of elements) {
                const rect = element.getBoundingClientRect();
                
                // Element is in critical viewport if any part is visible above the fold
                if (rect.top < viewportHeight && rect.bottom > 0) {
                    criticalElements.push({
                        element: element,
                        rect: rect,
                        tagName: element.tagName.toLowerCase(),
                        className: element.className,
                        id: element.id
                    });
                }
            }

            return criticalElements;
        },

        // Extract CSS rules needed for critical elements
        extractCriticalCSS: function(criticalElements) {
            const styleSheets = document.styleSheets;
            
            for (let i = 0; i < styleSheets.length; i++) {
                try {
                    const styleSheet = styleSheets[i];
                    if (!styleSheet.cssRules) continue;

                    this.processCSSRules(styleSheet.cssRules, criticalElements);
                } catch (e) {
                    // Cross-origin stylesheets may not be accessible
                    if (this.config.enableLogging) {
                        console.warn('Could not access stylesheet:', styleSheets[i].href, e);
                    }
                }
            }
        },

        // Process CSS rules and identify critical ones
        processCSSRules: function(cssRules, criticalElements) {
            for (let i = 0; i < cssRules.length; i++) {
                const rule = cssRules[i];

                if (rule.type === CSSRule.STYLE_RULE) {
                    this.processStyleRule(rule, criticalElements);
                } else if (rule.type === CSSRule.MEDIA_RULE) {
                    // Check if media rule applies to current viewport
                    if (window.matchMedia(rule.conditionText).matches) {
                        this.processCSSRules(rule.cssRules, criticalElements);
                    }
                } else if (rule.type === CSSRule.IMPORT_RULE) {
                    // Mark imported stylesheets for deferred loading
                    this.deferredStylesheets.push(rule.href);
                }
            }
        },

        // Process individual style rule
        processStyleRule: function(rule, criticalElements) {
            const selector = rule.selectorText;
            if (!selector) return;

            // Check if rule applies to any critical element
            const isCritical = criticalElements.some(({ element }) => {
                try {
                    return element.matches(selector);
                } catch (e) {
                    // Invalid selector, skip
                    return false;
                }
            });

            if (isCritical) {
                const ruleText = rule.cssText;
                const priority = this.calculateRulePriority(rule, criticalElements);
                
                this.criticalStyles.set(selector, {
                    cssText: ruleText,
                    priority: priority,
                    specificity: this.calculateSpecificity(selector),
                    size: ruleText.length
                });
            }
        },

        // Calculate rule priority (higher = more critical)
        calculateRulePriority: function(rule, criticalElements) {
            const selector = rule.selectorText;
            let priority = 0;

            // Higher priority for elements closer to top of page
            criticalElements.forEach(({ element, rect }) => {
                try {
                    if (element.matches(selector)) {
                        priority += Math.max(0, 1000 - rect.top);
                    }
                } catch (e) {
                    // Invalid selector
                }
            });

            // Higher priority for layout-affecting properties
            const cssText = rule.cssText.toLowerCase();
            if (cssText.includes('display:') || cssText.includes('position:')) priority += 500;
            if (cssText.includes('width:') || cssText.includes('height:')) priority += 300;
            if (cssText.includes('margin:') || cssText.includes('padding:')) priority += 200;
            if (cssText.includes('font-')) priority += 150;
            if (cssText.includes('color:') || cssText.includes('background:')) priority += 100;

            return priority;
        },

        // Calculate CSS selector specificity
        calculateSpecificity: function(selector) {
            let specificity = 0;
            
            // Count IDs (most specific)
            specificity += (selector.match(/#/g) || []).length * 100;
            
            // Count classes, attributes, pseudo-classes
            specificity += (selector.match(/\.|:\w+|\[/g) || []).length * 10;
            
            // Count elements and pseudo-elements
            specificity += (selector.match(/\b\w+\b/g) || []).length;
            
            return specificity;
        },

        // Optimize font loading
        optimizeFontLoading: function() {
            const fontLinks = document.querySelectorAll('link[href*="font"]');
            
            fontLinks.forEach(link => {
                // Add font-display: swap to existing font stylesheets
                if (this.config.fontDisplaySwap) {
                    this.addFontDisplaySwap(link);
                }

                // Preload critical fonts
                if (this.isCriticalFont(link.href)) {
                    this.preloadFont(link.href);
                }
            });

            // Monitor font loading
            this.monitorFontLoading();
        },

        // Add font-display: swap to font stylesheets
        addFontDisplaySwap: function(link) {
            // Create a style element to add font-display: swap
            const style = document.createElement('style');
            style.textContent = `
                @font-face {
                    font-display: swap;
                }
            `;
            document.head.appendChild(style);
        },

        // Check if font is critical (used in above-the-fold content)
        isCriticalFont: function(fontUrl) {
            // Simple heuristic: consider Google Fonts and system fonts as critical
            return fontUrl.includes('fonts.googleapis.com') || 
                   fontUrl.includes('fonts.bunny.net') ||
                   fontUrl.includes('typekit.net');
        },

        // Preload critical fonts
        preloadFont: function(fontUrl) {
            const preloadLink = document.createElement('link');
            preloadLink.rel = 'preload';
            preloadLink.as = 'font';
            preloadLink.href = fontUrl;
            preloadLink.crossOrigin = 'anonymous';
            document.head.appendChild(preloadLink);

            if (this.config.enableLogging) {
                console.log('Preloading critical font:', fontUrl);
            }
        },

        // Monitor font loading performance
        monitorFontLoading: function() {
            if ('fonts' in document) {
                document.fonts.addEventListener('loading', (event) => {
                    this.fontsLoading.add(event.fontface.family);
                    
                    if (this.config.enableLogging) {
                        console.log('Font loading started:', event.fontface.family);
                    }
                });

                document.fonts.addEventListener('loadingdone', (event) => {
                    this.fontsLoading.delete(event.fontface.family);
                    
                    if (this.config.enableLogging) {
                        console.log('Font loaded:', event.fontface.family);
                    }
                });

                document.fonts.addEventListener('loadingerror', (event) => {
                    this.fontsLoading.delete(event.fontface.family);
                    console.warn('Font loading failed:', event.fontface.family);
                });
            }
        },

        // Setup deferred loading of non-critical CSS
        setupDeferredLoading: function() {
            const stylesheets = document.querySelectorAll(this.config.stylesheetSelector);
            
            stylesheets.forEach(link => {
                if (this.isNonCriticalStylesheet(link)) {
                    this.deferStylesheet(link);
                }
            });

            // Load deferred stylesheets after a delay
            setTimeout(() => {
                this.loadDeferredStylesheets();
            }, this.config.deferredLoadDelay);
        },

        // Check if stylesheet is non-critical
        isNonCriticalStylesheet: function(link) {
            const href = link.href;
            if (!href) return false;

            // Consider these as non-critical (can be loaded later)
            const nonCriticalPatterns = [
                'font-awesome',
                'bootstrap-icons',
                'print.css',
                'admin.css',
                'charts.css',
                'prism.css'
            ];

            return nonCriticalPatterns.some(pattern => href.includes(pattern));
        },

        // Defer stylesheet loading
        deferStylesheet: function(link) {
            // Change rel to preload to download but not apply
            link.rel = 'preload';
            link.as = 'style';
            
            // Store original href for later loading
            link.dataset.originalRel = 'stylesheet';
            link.dataset.deferred = 'true';

            if (this.config.enableLogging) {
                console.log('Deferred stylesheet:', link.href);
            }
        },

        // Load all deferred stylesheets
        loadDeferredStylesheets: function() {
            const deferredStylesheets = document.querySelectorAll('link[data-deferred="true"]');
            
            deferredStylesheets.forEach(link => {
                link.rel = 'stylesheet';
                link.removeAttribute('data-deferred');
                
                if (this.config.enableLogging) {
                    console.log('Loading deferred stylesheet:', link.href);
                }
            });
        },

        // Prevent layout shift by adding placeholders
        preventLayoutShift: function() {
            // Add explicit dimensions to images without them
            this.addImageDimensions();
            
            // Add min-height to dynamic content areas
            this.addDynamicContentPlaceholders();
            
            // Prevent font loading layout shift
            this.preventFontLayoutShift();
        },

        // Add dimensions to images to prevent layout shift
        addImageDimensions: function() {
            const images = document.querySelectorAll('img:not([width]):not([height])');
            
            images.forEach(img => {
                // Try to get dimensions from CSS or data attributes
                const width = img.dataset.width || img.style.width;
                const height = img.dataset.height || img.style.height;
                const aspectRatio = img.dataset.aspectRatio;

                if (width && height) {
                    img.width = parseInt(width);
                    img.height = parseInt(height);
                } else if (aspectRatio) {
                    img.style.aspectRatio = aspectRatio;
                } else {
                    // Set a default aspect ratio for unknown images
                    img.style.aspectRatio = '16/9';
                    img.style.width = '100%';
                    img.style.height = 'auto';
                }
            });
        },

        // Add placeholders for dynamic content to prevent layout shift
        addDynamicContentPlaceholders: function() {
            const dynamicContainers = document.querySelectorAll('[data-dynamic], .loading, [data-lazy-component]');
            
            dynamicContainers.forEach(container => {
                const minHeight = container.dataset.minHeight || '200px';
                
                if (!container.style.minHeight && !container.style.height) {
                    container.style.minHeight = minHeight;
                }
            });
        },

        // Prevent layout shift caused by font loading
        preventFontLayoutShift: function() {
            // Add fallback font metrics to prevent layout shift
            const style = document.createElement('style');
            style.textContent = `
                /* Fallback font metrics to match web fonts */
                body, html {
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    font-display: swap;
                }
                
                /* Size adjustments for common web font families */
                .font-inter, .font-figtree {
                    font-size-adjust: 0.5;
                }
            `;
            document.head.appendChild(style);
        },

        // Setup media query optimization
        setupMediaQueryOptimization: function() {
            // Only load styles for current viewport initially
            const currentViewport = this.getCurrentViewportType();
            
            // Defer styles for other viewports
            const mediaStylesheets = document.querySelectorAll('link[media]');
            
            mediaStylesheets.forEach(link => {
                const media = link.media;
                
                if (!window.matchMedia(media).matches) {
                    this.deferStylesheet(link);
                }
            });

            // Load appropriate styles when viewport changes
            window.addEventListener('resize', this.debounce(() => {
                this.handleViewportChange();
            }, 250));
        },

        // Get current viewport type
        getCurrentViewportType: function() {
            const width = window.innerWidth;
            
            if (width < 640) return 'mobile';
            if (width < 1024) return 'tablet';
            return 'desktop';
        },

        // Handle viewport changes
        handleViewportChange: function() {
            const newViewport = this.getCurrentViewportType();
            
            // Load deferred stylesheets that now match
            const deferredStylesheets = document.querySelectorAll('link[data-deferred="true"][media]');
            
            deferredStylesheets.forEach(link => {
                if (window.matchMedia(link.media).matches) {
                    link.rel = 'stylesheet';
                    link.removeAttribute('data-deferred');
                }
            });
        },

        // Generate critical CSS string
        generateCriticalCSS: function() {
            const sortedRules = Array.from(this.criticalStyles.entries())
                .sort(([, a], [, b]) => b.priority - a.priority)
                .map(([selector, data]) => data.cssText);

            return sortedRules.join('\n');
        },

        // Inline critical CSS
        inlineCriticalCSS: function() {
            if (document.getElementById(this.config.criticalCSSId)) {
                return; // Already inlined
            }

            const criticalCSS = this.generateCriticalCSS();
            
            if (criticalCSS) {
                const style = document.createElement('style');
                style.id = this.config.criticalCSSId;
                style.textContent = criticalCSS;
                
                // Insert at beginning of head for highest priority
                document.head.insertBefore(style, document.head.firstChild);
                
                if (this.config.enableLogging) {
                    console.log(`Inlined ${criticalCSS.length} characters of critical CSS`);
                }
            }
        },

        // Utility: Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Get optimization metrics
        getMetrics: function() {
            return {
                criticalRules: this.criticalStyles.size,
                deferredStylesheets: this.deferredStylesheets.length,
                fontsLoading: this.fontsLoading.size,
                isOptimized: this.isOptimized,
                criticalCSSSize: this.generateCriticalCSS().length
            };
        },

        // Export critical CSS (for build-time optimization)
        exportCriticalCSS: function() {
            return {
                css: this.generateCriticalCSS(),
                metrics: this.getMetrics(),
                timestamp: Date.now()
            };
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            CriticalCSSOptimizer.init();
        });
    } else {
        CriticalCSSOptimizer.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.CriticalCSSOptimizer = CriticalCSSOptimizer;

    // Expose for debugging and build tools
    window.exportCriticalCSS = () => CriticalCSSOptimizer.exportCriticalCSS();
    window.getCSSOptimizationMetrics = () => CriticalCSSOptimizer.getMetrics();

})(window, document);
