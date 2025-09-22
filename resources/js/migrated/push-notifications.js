/**
 * HD Tickets Push Notifications Service
 * 
 * Comprehensive push notifications system for Progressive Web App
 * Features:
 * - Subscription management
 * - Notification targeting
 * - Browser compatibility detection
 * - Permission handling
 * - Notification customization
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 */

class HDTicketsPushNotifications {
    constructor() {
        this.config = {
            // Server endpoints for push subscription management
            endpoints: {
                subscribe: '/api/v1/push/subscribe',
                unsubscribe: '/api/v1/push/unsubscribe',
                status: '/api/v1/push/status'
            },
            
            // Notification preferences
            preferences: {
                priceAlerts: true,
                availabilityAlerts: true,
                eventReminders: true,
                systemUpdates: false
            },
            
            // Browser support detection
            isSupported: this.checkSupport(),
            
            // Current subscription status
            isSubscribed: false,
            subscription: null,
            
            // VAPID public key (to be set by server)
            vapidPublicKey: null
        };
        
        this.init();
    }
    
    /**
     * Initialize the push notifications system
     */
    async init() {
        try {
            console.log('[Push Notifications] Initializing...');
            
            if (!this.config.isSupported) {
                console.warn('[Push Notifications] Push notifications not supported in this browser');
                this.showUnsupportedMessage();
                return;
            }
            
            // Load VAPID public key from server
            await this.loadVapidKey();
            
            // Check current subscription status
            await this.checkSubscriptionStatus();
            
            // Initialize UI components
            this.initializeUI();
            
            // Set up event listeners
            this.setupEventListeners();
            
            console.log('[Push Notifications] Initialization completed');
        } catch (error) {
            console.error('[Push Notifications] Initialization failed:', error);
            this.showErrorMessage('Failed to initialize push notifications');
        }
    }
    
    /**
     * Check if push notifications are supported
     */
    checkSupport() {
        return 'serviceWorker' in navigator &&
               'PushManager' in window &&
               'Notification' in window;
    }
    
    /**
     * Load VAPID public key from server
     */
    async loadVapidKey() {
        try {
            const response = await fetch('/api/v1/push/vapid-key');
            const data = await response.json();
            
            if (data.success && data.publicKey) {
                this.config.vapidPublicKey = data.publicKey;
                console.log('[Push Notifications] VAPID key loaded successfully');
            } else {
                throw new Error('Failed to load VAPID key');
            }
        } catch (error) {
            console.error('[Push Notifications] Failed to load VAPID key:', error);
            throw error;
        }
    }
    
    /**
     * Check current subscription status
     */
    async checkSubscriptionStatus() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            
            if (subscription) {
                this.config.isSubscribed = true;
                this.config.subscription = subscription;
                console.log('[Push Notifications] User is subscribed');
                
                // Verify subscription with server
                await this.verifySubscriptionWithServer(subscription);
            } else {
                this.config.isSubscribed = false;
                console.log('[Push Notifications] User is not subscribed');
            }
            
