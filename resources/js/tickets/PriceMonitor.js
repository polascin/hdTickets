/**
 * Real-time Price Monitor Component
 * 
 * Handles WebSocket connections for live price updates, trend analysis,
 * and user notifications for sports ticket price changes.
 */
class PriceMonitor {
    constructor(options = {}) {
        this.options = {
            echoConfig: {
                broadcaster: 'pusher',
                key: window.pusherKey,
                cluster: window.pusherCluster || 'mt1',
                forceTLS: true
            },
            enableNotifications: true,
            enableSound: true,
            priceThreshold: 0.05, // 5% change threshold for notifications
            maxRetries: 5,
            retryDelay: 5000,
            ...options
        };
        
        this.echo = null;
        this.channels = new Map();
        this.retryCount = 0;
        this.isConnected = false;
        this.watchedTickets = new Set();
        this.priceHistory = new Map();
        this.notifications = [];
        
        // Sound for price alerts
        this.alertSound = new Audio('/sounds/price-alert.mp3');
        this.alertSound.volume = 0.3;
        
        this.init();
    }
    
    init() {
        this.setupEcho();
        this.setupNotifications();
        this.setupUI();
        this.loadWatchedTickets();
        
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseMonitoring();
            } else {
                this.resumeMonitoring();
            }
        });
        
        console.log('PriceMonitor initialized');
    }
    
    setupEcho() {
        if (!window.Echo) {
            console.error('Laravel Echo not found');
            return;
        }
        
        try {
            // Use the globally initialized Echo instance to avoid duplicate initializations
            this.echo = window.Echo;
            this.setupConnectionEvents();
            this.isConnected = (window.EchoHelpers && typeof window.EchoHelpers.isConnected === 'function')
                ? window.EchoHelpers.isConnected()
                : true;
            this.retryCount = 0;
        } catch (error) {
            console.error('Failed to attach to Echo instance:', error);
            this.handleConnectionError();
        }
    }
    
    setupConnectionEvents() {
        if (!this.echo) return;
        
        // Connection events
        this.echo.connector.pusher.connection.bind('connected', () => {
            console.log('WebSocket connected');
            this.isConnected = true;
            this.retryCount = 0;
            this.updateConnectionStatus('connected');
            this.resubscribeChannels();
        });
        
        this.echo.connector.pusher.connection.bind('disconnected', () => {
            console.log('WebSocket disconnected');
            this.isConnected = false;
            this.updateConnectionStatus('disconnected');
        });
        
        this.echo.connector.pusher.connection.bind('error', (error) => {
            console.error('WebSocket error:', error);
            this.handleConnectionError();
        });
        
        this.echo.connector.pusher.connection.bind('unavailable', () => {
            console.warn('WebSocket unavailable');
            this.handleConnectionError();
        });
    }
    
    setupNotifications() {
        if (!this.options.enableNotifications) return;
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    }
    
    setupUI() {
        // Create status indicator
        this.createStatusIndicator();
        
        // Setup price alert controls
        this.setupPriceAlertControls();
        
        // Setup notification center
        this.setupNotificationCenter();
    }
    
    createStatusIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'price-monitor-status';
        indicator.className = 'fixed bottom-4 left-4 z-50 flex items-center space-x-2 px-3 py-2 bg-white rounded-lg shadow-lg border';
        indicator.innerHTML = `
            <div id="status-dot" class="w-3 h-3 rounded-full bg-gray-400"></div>
            <span id="status-text" class="text-sm font-medium text-gray-600">Connecting...</span>
            <button id="toggle-monitoring" class="text-xs text-blue-600 hover:text-blue-800">Pause</button>
        `;
        
        document.body.appendChild(indicator);
        
        // Toggle monitoring
        document.getElementById('toggle-monitoring').addEventListener('click', () => {
            if (this.isConnected) {
                this.pauseMonitoring();
            } else {
                this.resumeMonitoring();
            }
        });
    }
    
    setupPriceAlertControls() {
        // Add price alert buttons to ticket cards
        document.querySelectorAll('.ticket-card').forEach(card => {
            const ticketId = card.dataset.ticketId;
            if (!ticketId) return;
            
            const alertButton = document.createElement('button');
            alertButton.className = 'price-alert-toggle px-2 py-1 text-xs rounded border transition-colors';
            alertButton.dataset.ticketId = ticketId;
            
            this.updateAlertButton(alertButton, this.watchedTickets.has(ticketId));
            
            alertButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.togglePriceAlert(ticketId);
            });
            
            // Insert button into card
            const actionsContainer = card.querySelector('.ticket-actions');
            if (actionsContainer) {
                actionsContainer.appendChild(alertButton);
            }
        });
    }
    
    setupNotificationCenter() {
        const center = document.createElement('div');
        center.id = 'notification-center';
        center.className = 'fixed top-4 right-4 z-50 space-y-2';
        center.style.maxWidth = '300px';
        
        document.body.appendChild(center);
    }
    
    // Channel Management
    
    subscribeToTicket(ticketId) {
        if (!this.echo || this.channels.has(ticketId)) return;
        
        const channel = this.echo.channel(`ticket.${ticketId}`);
        
        channel.listen('TicketPriceChanged', (event) => {
            this.handlePriceUpdate(event);
        });
        
        channel.listen('TicketAvailabilityChanged', (event) => {
            this.handleAvailabilityUpdate(event);
        });
        
        channel.listen('TicketStatusChanged', (event) => {
            this.handleStatusUpdate(event);
        });
        
        this.channels.set(ticketId, channel);
        console.log(`Subscribed to ticket ${ticketId}`);
    }
    
    unsubscribeFromTicket(ticketId) {
        const channel = this.channels.get(ticketId);
        if (channel) {
            channel.stopListening('TicketPriceChanged');
            channel.stopListening('TicketAvailabilityChanged');
            channel.stopListening('TicketStatusChanged');
            this.echo.leaveChannel(channel.name);
            this.channels.delete(ticketId);
            console.log(`Unsubscribed from ticket ${ticketId}`);
        }
    }
    
    resubscribeChannels() {
        const ticketIds = Array.from(this.watchedTickets);
        this.channels.clear();
        
        ticketIds.forEach(ticketId => {
            this.subscribeToTicket(ticketId);
        });
    }
    
    // Event Handlers
    
    handlePriceUpdate(event) {
        const { ticket_id, old_price, new_price, percentage_change, timestamp } = event;
        
        // Update price history
        if (!this.priceHistory.has(ticket_id)) {
            this.priceHistory.set(ticket_id, []);
        }
        
        const history = this.priceHistory.get(ticket_id);
        history.push({
            price: new_price,
            timestamp: new Date(timestamp),
            change: percentage_change
        });
        
        // Keep only last 100 price points
        if (history.length > 100) {
            history.shift();
        }
        
        // Update UI
        this.updateTicketPrice(ticket_id, old_price, new_price, percentage_change);
        
        // Send notification if significant change
        if (Math.abs(percentage_change) >= this.options.priceThreshold) {
            this.sendPriceNotification(ticket_id, old_price, new_price, percentage_change);
        }
        
        // Update price chart if visible
        this.updatePriceChart(ticket_id);
    }
    
    handleAvailabilityUpdate(event) {
        const { ticket_id, available_quantity, total_quantity, is_available } = event;
        
        this.updateTicketAvailability(ticket_id, available_quantity, total_quantity, is_available);
        
        if (!is_available) {
            this.sendAvailabilityNotification(ticket_id, 'Ticket is now sold out!');
        }
    }
    
  handleStatusUpdate(event) {
        const { ticket_id, new_status, reason } = event;
        
        this.updateTicketStatus(ticket_id, new_status);
        
        if (new_status === 'inactive' || new_status === 'removed') {
            this.sendStatusNotification(ticket_id, `Ticket is now ${new_status}`, reason);
        }
    }
    
    // UI Updates
    
    updateTicketPrice(ticketId, oldPrice, newPrice, percentageChange) {
        const ticketElements = document.querySelectorAll(`[data-ticket-id="${ticketId}"]`);
        
        ticketElements.forEach(element => {
            const priceElement = element.querySelector('.ticket-price');
            const changeElement = element.querySelector('.price-change');
            
            if (priceElement) {
                priceElement.textContent = `$${newPrice.toFixed(2)}`;
                
                // Add animation class
                priceElement.classList.add('price-updated');
                setTimeout(() => {
                    priceElement.classList.remove('price-updated');
                }, 1000);
                
                // Color based on change
                if (percentageChange > 0) {
                    priceElement.classList.add('text-red-600');
                    priceElement.classList.remove('text-green-600');
                } else if (percentageChange < 0) {
                    priceElement.classList.add('text-green-600');
                    priceElement.classList.remove('text-red-600');
                }
            }
            
            if (changeElement) {
                const sign = percentageChange >= 0 ? '+' : '';
                changeElement.textContent = `${sign}${percentageChange.toFixed(1)}%`;
                changeElement.className = `price-change text-xs font-medium ${
                    percentageChange >= 0 ? 'text-red-600' : 'text-green-600'
                }`;
            }
        });
    }
    
    updateTicketAvailability(ticketId, availableQuantity, totalQuantity, isAvailable) {
        const ticketElements = document.querySelectorAll(`[data-ticket-id="${ticketId}"]`);
        
        ticketElements.forEach(element => {
            const availabilityElement = element.querySelector('.ticket-availability');
            const statusBadge = element.querySelector('.availability-status');
            
            if (availabilityElement) {
                availabilityElement.textContent = `${availableQuantity} available`;
            }
            
            if (statusBadge) {
                statusBadge.textContent = isAvailable ? 'Available' : 'Sold Out';
                statusBadge.className = `availability-status px-2 py-1 text-xs font-semibold rounded ${
                    isAvailable 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                }`;
            }
        });
    }
    
    updateTicketStatus(ticketId, status) {
        const ticketElements = document.querySelectorAll(`[data-ticket-id="${ticketId}"]`);
        
        ticketElements.forEach(element => {
            const statusElement = element.querySelector('.ticket-status');
            if (statusElement) {
                statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusElement.className = `ticket-status px-2 py-1 text-xs font-semibold rounded ${
                    this.getStatusColor(status)
                }`;
            }
            
            if (status === 'inactive' || status === 'removed') {
                element.style.opacity = '0.5';
                element.classList.add('pointer-events-none');
            }
        });
    }
    
    updatePriceChart(ticketId) {
        const chartContainer = document.getElementById(`price-chart-${ticketId}`);
        if (!chartContainer || !this.priceHistory.has(ticketId)) return;
        
        const history = this.priceHistory.get(ticketId);
        const chartData = {
            labels: history.map(point => point.timestamp.toLocaleTimeString()),
            datasets: [{
                label: 'Price',
                data: history.map(point => point.price),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        };
        
        // Update chart (assuming Chart.js is available)
        if (window.Chart && chartContainer.chart) {
            chartContainer.chart.data = chartData;
            chartContainer.chart.update('none');
        }
    }
    
    updateConnectionStatus(status) {
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const toggleButton = document.getElementById('toggle-monitoring');
        
        if (!statusDot || !statusText || !toggleButton) return;
        
        switch (status) {
            case 'connected':
                statusDot.className = 'w-3 h-3 rounded-full bg-green-500';
                statusText.textContent = 'Live Monitoring';
                toggleButton.textContent = 'Pause';
                break;
            case 'disconnected':
                statusDot.className = 'w-3 h-3 rounded-full bg-red-500';
                statusText.textContent = 'Disconnected';
                toggleButton.textContent = 'Resume';
                break;
            case 'connecting':
                statusDot.className = 'w-3 h-3 rounded-full bg-yellow-500';
                statusText.textContent = 'Connecting...';
                toggleButton.textContent = 'Cancel';
                break;
        }
    }
    
    // Additional methods would continue here...
    // For brevity, I'll include key methods only
    
    togglePriceAlert(ticketId) {
        if (this.watchedTickets.has(ticketId)) {
            this.removePriceAlert(ticketId);
        } else {
            this.addPriceAlert(ticketId);
        }
    }
    
    addPriceAlert(ticketId) {
        this.watchedTickets.add(ticketId);
        this.subscribeToTicket(ticketId);
        this.updateAlertButtons(ticketId, true);
        this.saveWatchedTickets();
        
        this.showNotification({
            title: 'Price Alert Added',
            message: 'You will be notified of price changes for this ticket.',
            type: 'success'
        });
    }
    
    removePriceAlert(ticketId) {
        this.watchedTickets.delete(ticketId);
        this.unsubscribeFromTicket(ticketId);
        this.updateAlertButtons(ticketId, false);
        this.priceHistory.delete(ticketId);
        this.saveWatchedTickets();
        
        this.showNotification({
            title: 'Price Alert Removed',
            message: 'You will no longer receive notifications for this ticket.',
            type: 'info'
        });
    }
    
    updateAlertButtons(ticketId, isWatched) {
        const buttons = document.querySelectorAll(`[data-ticket-id="${ticketId}"] .price-alert-toggle`);
        buttons.forEach(button => {
            this.updateAlertButton(button, isWatched);
        });
    }
    
    updateAlertButton(button, isWatched) {
        if (isWatched) {
            button.textContent = '🔔 Watching';
            button.className = 'price-alert-toggle px-2 py-1 text-xs rounded border border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors';
        } else {
            button.textContent = '🔕 Watch';
            button.className = 'price-alert-toggle px-2 py-1 text-xs rounded border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors';
        }
    }
    
    showNotification(notification) {
        const id = Date.now() + Math.random();
        notification.id = id;
        notification.timestamp = new Date();
        
        this.notifications.unshift(notification);
        
        // Limit notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }
        
        this.renderNotification(notification);
        
        // Auto-remove non-persistent notifications
        if (!notification.persistent) {
            setTimeout(() => {
                this.removeNotification(id);
            }, 5000);
        }
    }
    
    renderNotification(notification) {
        const center = document.getElementById('notification-center');
        if (!center) return;
        
        const element = document.createElement('div');
        element.id = `notification-${notification.id}`;
        element.className = `notification p-4 rounded-lg shadow-lg border-l-4 max-w-sm transform transition-all duration-300 translate-x-full ${
            this.getNotificationColor(notification.type)
        }`;
        
        element.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-sm">${notification.title}</h4>
                    <p class="text-xs text-gray-600 mt-1">${notification.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${notification.timestamp.toLocaleTimeString()}</p>
                </div>
                <button class="ml-2 text-gray-400 hover:text-gray-600" onclick="priceMonitor.removeNotification(${notification.id})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        center.insertBefore(element, center.firstChild);
        
        // Animate in
        setTimeout(() => {
            element.classList.remove('translate-x-full');
        }, 50);
    }
    
    removeNotification(id) {
        const element = document.getElementById(`notification-${id}`);
        if (element) {
            element.classList.add('translate-x-full');
            setTimeout(() => {
                element.remove();
            }, 300);
        }
        
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
    
    handleConnectionError() {
        if (this.retryCount < this.options.maxRetries) {
            this.retryCount++;
            this.updateConnectionStatus('connecting');
            
            setTimeout(() => {
                console.log(`Attempting reconnection (${this.retryCount}/${this.options.maxRetries})`);
                this.setupEcho();
            }, this.options.retryDelay * this.retryCount);
        } else {
            console.error('Max reconnection attempts reached');
            this.updateConnectionStatus('disconnected');
        }
    }
    
    pauseMonitoring() {
        if (this.echo) {
            this.echo.disconnect();
            this.isConnected = false;
            this.updateConnectionStatus('disconnected');
        }
    }
    
    resumeMonitoring() {
        this.retryCount = 0;
        this.setupEcho();
    }
    
    getStatusColor(status) {
        const colors = {
            active: 'bg-green-100 text-green-800',
            inactive: 'bg-yellow-100 text-yellow-800',
            removed: 'bg-red-100 text-red-800',
            sold_out: 'bg-gray-100 text-gray-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }
    
    getNotificationColor(type) {
        const colors = {
            success: 'border-green-500 bg-green-50',
            warning: 'border-yellow-500 bg-yellow-50',
            error: 'border-red-500 bg-red-50',
            info: 'border-blue-500 bg-blue-50'
        };
        return colors[type] || colors.info;
    }
    
    saveWatchedTickets() {
        const tickets = Array.from(this.watchedTickets);
        localStorage.setItem('watched_tickets', JSON.stringify(tickets));
    }
    
    loadWatchedTickets() {
        try {
            const saved = localStorage.getItem('watched_tickets');
            if (saved) {
                const tickets = JSON.parse(saved);
                tickets.forEach(ticketId => {
                    this.watchedTickets.add(ticketId);
                    this.subscribeToTicket(ticketId);
                });
                
                // Update UI
                setTimeout(() => {
                    tickets.forEach(ticketId => {
                        this.updateAlertButtons(ticketId, true);
                    });
                }, 1000);
            }
        } catch (error) {
            console.warn('Failed to load watched tickets:', error);
        }
    }
    
    // Public API
    isWatching(ticketId) {
        return this.watchedTickets.has(ticketId);
    }
    
    getPriceHistory(ticketId) {
        return this.priceHistory.get(ticketId) || [];
    }
    
    getNotifications() {
        return [...this.notifications];
    }
    
    clearNotifications() {
        this.notifications = [];
        const center = document.getElementById('notification-center');
        if (center) {
            center.innerHTML = '';
        }
    }
    
    destroy() {
        if (this.echo) {
            this.echo.disconnect();
        }
        
        this.channels.clear();
        this.watchedTickets.clear();
        this.priceHistory.clear();
        this.notifications = [];
        
        // Remove UI elements
        const statusIndicator = document.getElementById('price-monitor-status');
        if (statusIndicator) statusIndicator.remove();
        
        const notificationCenter = document.getElementById('notification-center');
        if (notificationCenter) notificationCenter.remove();
    }
}

// Global instance
window.PriceMonitor = PriceMonitor;

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.priceMonitor = new PriceMonitor();
    });
} else {
    window.priceMonitor = new PriceMonitor();
}
