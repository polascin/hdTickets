/**
 * Error Reporting and Logging Utility
 * Handles browser console error reporting and monitoring
 */

class ErrorReporter {
    constructor(config = {}) {
        this.config = {
            enableConsoleCapture: true,
            enableWindowErrors: true,
            enableUnhandledRejections: true,
            enablePerformanceMonitoring: true,
            apiEndpoint: '/api/v1/dashboard/log-error',
            maxErrorsPerSession: 50,
            debugMode: config.debugMode || false,
            sessionId: this.generateSessionId(),
            ...config
        };

        this.errorQueue = [];
        this.errorCount = 0;
        this.performanceMarks = new Map();
        this.isInitialized = false;

        this.init();
    }

    /**
     * Initialize error reporting system
     */
    init() {
        if (this.isInitialized) {
            return;
        }

        console.log('🔍 Initializing HD Tickets Error Reporting System...');

        // Capture console errors
        if (this.config.enableConsoleCapture) {
            this.setupConsoleCapture();
        }

        // Capture window errors
        if (this.config.enableWindowErrors) {
            this.setupWindowErrorHandling();
        }

        // Capture unhandled promise rejections
        if (this.config.enableUnhandledRejections) {
            this.setupUnhandledRejectionHandling();
        }

        // Setup performance monitoring
        if (this.config.enablePerformanceMonitoring) {
            this.setupPerformanceMonitoring();
        }

        // Setup periodic error reporting
        this.setupPeriodicReporting();

        // Log successful initialization
        this.logEvent('info', 'Error reporting system initialized', {
            config: this.config,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        });

        this.isInitialized = true;
    }

    /**
     * Setup console capture to intercept console errors
     */
    setupConsoleCapture() {
        const originalConsole = {
            error: console.error.bind(console),
            warn: console.warn.bind(console),
            log: console.log.bind(console)
        };

        // Override console.error
        console.error = (...args) => {
            this.captureConsoleError('error', args);
            originalConsole.error(...args);
        };

        // Override console.warn
        console.warn = (...args) => {
            this.captureConsoleError('warn', args);
            originalConsole.warn(...args);
        };

        // Optionally capture info logs in debug mode
        if (this.config.debugMode) {
            console.log = (...args) => {
                this.captureConsoleError('log', args);
                originalConsole.log(...args);
            };
        }
    }

    /**
     * Setup window error handling
     */
    setupWindowErrorHandling() {
        window.addEventListener('error', (event) => {
            this.captureError({
                type: 'javascript_error',
                message: event.message,
                filename: event.filename,
                line: event.lineno,
                column: event.colno,
                error: event.error ? {
                    name: event.error.name,
                    message: event.error.message,
                    stack: event.error.stack
                } : null,
                timestamp: new Date().toISOString(),
                url: window.location.href
            });
        });
    }

