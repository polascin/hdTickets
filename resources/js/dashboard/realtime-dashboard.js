/**
 * Real-time Dashboard Integration
 * 
 * Handles WebSocket connections for live dashboard updates including
 * price alerts, ticket availability notifications, and live data updates.
 */

export class RealTimeDashboard {
    constructor(userId, options = {}) {
        this.userId = userId;
        this.options = {
            enableNotifications: true,
            enablePriceAlerts: true,
            enableTicketUpdates: true,
            enableDashboardUpdates: true,
            notificationDuration: 5000,
            maxRetryAttempts: 5,
            retryDelay: 2000,
            ...options
        };
        
        this.isConnected = false;
        this.retryAttempts = 0;
        this.subscriptions = new Map();
        this.eventHandlers = new Map();
        
        // Initialize connection
        this.init();
    }

    /**
     * Initialize the real-time dashboard
     */
    init() {
        if (!window.Echo) {
            console.error('Laravel Echo not initialized. Make sure echo.js is loaded.');
            return;
        }

        this.setupConnectionHandlers();
        this.subscribeToChannels();
        this.setupGlobalEventHandlers();
        
        console.log('🚀 Real-time dashboard initialized for user:', this.userId);
    }

    /**
     * Setup WebSocket connection event handlers
     */
    setupConnectionHandlers() {
        // Connection established
        document.addEventListener('echo:connected', (event) => {
            this.isConnected = true;
            this.retryAttempts = 0;
            console.log('✅ Real-time dashboard connected');
            
            this.showNotification('success', 'Real-time updates connected!', 2000);
            this.updateConnectionIndicator('connected');
            this.emit('connected', event.detail);
        });

        // Connection lost
        document.addEventListener('echo:disconnected', () => {
            this.isConnected = false;
            console.log('❌ Real-time dashboard disconnected');
            
            this.showNotification('warning', 'Real-time updates disconnected. Attempting to reconnect...', 3000);
            this.updateConnectionIndicator('disconnected');
            this.emit('disconnected');
            
            this.attemptReconnection();
        });

        // Connection error
        document.addEventListener('echo:error', (event) => {
            this.isConnected = false;
            console.error('🔴 Real-time dashboard connection error:', event.detail);
            
            this.showNotification('error', 'Connection error. Some features may be limited.');
            this.updateConnectionIndicator('error');
            this.emit('error', event.detail);
        });

        // Connection unavailable
        document.addEventListener('echo:unavailable', () => {
            this.showNotification('warning', 'Real-time service temporarily unavailable');
            this.updateConnectionIndicator('unavailable');
        });
    }

    /**
     * Subscribe to relevant WebSocket channels
     */
    subscribeToChannels() {
        // Dashboard updates channel
        if (this.options.enableDashboardUpdates) {
            this.subscribeToChannel(`private-dashboard.${this.userId}`, {
                'dashboard.updated': this.handleDashboardUpdate.bind(this)
            });
        }

        // Price alerts channel
        if (this.options.enablePriceAlerts) {
            this.subscribeToChannel(`private-price-alerts.${this.userId}`, {
                'PriceAlertTriggered': this.handlePriceAlert.bind(this)
            });
        }

        // General notifications channel
        if (this.options.enableNotifications) {
            this.subscribeToChannel(`private-notifications.${this.userId}`, {
                'App\\Events\\SystemNotification': this.handleSystemNotification.bind(this)
            });
        }

        // Ticket updates (public channel)
        if (this.options.enableTicketUpdates) {
            this.subscribeToChannel('ticket-updates', {
                'ticket.price.changed': this.handleTicketPriceChange.bind(this),
                'ticket.availability.changed': this.handleTicketAvailabilityChange.bind(this)
            });
        }
    }

    /**
     * Subscribe to a channel with event handlers
     */
    subscribeToChannel(channelName, eventHandlers) {
        try {
            const channel = window.Echo.channel(channelName);
            
            // Bind event handlers
            Object.entries(eventHandlers).forEach(([eventName, handler]) => {
                channel.listen(eventName, handler);
            });
            
            this.subscriptions.set(channelName, channel);
            console.log(`📡 Subscribed to channel: ${channelName}`);
            
        } catch (error) {
            console.error(`Failed to subscribe to channel ${channelName}:`, error);
        }
    }

