/**
 * WebSocket Manager for Real-time Sports Ticket Updates
 * Handles real-time communication for ticket availability, price changes, and notifications
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { io } from 'socket.io-client';

class WebSocketManager {
    constructor() {
        this.echo = null;
        this.pusher = null;
        this.socketIO = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 3000;
        this.channels = new Map();
        this.eventListeners = new Map();
        this.connectionType = null;
        
        // Configuration
        this.config = {
            pusher: {
                key: window.Laravel?.pusher?.key || process.env.VITE_PUSHER_APP_KEY,
                cluster: window.Laravel?.pusher?.cluster || process.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                disabledTransports: ['xhr_polling', 'xhr_streaming'],
                activityTimeout: 30000,
                pongTimeout: 6000,
                unavailableTimeout: 10000
            },
            socketIO: {
                url: window.Laravel?.socketIO?.url || process.env.VITE_SOCKET_IO_URL || 'http://localhost:3000',
                options: {
                    transports: ['websocket', 'polling'],
                    reconnection: true,
                    reconnectionAttempts: 5,
                    reconnectionDelay: 1000,
                    timeout: 20000
                }
            }
        };
        
        this.initializeConnection();
    }

    /**
     * Initialize WebSocket connection based on available configuration
     */
    initializeConnection() {
        // Try Pusher first (Laravel Echo with Pusher)
        if (this.config.pusher.key) {
            this.initializePusher();
        }
        // Fallback to Socket.IO
        else if (this.config.socketIO.url) {
            this.initializeSocketIO();
        }
        else {
            console.warn('No WebSocket configuration found');
        }
    }

    /**
     * Initialize Laravel Echo with Pusher
     */
    initializePusher() {
        try {
            window.Pusher = Pusher;
            
            this.echo = new Echo({
                broadcaster: 'pusher',
                key: this.config.pusher.key,
                cluster: this.config.pusher.cluster,
                forceTLS: this.config.pusher.forceTLS,
                enabledTransports: this.config.pusher.enabledTransports,
                disabledTransports: this.config.pusher.disabledTransports,
                activityTimeout: this.config.pusher.activityTimeout,
                pongTimeout: this.config.pusher.pongTimeout,
                unavailableTimeout: this.config.pusher.unavailableTimeout,
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Authorization': `Bearer ${this.getAuthToken()}`
                    }
                }
            });

            this.connectionType = 'pusher';
            this.setupPusherEventListeners();
            console.log('Laravel Echo with Pusher initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize Pusher:', error);
            this.initializeSocketIO(); // Fallback to Socket.IO
        }
    }

    /**
     * Initialize Socket.IO connection
     */
    initializeSocketIO() {
        try {
            this.socketIO = io(this.config.socketIO.url, {
                ...this.config.socketIO.options,
                auth: {
                    token: this.getAuthToken(),
                    userId: window.Laravel?.user?.id
                }
            });

            this.connectionType = 'socketio';
            this.setupSocketIOEventListeners();
            console.log('Socket.IO initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize Socket.IO:', error);
        }
    }

    /**
     * Setup Pusher-specific event listeners
     */
    setupPusherEventListeners() {
        this.echo.connector.pusher.connection.bind('connected', () => {
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.emit('connected');
            console.log('Pusher connected successfully');
        });

        this.echo.connector.pusher.connection.bind('disconnected', () => {
            this.isConnected = false;
            this.emit('disconnected');
            console.log('Pusher disconnected');
        });

        this.echo.connector.pusher.connection.bind('error', (error) => {
            console.error('Pusher connection error:', error);
            this.emit('error', error);
        });
    }

    /**
     * Setup Socket.IO-specific event listeners
     */
    setupSocketIOEventListeners() {
        this.socketIO.on('connect', () => {
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.emit('connected');
            console.log('Socket.IO connected successfully');
        });

        this.socketIO.on('disconnect', (reason) => {
            this.isConnected = false;
            this.emit('disconnected', reason);
            console.log('Socket.IO disconnected:', reason);
        });

        this.socketIO.on('connect_error', (error) => {
            console.error('Socket.IO connection error:', error);
            this.emit('error', error);
            this.handleReconnection();
        });

        this.socketIO.on('reconnect', (attemptNumber) => {
            console.log(`Socket.IO reconnected after ${attemptNumber} attempts`);
            this.emit('reconnected', attemptNumber);
        });
    }

    /**
     * Subscribe to ticket availability updates
     */
    subscribeToTicketUpdates(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const channel = this.echo.channel('ticket-updates');
            channel.listen('TicketAvailabilityUpdated', callback);
            this.channels.set('ticket-updates', channel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('ticket-availability-updated', callback);
        }
    }

    /**
     * Subscribe to price changes for specific tickets
     */
    subscribeToPriceChanges(ticketIds, callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            ticketIds.forEach(ticketId => {
                const channel = this.echo.channel(`ticket-price.${ticketId}`);
                channel.listen('TicketPriceChanged', callback);
                this.channels.set(`ticket-price.${ticketId}`, channel);
            });
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            ticketIds.forEach(ticketId => {
                this.socketIO.emit('subscribe-price-changes', ticketId);
            });
            this.socketIO.on('ticket-price-changed', callback);
        }
    }

    /**
     * Subscribe to user-specific notifications
     */
    subscribeToUserNotifications(userId, callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const channel = this.echo.private(`user.${userId}`);
            channel.notification(callback);
            this.channels.set(`user.${userId}`, channel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.emit('subscribe-user-notifications', userId);
            this.socketIO.on('user-notification', callback);
        }
    }

    /**
     * Subscribe to real-time analytics updates
     */
    subscribeToAnalytics(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const channel = this.echo.channel('analytics-updates');
            channel.listen('AnalyticsUpdated', callback);
            this.channels.set('analytics-updates', channel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('analytics-updated', callback);
        }
    }

    /**
     * Subscribe to platform monitoring updates
     */
    subscribeToPlatformMonitoring(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const channel = this.echo.channel('platform-monitoring');
            channel.listen('PlatformStatusUpdated', callback);
            this.channels.set('platform-monitoring', channel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('platform-status-updated', callback);
        }
    }

    /**
     * Subscribe to ticket price changes with enhanced event handling
     */
    subscribeToEnhancedPriceChanges(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const ticketChannel = this.echo.channel('ticket-updates');
            const priceChannel = this.echo.channel('price-changes');
            
            ticketChannel.listen('ticket.price.changed', (data) => {
                this.handleTicketPriceChange(data, callback);
            });
            
            priceChannel.listen('ticket.price.changed', (data) => {
                this.handleTicketPriceChange(data, callback);
            });
            
            this.channels.set('enhanced-price-changes', ticketChannel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('ticket.price.changed', (data) => {
                this.handleTicketPriceChange(data, callback);
            });
        }
    }

    /**
     * Subscribe to ticket availability changes with enhanced event handling
     */
    subscribeToEnhancedAvailabilityChanges(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const ticketChannel = this.echo.channel('ticket-updates');
            const availabilityChannel = this.echo.channel('availability-changes');
            
            ticketChannel.listen('ticket.availability.changed', (data) => {
                this.handleAvailabilityChange(data, callback);
            });
            
            availabilityChannel.listen('ticket.availability.changed', (data) => {
                this.handleAvailabilityChange(data, callback);
            });
            
            this.channels.set('enhanced-availability-changes', ticketChannel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('ticket.availability.changed', (data) => {
                this.handleAvailabilityChange(data, callback);
            });
        }
    }

    /**
     * Subscribe to system updates
     */
    subscribeToSystemUpdates(callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            const systemChannel = this.echo.channel('system-updates');
            const dashboardChannel = this.echo.channel('realtime-dashboard');
            
            systemChannel.listen('system.update', (data) => {
                this.handleSystemUpdate(data, callback);
            });
            
            dashboardChannel.listen('system.update', (data) => {
                this.handleSystemUpdate(data, callback);
            });
            
            this.channels.set('system-updates', systemChannel);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.on('system.update', (data) => {
                this.handleSystemUpdate(data, callback);
            });
        }
    }

    /**
     * Subscribe to platform-specific updates
     */
    subscribeToPlatformUpdates(platforms, callback) {
        if (this.connectionType === 'pusher' && this.echo) {
            platforms.forEach(platform => {
                const channel = this.echo.channel(`platform.${platform}`);
                
                channel.listen('ticket.price.changed', (data) => {
                    callback({ type: 'price', platform, data });
                });
                
                channel.listen('ticket.availability.changed', (data) => {
                    callback({ type: 'availability', platform, data });
                });
                
                this.channels.set(`platform.${platform}`, channel);
            });
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            platforms.forEach(platform => {
                this.socketIO.on(`platform.${platform}.update`, (data) => {
                    callback({ platform, data });
                });
            });
        }
    }

    /**
     * Unsubscribe from a specific channel
     */
    unsubscribe(channelName) {
        if (this.connectionType === 'pusher' && this.channels.has(channelName)) {
            this.echo.leaveChannel(channelName);
            this.channels.delete(channelName);
            
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.off(channelName);
        }
    }

    /**
     * Send a message through the WebSocket connection
     */
    send(event, data) {
        if (this.connectionType === 'pusher' && this.echo) {
            // Pusher typically doesn't send client events in this context
            console.warn('Client events not typically sent via Pusher in this setup');
            
        } else if (this.connectionType === 'socketio' && this.socketIO && this.isConnected) {
            this.socketIO.emit(event, data);
        }
    }

    /**
     * Handle reconnection logic
     */
    handleReconnection() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            setTimeout(() => {
                console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
                
                if (this.connectionType === 'socketio' && this.socketIO) {
                    this.socketIO.connect();
                }
            }, this.reconnectInterval * this.reconnectAttempts);
        } else {
            console.error('Max reconnection attempts reached');
            this.emit('max-reconnect-attempts-reached');
        }
    }

    /**
     * Get authentication token
     */
    getAuthToken() {
        return localStorage.getItem('auth_token') || 
               window.Laravel?.user?.api_token || 
               document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    }

    /**
     * Generic event emitter
     */
    emit(event, data = null) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(listener => {
                try {
                    listener(data);
                } catch (error) {
                    console.error(`Error in event listener for ${event}:`, error);
                }
            });
        }
        
        // Also dispatch as DOM event for broader compatibility
        window.dispatchEvent(new CustomEvent(`websocket:${event}`, { detail: data }));
    }

    /**
     * Add event listener
     */
    on(event, listener) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(listener);
    }

    /**
     * Remove event listener
     */
    off(event, listener) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(listener);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    /**
     * Get connection status
     */
    getConnectionStatus() {
        return {
            isConnected: this.isConnected,
            connectionType: this.connectionType,
            reconnectAttempts: this.reconnectAttempts
        };
    }

    /**
     * Disconnect and cleanup
     */
    disconnect() {
        if (this.connectionType === 'pusher' && this.echo) {
            this.echo.disconnect();
        } else if (this.connectionType === 'socketio' && this.socketIO) {
            this.socketIO.disconnect();
        }
        
        this.isConnected = false;
        this.channels.clear();
        this.eventListeners.clear();
    }

    /**
     * Force reconnection
     */
    reconnect() {
        this.disconnect();
        setTimeout(() => {
            this.initializeConnection();
        }, 1000);
    }

    /**
     * Handle ticket price change events
     */
    handleTicketPriceChange(data, callback) {
        console.log('Ticket price change received:', data);
        
        // Call user callback
        if (callback) callback(data);
        
        // Show notification for significant price changes
        if (Math.abs(data.change_percentage) >= 10) {
            this.showNotification(
                `Price ${data.price_change > 0 ? 'increased' : 'decreased'} by ${Math.abs(data.change_percentage)}%`,
                `${data.event_name} on ${data.platform}`,
                data.price_change > 0 ? 'warning' : 'success'
            );
        }
        
        // Emit generic event
        this.emit('price-change', data);
    }

    /**
     * Handle ticket availability change events
     */
    handleAvailabilityChange(data, callback) {
        console.log('Ticket availability change received:', data);
        
        // Call user callback
        if (callback) callback(data);
        
        // Show notification for availability changes
        if (data.new_status === 'available' && data.old_status !== 'available') {
            this.showNotification(
                'Tickets now available!',
                `${data.event_name} on ${data.platform}`,
                'success'
            );
        } else if (data.new_status === 'sold_out') {
            this.showNotification(
                'Tickets sold out',
                `${data.event_name} on ${data.platform}`,
                'error'
            );
        }
        
        // Emit generic event
        this.emit('availability-change', data);
    }

    /**
     * Handle system update events
     */
    handleSystemUpdate(data, callback) {
        console.log('System update received:', data);
        
        // Call user callback
        if (callback) callback(data);
        
        // Show system notifications
        if (data.level === 'error' || data.level === 'warning') {
            this.showNotification(data.message, data.type, data.level);
        }
        
        // Emit generic event
        this.emit('system-update', data);
    }

    /**
     * Show notification using available UI manager or fallback
     */
    showNotification(title, message, type = 'info') {
        // Use UIFeedbackManager if available
        if (window.uiManager && window.uiManager.showNotification) {
            window.uiManager.showNotification(message, type, {
                title: title,
                duration: 5000
            });
        } else if (window.UIFeedbackManager) {
            window.UIFeedbackManager.showNotification(message, type, {
                title: title,
                duration: 5000
            });
        } else {
            // Fallback to console
            console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
        }
    }
}

// Create and export singleton instance
const websocketManager = new WebSocketManager();

// Make it globally available
window.websocketManager = websocketManager;

export default websocketManager;