    /**
     * Setup unhandled promise rejection handling
     */
    setupUnhandledRejectionHandling() {
        window.addEventListener('unhandledrejection', (event) => {
            this.captureError({
                type: 'unhandled_promise_rejection',
                message: event.reason ? event.reason.toString() : 'Unhandled Promise Rejection',
                error: event.reason instanceof Error ? {
                    name: event.reason.name,
                    message: event.reason.message,
                    stack: event.reason.stack
                } : null,
                timestamp: new Date().toISOString(),
                url: window.location.href,
                reason: event.reason
            });
        });
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page load performance
        if (typeof performance !== 'undefined' && performance.timing) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.reportPerformanceMetrics();
                }, 1000);
            });
        }

        // Monitor long tasks (if supported)
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.duration > 50) { // Tasks longer than 50ms
                            this.capturePerformanceIssue({
                                type: 'long_task',
                                duration: entry.duration,
                                startTime: entry.startTime,
                                name: entry.name,
                                timestamp: new Date().toISOString()
                            });
                        }
                    }
                });

                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                console.warn('PerformanceObserver not supported for longtask');
            }
        }
    }

    /**
     * Setup periodic error reporting
     */
    setupPeriodicReporting() {
        // Send queued errors every 30 seconds
        setInterval(() => {
            if (this.errorQueue.length > 0) {
                this.sendErrorBatch();
            }
        }, 30000);

        // Send errors before page unload
        window.addEventListener('beforeunload', () => {
            if (this.errorQueue.length > 0) {
                this.sendErrorBatch(true); // Synchronous send
            }
        });
    }

    /**
     * Capture console errors
     */
    captureConsoleError(level, args) {
        if (this.errorCount >= this.config.maxErrorsPerSession) {
            return;
        }

        const errorData = {
            type: 'console_' + level,
            message: args.map(arg => 
                typeof arg === 'object' ? JSON.stringify(arg, null, 2) : String(arg)
            ).join(' '),
            level: level,
            args: args.map(arg => this.serializeValue(arg)),
            timestamp: new Date().toISOString(),
            url: window.location.href,
            stack: new Error().stack
        };

        this.queueError(errorData);
    }

    /**
     * Capture general errors
     */
    captureError(errorData) {
        if (this.errorCount >= this.config.maxErrorsPerSession) {
            return;
        }

        this.queueError({
            ...errorData,
            session_id: this.config.sessionId,
            user_agent: navigator.userAgent,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            }
        });
    }

    /**
     * Capture performance issues
     */
    capturePerformanceIssue(performanceData) {
        this.queueError({
            type: 'performance_issue',
            ...performanceData,
            session_id: this.config.sessionId,
            url: window.location.href
        });
    }

    /**
     * Queue error for batch sending
     */
    queueError(errorData) {
        this.errorQueue.push(errorData);
        this.errorCount++;

        if (this.config.debugMode) {
            console.debug('🚨 Error queued:', errorData);
        }

        // Send immediately if queue is getting full
        if (this.errorQueue.length >= 10) {
            this.sendErrorBatch();
        }
    }

    /**
     * Send batch of errors to server
     */
    async sendErrorBatch(synchronous = false) {
        if (this.errorQueue.length === 0) {
            return;
        }

        const batch = [...this.errorQueue];
        this.errorQueue = [];

        const payload = {
            session_id: this.config.sessionId,
            timestamp: new Date().toISOString(),
            errors: batch,
            meta: {
                url: window.location.href,
                referrer: document.referrer,
                user_agent: navigator.userAgent,
                screen_resolution: `${screen.width}x${screen.height}`,
                color_depth: screen.colorDepth,
                language: navigator.language,
                platform: navigator.platform,
                online: navigator.onLine
            }
        };

        try {
            if (synchronous && 'sendBeacon' in navigator) {
                // Use sendBeacon for synchronous sending during page unload
                navigator.sendBeacon(
                    this.config.apiEndpoint,
                    JSON.stringify(payload)
                );
            } else {
                const response = await fetch(this.config.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                if (this.config.debugMode) {
                    console.debug('✅ Error batch sent successfully:', batch.length, 'errors');
                }
            }
        } catch (error) {
            console.warn('Failed to send error batch:', error);
            // Re-queue errors for retry (but limit retries)
            if (batch.length < this.config.maxErrorsPerSession / 2) {
                this.errorQueue.unshift(...batch);
            }
        }
    }

    /**
     * Log custom events
     */
    logEvent(level, message, context = {}) {
        this.captureError({
            type: 'custom_event',
            level: level,
            message: message,
            context: context,
            timestamp: new Date().toISOString(),
            url: window.location.href
        });
    }

    /**
     * Start performance timing
     */
    startTiming(name) {
        this.performanceMarks.set(name, performance.now());
    }

    /**
     * End performance timing and log if slow
     */
    endTiming(name, warningThreshold = 1000) {
        const startTime = this.performanceMarks.get(name);
        if (startTime === undefined) {
            return;
        }

        const duration = performance.now() - startTime;
        this.performanceMarks.delete(name);

        if (duration > warningThreshold) {
            this.capturePerformanceIssue({
                type: 'slow_operation',
                operation_name: name,
                duration: duration,
                threshold: warningThreshold,
                timestamp: new Date().toISOString()
            });
        }

        return duration;
    }

    /**
     * Report page performance metrics
     */
    reportPerformanceMetrics() {
        if (!performance.timing) return;

        const timing = performance.timing;
        const metrics = {
            type: 'page_performance',
            dns_lookup: timing.domainLookupEnd - timing.domainLookupStart,
            tcp_connection: timing.connectEnd - timing.connectStart,
            ssl_handshake: timing.secureConnectionStart > 0 ? timing.connectEnd - timing.secureConnectionStart : 0,
            server_response: timing.responseStart - timing.requestStart,
            dom_processing: timing.domComplete - timing.domLoading,
            page_load_total: timing.loadEventEnd - timing.navigationStart,
            first_paint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-paint')?.startTime,
            first_contentful_paint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-contentful-paint')?.startTime,
            timestamp: new Date().toISOString(),
            url: window.location.href
        };

        this.queueError(metrics);
    }

    /**
     * Serialize values for JSON transmission
     */
    serializeValue(value) {
        try {
            if (value instanceof Error) {
                return {
                    name: value.name,
                    message: value.message,
                    stack: value.stack
                };
            }
            if (typeof value === 'function') {
                return '[Function]';
            }
            if (value instanceof HTMLElement) {
                return `[HTMLElement: ${value.tagName}]`;
            }
            return JSON.parse(JSON.stringify(value));
        } catch (e) {
            return '[Unserializable Value]';
        }
    }

    /**
     * Generate unique session ID
     */
    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Get current error statistics
     */
    getStats() {
        return {
            session_id: this.config.sessionId,
            errors_captured: this.errorCount,
            errors_queued: this.errorQueue.length,
            max_errors: this.config.maxErrorsPerSession,
            initialized: this.isInitialized
        };
    }
}

// Create and export global instance
const errorReporter = new ErrorReporter({
    debugMode: import.meta.env.DEV || false
});

// Make it globally available
window.hdTicketsErrorReporter = errorReporter;

export default errorReporter;
