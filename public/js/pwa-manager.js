/**
 * PWA Manager - Service Worker Registration and Management
 * Handles service worker lifecycle, push notifications, and offline functionality
 */

class PWAManager {
    constructor() {
        this.swRegistration = null;
        this.isOnline = navigator.onLine;
        this.pushSupported = 'PushManager' in window;
        this.notificationSupported = 'Notification' in window;
        this.syncSupported = 'serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype;
        
        this.init();
    }

    async init() {
        if ('serviceWorker' in navigator) {
            try {
                await this.registerServiceWorker();
                this.setupEventListeners();
                this.setupPushNotifications();
                this.checkForUpdates();
                
                console.log('[PWA] Initialized successfully');
            } catch (error) {
                console.error('[PWA] Initialization failed:', error);
            }
        } else {
            console.warn('[PWA] Service Workers not supported');
        }
    }

    async registerServiceWorker() {
        try {
            // Try to register enhanced service worker first, fall back to default
            let swUrl = '/enhanced-sw.js';
            
            this.swRegistration = await navigator.serviceWorker.register(swUrl, {
                scope: '/',
                updateViaCache: 'none'
            });

            console.log('[PWA] Service Worker registered:', this.swRegistration);

            // Handle service worker updates
            this.swRegistration.addEventListener('updatefound', () => {
                const newWorker = this.swRegistration.installing;
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        this.showUpdateNotification();
                    }
                });
            });

            return this.swRegistration;
        } catch (error) {
            console.error('[PWA] Service Worker registration failed:', error);
            throw error;
        }
    }

    setupEventListeners() {
        // Online/offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.handleOnlineStatusChange(true);
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.handleOnlineStatusChange(false);
        });

        // Service worker messages
        navigator.serviceWorker.addEventListener('message', (event) => {
            this.handleServiceWorkerMessage(event);
        });

        // Before install prompt (for PWA installation)
        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            this.deferredPrompt = event;
            this.showInstallPrompt();
        });

        // App installed
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.hideInstallPrompt();
            this.trackEvent('pwa_installed');
        });
    }

    async setupPushNotifications() {
        if (!this.pushSupported || !this.notificationSupported) {
            console.warn('[PWA] Push notifications not supported');
            return;
        }

        // Check if user already granted permission
        if (Notification.permission === 'granted') {
            await this.subscribeToPush();
        }
    }

    async requestNotificationPermission() {
        if (!this.notificationSupported) {
            throw new Error('Notifications not supported');
        }

        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            console.log('[PWA] Notification permission granted');
            await this.subscribeToPush();
            this.trackEvent('notification_permission_granted');
            return true;
        } else {
            console.log('[PWA] Notification permission denied');
            this.trackEvent('notification_permission_denied');
            return false;
        }
    }

    async subscribeToPush() {
        if (!this.swRegistration || !this.pushSupported) {
            return null;
        }

        try {
            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(window.vapidPublicKey || '')
            });

            console.log('[PWA] Push subscription created:', subscription);
            
            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
            return subscription;
        } catch (error) {
            console.error('[PWA] Failed to subscribe to push notifications:', error);
            return null;
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(subscription)
            });

            if (!response.ok) {
                throw new Error('Failed to send subscription to server');
            }

            console.log('[PWA] Subscription sent to server');
        } catch (error) {
            console.error('[PWA] Failed to send subscription to server:', error);
        }
    }

    async backgroundSync(tag, data) {
        if (!this.syncSupported || !this.swRegistration) {
            console.warn('[PWA] Background sync not supported');
            return false;
        }

        try {
            // Store data for background sync
            await this.storeForSync(tag, data);
            
            // Register for background sync
            await this.swRegistration.sync.register(tag);
            
            console.log('[PWA] Background sync registered:', tag);
            return true;
        } catch (error) {
            console.error('[PWA] Background sync failed:', error);
            return false;
        }
    }

    async storeForSync(tag, data) {
        const request = {
            id: Date.now() + Math.random(),
            type: tag.replace('background-sync-', ''),
            url: data.url,
            method: data.method || 'POST',
            headers: data.headers || {},
            body: data.body ? JSON.stringify(data.body) : null,
            timestamp: Date.now()
        };

        // Store in IndexedDB
        return new Promise((resolve, reject) => {
            const dbRequest = indexedDB.open('HDTicketsOfflineDB', 1);
            
            dbRequest.onsuccess = () => {
                const db = dbRequest.result;
                const transaction = db.transaction(['requests'], 'readwrite');
                const store = transaction.objectStore('requests');
                
                const addRequest = store.add(request);
                addRequest.onsuccess = () => resolve(request.id);
                addRequest.onerror = () => reject(addRequest.error);
            };
            
            dbRequest.onerror = () => reject(dbRequest.error);
            
            dbRequest.onupgradeneeded = () => {
                const db = dbRequest.result;
                if (!db.objectStoreNames.contains('requests')) {
                    db.createObjectStore('requests', { keyPath: 'id' });
                }
            };
        });
    }

    handleOnlineStatusChange(isOnline) {
        console.log('[PWA] Connection status changed:', isOnline ? 'online' : 'offline');
        
        // Update UI
        this.updateConnectionUI(isOnline);
        
        // Show notification
        if (isOnline) {
            this.showToast('🌐 Back online! Syncing data...', 'success');
            this.syncPendingRequests();
        } else {
            this.showToast('📱 You\'re offline. Don\'t worry, you can still browse cached content!', 'info');
        }

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('connectionchange', {
            detail: { isOnline }
        }));
    }

    handleServiceWorkerMessage(event) {
        const { type, data } = event.data || {};
        
        switch (type) {
            case 'CACHE_UPDATED':
                console.log('[PWA] Cache updated');
                break;
                
            case 'BACKGROUND_SYNC_SUCCESS':
                this.showToast('✅ Data synced successfully!', 'success');
                break;
                
            case 'BACKGROUND_SYNC_FAILED':
                this.showToast('❌ Failed to sync some data. Will retry later.', 'error');
                break;
                
            case 'PUSH_RECEIVED':
                console.log('[PWA] Push notification received:', data);
                break;
                
            default:
                console.log('[PWA] Unknown message from service worker:', event.data);
        }
    }

    async syncPendingRequests() {
        if (!this.syncSupported) return;

        try {
            await this.swRegistration.sync.register('background-sync-tickets');
            await this.swRegistration.sync.register('background-sync-alerts');
            await this.swRegistration.sync.register('background-sync-purchases');
        } catch (error) {
            console.error('[PWA] Failed to sync pending requests:', error);
        }
    }

    showUpdateNotification() {
        this.showToast(
            '🆕 A new version is available! <button onclick="pwaManager.updateServiceWorker()" class="toast-button">Update Now</button>',
            'info',
            0 // Don't auto-hide
        );
    }

    async updateServiceWorker() {
        if (!this.swRegistration || !this.swRegistration.waiting) {
            return;
        }

        // Tell the waiting service worker to skip waiting
        this.swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
        
        // Reload the page when the new service worker takes control
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            window.location.reload();
        });
    }

    showInstallPrompt() {
        // Create install prompt UI
        const installBanner = document.createElement('div');
        installBanner.id = 'pwa-install-banner';
        installBanner.className = 'pwa-install-banner';
        installBanner.innerHTML = `
            <div class="install-content">
                <div class="install-icon">📱</div>
                <div class="install-text">
                    <strong>Install HD Tickets</strong>
                    <p>Get the full app experience with offline access!</p>
                </div>
                <div class="install-actions">
                    <button id="install-app-btn" class="btn btn-primary btn-sm">Install</button>
                    <button id="dismiss-install-btn" class="btn btn-secondary btn-sm">Later</button>
                </div>
            </div>
        `;

        document.body.appendChild(installBanner);

        // Add event listeners
        document.getElementById('install-app-btn').addEventListener('click', () => {
            this.installApp();
        });

        document.getElementById('dismiss-install-btn').addEventListener('click', () => {
            this.hideInstallPrompt();
        });

        // Show with animation
        setTimeout(() => {
            installBanner.classList.add('show');
        }, 1000);
    }

    async installApp() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        console.log('[PWA] Install prompt result:', outcome);
        this.trackEvent('pwa_install_prompt_result', { outcome });
        
        this.deferredPrompt = null;
        this.hideInstallPrompt();
    }

    hideInstallPrompt() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.remove();
        }
    }

    updateConnectionUI(isOnline) {
        // Update connection indicators
        const indicators = document.querySelectorAll('.connection-indicator');
        indicators.forEach(indicator => {
            indicator.classList.toggle('online', isOnline);
            indicator.classList.toggle('offline', !isOnline);
            
            const text = indicator.querySelector('.connection-text');
            if (text) {
                text.textContent = isOnline ? 'Online' : 'Offline';
            }
        });

        // Update body class for global styling
        document.body.classList.toggle('is-online', isOnline);
        document.body.classList.toggle('is-offline', !isOnline);
    }

    showToast(message, type = 'info', duration = 5000) {
        // Dispatch toast event for the toast component to handle
        window.dispatchEvent(new CustomEvent('showtoast', {
            detail: { message, type, duration }
        }));
    }

    async checkForUpdates() {
        if (!this.swRegistration) return;

        try {
            await this.swRegistration.update();
            console.log('[PWA] Checked for service worker updates');
        } catch (error) {
            console.error('[PWA] Failed to check for updates:', error);
        }
    }

    async clearCache() {
        if (!this.swRegistration) return;

        try {
            await this.sendMessageToSW({ type: 'CLEAR_CACHE' });
            this.showToast('✅ Cache cleared successfully!', 'success');
        } catch (error) {
            console.error('[PWA] Failed to clear cache:', error);
            this.showToast('❌ Failed to clear cache', 'error');
        }
    }

    async sendMessageToSW(message) {
        if (!navigator.serviceWorker.controller) {
            throw new Error('No service worker controller');
        }

        return new Promise((resolve, reject) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                if (event.data.error) {
                    reject(new Error(event.data.error));
                } else {
                    resolve(event.data);
                }
            };

            navigator.serviceWorker.controller.postMessage(message, [messageChannel.port2]);
        });
    }

    trackEvent(eventName, data = {}) {
        // Track PWA events for analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, {
                event_category: 'PWA',
                ...data
            });
        }

        console.log('[PWA] Event tracked:', eventName, data);
    }

    // Utility function for VAPID key conversion
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        
        return outputArray;
    }

    // Public API methods
    async enableNotifications() {
        return await this.requestNotificationPermission();
    }

    async scheduleBackgroundSync(tag, data) {
        return await this.backgroundSync(tag, data);
    }

    isOffline() {
        return !this.isOnline;
    }

    getInstallationStatus() {
        return {
            canInstall: !!this.deferredPrompt,
            isInstalled: window.matchMedia('(display-mode: standalone)').matches ||
                        window.navigator.standalone === true
        };
    }
}

// Global PWA Manager instance
let pwaManager;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        pwaManager = new PWAManager();
    });
} else {
    pwaManager = new PWAManager();
}

// CSS for install banner (inject into page)
const installBannerCSS = `
    .pwa-install-banner {
        position: fixed;
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        padding: 1rem;
        max-width: 400px;
        width: 90vw;
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .pwa-install-banner.show {
        top: 20px;
    }
    
    .install-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .install-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    .install-text {
        flex: 1;
    }
    
    .install-text strong {
        display: block;
        color: #1f2937;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .install-text p {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
    }
    
    .install-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .toast-button {
        background: #1e40af;
        color: white;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        cursor: pointer;
        margin-left: 0.5rem;
    }
    
    .toast-button:hover {
        background: #1d4ed8;
    }
    
    @media (max-width: 640px) {
        .install-actions {
            flex-direction: column;
        }
    }
`;

// Inject CSS
const style = document.createElement('style');
style.textContent = installBannerCSS;
document.head.appendChild(style);

// Export for global access
window.pwaManager = pwaManager;