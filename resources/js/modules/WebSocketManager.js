/**
 * WebSocket Manager Module
 * Handles real-time communication for the HD Tickets application
 */
export class WebSocketManager {
    constructor(config = {}) {
        this.config = {
            url: config.url || (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/ws',
            reconnectInterval: config.reconnectInterval || 3000,
            maxReconnectAttempts: config.maxReconnectAttempts || 10,
            heartbeatInterval: config.heartbeatInterval || 30000,
            debug: config.debug || false,
            ...config
        };

        this.ws = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.subscriptions = new Map();
        this.eventListeners = new Map();
        this.heartbeatTimer = null;
        this.reconnectTimer = null;
        this.messageQueue = [];

        this.init();
    }

    /**
     * Initialize WebSocket connection
     */
    init() {
        this.connect();
        this.setupEventListeners();
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        try {
            this.log('Attempting to connect to WebSocket...');
            
            this.ws = new WebSocket(this.config.url);
            
            this.ws.onopen = this.handleOpen.bind(this);
            this.ws.onmessage = this.handleMessage.bind(this);
            this.ws.onclose = this.handleClose.bind(this);
            this.ws.onerror = this.handleError.bind(this);
            
        } catch (error) {
            this.log('Failed to create WebSocket connection:', error);
            this.scheduleReconnect();
        }
    }

    /**
     * Handle WebSocket open event
     */
    handleOpen(event) {
        this.log('WebSocket connected successfully');
        this.isConnected = true;
        this.reconnectAttempts = 0;
        
        // Clear reconnect timer
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }

        // Start heartbeat
        this.startHeartbeat();

        // Process queued messages
        this.processMessageQueue();

        // Emit connected event
        this.emit('connected', { timestamp: new Date().toISOString() });
        
        // Re-establish subscriptions
        this.reestablishSubscriptions();
    }

    /**
     * Handle WebSocket message event
     */
    handleMessage(event) {
        try {
            const data = JSON.parse(event.data);
            this.log('Received message:', data);

            // Handle different message types
            switch (data.type) {
                case 'heartbeat':
                    this.handleHeartbeat(data);
                    break;
                case 'subscription':
                    this.handleSubscriptionMessage(data);
                    break;
                case 'broadcast':
                    this.handleBroadcastMessage(data);
                    break;
                case 'error':
                    this.handleServerError(data);
                    break;
                default:
                    this.log('Unknown message type:', data.type);
            }
        } catch (error) {
            this.log('Failed to parse WebSocket message:', error);
        }
    }

    /**
     * Handle WebSocket close event
     */
    handleClose(event) {
        this.log('WebSocket connection closed:', event.code, event.reason);
        this.isConnected = false;
        
        // Stop heartbeat
        this.stopHeartbeat();

        // Emit disconnected event
        this.emit('disconnected', { 
            code: event.code, 
            reason: event.reason,
            timestamp: new Date().toISOString()
        });

        // Schedule reconnect if not manually closed
        if (event.code !== 1000) {
            this.scheduleReconnect();
        }
    }

    /**
     * Handle WebSocket error event
     */
    handleError(event) {
        this.log('WebSocket error:', event);
        this.emit('error', { error: event, timestamp: new Date().toISOString() });
    }

