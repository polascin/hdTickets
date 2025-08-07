/**
 * WebSocket Testing Utility for Browser Console
 * Usage: Run this script in browser console to test WebSocket connections
 */

window.WebSocketTester = {
    /**
     * Test WebSocket Manager connection status
     */
    testConnection() {
        console.log('🔍 Testing WebSocket Connection...');
        console.log('=====================================');
        
        if (typeof window.websocketManager === 'undefined') {
            console.error('❌ WebSocket Manager not found');
            return false;
        }
        
        const status = window.websocketManager.getConnectionStatus();
        console.log('📊 Connection Status:', status);
        
        // Test basic functionality
        console.log('🧪 Testing basic functionality...');
        
        // Add a test event listener
        window.websocketManager.on('test-connection', (data) => {
            console.log('✅ Test connection event received:', data);
        });
        
        // Emit a test event
        setTimeout(() => {
            window.websocketManager.emit('test-connection', {
                message: 'Connection test successful',
                timestamp: new Date().toISOString()
            });
        }, 500);
        
        return status.isConnected;
    },
    
    /**
     * Test ticket update subscriptions
     */
    testTicketSubscription() {
        console.log('🎟️ Testing Ticket Update Subscription...');
        
        window.websocketManager.subscribeToTicketUpdates((data) => {
            console.log('🎟️ Ticket update received:', data);
        });
        
        // Simulate a ticket update
        setTimeout(() => {
            window.websocketManager.emit('ticket-updated', {
                id: 'test-123',
                event: 'Test Event',
                availability: 'available',
                price: 99.99,
                timestamp: new Date().toISOString()
            });
        }, 1000);
        
        console.log('✅ Ticket subscription test initiated');
    },
    
    /**
     * Test platform monitoring subscription
     */
    testPlatformMonitoring() {
        console.log('🖥️ Testing Platform Monitoring Subscription...');
        
        window.websocketManager.subscribeToPlatformMonitoring((data) => {
            console.log('🖥️ Platform status update received:', data);
        });
        
        // Simulate a platform update
        setTimeout(() => {
            window.websocketManager.emit('platform-status-updated', {
                platform: 'test-platform',
                status: 'online',
                response_time: 150,
                timestamp: new Date().toISOString()
            });
        }, 1500);
        
        console.log('✅ Platform monitoring test initiated');
    },
    
    /**
     * Test analytics subscription
     */
    testAnalytics() {
        console.log('📊 Testing Analytics Subscription...');
        
        window.websocketManager.subscribeToAnalytics((data) => {
            console.log('📊 Analytics update received:', data);
        });
        
        // Simulate an analytics update
        setTimeout(() => {
            window.websocketManager.emit('analytics-updated', {
                active_users: 42,
                active_monitors: 15,
                total_tickets_found: 237,
                timestamp: new Date().toISOString()
            });
        }, 2000);
        
        console.log('✅ Analytics subscription test initiated');
    },
    
    /**
     * Test reconnection functionality
     */
    testReconnection() {
        console.log('🔄 Testing Reconnection...');
        
        if (window.websocketManager.reconnect) {
            window.websocketManager.on('connected', () => {
                console.log('✅ Reconnection successful');
            });
            
            window.websocketManager.on('disconnected', () => {
                console.log('⚠️ Connection lost, attempting to reconnect...');
            });
            
            window.websocketManager.reconnect();
            console.log('🔄 Reconnection initiated');
        } else {
            console.warn('⚠️ Reconnection method not available');
        }
    },
    
    /**
     * Run comprehensive test suite
     */
    runFullTest() {
        console.log('🚀 Running Comprehensive WebSocket Test Suite...');
        console.log('====================================================');
        
        this.testConnection();
        
        setTimeout(() => this.testTicketSubscription(), 1000);
        setTimeout(() => this.testPlatformMonitoring(), 2000);
        setTimeout(() => this.testAnalytics(), 3000);
        
        // Show results after all tests
        setTimeout(() => {
            console.log('📋 Test Summary');
            console.log('================');
            console.log('Connection Type:', window.websocketManager?.getConnectionStatus()?.connectionType || 'unknown');
            console.log('Is Connected:', window.websocketManager?.getConnectionStatus()?.isConnected || false);
            console.log('Reconnect Attempts:', window.websocketManager?.getConnectionStatus()?.reconnectAttempts || 0);
            console.log('✅ Full test suite completed!');
        }, 5000);
    },
    
    /**
     * Test Soketi server connectivity
     */
    testSoketiServer() {
        console.log('🌐 Testing Soketi Server Connectivity...');
        
        fetch('http://127.0.0.1:6001/apps/hd-tickets-app/channels', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                console.log('✅ Soketi server is reachable');
                return response.json();
            } else {
                console.warn('⚠️ Soketi server responded with status:', response.status);
            }
        })
        .then(data => {
            if (data) {
                console.log('📊 Soketi channels:', data);
            }
        })
        .catch(error => {
            console.error('❌ Failed to connect to Soketi server:', error);
            console.log('💡 Make sure Soketi is running on port 6001');
        });
    },
    
    /**
     * Show connection diagnostics
     */
    diagnose() {
        console.log('🔬 WebSocket Connection Diagnostics');
        console.log('=====================================');
        
        // Check if WebSocket is supported
        console.log('WebSocket Support:', typeof WebSocket !== 'undefined' ? '✅' : '❌');
        
        // Check dependencies
        console.log('Laravel Echo:', typeof window.Echo !== 'undefined' ? '✅' : '❌');
        console.log('Pusher:', typeof window.Pusher !== 'undefined' ? '✅' : '❌');
        console.log('WebSocket Manager:', typeof window.websocketManager !== 'undefined' ? '✅' : '❌');
        
        // Check configuration
        if (window.websocketManager) {
            const status = window.websocketManager.getConnectionStatus();
            console.log('Connection Type:', status.connectionType || 'none');
            console.log('Is Connected:', status.isConnected ? '✅' : '❌');
            console.log('Reconnect Attempts:', status.reconnectAttempts);
        }
        
        // Check environment variables
        console.log('Environment Variables:');
        console.log('- VITE_PUSHER_APP_KEY:', import.meta.env?.VITE_PUSHER_APP_KEY ? '✅' : '❌');
        console.log('- VITE_PUSHER_HOST:', import.meta.env?.VITE_PUSHER_HOST || 'not set');
        console.log('- VITE_PUSHER_PORT:', import.meta.env?.VITE_PUSHER_PORT || 'not set');
        
        // Test Soketi connectivity
        this.testSoketiServer();
    }
};

// Auto-run diagnostics when script loads
if (typeof window !== 'undefined') {
    console.log('🧪 WebSocket Tester loaded. Available commands:');
    console.log('- WebSocketTester.diagnose() - Run connection diagnostics');
    console.log('- WebSocketTester.testConnection() - Test basic connection');
    console.log('- WebSocketTester.runFullTest() - Run comprehensive test suite');
    console.log('- WebSocketTester.testSoketiServer() - Test Soketi server connectivity');
}
