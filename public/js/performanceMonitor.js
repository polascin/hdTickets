/**
 * HD Tickets Performance Monitor v2.1.0
 * Comprehensive performance monitoring and analytics
 */

class HDTicketsPerformanceMonitor {
  constructor() {
    this.metrics = {
      pageLoad: {},
      navigation: {},
      resources: [],
      vitals: {},
      errors: [],
      interactions: [],
      api: []
    };
    
    this.observers = new Map();
    this.startTime = performance.now();
    this.isEnabled = this.shouldEnableMonitoring();
    this.reportingEndpoint = '/api/analytics/performance';
    this.reportingInterval = 30000; // 30 seconds
    this.reportingTimer = null;
    
    if (this.isEnabled) {
      this.init();
    }
  }

  init() {
    console.log('[PerformanceMonitor] Initializing HD Tickets Performance Monitor v2.1.0');
    
    // Wait for page load to complete
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        this.setupMonitoring();
      });
    } else {
      this.setupMonitoring();
    }
    
    // Start periodic reporting
    this.startReporting();
  }

  setupMonitoring() {
    this.measurePageLoad();
    this.measureNavigation();
    this.measureResources();
    this.measureWebVitals();
    this.setupErrorTracking();
    this.setupInteractionTracking();
    this.setupAPIMonitoring();
    this.setupMemoryMonitoring();
    this.setupNetworkMonitoring();
  }

  // Page Load Performance
  measurePageLoad() {
    if (!('performance' in window)) return;

    const loadComplete = () => {
      const navigation = performance.getEntriesByType('navigation')[0];
      if (!navigation) return;

      this.metrics.pageLoad = {
        timestamp: Date.now(),
        url: window.location.href,
        loadTime: navigation.loadEventEnd - navigation.fetchStart,
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.fetchStart,
        firstPaint: this.getFirstPaint(),
        firstContentfulPaint: this.getFirstContentfulPaint(),
        largestContentfulPaint: null, // Will be set by Web Vitals
        dns: navigation.domainLookupEnd - navigation.domainLookupStart,
        tcp: navigation.connectEnd - navigation.connectStart,
        ssl: navigation.secureConnectionStart > 0 ? 
          navigation.connectEnd - navigation.secureConnectionStart : 0,
        request: navigation.responseStart - navigation.requestStart,
        response: navigation.responseEnd - navigation.responseStart,
        dom: navigation.domComplete - navigation.domLoading,
        timeToInteractive: this.estimateTimeToInteractive(navigation)
      };

      console.log('[PerformanceMonitor] Page load metrics collected:', this.metrics.pageLoad);
    };

    if (document.readyState === 'complete') {
      loadComplete();
    } else {
      window.addEventListener('load', loadComplete);
    }
  }

  // Navigation Performance
  measureNavigation() {
    // Monitor SPA navigation if present
    const originalPushState = history.pushState;
    const originalReplaceState = history.replaceState;
    
    const trackNavigation = (type, url) => {
      const startTime = performance.now();
      
      // Wait for next tick to measure navigation
      requestAnimationFrame(() => {
        const endTime = performance.now();
        
        this.metrics.navigation = {
          timestamp: Date.now(),
          type: type,
          url: url,
          duration: endTime - startTime,
          from: document.referrer || 'direct'
        };
        
        console.log('[PerformanceMonitor] Navigation tracked:', this.metrics.navigation);
      });
    };

    history.pushState = function(state, title, url) {
      trackNavigation('pushState', url);
      return originalPushState.apply(this, arguments);
    };

    history.replaceState = function(state, title, url) {
      trackNavigation('replaceState', url);
      return originalReplaceState.apply(this, arguments);
    };

    window.addEventListener('popstate', (event) => {
      trackNavigation('popstate', window.location.href);
    });
  }

  // Resource Performance
  measureResources() {
    if (!('PerformanceObserver' in window)) return;

    const resourceObserver = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (this.shouldTrackResource(entry.name)) {
          this.metrics.resources.push({
            name: entry.name,
            type: this.getResourceType(entry),
            duration: entry.duration,
            size: entry.transferSize || 0,
            cached: entry.transferSize === 0 && entry.decodedBodySize > 0,
            timestamp: Date.now()
          });
        }
      }
    });

    try {
      resourceObserver.observe({ entryTypes: ['resource'] });
      this.observers.set('resource', resourceObserver);
    } catch (error) {
      console.warn('[PerformanceMonitor] Resource observer failed:', error);
    }
  }

  // Web Vitals (Core Web Vitals)
  measureWebVitals() {
    // Largest Contentful Paint (LCP)
    this.measureLCP();
    
    // First Input Delay (FID)
    this.measureFID();
    
    // Cumulative Layout Shift (CLS)
    this.measureCLS();
    
    // Additional vitals
    this.measureTTFB();
    this.measureINP();
  }

  measureLCP() {
    if (!('PerformanceObserver' in window)) return;

    const lcpObserver = new PerformanceObserver((list) => {
      const entries = list.getEntries();
      const lastEntry = entries[entries.length - 1];
      
      this.metrics.vitals.lcp = {
        value: lastEntry.startTime,
        element: lastEntry.element?.tagName || 'unknown',
        timestamp: Date.now()
      };
    });

    try {
      lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
      this.observers.set('lcp', lcpObserver);
    } catch (error) {
      console.warn('[PerformanceMonitor] LCP observer failed:', error);
    }
  }

  measureFID() {
    if (!('PerformanceObserver' in window)) return;

    const fidObserver = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        this.metrics.vitals.fid = {
          value: entry.processingStart - entry.startTime,
          timestamp: Date.now()
        };
        
        // FID only happens once
        fidObserver.disconnect();
      }
    });

    try {
      fidObserver.observe({ entryTypes: ['first-input'] });
      this.observers.set('fid', fidObserver);
    } catch (error) {
      console.warn('[PerformanceMonitor] FID observer failed:', error);
    }
  }

  measureCLS() {
    if (!('PerformanceObserver' in window)) return;

    let clsValue = 0;
    let clsEntries = [];

    const clsObserver = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (!entry.hadRecentInput) {
          clsValue += entry.value;
          clsEntries.push(entry);
        }
      }
      
      this.metrics.vitals.cls = {
        value: clsValue,
        entries: clsEntries.length,
        timestamp: Date.now()
      };
    });

    try {
      clsObserver.observe({ entryTypes: ['layout-shift'] });
      this.observers.set('cls', clsObserver);
    } catch (error) {
      console.warn('[PerformanceMonitor] CLS observer failed:', error);
    }
  }

  measureTTFB() {
    const navigation = performance.getEntriesByType('navigation')[0];
    if (navigation) {
      this.metrics.vitals.ttfb = {
        value: navigation.responseStart - navigation.fetchStart,
        timestamp: Date.now()
      };
    }
  }

  measureINP() {
    // Interaction to Next Paint - experimental
    if ('PerformanceEventTiming' in window) {
      const inpObserver = new PerformanceObserver((list) => {
        let maxDuration = 0;
        
        for (const entry of list.getEntries()) {
          if (entry.duration > maxDuration) {
            maxDuration = entry.duration;
          }
        }
        
        if (maxDuration > 0) {
          this.metrics.vitals.inp = {
            value: maxDuration,
            timestamp: Date.now()
          };
        }
      });

      try {
        inpObserver.observe({ entryTypes: ['event'] });
        this.observers.set('inp', inpObserver);
      } catch (error) {
        console.warn('[PerformanceMonitor] INP observer failed:', error);
      }
    }
  }

  // Error Tracking
  setupErrorTracking() {
    // JavaScript errors
    window.addEventListener('error', (event) => {
      this.trackError({
        type: 'javascript',
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        stack: event.error?.stack,
        timestamp: Date.now()
      });
    });

    // Promise rejections
    window.addEventListener('unhandledrejection', (event) => {
      this.trackError({
        type: 'promise',
        message: event.reason?.message || String(event.reason),
        stack: event.reason?.stack,
        timestamp: Date.now()
      });
    });

    // Resource loading errors
    document.addEventListener('error', (event) => {
      if (event.target !== window) {
        this.trackError({
          type: 'resource',
          message: `Failed to load ${event.target.tagName}: ${event.target.src || event.target.href}`,
          element: event.target.tagName,
          url: event.target.src || event.target.href,
          timestamp: Date.now()
        });
      }
    }, true);
  }

  trackError(error) {
    this.metrics.errors.push(error);
    console.error('[PerformanceMonitor] Error tracked:', error);
    
    // Immediately report critical errors
    if (this.metrics.errors.length >= 5) {
      this.reportMetrics();
    }
  }

  // User Interaction Tracking
  setupInteractionTracking() {
    const interactionTypes = ['click', 'keydown', 'scroll', 'touchstart'];
    
    interactionTypes.forEach(type => {
      document.addEventListener(type, (event) => {
        this.trackInteraction({
          type: type,
          target: event.target.tagName.toLowerCase(),
          className: event.target.className,
          id: event.target.id,
          timestamp: Date.now()
        });
      }, { passive: true });
    });
  }

  trackInteraction(interaction) {
    // Limit interaction tracking to prevent memory issues
    if (this.metrics.interactions.length >= 100) {
      this.metrics.interactions.shift();
    }
    
    this.metrics.interactions.push(interaction);
  }

  // API Performance Monitoring
  setupAPIMonitoring() {
    const originalFetch = window.fetch;
    
    window.fetch = async (...args) => {
      const startTime = performance.now();
      const url = typeof args[0] === 'string' ? args[0] : args[0].url;
      
      try {
        const response = await originalFetch.apply(this, args);
        const endTime = performance.now();
        
        this.trackAPICall({
          url: url,
          method: args[1]?.method || 'GET',
          status: response.status,
          duration: endTime - startTime,
          success: response.ok,
          timestamp: Date.now()
        });
        
        return response;
      } catch (error) {
        const endTime = performance.now();
        
        this.trackAPICall({
          url: url,
          method: args[1]?.method || 'GET',
          status: 0,
          duration: endTime - startTime,
          success: false,
          error: error.message,
          timestamp: Date.now()
        });
        
        throw error;
      }
    };
  }

  trackAPICall(apiCall) {
    // Limit API call tracking
    if (this.metrics.api.length >= 50) {
      this.metrics.api.shift();
    }
    
    this.metrics.api.push(apiCall);
  }

  // Memory Monitoring
  setupMemoryMonitoring() {
    if ('memory' in performance) {
      setInterval(() => {
        const memory = performance.memory;
        this.metrics.memory = {
          used: memory.usedJSHeapSize,
          total: memory.totalJSHeapSize,
          limit: memory.jsHeapSizeLimit,
          timestamp: Date.now()
        };
      }, 10000); // Every 10 seconds
    }
  }

  // Network Monitoring
  setupNetworkMonitoring() {
    if ('connection' in navigator) {
      const connection = navigator.connection;
      
      this.metrics.network = {
        effectiveType: connection.effectiveType,
        downlink: connection.downlink,
        rtt: connection.rtt,
        saveData: connection.saveData,
        timestamp: Date.now()
      };
      
      // Monitor connection changes
      connection.addEventListener('change', () => {
        this.metrics.network = {
          effectiveType: connection.effectiveType,
          downlink: connection.downlink,
          rtt: connection.rtt,
          saveData: connection.saveData,
          timestamp: Date.now()
        };
      });
    }
  }

  // Reporting
  startReporting() {
    if (!this.reportingEndpoint) return;
    
    this.reportingTimer = setInterval(() => {
      this.reportMetrics();
    }, this.reportingInterval);
    
    // Report on page unload
    window.addEventListener('beforeunload', () => {
      this.reportMetrics(true);
    });
    
    // Report on visibility change
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.reportMetrics();
      }
    });
  }

  async reportMetrics(isUnload = false) {
    if (!this.hasDataToReport()) return;
    
    const payload = {
      session: this.getSessionId(),
      url: window.location.href,
      userAgent: navigator.userAgent,
      timestamp: Date.now(),
      metrics: this.metrics
    };
    
    try {
      if (isUnload && 'sendBeacon' in navigator) {
        // Use sendBeacon for unload events
        const success = navigator.sendBeacon(
          this.reportingEndpoint,
          JSON.stringify(payload)
        );
        console.log('[PerformanceMonitor] Beacon sent:', success);
      } else {
        // Use fetch for regular reporting
        const response = await fetch(this.reportingEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(payload)
        });
        
        if (response.ok) {
          console.log('[PerformanceMonitor] Metrics reported successfully');
          this.clearReportedMetrics();
        }
      }
    } catch (error) {
      console.error('[PerformanceMonitor] Failed to report metrics:', error);
    }
  }

  clearReportedMetrics() {
    // Clear metrics that have been reported
    this.metrics.errors = [];
    this.metrics.interactions = [];
    this.metrics.api = [];
    this.metrics.resources = [];
  }

  // Utility Methods
  shouldEnableMonitoring() {
    // Enable monitoring based on environment and user preferences
    const isProduction = !window.location.hostname.includes('localhost');
    const hasOptOut = localStorage.getItem('hd-disable-monitoring') === 'true';
    const isSlowDevice = this.isSlowDevice();
    
    return isProduction && !hasOptOut && !isSlowDevice;
  }

  isSlowDevice() {
    // Basic device capability detection
    const cores = navigator.hardwareConcurrency || 1;
    const memory = navigator.deviceMemory || 1;
    const connection = navigator.connection?.effectiveType;
    
    return cores < 4 || memory < 4 || connection === 'slow-2g' || connection === '2g';
  }

  hasDataToReport() {
    return (
      this.metrics.errors.length > 0 ||
      this.metrics.interactions.length > 0 ||
      this.metrics.api.length > 0 ||
      this.metrics.resources.length > 0 ||
      Object.keys(this.metrics.vitals).length > 0
    );
  }

  getSessionId() {
    let sessionId = sessionStorage.getItem('hd-session-id');
    if (!sessionId) {
      sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      sessionStorage.setItem('hd-session-id', sessionId);
    }
    return sessionId;
  }

  shouldTrackResource(url) {
    // Filter out unnecessary resources
    const excludePatterns = [
      'google-analytics',
      'googletagmanager',
      'facebook.net',
      'doubleclick.net',
      'chrome-extension:',
      'moz-extension:'
    ];
    
    return !excludePatterns.some(pattern => url.includes(pattern));
  }

  getResourceType(entry) {
    if (entry.initiatorType) return entry.initiatorType;
    
    const url = entry.name;
    if (url.match(/\.(js)$/i)) return 'script';
    if (url.match(/\.(css)$/i)) return 'stylesheet';
    if (url.match(/\.(png|jpg|jpeg|gif|svg|webp)$/i)) return 'image';
    if (url.match(/\.(woff|woff2|ttf|eot)$/i)) return 'font';
    
    return 'other';
  }

  getFirstPaint() {
    const paintEntries = performance.getEntriesByType('paint');
    const fp = paintEntries.find(entry => entry.name === 'first-paint');
    return fp ? fp.startTime : null;
  }

  getFirstContentfulPaint() {
    const paintEntries = performance.getEntriesByType('paint');
    const fcp = paintEntries.find(entry => entry.name === 'first-contentful-paint');
    return fcp ? fcp.startTime : null;
  }

  estimateTimeToInteractive(navigation) {
    // Simple TTI estimation
    const domContentLoaded = navigation.domContentLoadedEventEnd - navigation.fetchStart;
    const loadComplete = navigation.loadEventEnd - navigation.fetchStart;
    
    // TTI is typically between DOMContentLoaded and load complete
    return domContentLoaded + ((loadComplete - domContentLoaded) * 0.7);
  }

  // Public API
  getMetrics() {
    return { ...this.metrics };
  }

  getWebVitals() {
    return { ...this.metrics.vitals };
  }

  forceReport() {
    this.reportMetrics();
  }

  disable() {
    console.log('[PerformanceMonitor] Disabling performance monitoring');
    
    // Stop reporting
    if (this.reportingTimer) {
      clearInterval(this.reportingTimer);
    }
    
    // Disconnect observers
    this.observers.forEach(observer => {
      observer.disconnect();
    });
    
    // Clear data
    this.observers.clear();
    this.metrics = {};
    this.isEnabled = false;
    
    // Save preference
    localStorage.setItem('hd-disable-monitoring', 'true');
  }

  enable() {
    localStorage.removeItem('hd-disable-monitoring');
    window.location.reload(); // Restart monitoring
  }
}

// Export and initialize
window.HDTicketsPerformanceMonitor = HDTicketsPerformanceMonitor;

// Auto-initialize
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.hdPerformanceMonitor = new HDTicketsPerformanceMonitor();
  });
} else {
  window.hdPerformanceMonitor = new HDTicketsPerformanceMonitor();
}

console.log('[PerformanceMonitor] HD Tickets Performance Monitor v2.1.0 loaded');
