/**
 * Enhanced Customer Dashboard JavaScript
 * Handles real-time updates, interactions, and dynamic UI components
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Alpine.js data for enhanced dashboard
    window.enhancedDashboard = function() {
        return {
            // State Management
            isLoading: true,
            isRefreshing: false,
            showToast: false,
            
            // Data Properties
            dashboardData: {
                statistics: {},
                recentTickets: [],
                trends: {}
            },
            personalizedRecommendations: [],
            
            // System Status
            systemStatus: {
                text: 'Live',
                class: 'status-live',
                dotClass: 'status-dot-live'
            },
            dataFreshness: {
                text: 'Fresh',
                class: 'data-fresh'
            },
            
            // Toast Properties
            toastType: 'info',
            toastTitle: '',
            toastMessage: '',
            
            // API Endpoints
            realtimeApiUrl: '',
            analyticsApiUrl: '',
            
            // Chart Instance
            trendsChart: null,
            
            // Update Intervals
            realtimeInterval: null,
            analyticsInterval: null,
            
            // Cached Data
            statistics: {},
            recentTickets: [],
            lastUpdated: new Date().toISOString(),

            /**
             * Initialize the dashboard
             */
            async init() {
                try {
                    console.log('🚀 Initializing Enhanced Customer Dashboard');
                    
                    // Get API URLs from meta tags
                    this.realtimeApiUrl = document.querySelector('meta[name="dashboard-api"]')?.getAttribute('content');
                    this.analyticsApiUrl = document.querySelector('meta[name="analytics-api"]')?.getAttribute('content');
                    
                    // Load initial data
                    await this.loadInitialData();
                    
                    // Initialize chart
                    this.initializeChart();
                    
                    // Start real-time updates
                    this.startRealtimeUpdates();
                    
                    // Setup event listeners
                    this.setupEventListeners();
                    
                    // Hide loading overlay
                    setTimeout(() => {
                        this.isLoading = false;
                        this.showSuccessToast('Dashboard Loaded', 'Welcome to your enhanced dashboard experience');
                    }, 1000);
                    
                    console.log('✅ Dashboard initialization complete');
                    
                } catch (error) {
                    console.error('❌ Dashboard initialization failed:', error);
                    this.showErrorToast('Initialization Error', 'Failed to load dashboard. Please refresh the page.');
                    this.isLoading = false;
                }
            },

            /**
             * Load initial dashboard data
             */
            async loadInitialData() {
                try {
                    // Load analytics data
                    if (this.analyticsApiUrl) {
                        const analyticsResponse = await fetch(this.analyticsApiUrl);
                        if (analyticsResponse.ok) {
                            const analyticsData = await analyticsResponse.json();
                            this.dashboardData = { ...this.dashboardData, ...analyticsData };
                            this.statistics = analyticsData.statistics || {};
                            this.recentTickets = analyticsData.recent_tickets || [];
                        }
                    }
                    
                    // Load real-time data
                    if (this.realtimeApiUrl) {
                        await this.updateRealtimeData();
                    }
                    
                    // Load recommendations
                    await this.loadRecommendations();
                    
                } catch (error) {
                    console.error('Failed to load initial data:', error);
                }
            },

            /**
             * Update real-time data
             */
            async updateRealtimeData() {
                try {
                    if (!this.realtimeApiUrl) return;
                    
                    const response = await fetch(this.realtimeApiUrl);
                    if (response.ok) {
                        const data = await response.json();
                        
                        // Update system status
                        this.updateSystemStatus(data);
                        
                        // Update data freshness
                        this.updateDataFreshness(data.timestamp);
                        
                        // Merge real-time data
                        if (data.statistics) {
                            this.statistics = { ...this.statistics, ...data.statistics };
                        }
                        
                        if (data.recent_tickets) {
                            this.recentTickets = data.recent_tickets;
                        }
                        
                        this.lastUpdated = new Date().toISOString();
                        
                    } else {
                        this.updateSystemStatus({ system_status: { status: 'degraded' } });
                    }
                } catch (error) {
                    console.error('Failed to update real-time data:', error);
                    this.updateSystemStatus({ system_status: { status: 'error' } });
                }
            },

            /**
             * Load personalized recommendations
             */
            async loadRecommendations() {
                try {
                    const response = await fetch('/api/dashboard/enhanced/recommendations');
                    if (response.ok) {
                        const data = await response.json();
                        this.personalizedRecommendations = data.recommendations || [];
                    }
                } catch (error) {
                    console.error('Failed to load recommendations:', error);
                }
            },

            /**
             * Update system status indicator
             */
            updateSystemStatus(data) {
                const status = data.system_status?.status || 'operational';
                
                switch (status) {
                    case 'operational':
                        this.systemStatus = {
                            text: 'Live',
                            class: 'status-live',
                            dotClass: 'status-dot-live'
                        };
                        break;
                    case 'degraded':
                        this.systemStatus = {
                            text: 'Slow',
                            class: 'warning',
                            dotClass: 'warning'
                        };
                        break;
                    case 'error':
                        this.systemStatus = {
                            text: 'Error',
                            class: 'error',
                            dotClass: 'error'
                        };
                        break;
                }
            },

            /**
             * Update data freshness indicator
             */
            updateDataFreshness(timestamp) {
                const now = new Date();
                const dataTime = new Date(timestamp);
                const diffMinutes = Math.floor((now - dataTime) / 1000 / 60);
                
                if (diffMinutes < 1) {
                    this.dataFreshness = { text: 'Live', class: 'data-fresh' };
                } else if (diffMinutes < 5) {
                    this.dataFreshness = { text: `${diffMinutes}m ago`, class: 'data-fresh' };
                } else if (diffMinutes < 15) {
                    this.dataFreshness = { text: `${diffMinutes}m ago`, class: 'data-stale' };
                } else {
                    this.dataFreshness = { text: 'Stale', class: 'data-stale' };
                }
            },

            /**
             * Start real-time data updates
             */
            startRealtimeUpdates() {
                // Real-time updates every 30 seconds
                if (this.realtimeApiUrl) {
                    this.realtimeInterval = setInterval(() => {
                        this.updateRealtimeData();
                    }, 30000);
                }
                
                // Analytics updates every 5 minutes
                if (this.analyticsApiUrl) {
                    this.analyticsInterval = setInterval(() => {
                        this.refreshAnalytics();
                    }, 300000);
                }
            },

            /**
             * Refresh analytics data
             */
            async refreshAnalytics() {
                try {
                    if (!this.analyticsApiUrl) return;
                    
                    const response = await fetch(this.analyticsApiUrl);
                    if (response.ok) {
                        const data = await response.json();
                        this.statistics = data.statistics || {};
                        
                        // Update chart if trends data changed
                        if (data.trends && this.trendsChart) {
                            this.updateChart(data.trends);
                        }
                    }
                } catch (error) {
                    console.error('Failed to refresh analytics:', error);
                }
            },

            /**
             * Manual refresh action
             */
            async refreshData() {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                
                try {
                    await Promise.all([
                        this.updateRealtimeData(),
                        this.refreshAnalytics(),
                        this.loadRecommendations()
                    ]);
                    
                    this.showSuccessToast('Data Refreshed', 'Dashboard data has been updated');
                } catch (error) {
                    this.showErrorToast('Refresh Failed', 'Unable to refresh dashboard data');
                } finally {
                    this.isRefreshing = false;
                }
            },

            /**
             * Refresh recommendations
             */
            async refreshRecommendations() {
                try {
                    await this.loadRecommendations();
                    this.showSuccessToast('Recommendations Updated', 'Your personalized recommendations have been refreshed');
                } catch (error) {
                    this.showErrorToast('Update Failed', 'Unable to refresh recommendations');
                }
            },

            /**
             * Initialize trends chart
             */
            initializeChart() {
                const canvas = document.getElementById('trendsChart');
                if (!canvas) return;
                
                const ctx = canvas.getContext('2d');
                
                const trends = this.dashboardData.trends || {};
                const dates = trends.dates || [];
                const tickets = trends.tickets || [];
                const demand = trends.demand || [];
                
                this.trendsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates.map(date => {
                            return new Date(date).toLocaleDateString('en-US', { 
                                month: 'short', 
                                day: 'numeric' 
                            });
                        }),
                        datasets: [
                            {
                                label: 'Available Tickets',
                                data: tickets,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'High Demand',
                                data: demand,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }
                        ]
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
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgba(255, 255, 255, 0.2)',
                                borderWidth: 1,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(107, 114, 128, 0.1)'
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            }
                        }
                    }
                });
            },

            /**
             * Update chart with new data
             */
            updateChart(trendsData) {
                if (!this.trendsChart) return;
                
                const dates = trendsData.dates || [];
                const tickets = trendsData.tickets || [];
                const demand = trendsData.demand || [];
                
                this.trendsChart.data.labels = dates.map(date => {
                    return new Date(date).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric' 
                    });
                });
                
                this.trendsChart.data.datasets[0].data = tickets;
                this.trendsChart.data.datasets[1].data = demand;
                
                this.trendsChart.update('none');
            },

            /**
             * Setup event listeners
             */
            setupEventListeners() {
                // Handle page visibility changes
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.pauseUpdates();
                    } else {
                        this.resumeUpdates();
                    }
                });
                
                // Handle online/offline status
                window.addEventListener('online', () => {
                    this.showSuccessToast('Connection Restored', 'Dashboard is back online');
                    this.resumeUpdates();
                });
                
                window.addEventListener('offline', () => {
                    this.showErrorToast('Connection Lost', 'Dashboard is offline');
                    this.pauseUpdates();
                });
            },

            /**
             * Pause real-time updates
             */
            pauseUpdates() {
                if (this.realtimeInterval) {
                    clearInterval(this.realtimeInterval);
                    this.realtimeInterval = null;
                }
                
                if (this.analyticsInterval) {
                    clearInterval(this.analyticsInterval);
                    this.analyticsInterval = null;
                }
            },

            /**
             * Resume real-time updates
             */
            resumeUpdates() {
                if (!this.realtimeInterval && this.realtimeApiUrl) {
                    this.realtimeInterval = setInterval(() => {
                        this.updateRealtimeData();
                    }, 30000);
                }
                
                if (!this.analyticsInterval && this.analyticsApiUrl) {
                    this.analyticsInterval = setInterval(() => {
                        this.refreshAnalytics();
                    }, 300000);
                }
            },

            /**
             * Navigation helpers
             */
            navigateTo(url) {
                window.location.href = url;
            },

            viewTicket(ticketId) {
                window.open(`/tickets/${ticketId}`, '_blank');
            },

            createAlert(ticket) {
                const params = new URLSearchParams({
                    venue: ticket.venue,
                    max_price: ticket.price,
                    event_type: ticket.title
                });
                
                window.location.href = `/tickets/alerts/create?${params.toString()}`;
            },

            /**
             * Formatting helpers
             */
            formatNumber(num) {
                if (num >= 1000000) {
                    return (num / 1000000).toFixed(1) + 'M';
                } else if (num >= 1000) {
                    return (num / 1000).toFixed(1) + 'K';
                } else {
                    return num.toLocaleString();
                }
            },

            formatChange(change) {
                const sign = change > 0 ? '+' : '';
                return `${sign}${change.toFixed(1)}%`;
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount);
            },

            formatDate(date) {
                return new Date(date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            },

            /**
             * UI state helpers
             */
            getChangeClass(change) {
                if (change > 0) return 'positive';
                if (change < 0) return 'negative';
                return 'neutral';
            },

            getTrendClass(trend) {
                return `trend-${trend}`;
            },

            getDemandClass(demand) {
                return `demand-${demand}`;
            },

            getDemandText(demand) {
                const textMap = {
                    high: 'High',
                    medium: 'Med',
                    low: 'Low'
                };
                return textMap[demand] || 'Unknown';
            },

            getUrgencyClass(urgency) {
                return `urgency-${urgency}`;
            },

            getUrgencyText(urgency) {
                const textMap = {
                    high: 'High',
                    medium: 'Medium',
                    low: 'Low'
                };
                return textMap[urgency] || 'Low';
            },

            /**
             * Toast notification helpers
             */
            showToast(type, title, message) {
                this.toastType = type;
                this.toastTitle = title;
                this.toastMessage = message;
                this.showToast = true;
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    this.hideToast();
                }, 5000);
            },

            showSuccessToast(title, message) {
                this.showToast('success', title, message);
            },

            showErrorToast(title, message) {
                this.showToast('error', title, message);
            },

            showInfoToast(title, message) {
                this.showToast('info', title, message);
            },

            hideToast() {
                this.showToast = false;
            },

            /**
             * Cleanup on destroy
             */
            destroy() {
                this.pauseUpdates();
                
                if (this.trendsChart) {
                    this.trendsChart.destroy();
                }
            }
        };
    };
});

/**
 * Utility functions for dashboard components
 */
window.DashboardUtils = {
    /**
     * Animate number changes
     */
    animateNumber(element, start, end, duration = 1000) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            
            element.textContent = Math.floor(current).toLocaleString();
        }, 16);
    },

    /**
     * Format time ago
     */
    timeAgo(date) {
        const now = new Date();
        const past = new Date(date);
        const diffMs = now - past;
        
        const minutes = Math.floor(diffMs / 60000);
        const hours = Math.floor(diffMs / 3600000);
        const days = Math.floor(diffMs / 86400000);
        
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        return `${days}d ago`;
    },

    /**
     * Debounce function calls
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Generate color from string
     */
    stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        const hue = hash % 360;
        return `hsl(${hue}, 70%, 50%)`;
    }
};

/**
 * Initialize dashboard when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎛️ Enhanced Customer Dashboard JavaScript loaded');
});

/**
 * Handle cleanup on page unload
 */
window.addEventListener('beforeunload', function() {
    // Cleanup intervals and resources
    if (window.dashboardInstance?.destroy) {
        window.dashboardInstance.destroy();
    }
});
