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
        events: '/api/v1/dashboard/events'
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
        
        // UI State
        showNotifications: false,
        showSettings: false,
        notificationCount: 0,
        hasNotifications: false,
        
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
                const [realtimeData, analyticsData, recommendations, notifications] = await Promise.allSettled([
                    this.fetchWithCache('realtime'),
                    this.fetchWithCache('analytics'),
                    this.fetchWithCache('recommendations'),
                    this.fetchWithCache('notifications')
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
                
                this.lastUpdate = new Date();
                
            } catch (error) {
                console.error('Failed to load initial data:', error);
                throw error;
            }
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
