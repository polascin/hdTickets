/**
 * Enhanced Customer Dashboard JavaScript
 * Provides real-time data updates, interactive features, and modern UX
 */

// Global dashboard configuration
const DASHBOARD_CONFIG = {
    refreshInterval: 60000, // 1 minute default
    maxRetries: 3,
    retryDelay: 5000,
    apiTimeout: 10000,
    debounceDelay: 300,
    animationDuration: 300,
    endpoints: {
        realtime: '/api/v1/dashboard/realtime',
        analytics: '/api/v1/dashboard/analytics-data',
        recommendations: '/api/v1/dashboard/recommendations',
        notifications: '/api/v1/dashboard/notifications',
        settings: '/api/v1/dashboard/settings',
        events: '/api/v1/dashboard/events',
        ticketsFilter: '/api/v1/tickets/filter'
    }
};

// Enhanced Customer Dashboard Alpine.js Component
function enhancedCustomerDashboard() {
    return {
        // State Management
        isLoading: false,
        isLiveData: false,
        autoRefresh: true,
        refreshInterval: DASHBOARD_CONFIG.refreshInterval,
        lastUpdate: new Date(),
        retryCount: 0,
        
        // Data Properties
        dashboardData: {
            statistics: {},
            recentTickets: [],
            personalizedRecommendations: [],
            upcomingEvents: [],
            notifications: [],
            trends: {}
        },

        // Filter State
        filters: {
            sports: [],
            platforms: [],
            price_min: null,
            price_max: null,
            date_from: null,
            date_to: null,
            sort: 'newest'
        },
        filterCount: 0,
        isFiltering: false,
        
        // UI State
        showNotifications: false,
        showSettings: false,
        showCreateAlert: false,
        notificationCount: 0,
        hasNotifications: false,
        
        // Alerts
        userAlerts: [],
        newAlert: { name: '', sport: '', max_price: null, platform: '' },
        isCreatingAlert: false,
        
        // User Preferences
        settings: {
            autoRefresh: true,
            refreshInterval: 5,
            notifications: true,
            emailAlerts: true,
            theme: 'auto'
        },
        
        // Cache Management
        cache: new Map(),
        cacheExpiry: new Map(),
        
        // Performance Monitoring
        performanceMetrics: {
            pageLoadTime: 0,
            apiResponseTimes: {},
            errorCount: 0,
            userInteractions: 0,
            websocketStability: 0
        },
        analyticsQueue: [],
        
        // WebSocket Connection
        websocket: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        
        // Service Worker
        serviceWorker: null,
        
        /**
         * Initialize the dashboard
         */
        async init() {
            console.log('🚀 Initializing Enhanced Customer Dashboard');
            
            try {
                // Load initial data
                await this.loadInitialData();
                
                // Setup real-time features
                this.setupWebSocket();
                this.setupServiceWorker();
                
                // Initialize auto-refresh
                if (this.autoRefresh) {
                    this.startAutoRefresh();
                }
                
                // Setup event listeners
                this.setupEventListeners();
                
                // Load user preferences
                await this.loadUserPreferences();
                
                // Setup performance monitoring
                this.setupPerformanceMonitoring();
                
                // Record page load performance
                this.recordPageLoadMetrics();
                
                // Mark as live
                this.isLiveData = true;
                
                console.log('✅ Dashboard initialized successfully');
                
            } catch (error) {
                console.error('❌ Dashboard initialization failed:', error);
                this.handleError(error);
            }
        },
        
        /**
         * Load initial dashboard data
         */
        async loadInitialData() {
            try {
                // Use provided data if available
                if (window.dashboardInitialData) {
                    this.dashboardData = { 
                        ...this.dashboardData, 
                        ...window.dashboardInitialData 
                    };
                }
                
                // Load fresh data in parallel
                const [realtimeData, analyticsData, recommendations, notifications, alerts] = await Promise.allSettled([
                    this.fetchWithCache('realtime'),
                    this.fetchWithCache('analytics'),
                    this.fetchWithCache('recommendations'),
                    this.fetchWithCache('notifications'),
                    this.fetchUserAlerts()
                ]);
                
                // Process successful responses
                if (realtimeData.status === 'fulfilled') {
                    this.dashboardData = { ...this.dashboardData, ...realtimeData.value };
                }
                
                if (analyticsData.status === 'fulfilled') {
                    this.dashboardData.trends = analyticsData.value.trends || {};
                }
                
                if (recommendations.status === 'fulfilled') {
                    this.dashboardData.personalizedRecommendations = recommendations.value.recommendations || [];
                }
                
                if (notifications.status === 'fulfilled') {
                    this.processNotifications(notifications.value);
                }

                if (alerts.status === 'fulfilled') {
                    this.userAlerts = alerts.value || [];
                }
                
                this.lastUpdate = new Date();
                
            } catch (error) {
                console.error('Failed to load initial data:', error);
                throw error;
            }
        },

        /**
         * Apply current filters and refresh tickets
         */
        async applyFilters() {
            this.isFiltering = true;
            try {
                await this.fetchFilteredTickets();
            } finally {
                this.isFiltering = false;
            }
        },

        /**
         * Reset filters to defaults
         */
        async resetFilters() {
            this.filters = { sports: [], platforms: [], price_min: null, price_max: null, date_from: null, date_to: null, sort: 'newest' };
            await this.applyFilters();
        },

        /**
         * Compute active filter count
         */
        computeFilterCount() {
            let count = 0;
            if (this.filters.sports?.length) count += 1;
            if (this.filters.platforms?.length) count += 1;
            if (this.filters.price_min != null || this.filters.price_max != null) count += 1;
            if (this.filters.date_from || this.filters.date_to) count += 1;
            if (this.filters.sort && this.filters.sort !== 'newest') count += 1;
            this.filterCount = count;
        },

        /**
         * Fetch filtered tickets from API
         */
        async fetchFilteredTickets() {
            this.computeFilterCount();
            const params = new URLSearchParams();
            if (this.filters.sports?.length) this.filters.sports.forEach(s => params.append('sport[]', s));
            if (this.filters.platforms?.length) this.filters.platforms.forEach(p => params.append('platform[]', p));
            if (this.filters.price_min != null) params.set('price_min', this.filters.price_min);
            if (this.filters.price_max != null) params.set('price_max', this.filters.price_max);
            if (this.filters.date_from) params.set('date_from', this.filters.date_from);
            if (this.filters.date_to) params.set('date_to', this.filters.date_to);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            params.set('limit', '12');

            const url = `${DASHBOARD_CONFIG.endpoints.ticketsFilter}?${params.toString()}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, method: 'GET' });
            if (!res.ok) throw new Error('Failed to fetch filtered tickets');
            const json = await res.json();
            // Expecting json.data or json.tickets
            const items = json.data?.tickets || json.data || json.tickets || [];
            this.dashboardData.recentTickets = items;
            this.recentTickets = items;
            this.lastUpdate = new Date();
        },
        
        /**
         * Fetch user alerts
         */
        async fetchUserAlerts() {
            try {
                const res = await fetch('/api/v1/dashboard/notifications', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return [];
                const json = await res.json();
                // Adapt: if endpoint returns notifications, map to alerts fallback
                if (Array.isArray(json?.data)) {
                    return json.data.filter(n => n.type === 'alert').map(n => ({
                        id: n.id,
                        name: n.title || 'Alert',
                        is_active: !n.read_at,
                        matches_count: n.matches_count || 0,
                        success_rate: n.success_rate || 0,
                        criteria: n.criteria || {}
                    }));
                }
                // If EnhancedDashboardController exposes alerts_data
                if (json?.alerts) return json.alerts;
                return [];
            } catch (_) { return []; }
        },

        getAlertCriteria(alert) {
            const parts = [];
            if (alert.criteria?.sport) parts.push(`Sport: ${alert.criteria.sport}`);
            if (alert.criteria?.platform) parts.push(`Platform: ${alert.criteria.platform}`);
            if (alert.criteria?.max_price) parts.push(`<= $${alert.criteria.max_price}`);
            return parts.join(' • ') || 'Any';
        },

        async createAlert() {
            this.isCreatingAlert = true;
            try {
                const payload = {
                    name: this.newAlert.name,
                    sport: this.newAlert.sport || null,
                    max_price: this.newAlert.max_price || null,
                    platform: this.newAlert.platform || null,
                };
                const res = await fetch('/api/v1/alerts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error('Failed to create alert');
                await this.refreshAlerts();
                this.showCreateAlert = false;
                this.newAlert = { name: '', sport: '', max_price: null, platform: '' };
            } catch (e) {
                console.error(e);
            } finally {
                this.isCreatingAlert = false;
            }
        },

        async refreshAlerts() {
            this.userAlerts = await this.fetchUserAlerts();
        },

        async toggleAlert(alert) {
            try {
                const url = `/api/v1/alerts/${alert.uuid || alert.id}/toggle`;
                const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Toggle failed');
                await this.refreshAlerts();
            } catch (e) { console.error(e); }
        },

        editAlert(alert) {
            // Simple placeholder open settings with prefilled data
            this.showCreateAlert = true;
            this.newAlert = { name: alert.name, sport: alert.criteria?.sport || '', max_price: alert.criteria?.max_price || null, platform: alert.criteria?.platform || '' };
        },

        async deleteAlert(alert) {
            if (!confirm('Delete this alert?')) return;
            try {
                const url = `/api/v1/alerts/${alert.uuid || alert.id}`;
                const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Delete failed');
                await this.refreshAlerts();
            } catch (e) { console.error(e); }
        },

        useTemplate(type) {
            if (type === 'price_drop') this.newAlert = { name: 'Price Drop', sport: '', max_price: 100, platform: '' };
            if (type === 'last_minute') this.newAlert = { name: 'Last Minute', sport: '', max_price: 50, platform: '' };
            if (type === 'premium_seats') this.newAlert = { name: 'Premium Seats', sport: '', max_price: 500, platform: '' };
            this.showCreateAlert = true;
            this.trackInteraction('template_used', { template: type });
        },
        
        /**
         * Record page load performance metrics
         */
        recordPageLoadMetrics() {
            if (performance && performance.timing) {
                const timing = performance.timing;
                this.performanceMetrics.pageLoadTime = timing.loadEventEnd - timing.navigationStart;
                
                // Add performance marks
                if (performance.mark) {
                    performance.mark('dashboard-init-complete');
                }
                
                this.trackEvent('page_load', {
                    load_time: this.performanceMetrics.pageLoadTime,
                    dom_ready: timing.domContentLoadedEventEnd - timing.navigationStart,
                    first_paint: timing.responseEnd - timing.navigationStart
                });
            }
        },
        
        /**
         * Setup comprehensive performance monitoring
         */
        setupPerformanceMonitoring() {
            // Monitor API response times
            this.monitorApiPerformance();
            
            // Track user interactions
            this.setupInteractionTracking();
            
            // Monitor WebSocket stability
            this.monitorWebSocketStability();
            
            // Setup error tracking
            this.setupErrorTracking();
            
            // Send analytics data periodically
            this.startAnalyticsReporting();
        },
        
        /**
         * Monitor API performance
         */
        monitorApiPerformance() {
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                const startTime = performance.now();
                const url = args[0];
                
                try {
                    const response = await originalFetch(...args);
                    const endTime = performance.now();
                    const duration = endTime - startTime;
                    
                    // Record API response time
                    const endpoint = this.getEndpointName(url);
                    if (!this.performanceMetrics.apiResponseTimes[endpoint]) {
                        this.performanceMetrics.apiResponseTimes[endpoint] = [];
                    }
                    this.performanceMetrics.apiResponseTimes[endpoint].push(duration);
                    
                    // Track successful API calls
                    this.trackEvent('api_call', {
                        endpoint: endpoint,
                        duration: Math.round(duration),
                        status: response.status,
                        success: response.ok
                    });
                    
                    return response;
                } catch (error) {
                    const endTime = performance.now();
                    const duration = endTime - startTime;
                    
                    // Track failed API calls
                    this.trackEvent('api_error', {
                        endpoint: this.getEndpointName(url),
                        duration: Math.round(duration),
                        error: error.message
                    });
                    
                    this.performanceMetrics.errorCount++;
                    throw error;
                }
            };
        },
        
        /**
         * Setup interaction tracking
         */
        setupInteractionTracking() {
            document.addEventListener('click', (event) => {
                this.performanceMetrics.userInteractions++;
                
                // Track specific dashboard interactions
                const target = event.target.closest('[data-track]');
                if (target) {
                    this.trackInteraction('click', {
                        element: target.dataset.track,
                        timestamp: Date.now()
                    });
                }
            });
            
            // Track scroll events (throttled)
            let scrollTimeout;
            document.addEventListener('scroll', () => {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    this.trackInteraction('scroll', {
                        scrollTop: window.scrollY,
                        timestamp: Date.now()
                    });
                }, 500);
            });
        },
        
        /**
         * Monitor WebSocket connection stability
         */
        monitorWebSocketStability() {
            let connectionAttempts = 0;
            let successfulConnections = 0;
            
            const originalSetupWebSocket = this.setupWebSocket;
            this.setupWebSocket = () => {
                connectionAttempts++;
                
                originalSetupWebSocket.call(this);
                
                if (this.websocket) {
                    this.websocket.addEventListener('open', () => {
                        successfulConnections++;
                        this.performanceMetrics.websocketStability = (successfulConnections / connectionAttempts) * 100;
                        
                        this.trackEvent('websocket_connect', {
                            attempts: connectionAttempts,
                            success_rate: this.performanceMetrics.websocketStability
                        });
                    });
                    
                    this.websocket.addEventListener('error', (error) => {
                        this.trackEvent('websocket_error', {
                            error: error.type,
                            attempts: connectionAttempts
                        });
                    });
                }
            };
        },
        
        /**
         * Setup error tracking
         */
        setupErrorTracking() {
            window.addEventListener('error', (event) => {
                this.performanceMetrics.errorCount++;
                
                this.trackEvent('js_error', {
                    message: event.message,
                    filename: event.filename,
                    line: event.lineno,
                    column: event.colno,
                    stack: event.error?.stack?.substring(0, 500)
                });
            });
            
            window.addEventListener('unhandledrejection', (event) => {
                this.performanceMetrics.errorCount++;
                
                this.trackEvent('promise_rejection', {
                    reason: event.reason?.toString?.()?.substring(0, 200) || 'Unknown',
                    stack: event.reason?.stack?.substring(0, 500)
                });
            });
        },
        
        /**
         * Track custom events
         */
        trackEvent(eventName, data = {}) {
            const event = {
                name: eventName,
                data: data,
                timestamp: Date.now(),
                url: window.location.pathname,
                user_agent: navigator.userAgent,
                session_id: this.getSessionId()
            };
            
            this.analyticsQueue.push(event);
            
            // Immediate send for critical events
            if (['js_error', 'api_error', 'websocket_error'].includes(eventName)) {
                this.sendAnalytics([event]);
            }
        },
        
        /**
         * Track user interactions
         */
        trackInteraction(type, data = {}) {
            this.trackEvent('user_interaction', {
                interaction_type: type,
                ...data
            });
        },
        
        /**
         * Start periodic analytics reporting
         */
        startAnalyticsReporting() {
            // Send analytics every 30 seconds
            setInterval(() => {
                if (this.analyticsQueue.length > 0) {
                    const events = [...this.analyticsQueue];
                    this.analyticsQueue = [];
                    this.sendAnalytics(events);
                }
            }, 30000);
            
            // Send analytics when user leaves
            window.addEventListener('beforeunload', () => {
                if (this.analyticsQueue.length > 0) {
                    navigator.sendBeacon(
                        '/api/v1/dashboard/analytics',
                        JSON.stringify({
                            events: this.analyticsQueue,
                            performance_summary: this.getPerformanceSummary()
                        })
                    );
                }
            });
        },
        
        /**
         * Send analytics data to server
         */
        async sendAnalytics(events) {
            try {
                await fetch('/api/v1/dashboard/analytics', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        events: events,
                        performance_summary: this.getPerformanceSummary()
                    })
                });
            } catch (error) {
                console.warn('Analytics tracking failed:', error);
            }
        },
        
        /**
         * Get performance summary
         */
        getPerformanceSummary() {
            const avgApiTimes = {};
            for (const [endpoint, times] of Object.entries(this.performanceMetrics.apiResponseTimes)) {
                avgApiTimes[endpoint] = times.length > 0 
                    ? Math.round(times.reduce((a, b) => a + b, 0) / times.length)
                    : 0;
            }
            
            return {
                page_load_time: this.performanceMetrics.pageLoadTime,
                avg_api_response_times: avgApiTimes,
                error_count: this.performanceMetrics.errorCount,
                user_interactions: this.performanceMetrics.userInteractions,
                websocket_stability: this.performanceMetrics.websocketStability,
                session_duration: Date.now() - (this.initTime || Date.now())
            };
        },
        
        /**
         * Helper methods
         */
        getEndpointName(url) {
            if (typeof url !== 'string') return 'unknown';
            const parts = url.split('/');
            return parts[parts.length - 1] || parts[parts.length - 2] || 'root';
        },
        
        getSessionId() {
            if (!this.sessionId) {
                this.sessionId = 'dashboard_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            }
            return this.sessionId;
        },

        /**
         * Setup WebSocket connection for real-time updates
         */
        setupWebSocket() {
            if (!window.WebSocket) {
                console.warn('WebSocket not supported, falling back to polling');
                return;
            }
            
            try {
                const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
                const wsUrl = `${protocol}//${window.location.host}/ws/dashboard`;
                
                this.websocket = new WebSocket(wsUrl);
                
                this.websocket.onopen = () => {
                    console.log('🔌 WebSocket connected');
                    this.reconnectAttempts = 0;
                    this.isLiveData = true;
                    
                    // Send authentication
                    this.websocket.send(JSON.stringify({
                        type: 'auth',
                        token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }));
                };
                
                this.websocket.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        this.handleWebSocketMessage(data);
                    } catch (error) {
                        console.error('Failed to parse WebSocket message:', error);
                    }
                };
                
                this.websocket.onclose = (event) => {
                    console.log('🔌 WebSocket disconnected:', event.code, event.reason);
                    this.isLiveData = false;
                    
                    // Attempt reconnection
                    if (this.reconnectAttempts < this.maxReconnectAttempts) {
                        this.reconnectAttempts++;
                        setTimeout(() => {
                            console.log(`🔄 Reconnecting WebSocket (attempt ${this.reconnectAttempts})`);
                            this.setupWebSocket();
                        }, Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000));
                    }
                };
                
                this.websocket.onerror = (error) => {
                    console.error('🔌 WebSocket error:', error);
                    this.isLiveData = false;
                };
                
            } catch (error) {
                console.error('Failed to setup WebSocket:', error);
            }
        },
        
        /**
         * Handle WebSocket messages
         */
        handleWebSocketMessage(data) {
            switch (data.type) {
                case 'realtime_update':
                    this.dashboardData.statistics = { ...this.dashboardData.statistics, ...data.payload };
                    this.lastUpdate = new Date();
                    break;
                    
                case 'new_tickets':
                    this.dashboardData.recentTickets = data.payload;
                    this.showNotification('New tickets available!', 'success');
                    break;
                    
                case 'alert_triggered':
                    this.processNotifications([data.payload]);
                    this.showNotification(data.payload.message, 'warning');
                    break;
                    
                case 'system_status':
                    this.isLiveData = data.payload.status === 'online';
                    break;
                    
                default:
                    console.log('Unknown WebSocket message type:', data.type);
            }
        },
        
        /**
         * Setup Service Worker for offline support
         */
        async setupServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.register('/sw-dashboard.js');
                    console.log('📱 Service Worker registered:', registration);
                    this.serviceWorker = registration;
                    
                    // Listen for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                this.showNotification('Dashboard updated! Refresh to see changes.', 'info');
                            }
                        });
                    });
                    
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        },
        
        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Visibility change - pause/resume updates when tab is hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseUpdates();
                } else {
                    this.resumeUpdates();
                }
            });
            
            // Online/offline status
            window.addEventListener('online', () => {
                console.log('🌐 Back online');
                this.resumeUpdates();
                this.showNotification('Connection restored', 'success');
            });
            
            window.addEventListener('offline', () => {
                console.log('🌐 Gone offline');
                this.pauseUpdates();
                this.showNotification('Working offline', 'warning');
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + R for manual refresh
                if ((e.ctrlKey || e.metaKey) && e.key === 'r' && !e.shiftKey) {
                    e.preventDefault();
                    this.refreshDashboard();
                }
                
                // Escape to close modals
                if (e.key === 'Escape') {
                    this.showNotifications = false;
                    this.showSettings = false;
                }
            });
            
            // Performance observer
            if ('PerformanceObserver' in window) {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.entryType === 'navigation') {
                            console.log(`📊 Page load: ${entry.loadEventEnd - entry.fetchStart}ms`);
                        }
                    }
                });
                observer.observe({entryTypes: ['navigation']});
            }
        },
        
        /**
         * Setup performance monitoring
         */
        setupPerformanceMonitoring() {
            // Monitor API response times
            this.apiResponseTimes = [];
            
            // Track user interactions
            this.userInteractions = {
                clicks: 0,
                refreshes: 0,
                navigations: 0
            };
            
            // Send analytics periodically
            setInterval(() => {
                this.sendAnalytics();
            }, 300000); // 5 minutes
        },
        
        /**
         * Fetch data with caching
         */
        async fetchWithCache(endpoint, options = {}) {
            const cacheKey = `${endpoint}_${JSON.stringify(options)}`;
            const cached = this.cache.get(cacheKey);
            const expiry = this.cacheExpiry.get(cacheKey);
            
            // Return cached data if still valid
            if (cached && expiry && Date.now() < expiry) {
                return cached;
            }
            
            try {
                const startTime = performance.now();
                const response = await this.fetchWithTimeout(
                    DASHBOARD_CONFIG.endpoints[endpoint] || `/api/dashboard/${endpoint}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        ...options
                    }
                );
                
                const endTime = performance.now();
                this.apiResponseTimes.push(endTime - startTime);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                // Cache the response
                this.cache.set(cacheKey, data);
                this.cacheExpiry.set(cacheKey, Date.now() + 60000); // 1 minute cache
                
                return data;
                
            } catch (error) {
                console.error(`API fetch failed for ${endpoint}:`, error);
                
                // Return cached data if available, even if expired
                if (cached) {
                    console.log(`Using stale cache for ${endpoint}`);
                    return cached;
                }
                
                throw error;
            }
        },
        
        /**
         * Fetch with timeout
         */
        async fetchWithTimeout(url, options = {}) {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), DASHBOARD_CONFIG.apiTimeout);
            
            try {
                const response = await fetch(url, {
                    ...options,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                return response;
            } catch (error) {
                clearTimeout(timeoutId);
                throw error;
            }
        },
        
        /**
         * Refresh dashboard data
         */
        async refreshDashboard() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.userInteractions.refreshes++;
            
            try {
                // Clear relevant caches
                this.clearCache(['realtime', 'analytics']);
                
                // Fetch fresh data
                const [realtimeData, analyticsData] = await Promise.allSettled([
                    this.fetchWithCache('realtime'),
                    this.fetchWithCache('analytics')
                ]);
                
                // Update data
                if (realtimeData.status === 'fulfilled') {
                    this.dashboardData = { ...this.dashboardData, ...realtimeData.value };
                }
                
                if (analyticsData.status === 'fulfilled') {
                    this.dashboardData.trends = analyticsData.value.trends || {};
                }
                
                this.lastUpdate = new Date();
                this.retryCount = 0;
                
                // Subtle feedback
                this.showNotification('Dashboard updated', 'success', 2000);
                
            } catch (error) {
                console.error('Failed to refresh dashboard:', error);
                this.handleError(error);
            } finally {
                this.isLoading = false;
            }
        },
        
        /**
         * Refresh recent tickets
         */
        async refreshRecentTickets() {
            try {
                this.clearCache(['realtime']);
                const data = await this.fetchWithCache('realtime');
                this.dashboardData.recentTickets = data.recentTickets || [];
            } catch (error) {
                console.error('Failed to refresh recent tickets:', error);
            }
        },
        
        /**
         * Toggle auto-refresh
         */
        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            
            if (this.autoRefresh) {
                this.startAutoRefresh();
                this.showNotification('Auto-refresh enabled', 'success', 2000);
            } else {
                this.stopAutoRefresh();
                this.showNotification('Auto-refresh disabled', 'info', 2000);
            }
            
            // Save preference
            this.settings.autoRefresh = this.autoRefresh;
            this.saveUserPreferences();
        },
        
        /**
         * Start auto-refresh timer
         */
        startAutoRefresh() {
            this.stopAutoRefresh(); // Clear existing timer
            
            this.refreshTimer = setInterval(() => {
                if (!document.hidden && navigator.onLine) {
                    this.refreshDashboard();
                }
            }, this.refreshInterval);
        },
        
        /**
         * Stop auto-refresh timer
         */
        stopAutoRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }
        },
        
        /**
         * Pause updates (when tab is hidden)
         */
        pauseUpdates() {
            this.stopAutoRefresh();
            if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
                this.websocket.send(JSON.stringify({ type: 'pause' }));
            }
        },
        
        /**
         * Resume updates (when tab becomes visible)
         */
        resumeUpdates() {
            if (this.autoRefresh) {
                this.startAutoRefresh();
            }
            if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
                this.websocket.send(JSON.stringify({ type: 'resume' }));
            }
        },
        
        /**
         * Handle errors with retry logic
         */
        handleError(error) {
            this.retryCount++;
            
            if (this.retryCount < DASHBOARD_CONFIG.maxRetries) {
                console.log(`Retrying in ${DASHBOARD_CONFIG.retryDelay}ms (attempt ${this.retryCount})`);
                setTimeout(() => {
                    this.refreshDashboard();
                }, DASHBOARD_CONFIG.retryDelay);
            } else {
                console.error('Max retries reached. Stopping auto-refresh.');
                this.autoRefresh = false;
                this.stopAutoRefresh();
                this.showNotification('Connection issues detected. Auto-refresh disabled.', 'error');
            }
        },
        
        /**
         * Process notifications
         */
        processNotifications(notifications) {
            if (!Array.isArray(notifications)) return;
            
            this.dashboardData.notifications = notifications;
            this.notificationCount = notifications.filter(n => !n.read).length;
            this.hasNotifications = this.notificationCount > 0;
            
            // Show browser notification for high priority alerts
            notifications.forEach(notification => {
                if (notification.priority === 'high' && !notification.read) {
                    this.showBrowserNotification(notification);
                }
            });
        },
        
        /**
         * Show browser notification
         */
        async showBrowserNotification(notification) {
            if (!('Notification' in window)) return;
            
            if (Notification.permission === 'default') {
                await Notification.requestPermission();
            }
            
            if (Notification.permission === 'granted') {
                new Notification(notification.title, {
                    body: notification.message,
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                    tag: `hdtickets-${notification.id}`,
                    requireInteraction: notification.priority === 'high'
                });
            }
        },
        
        /**
         * Show in-app notification
         */
        showNotification(message, type = 'info', duration = 4000) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${this.getNotificationIcon(type)}
                    </svg>
                    <span class="notification-message">${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            
            // Add styles
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.9);
                backdrop-filter: blur(10px);
                color: white;
                padding: 16px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 400px;
                display: flex;
                align-items: center;
                gap: 12px;
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            requestAnimationFrame(() => {
                notification.style.transform = 'translateX(0)';
            });
            
            // Auto remove
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        },
        
        /**
         * Get notification icon SVG
         */
        getNotificationIcon(type) {
            const icons = {
                success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
                error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
                warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
                info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            };
            return icons[type] || icons.info;
        },
        
        /**
         * Navigation helpers
         */
        navigateTo(url) {
            this.userInteractions.navigations++;
            window.location.href = url;
        },
        
        viewTicket(ticket) {
            this.userInteractions.clicks++;
            window.open(`/tickets/scraping/${ticket.id}`, '_blank');
        },
        
        createEventAlert(eventId) {
            this.navigateTo(`/tickets/alerts/create?event_id=${eventId}`);
        },
        
        /**
         * Modal management
         */
        openNotifications() {
            this.showNotifications = true;
            // Mark notifications as read
            if (this.dashboardData.notifications.length > 0) {
                this.markNotificationsAsRead();
            }
        },
        
        openSettings() {
            this.showSettings = true;
        },
        
        /**
         * Mark notifications as read
         */
        async markNotificationsAsRead() {
            try {
                await this.fetchWithCache('notifications', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'mark_read' }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                this.notificationCount = 0;
                this.hasNotifications = false;
                
            } catch (error) {
                console.error('Failed to mark notifications as read:', error);
            }
        },
        
        /**
         * Load user preferences
         */
        async loadUserPreferences() {
            try {
                // Load from localStorage first
                const stored = localStorage.getItem('dashboard-preferences');
                if (stored) {
                    this.settings = { ...this.settings, ...JSON.parse(stored) };
                }
                
                // Then try to load from server
                const serverPrefs = await this.fetchWithCache('settings');
                if (serverPrefs.preferences) {
                    this.settings = { ...this.settings, ...serverPrefs.preferences };
                    localStorage.setItem('dashboard-preferences', JSON.stringify(this.settings));
                }
                
                // Apply settings
                this.applySettings();
                
            } catch (error) {
                console.error('Failed to load user preferences:', error);
            }
        },
        
        /**
         * Save user preferences
         */
        async saveUserPreferences() {
            try {
                // Save to localStorage immediately
                localStorage.setItem('dashboard-preferences', JSON.stringify(this.settings));
                
                // Save to server
                await this.fetchWithCache('settings', {
                    method: 'POST',
                    body: JSON.stringify({ preferences: this.settings }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                this.showNotification('Settings saved', 'success', 2000);
                
            } catch (error) {
                console.error('Failed to save user preferences:', error);
                this.showNotification('Failed to save settings', 'error');
            }
        },
        
        /**
         * Apply user settings
         */
        applySettings() {
            // Auto-refresh
            this.autoRefresh = this.settings.autoRefresh;
            this.refreshInterval = this.settings.refreshInterval * 60000; // Convert minutes to ms
            
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
            
            // Theme
            document.documentElement.setAttribute('data-theme', this.settings.theme);
        },
        
        /**
         * Save settings from modal
         */
        async saveSettings() {
            this.applySettings();
            await this.saveUserPreferences();
            this.showSettings = false;
        },
        
        /**
         * Utility functions
         */
        formatNumber(num) {
            if (typeof num !== 'number') return '0';
            return num.toLocaleString();
        },
        
        formatPrice(price) {
            if (typeof price !== 'number') return '$0.00';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(price);
        },
        
        formatDate(dateString) {
            if (!dateString) return 'TBD';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },
        
        formatChange(change) {
            if (typeof change !== 'number') return '';
            const sign = change >= 0 ? '+' : '';
            return `${sign}${change.toFixed(1)}%`;
        },
        
        getTrendClass(trend) {
            if (!trend) return 'neutral';
            return trend > 0 ? 'positive' : trend < 0 ? 'negative' : 'neutral';
        },
        
        getDataFreshnessClass() {
            const now = Date.now();
            const lastUpdateTime = this.lastUpdate.getTime();
            const ageMinutes = (now - lastUpdateTime) / (1000 * 60);
            
            if (ageMinutes < 2) return 'fresh';
            if (ageMinutes < 5) return 'stale';
            return 'outdated';
        },
        
        getDataFreshnessText() {
            const className = this.getDataFreshnessClass();
            const texts = {
                fresh: 'Live data',
                stale: 'Recent data',
                outdated: 'Updating...'
            };
            return texts[className];
        },
        
        getLastUpdateTime() {
            const now = new Date();
            const diff = now.getTime() - this.lastUpdate.getTime();
            
            if (diff < 60000) return 'just now';
            if (diff < 3600000) return `${Math.floor(diff / 60000)} min ago`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)} hr ago`;
            return this.lastUpdate.toLocaleDateString();
        },
        
        /**
         * Clear cache entries
         */
        clearCache(endpoints = []) {
            if (endpoints.length === 0) {
                this.cache.clear();
                this.cacheExpiry.clear();
            } else {
                endpoints.forEach(endpoint => {
                    Array.from(this.cache.keys())
                        .filter(key => key.startsWith(endpoint))
                        .forEach(key => {
                            this.cache.delete(key);
                            this.cacheExpiry.delete(key);
                        });
                });
            }
        },
        
        /**
         * Send analytics data
         */
        async sendAnalytics() {
            try {
                const analytics = {
                    interactions: this.userInteractions,
                    performance: {
                        avgResponseTime: this.apiResponseTimes.length > 0 
                            ? this.apiResponseTimes.reduce((a, b) => a + b, 0) / this.apiResponseTimes.length 
                            : 0,
                        cacheHitRate: this.getCacheHitRate()
                    },
                    timestamp: Date.now()
                };
                
                await this.fetchWithCache('analytics', {
                    method: 'POST',
                    body: JSON.stringify(analytics),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                // Reset counters
                this.userInteractions = { clicks: 0, refreshes: 0, navigations: 0 };
                this.apiResponseTimes = [];
                
            } catch (error) {
                console.error('Failed to send analytics:', error);
            }
        },
        
        /**
         * Calculate cache hit rate
         */
        getCacheHitRate() {
            // This would be tracked in a real implementation
            return 0.75; // Placeholder
        },
        
        /**
         * Cleanup when component is destroyed
         */
        destroy() {
            console.log('🧹 Cleaning up dashboard');
            
            this.stopAutoRefresh();
            
            if (this.websocket) {
                this.websocket.close();
            }
            
            // Clear caches
            this.cache.clear();
            this.cacheExpiry.clear();
            
            // Send final analytics
            this.sendAnalytics();
        }
    };
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Enhanced Customer Dashboard script loaded');
    
    // Register Alpine.js component globally
    if (window.Alpine) {
        Alpine.data('enhancedCustomerDashboard', enhancedCustomerDashboard);
    }
});

// Handle page unload
window.addEventListener('beforeunload', () => {
    // Try to clean up if component exists
    const dashboard = document.querySelector('[x-data="enhancedCustomerDashboard()"]');
    if (dashboard && dashboard._x_dataStack && dashboard._x_dataStack[0]) {
        dashboard._x_dataStack[0].destroy();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = enhancedCustomerDashboard;
}

// Global helper functions
window.dashboardHelpers = {
    formatNumber: (num) => typeof num === 'number' ? num.toLocaleString() : '0',
    formatPrice: (price) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price || 0),
    formatDate: (date) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'TBD'
};

console.log('✅ Enhanced Customer Dashboard JavaScript loaded successfully');
