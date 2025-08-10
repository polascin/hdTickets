/**
 * HD Tickets - Dashboard Real-time Updates
 * Handles real-time data updates and WebSocket integration
 */

class DashboardRealtime {
    constructor(options = {}) {
        this.options = {
            userId: options.userId,
            websocket: options.websocket,
            refreshInterval: options.refreshInterval || 30000,
            enableSkeletonLoaders: options.enableSkeletonLoaders || true,
            ...options
        };
        
        this.wsClient = null;
        this.refreshTimer = null;
        this.updateQueue = [];
        this.isProcessingUpdates = false;
    }

    init() {
        console.log('Initializing Dashboard Real-time Updates');
        
        // Initialize WebSocket if available
        if (window.WebSocketClient && this.options.websocket) {
            this.initializeWebSocket();
        }
        
        // Set up periodic refresh
        this.startPeriodicRefresh();
        
        // Set up event listeners
        this.setupEventListeners();
        
        console.log('Dashboard Real-time Updates initialized');
    }

    initializeWebSocket() {
        try {
            this.wsClient = new WebSocketClient(this.options.websocket);
            
            // Set up WebSocket event handlers
            this.wsClient.on('connected', () => {
                this.updateConnectionStatus('connected');
                console.log('Dashboard WebSocket connected');
            });
            
            this.wsClient.on('disconnected', () => {
                this.updateConnectionStatus('disconnected');
                console.log('Dashboard WebSocket disconnected');
            });
            
            this.wsClient.on('ticket_update', (data) => {
                this.handleTicketUpdate(data);
            });
            
            this.wsClient.on('stats_update', (data) => {
                this.handleStatsUpdate(data);
            });
            
            this.wsClient.on('alert_update', (data) => {
                this.handleAlertUpdate(data);
            });
            
        } catch (error) {
            console.warn('WebSocket initialization failed:', error);
            this.updateConnectionStatus('error');
        }
    }

    updateConnectionStatus(status) {
        const indicator = document.querySelector('[data-connection-indicator]');
        const statusElement = document.querySelector('[data-status]');
        const textElement = document.querySelector('[data-connection-text]');
        
        if (!indicator || !statusElement || !textElement) return;
        
        // Remove all status classes
        statusElement.classList.remove('bg-green-400', 'bg-yellow-400', 'bg-red-400', 'animate-pulse');
        
        switch (status) {
            case 'connected':
                statusElement.classList.add('bg-green-400', 'animate-pulse');
                textElement.textContent = 'Connected';
                break;
            case 'disconnected':
                statusElement.classList.add('bg-yellow-400');
                textElement.textContent = 'Reconnecting...';
                break;
            case 'error':
                statusElement.classList.add('bg-red-400');
                textElement.textContent = 'Offline';
                break;
        }
    }

    handleTicketUpdate(data) {
        console.log('Ticket update received:', data);
        
        if (data.ticketId) {
            this.updateTicketElement(data.ticketId, data);
        }
        
        // Update recent tickets list
        if (data.action === 'new_ticket') {
            this.addNewTicketToList(data.ticket);
        } else if (data.action === 'update_ticket') {
            this.updateTicketInList(data.ticketId, data.updates);
        }
        
        // Update statistics if provided
        if (data.stats) {
            this.updateStats(data.stats);
        }
    }

    handleStatsUpdate(data) {
        console.log('Stats update received:', data);
        this.updateStats(data);
    }

    handleAlertUpdate(data) {
        console.log('Alert update received:', data);
        
        // Update alert count in header
        const alertStat = document.querySelector('[data-stat="user-alerts"]');
        if (alertStat && data.userAlerts !== undefined) {
            alertStat.textContent = `${data.userAlerts} Active Alerts`;
        }
        
        // Update alert stat card
        const alertValue = document.querySelector('[data-live-value="alerts"]');
        if (alertValue && data.userAlerts !== undefined) {
            this.animateValueChange(alertValue, data.userAlerts);
        }
    }

