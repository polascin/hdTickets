/**
 * Enhanced Customer Dashboard Alpine.js Component
 * HD Tickets Sports Events Entry System
 */

function enhancedCustomerDashboard() {
    return {
        // Core state
        dashboardData: {
            statistics: {
                available_tickets: { current: 0, trend: 'stable', change_24h: 0 },
                active_alerts: { current: 0, trend: 'stable' },
                high_demand: { current: 0, trend: 'stable' },
                watchlist: { current: 0 }
            },
            recent_events: [],
            recommendations: [],
            alerts: []
        },
        
        // UI state
        isLoading: false,
        isLiveData: false,
        autoRefresh: true,
        lastUpdate: new Date(),
        errorMessage: '',
        notificationCount: 0,
        hasNotifications: false,
        showNotifications: false,
        showSettings: false,
        recentTickets: [],
        
        // Settings state
        settings: {
            autoRefresh: true,
            refreshInterval: 30,
            notifications: true,
            emailAlerts: true,
            theme: 'light'
        },
        
        // Chart instances
        charts: {
            priceChart: null,
            demandChart: null,
            alertsChart: null
        },
        
        // Intervals
        intervals: {
            refresh: null,
            heartbeat: null
        },

        /**
         * Component initialization
         */
        init() {
            console.log('🎫 Enhanced Customer Dashboard initialized');
            this.loadSettings();
            this.setupEventListeners();
            this.loadInitialData();
            this.startHeartbeat();
            
            if (this.autoRefresh) {
                this.startAutoRefresh();
            }
        },

        /**
         * Load initial dashboard data
         */
        async loadInitialData() {
            this.isLoading = true;
            
            try {
                await this.refreshDashboard(false);
                console.log('✅ Initial dashboard data loaded');
            } catch (error) {
                console.error('❌ Failed to load initial data:', error);
                this.handleError('Failed to load dashboard data');
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Refresh dashboard data
         */
        async refreshDashboard(showLoading = true) {
            if (showLoading) {
                this.isLoading = true;
            }
            
            try {
                const response = await this.fetchDashboardData();
                
                if (response.ok) {
                    const data = await response.json();
                    this.updateDashboardData(data);
                    this.updateCharts();
                    this.lastUpdate = new Date();
                    this.clearError();
                    
                    // Show live indicator briefly
                    this.isLiveData = true;
                    setTimeout(() => this.isLiveData = false, 2000);
                    
                    console.log('🔄 Dashboard data refreshed');
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('❌ Dashboard refresh failed:', error);
                this.handleError('Failed to refresh dashboard data');
            } finally {
                if (showLoading) {
                    this.isLoading = false;
                }
            }
        },

        /**
         * Fetch dashboard data from API
         */
        async fetchDashboardData() {
            const apiUrl = document.querySelector('meta[name="dashboard-api"]')?.getAttribute('content');
            
            if (!apiUrl) {
                throw new Error('Dashboard API URL not found');
            }

            return fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
        },

        /**
         * Update dashboard data
         */
        updateDashboardData(data) {
            // Deep merge to preserve existing structure
            this.dashboardData = {
                ...this.dashboardData,
                ...data,
                statistics: {
                    ...this.dashboardData.statistics,
                    ...data.statistics
                }
            };

            // Update notification state
            this.updateNotifications();
        },

        /**
         * Update notifications
         */
        updateNotifications() {
            const alerts = this.dashboardData.alerts || [];
            this.notificationCount = alerts.filter(alert => !alert.read).length;
            this.hasNotifications = this.notificationCount > 0;
        },

        /**
         * Toggle auto-refresh
         */
        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            
            if (this.autoRefresh) {
                this.startAutoRefresh();
                console.log('🔄 Auto-refresh enabled');
            } else {
                this.stopAutoRefresh();
                console.log('⏸️ Auto-refresh disabled');
            }
        },

        /**
         * Start auto-refresh interval
         */
        startAutoRefresh() {
            this.stopAutoRefresh(); // Clear any existing interval
            
            this.intervals.refresh = setInterval(() => {
                if (!document.hidden) { // Only refresh when tab is visible
                    this.refreshDashboard(false);
                }
            }, 30000); // 30 seconds
        },

        /**
         * Stop auto-refresh interval
         */
        stopAutoRefresh() {
            if (this.intervals.refresh) {
                clearInterval(this.intervals.refresh);
                this.intervals.refresh = null;
            }
        },

        /**
         * Start heartbeat for connection monitoring
         */
        startHeartbeat() {
            this.intervals.heartbeat = setInterval(() => {
                // Simple heartbeat to detect connection issues
                fetch('/api/heartbeat', { 
                    method: 'HEAD',
                    cache: 'no-cache'
                }).catch(() => {
                    console.warn('⚠️ Connection heartbeat failed');
                });
            }, 60000); // 1 minute
        },

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Page visibility change
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && this.autoRefresh) {
                    this.refreshDashboard(false);
                }
            });

            // Before unload - cleanup intervals
            window.addEventListener('beforeunload', () => {
                this.cleanup();
            });

            // Error handling for uncaught errors
            window.addEventListener('error', (event) => {
                console.error('🚨 Uncaught error:', event.error);
            });
        },

        /**
         * Navigation helper
         */
        navigateTo(url) {
            if (url) {
                window.location.href = url;
            }
        },

        /**
         * Open notifications panel
         */
        openNotifications() {
            this.showNotifications = !this.showNotifications;
            console.log('🔔 Notifications panel:', this.showNotifications ? 'OPEN' : 'CLOSED');
        },

        /**
         * Mark notification as read
         */
        markNotificationRead(notificationId) {
            // Update notification read status
            if (this.dashboardData.alerts) {
                const notification = this.dashboardData.alerts.find(n => n.id === notificationId);
                if (notification) {
                    notification.read = true;
                    this.updateNotifications();
                }
            }
            console.log('🔔 Marked notification as read:', notificationId);
        },

        /**
         * Open settings modal
         */
        openSettings() {
            this.showSettings = true;
            console.log('⚙️ Settings modal opened');
        },

        /**
         * Save dashboard settings
         */
        saveSettings() {
            // Save settings to localStorage and/or API
            localStorage.setItem('dashboard_settings', JSON.stringify(this.settings));
            
            // Apply settings
            this.autoRefresh = this.settings.autoRefresh;
            
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
            
            this.showSettings = false;
            console.log('⚙️ Settings saved:', this.settings);
        },

        /**
         * Load dashboard settings
         */
        loadSettings() {
            const savedSettings = localStorage.getItem('dashboard_settings');
            if (savedSettings) {
                try {
                    this.settings = { ...this.settings, ...JSON.parse(savedSettings) };
                } catch (error) {
                    console.warn('⚠️ Failed to load settings:', error);
                }
            }
        },

        /**
         * Refresh recent tickets
         */
        async refreshRecentTickets() {
            try {
                const response = await this.fetchDashboardData();
                const data = await response.json();
                
                if (data.recent_events) {
                    this.recentTickets = data.recent_events;
                    this.dashboardData.recent_events = data.recent_events;
                }
                
                console.log('🎫 Recent tickets refreshed');
            } catch (error) {
                console.error('❌ Failed to refresh recent tickets:', error);
            }
        },

        /**
         * View ticket details
         */
        viewTicket(ticket) {
            if (ticket && ticket.id) {
                window.open(`/tickets/${ticket.id}`, '_blank');
            }
        },

        /**
         * Format price for display
         */
        formatPrice(price) {
            return HDTickets.Dashboard.utils.formatCurrency(price || 0);
        },

        /**
         * Format date for display
         */
        formatDate(date) {
            return HDTickets.Dashboard.utils.formatDateTime(date);
        },

        /**
         * Create event alert
         */
        createEventAlert(eventId) {
            if (eventId) {
                window.location.href = `/monitoring/create?event_id=${eventId}`;
            }
        },

        /**
         * Format numbers for display
         */
        formatNumber(number) {
            if (typeof number !== 'number') return '0';
            
            if (number >= 1000000) {
                return (number / 1000000).toFixed(1) + 'M';
            } else if (number >= 1000) {
                return (number / 1000).toFixed(1) + 'K';
            }
            
            return number.toLocaleString();
        },

        /**
         * Format change percentage
         */
        formatChange(change) {
            if (typeof change !== 'number') return '';
            
            const sign = change > 0 ? '+' : '';
            return `${sign}${change.toFixed(1)}%`;
        },

        /**
         * Get trend CSS class
         */
        getTrendClass(trend) {
            switch (trend) {
                case 'up': return 'trend-up';
                case 'down': return 'trend-down';
                case 'stable':
                default: return 'trend-stable';
            }
        },

        /**
         * Get data freshness class
         */
        getDataFreshnessClass() {
            const minutesOld = (new Date() - this.lastUpdate) / 60000;
            
            if (minutesOld < 2) return 'fresh';
            if (minutesOld < 10) return 'recent';
            return 'stale';
        },

        /**
         * Get data freshness text
         */
        getDataFreshnessText() {
            const minutesOld = Math.floor((new Date() - this.lastUpdate) / 60000);
            
            if (minutesOld < 1) return 'Live';
            if (minutesOld < 2) return 'Fresh';
            if (minutesOld < 10) return 'Recent';
            return 'Outdated';
        },

        /**
         * Get last update time
         */
        getLastUpdateTime() {
            return this.lastUpdate.toLocaleTimeString([], { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        /**
         * Update charts
         */
        updateCharts() {
            // Initialize or update Chart.js instances
            this.$nextTick(() => {
                this.initializePriceChart();
                this.initializeDemandChart();
                this.initializeAlertsChart();
            });
        },

        /**
         * Initialize price trend chart
         */
        initializePriceChart() {
            const canvas = document.getElementById('priceChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            if (this.charts.priceChart) {
                this.charts.priceChart.destroy();
            }

            this.charts.priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['6h ago', '5h ago', '4h ago', '3h ago', '2h ago', '1h ago', 'Now'],
                    datasets: [{
                        label: 'Average Price',
                        data: [120, 135, 118, 142, 128, 155, 149],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    }
                }
            });
        },

        /**
         * Initialize demand chart
         */
        initializeDemandChart() {
            const canvas = document.getElementById('demandChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            if (this.charts.demandChart) {
                this.charts.demandChart.destroy();
            }

            this.charts.demandChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['High', 'Medium', 'Low'],
                    datasets: [{
                        data: [35, 45, 20],
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        /**
         * Initialize alerts chart
         */
        initializeAlertsChart() {
            const canvas = document.getElementById('alertsChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            if (this.charts.alertsChart) {
                this.charts.alertsChart.destroy();
            }

            this.charts.alertsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        data: [12, 8, 15, 20, 18, 25, 14],
                        backgroundColor: '#8b5cf6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    }
                }
            });
        },

        /**
         * Handle errors
         */
        handleError(message) {
            this.errorMessage = message;
            console.error('❌ Dashboard Error:', message);
            
            // Clear error after 5 seconds
            setTimeout(() => {
                this.clearError();
            }, 5000);
        },

        /**
         * Clear error message
         */
        clearError() {
            this.errorMessage = '';
        },

        /**
         * Cleanup resources
         */
        cleanup() {
            console.log('🧹 Cleaning up dashboard resources');
            
            // Clear intervals
            Object.values(this.intervals).forEach(interval => {
                if (interval) clearInterval(interval);
            });

            // Destroy charts
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.destroy();
            });
        }
    };
}