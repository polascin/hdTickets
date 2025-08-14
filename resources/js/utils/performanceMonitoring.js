/**
 * HD Tickets - Enhanced Browser Performance Monitoring
 * Using Performance Timing API and Real User Monitoring (RUM)
 */

class PerformanceMonitoring {
    constructor() {
        this.metrics = {
            navigation: {},
            resources: [],
            userInteractions: [],
            customMarks: {},
            vitals: {}
        };
        
        this.config = {
            enabled: true,
            enableRUM: true,
            reportingEndpoint: '/api/performance/metrics',
            bufferSize: 50,
            reportInterval: 30000, // 30 seconds
            enableDetailedResourceTiming: true,
            enableUserInteractionTracking: true,
            enableAutoReporting: true
        };
        
        this.buffer = [];
        this.observers = [];
        
        this.init();
    }
    
    /**
     * Initialize performance monitoring
     */
    init() {
        if (!this.config.enabled || typeof window === 'undefined') {
            return;
        }
        
        // Wait for page load to collect initial metrics
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.collectInitialMetrics(), 1000);
            });
        } else {
            setTimeout(() => this.collectInitialMetrics(), 1000);
        }
        
        // Set up continuous monitoring
        this.setupObservers();
        this.trackUserInteractions();
        
        // Set up automatic reporting
        if (this.config.enableAutoReporting) {
            this.startAutoReporting();
        }
        
        // Track page visibility changes
        this.trackPageVisibility();
        
        console.log('🚀 Performance monitoring initialized');
    }
    
    /**
     * Collect initial page load metrics
     */
    collectInitialMetrics() {
        try {
            this.collectNavigationTiming();
            this.collectResourceTiming();
            this.collectWebVitals();
            this.collectMemoryInfo();
            this.collectConnectionInfo();
            
            // Mark as ready
            this.mark('monitoring-ready');
            
            console.log('📊 Initial performance metrics collected', this.metrics);
        } catch (error) {
            console.error('❌ Failed to collect initial metrics:', error);
        }
    }
    
    /**
     * Collect Navigation Timing API data
     */
    collectNavigationTiming() {
        if (!window.performance || !window.performance.timing) {
            return;
        }
        
        const timing = window.performance.timing;
        const navigation = window.performance.navigation;
        
        // Calculate key timing metrics
        this.metrics.navigation = {
            // DNS Resolution
            dnsLookup: timing.domainLookupEnd - timing.domainLookupStart,
            
            // Connection
            tcpConnection: timing.connectEnd - timing.connectStart,
            sslHandshake: timing.requestStart - timing.secureConnectionStart,
            
            // Request/Response
            requestTime: timing.responseStart - timing.requestStart,
            responseTime: timing.responseEnd - timing.responseStart,
            
            // DOM Processing
            domProcessing: timing.domComplete - timing.domLoading,
            domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
            
            // Complete Load
            loadComplete: timing.loadEventEnd - timing.navigationStart,
            
            // Time to First Byte
            ttfb: timing.responseStart - timing.navigationStart,
            
            // Page Load Time
            pageLoadTime: timing.loadEventEnd - timing.navigationStart,
            
            // Navigation type
            navigationType: this.getNavigationType(navigation.type),
            redirectCount: navigation.redirectCount
        };
        
        // Calculate derived metrics
        this.metrics.navigation.timeToInteractive = this.calculateTimeToInteractive();
        this.metrics.navigation.totalBlockingTime = this.calculateTotalBlockingTime();
    }
    
    /**
     * Collect Resource Timing data
     */
    collectResourceTiming() {
        if (!window.performance || !window.performance.getEntriesByType) {
            return;
        }
        
        const resources = window.performance.getEntriesByType('resource');
        
        this.metrics.resources = resources.map(resource => ({
            name: resource.name,
            type: this.getResourceType(resource),
            startTime: resource.startTime,
            duration: resource.duration,
            size: resource.transferSize || resource.encodedBodySize || 0,
            cached: resource.transferSize === 0 && resource.encodedBodySize > 0,
            protocol: resource.nextHopProtocol || 'unknown',
            
            // Timing breakdown
            dns: resource.domainLookupEnd - resource.domainLookupStart,
            tcp: resource.connectEnd - resource.connectStart,
            request: resource.responseStart - resource.requestStart,
            response: resource.responseEnd - resource.responseStart,
            
            // Performance scores
            isSlowResource: resource.duration > 1000,
            isLargeResource: (resource.transferSize || resource.encodedBodySize || 0) > 100000
        }));
        
        // Aggregate resource statistics
        this.metrics.resourceSummary = this.aggregateResourceStats();
    }
    
    /**
     * Collect Core Web Vitals
     */
    collectWebVitals() {
        // Largest Contentful Paint (LCP)
        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                this.metrics.vitals.lcp = {
                    value: lastEntry.startTime,
                    element: lastEntry.element ? lastEntry.element.tagName : 'unknown',
                    timestamp: Date.now()
                };
                
                this.reportVital('LCP', lastEntry.startTime);
            });
            
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
            this.observers.push(lcpObserver);
        }
        
        // First Input Delay (FID)
        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('first-input')) {
            const fidObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    this.metrics.vitals.fid = {
                        value: entry.processingStart - entry.startTime,
                        eventType: entry.name,
                        timestamp: Date.now()
                    };
                    
                    this.reportVital('FID', entry.processingStart - entry.startTime);
                });
            });
            
            fidObserver.observe({ entryTypes: ['first-input'], buffered: true });
            this.observers.push(fidObserver);
        }
        
        // Cumulative Layout Shift (CLS)
        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('layout-shift')) {
            let cumulativeScore = 0;
            
            const clsObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (!entry.hadRecentInput) {
                        cumulativeScore += entry.value;
                        
                        this.metrics.vitals.cls = {
                            value: cumulativeScore,
                            lastShiftTime: entry.startTime,
                            timestamp: Date.now()
                        };
                    }
                });
                
                this.reportVital('CLS', cumulativeScore);
            });
            
            clsObserver.observe({ entryTypes: ['layout-shift'], buffered: true });
            this.observers.push(clsObserver);
        }
        
        // First Contentful Paint (FCP)
        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('paint')) {
            const paintObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.name === 'first-contentful-paint') {
                        this.metrics.vitals.fcp = {
                            value: entry.startTime,
                            timestamp: Date.now()
                        };
                        
                        this.reportVital('FCP', entry.startTime);
                    }
                });
            });
            
            paintObserver.observe({ entryTypes: ['paint'], buffered: true });
            this.observers.push(paintObserver);
        }
    }
    
    /**
     * Collect memory information
     */
    collectMemoryInfo() {
        if (window.performance && window.performance.memory) {
            this.metrics.memory = {
                usedJSHeapSize: window.performance.memory.usedJSHeapSize,
                totalJSHeapSize: window.performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: window.performance.memory.jsHeapSizeLimit,
                usage: (window.performance.memory.usedJSHeapSize / window.performance.memory.jsHeapSizeLimit) * 100
            };
        }
    }
    
    /**
     * Collect connection information
     */
    collectConnectionInfo() {
        if (navigator.connection) {
            this.metrics.connection = {
                effectiveType: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt,
                saveData: navigator.connection.saveData
            };
        }
    }
    
    /**
     * Track user interactions
     */
    trackUserInteractions() {
        if (!this.config.enableUserInteractionTracking) return;
        
        const interactionTypes = ['click', 'scroll', 'keydown', 'touchstart'];
        
        interactionTypes.forEach(type => {
            document.addEventListener(type, (event) => {
                this.recordInteraction(type, event);
            }, { passive: true });
        });
        
        // Track form interactions
        document.addEventListener('submit', (event) => {
            this.recordFormSubmission(event);
        });
        
        // Track AJAX requests
        this.interceptAjaxRequests();
    }
    
    /**
     * Record user interaction
     */
    recordInteraction(type, event) {
        const interaction = {
            type,
            timestamp: Date.now(),
            target: event.target ? event.target.tagName : 'unknown',
            path: window.location.pathname
        };
        
        if (type === 'click' || type === 'touchstart') {
            interaction.coordinates = {
                x: event.clientX || 0,
                y: event.clientY || 0
            };
        }
        
        this.metrics.userInteractions.push(interaction);
        
        // Keep only recent interactions
        if (this.metrics.userInteractions.length > 100) {
            this.metrics.userInteractions = this.metrics.userInteractions.slice(-50);
        }
    }
    
    /**
     * Record form submission
     */
    recordFormSubmission(event) {
        const form = event.target;
        const interaction = {
            type: 'form_submission',
            timestamp: Date.now(),
            formId: form.id || 'anonymous',
            action: form.action || window.location.href,
            method: form.method || 'GET',
            fieldCount: form.elements.length
        };
        
        this.metrics.userInteractions.push(interaction);
    }
    
    /**
     * Intercept and monitor AJAX requests
     */
    interceptAjaxRequests() {
        const originalFetch = window.fetch;
        const originalXHROpen = XMLHttpRequest.prototype.open;
        
        // Monitor fetch requests
        window.fetch = (...args) => {
            const startTime = performance.now();
            const url = args[0];
            
            return originalFetch(...args)
                .then(response => {
                    this.recordAjaxRequest('fetch', url, performance.now() - startTime, response.status);
                    return response;
                })
                .catch(error => {
                    this.recordAjaxRequest('fetch', url, performance.now() - startTime, 'error');
                    throw error;
                });
        };
        
        // Monitor XMLHttpRequest
        XMLHttpRequest.prototype.open = function(method, url) {
            this._startTime = performance.now();
            this._url = url;
            this._method = method;
            
            this.addEventListener('loadend', () => {
                const duration = performance.now() - this._startTime;
                window.performanceMonitoring.recordAjaxRequest('xhr', url, duration, this.status);
            });
            
            return originalXHROpen.apply(this, arguments);
        };
    }
    
    /**
     * Record AJAX request performance
     */
    recordAjaxRequest(type, url, duration, status) {
        const request = {
            type,
            url: url.toString(),
            duration,
            status,
            timestamp: Date.now(),
            isSlowRequest: duration > 2000
        };
        
        this.addToBuffer('ajax_request', request);
    }
    
    /**
     * Custom performance marking
     */
    mark(name, detail = null) {
        if (window.performance && window.performance.mark) {
            performance.mark(name);
        }
        
        this.metrics.customMarks[name] = {
            timestamp: Date.now(),
            detail
        };
        
        console.log(`🏃 Performance mark: ${name}`, detail);
    }
    
    /**
     * Measure time between two marks
     */
    measure(name, startMark, endMark = null) {
        try {
            if (window.performance && window.performance.measure) {
                performance.measure(name, startMark, endMark);
                
                const entries = performance.getEntriesByName(name, 'measure');
                if (entries.length > 0) {
                    const measurement = entries[entries.length - 1];
                    
                    this.addToBuffer('custom_measurement', {
                        name,
                        duration: measurement.duration,
                        startTime: measurement.startTime,
                        timestamp: Date.now()
                    });
                    
                    console.log(`📏 Performance measure: ${name} = ${measurement.duration}ms`);
                    
                    return measurement.duration;
                }
            }
        } catch (error) {
            console.warn('Failed to measure performance:', error);
        }
        
        return 0;
    }
    
    /**
     * Track long tasks (> 50ms)
     */
    setupObservers() {
        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes.includes('longtask')) {
            const longTaskObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    this.addToBuffer('long_task', {
                        name: entry.name,
                        duration: entry.duration,
                        startTime: entry.startTime,
                        attribution: entry.attribution ? entry.attribution.map(attr => ({
                            name: attr.name,
                            containerType: attr.containerType,
                            containerId: attr.containerId,
                            containerName: attr.containerName
                        })) : []
                    });
                });
            });
            
            longTaskObserver.observe({ entryTypes: ['longtask'], buffered: true });
            this.observers.push(longTaskObserver);
        }
    }
    
    /**
     * Track page visibility changes
     */
    trackPageVisibility() {
        document.addEventListener('visibilitychange', () => {
            this.addToBuffer('visibility_change', {
                hidden: document.hidden,
                timestamp: Date.now(),
                visibilityState: document.visibilityState
            });
        });
        
        // Track page unload
        window.addEventListener('beforeunload', () => {
            this.sendBeacon();
        });
    }
    
    /**
     * Add data to reporting buffer
     */
    addToBuffer(type, data) {
        this.buffer.push({
            type,
            data,
            timestamp: Date.now(),
            url: window.location.href,
            userAgent: navigator.userAgent
        });
        
        // Send buffer if it's full
        if (this.buffer.length >= this.config.bufferSize) {
            this.sendMetrics();
        }
    }
    
    /**
     * Report Core Web Vital
     */
    reportVital(name, value) {
        const rating = this.getVitalRating(name, value);
        
        this.addToBuffer('web_vital', {
            name,
            value,
            rating,
            timestamp: Date.now()
        });
        
        console.log(`💡 ${name}: ${value}ms (${rating})`);
    }
    
    /**
     * Get vital rating (good/needs-improvement/poor)
     */
    getVitalRating(name, value) {
        const thresholds = {
            LCP: { good: 2500, poor: 4000 },
            FID: { good: 100, poor: 300 },
            CLS: { good: 0.1, poor: 0.25 },
            FCP: { good: 1800, poor: 3000 }
        };
        
        const threshold = thresholds[name];
        if (!threshold) return 'unknown';
        
        if (value <= threshold.good) return 'good';
        if (value <= threshold.poor) return 'needs-improvement';
        return 'poor';
    }
    
    /**
     * Start automatic reporting
     */
    startAutoReporting() {
        setInterval(() => {
            if (this.buffer.length > 0) {
                this.sendMetrics();
            }
        }, this.config.reportInterval);
    }
    
    /**
     * Send metrics to server
     */
    async sendMetrics() {
        if (this.buffer.length === 0) return;
        
        const payload = {
            metrics: [...this.buffer],
            page: {
                url: window.location.href,
                title: document.title,
                referrer: document.referrer
            },
            session: {
                timestamp: Date.now(),
                timeOnPage: Date.now() - (performance.timing.navigationStart || Date.now())
            },
            device: {
                userAgent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                screen: {
                    width: screen.width,
                    height: screen.height,
                    pixelRatio: window.devicePixelRatio || 1
                }
            }
        };
        
        try {
            await fetch(this.config.reportingEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            console.log(`📤 Sent ${this.buffer.length} performance metrics to server`);
            this.buffer = [];
            
        } catch (error) {
            console.error('❌ Failed to send performance metrics:', error);
            
            // Keep metrics in buffer for retry, but limit size
            if (this.buffer.length > this.config.bufferSize * 2) {
                this.buffer = this.buffer.slice(-this.config.bufferSize);
            }
        }
    }
    
    /**
     * Send beacon on page unload
     */
    sendBeacon() {
        if (this.buffer.length === 0) return;
        
        const payload = JSON.stringify({
            metrics: this.buffer,
            page: { url: window.location.href },
            session: { timestamp: Date.now() }
        });
        
        if (navigator.sendBeacon) {
            navigator.sendBeacon(this.config.reportingEndpoint, payload);
            console.log('📡 Sent unload beacon with performance data');
        }
    }
    
    /**
     * Get current performance summary
     */
    getSummary() {
        return {
            navigation: this.metrics.navigation,
            vitals: this.metrics.vitals,
            resources: {
                total: this.metrics.resources.length,
                slowResources: this.metrics.resources.filter(r => r.isSlowResource).length,
                largeResources: this.metrics.resources.filter(r => r.isLargeResource).length,
                cachedResources: this.metrics.resources.filter(r => r.cached).length
            },
            interactions: {
                total: this.metrics.userInteractions.length,
                types: this.metrics.userInteractions.reduce((acc, int) => {
                    acc[int.type] = (acc[int.type] || 0) + 1;
                    return acc;
                }, {})
            },
            memory: this.metrics.memory,
            connection: this.metrics.connection
        };
    }
    
    // Helper methods
    
    getNavigationType(type) {
        const types = ['navigate', 'reload', 'back_forward', 'prerender'];
        return types[type] || 'unknown';
    }
    
    getResourceType(resource) {
        const name = resource.name.toLowerCase();
        
        if (name.includes('.css')) return 'stylesheet';
        if (name.includes('.js')) return 'script';
        if (name.match(/\.(jpg|jpeg|png|gif|webp|svg)$/)) return 'image';
        if (name.match(/\.(woff|woff2|ttf|otf)$/)) return 'font';
        if (name.includes('/api/')) return 'api';
        
        return resource.initiatorType || 'other';
    }
    
    aggregateResourceStats() {
        const stats = {
            totalSize: 0,
            totalDuration: 0,
            byType: {},
            slowest: null,
            largest: null
        };
        
        this.metrics.resources.forEach(resource => {
            stats.totalSize += resource.size;
            stats.totalDuration += resource.duration;
            
            const type = resource.type;
            if (!stats.byType[type]) {
                stats.byType[type] = { count: 0, size: 0, duration: 0 };
            }
            
            stats.byType[type].count++;
            stats.byType[type].size += resource.size;
            stats.byType[type].duration += resource.duration;
            
            if (!stats.slowest || resource.duration > stats.slowest.duration) {
                stats.slowest = resource;
            }
            
            if (!stats.largest || resource.size > stats.largest.size) {
                stats.largest = resource;
            }
        });
        
        return stats;
    }
    
    calculateTimeToInteractive() {
        // Simplified TTI calculation
        const domContentLoaded = this.metrics.navigation.domContentLoaded;
        return domContentLoaded || 0;
    }
    
    calculateTotalBlockingTime() {
        // This would require more complex calculation based on long tasks
        // For now, return 0 as placeholder
        return 0;
    }
    
    /**
     * Clean up observers
     */
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers = [];
        console.log('🧹 Performance monitoring destroyed');
    }
}

// Initialize global performance monitoring
if (typeof window !== 'undefined') {
    window.performanceMonitoring = new PerformanceMonitoring();
    
    // Expose methods for manual tracking
    window.perfMark = (name, detail) => window.performanceMonitoring.mark(name, detail);
    window.perfMeasure = (name, startMark, endMark) => window.performanceMonitoring.measure(name, startMark, endMark);
}

export default PerformanceMonitoring;
