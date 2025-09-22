{{-- Real-Time Ticket Updates Component --}}
{{-- Integrates with Laravel Echo for live price, availability, and status updates --}}

<div x-data="realtimeTicketUpdates()" x-init="init()" class="realtime-ticket-updates">
    {{-- Connection Status Indicator --}}
    <div class="fixed top-4 right-4 z-50" x-show="showConnectionStatus">
        <div 
            class="bg-white rounded-lg shadow-lg p-3 border-l-4 transition-all duration-300"
            :class="getConnectionStatusClass()"
            data-connection-status="disconnected"
        >
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <div 
                        class="w-3 h-3 rounded-full transition-all duration-300"
                        :class="getStatusIconClass()"
                        data-status-icon
                    ></div>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900">
                        <span data-status-text>Connecting...</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Live Updates <span x-show="isConnected">Active</span><span x-show="!isConnected">Inactive</span>
                    </div>
                </div>
                <button 
                    @click="showConnectionStatus = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Live Price Alert Toasts --}}
    <div class="fixed top-4 left-4 z-40 space-y-2" x-show="priceAlerts.length > 0">
        <template x-for="alert in priceAlerts" :key="alert.id">
            <div 
                x-show="alert.visible"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg 
                                class="h-6 w-6" 
                                :class="alert.type === 'price_drop' ? 'text-green-400' : alert.type === 'price_increase' ? 'text-red-400' : 'text-blue-400'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="alert.type === 'price_drop' ? 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' : 
                                          alert.type === 'price_increase' ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 
                                          'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="alert.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="alert.message"></p>
                            <div class="mt-2 flex items-center text-xs text-gray-400">
                                <span x-text="alert.eventName"></span>
                                <span class="mx-2">•</span>
                                <span x-text="formatTime(alert.timestamp)"></span>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button 
                                @click="dismissAlert(alert.id)"
                                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Availability Alert Banner --}}
    <div 
        x-show="availabilityAlert.visible" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-0 left-0 right-0 z-50"
    >
        <div 
            class="p-4 text-center text-white font-medium"
            :class="availabilityAlert.type === 'low_stock' ? 'bg-orange-600' : availabilityAlert.type === 'sold_out' ? 'bg-red-600' : 'bg-green-600'"
        >
            <div class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span x-text="availabilityAlert.message"></span>
                <button 
                    @click="availabilityAlert.visible = false"
                    class="ml-4 text-white hover:text-gray-200"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Live Activity Feed (for Dashboard) --}}
    <div x-show="showActivityFeed" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                Live Activity Feed
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Auto-refresh:</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                        type="checkbox" 
                        x-model="autoRefreshFeed" 
                        @change="toggleAutoRefresh()"
                        class="sr-only peer"
                    >
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
        
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <template x-for="activity in activityFeed.slice(0, 10)" :key="activity.id">
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex-shrink-0 mt-1">
                        <div 
                            class="w-2 h-2 rounded-full"
                            :class="getActivityTypeColor(activity.type)"
                        ></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <p class="text-sm text-gray-900" x-html="activity.message"></p>
                            <span class="text-xs text-gray-500 whitespace-nowrap ml-2" x-text="formatRelativeTime(activity.timestamp)"></span>
                        </div>
                        <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                            <span x-text="activity.eventName || 'System'"></span>
                            <span x-show="activity.venue">• <span x-text="activity.venue"></span></span>
                            <span x-show="activity.price">• <span x-text="activity.price"></span></span>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-show="activityFeed.length === 0" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4zM9 3v1h6V3H9zm2 8a1 1 0 112 0v4a1 1 0 11-2 0v-4z"></path>
                </svg>
                <p>No recent activity</p>
            </div>
        </div>
    </div>

    {{-- Debug Panel (Development Only) --}}
    <div x-show="debugMode && isDebug" class="fixed bottom-4 right-4 bg-black bg-opacity-90 text-white rounded-lg p-4 max-w-sm text-xs">
        <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold">WebSocket Debug</h4>
            <button @click="debugMode = false" class="text-gray-400 hover:text-white">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-1">
            <div>Status: <span :class="isConnected ? 'text-green-400' : 'text-red-400'" x-text="connectionStatus"></span></div>
            <div>Socket ID: <span class="text-gray-300" x-text="socketId || 'N/A'"></span></div>
            <div>Channels: <span class="text-blue-300" x-text="subscribedChannels.length"></span></div>
            <div>Messages: <span class="text-yellow-300" x-text="messageCount"></span></div>
            <div>Last Event: <span class="text-purple-300" x-text="lastEventTime"></span></div>
        </div>
        <div class="mt-2 flex gap-2">
            <button @click="testConnection()" class="px-2 py-1 bg-blue-600 rounded text-xs">Test</button>
            <button @click="clearDebugData()" class="px-2 py-1 bg-gray-600 rounded text-xs">Clear</button>
        </div>
    </div>
