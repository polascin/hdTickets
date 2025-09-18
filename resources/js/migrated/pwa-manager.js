/**
 * HD Tickets PWA Manager
 * 
 * Comprehensive Progressive Web App manager for the HD Tickets platform.
 * Handles app installation prompts, push notifications, background sync,
 * and offline functionality coordination.
 * 
 * @version 2.0.0
 * @author HD Tickets Development Team
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.notificationPermission = 'default';
        this.serviceWorker = null;
        this.backgroundSync = null;
        
        this.config = {
            enableInstallPrompt: true,
            enablePushNotifications: true,
            enableBackgroundSync: true,
            installPromptDelay: 60000, // 1 minute
            maxInstallPrompts: 3,
            debugMode: false,
        };
        
        this.init();
    }
    
    /**
     * Initialize PWA manager
     */
    async init() {
        console.log('[PWA] Initializing HD Tickets PWA Manager v2.0.0');
        
        try {
            await this.registerServiceWorker();
            await this.setupInstallPrompt();
            await this.checkInstallStatus();
            await this.initializeNotifications();
            await this.initializeBackgroundSync();
            this.setupEventListeners();
            this.createPWAControls();
            
            console.log('[PWA] PWA Manager initialized successfully');
        } catch (error) {
            console.error('[PWA] Error initializing PWA Manager:', error);
        }
    }
    
    /**
     * Register service worker
     */
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/service-worker.js', {
                    scope: '/'
                });
                
                this.serviceWorker = registration;
                console.log('[PWA] Service Worker registered successfully');
                
                // Handle updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateAvailable();
                        }
                    });
                });
                
            } catch (error) {
                console.error('[PWA] Service Worker registration failed:', error);
            }
        } else {
            console.warn('[PWA] Service Worker not supported');
        }
    }
    
    /**
     * Setup install prompt handling
     */
    async setupInstallPrompt() {
        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (event) => {
            console.log('[PWA] Install prompt available');
            
            // Prevent the mini-infobar from appearing on mobile
            event.preventDefault();
            
            // Save the event for later use
            this.deferredPrompt = event;
            
            // Show custom install prompt after delay
            if (this.config.enableInstallPrompt) {
                setTimeout(() => {
                    this.showInstallPrompt();
                }, this.config.installPromptDelay);
            }
        });
        
        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.isInstalled = true;
            this.hideInstallPrompt();
            this.showInstallSuccess();
        });
    }
    
    /**
     * Check if app is already installed
     */
    async checkInstallStatus() {
        // Check if running in standalone mode (installed)
        if (window.matchMedia('(display-mode: standalone)').matches || 
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('[PWA] App is running in installed mode');
        }
        
        // For browsers that support getInstalledRelatedApps
        if ('getInstalledRelatedApps' in navigator) {
            try {
                const relatedApps = await navigator.getInstalledRelatedApps();
                this.isInstalled = relatedApps.length > 0;
            } catch (error) {
                console.warn('[PWA] Could not check installed apps:', error);
            }
        }
    }
    
    /**
     * Initialize push notifications
     */
    async initializeNotifications() {
        if (!('Notification' in window)) {
            console.warn('[PWA] Notifications not supported');
            return;
        }
        
        this.notificationPermission = Notification.permission;
        console.log('[PWA] Notification permission:', this.notificationPermission);
        
        // Setup push notifications if permission granted
        if (this.notificationPermission === 'granted' && this.serviceWorker) {
            await this.setupPushNotifications();
        }
    }
    
    /**
     * Setup push notifications
     */
    async setupPushNotifications() {
        try {
            const subscription = await this.serviceWorker.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(
                    'BEl62iUYgUivxIkv69yViEuiBIa40HI8YlOu_vYKoPlE9fXjb-8H8Y5BKtL1_x6JZ_R8LlQZx5k9VgM8sTOZC7c'
                )
            });
            
            console.log('[PWA] Push subscription created:', subscription);
            
            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
        } catch (error) {
            console.error('[PWA] Push notification setup failed:', error);
        }
    }
    
    /**
     * Initialize background sync
     */
    async initializeBackgroundSync() {
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            this.backgroundSync = {
                supported: true,
                pendingSync: new Set()
            };
            console.log('[PWA] Background sync supported');
        } else {
            console.warn('[PWA] Background sync not supported');
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Online/offline events
        window.addEventListener('online', () => {
            console.log('[PWA] Connection restored');
            this.handleOnline();
        });
        
        window.addEventListener('offline', () => {
            console.log('[PWA] Connection lost');
            this.handleOffline();
        });
        
        // Visibility change for app lifecycle
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.handleAppForeground();
            } else {
                this.handleAppBackground();
            }
        });
        
        // Service worker messages
        if (this.serviceWorker) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                this.handleServiceWorkerMessage(event);
            });
        }
    }
    
    /**
     * Create PWA control panel
     */
    createPWAControls() {
        // Only create controls if not already installed
        if (this.isInstalled) {
            return;
        }
        
        const controlsContainer = document.createElement('div');
        controlsContainer.id = 'pwa-controls';
        controlsContainer.innerHTML = `
            <div class="pwa-controls">
                <button class="pwa-control-btn" id="pwa-install-btn" style="display: none;">
                    📱 Install HD Tickets
                </button>
                <button class="pwa-control-btn" id="pwa-notifications-btn" style="display: none;">
                    🔔 Enable Notifications
                </button>
                <button class="pwa-control-btn" id="pwa-close-btn">
                    ✕
                </button>
            </div>
        `;
        
        // Add styles
        const styles = `
            <style>
                .pwa-controls {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #2563eb;
                    color: white;
                    padding: 1rem;
                    border-radius: 12px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 10000;
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    max-width: 250px;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                
                .pwa-control-btn {
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: white;
                    padding: 0.75rem;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 0.875rem;
                    font-weight: 500;
                    transition: background-color 0.2s ease;
                }
                
                .pwa-control-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                #pwa-close-btn {
                    align-self: flex-end;
                    padding: 0.5rem;
                    width: 2rem;
                    height: 2rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                @media (max-width: 640px) {
                    .pwa-controls {
                        bottom: 10px;
                        right: 10px;
                        left: 10px;
                        max-width: none;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
        document.body.appendChild(controlsContainer);
        
        // Setup event handlers
        this.setupPWAControlHandlers();
    }
    
    /**
     * Setup PWA control handlers
     */
    setupPWAControlHandlers() {
        const installBtn = document.getElementById('pwa-install-btn');
        const notificationsBtn = document.getElementById('pwa-notifications-btn');
        const closeBtn = document.getElementById('pwa-close-btn');
        
        if (installBtn) {
            installBtn.addEventListener('click', () => {
                this.promptInstall();
            });
        }
        
        if (notificationsBtn) {
            notificationsBtn.addEventListener('click', () => {
                this.requestNotificationPermission();
            });
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hidePWAControls();
            });
        }
    }
    
    /**
     * Show install prompt
     */
    showInstallPrompt() {
        if (!this.deferredPrompt || this.isInstalled) {
            return;
        }
        
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
        }
        
        const promptsShown = parseInt(localStorage.getItem('pwa-install-prompts') || '0');
        if (promptsShown >= this.config.maxInstallPrompts) {
            return;
        }
        
        // Show custom install modal
        this.showInstallModal();
    }
    
    /**
     * Show install modal
     */
    showInstallModal() {
        const modal = document.createElement('div');
        modal.id = 'pwa-install-modal';
        modal.innerHTML = `
            <div class="pwa-modal-overlay">
                <div class="pwa-modal">
                    <div class="pwa-modal-header">
                        <h3>Install HD Tickets</h3>
                        <button class="pwa-modal-close" onclick="this.closest('.pwa-modal-overlay').remove()">✕</button>
                    </div>
                    <div class="pwa-modal-body">
                        <div class="pwa-modal-icon">📱</div>
                        <p>Get the full HD Tickets experience with faster loading, offline access, and push notifications for price alerts.</p>
                        <div class="pwa-modal-benefits">
                            <div class="benefit">🚀 Faster loading times</div>
                            <div class="benefit">📱 Works offline</div>
                            <div class="benefit">🔔 Push notifications</div>
                            <div class="benefit">🎯 Direct access from home screen</div>
                        </div>
                    </div>
                    <div class="pwa-modal-actions">
                        <button class="pwa-btn-secondary" onclick="this.closest('.pwa-modal-overlay').remove()">
                            Maybe Later
                        </button>
                        <button class="pwa-btn-primary" onclick="window.pwaManager.promptInstall()">
                            Install App
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal styles
        const modalStyles = `
            <style>
                .pwa-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10001;
                    padding: 1rem;
                }
                
                .pwa-modal {
                    background: white;
                    border-radius: 16px;
                    max-width: 400px;
                    width: 100%;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                }
                
                .pwa-modal-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 1.5rem 1.5rem 0;
                }
                
                .pwa-modal-header h3 {
                    margin: 0;
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: #1f2937;
                }
                
                .pwa-modal-close {
                    background: none;
                    border: none;
                    font-size: 1.25rem;
                    cursor: pointer;
                    padding: 0.25rem;
                    color: #6b7280;
                }
                
                .pwa-modal-body {
                    padding: 1.5rem;
                    text-align: center;
                }
                
                .pwa-modal-icon {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                }
                
                .pwa-modal-body p {
                    color: #6b7280;
                    line-height: 1.6;
                    margin-bottom: 1.5rem;
                }
                
                .pwa-modal-benefits {
                    display: grid;
                    gap: 0.75rem;
                    margin-bottom: 1.5rem;
                }
                
                .benefit {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    font-size: 0.875rem;
                    color: #374151;
                }
                
                .pwa-modal-actions {
                    display: flex;
                    gap: 1rem;
                    padding: 0 1.5rem 1.5rem;
                }
                
                .pwa-btn-primary, .pwa-btn-secondary {
                    flex: 1;
                    padding: 0.75rem 1rem;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .pwa-btn-primary {
                    background: #2563eb;
                    color: white;
                    border: none;
                }
                
                .pwa-btn-primary:hover {
                    background: #1d4ed8;
                }
                
                .pwa-btn-secondary {
                    background: transparent;
                    color: #6b7280;
                    border: 2px solid #e5e7eb;
                }
                
                .pwa-btn-secondary:hover {
                    background: #f9fafb;
                    color: #374151;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', modalStyles);
        document.body.appendChild(modal);
        
        // Track prompt shown
        const promptsShown = parseInt(localStorage.getItem('pwa-install-prompts') || '0');
        localStorage.setItem('pwa-install-prompts', (promptsShown + 1).toString());
    }
    
    /**
     * Prompt app installation
     */
    async promptInstall() {
        if (!this.deferredPrompt) {
            console.warn('[PWA] No deferred install prompt available');
            return;
        }
        
        try {
            // Show the install prompt
            this.deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            const { outcome } = await this.deferredPrompt.userChoice;
            
            console.log(`[PWA] Install prompt outcome: ${outcome}`);
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted the install prompt');
            } else {
                console.log('[PWA] User dismissed the install prompt');
            }
            
            // Clear the deferredPrompt
            this.deferredPrompt = null;
            
            // Remove modal
            const modal = document.getElementById('pwa-install-modal');
            if (modal) {
                modal.remove();
            }
            
        } catch (error) {
            console.error('[PWA] Error prompting install:', error);
        }
    }
    
    /**
     * Request notification permission
     */
    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.warn('[PWA] Notifications not supported');
            return false;
        }
        
        const permission = await Notification.requestPermission();
        this.notificationPermission = permission;
        
        if (permission === 'granted') {
            console.log('[PWA] Notification permission granted');
            await this.setupPushNotifications();
            
            // Hide notification button
            const notificationsBtn = document.getElementById('pwa-notifications-btn');
            if (notificationsBtn) {
                notificationsBtn.style.display = 'none';
            }
            
            return true;
        } else {
            console.log('[PWA] Notification permission denied');
            return false;
        }
    }
    
    /**
     * Schedule background sync
     */
    async scheduleBackgroundSync(tag) {
        if (!this.backgroundSync || !this.backgroundSync.supported) {
            console.warn('[PWA] Background sync not supported');
            return false;
        }
        
        try {
            await this.serviceWorker.sync.register(tag);
            this.backgroundSync.pendingSync.add(tag);
            console.log(`[PWA] Background sync scheduled: ${tag}`);
            return true;
        } catch (error) {
            console.error('[PWA] Error scheduling background sync:', error);
            return false;
        }
    }
    
    /**
     * Handle online event
     */
    handleOnline() {
        // Update UI to show online status
        document.body.classList.remove('pwa-offline');
        document.body.classList.add('pwa-online');
        
        // Trigger any pending background sync
        if (this.backgroundSync && this.backgroundSync.pendingSync.size > 0) {
            this.backgroundSync.pendingSync.forEach(tag => {
                this.scheduleBackgroundSync(tag);
            });
        }
        
        // Notify service worker
        this.sendMessageToSW({ type: 'CONNECTION_STATUS', online: true });
    }
    
    /**
     * Handle offline event
     */
    handleOffline() {
        // Update UI to show offline status
        document.body.classList.remove('pwa-online');
        document.body.classList.add('pwa-offline');
        
        // Show offline indicator
        this.showOfflineIndicator();
        
        // Notify service worker
        this.sendMessageToSW({ type: 'CONNECTION_STATUS', online: false });
    }
    
    /**
     * Handle app coming to foreground
     */
    handleAppForeground() {
        // Check for updates
        if (this.serviceWorker) {
            this.serviceWorker.update();
        }
        
        // Refresh critical data
        this.refreshCriticalData();
    }
    
    /**
     * Handle app going to background
     */
    handleAppBackground() {
        // Save any pending data
        this.savePendingData();
    }
    
    /**
     * Handle service worker messages
     */
    handleServiceWorkerMessage(event) {
        const { type, data } = event.data;
        
        switch (type) {
            case 'CACHE_UPDATED':
                console.log('[PWA] Cache updated');
                break;
            case 'PUSH_RECEIVED':
                this.handlePushMessage(data);
                break;
            case 'SYNC_COMPLETED':
                this.handleSyncCompleted(data);
                break;
            default:
                console.log('[PWA] Unknown SW message:', type);
        }
    }
    
    /**
     * Utility functions
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
     * Send message to service worker
     */
    sendMessageToSW(message) {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage(message);
        }
    }
    
    /**
     * Send subscription to server
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(subscription)
            });
            
            if (response.ok) {
                console.log('[PWA] Subscription sent to server');
            } else {
                console.error('[PWA] Failed to send subscription to server');
            }
        } catch (error) {
            console.error('[PWA] Error sending subscription to server:', error);
        }
    }
    
    /**
     * Show update available notification
     */
    showUpdateAvailable() {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div class="pwa-update-notification">
                <span>🚀 New version available!</span>
                <button onclick="location.reload()">Update Now</button>
                <button onclick="this.parentElement.remove()">✕</button>
            </div>
        `;
        
        document.body.appendChild(notification);
    }
    
    /**
     * Show offline indicator
     */
    showOfflineIndicator() {
        if (document.getElementById('pwa-offline-indicator')) {
            return;
        }
        
        const indicator = document.createElement('div');
        indicator.id = 'pwa-offline-indicator';
        indicator.innerHTML = `
            <div class="pwa-offline-banner">
                <span>📶 You're offline - Some features may be limited</span>
            </div>
        `;
        
        document.body.appendChild(indicator);
    }
    
    /**
     * Hide PWA controls
     */
    hidePWAControls() {
        const controls = document.getElementById('pwa-controls');
        if (controls) {
            controls.remove();
        }
    }
    
    /**
     * Hide install prompt
     */
    hideInstallPrompt() {
        this.hidePWAControls();
        const modal = document.getElementById('pwa-install-modal');
        if (modal) {
            modal.remove();
        }
    }
    
    /**
     * Show install success
     */
    showInstallSuccess() {
        console.log('[PWA] Showing install success message');
        
        // Show temporary success message
        const successMessage = document.createElement('div');
        successMessage.innerHTML = `
            <div class="pwa-success-message">
                <div class="pwa-success-content">
                    <div class="pwa-success-icon">🎉</div>
                    <h3>HD Tickets Installed!</h3>
                    <p>You can now access HD Tickets directly from your home screen.</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(successMessage);
        
        setTimeout(() => {
            successMessage.remove();
        }, 3000);
    }
    
    /**
     * Refresh critical data
     */
    refreshCriticalData() {
        // Trigger refresh of critical app data
        // This would typically call your app's data refresh methods
        console.log('[PWA] Refreshing critical data');
    }
    
    /**
     * Save pending data
     */
    savePendingData() {
        // Save any unsaved data before app goes to background
        console.log('[PWA] Saving pending data');
    }
    
    /**
     * Handle push message
     */
    handlePushMessage(data) {
        console.log('[PWA] Push message received:', data);
    }
    
    /**
     * Handle sync completed
     */
    handleSyncCompleted(data) {
        console.log('[PWA] Background sync completed:', data);
        if (this.backgroundSync) {
            this.backgroundSync.pendingSync.delete(data.tag);
        }
    }
}

// Initialize PWA Manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pwaManager = new PWAManager();
    });
} else {
    window.pwaManager = new PWAManager();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PWAManager;
}