    /**
     * Handle dashboard data updates
     */
    handleDashboardUpdate(event) {
        console.log('📊 Dashboard update received:', event);
        
        const { update_type, data, message } = event;
        
        // Update dashboard data based on type
        switch (update_type) {
            case 'stats':
                this.updateDashboardStats(data);
                break;
            case 'tickets':
                this.updateTicketsList(data);
                break;
            case 'alerts':
                this.updateAlertsList(data);
                break;
            case 'subscription':
                this.updateSubscriptionStatus(data);
                break;
            default:
                this.handleGenericUpdate(data);
        }
        
        if (message) {
            this.showNotification('info', message, 3000);
        }
        
        this.emit('dashboardUpdated', event);
    }

    /**
     * Handle price alert notifications
     */
    handlePriceAlert(event) {
        console.log('💰 Price alert triggered:', event);
        
        const { notification, price_alert, ticket } = event;
        
        // Show notification
        this.showNotification('success', notification.title, 8000, {
            body: notification.message,
            icon: '💰',
            actions: [
                {
                    label: 'View Ticket',
                    action: () => window.location.href = ticket.url
                },
                {
                    label: 'Dismiss',
                    action: () => {}
                }
            ]
        });
        
        // Update alerts in dashboard
        this.updateAlertsCount();
        
        // Play notification sound if enabled
        this.playNotificationSound('alert');
        
        // Emit custom event
        this.emit('priceAlert', event);
    }

    /**
     * Handle system notifications
     */
    handleSystemNotification(event) {
        console.log('🔔 System notification:', event);
        
        const { type, title, message, data } = event;
        
        this.showNotification(type || 'info', title, this.options.notificationDuration, {
            body: message,
            data: data
        });
        
        this.emit('systemNotification', event);
    }

    /**
     * Handle ticket price changes
     */
    handleTicketPriceChange(event) {
        console.log('💲 Ticket price changed:', event);
        
        // Update ticket cards if visible
        this.updateTicketPrice(event.ticket_id, event.new_price, event.old_price);
        
        // Show subtle notification for significant changes
        if (Math.abs(event.change_percentage) >= 10) {
            const changeText = event.price_change > 0 ? 'increased' : 'dropped';
            this.showNotification('info', 
                `${event.event_name} price ${changeText} by ${Math.abs(event.change_percentage)}%`,
                4000
            );
        }
        
        this.emit('priceChanged', event);
    }

    /**
     * Handle ticket availability changes
     */
    handleTicketAvailabilityChange(event) {
        console.log('🎫 Ticket availability changed:', event);
        
        // Update availability indicators
        this.updateTicketAvailability(event.ticket_id, event.is_available, event.quantity);
        
        this.emit('availabilityChanged', event);
    }

    /**
     * Update dashboard statistics
     */
    updateDashboardStats(stats) {
        const statsContainer = document.querySelector('[data-dashboard-stats]');
        if (!statsContainer) return;

        Object.entries(stats).forEach(([key, value]) => {
            const statElement = statsContainer.querySelector(`[data-stat="${key}"]`);
            if (statElement) {
                // Animate the value change
                this.animateValueChange(statElement, value);
            }
        });

        // Trigger any Alpine.js reactivity
        if (window.Alpine && statsContainer._x_dataStack) {
            const component = statsContainer._x_dataStack[0];
            if (component && component.stats) {
                Object.assign(component.stats, stats);
            }
        }
    }

    /**
     * Update tickets list
     */
    updateTicketsList(tickets) {
        const ticketsContainer = document.querySelector('[data-tickets-list]');
        if (!ticketsContainer) return;

        // Trigger Alpine.js component update if available
        if (window.Alpine && ticketsContainer._x_dataStack) {
            const component = ticketsContainer._x_dataStack[0];
            if (component && component.tickets) {
                component.tickets = tickets;
            }
        }
    }

    /**
     * Update alerts count
     */
    updateAlertsCount() {
        const alertsCounters = document.querySelectorAll('[data-alerts-count]');
        alertsCounters.forEach(counter => {
            const currentCount = parseInt(counter.textContent) || 0;
            counter.textContent = currentCount + 1;
            
            // Add visual feedback
            counter.classList.add('pulse-once');
            setTimeout(() => counter.classList.remove('pulse-once'), 1000);
        });
    }

