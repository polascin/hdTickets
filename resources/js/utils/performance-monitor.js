/**
 * Performance Monitoring Dashboard
 * Tracks user activity, page performance, and app metrics for HD Tickets profile features
 */

class PerformanceMonitor {
    constructor(options = {}) {
        this.options = {
            enableMetrics: true,
            enableUserTracking: true,
            enablePerformanceAPI: true,
            enableMemoryTracking: true,
            reportingInterval: 30000, // 30 seconds
            maxStoredMetrics: 1000,
            debugMode: false,
            apiEndpoint: '/api/performance-metrics',
            ...options
        };

        this.metrics = {
            pageViews: [],
            userActions: [],
            performance: [],
            errors: [],
            memory: [],
            network: []
        };

        this.observers = new Map();
        this.timers = new Map();
        this.startTime = Date.now();
        this.sessionId = this.generateSessionId();
        
        this.init();
    }

    init() {
        if (!this.options.enableMetrics) return;

        this.setupPerformanceObserver();
        this.setupUserInteractionTracking();
        this.setupNetworkMonitoring();
        this.setupMemoryTracking();
        this.setupErrorTracking();
        this.setupVisibilityTracking();
        this.startReporting();

        if (this.options.debugMode) {
            console.log('PerformanceMonitor initialized', this.options);
            this.setupDebugConsole();
        }
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Performance API Integration
    setupPerformanceObserver() {
        if (!this.options.enablePerformanceAPI || !window.PerformanceObserver) return;

        // Navigation timing
        if (performance.getEntriesByType) {
            const navigationEntries = performance.getEntriesByType('navigation');
            if (navigationEntries.length > 0) {
                this.recordNavigationTiming(navigationEntries[0]);
            }
        }

        // Resource timing
        const resourceObserver = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                this.recordResourceTiming(entry);
            }
        });
        resourceObserver.observe({ entryTypes: ['resource'] });
        this.observers.set('resource', resourceObserver);

        // Paint timing
        const paintObserver = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                this.recordPaintTiming(entry);
            }
        });
        paintObserver.observe({ entryTypes: ['paint'] });
        this.observers.set('paint', paintObserver);

        // Long tasks
        if ('PerformanceObserver' in window) {
            const longTaskObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    this.recordLongTask(entry);
                }
            });
            try {
                longTaskObserver.observe({ entryTypes: ['longtask'] });
                this.observers.set('longtask', longTaskObserver);
            } catch (e) {
                // Longtask not supported
            }
        }

        // Layout shifts (CLS)
        const layoutShiftObserver = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                this.recordLayoutShift(entry);
            }
        });
        try {
            layoutShiftObserver.observe({ entryTypes: ['layout-shift'] });
            this.observers.set('layout-shift', layoutShiftObserver);
        } catch (e) {
            // Layout shift not supported
        }
    }

    // User Interaction Tracking
    setupUserInteractionTracking() {
        if (!this.options.enableUserTracking) return;

        // Track clicks
        document.addEventListener('click', (e) => {
            this.recordUserAction('click', {
                element: this.getElementInfo(e.target),
                timestamp: Date.now(),
                coordinates: { x: e.clientX, y: e.clientY }
            });
        });

        // Track form submissions
        document.addEventListener('submit', (e) => {
            this.recordUserAction('form_submit', {
                form: this.getElementInfo(e.target),
                timestamp: Date.now()
            });
        });

        // Track scroll behavior
        let scrollTimer;
        document.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                this.recordUserAction('scroll', {
                    scrollY: window.scrollY,
                    timestamp: Date.now(),
                    maxScroll: document.documentElement.scrollHeight - window.innerHeight
                });
            }, 100);
        });

        // Track focus events
        document.addEventListener('focusin', (e) => {
            this.recordUserAction('focus', {
                element: this.getElementInfo(e.target),
                timestamp: Date.now()
            });
        });

        // Track key interactions
        document.addEventListener('keydown', (e) => {
            // Only track important keys, not every keystroke
            if (['Enter', 'Escape', 'Tab'].includes(e.key)) {
                this.recordUserAction('keydown', {
                    key: e.key,
                    element: this.getElementInfo(e.target),
                    timestamp: Date.now()
                });
            }
        });
    }

    // Network Monitoring
    setupNetworkMonitoring() {
        // Monitor fetch requests
        if (window.fetch) {
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                const startTime = performance.now();
                const url = args[0];
                
                try {
                    const response = await originalFetch(...args);
                    const endTime = performance.now();
                    
                    this.recordNetworkRequest({
                        url,
                        method: args[1]?.method || 'GET',
                        duration: endTime - startTime,
                        status: response.status,
                        size: response.headers.get('content-length'),
                        timestamp: Date.now(),
                        success: response.ok
                    });
                    
                    return response;
                } catch (error) {
                    const endTime = performance.now();
                    
                    this.recordNetworkRequest({
                        url,
                        method: args[1]?.method || 'GET',
                        duration: endTime - startTime,
                        error: error.message,
                        timestamp: Date.now(),
                        success: false
                    });
                    
                    throw error;
                }
            };
        }

        // Monitor XMLHttpRequest
        if (window.XMLHttpRequest) {
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;

            XMLHttpRequest.prototype.open = function(method, url) {
                this._performanceMonitor = {
                    method,
                    url,
                    startTime: null
                };
                return originalOpen.apply(this, arguments);
            };

            XMLHttpRequest.prototype.send = function() {
                if (this._performanceMonitor) {
                    this._performanceMonitor.startTime = performance.now();
                    
                    this.addEventListener('loadend', () => {
                        const endTime = performance.now();
                        const monitor = window.performanceMonitor || this.performanceMonitor;
                        
                        if (monitor) {
                            monitor.recordNetworkRequest({
                                url: this._performanceMonitor.url,
                                method: this._performanceMonitor.method,
                                duration: endTime - this._performanceMonitor.startTime,
                                status: this.status,
                                timestamp: Date.now(),
                                success: this.status >= 200 && this.status < 300
                            });
                        }
                    });
                }
                return originalSend.apply(this, arguments);
            };
        }
    }

    // Memory Tracking
    setupMemoryTracking() {
        if (!this.options.enableMemoryTracking || !performance.memory) return;

        const trackMemory = () => {
            this.recordMemoryUsage({
                used: performance.memory.usedJSHeapSize,
                total: performance.memory.totalJSHeapSize,
                limit: performance.memory.jsHeapSizeLimit,
                timestamp: Date.now()
            });
        };

        // Track memory every 10 seconds
        this.timers.set('memory', setInterval(trackMemory, 10000));
        trackMemory(); // Initial reading
    }

    // Error Tracking
    setupErrorTracking() {
        // JavaScript errors
        window.addEventListener('error', (e) => {
            this.recordError({
                type: 'javascript',
                message: e.message,
                filename: e.filename,
                line: e.lineno,
                column: e.colno,
                stack: e.error?.stack,
                timestamp: Date.now()
            });
        });

        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', (e) => {
            this.recordError({
                type: 'promise_rejection',
                message: e.reason?.message || String(e.reason),
                stack: e.reason?.stack,
                timestamp: Date.now()
            });
        });

        // Resource loading errors
        document.addEventListener('error', (e) => {
            if (e.target !== window) {
                this.recordError({
                    type: 'resource',
                    message: `Failed to load: ${e.target.src || e.target.href}`,
                    element: this.getElementInfo(e.target),
                    timestamp: Date.now()
                });
            }
        }, true);
    }

    // Visibility Tracking
    setupVisibilityTracking() {
        let visibilityStart = Date.now();
        
        document.addEventListener('visibilitychange', () => {
            const now = Date.now();
            
            if (document.hidden) {
                // Page became hidden
                this.recordUserAction('page_hidden', {
                    visibleDuration: now - visibilityStart,
                    timestamp: now
                });
            } else {
                // Page became visible
                visibilityStart = now;
                this.recordUserAction('page_visible', {
                    timestamp: now
                });
            }
        });

        // Track initial visibility
        if (!document.hidden) {
            this.recordUserAction('page_visible', {
                timestamp: visibilityStart
            });
        }
    }

    // Recording Methods
    recordNavigationTiming(entry) {
        this.addMetric('performance', {
            type: 'navigation',
            domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
            loadComplete: entry.loadEventEnd - entry.loadEventStart,
            dnsLookup: entry.domainLookupEnd - entry.domainLookupStart,
            tcpConnect: entry.connectEnd - entry.connectStart,
            request: entry.responseStart - entry.requestStart,
            response: entry.responseEnd - entry.responseStart,
            domProcessing: entry.domComplete - entry.domLoading,
            timestamp: Date.now()
        });
    }

    recordResourceTiming(entry) {
        this.addMetric('performance', {
            type: 'resource',
            name: entry.name,
            duration: entry.duration,
            size: entry.transferSize,
            cached: entry.transferSize === 0 && entry.decodedBodySize > 0,
            timestamp: Date.now()
        });
    }

    recordPaintTiming(entry) {
        this.addMetric('performance', {
            type: 'paint',
            name: entry.name,
            startTime: entry.startTime,
            timestamp: Date.now()
        });
    }

    recordLongTask(entry) {
        this.addMetric('performance', {
            type: 'longtask',
            duration: entry.duration,
            startTime: entry.startTime,
            timestamp: Date.now()
        });
    }

    recordLayoutShift(entry) {
        this.addMetric('performance', {
            type: 'layout_shift',
            value: entry.value,
            hadRecentInput: entry.hadRecentInput,
            timestamp: Date.now()
        });
    }

    recordUserAction(action, data) {
        this.addMetric('userActions', {
            action,
            ...data,
            sessionId: this.sessionId
        });
    }

    recordNetworkRequest(data) {
        this.addMetric('network', {
            ...data,
            sessionId: this.sessionId
        });
    }

    recordMemoryUsage(data) {
        this.addMetric('memory', {
            ...data,
            sessionId: this.sessionId
        });
    }

    recordError(data) {
        this.addMetric('errors', {
            ...data,
            sessionId: this.sessionId,
            userAgent: navigator.userAgent,
            url: window.location.href
        });
    }

    // Utility Methods
    addMetric(category, data) {
        if (!this.metrics[category]) {
            this.metrics[category] = [];
        }

        this.metrics[category].push(data);

        // Limit stored metrics to prevent memory issues
        if (this.metrics[category].length > this.options.maxStoredMetrics) {
            this.metrics[category] = this.metrics[category].slice(-this.options.maxStoredMetrics / 2);
        }

        if (this.options.debugMode) {
            console.log(`Performance metric [${category}]:`, data);
        }
    }

    getElementInfo(element) {
        if (!element) return null;
        
        return {
            tagName: element.tagName?.toLowerCase(),
            id: element.id,
            className: element.className,
            text: element.textContent?.slice(0, 100),
            href: element.href,
            src: element.src
        };
    }

    // Reporting
    startReporting() {
        const report = () => {
            if (this.hasMetricsToReport()) {
                this.sendMetrics();
            }
        };

        this.timers.set('reporting', setInterval(report, this.options.reportingInterval));
        
        // Send metrics before page unload
        window.addEventListener('beforeunload', () => {
            this.sendMetrics(true);
        });
    }

    hasMetricsToReport() {
        return Object.values(this.metrics).some(category => category.length > 0);
    }

    async sendMetrics(isBeforeUnload = false) {
        const metricsToSend = { ...this.metrics };
        
        // Clear metrics after copying
        Object.keys(this.metrics).forEach(key => {
            this.metrics[key] = [];
        });

        const payload = {
            sessionId: this.sessionId,
            url: window.location.href,
            timestamp: Date.now(),
            userAgent: navigator.userAgent,
            metrics: metricsToSend
        };

        try {
            if (isBeforeUnload && navigator.sendBeacon) {
                // Use sendBeacon for more reliable reporting on page unload
                navigator.sendBeacon(
                    this.options.apiEndpoint,
                    JSON.stringify(payload)
                );
            } else {
                await fetch(this.options.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(payload)
                });
            }

            if (this.options.debugMode) {
                console.log('Performance metrics sent:', payload);
            }
        } catch (error) {
            console.error('Failed to send performance metrics:', error);
            
            // Restore metrics if sending failed (unless it's before unload)
            if (!isBeforeUnload) {
                Object.keys(metricsToSend).forEach(key => {
                    this.metrics[key] = [...metricsToSend[key], ...this.metrics[key]];
                });
            }
        }
    }

    // Public API Methods
    getMetrics() {
        return { ...this.metrics };
    }

    getPerformanceScore() {
        const navigation = this.metrics.performance.find(p => p.type === 'navigation');
        const longTasks = this.metrics.performance.filter(p => p.type === 'longtask');
        const layoutShifts = this.metrics.performance.filter(p => p.type === 'layout_shift');
        const errors = this.metrics.errors;

        let score = 100;

        // Deduct for slow loading
        if (navigation && navigation.loadComplete > 3000) {
            score -= 20;
        }

        // Deduct for long tasks
        score -= Math.min(longTasks.length * 5, 30);

        // Deduct for layout shifts
        const totalShift = layoutShifts.reduce((sum, shift) => sum + shift.value, 0);
        if (totalShift > 0.25) {
            score -= 25;
        } else if (totalShift > 0.1) {
            score -= 10;
        }

        // Deduct for errors
        score -= Math.min(errors.length * 10, 40);

        return Math.max(score, 0);
    }

    getUserEngagement() {
        const actions = this.metrics.userActions;
        const sessionDuration = Date.now() - this.startTime;
        
        return {
            totalActions: actions.length,
            sessionDuration,
            actionsPerMinute: actions.length / (sessionDuration / 60000),
            uniqueElements: new Set(actions.map(a => a.element?.id || a.element?.className)).size,
            scrollDepth: Math.max(...actions.filter(a => a.action === 'scroll').map(a => a.scrollY / a.maxScroll * 100), 0)
        };
    }

    // Debug Console
    setupDebugConsole() {
        window.performanceMonitorDebug = {
            getMetrics: () => this.getMetrics(),
            getScore: () => this.getPerformanceScore(),
            getEngagement: () => this.getUserEngagement(),
            sendMetrics: () => this.sendMetrics(),
            clearMetrics: () => {
                Object.keys(this.metrics).forEach(key => {
                    this.metrics[key] = [];
                });
            }
        };

        console.log('Performance Monitor Debug Console available at window.performanceMonitorDebug');
    }

    // Cleanup
    destroy() {
        // Clear all timers
        this.timers.forEach(timer => clearInterval(timer));
        this.timers.clear();

        // Disconnect all observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();

        // Send final metrics
        this.sendMetrics(true);
    }
}

// Export for use in modules
export default PerformanceMonitor;

// Global instance
window.PerformanceMonitor = PerformanceMonitor;

// Auto-initialize with default options
document.addEventListener('DOMContentLoaded', () => {
    if (!window.performanceMonitor) {
        window.performanceMonitor = new PerformanceMonitor({
            debugMode: document.body.hasAttribute('data-debug-performance')
        });
    }
});

// CSS for performance indicators
const performanceStyles = `
    .performance-indicator {
        position: fixed;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        z-index: 10000;
        display: none;
    }

    .performance-indicator.show {
        display: block;
    }

    .performance-score-excellent {
        background: #10b981;
    }

    .performance-score-good {
        background: #f59e0b;
    }

    .performance-score-poor {
        background: #ef4444;
    }
`;

// Inject performance indicator styles
if (!document.querySelector('#performance-monitor-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'performance-monitor-styles';
    styleElement.textContent = performanceStyles;
    document.head.appendChild(styleElement);
}
