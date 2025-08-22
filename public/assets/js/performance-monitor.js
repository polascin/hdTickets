/**
 * HD Tickets Performance Monitoring System
 * 
 * Monitors Core Web Vitals and layout performance:
 * - Cumulative Layout Shift (CLS)
 * - First Input Delay (FID)
 * - Largest Contentful Paint (LCP)
 * - First Contentful Paint (FCP)
 * - Time to Interactive (TTI)
 */

(function(window, document) {
    'use strict';

    const PerformanceMonitor = {
        // Configuration
        config: {
            enableLogging: true,
            enableReporting: false,
            reportingEndpoint: '/api/performance-metrics',
            sampling: 1.0, // 100% sampling
            thresholds: {
                LCP: 2500, // 2.5s (Good)
                FID: 100,  // 100ms (Good)
                CLS: 0.1,  // 0.1 (Good)
                FCP: 1800, // 1.8s (Good)
                TTI: 3800  // 3.8s (Good)
            }
        },

        // Metrics storage
        metrics: {},
        observers: [],

        // Initialize performance monitoring
        init: function() {
            console.log('🚀 HD Tickets Performance Monitor initialized');
            
            this.setupWebVitalsMonitoring();
            this.setupLayoutShiftMonitoring();
            this.setupResourceTiming();
            this.setupNavigationTiming();
            this.setupLongTaskMonitoring();
            this.setupMemoryMonitoring();
            this.setupNetworkMonitoring();
            
            // Report metrics when page is about to unload
            window.addEventListener('beforeunload', () => {
                this.reportMetrics();
            });

            // Report metrics on visibility change (for SPAs)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    this.reportMetrics();
                }
            });

            // Set up periodic reporting
            this.setupPeriodicReporting();
        },

        // Setup Web Vitals monitoring using native APIs
        setupWebVitalsMonitoring: function() {
            // Largest Contentful Paint (LCP)
            if ('PerformanceObserver' in window) {
                try {
                    const lcpObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        
                        this.metrics.LCP = {
                            value: lastEntry.startTime,
                            entries: entries,
                            timestamp: Date.now()
                        };
                        
                        this.evaluateMetric('LCP', lastEntry.startTime);
                    });
                    
                    lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
                    this.observers.push(lcpObserver);
                } catch (e) {
                    console.warn('LCP monitoring not supported');
                }

                // First Contentful Paint (FCP)
                try {
                    const fcpObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        entries.forEach((entry) => {
                            if (entry.name === 'first-contentful-paint') {
                                this.metrics.FCP = {
                                    value: entry.startTime,
                                    timestamp: Date.now()
                                };
                                
                                this.evaluateMetric('FCP', entry.startTime);
                            }
                        });
                    });
                    
                    fcpObserver.observe({ entryTypes: ['paint'] });
                    this.observers.push(fcpObserver);
                } catch (e) {
                    console.warn('FCP monitoring not supported');
                }

                // First Input Delay (FID)
                try {
                    const fidObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        entries.forEach((entry) => {
                            this.metrics.FID = {
                                value: entry.processingStart - entry.startTime,
                                entries: [entry],
                                timestamp: Date.now()
                            };
                            
                            this.evaluateMetric('FID', entry.processingStart - entry.startTime);
                        });
                    });
                    
                    fidObserver.observe({ entryTypes: ['first-input'] });
                    this.observers.push(fidObserver);
                } catch (e) {
                    console.warn('FID monitoring not supported');
                }
            }
        },

        // Setup Cumulative Layout Shift monitoring
        setupLayoutShiftMonitoring: function() {
            if ('PerformanceObserver' in window) {
                try {
                    let clsValue = 0;
                    let sessionValue = 0;
                    let sessionEntries = [];
                    
                    const clsObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        
                        entries.forEach((entry) => {
                            // Only count layout shifts without recent user input
                            if (!entry.hadRecentInput) {
                                const firstSessionEntry = sessionEntries[0];
                                const lastSessionEntry = sessionEntries[sessionEntries.length - 1];
                                
                                // If the entry occurred less than 1 second after the previous entry
                                // and less than 5 seconds after the first entry in the session,
                                // include the entry in the current session.
                                if (sessionValue &&
                                    entry.startTime - lastSessionEntry.startTime < 1000 &&
                                    entry.startTime - firstSessionEntry.startTime < 5000) {
                                    sessionValue += entry.value;
                                    sessionEntries.push(entry);
                                } else {
                                    sessionValue = entry.value;
                                    sessionEntries = [entry];
                                }
                                
                                // If the current session value is larger than the current CLS value,
                                // update CLS and the entries contributing to it.
                                if (sessionValue > clsValue) {
                                    clsValue = sessionValue;
                                    this.metrics.CLS = {
                                        value: clsValue,
                                        entries: [...sessionEntries],
                                        timestamp: Date.now()
                                    };
                                    
                                    this.evaluateMetric('CLS', clsValue);
                                    
                                    if (this.config.enableLogging && clsValue > 0.05) {
                                        console.warn(`Layout shift detected: ${clsValue.toFixed(4)}`, entry);
                                        this.logLayoutShiftDetails(entry);
                                    }
                                }
                            }
                        });
                    });
                    
                    clsObserver.observe({ entryTypes: ['layout-shift'] });
                    this.observers.push(clsObserver);
                } catch (e) {
                    console.warn('CLS monitoring not supported');
                }
            }
        },

        // Setup resource timing monitoring
        setupResourceTiming: function() {
            if ('PerformanceObserver' in window) {
                try {
                    const resourceObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        
                        entries.forEach((entry) => {
                            // Monitor slow resources
                            const duration = entry.responseEnd - entry.startTime;
                            
                            if (duration > 1000) { // Slow resources (>1s)
                                if (this.config.enableLogging) {
                                    console.warn(`Slow resource detected: ${entry.name} (${duration.toFixed(0)}ms)`);
                                }
                                
                                if (!this.metrics.slowResources) {
                                    this.metrics.slowResources = [];
                                }
                                
                                this.metrics.slowResources.push({
                                    name: entry.name,
                                    duration: duration,
                                    size: entry.transferSize,
                                    type: this.getResourceType(entry.name),
                                    timestamp: Date.now()
                                });
                            }
                            
                            // Monitor failed resources
                            if (entry.responseStatus >= 400) {
                                if (!this.metrics.failedResources) {
                                    this.metrics.failedResources = [];
                                }
                                
                                this.metrics.failedResources.push({
                                    name: entry.name,
                                    status: entry.responseStatus,
                                    timestamp: Date.now()
                                });
                            }
                        });
                    });
                    
                    resourceObserver.observe({ entryTypes: ['resource'] });
                    this.observers.push(resourceObserver);
                } catch (e) {
                    console.warn('Resource timing monitoring not supported');
                }
            }
        },

        // Setup navigation timing
        setupNavigationTiming: function() {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const navTiming = performance.getEntriesByType('navigation')[0];
                    
                    if (navTiming) {
                        this.metrics.navigationTiming = {
                            dns: navTiming.domainLookupEnd - navTiming.domainLookupStart,
                            tcp: navTiming.connectEnd - navTiming.connectStart,
                            request: navTiming.responseStart - navTiming.requestStart,
                            response: navTiming.responseEnd - navTiming.responseStart,
                            domInteractive: navTiming.domInteractive,
                            domComplete: navTiming.domComplete,
                            loadComplete: navTiming.loadEventEnd,
                            timestamp: Date.now()
                        };
                        
                        // Calculate Time to Interactive approximation
                        this.metrics.TTI = {
                            value: navTiming.domInteractive,
                            timestamp: Date.now()
                        };
                        
                        this.evaluateMetric('TTI', navTiming.domInteractive);
                        
                        if (this.config.enableLogging) {
                            console.log('Navigation Timing:', this.metrics.navigationTiming);
                        }
                    }
                }, 0);
            });
        },

        // Setup long task monitoring
        setupLongTaskMonitoring: function() {
            if ('PerformanceObserver' in window) {
                try {
                    const longTaskObserver = new PerformanceObserver((entryList) => {
                        const entries = entryList.getEntries();
                        
                        entries.forEach((entry) => {
                            if (!this.metrics.longTasks) {
                                this.metrics.longTasks = [];
                            }
                            
                            this.metrics.longTasks.push({
                                duration: entry.duration,
                                startTime: entry.startTime,
                                timestamp: Date.now()
                            });
                            
                            if (this.config.enableLogging && entry.duration > 100) {
                                console.warn(`Long task detected: ${entry.duration.toFixed(0)}ms`);
                            }
                        });
                    });
                    
                    longTaskObserver.observe({ entryTypes: ['longtask'] });
                    this.observers.push(longTaskObserver);
                } catch (e) {
                    console.warn('Long task monitoring not supported');
                }
            }
        },

        // Setup memory monitoring
        setupMemoryMonitoring: function() {
            if ('memory' in performance) {
                setInterval(() => {
                    this.metrics.memory = {
                        used: performance.memory.usedJSHeapSize,
                        total: performance.memory.totalJSHeapSize,
                        limit: performance.memory.jsHeapSizeLimit,
                        timestamp: Date.now()
                    };
                    
                    // Warn about high memory usage
                    const usage = performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit;
                    if (usage > 0.9 && this.config.enableLogging) {
                        console.warn(`High memory usage: ${(usage * 100).toFixed(1)}%`);
                    }
                }, 30000); // Check every 30 seconds
            }
        },

        // Setup network monitoring
        setupNetworkMonitoring: function() {
            if ('connection' in navigator) {
                const updateNetworkInfo = () => {
                    this.metrics.network = {
                        effectiveType: navigator.connection.effectiveType,
                        downlink: navigator.connection.downlink,
                        rtt: navigator.connection.rtt,
                        saveData: navigator.connection.saveData,
                        timestamp: Date.now()
                    };
                };
                
                updateNetworkInfo();
                navigator.connection.addEventListener('change', updateNetworkInfo);
            }
        },

        // Log layout shift details
        logLayoutShiftDetails: function(entry) {
            if (entry.sources) {
                entry.sources.forEach((source, index) => {
                    console.log(`Layout shift source ${index + 1}:`, {
                        element: source.node,
                        previousRect: source.previousRect,
                        currentRect: source.currentRect
                    });
                });
            }
        },

        // Evaluate metric against thresholds
        evaluateMetric: function(metricName, value) {
            const threshold = this.config.thresholds[metricName];
            if (!threshold) return;
            
            const status = value <= threshold ? 'good' : 'needs-improvement';
            
            if (this.config.enableLogging) {
                const color = status === 'good' ? 'color: green' : 'color: orange';
                console.log(`%c${metricName}: ${value.toFixed(2)}ms (${status})`, color);
            }
            
            // Store evaluation result
            if (this.metrics[metricName]) {
                this.metrics[metricName].status = status;
                this.metrics[metricName].threshold = threshold;
            }
        },

        // Get resource type from URL
        getResourceType: function(url) {
            if (url.match(/\.(css)$/)) return 'stylesheet';
            if (url.match(/\.(js)$/)) return 'script';
            if (url.match(/\.(png|jpg|jpeg|gif|svg|webp)$/)) return 'image';
            if (url.match(/\.(woff|woff2|ttf|otf)$/)) return 'font';
            return 'other';
        },

        // Setup periodic reporting
        setupPeriodicReporting: function() {
            // Report metrics every 30 seconds
            setInterval(() => {
                if (this.config.enableReporting) {
                    this.reportMetrics();
                }
            }, 30000);
        },

        // Report metrics to server
        reportMetrics: function() {
            if (!this.config.enableReporting || Math.random() > this.config.sampling) {
                return;
            }
            
            const reportData = {
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: Date.now(),
                metrics: this.metrics,
                performance: {
                    navigation: performance.getEntriesByType('navigation')[0],
                    paint: performance.getEntriesByType('paint'),
                    memory: this.metrics.memory,
                    network: this.metrics.network
                }
            };
            
            // Use sendBeacon if available (doesn't block page unload)
            if (navigator.sendBeacon && this.config.reportingEndpoint) {
                navigator.sendBeacon(
                    this.config.reportingEndpoint,
                    JSON.stringify(reportData)
                );
            } else if (this.config.reportingEndpoint) {
                // Fallback to fetch
                fetch(this.config.reportingEndpoint, {
                    method: 'POST',
                    body: JSON.stringify(reportData),
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    keepalive: true
                }).catch(err => {
                    console.warn('Failed to report performance metrics:', err);
                });
            }
            
            if (this.config.enableLogging) {
                console.log('📊 Performance metrics reported:', reportData);
            }
        },

        // Get current performance summary
        getPerformanceSummary: function() {
            return {
                coreWebVitals: {
                    LCP: this.metrics.LCP,
                    FID: this.metrics.FID,
                    CLS: this.metrics.CLS
                },
                otherMetrics: {
                    FCP: this.metrics.FCP,
                    TTI: this.metrics.TTI
                },
                resources: {
                    slow: this.metrics.slowResources?.length || 0,
                    failed: this.metrics.failedResources?.length || 0
                },
                tasks: {
                    long: this.metrics.longTasks?.length || 0
                },
                memory: this.metrics.memory,
                network: this.metrics.network
            };
        },

        // Enable/disable reporting
        enableReporting: function(endpoint) {
            this.config.enableReporting = true;
            if (endpoint) {
                this.config.reportingEndpoint = endpoint;
            }
        },

        disableReporting: function() {
            this.config.enableReporting = false;
        },

        // Clean up observers
        destroy: function() {
            this.observers.forEach(observer => {
                observer.disconnect();
            });
            this.observers = [];
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            PerformanceMonitor.init();
        });
    } else {
        PerformanceMonitor.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.PerformanceMonitor = PerformanceMonitor;

    // Expose utilities for debugging
    window.getPerformanceMetrics = () => PerformanceMonitor.getPerformanceSummary();
    window.reportPerformanceMetrics = () => PerformanceMonitor.reportMetrics();

})(window, document);