    /**
     * Show enhanced notification
     */
    showNotification(type, message, duration = 5000, options = {}) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} show`;
        
        notification.innerHTML = `
            <div class="notification-content">
                <div class="flex items-start gap-3">
                    <div class="notification-icon">
                        ${this.getNotificationIcon(type)}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm">${message}</div>
                        ${options.body ? `<div class="text-xs text-gray-600 mt-1">${options.body}</div>` : ''}
                    </div>
                    <button class="notification-close text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                ${options.actions ? this.renderNotificationActions(options.actions) : ''}
            </div>
        `;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Setup close handler
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.closeNotification(notification));
        
        // Setup action handlers
        if (options.actions) {
            options.actions.forEach((action, index) => {
                const actionBtn = notification.querySelector(`[data-action="${index}"]`);
                if (actionBtn) {
                    actionBtn.addEventListener('click', () => {
                        action.action();
                        this.closeNotification(notification);
                    });
                }
            });
        }
        
        // Auto-close
        if (duration > 0) {
            setTimeout(() => this.closeNotification(notification), duration);
        }
        
        return notification;
    }

    /**
     * Get notification icon based on type
     */
    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️',
            alert: '💰'
        };
        return icons[type] || '📢';
    }

    /**
     * Render notification actions
     */
    renderNotificationActions(actions) {
        return `
            <div class="flex gap-2 mt-3 pt-2 border-t border-gray-200">
                ${actions.map((action, index) => `
                    <button data-action="${index}" class="btn-modern btn-secondary text-xs px-3 py-1">
                        ${action.label}
                    </button>
                `).join('')}
            </div>
        `;
    }

    /**
     * Close notification with animation
     */
    closeNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    /**
     * Update connection indicator in UI
     */
    updateConnectionIndicator(status) {
        const indicators = document.querySelectorAll('[data-realtime-status]');
        indicators.forEach(indicator => {
            indicator.setAttribute('data-realtime-status', status);
            
            const statusText = indicator.querySelector('[data-status-text]');
            if (statusText) {
                const statusTexts = {
                    connected: 'Live',
                    disconnected: 'Offline',
                    connecting: 'Connecting...',
                    error: 'Error',
                    unavailable: 'Unavailable'
                };
                statusText.textContent = statusTexts[status] || status;
            }
        });
    }

    /**
     * Animate value changes
     */
    animateValueChange(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        const difference = newValue - currentValue;
        
        if (difference === 0) return;
        
        // Add animation class
        element.classList.add('value-changing');
        
        // Animate the number
        const duration = 800;
        const steps = 20;
        const increment = difference / steps;
        let current = currentValue;
        let step = 0;
        
        const animation = setInterval(() => {
            step++;
            current += increment;
            element.textContent = Math.round(current);
            
            if (step >= steps) {
                clearInterval(animation);
                element.textContent = newValue;
                element.classList.remove('value-changing');
            }
        }, duration / steps);
    }

    /**
     * Play notification sound
     */
    playNotificationSound(type = 'default') {
        if (!this.options.enableSounds) return;
        
        // Create audio element
        const audio = new Audio();
        const sounds = {
            default: '/sounds/notification.mp3',
            alert: '/sounds/alert.mp3',
            success: '/sounds/success.mp3'
        };
        
        audio.src = sounds[type] || sounds.default;
        audio.volume = 0.3;
        audio.play().catch(e => {
            console.log('Could not play notification sound:', e);
        });
    }

    /**
     * Attempt to reconnect
     */
    attemptReconnection() {
        if (this.retryAttempts >= this.options.maxRetryAttempts) {
            console.error('Max retry attempts reached. Please refresh the page.');
            this.showNotification('error', 'Connection failed. Please refresh the page.', 0);
            return;
        }
        
        this.retryAttempts++;
        const delay = this.options.retryDelay * this.retryAttempts;
        
        console.log(`Attempting reconnection ${this.retryAttempts}/${this.options.maxRetryAttempts} in ${delay}ms`);
        
        setTimeout(() => {
            if (!this.isConnected && window.Echo) {
                this.subscribeToChannels();
            }
        }, delay);
    }

    /**
     * Event emitter methods
     */
    on(event, callback) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(callback);
    }

    emit(event, data = null) {
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).forEach(callback => callback(data));
        }
    }

    /**
     * Setup global event handlers
     */
    setupGlobalEventHandlers() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.isConnected) {
                // Page became visible, refresh dashboard data
                this.emit('pageVisible');
            }
        });
    }

    /**
     * Clean up subscriptions
     */
    destroy() {
        this.subscriptions.forEach((channel, channelName) => {
            try {
                window.Echo.leave(channelName);
            } catch (error) {
                console.error(`Error leaving channel ${channelName}:`, error);
            }
        });
        
        this.subscriptions.clear();
        this.eventHandlers.clear();
        
        console.log('🔌 Real-time dashboard destroyed');
    }
}

// Auto-initialize if user data is available
if (window.currentUser?.id) {
    window.realTimeDashboard = new RealTimeDashboard(window.currentUser.id, {
        enableNotifications: true,
        enablePriceAlerts: true,
        enableTicketUpdates: true,
        enableDashboardUpdates: true
    });
}

export default RealTimeDashboard;