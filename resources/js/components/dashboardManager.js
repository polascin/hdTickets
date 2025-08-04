/**
 * Dashboard Manager Alpine.js Component
 * Handles dashboard state, real-time updates, and user interactions
 */
export function dashboardManager() {
    return {
        // Component state
        loading: false,
        stats: {
            activeMonitors: 0,
            alertsToday: 0,
            priceDrops: 0,
            availableNow: 0,
            lastUpdate: null
        },
        platformStatus: new Map(),
        recentAlerts: [],
        refreshInterval: null,
        websocketConnected: false,
        
        // Component initialization
        init() {
            console.log('Dashboard Manager initialized');
            
            // Initialize core integration
            this.setupCoreIntegration();
            
            // Load initial data
            this.loadDashboardData();
            
            // Setup real-time updates
            this.setupRealTimeUpdates();
            
            // Setup periodic refresh
            this.setupPeriodicRefresh();
            
            // Update clock
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            
            // Listen for app events
            this.setupEventListeners();
        },

        // Core integration setup
        setupCoreIntegration() {
            if (window.AppCore) {
                // Subscribe to core events
                window.AppCore.on('websocket:connected', () => {
                    this.websocketConnected = true;
                    this.showNotification('success', 'Connected to live updates');
                });
                
                window.AppCore.on('websocket:disconnected', () => {
                    this.websocketConnected = false;
                    this.showNotification('warning', 'Connection lost - attempting to reconnect');
                });
                
                window.AppCore.on('stats:updated', (data) => {
                    this.updateStats(data);
                });
                
                window.AppCore.on('alert:new', (alert) => {
                    this.handleNewAlert(alert);
                });
                
                window.AppCore.on('platform:status', (status) => {
                    this.updatePlatformStatus(status);
                });
            }
        },

        // Load initial dashboard data
        async loadDashboardData() {
            this.loading = true;
            
            try {
                const response = await window.AppCore?.apiRequest('/api/dashboard/stats') || 
                    await fetch('/api/dashboard/stats', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Accept': 'application/json'
                        }
                    });
                
                if (response.ok) {
                    const data = await response.json();
                    this.updateStats(data.stats);
                    this.recentAlerts = data.recent_alerts || [];
                    this.platformStatus = new Map(Object.entries(data.platform_status || {}));
                }
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
                this.showNotification('error', 'Failed to load dashboard data');
            } finally {
                this.loading = false;
            }
        },

        // Update statistics
        updateStats(newStats) {
            if (!newStats) return;
            
            // Animate changes
            Object.keys(newStats).forEach(key => {
                if (this.stats[key] !== newStats[key]) {
                    this.animateStatChange(key, this.stats[key], newStats[key]);
                }
            });
            
            this.stats = { ...this.stats, ...newStats };
            this.stats.lastUpdate = new Date();
        },

        // Animate stat changes
        animateStatChange(statKey, oldValue, newValue) {
            const element = document.querySelector(`[x-stat="${statKey}"]`);
            if (element) {
                element.classList.add('stat-changing');
                setTimeout(() => {
                    element.classList.remove('stat-changing');
                    element.classList.add('stat-changed');
                    setTimeout(() => element.classList.remove('stat-changed'), 1000);
                }, 100);
            }
        },

        // Handle new alerts
        handleNewAlert(alert) {
            // Add to recent alerts
            this.recentAlerts.unshift(alert);
            if (this.recentAlerts.length > 10) {
                this.recentAlerts.pop();
            }
            
            // Show notification
            this.showNotification('success', `New alert: ${alert.title}`);
            
            // Update stats
            this.stats.alertsToday++;
            
            // Play sound if enabled
            this.playNotificationSound();
        },

        // Update platform status
        updatePlatformStatus(status) {
            Object.entries(status).forEach(([platform, platformStatus]) => {
                this.platformStatus.set(platform, platformStatus);
            });
        },

        // Setup real-time updates
        setupRealTimeUpdates() {
            if (window.AppCore?.getModule('websocket')) {
                const ws = window.AppCore.getModule('websocket');
                
                ws.subscribe('dashboard.stats', (data) => {
                    this.updateStats(data);
                });
                
                ws.subscribe('alerts.new', (alert) => {
                    this.handleNewAlert(alert);
                });
                
                ws.subscribe('platform.status', (status) => {
                    this.updatePlatformStatus(status);
                });
            }
        },

        // Setup periodic refresh
        setupPeriodicRefresh() {
            this.refreshInterval = setInterval(() => {
                if (!this.websocketConnected) {
                    this.loadDashboardData();
                }
            }, 30000); // Refresh every 30 seconds if websocket is not connected
        },

        // Update clock
        updateClock() {
            const clockElement = document.getElementById('currentTime');
            if (clockElement) {
                clockElement.textContent = new Date().toLocaleTimeString();
            }
        },

        // Setup event listeners
        setupEventListeners() {
            // Listen for visibility changes
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.loadDashboardData(); // Refresh when tab becomes visible
                }
            });
            
            // Listen for network status
            window.addEventListener('online', () => {
                this.showNotification('success', 'Connection restored');
                this.loadDashboardData();
            });
            
            window.addEventListener('offline', () => {
                this.showNotification('warning', 'Connection lost');
            });
        },

        // Manual refresh
        async refresh() {
            this.showNotification('info', 'Refreshing dashboard...');
            await this.loadDashboardData();
            this.showNotification('success', 'Dashboard refreshed');
        },

        // Reconnect websocket
        reconnectWebSocket() {
            if (window.AppCore?.getModule('websocket')) {
                window.AppCore.getModule('websocket').reconnect();
            }
        },

        // Show notification
        showNotification(type, message) {
            if (window.AppCore?.getModule('uiFeedback')) {
                window.AppCore.getModule('uiFeedback')[type](message);
            } else if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback[type]('Dashboard', message);
            }
        },

        // Play notification sound
        playNotificationSound() {
            if (window.AppCore?.getModule('userPreferences')?.get('notifications.sound')) {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.3;
                audio.play().catch(() => {}); // Ignore errors
            }
        },

        // Format price
        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(price);
        },

        // Format time ago
        timeAgo(date) {
            if (!date) return '';
            const now = new Date();
            const diffMs = now - new Date(date);
            const diffMins = Math.floor(diffMs / 60000);
            
            if (diffMins < 1) return 'just now';
            if (diffMins === 1) return '1 minute ago';
            if (diffMins < 60) return `${diffMins} minutes ago`;
            
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours === 1) return '1 hour ago';
            if (diffHours < 24) return `${diffHours} hours ago`;
            
            const diffDays = Math.floor(diffHours / 24);
            if (diffDays === 1) return '1 day ago';
            return `${diffDays} days ago`;
        },

        // Get platform status color
        getPlatformStatusColor(platform) {
            const status = this.platformStatus.get(platform);
            if (!status) return 'gray';
            
            switch (status.status) {
                case 'online': return 'green';
                case 'slow': return 'yellow';
                case 'offline': return 'red';
                default: return 'gray';
            }
        },

        // Component cleanup
        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            
            console.log('Dashboard Manager destroyed');
        }
    };
}