            this.updateUI();
        } catch (error) {
            console.error('[Push Notifications] Failed to check subscription status:', error);
        }
    }
    
    /**
     * Verify subscription with server
     */
    async verifySubscriptionWithServer(subscription) {
        try {
            const response = await fetch(this.config.endpoints.status, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    subscription: subscription.toJSON()
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                console.warn('[Push Notifications] Subscription not found on server, re-subscribing...');
                await this.sendSubscriptionToServer(subscription);
            }
        } catch (error) {
            console.error('[Push Notifications] Failed to verify subscription:', error);
        }
    }
    
    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        try {
            console.log('[Push Notifications] Starting subscription process...');
            
            // Request notification permission
            const permission = await this.requestPermission();
            
            if (permission !== 'granted') {
                throw new Error('Notification permission denied');
            }
            
            // Get service worker registration
            const registration = await navigator.serviceWorker.ready;
            
            // Subscribe to push manager
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.config.vapidPublicKey)
            });
            
            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
            // Update local state
            this.config.isSubscribed = true;
            this.config.subscription = subscription;
            
            // Update UI
            this.updateUI();
            
            // Show success message
            this.showSuccessMessage('Push notifications enabled successfully!');
            
            // Track analytics event
            this.trackEvent('push_notification_subscribed');
            
            console.log('[Push Notifications] Subscription completed successfully');
            
        } catch (error) {
            console.error('[Push Notifications] Subscription failed:', error);
            this.showErrorMessage('Failed to enable push notifications: ' + error.message);
        }
    }
    
    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            console.log('[Push Notifications] Starting unsubscription process...');
            
            if (!this.config.subscription) {
                throw new Error('No active subscription found');
            }
            
            // Unsubscribe from push manager
            const successful = await this.config.subscription.unsubscribe();
            
            if (successful) {
                // Remove subscription from server
                await this.removeSubscriptionFromServer(this.config.subscription);
                
                // Update local state
                this.config.isSubscribed = false;
                this.config.subscription = null;
                
                // Update UI
                this.updateUI();
                
                // Show success message
                this.showSuccessMessage('Push notifications disabled successfully');
                
                // Track analytics event
                this.trackEvent('push_notification_unsubscribed');
                
                console.log('[Push Notifications] Unsubscription completed successfully');
            } else {
                throw new Error('Failed to unsubscribe from push manager');
            }
            
        } catch (error) {
            console.error('[Push Notifications] Unsubscription failed:', error);
            this.showErrorMessage('Failed to disable push notifications: ' + error.message);
        }
    }
    
    /**
     * Request notification permission
     */
    async requestPermission() {
        if (!('Notification' in window)) {
            throw new Error('This browser does not support notifications');
        }
        
        let permission = Notification.permission;
        
        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }
        
        return permission;
    }
    
    /**
     * Send subscription to server
     */
    async sendSubscriptionToServer(subscription) {
        const response = await fetch(this.config.endpoints.subscribe, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                subscription: subscription.toJSON(),
                preferences: this.config.preferences,
                user_agent: navigator.userAgent,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to save subscription on server');
        }
        
        return data;
    }
    
    /**
     * Remove subscription from server
     */
    async removeSubscriptionFromServer(subscription) {
        const response = await fetch(this.config.endpoints.unsubscribe, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                subscription: subscription.toJSON()
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.warn('[Push Notifications] Failed to remove subscription from server:', data.message);
        }
        
        return data;
    }
    
    /**
     * Update notification preferences
     */
    async updatePreferences(preferences) {
        try {
            this.config.preferences = { ...this.config.preferences, ...preferences };
            
            if (this.config.isSubscribed) {
                // Send updated preferences to server
                const response = await fetch('/api/v1/push/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        subscription: this.config.subscription.toJSON(),
                        preferences: this.config.preferences
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccessMessage('Notification preferences updated successfully');
                    this.trackEvent('push_notification_preferences_updated');
                } else {
                    throw new Error(data.message || 'Failed to update preferences');
                }
            }
            
            // Update UI
            this.updatePreferencesUI();
            
        } catch (error) {
            console.error('[Push Notifications] Failed to update preferences:', error);
            this.showErrorMessage('Failed to update notification preferences');
        }
    }
    
    /**
     * Send test notification
     */
    async sendTestNotification() {
        try {
            if (!this.config.isSubscribed) {
                throw new Error('Not subscribed to push notifications');
            }
            
            const response = await fetch('/api/v1/push/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    subscription: this.config.subscription.toJSON()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccessMessage('Test notification sent! Check your notifications.');
                this.trackEvent('push_notification_test_sent');
            } else {
                throw new Error(data.message || 'Failed to send test notification');
            }
            
        } catch (error) {
            console.error('[Push Notifications] Failed to send test notification:', error);
            this.showErrorMessage('Failed to send test notification: ' + error.message);
        }
    }
    
    /**
     * Initialize UI components
     */
    initializeUI() {
        // Create notification settings panel if it doesn't exist
        if (!document.getElementById('push-notifications-panel')) {
            this.createNotificationPanel();
        }
        
        this.updateUI();
    }
    
    /**
     * Create notification settings panel
     */
    createNotificationPanel() {
        const panel = document.createElement('div');
        panel.id = 'push-notifications-panel';
        panel.className = 'push-notifications-panel hidden';
        panel.innerHTML = `
            <div class="push-notifications-content">
                <div class="push-notifications-header">
                    <h3>Push Notifications</h3>
                    <button class="close-btn" onclick="hdPushNotifications.hidePanel()">×</button>
                </div>
                
                <div class="push-notifications-body">
                    <div class="subscription-status">
                        <div class="status-indicator"></div>
                        <span class="status-text"></span>
                    </div>
                    
                    <div class="subscription-actions">
                        <button id="subscribe-btn" class="btn btn-primary">Enable Notifications</button>
                        <button id="unsubscribe-btn" class="btn btn-secondary hidden">Disable Notifications</button>
                        <button id="test-btn" class="btn btn-outline hidden">Send Test</button>
                    </div>
                    
                    <div class="notification-preferences">
                        <h4>Notification Types</h4>
                        <label class="preference-item">
                            <input type="checkbox" id="price-alerts" checked>
                            <span>Price Alerts</span>
                        </label>
                        <label class="preference-item">
                            <input type="checkbox" id="availability-alerts" checked>
                            <span>Availability Alerts</span>
                        </label>
                        <label class="preference-item">
                            <input type="checkbox" id="event-reminders" checked>
                            <span>Event Reminders</span>
                        </label>
                        <label class="preference-item">
                            <input type="checkbox" id="system-updates">
                            <span>System Updates</span>
                        </label>
                    </div>
                    
                    <div class="browser-compatibility">
                        <small class="compatibility-info"></small>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(panel);
    }
    
    /**
     * Update UI based on current state
     */
    updateUI() {
        const statusIndicator = document.querySelector('.status-indicator');
        const statusText = document.querySelector('.status-text');
        const subscribeBtn = document.getElementById('subscribe-btn');
        const unsubscribeBtn = document.getElementById('unsubscribe-btn');
        const testBtn = document.getElementById('test-btn');
        const compatibilityInfo = document.querySelector('.compatibility-info');
        
        if (!statusIndicator) return; // Panel not created yet
        
        if (this.config.isSubscribed) {
            statusIndicator.className = 'status-indicator active';
            statusText.textContent = 'Notifications Enabled';
            subscribeBtn?.classList.add('hidden');
            unsubscribeBtn?.classList.remove('hidden');
            testBtn?.classList.remove('hidden');
        } else {
            statusIndicator.className = 'status-indicator inactive';
            statusText.textContent = 'Notifications Disabled';
            subscribeBtn?.classList.remove('hidden');
            unsubscribeBtn?.classList.add('hidden');
            testBtn?.classList.add('hidden');
        }
        
        // Update compatibility info
        if (compatibilityInfo) {
            if (this.config.isSupported) {
                compatibilityInfo.textContent = 'Push notifications are supported in your browser';
                compatibilityInfo.className = 'compatibility-info supported';
            } else {
                compatibilityInfo.textContent = 'Push notifications are not supported in your browser';
                compatibilityInfo.className = 'compatibility-info unsupported';
            }
        }
        
        this.updatePreferencesUI();
    }
    
    /**
     * Update preferences UI
     */
    updatePreferencesUI() {
        const priceAlertsCheckbox = document.getElementById('price-alerts');
        const availabilityAlertsCheckbox = document.getElementById('availability-alerts');
        const eventRemindersCheckbox = document.getElementById('event-reminders');
        const systemUpdatesCheckbox = document.getElementById('system-updates');
        
        if (priceAlertsCheckbox) {
            priceAlertsCheckbox.checked = this.config.preferences.priceAlerts;
        }
        if (availabilityAlertsCheckbox) {
            availabilityAlertsCheckbox.checked = this.config.preferences.availabilityAlerts;
        }
        if (eventRemindersCheckbox) {
            eventRemindersCheckbox.checked = this.config.preferences.eventReminders;
        }
        if (systemUpdatesCheckbox) {
            systemUpdatesCheckbox.checked = this.config.preferences.systemUpdates;
        }
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Subscribe button
        const subscribeBtn = document.getElementById('subscribe-btn');
        if (subscribeBtn) {
            subscribeBtn.addEventListener('click', () => this.subscribe());
        }
        
        // Unsubscribe button
        const unsubscribeBtn = document.getElementById('unsubscribe-btn');
        if (unsubscribeBtn) {
            unsubscribeBtn.addEventListener('click', () => this.unsubscribe());
        }
        
        // Test button
        const testBtn = document.getElementById('test-btn');
        if (testBtn) {
            testBtn.addEventListener('click', () => this.sendTestNotification());
        }
        
        // Preference checkboxes
        const preferenceCheckboxes = document.querySelectorAll('.notification-preferences input[type="checkbox"]');
        preferenceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const preferences = {
                    priceAlerts: document.getElementById('price-alerts')?.checked || false,
                    availabilityAlerts: document.getElementById('availability-alerts')?.checked || false,
                    eventReminders: document.getElementById('event-reminders')?.checked || false,
                    systemUpdates: document.getElementById('system-updates')?.checked || false
                };
                
                this.updatePreferences(preferences);
            });
        });
        
        // Listen for service worker messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                const { type, payload } = event.data;
                
                if (type === 'PUSH_NOTIFICATION_CLICKED') {
                    this.handleNotificationClick(payload);
                }
            });
        }
    }
    
    /**
     * Show notification panel
     */
    showPanel() {
        const panel = document.getElementById('push-notifications-panel');
        if (panel) {
            panel.classList.remove('hidden');
        }
    }
    
    /**
     * Hide notification panel
     */
    hidePanel() {
        const panel = document.getElementById('push-notifications-panel');
        if (panel) {
            panel.classList.add('hidden');
        }
    }
    
    /**
     * Handle notification click events
     */
    handleNotificationClick(payload) {
        console.log('[Push Notifications] Notification clicked:', payload);
        
        // Track click event
        this.trackEvent('push_notification_clicked', {
            notification_type: payload.type,
            notification_id: payload.id
        });
        
        // Handle different notification types
        switch (payload.type) {
            case 'price-alert':
                if (payload.ticket_id) {
                    window.location.href = `/tickets/${payload.ticket_id}`;
                }
                break;
                
            case 'availability':
                if (payload.event_id) {
                    window.location.href = `/events/${payload.event_id}`;
                }
                break;
                
            case 'event-reminder':
                if (payload.event_id) {
                    window.location.href = `/events/${payload.event_id}`;
                }
                break;
                
            default:
                window.location.href = '/dashboard';
        }
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.showMessage(message, 'success');
    }
    
    /**
     * Show error message
     */
    showErrorMessage(message) {
        this.showMessage(message, 'error');
    }
    
    /**
     * Show unsupported browser message
     */
    showUnsupportedMessage() {
        this.showMessage('Push notifications are not supported in your browser. Please use a modern browser for the best experience.', 'warning');
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        // Use existing notification system if available
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            // Fallback to console and alert
            console.log(`[Push Notifications] ${type.toUpperCase()}: ${message}`);
            if (type === 'error') {
                alert(message);
            }
        }
    }
    
    /**
     * Track analytics events
     */
    trackEvent(eventName, properties = {}) {
        // Use existing analytics system if available
        if (window.analytics && typeof window.analytics.track === 'function') {
            window.analytics.track(eventName, {
                category: 'Push Notifications',
                ...properties
            });
        } else {
            console.log('[Push Notifications] Analytics event:', eventName, properties);
        }
    }
    
    /**
     * Convert VAPID key from base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        
        return outputArray;
    }
    
    /**
     * Get subscription status for external use
     */
    getStatus() {
        return {
            isSupported: this.config.isSupported,
            isSubscribed: this.config.isSubscribed,
            permission: Notification.permission,
            preferences: this.config.preferences
        };
    }
    
    /**
     * Check if notifications are blocked
     */
    isBlocked() {
        return Notification.permission === 'denied';
    }
    
    /**
     * Reset notification permission (requires user action)
     */
    showPermissionHelp() {
        const helpMessage = `
            To enable push notifications:
            
            1. Click the lock icon in your browser's address bar
            2. Select "Allow" for notifications
            3. Refresh the page
            
            Or go to your browser settings and enable notifications for this site.
        `;
        
        alert(helpMessage);
    }
}

// Initialize push notifications system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.hdPushNotifications = new HDTicketsPushNotifications();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HDTicketsPushNotifications;
}