    updateTicketElement(ticketId, data) {
        const ticketElement = document.querySelector(`[data-ticket-id="${ticketId}"]`);
        if (!ticketElement) return;
        
        // Update availability indicator
        const indicator = ticketElement.querySelector('.availability-indicator');
        if (indicator && data.isAvailable !== undefined) {
            indicator.classList.remove('bg-green-400', 'bg-red-400');
            indicator.classList.add(data.isAvailable ? 'bg-green-400' : 'bg-red-400');
            indicator.setAttribute('data-status', data.isAvailable ? 'available' : 'unavailable');
        }
        
        // Update price
        if (data.price !== undefined) {
            const priceElement = ticketElement.querySelector('.text-green-600');
            if (priceElement) {
                const oldPrice = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, ''));
                priceElement.textContent = `$${parseFloat(data.price).toFixed(2)}`;
                
                // Add price change animation
                if (data.price !== oldPrice) {
                    this.animatePriceChange(priceElement, data.price < oldPrice);
                }
            }
        }
        
        // Update badges
        if (data.isHighDemand !== undefined) {
            this.updateTicketBadges(ticketElement, data);
        }
    }

    animatePriceChange(element, isDecrease) {
        element.classList.add(isDecrease ? 'price-decrease' : 'price-increase');
        setTimeout(() => {
            element.classList.remove('price-decrease', 'price-increase');
        }, 2000);
    }

    updateTicketBadges(ticketElement, data) {
        const badgesContainer = ticketElement.querySelector('.space-x-2');
        if (!badgesContainer) return;
        
        // Remove existing high demand badge
        const existingBadge = badgesContainer.querySelector('.bg-red-100');
        if (existingBadge && existingBadge.textContent.includes('High Demand')) {
            existingBadge.remove();
        }
        
        // Add high demand badge if needed
        if (data.isHighDemand) {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            badge.textContent = 'High Demand';
            badgesContainer.insertBefore(badge, badgesContainer.firstChild);
        }
    }

    updateStats(stats) {
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-live-value="${key}"]`);
            if (element) {
                this.animateValueChange(element, value);
            }
            
            // Update header stats
            const headerStat = document.querySelector(`[data-stat="${key}"]`);
            if (headerStat) {
                headerStat.textContent = this.formatStatText(key, value);
            }
        });
    }

    formatStatText(key, value) {
        const formatMap = {
            'total-tickets': `${value} Available`,
            'user-alerts': `${value} Active Alerts`,
            'available-tickets': `${value} Available`,
            'high-demand': `${value} High Demand`,
            'alerts': `${value} Alerts`,
            'queue': `${value} In Queue`
        };
        
        return formatMap[key] || `${value}`;
    }

    animateValueChange(element, newValue) {
        const currentValue = element.textContent;
        const formattedValue = typeof newValue === 'number' ? 
            newValue.toLocaleString() : 
            newValue.toString();
        
        if (currentValue !== formattedValue) {
            // Add update animation
            element.classList.add('stat-updating');
            
            setTimeout(() => {
                element.textContent = formattedValue;
                element.classList.remove('stat-updating');
                element.classList.add('stat-updated');
                
                setTimeout(() => {
                    element.classList.remove('stat-updated');
                }, 1000);
            }, 150);
        }
    }

    addNewTicketToList(ticket) {
        const ticketsList = document.querySelector('.recent-tickets-list .space-y-4');
        if (!ticketsList) return;
        
        // Create new ticket element
        const ticketElement = this.createTicketElement(ticket);
        
        // Add to top of list
        ticketsList.insertBefore(ticketElement, ticketsList.firstChild);
        
        // Remove last ticket if more than 5
        const tickets = ticketsList.querySelectorAll('.ticket-item');
        if (tickets.length > 5) {
            tickets[tickets.length - 1].remove();
        }
        
        // Animate new ticket
        ticketElement.classList.add('animate-fade-in-up');
    }

    createTicketElement(ticket) {
        const element = document.createElement('div');
        element.className = 'ticket-item';
        element.setAttribute('data-ticket-id', ticket.id);
        element.setAttribute('data-realtime', `ticket-${ticket.id}`);
        
        element.innerHTML = `
            <div class="availability-indicator ${ticket.is_available ? 'bg-green-400' : 'bg-red-400'} ${ticket.is_high_demand ? 'animate-pulse' : ''}" data-status="${ticket.is_available ? 'available' : 'unavailable'}"></div>
            <div class="ml-4 flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <span class="ticket-title">${ticket.event_name || 'Sports Event Ticket'}</span>
                            ${ticket.venue ? `<span class="text-gray-500">at ${ticket.venue}</span>` : ''}
                        </p>
                        <p class="text-sm text-gray-500">Just scraped</p>
                        ${ticket.price ? `<p class="text-sm font-semibold text-green-600">$${parseFloat(ticket.price).toFixed(2)}</p>` : ''}
                    </div>
                    <div class="flex items-center space-x-2">
                        ${ticket.is_high_demand ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High Demand</span>' : ''}
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${ticket.is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${ticket.is_available ? 'Available' : 'Sold Out'}
                        </span>
                        ${ticket.source_platform ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${ticket.source_platform.charAt(0).toUpperCase() + ticket.source_platform.slice(1)}</span>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        return element;
    }

    startPeriodicRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // Only start periodic refresh if page is visible
        if (!document.hidden) {
            this.refreshTimer = setInterval(() => {
                // Double-check visibility before refreshing
                if (!document.hidden) {
                    this.refreshData();
                }
            }, this.options.refreshInterval);
        }
    }

    stopPeriodicRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    async refreshData() {
        // Skip refresh if page is hidden or user is not active
        if (document.hidden || this.isProcessingUpdates) {
            return;
        }
        
        try {
            this.isProcessingUpdates = true;
            
            // Request data refresh via WebSocket if available
            if (this.wsClient && this.wsClient.isConnected()) {
                this.wsClient.send({
                    type: 'refresh_dashboard',
                    userId: this.options.userId
                });
            } else {
                // Fallback to HTTP refresh with throttling
                await this.httpRefresh();
            }
        } catch (error) {
            console.error('Error refreshing data:', error);
        } finally {
            // Reset processing flag after a short delay
            setTimeout(() => {
                this.isProcessingUpdates = false;
            }, 1000);
        }
    }

    async httpRefresh() {
        // This would typically make AJAX calls to refresh data
        // For now, we'll just log that a refresh would happen
        console.log('HTTP refresh would be performed here');
    }

    setupEventListeners() {
        // Handle visibility change to pause/resume updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPeriodicRefresh();
                console.log('Dashboard updates paused (page hidden)');
            } else {
                // Small delay before resuming to prevent rapid switching
                setTimeout(() => {
                    this.startPeriodicRefresh();
                    console.log('Dashboard updates resumed (page visible)');
                }, 500);
            }
        });
        
        // Handle online/offline events
        window.addEventListener('online', () => {
            console.log('Connection restored');
            if (this.wsClient) {
                this.wsClient.connect();
            }
            this.refreshData();
        });
        
        window.addEventListener('offline', () => {
            console.log('Connection lost');
            this.updateConnectionStatus('error');
        });
    }

    // Public methods for manual control
    refresh() {
        this.refreshData();
    }

    pause() {
        this.stopPeriodicRefresh();
    }

    resume() {
        this.startPeriodicRefresh();
    }

    destroy() {
        this.stopPeriodicRefresh();
        if (this.wsClient) {
            this.wsClient.disconnect();
        }
    }
}

// Add CSS for animations
const realtimeStyles = `
    .stat-updating {
        transform: scale(0.95);
        opacity: 0.7;
        transition: all 0.15s ease-out;
    }
    
    .stat-updated {
        transform: scale(1.05);
        transition: all 0.15s ease-out;
    }
    
    .price-decrease {
        color: #059669 !important;
        animation: priceChangeGlow 2s ease-out;
    }
    
    .price-increase {
        color: #dc2626 !important;
        animation: priceChangeGlow 2s ease-out;
    }
    
    @keyframes priceChangeGlow {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;

// Inject styles
if (typeof document !== 'undefined') {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = realtimeStyles;
    document.head.appendChild(styleSheet);
}

// Global instance
if (typeof window !== 'undefined') {
    window.DashboardRealtime = DashboardRealtime;
}