    /**
     * Handle heartbeat message
     */
    handleHeartbeat(data) {
        // Send pong response
        this.send({
            type: 'heartbeat',
            action: 'pong',
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Handle subscription message
     */
    handleSubscriptionMessage(data) {
        const { channel, payload } = data;
        
        if (this.subscriptions.has(channel)) {
            const callbacks = this.subscriptions.get(channel);
            callbacks.forEach(callback => {
                try {
                    callback(payload);
                } catch (error) {
                    this.log('Error in subscription callback:', error);
                }
            });
        }
    }

    /**
     * Handle broadcast message
     */
    handleBroadcastMessage(data) {
        const { event, payload } = data;
        this.emit(event, payload);
    }

    /**
     * Handle server error
     */
    handleServerError(data) {
        this.log('Server error:', data.message);
        this.emit('server-error', data);
    }

    /**
     * Send message to server
     */
    send(data) {
        if (this.isConnected && this.ws.readyState === WebSocket.OPEN) {
            try {
                this.ws.send(JSON.stringify(data));
                this.log('Message sent:', data);
                return true;
            } catch (error) {
                this.log('Failed to send message:', error);
                return false;
            }
        } else {
            // Queue message for later
            this.messageQueue.push(data);
            this.log('Message queued (not connected):', data);
            return false;
        }
    }

    /**
     * Subscribe to a channel
     */
    subscribe(channel, callback) {
        if (!this.subscriptions.has(channel)) {
            this.subscriptions.set(channel, []);
        }
        
        this.subscriptions.get(channel).push(callback);

        // Send subscription request to server
        this.send({
            type: 'subscribe',
            channel: channel,
            timestamp: new Date().toISOString()
        });

        this.log(`Subscribed to channel: ${channel}`);
        
        return () => this.unsubscribe(channel, callback);
    }

    /**
     * Unsubscribe from a channel
     */
    unsubscribe(channel, callback = null) {
        if (this.subscriptions.has(channel)) {
            if (callback) {
                const callbacks = this.subscriptions.get(channel);
                const index = callbacks.indexOf(callback);
                if (index > -1) {
                    callbacks.splice(index, 1);
                }
                
                // Remove channel if no callbacks left
                if (callbacks.length === 0) {
                    this.subscriptions.delete(channel);
                }
            } else {
                // Remove all callbacks for channel
                this.subscriptions.delete(channel);
            }

            // Send unsubscribe request to server
            this.send({
                type: 'unsubscribe',
                channel: channel,
                timestamp: new Date().toISOString()
            });

            this.log(`Unsubscribed from channel: ${channel}`);
        }
    }

    /**
     * Subscribe to ticket updates
     */
    subscribeToTicketUpdates(callback) {
        return this.subscribe('ticket.updates', callback);
    }

    /**
     * Subscribe to dashboard updates
     */
    subscribeToDashboardUpdates(callback) {
        return this.subscribe('dashboard.stats', callback);
    }

    /**
     * Subscribe to alert notifications
     */
    subscribeToAlerts(callback) {
        return this.subscribe('alerts.new', callback);
    }

    /**
     * Subscribe to platform status updates
     */
    subscribeToPlatformStatus(callback) {
        return this.subscribe('platform.status', callback);
    }

    /**
     * Subscribe to user-specific notifications
     */
    subscribeToUserNotifications(userId, callback) {
        return this.subscribe(`user.${userId}.notifications`, callback);
    }

    /**
     * Add event listener
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        
        this.eventListeners.get(event).push(callback);
        
        return () => this.off(event, callback);
    }

    /**
     * Remove event listener
     */
    off(event, callback = null) {
        if (this.eventListeners.has(event)) {
            if (callback) {
                const callbacks = this.eventListeners.get(event);
                const index = callbacks.indexOf(callback);
                if (index > -1) {
                    callbacks.splice(index, 1);
                }
            } else {
                this.eventListeners.delete(event);
            }
        }
    }

    /**
     * Emit event to listeners
     */
    emit(event, data = null) {
        if (this.eventListeners.has(event)) {
            const callbacks = this.eventListeners.get(event);
            callbacks.forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    this.log('Error in event callback:', error);
                }
            });
        }
    }

    /**
     * Start heartbeat timer
     */
    startHeartbeat() {
        this.stopHeartbeat(); // Clear existing timer
        
        this.heartbeatTimer = setInterval(() => {
            this.send({
                type: 'heartbeat',
                action: 'ping',
                timestamp: new Date().toISOString()
            });
        }, this.config.heartbeatInterval);
    }

    /**
     * Stop heartbeat timer
     */
    stopHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.config.maxReconnectAttempts) {
            this.log('Max reconnect attempts reached');
            this.emit('max-reconnect-attempts');
            return;
        }

        const delay = Math.min(
            this.config.reconnectInterval * Math.pow(2, this.reconnectAttempts),
            30000 // Max 30 seconds
        );

        this.log(`Scheduling reconnect attempt ${this.reconnectAttempts + 1} in ${delay}ms`);
        
        this.reconnectTimer = setTimeout(() => {
            this.reconnectAttempts++;
            this.connect();
        }, delay);
    }

    /**
     * Process queued messages
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.send(message);
        }
    }

    /**
     * Re-establish subscriptions after reconnect
     */
    reestablishSubscriptions() {
        for (const channel of this.subscriptions.keys()) {
            this.send({
                type: 'subscribe',
                channel: channel,
                timestamp: new Date().toISOString()
            });
        }
    }

    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.log('Page hidden, maintaining connection');
            } else {
                this.log('Page visible, checking connection');
                if (!this.isConnected) {
                    this.reconnect();
                }
            }
        });

        // Handle network status changes
        window.addEventListener('online', () => {
            this.log('Network online, attempting to reconnect');
            this.reconnect();
        });

        window.addEventListener('offline', () => {
            this.log('Network offline');
            this.emit('network-offline');
        });
    }

    /**
     * Manually reconnect
     */
    reconnect() {
        this.log('Manual reconnect requested');
        this.disconnect();
        this.reconnectAttempts = 0;
        setTimeout(() => this.connect(), 1000);
    }

    /**
     * Disconnect WebSocket
     */
    disconnect() {
        this.log('Disconnecting WebSocket');
        
        if (this.ws) {
            this.ws.close(1000, 'Manual disconnect');
        }
        
        this.stopHeartbeat();
        
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
    }

    /**
     * Get connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            readyState: this.ws ? this.ws.readyState : null,
            reconnectAttempts: this.reconnectAttempts,
            subscriptions: Array.from(this.subscriptions.keys()),
            queuedMessages: this.messageQueue.length
        };
    }

    /**
     * Log message with timestamp
     */
    log(...args) {
        if (this.config.debug) {
            console.log(`[WebSocket ${new Date().toLocaleTimeString()}]`, ...args);
        }
    }

    /**
     * Cleanup resources
     */
    destroy() {
        this.disconnect();
        this.subscriptions.clear();
        this.eventListeners.clear();
        this.messageQueue = [];
        this.log('WebSocket manager destroyed');
    }
}
