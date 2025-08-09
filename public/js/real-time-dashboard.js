/**
 * Real-Time Dashboard with WebSocket Integration
 * Enhanced HD Tickets System - Advanced Dashboard Features
 */

class RealTimeDashboard {
    constructor(options = {}) {
        this.options = {
            wsUrl: options.wsUrl || `ws://${window.location.host}/ws`,
            updateInterval: options.updateInterval || 5000,
            chartColors: options.chartColors || {
                primary: '#4f46e5',
                secondary: '#10b981',
                accent: '#f59e0b',
                danger: '#ef4444',
                info: '#3b82f6'
            },
            ...options
        };

        this.ws = null;
        this.charts = new Map();
        this.widgets = new Map();
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;

        this.init();
    }

    /**
     * Initialize dashboard
     */
    async init() {
        try {
            await this.setupWebSocket();
            this.initializeWidgets();
            this.initializeCharts();
            this.bindEventHandlers();
            this.startPerformanceMonitoring();

            console.log('Real-time dashboard initialized successfully');
        } catch (error) {
            console.error('Failed to initialize dashboard:', error);
            this.showErrorNotification('Failed to initialize real-time dashboard');
        }
    }

    /**
     * Setup WebSocket connection
     */
    async setupWebSocket() {
        return new Promise((resolve, reject) => {
            try {
                this.ws = new WebSocket(this.options.wsUrl);
                
                this.ws.onopen = () => {
                    console.log('WebSocket connection established');
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this.updateConnectionStatus('connected');
                    this.subscribeToChannels();
                    resolve();
                };

                this.ws.onmessage = (event) => {
                    this.handleWebSocketMessage(JSON.parse(event.data));
                };

                this.ws.onclose = () => {
                    console.log('WebSocket connection closed');
                    this.isConnected = false;
                    this.updateConnectionStatus('disconnected');
                    this.scheduleReconnection();
                };

                this.ws.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    this.updateConnectionStatus('error');
                    reject(error);
                };

            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(data) {
        try {
            switch (data.type) {
                case 'ticket_update':
                    this.handleTicketUpdate(data.payload);
                    break;
                case 'price_change':
                    this.handlePriceChange(data.payload);
                    break;
                case 'analytics_update':
                    this.handleAnalyticsUpdate(data.payload);
                    break;
                case 'notification':
                    this.handleNotification(data.payload);
                    break;
                case 'system_status':
                    this.handleSystemStatus(data.payload);
                    break;
                default:
                    console.log('Unknown message type:', data.type);
            }
        } catch (error) {
            console.error('Error handling WebSocket message:', error);
        }
    }

    /**
     * Initialize dashboard widgets
     */
    initializeWidgets() {
        // Circular Progress Widget
        this.widgets.set('circular-progress', new CircularProgressWidget({
            container: '#circular-progress-widget',
            radius: 50,
            strokeWidth: 8,
            colors: this.options.chartColors
        }));

        // Live Metrics Widget
        this.widgets.set('live-metrics', new LiveMetricsWidget({
            container: '#live-metrics-widget',
            updateInterval: 2000
        }));

        // Heat Map Calendar Widget
        this.widgets.set('heat-map', new HeatMapCalendarWidget({
            container: '#heat-map-widget',
            colors: [this.options.chartColors.info, this.options.chartColors.primary]
        }));

        // Interactive Seat Map Widget
        this.widgets.set('seat-map', new InteractiveSeatMapWidget({
            container: '#seat-map-widget',
            venue: 'default'
        }));

        // Price Comparison Widget
        this.widgets.set('price-comparison', new PriceComparisonWidget({
            container: '#price-comparison-widget',
            colors: this.options.chartColors
        }));

        // Alert Management Widget
        this.widgets.set('alert-management', new AlertManagementWidget({
            container: '#alert-management-widget'
        }));
    }

    /**
     * Initialize charts
     */
    initializeCharts() {
        // Real-time ticket flow chart
        this.charts.set('ticket-flow', this.createTicketFlowChart());
        
        // Price trend chart
        this.charts.set('price-trends', this.createPriceTrendChart());
        
        // User activity chart
        this.charts.set('user-activity', this.createUserActivityChart());
        
        // Revenue analytics chart
        this.charts.set('revenue-analytics', this.createRevenueChart());
    }

    /**
     * Create ticket flow chart
     */
    createTicketFlowChart() {
        const ctx = document.getElementById('ticket-flow-chart');
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Available Tickets',
                    data: [],
                    borderColor: this.options.chartColors.primary,
                    backgroundColor: this.options.chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Sold Tickets',
                    data: [],
                    borderColor: this.options.chartColors.secondary,
                    backgroundColor: this.options.chartColors.secondary + '20',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            displayFormats: {
                                minute: 'HH:mm',
                                hour: 'HH:mm'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    /**
     * Create price trend chart
     */
    createPriceTrendChart() {
        const ctx = document.getElementById('price-trends-chart');
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'candlestick',
            data: {
                datasets: [{
                    label: 'Ticket Prices',
                    data: [],
                    borderColor: this.options.chartColors.accent,
                    backgroundColor: this.options.chartColors.accent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            displayFormats: {
                                hour: 'MMM DD, HH:mm'
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const point = context.raw;
                                return [
                                    `Open: $${point.o}`,
                                    `High: $${point.h}`,
                                    `Low: $${point.l}`,
                                    `Close: $${point.c}`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Create user activity chart
     */
    createUserActivityChart() {
        const ctx = document.getElementById('user-activity-chart');
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active Users', 'Inactive Users', 'New Users'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: [
                        this.options.chartColors.secondary,
                        this.options.chartColors.info,
                        this.options.chartColors.accent
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });
    }

    /**
     * Create revenue chart
     */
    createRevenueChart() {
        const ctx = document.getElementById('revenue-chart');
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [],
                    backgroundColor: this.options.chartColors.secondary,
                    borderColor: this.options.chartColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Handle ticket updates
     */
    handleTicketUpdate(data) {
        // Update ticket flow chart
        const flowChart = this.charts.get('ticket-flow');
        if (flowChart) {
            this.updateChartData(flowChart, data.timestamp, {
                available: data.available_tickets,
                sold: data.sold_tickets
            });
        }

        // Update widgets
        this.widgets.get('live-metrics')?.updateTicketData(data);
        this.widgets.get('circular-progress')?.updateProgress('tickets', data.completion_rate);

        // Trigger visual feedback
        this.triggerUpdateAnimation('.ticket-stats');
    }

    /**
     * Handle price changes
     */
    handlePriceChange(data) {
        // Update price trend chart
        const trendChart = this.charts.get('price-trends');
        if (trendChart) {
            this.updatePriceTrendChart(trendChart, data);
        }

        // Update price comparison widget
        this.widgets.get('price-comparison')?.updatePrice(data);

        // Show price alert if significant change
        if (Math.abs(data.change_percent) > 10) {
            this.showPriceAlert(data);
        }

        this.triggerUpdateAnimation('.price-stats');
    }

    /**
     * Handle analytics updates
     */
    handleAnalyticsUpdate(data) {
        // Update user activity chart
        const activityChart = this.charts.get('user-activity');
        if (activityChart) {
            activityChart.data.datasets[0].data = [
                data.active_users,
                data.inactive_users,
                data.new_users
            ];
            activityChart.update('none');
        }

        // Update heat map
        this.widgets.get('heat-map')?.updateData(data.event_density);

        // Update metrics widgets
        this.widgets.get('live-metrics')?.updateAnalytics(data);
    }

    /**
     * Handle notifications
     */
    handleNotification(notification) {
        this.showNotification(notification.title, notification.message, notification.type);
        
        // Update alert management widget
        this.widgets.get('alert-management')?.addAlert(notification);
    }

    /**
     * Handle system status updates
     */
    handleSystemStatus(status) {
        this.updateSystemStatus(status);
        
        if (status.health_score < 80) {
            this.showWarningNotification('System performance degraded');
        }
    }

    /**
     * Update chart data with new timestamp
     */
    updateChartData(chart, timestamp, data) {
        const time = new Date(timestamp);
        
        // Add new data points
        chart.data.labels.push(time);
        chart.data.datasets[0].data.push(data.available);
        chart.data.datasets[1].data.push(data.sold);

        // Keep only last 50 data points
        if (chart.data.labels.length > 50) {
            chart.data.labels.shift();
            chart.data.datasets.forEach(dataset => dataset.data.shift());
        }

        chart.update('none');
    }

    /**
     * Update price trend chart
     */
    updatePriceTrendChart(chart, data) {
        const candlestickData = {
            x: new Date(data.timestamp),
            o: data.open_price,
            h: data.high_price,
            l: data.low_price,
            c: data.current_price
        };

        chart.data.datasets[0].data.push(candlestickData);

        // Keep only last 100 data points
        if (chart.data.datasets[0].data.length > 100) {
            chart.data.datasets[0].data.shift();
        }

        chart.update('none');
    }

    /**
     * Subscribe to WebSocket channels
     */
    subscribeToChannels() {
        const channels = [
            'ticket-updates',
            'price-changes',
            'analytics',
            'notifications',
            'system-status'
        ];

        channels.forEach(channel => {
            this.ws.send(JSON.stringify({
                type: 'subscribe',
                channel: channel
            }));
        });
    }

    /**
     * Bind event handlers
     */
    bindEventHandlers() {
        // Refresh dashboard button
        document.getElementById('refresh-dashboard')?.addEventListener('click', () => {
            this.refreshDashboard();
        });

        // Export data button
        document.getElementById('export-data')?.addEventListener('click', () => {
            this.exportDashboardData();
        });

        // Toggle real-time updates
        document.getElementById('toggle-realtime')?.addEventListener('change', (e) => {
            this.toggleRealTimeUpdates(e.target.checked);
        });

        // Fullscreen toggle
        document.getElementById('fullscreen-toggle')?.addEventListener('click', () => {
            this.toggleFullscreen();
        });

        // Dark mode toggle
        document.getElementById('dark-mode-toggle')?.addEventListener('click', () => {
            this.toggleDarkMode();
        });

        // Window visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseUpdates();
            } else {
                this.resumeUpdates();
            }
        });
    }

    /**
     * Start performance monitoring
     */
    startPerformanceMonitoring() {
        // Monitor frame rate
        this.frameRateMonitor = new FrameRateMonitor();
        
        // Monitor memory usage
        setInterval(() => {
            if (performance.memory) {
                const memoryInfo = {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                };
                this.updateMemoryStats(memoryInfo);
            }
        }, 10000);

        // Monitor WebSocket connection quality
        setInterval(() => {
            this.monitorConnectionQuality();
        }, 5000);
    }

    /**
     * Show notifications
     */
    showNotification(title, message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-header">
                <h4>${title}</h4>
                <button class="notification-close">&times;</button>
            </div>
            <div class="notification-body">${message}</div>
        `;

        // Add to notification container
        const container = document.getElementById('notification-container');
        if (container) {
            container.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);

            // Close button handler
            notification.querySelector('.notification-close').addEventListener('click', () => {
                notification.remove();
            });
        }

        // Trigger browser notification if permitted
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/images/logo-icon.png'
            });
        }
    }

    /**
     * Show price alert
     */
    showPriceAlert(data) {
        const message = `Price ${data.change_percent > 0 ? 'increased' : 'dropped'} by ${Math.abs(data.change_percent).toFixed(1)}%`;
        this.showNotification('Price Alert', message, data.change_percent > 0 ? 'warning' : 'success');
    }

    /**
     * Show error notification
     */
    showErrorNotification(message) {
        this.showNotification('Error', message, 'error');
    }

    /**
     * Show warning notification
     */
    showWarningNotification(message) {
        this.showNotification('Warning', message, 'warning');
    }

    /**
     * Update connection status
     */
    updateConnectionStatus(status) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            statusElement.className = `connection-status connection-${status}`;
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    }

    /**
     * Schedule reconnection
     */
    scheduleReconnection() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            const delay = Math.pow(2, this.reconnectAttempts) * 1000; // Exponential backoff
            setTimeout(() => {
                console.log(`Attempting to reconnect... (${this.reconnectAttempts + 1}/${this.maxReconnectAttempts})`);
                this.reconnectAttempts++;
                this.setupWebSocket();
            }, delay);
        } else {
            this.showErrorNotification('Failed to establish WebSocket connection. Please refresh the page.');
        }
    }

    /**
     * Trigger update animation
     */
    triggerUpdateAnimation(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            element.classList.add('data-updated');
            setTimeout(() => {
                element.classList.remove('data-updated');
            }, 1000);
        });
    }

    /**
     * Refresh dashboard
     */
    async refreshDashboard() {
        try {
            const response = await fetch('/api/dashboard/refresh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateDashboardData(data);
                this.showNotification('Success', 'Dashboard refreshed successfully', 'success');
            } else {
                throw new Error('Failed to refresh dashboard');
            }
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            this.showErrorNotification('Failed to refresh dashboard data');
        }
    }

    /**
     * Export dashboard data
     */
    async exportDashboardData() {
        try {
            const data = {
                charts: this.getChartData(),
                widgets: this.getWidgetData(),
                timestamp: new Date().toISOString()
            };

            const blob = new Blob([JSON.stringify(data, null, 2)], {
                type: 'application/json'
            });

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard-export-${new Date().toISOString().split('T')[0]}.json`;
            a.click();

            URL.revokeObjectURL(url);
            this.showNotification('Success', 'Dashboard data exported successfully', 'success');
        } catch (error) {
            console.error('Error exporting dashboard data:', error);
            this.showErrorNotification('Failed to export dashboard data');
        }
    }

    /**
     * Toggle real-time updates
     */
    toggleRealTimeUpdates(enabled) {
        if (enabled) {
            this.resumeUpdates();
            this.showNotification('Info', 'Real-time updates enabled', 'info');
        } else {
            this.pauseUpdates();
            this.showNotification('Info', 'Real-time updates paused', 'info');
        }
    }

    /**
     * Toggle fullscreen mode
     */
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    /**
     * Toggle dark mode
     */
    toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        
        // Update charts for dark mode
        this.charts.forEach(chart => {
            this.updateChartTheme(chart);
        });

        // Save preference
        localStorage.setItem('dashboard-dark-mode', document.body.classList.contains('dark-mode'));
    }

    /**
     * Pause updates when tab is hidden
     */
    pauseUpdates() {
        this.updatesPaused = true;
        console.log('Dashboard updates paused');
    }

    /**
     * Resume updates when tab is visible
     */
    resumeUpdates() {
        this.updatesPaused = false;
        console.log('Dashboard updates resumed');
        this.refreshDashboard();
    }

    /**
     * Get chart data for export
     */
    getChartData() {
        const chartData = {};
        this.charts.forEach((chart, key) => {
            if (chart) {
                chartData[key] = {
                    labels: chart.data.labels,
                    datasets: chart.data.datasets
                };
            }
        });
        return chartData;
    }

    /**
     * Get widget data for export
     */
    getWidgetData() {
        const widgetData = {};
        this.widgets.forEach((widget, key) => {
            if (widget && widget.getData) {
                widgetData[key] = widget.getData();
            }
        });
        return widgetData;
    }

    /**
     * Update chart theme for dark mode
     */
    updateChartTheme(chart) {
        const isDark = document.body.classList.contains('dark-mode');
        
        if (chart.options.scales) {
            Object.values(chart.options.scales).forEach(scale => {
                if (scale.ticks) {
                    scale.ticks.color = isDark ? '#e5e7eb' : '#374151';
                }
                if (scale.grid) {
                    scale.grid.color = isDark ? '#374151' : '#e5e7eb';
                }
            });
        }

        if (chart.options.plugins?.legend?.labels) {
            chart.options.plugins.legend.labels.color = isDark ? '#e5e7eb' : '#374151';
        }

        chart.update('none');
    }

    /**
     * Monitor connection quality
     */
    monitorConnectionQuality() {
        if (this.isConnected && this.ws) {
            // Send ping message
            this.ws.send(JSON.stringify({
                type: 'ping',
                timestamp: Date.now()
            }));
        }
    }

    /**
     * Update memory statistics
     */
    updateMemoryStats(memoryInfo) {
        const memoryElement = document.getElementById('memory-usage');
        if (memoryElement) {
            const usedMB = Math.round(memoryInfo.used / 1024 / 1024);
            const totalMB = Math.round(memoryInfo.total / 1024 / 1024);
            memoryElement.textContent = `${usedMB}MB / ${totalMB}MB`;
        }
    }

    /**
     * Update system status
     */
    updateSystemStatus(status) {
        const statusElements = {
            cpu: document.getElementById('cpu-usage'),
            memory: document.getElementById('system-memory'),
            health: document.getElementById('health-score')
        };

        if (statusElements.cpu) {
            statusElements.cpu.textContent = `${status.cpu_usage}%`;
        }

        if (statusElements.memory) {
            statusElements.memory.textContent = `${status.memory_usage}%`;
        }

        if (statusElements.health) {
            statusElements.health.textContent = `${status.health_score}%`;
            statusElements.health.className = `health-score health-${this.getHealthClass(status.health_score)}`;
        }
    }

    /**
     * Get health status class
     */
    getHealthClass(score) {
        if (score >= 90) return 'excellent';
        if (score >= 70) return 'good';
        if (score >= 50) return 'warning';
        return 'critical';
    }

    /**
     * Update dashboard data
     */
    updateDashboardData(data) {
        // Update all charts and widgets with fresh data
        if (data.analytics) {
            this.handleAnalyticsUpdate(data.analytics);
        }

        if (data.tickets) {
            this.handleTicketUpdate(data.tickets);
        }

        if (data.system_status) {
            this.handleSystemStatus(data.system_status);
        }
    }

    /**
     * Cleanup and destroy dashboard
     */
    destroy() {
        // Close WebSocket connection
        if (this.ws) {
            this.ws.close();
        }

        // Destroy all charts
        this.charts.forEach(chart => {
            if (chart) chart.destroy();
        });

        // Destroy all widgets
        this.widgets.forEach(widget => {
            if (widget && widget.destroy) widget.destroy();
        });

        // Clear intervals
        if (this.frameRateMonitor) {
            this.frameRateMonitor.stop();
        }

        console.log('Real-time dashboard destroyed');
    }
}

/**
 * Frame Rate Monitor
 */
class FrameRateMonitor {
    constructor() {
        this.fps = 0;
        this.frame = 0;
        this.lastTime = performance.now();
        this.running = true;
        
        this.start();
    }

    start() {
        const loop = (time) => {
            if (!this.running) return;

            this.frame++;
            if (time - this.lastTime >= 1000) {
                this.fps = Math.round((this.frame * 1000) / (time - this.lastTime));
                this.updateFPSDisplay();
                this.lastTime = time;
                this.frame = 0;
            }

            requestAnimationFrame(loop);
        };
        
        requestAnimationFrame(loop);
    }

    updateFPSDisplay() {
        const fpsElement = document.getElementById('fps-counter');
        if (fpsElement) {
            fpsElement.textContent = `${this.fps} FPS`;
        }
    }

    stop() {
        this.running = false;
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardInstance = new RealTimeDashboard({
        wsUrl: `wss://${window.location.host}/ws`,
        updateInterval: 3000
    });

    // Request notification permission
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

// Handle page unload
window.addEventListener('beforeunload', () => {
    if (window.dashboardInstance) {
        window.dashboardInstance.destroy();
    }
});

// Export for use in other modules
window.RealTimeDashboard = RealTimeDashboard;
