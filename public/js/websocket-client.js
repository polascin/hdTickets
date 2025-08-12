/**
 * HD Tickets - WebSocket Client
 * Real-time connection handler for dashboard updates
 */

class WebSocketClient {
    constructor(config) {
        this.config = config || window.websocketConfig;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 1000;
        this.heartbeatInterval = null;
        this.callbacks = {};
        
        this.init();
    }

    init() {
        if (!this.config) {
            console.warn('WebSocket configuration not found');
            return;
        }
        
        this.connect();
    }

    connect() {
        try {
            // Create WebSocket URL with authentication
            const wsUrl = `${this.config.url}?userId=${this.config.auth.userId}&token=${this.config.auth.token}`;
            
            console.log('Connecting to WebSocket:', wsUrl);
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = this.onOpen.bind(this);
            this.ws.onmessage = this.onMessage.bind(this);
            this.ws.onclose = this.onClose.bind(this);
            this.ws.onerror = this.onError.bind(this);
            
        } catch (error) {
            console.error('WebSocket connection failed:', error);
            this.scheduleReconnect();
        }
    }

    onOpen(event) {
        console.log('WebSocket connected successfully');
        this.reconnectAttempts = 0;
        
        // Start heartbeat
        this.startHeartbeat();
        
        // Trigger connection callbacks
        this.trigger('connected', { event });
        
        // Send authentication if required
        if (this.config.key) {
            this.send({
                type: 'auth',
                key: this.config.key,
                userId: this.config.auth.userId
            });
        }
    }

    onMessage(event) {
        try {
            const data = JSON.parse(event.data);
            console.log('WebSocket message received:', data);
            
            // Handle system messages
            if (data.type === 'pong') {
                return; // Heartbeat response
            }
            
            if (data.type === 'auth_success') {
                console.log('WebSocket authenticated successfully');
                this.trigger('authenticated', data);
                return;
            }
            
            // Trigger message callbacks
            this.trigger('message', data);
            this.trigger(data.type, data);
            
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }

    onClose(event) {
        console.log('WebSocket connection closed', event);
        this.stopHeartbeat();
        this.trigger('disconnected', { event });
        
        // Attempt to reconnect unless it was intentional
        if (!event.wasClean) {
            this.scheduleReconnect();
        }
    }

    onError(error) {
        console.error('WebSocket error:', error);
        this.trigger('error', { error });
    }

    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
            return true;
        } else {
            console.warn('WebSocket not connected, cannot send message:', data);
            return false;
        }
    }

    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            this.trigger('maxReconnectAttemptsReached');
            return;
        }
        
        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1); // Exponential backoff
        
        console.log(`Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
        
        setTimeout(() => {
            this.connect();
        }, delay);
    }

    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            this.send({ type: 'ping' });
        }, 30000); // 30 seconds
    }

    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }

    // Event system
    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    off(event, callback) {
        if (this.callbacks[event]) {
            const index = this.callbacks[event].indexOf(callback);
            if (index > -1) {
                this.callbacks[event].splice(index, 1);
            }
        }
    }

    trigger(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error in WebSocket callback:', error);
                }
            });
        }
    }

    disconnect() {
        if (this.ws) {
            this.ws.close(1000, 'Client disconnect');
        }
        this.stopHeartbeat();
    }

    // Utility methods
    isConnected() {
        return this.ws && this.ws.readyState === WebSocket.OPEN;
    }

    getReadyState() {
        if (!this.ws) return 'CLOSED';
        
        switch (this.ws.readyState) {
            case WebSocket.CONNECTING: return 'CONNECTING';
            case WebSocket.OPEN: return 'OPEN';
            case WebSocket.CLOSING: return 'CLOSING';
            case WebSocket.CLOSED: return 'CLOSED';
            default: return 'UNKNOWN';
        }
    }
}

// Global instance
if (typeof window !== 'undefined') {
    window.WebSocketClient = WebSocketClient;
    
    // Auto-initialize if config is available
    if (window.websocketConfig) {
        window.wsClient = new WebSocketClient(window.websocketConfig);
    }
}
