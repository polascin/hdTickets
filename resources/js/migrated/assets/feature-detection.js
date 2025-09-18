/**
 * HD Tickets Feature Detection & Progressive Enhancement
 * 
 * This script:
 * - Detects browser capabilities and adds appropriate classes
 * - Provides fallbacks for modern CSS features
 * - Handles browser-specific quirks
 * - Implements progressive enhancement patterns
 */

(function(window, document) {
    'use strict';

    // Feature Detection Object
    const FeatureDetection = {
        
        // Initialize feature detection
        init: function() {
            this.detectBrowser();
            this.detectFeatures();
            this.applyPolyfills();
            this.handleSafariSpecificFixes();
            this.handleFirefoxSpecificFixes();
            this.handleEdgeSpecificFixes();
            this.setupProgressiveEnhancement();
        },

        // Detect browser and add classes
        detectBrowser: function() {
            const html = document.documentElement;
            const userAgent = navigator.userAgent.toLowerCase();
            
            // Remove no-js class
            html.classList.remove('no-js');
            html.classList.add('js');
            
            // Browser detection
            if (userAgent.indexOf('safari') !== -1 && userAgent.indexOf('chrome') === -1) {
                html.classList.add('safari');
                
                // iOS Safari detection
                if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                    html.classList.add('ios-safari');
                }
            }
            
            if (userAgent.indexOf('firefox') !== -1) {
                html.classList.add('firefox');
            }
            
            if (userAgent.indexOf('edge') !== -1 || userAgent.indexOf('edg/') !== -1) {
                html.classList.add('edge');
            }
            
            if (userAgent.indexOf('chrome') !== -1 && userAgent.indexOf('edge') === -1) {
                html.classList.add('chrome');
            }
            
            // IE detection
            if (userAgent.indexOf('trident') !== -1 || userAgent.indexOf('msie') !== -1) {
                html.classList.add('ie');
            }

            // Touch device detection
            if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
                html.classList.add('touch');
            } else {
                html.classList.add('no-touch');
            }

            // Mobile device detection
            if (window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                html.classList.add('mobile');
            } else {
                html.classList.add('desktop');
            }
        },

        // Feature detection for CSS and JS capabilities
        detectFeatures: function() {
            const html = document.documentElement;
            const testElement = document.createElement('div');
            
            // CSS Features
            const features = {
                // Layout features
                grid: this.supportsCSS('display', 'grid'),
                flexbox: this.supportsCSS('display', 'flex'),
                sticky: this.supportsCSS('position', 'sticky'),
                
                // Visual features
                objectFit: this.supportsCSS('object-fit', 'cover'),
                clipPath: this.supportsCSS('clip-path', 'polygon(0 0, 100% 0, 100% 100%, 0 100%)'),
                backdropFilter: this.supportsCSS('backdrop-filter', 'blur(5px)') || 
                               this.supportsCSS('-webkit-backdrop-filter', 'blur(5px)'),
                
                // CSS Custom Properties
                customProperties: this.supportsCSSCustomProperties(),
                
                // Transform features
                transforms: this.supportsCSS('transform', 'translateX(0)'),
                transforms3d: this.supportsCSS('transform', 'translate3d(0,0,0)'),
                
                // JavaScript features
                intersection: 'IntersectionObserver' in window,
                webGL: this.supportsWebGL(),
                localStorage: this.supportsLocalStorage(),
                
                // Media features
                prefers: this.supportsPrefersMediaQueries()
            };

            // Apply feature classes
            Object.keys(features).forEach(feature => {
                if (features[feature]) {
                    html.classList.add('supports-' + feature);
                } else {
                    html.classList.add('no-' + feature);
                }
            });

            // Store features for later use
            window.HDTickets = window.HDTickets || {};
            window.HDTickets.features = features;
        },

        // CSS Support Detection
        supportsCSS: function(property, value) {
            const testElement = document.createElement('div');
            
            try {
                testElement.style[property] = value;
                return testElement.style[property] === value;
            } catch (e) {
                return false;
            }
        },

        // CSS Custom Properties Support
        supportsCSSCustomProperties: function() {
            try {
                return window.CSS && window.CSS.supports && window.CSS.supports('--test', 'var(--test)');
            } catch (e) {
                return false;
            }
        },

        // WebGL Support
        supportsWebGL: function() {
            try {
                const canvas = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && 
                         (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
            } catch (e) {
                return false;
            }
        },

        // Local Storage Support
        supportsLocalStorage: function() {
            try {
                const test = 'test';
                localStorage.setItem(test, test);
                localStorage.removeItem(test);
                return true;
            } catch (e) {
                return false;
            }
        },

        // Prefers Media Queries Support
        supportsPrefersMediaQueries: function() {
            return window.matchMedia && window.matchMedia('(prefers-reduced-motion)').media !== 'not all';
        },

        // Apply polyfills and fallbacks
        applyPolyfills: function() {
            // CSS Custom Properties Polyfill for IE11
            if (!window.HDTickets.features.customProperties) {
                this.cssCustomPropertiesPolyfill();
            }

            // Intersection Observer Polyfill
            if (!window.HDTickets.features.intersection) {
                this.loadPolyfill('intersection-observer');
            }

            // Object-fit polyfill
            if (!window.HDTickets.features.objectFit) {
                this.objectFitPolyfill();
            }

            // Sticky position polyfill
            if (!window.HDTickets.features.sticky) {
                this.stickyPolyfill();
            }
        },

        // CSS Custom Properties Polyfill
        cssCustomPropertiesPolyfill: function() {
            const cssCustomProperties = {
                '--hd-primary': '#3b82f6',
                '--hd-secondary': '#6366f1',
                '--hd-success': '#10b981',
                '--hd-warning': '#f59e0b',
                '--hd-error': '#ef4444',
                '--hd-gray-100': '#f3f4f6',
                '--hd-gray-200': '#e5e7eb',
                '--hd-gray-400': '#9ca3af',
                '--hd-gray-500': '#6b7280',
                '--hd-gray-700': '#374151',
                '--hd-text-primary': '#111827',
                '--hd-text-secondary': '#4b5563',
                '--hd-text-muted': '#6b7280',
                '--hd-bg-surface': '#ffffff',
                '--hd-border-color': '#e5e7eb',
                '--hd-radius': '8px',
                '--hd-radius-sm': '4px',
                '--hd-radius-lg': '12px',
                '--hd-spacing-1': '4px',
                '--hd-spacing-2': '8px',
                '--hd-spacing-3': '12px',
                '--hd-spacing-4': '16px',
                '--hd-spacing-5': '20px',
                '--hd-spacing-6': '24px',
                '--hd-spacing-8': '32px',
                '--hd-shadow-sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                '--hd-shadow-md': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                '--hd-shadow-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                '--hd-transition': '0.2s ease'
            };

            // Apply fallback values
            const style = document.createElement('style');
            let css = '';
            
            Object.keys(cssCustomProperties).forEach(property => {
                const value = cssCustomProperties[property];
                const className = property.replace('--hd-', '').replace(/-/g, '_');
                css += `.ie .${className} { /* IE11 fallback styles */ }\n`;
            });
            
            style.innerHTML = css;
            document.head.appendChild(style);
        },

        // Object-fit polyfill
        objectFitPolyfill: function() {
            const images = document.querySelectorAll('img[data-object-fit]');
            
            images.forEach(img => {
                const objectFit = img.getAttribute('data-object-fit') || 'cover';
                const objectPosition = img.getAttribute('data-object-position') || 'center';
                
                if (img.parentElement) {
                    const wrapper = img.parentElement;
                    wrapper.style.position = 'relative';
                    wrapper.style.overflow = 'hidden';
                    
                    img.style.position = 'absolute';
                    img.style.top = '0';
                    img.style.left = '0';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    
                    if (objectFit === 'cover') {
                        img.style.objectFit = 'cover';
                        img.style.objectPosition = objectPosition;
                        
                        // Fallback for older browsers
                        img.style.minWidth = '100%';
                        img.style.minHeight = '100%';
                    }
                }
            });
        },

        // Sticky position polyfill
        stickyPolyfill: function() {
            const stickyElements = document.querySelectorAll('[data-sticky]');
            
            stickyElements.forEach(element => {
                let isSticky = false;
                const originalTop = element.offsetTop;
                
                const checkSticky = () => {
                    const scrollTop = window.pageYOffset;
                    
                    if (scrollTop >= originalTop && !isSticky) {
                        isSticky = true;
                        element.style.position = 'fixed';
                        element.style.top = '0';
                        element.style.left = '0';
                        element.style.right = '0';
                        element.style.zIndex = '40';
                        element.classList.add('is-sticky');
                    } else if (scrollTop < originalTop && isSticky) {
                        isSticky = false;
                        element.style.position = '';
                        element.style.top = '';
                        element.style.left = '';
                        element.style.right = '';
                        element.style.zIndex = '';
                        element.classList.remove('is-sticky');
                    }
                };
                
                window.addEventListener('scroll', checkSticky);
                window.addEventListener('resize', checkSticky);
            });
        },

        // Safari-specific fixes
        handleSafariSpecificFixes: function() {
            if (!document.documentElement.classList.contains('safari')) return;

            // Fix iOS Safari viewport height issue
            if (document.documentElement.classList.contains('ios-safari')) {
                const fixViewportHeight = () => {
                    document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
                };
                
                fixViewportHeight();
                window.addEventListener('resize', fixViewportHeight);
                window.addEventListener('orientationchange', fixViewportHeight);
            }

            // Fix Safari flexbox shrinking
            const safariFlexFix = () => {
                const flexItems = document.querySelectorAll('.hd-card, .hd-stat-card');
                flexItems.forEach(item => {
                    item.style.flexShrink = '0';
                });
            };
            
            document.addEventListener('DOMContentLoaded', safariFlexFix);

            // Fix Safari form zoom
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (parseFloat(getComputedStyle(input).fontSize) < 16) {
                    input.style.fontSize = '16px';
                }
            });

            // Handle safe area insets
            if (window.CSS && window.CSS.supports('padding-top: env(safe-area-inset-top)')) {
                document.documentElement.classList.add('has-safe-area');
            }
        },

        // Firefox-specific fixes
        handleFirefoxSpecificFixes: function() {
            if (!document.documentElement.classList.contains('firefox')) return;

            // Fix Firefox flexbox min-height issue
            const firefoxFlexFix = () => {
                const flexItems = document.querySelectorAll('.hd-card, .hd-stat-card');
                flexItems.forEach(item => {
                    item.style.minHeight = '0';
                });
            };
            
            document.addEventListener('DOMContentLoaded', firefoxFlexFix);

            // Fix Firefox button focus outline
            const buttons = document.querySelectorAll('button, .hd-btn');
            buttons.forEach(button => {
                button.addEventListener('focus', (e) => {
                    e.target.style.outline = '2px solid #3b82f6';
                    e.target.style.outlineOffset = '2px';
                });
                
                button.addEventListener('blur', (e) => {
                    e.target.style.outline = '';
                    e.target.style.outlineOffset = '';
                });
            });
        },

        // Edge-specific fixes
        handleEdgeSpecificFixes: function() {
            if (!document.documentElement.classList.contains('edge')) return;

            // Fix Edge input height consistency
            const edgeInputFix = () => {
                const inputs = document.querySelectorAll('.hd-input, .hd-select');
                inputs.forEach(input => {
                    input.style.height = '44px';
                    input.style.lineHeight = 'normal';
                });
            };
            
            document.addEventListener('DOMContentLoaded', edgeInputFix);
        },

        // Progressive Enhancement Setup
        setupProgressiveEnhancement: function() {
            // Enhanced grid layout for modern browsers
            if (window.HDTickets.features.grid) {
                const grids = document.querySelectorAll('.hd-grid, .hd-card-grid');
                grids.forEach(grid => {
                    grid.classList.add('hd-grid--enhanced');
                });
            }

            // Enhanced animations for capable browsers
            if (window.HDTickets.features.transforms3d) {
                document.documentElement.classList.add('enhanced-animations');
            }

            // Lazy loading for modern browsers
            if (window.HDTickets.features.intersection) {
                this.setupLazyLoading();
            }

            // Smooth scrolling for modern browsers
            if (window.CSS && window.CSS.supports('scroll-behavior', 'smooth')) {
                document.documentElement.style.scrollBehavior = 'smooth';
            }

            // Reduced motion preferences
            if (window.HDTickets.features.prefers) {
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
                
                if (prefersReducedMotion.matches) {
                    document.documentElement.classList.add('prefers-reduced-motion');
                }
                
                prefersReducedMotion.addEventListener('change', (e) => {
                    if (e.matches) {
                        document.documentElement.classList.add('prefers-reduced-motion');
                    } else {
                        document.documentElement.classList.remove('prefers-reduced-motion');
                    }
                });
            }
        },

        // Lazy Loading Setup
        setupLazyLoading: function() {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                            img.classList.remove('lazy');
                            img.classList.add('lazy-loaded');
                        }
                        
                        observer.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => {
                img.classList.add('lazy');
                imageObserver.observe(img);
            });
        },

        // Load external polyfills
        loadPolyfill: function(polyfillName) {
            const script = document.createElement('script');
            const polyfills = {
                'intersection-observer': 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver'
            };
            
            if (polyfills[polyfillName]) {
                script.src = polyfills[polyfillName];
                script.async = true;
                document.head.appendChild(script);
            }
        },

        // Utility function to debounce events
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
        }
    };

    // Browser-specific performance optimizations
    const PerformanceOptimizations = {
        init: function() {
            this.optimizeScrolling();
            this.optimizeAnimations();
            this.optimizeImages();
        },

        optimizeScrolling: function() {
            // Passive event listeners for better scroll performance
            const passiveEvents = ['scroll', 'touchstart', 'touchmove', 'touchend', 'wheel'];
            
            passiveEvents.forEach(eventName => {
                const originalAddEventListener = EventTarget.prototype.addEventListener;
                EventTarget.prototype.addEventListener = function(type, listener, options) {
                    if (passiveEvents.includes(type) && typeof options !== 'object') {
                        options = { passive: true };
                    }
                    return originalAddEventListener.call(this, type, listener, options);
                };
            });
        },

        optimizeAnimations: function() {
            // Use requestAnimationFrame for animations
            window.requestAnimFrame = window.requestAnimationFrame ||
                                     window.webkitRequestAnimationFrame ||
                                     window.mozRequestAnimationFrame ||
                                     function(callback) { 
                                         return window.setTimeout(callback, 1000 / 60); 
                                     };

            // Cancel animation frame polyfill
            window.cancelAnimFrame = window.cancelAnimationFrame ||
                                    window.webkitCancelAnimationFrame ||
                                    window.mozCancelAnimationFrame ||
                                    function(id) { 
                                        clearTimeout(id); 
                                    };
        },

        optimizeImages: function() {
            // WebP support detection and fallback
            const webpSupport = document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') === 0;
            
            if (webpSupport) {
                document.documentElement.classList.add('webp');
            } else {
                document.documentElement.classList.add('no-webp');
            }
        }
    };

    // Initialize everything when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            FeatureDetection.init();
            PerformanceOptimizations.init();
        });
    } else {
        FeatureDetection.init();
        PerformanceOptimizations.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.FeatureDetection = FeatureDetection;
    window.HDTickets.PerformanceOptimizations = PerformanceOptimizations;

})(window, document);
