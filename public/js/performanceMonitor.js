/**
 * HD Tickets - Performance Monitor
 * Monitors application performance and provides insights
 */

class PerformanceMonitor {
    constructor() {
        this.metrics = {
            pageLoad: null,
            apiCalls: new Map(),
            renderTimes: new Map(),
            memoryUsage: new Map(),
            userInteractions: []
        };
        
        this.thresholds = {
            slowPageLoad: 3000, // 3 seconds
            slowApiCall: 1000,  // 1 second
            slowRender: 100     // 100ms
        };
        
        this.init();
    }

    init() {
        // Monitor page load performance
        this.monitorPageLoad();
        
        // Monitor API calls
        this.monitorFetchRequests();
        
        // Monitor DOM mutations for render performance
        this.monitorDOMChanges();
        
        // Monitor user interactions
        this.monitorUserInteractions();
        
        // Monitor memory usage
        this.monitorMemoryUsage();
        
        // Set up periodic reporting
        this.startPeriodicReporting();
        
        console.log('✅ PerformanceMonitor initialized');
    }

    monitorPageLoad() {
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const navigation = performance.getEntriesByType('navigation')[0];
                    const paint = performance.getEntriesByType('paint');
                    
                    this.metrics.pageLoad = {
                        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                        loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                        totalTime: navigation.loadEventEnd - navigation.fetchStart,
                        firstPaint: paint.find(p => p.name === 'first-paint')?.startTime || 0,
                        firstContentfulPaint: paint.find(p => p.name === 'first-contentful-paint')?.startTime || 0,
                        timestamp: Date.now()
                    };
                    
                    // Report slow page loads
                    if (this.metrics.pageLoad.totalTime > this.thresholds.slowPageLoad) {
                        this.reportSlowPerformance('pageLoad', this.metrics.pageLoad);
                    }
                    
                    console.log('📊 Page load metrics:', this.metrics.pageLoad);
                }, 0);
            });
        }
    }

    monitorFetchRequests() {
        const originalFetch = window.fetch;
        
        window.fetch = async (...args) => {
            const startTime = performance.now();
            const url = args[0];
            const callId = `${url}_${Date.now()}`;
            
            try {
                const response = await originalFetch(...args);
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                this.metrics.apiCalls.set(callId, {
                    url,
                    duration,
                    status: response.status,
                    success: response.ok,
                    timestamp: Date.now()
                });
                
                // Report slow API calls
                if (duration > this.thresholds.slowApiCall) {
                    this.reportSlowPerformance('apiCall', {
                        url,
                        duration,
                        status: response.status
                    });
                }
                
                return response;
            } catch (error) {
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                this.metrics.apiCalls.set(callId, {
                    url,
                    duration,
                    success: false,
                    error: error.message,
                    timestamp: Date.now()
                });
                
                throw error;
            }
        };
    }

    monitorDOMChanges() {
        if ('MutationObserver' in window) {
            const observer = new MutationObserver((mutations) => {
                const startTime = performance.now();
                
                // Count significant mutations
                const significantMutations = mutations.filter(mutation => 
                    mutation.type === 'childList' && 
                    mutation.addedNodes.length > 0
                ).length;
                
                if (significantMutations > 0) {
                    // Measure time until next frame
                    requestAnimationFrame(() => {
                        const endTime = performance.now();
                        const renderTime = endTime - startTime;
                        
                        this.metrics.renderTimes.set(Date.now(), {
                            mutations: significantMutations,
                            renderTime,
                            timestamp: Date.now()
                        });
                        
                        // Report slow renders
                        if (renderTime > this.thresholds.slowRender) {
                            this.reportSlowPerformance('render', {
                                mutations: significantMutations,
                                renderTime
                            });
                        }
                    });
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: false
            });
        }
    }

    monitorUserInteractions() {
        const interactionTypes = ['click', 'keydown', 'scroll', 'touch'];
        
        interactionTypes.forEach(type => {
            document.addEventListener(type, (event) => {
                this.metrics.userInteractions.push({
                    type,
                    target: event.target.tagName || 'unknown',
                    timestamp: Date.now()
                });
                
                // Keep only last 100 interactions
                if (this.metrics.userInteractions.length > 100) {
                    this.metrics.userInteractions = this.metrics.userInteractions.slice(-100);
                }
            }, { passive: true });
        });
    }

    monitorMemoryUsage() {
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                this.metrics.memoryUsage.set(Date.now(), {
                    used: memory.usedJSHeapSize,
                    total: memory.totalJSHeapSize,
                    limit: memory.jsHeapSizeLimit,
                    timestamp: Date.now()
                });
                
                // Keep only last 20 measurements
                if (this.metrics.memoryUsage.size > 20) {
                    const keys = Array.from(this.metrics.memoryUsage.keys());
                    this.metrics.memoryUsage.delete(keys[0]);
                }
            }, 30000); // Every 30 seconds
        }
    }

    startPeriodicReporting() {
        // Report metrics every 5 minutes
        setInterval(() => {
            this.reportMetrics();
        }, 300000);
        
        // Report on page unload
        window.addEventListener('beforeunload', () => {
            this.reportMetrics();
        });
    }

    reportSlowPerformance(type, data) {
        console.warn(`🐌 Slow ${type} detected:`, data);
        
        // Send to analytics if available
        if (window.gtag) {
            gtag('event', 'slow_performance', {
                event_category: 'Performance',
                event_label: type,
                value: Math.round(data.duration || data.renderTime || data.totalTime),
                custom_map: {
                    performance_type: type
                }
            });
        }
        
        // Show notification for severe performance issues
        if (type === 'pageLoad' && data.totalTime > this.thresholds.slowPageLoad * 2) {
            if (window.notificationManager) {
                window.notificationManager.warning(
                    'Slow Loading',
                    'The page is loading slowly. Please check your connection.',
                    { duration: 8000 }
                );
            }
        }
    }

    reportMetrics() {
        const summary = this.getMetricsSummary();
        console.log('📊 Performance Summary:', summary);
        
        // Send to server if endpoint available
        if (summary.apiCalls.count > 0) {
            fetch('/api/performance-metrics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(summary)
            }).catch(err => {
                console.debug('Performance metrics reporting failed:', err);
            });
        }
    }

    getMetricsSummary() {
        const apiCalls = Array.from(this.metrics.apiCalls.values());
        const renderTimes = Array.from(this.metrics.renderTimes.values());
        const memoryUsage = Array.from(this.metrics.memoryUsage.values());
        
        return {
            pageLoad: this.metrics.pageLoad,
            apiCalls: {
                count: apiCalls.length,
                averageDuration: apiCalls.length > 0 ? 
                    apiCalls.reduce((sum, call) => sum + call.duration, 0) / apiCalls.length : 0,
                successRate: apiCalls.length > 0 ? 
                    apiCalls.filter(call => call.success).length / apiCalls.length : 0,
                slowCalls: apiCalls.filter(call => call.duration > this.thresholds.slowApiCall).length
            },
            rendering: {
                count: renderTimes.length,
                averageTime: renderTimes.length > 0 ?
                    renderTimes.reduce((sum, render) => sum + render.renderTime, 0) / renderTimes.length : 0,
                slowRenders: renderTimes.filter(render => render.renderTime > this.thresholds.slowRender).length
            },
            userInteractions: {
                count: this.metrics.userInteractions.length,
                types: this.getInteractionTypeCounts()
            },
            memory: {
                current: memoryUsage[memoryUsage.length - 1] || null,
                peak: memoryUsage.length > 0 ? 
                    Math.max(...memoryUsage.map(m => m.used)) : 0,
                average: memoryUsage.length > 0 ?
                    memoryUsage.reduce((sum, m) => sum + m.used, 0) / memoryUsage.length : 0
            },
            timestamp: Date.now()
        };
    }

    getInteractionTypeCounts() {
        const counts = {};
        this.metrics.userInteractions.forEach(interaction => {
            counts[interaction.type] = (counts[interaction.type] || 0) + 1;
        });
        return counts;
    }

    // Public API for manual performance measurement
    startMeasurement(name) {
        if ('performance' in window && performance.mark) {
            performance.mark(`${name}-start`);
        }
    }

    endMeasurement(name) {
        if ('performance' in window && performance.mark && performance.measure) {
            performance.mark(`${name}-end`);
            performance.measure(name, `${name}-start`, `${name}-end`);
            
            const measure = performance.getEntriesByName(name)[0];
            if (measure) {
                console.log(`⏱️ ${name}: ${measure.duration.toFixed(2)}ms`);
                return measure.duration;
            }
        }
        return null;
    }

    // Get current performance state
    getCurrentState() {
        return {
            isPageSlow: this.metrics.pageLoad && 
                       this.metrics.pageLoad.totalTime > this.thresholds.slowPageLoad,
            hasSlowApiCalls: Array.from(this.metrics.apiCalls.values())
                                  .some(call => call.duration > this.thresholds.slowApiCall),
            hasSlowRenders: Array.from(this.metrics.renderTimes.values())
                                 .some(render => render.renderTime > this.thresholds.slowRender),
            summary: this.getMetricsSummary()
        };
    }
}

// Initialize performance monitor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.performanceMonitor = new PerformanceMonitor();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceMonitor;
}
