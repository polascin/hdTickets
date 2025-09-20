/**
 * Dashboard Performance and Accessibility Enhancements
 * HD Tickets Sports Events Entry System
 */

(function() {
    'use strict';

    // Performance monitoring
    const Performance = {
        // Track loading times
        startTime: null,
        loadTimes: {},

        start(key) {
            this.startTime = performance.now();
            this.loadTimes[key] = { start: this.startTime };
        },

        end(key) {
            const endTime = performance.now();
            if (this.loadTimes[key]) {
                this.loadTimes[key].end = endTime;
                this.loadTimes[key].duration = endTime - this.loadTimes[key].start;
                console.log(`⚡ ${key}: ${this.loadTimes[key].duration.toFixed(2)}ms`);
            }
        },

        // Monitor memory usage (if available)
        checkMemory() {
            if (performance.memory) {
                console.log('🧠 Memory usage:', {
                    used: Math.round(performance.memory.usedJSHeapSize / 1048576 * 100) / 100 + 'MB',
                    total: Math.round(performance.memory.totalJSHeapSize / 1048576 * 100) / 100 + 'MB',
                    limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576 * 100) / 100 + 'MB'
                });
            }
        }
    };

    // Accessibility manager
    const A11y = {
        // Announce to screen readers
        announce(message, priority = 'polite') {
            const announcer = document.getElementById('a11y-announcer');
            if (announcer) {
                announcer.setAttribute('aria-live', priority);
                announcer.textContent = message;
            }
        },

        // Focus management
        trapFocus(element) {
            const focusableElements = element.querySelectorAll(
                'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex="0"]'
            );
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            element.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey && document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
                
                if (e.key === 'Escape') {
                    element.style.display = 'none';
                }
            });
        },

        // Skip link functionality
        setupSkipLinks() {
            const skipLinks = document.querySelectorAll('a[href^="#"]');
            skipLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const targetId = link.getAttribute('href').substring(1);
                    const target = document.getElementById(targetId);
                    if (target) {
                        e.preventDefault();
                        target.focus();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        // High contrast mode detection
        setupHighContrast() {
            if (window.matchMedia && window.matchMedia('(prefers-contrast: high)').matches) {
                document.body.classList.add('high-contrast');
            }
        },

        // Reduced motion detection
        setupReducedMotion() {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.body.classList.add('reduced-motion');
            }
        }
    };

    // Mobile optimizations
    const Mobile = {
        // Detect mobile device
        isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                   window.innerWidth < 768;
        },

        // Optimize touch events
        setupTouchOptimizations() {
            if (this.isMobile()) {
                // Add touch feedback
                document.body.classList.add('mobile-device');
                
                // Prevent zoom on double tap for buttons
                const buttons = document.querySelectorAll('button, .btn, [role="button"]');
                buttons.forEach(button => {
                    button.addEventListener('touchend', function(e) {
                        e.preventDefault();
                        this.click();
                    }, { passive: false });
                });

                // Optimize scrolling
                this.setupSmoothScrolling();
                
                // Handle orientation changes
                this.setupOrientationChange();
            }
        },

        setupSmoothScrolling() {
            // Add momentum scrolling for iOS
            document.body.style.webkitOverflowScrolling = 'touch';
            
            // Optimize scroll performance
            let ticking = false;
            
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        this.handleScroll();
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
        },

        handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Update navigation scroll state
            const nav = document.querySelector('nav');
            if (nav) {
                if (scrollTop > 10) {
                    nav.classList.add('nav-scrolled');
                } else {
                    nav.classList.remove('nav-scrolled');
                }
            }
        },

        setupOrientationChange() {
            window.addEventListener('orientationchange', () => {
                // Force redraw after orientation change
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            });
        },

        // Optimize images for mobile
        setupImageOptimization() {
            const images = document.querySelectorAll('img[data-src]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            } else {
                // Fallback for browsers without IntersectionObserver
                images.forEach(img => {
                    img.src = img.dataset.src;
                });
            }
        }
    };

    // Network optimization
    const Network = {
        // Connection quality detection
        getConnectionType() {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (connection) {
                return {
                    effectiveType: connection.effectiveType,
                    downlink: connection.downlink,
                    rtt: connection.rtt,
                    saveData: connection.saveData
                };
            }
            return null;
        },

        // Optimize based on connection
        optimizeForConnection() {
            const connection = this.getConnectionType();
            if (connection) {
                console.log('🌐 Connection:', connection);
                
                // Reduce polling frequency on slow connections
                if (connection.effectiveType === '2g' || connection.effectiveType === 'slow-2g') {
                    HDTickets.Dashboard.config.refreshInterval = 60000; // 1 minute
                } else if (connection.effectiveType === '3g') {
                    HDTickets.Dashboard.config.refreshInterval = 45000; // 45 seconds
                }
                
                // Disable auto-refresh on save-data mode
                if (connection.saveData) {
                    console.log('📱 Data saver mode detected, reducing data usage');
                    HDTickets.Dashboard.config.refreshInterval = 120000; // 2 minutes
                }
            }
        },

        // Service worker registration for caching
        registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('🔧 Service Worker registered:', registration);
                    })
                    .catch(error => {
                        console.log('❌ Service Worker registration failed:', error);
                    });
            }
        }
    };

    // Error handling and reporting
    const ErrorHandler = {
        setup() {
            // Global error handler
            window.addEventListener('error', (event) => {
                this.logError('JavaScript Error', event.error, {
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno
                });
            });

            // Promise rejection handler
            window.addEventListener('unhandledrejection', (event) => {
                this.logError('Unhandled Promise Rejection', event.reason);
                
                // Show user-friendly message
                if (HDTickets && HDTickets.Dashboard && HDTickets.Dashboard.ui) {
                    HDTickets.Dashboard.ui.showToast(
                        'A network error occurred. Please check your connection.',
                        'error'
                    );
                }
            });
        },

        logError(type, error, details = {}) {
            const errorInfo = {
                type,
                message: error.message || error,
                stack: error.stack,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href,
                ...details
            };

            console.error('🚨 Error logged:', errorInfo);
            
            // Send to error tracking service (if available)
            if (window.analytics && typeof window.analytics.track === 'function') {
                window.analytics.track('Dashboard Error', errorInfo);
            }
        }
    };

    // Performance observer for Core Web Vitals
    const WebVitals = {
        setup() {
            if ('PerformanceObserver' in window) {
                // Largest Contentful Paint
                this.observeLCP();
                
                // First Input Delay
                this.observeFID();
                
                // Cumulative Layout Shift
                this.observeCLS();
            }
        },

        observeLCP() {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                console.log('🎯 LCP:', Math.round(lastEntry.startTime), 'ms');
            });
            
            observer.observe({ entryTypes: ['largest-contentful-paint'] });
        },

        observeFID() {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    console.log('⚡ FID:', Math.round(entry.processingStart - entry.startTime), 'ms');
                });
            });
            
            observer.observe({ entryTypes: ['first-input'] });
        },

        observeCLS() {
            let clsValue = 0;
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                        console.log('📐 CLS:', Math.round(clsValue * 1000) / 1000);
                    }
                });
            });
            
            observer.observe({ entryTypes: ['layout-shift'] });
        }
    };

    // Initialize everything when DOM is ready
    function initialize() {
        console.log('🚀 Dashboard performance enhancements initializing...');
        
        Performance.start('dashboard-init');
        
        // Setup accessibility features
        A11y.setupSkipLinks();
        A11y.setupHighContrast();
        A11y.setupReducedMotion();
        
        // Setup mobile optimizations
        Mobile.setupTouchOptimizations();
        Mobile.setupImageOptimization();
        
        // Setup network optimizations
        Network.optimizeForConnection();
        Network.registerServiceWorker();
        
        // Setup error handling
        ErrorHandler.setup();
        
        // Setup performance monitoring
        WebVitals.setup();
        
        Performance.end('dashboard-init');
        
        // Check memory usage periodically in development
        if (process.env.NODE_ENV === 'development') {
            setInterval(() => Performance.checkMemory(), 30000);
        }
        
        console.log('✅ Dashboard performance enhancements ready');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Expose utilities globally
    window.DashboardPerformance = {
        Performance,
        A11y,
        Mobile,
        Network,
        ErrorHandler,
        WebVitals
    };

})();