</div>

<script>
function realtimeTicketUpdates() {
    return {
        // Connection state
        isConnected: false,
        connectionStatus: 'disconnected',
        socketId: null,
        showConnectionStatus: false,
        
        // Subscribed channels tracking
        subscribedChannels: [],
        messageCount: 0,
        lastEventTime: '',
        
        // UI state
        autoRefreshFeed: true,
        showActivityFeed: true,
        debugMode: false,
        isDebug: document.documentElement.getAttribute('data-debug') === 'true',
        
        // Notifications and alerts
        priceAlerts: [],
        availabilityAlert: {
            visible: false,
            type: '',
            message: ''
        },
        
        // Activity feed
        activityFeed: [],
        maxActivityItems: 50,
        
        // Timers
        connectionCheckInterval: null,
        activityRefreshInterval: null,
        
        init() {
            this.setupConnectionListeners();
            this.startConnectionCheck();
            this.subscribeToGlobalChannels();
            
            // Initialize based on current page context
            this.initializePageSpecificSubscriptions();
            
            console.log('[RealTimeUpdates] Initialized');
        },
        
        setupConnectionListeners() {
            // Connection established
            document.addEventListener('echo:connected', (event) => {
                this.isConnected = true;
                this.connectionStatus = 'connected';
                this.socketId = event.detail?.socketId || null;
                this.showConnectionStatus = false;
                
                this.addActivity({
                    type: 'connection',
                    message: '✅ Connected to live updates',
                    timestamp: Date.now()
                });
                
                console.log('✅ Real-time updates connected');
            });
            
            // Connection lost
            document.addEventListener('echo:disconnected', () => {
                this.isConnected = false;
                this.connectionStatus = 'disconnected';
                this.socketId = null;
                this.showConnectionStatus = true;
                
                this.addActivity({
                    type: 'connection',
                    message: '❌ Live updates disconnected',
                    timestamp: Date.now()
                });
                
                console.log('❌ Real-time updates disconnected');
            });
            
            // Connection error
            document.addEventListener('echo:error', (event) => {
                this.connectionStatus = 'error';
                this.showConnectionStatus = true;
                
                this.addActivity({
                    type: 'error',
                    message: `🔴 Connection error: ${event.detail?.error || 'Unknown'}`,
                    timestamp: Date.now()
                });
                
                console.error('🔴 WebSocket error:', event.detail?.error);
            });
            
            // Connection state changes
            document.addEventListener('echo:state_change', (event) => {
                const { current, previous } = event.detail;
                this.connectionStatus = current;
                
                if (current === 'connecting') {
                    this.showConnectionStatus = true;
                }
                
                this.addActivity({
                    type: 'connection',
                    message: `🔄 Connection: ${previous} → ${current}`,
                    timestamp: Date.now()
                });
            });
        },
        
        subscribeToGlobalChannels() {
            if (!window.EchoHelpers) {
                console.warn('[RealTimeUpdates] EchoHelpers not available');
                return;
            }
            
            // Subscribe to system announcements
            const systemChannel = window.EchoHelpers.subscribeToSystemAnnouncements({
                onAnnouncement: (event) => this.handleSystemAnnouncement(event),
                onMaintenance: (event) => this.handleMaintenanceNotification(event),
                onServiceUpdate: (event) => this.handleServiceUpdate(event)
            });
            
            this.subscribedChannels.push('system.announcements');
            
            // Subscribe to global ticket events for activity feed
            if (window.Echo) {
                const globalTicketChannel = window.Echo.channel('tickets.global');
                
                globalTicketChannel
                    .listen('TicketPriceChanged', (event) => this.handleGlobalPriceChange(event))
                    .listen('TicketAvailabilityChanged', (event) => this.handleGlobalAvailabilityChange(event))
                    .listen('TicketStatusChanged', (event) => this.handleGlobalStatusChange(event))
                    .listen('NewTicketListing', (event) => this.handleNewTicketListing(event));
                
                this.subscribedChannels.push('tickets.global');
            }
        },
        
        initializePageSpecificSubscriptions() {
            const currentPage = document.body.getAttribute('data-page') || '';
            const userId = document.body.getAttribute('data-user-id');
            
            // User-specific subscriptions
            if (userId && window.EchoHelpers) {
                const userChannel = window.EchoHelpers.subscribeToUserNotifications(userId, {
                    onPriceAlert: (event) => this.handlePriceAlert(event),
                    onBookmarkUpdate: (event) => this.handleBookmarkUpdate(event),
                    onNotification: (event) => this.handleUserNotification(event)
                });
                
                this.subscribedChannels.push(`user.${userId}`);
            }
            
            // Page-specific subscriptions
            if (currentPage.includes('tickets')) {
                this.subscribeToTicketPageUpdates();
            } else if (currentPage.includes('dashboard')) {
                this.showActivityFeed = true;
                this.startActivityRefresh();
            }
        },
        
        subscribeToTicketPageUpdates() {
            // Subscribe to individual ticket updates based on visible tickets
            const ticketCards = document.querySelectorAll('[data-ticket-id]');
            
            ticketCards.forEach(card => {
                const ticketId = card.getAttribute('data-ticket-id');
                if (ticketId && window.EchoHelpers) {
                    const channel = window.EchoHelpers.subscribeToTicket(ticketId, {
                        onPriceChange: (event) => this.handleTicketPriceChange(event, ticketId),
                        onAvailabilityChange: (event) => this.handleTicketAvailabilityChange(event, ticketId),
                        onStatusChange: (event) => this.handleTicketStatusChange(event, ticketId),
                        onUpdate: (event) => this.handleTicketUpdate(event, ticketId)
                    });
                    
                    this.subscribedChannels.push(`ticket.${ticketId}`);
                }
            });
        },
        
        // Event Handlers
        handleSystemAnnouncement(event) {
            this.showAlert({
                type: 'announcement',
                title: 'System Announcement',
                message: event.message,
                timestamp: Date.now()
            });
            
            this.addActivity({
                type: 'announcement',
                message: `📢 ${event.message}`,
                timestamp: Date.now()
            });
        },
        
        handleMaintenanceNotification(event) {
            this.showAlert({
                type: 'maintenance',
                title: 'Scheduled Maintenance',
                message: `Maintenance scheduled for ${new Date(event.scheduled_at).toLocaleString()}`,
                timestamp: Date.now()
            });
        },
        
        handleServiceUpdate(event) {
            this.addActivity({
                type: 'service',
                message: `🔧 Service update: ${event.service} - ${event.status}`,
                timestamp: Date.now()
            });
        },
        
        handleGlobalPriceChange(event) {
            this.addActivity({
                type: 'price',
                message: `💰 Price ${event.old_price > event.new_price ? 'dropped' : 'increased'}: <strong>${event.event_name}</strong>`,
                eventName: event.event_name,
                venue: event.venue,
                price: `${this.formatCurrency(event.old_price)} → ${this.formatCurrency(event.new_price)}`,
                timestamp: Date.now()
            });
            
            this.messageCount++;
            this.lastEventTime = this.formatTime(Date.now());
        },
        
        handleGlobalAvailabilityChange(event) {
            this.addActivity({
                type: 'availability',
                message: `🎫 Availability changed: <strong>${event.event_name}</strong> - ${event.available_tickets} tickets available`,
                eventName: event.event_name,
                venue: event.venue,
                timestamp: Date.now()
            });
            
            // Show banner for critical availability changes
            if (event.available_tickets <= 10 && event.available_tickets > 0) {
                this.showAvailabilityAlert('low_stock', `Only ${event.available_tickets} tickets left for ${event.event_name}!`);
            } else if (event.available_tickets === 0) {
                this.showAvailabilityAlert('sold_out', `${event.event_name} is now sold out!`);
            }
        },
        
        handleGlobalStatusChange(event) {
            this.addActivity({
                type: 'status',
                message: `📊 Status changed: <strong>${event.event_name}</strong> - ${event.old_status} → ${event.new_status}`,
                eventName: event.event_name,
                timestamp: Date.now()
            });
        },
        
        handleNewTicketListing(event) {
            this.addActivity({
                type: 'new_listing',
                message: `🆕 New listing: <strong>${event.event_name}</strong>`,
                eventName: event.event_name,
                venue: event.venue,
                price: this.formatCurrency(event.price),
                timestamp: Date.now()
            });
        },
        
        handlePriceAlert(event) {
            this.showAlert({
                type: event.alert_type, // 'price_drop', 'price_increase', 'target_price'
                title: event.title,
                message: event.message,
                eventName: event.event_name,
                timestamp: Date.now()
            });
        },
        
        handleTicketPriceChange(event, ticketId) {
            // Update price in the UI
            const ticketCard = document.querySelector(`[data-ticket-id="${ticketId}"]`);
            if (ticketCard) {
                const priceElement = ticketCard.querySelector('[data-ticket-price]');
                if (priceElement) {
                    // Animate price change
                    priceElement.classList.add('animate-pulse');
                    priceElement.textContent = this.formatCurrency(event.new_price);
                    
                    // Add price change indicator
                    const changeIndicator = document.createElement('span');
                    changeIndicator.className = `ml-1 text-xs ${event.new_price < event.old_price ? 'text-green-600' : 'text-red-600'}`;
                    changeIndicator.textContent = event.new_price < event.old_price ? '↓' : '↑';
                    priceElement.appendChild(changeIndicator);
                    
                    // Remove animations after delay
                    setTimeout(() => {
                        priceElement.classList.remove('animate-pulse');
                        changeIndicator.remove();
                    }, 3000);
                }
                
                // Update last updated timestamp
                const timestampElement = ticketCard.querySelector('[data-last-updated]');
                if (timestampElement) {
                    timestampElement.textContent = 'Just updated';
                    timestampElement.classList.add('text-green-600', 'font-medium');
                }
            }
        },
        
        handleTicketAvailabilityChange(event, ticketId) {
            const ticketCard = document.querySelector(`[data-ticket-id="${ticketId}"]`);
            if (ticketCard) {
                const availabilityElement = ticketCard.querySelector('[data-availability]');
                if (availabilityElement) {
                    availabilityElement.textContent = `${event.available_tickets} available`;
                    
                    // Update styling based on availability
                    if (event.available_tickets <= 5) {
                        availabilityElement.className = 'text-xs text-red-600 font-medium';
                    } else if (event.available_tickets <= 20) {
                        availabilityElement.className = 'text-xs text-orange-600';
                    } else {
                        availabilityElement.className = 'text-xs text-gray-600';
                    }
                }
            }
        },
        
        handleTicketStatusChange(event, ticketId) {
            const ticketCard = document.querySelector(`[data-ticket-id="${ticketId}"]`);
            if (ticketCard) {
                if (event.new_status === 'sold_out') {
                    ticketCard.classList.add('opacity-50', 'pointer-events-none');
                    
                    // Add sold out overlay
                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center rounded-lg';
                    overlay.innerHTML = '<span class="text-white font-bold">SOLD OUT</span>';
                    ticketCard.style.position = 'relative';
                    ticketCard.appendChild(overlay);
                } else if (event.old_status === 'sold_out' && event.new_status === 'available') {
                    // Remove sold out styling
                    ticketCard.classList.remove('opacity-50', 'pointer-events-none');
                    const overlay = ticketCard.querySelector('.absolute.inset-0');
                    if (overlay) overlay.remove();
                }
            }
        },
        
        // UI Helper Methods
        showAlert(alertData) {
            const alert = {
                id: Date.now() + Math.random(),
                visible: true,
                ...alertData
            };
            
            this.priceAlerts.push(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                this.dismissAlert(alert.id);
            }, 5000);
        },
        
        dismissAlert(alertId) {
            const alertIndex = this.priceAlerts.findIndex(alert => alert.id === alertId);
            if (alertIndex > -1) {
                this.priceAlerts[alertIndex].visible = false;
                
                // Remove from array after animation
                setTimeout(() => {
                    this.priceAlerts.splice(alertIndex, 1);
                }, 300);
            }
        },
        
        showAvailabilityAlert(type, message) {
            this.availabilityAlert = {
                visible: true,
                type: type,
                message: message
            };
            
            // Auto-dismiss after 7 seconds
            setTimeout(() => {
                this.availabilityAlert.visible = false;
            }, 7000);
        },
        
        addActivity(activity) {
            activity.id = Date.now() + Math.random();
            this.activityFeed.unshift(activity);
            
            // Keep only the most recent items
            if (this.activityFeed.length > this.maxActivityItems) {
                this.activityFeed = this.activityFeed.slice(0, this.maxActivityItems);
            }
        },
        
        startConnectionCheck() {
            this.connectionCheckInterval = setInterval(() => {
                if (window.EchoHelpers) {
                    this.isConnected = window.EchoHelpers.isConnected();
                    this.socketId = window.EchoHelpers.getSocketId();
                }
            }, 5000);
        },
        
        startActivityRefresh() {
            if (this.autoRefreshFeed) {
                this.activityRefreshInterval = setInterval(() => {
                    // Refresh activity feed if needed
                }, 30000);
            }
        },
        
        toggleAutoRefresh() {
            if (this.autoRefreshFeed) {
                this.startActivityRefresh();
            } else {
                if (this.activityRefreshInterval) {
                    clearInterval(this.activityRefreshInterval);
                    this.activityRefreshInterval = null;
                }
            }
        },
        
        // Utility Methods
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString();
        },
        
        formatRelativeTime(timestamp) {
            const now = Date.now();
            const diff = now - timestamp;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (diff < 60000) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            return `${days}d ago`;
        },
        
        getConnectionStatusClass() {
            const classes = {
                connected: 'border-green-500',
                disconnected: 'border-red-500',
                connecting: 'border-yellow-500',
                error: 'border-red-600',
                unavailable: 'border-gray-500'
            };
            return classes[this.connectionStatus] || 'border-gray-400';
        },
        
        getStatusIconClass() {
            const classes = {
                connected: 'bg-green-500',
                disconnected: 'bg-red-500',
                connecting: 'bg-yellow-500 animate-pulse',
                error: 'bg-red-600 animate-bounce',
                unavailable: 'bg-gray-500'
            };
            return classes[this.connectionStatus] || 'bg-gray-400';
        },
        
        getActivityTypeColor(type) {
            const colors = {
                connection: 'bg-blue-500',
                price: 'bg-green-500',
                availability: 'bg-orange-500',
                status: 'bg-purple-500',
                new_listing: 'bg-teal-500',
                announcement: 'bg-indigo-500',
                service: 'bg-gray-500',
                error: 'bg-red-500'
            };
            return colors[type] || 'bg-gray-400';
        },
        
        // Debug Methods
        testConnection() {
            if (window.EchoHelpers) {
                console.log('Testing WebSocket connection...');
                console.log('Connected:', window.EchoHelpers.isConnected());
                console.log('Socket ID:', window.EchoHelpers.getSocketId());
                console.log('Channels:', this.subscribedChannels);
            }
        },
        
        clearDebugData() {
            this.messageCount = 0;
            this.lastEventTime = '';
            this.activityFeed = [];
        },
        
        // Cleanup
        destroy() {
            if (this.connectionCheckInterval) {
                clearInterval(this.connectionCheckInterval);
            }
            if (this.activityRefreshInterval) {
                clearInterval(this.activityRefreshInterval);
            }
            
            // Leave all subscribed channels
            this.subscribedChannels.forEach(channel => {
                if (window.EchoHelpers) {
                    window.EchoHelpers.leaveChannel(channel);
                }
            });
        }
    };
}

// Auto-initialize debug mode based on environment
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
        document.documentElement.setAttribute('data-debug', 'true');
    }
});
</script>