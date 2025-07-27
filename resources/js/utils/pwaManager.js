/**
 * PWA Manager - Handles Progressive Web App functionality
 * Including service worker management, push notifications, and mobile optimizations
 */

class PWAManager {
    constructor() {
        this.swRegistration = null;
        this.isOnline = navigator.onLine;
        this.installPrompt = null;
        this.pushManager = null;
        this.backgroundSyncTags = [
            'ticket-alerts-sync',
            'user-preferences-sync',
            'analytics-sync'
        ];
        
        this.init();
    }

    /**
     * Initialize PWA Manager
     */
    async init() {
        console.log('[PWA] Initializing PWA Manager...');
        
        // Register service worker
        await this.registerServiceWorker();
        
        // Setup offline/online detection
        this.setupConnectionListeners();
        
        // Setup install prompt handling
        this.setupInstallPrompt();
        
        // Initialize push notifications
        await this.initializePushNotifications();
        
        // Setup mobile-specific features
        this.setupMobileFeatures();
        
        console.log('[PWA] PWA Manager initialized successfully');
    }

    /**
     * Register service worker
     */
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                });
                
                console.log('[PWA] Service Worker registered:', this.swRegistration.scope);
                
                // Handle service worker updates
                this.swRegistration.addEventListener('updatefound', () => {
                    this.handleServiceWorkerUpdate();
                });
                
                // Check for existing service worker
                if (this.swRegistration.active) {
                    console.log('[PWA] Service Worker is active and ready');
                }
                
                return this.swRegistration;
            } catch (error) {
                console.error('[PWA] Service Worker registration failed:', error);
                throw error;
            }
        } else {
            console.warn('[PWA] Service Workers not supported');
            return null;
        }
    }

    /**
     * Handle service worker updates
     */
    handleServiceWorkerUpdate() {
        const newWorker = this.swRegistration.installing;
        
        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New version available
                this.showUpdateNotification();
            }
        });
    }

    /**
     * Show update notification
     */
    showUpdateNotification() {
        if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
            window.hdTicketsUtils.notify(
                'New version available! Refresh to update.',
                'info',
                {
                    persistent: true,
                    actions: [
                        {
                            text: 'Update Now',
                            action: () => window.location.reload()
                        },
                        {
                            text: 'Later',
                            action: () => {}
                        }
                    ]
                }
            );
        } else {
            // Fallback to confirm dialog
            if (confirm('New version available! Refresh to update?')) {
                window.location.reload();
            }
        }
    }

    /**
     * Setup connection listeners
     */
    setupConnectionListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.handleConnectionChange(true);
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.handleConnectionChange(false);
        });
    }

    /**
     * Handle connection changes
     */
    handleConnectionChange(isOnline) {
        console.log(`[PWA] Connection status: ${isOnline ? 'online' : 'offline'}`);
        
        if (isOnline) {
            // Trigger background sync when coming back online
            this.triggerBackgroundSync();
            
            // Show connection restored notification
            if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
                window.hdTicketsUtils.notify('Connection restored!', 'success');
            }
        } else {
            // Show offline notification
            if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
                window.hdTicketsUtils.notify(
                    'You are offline. Changes will sync when connection returns.',
                    'warning',
                    { duration: 5000 }
                );
            }
        }
        
        // Update UI elements
        this.updateConnectionUI(isOnline);
    }

    /**
     * Update connection UI elements
     */
    updateConnectionUI(isOnline) {
        // Update connection indicators
        const indicators = document.querySelectorAll('.connection-indicator');
        indicators.forEach(indicator => {
            if (isOnline) {
                indicator.classList.add('online');
                indicator.classList.remove('offline');
            } else {
                indicator.classList.add('offline');
                indicator.classList.remove('online');
            }
        });
        
        // Update page title
        if (!isOnline && !document.title.includes('(Offline)')) {
            document.title = document.title + ' (Offline)';
        } else if (isOnline && document.title.includes('(Offline)')) {
            document.title = document.title.replace(' (Offline)', '');
        }
    }

    /**
     * Setup install prompt handling
     */
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.installPrompt = e;
            this.showInstallBanner();
        });
        
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.installPrompt = null;
            this.hideInstallBanner();
            
            if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
                window.hdTicketsUtils.notify('HD Tickets installed successfully!', 'success');
            }
        });
    }

    /**
     * Show install banner
     */
    showInstallBanner() {
        // Don't show if already installed or dismissed recently
        if (this.isInstalled() || this.wasRecentlyDismissed()) {
            return;
        }
        
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.className = 'pwa-install-banner';
        banner.innerHTML = `
            <div class="pwa-banner-content">
                <div class="pwa-banner-icon">📱</div>
                <div class="pwa-banner-text">
                    <strong>Install HD Tickets</strong>
                    <span>Get the full app experience with offline access and notifications</span>
                </div>
                <div class="pwa-banner-actions">
                    <button onclick="pwaManager.installApp()" class="pwa-install-btn">Install</button>
                    <button onclick="pwaManager.dismissInstallBanner()" class="pwa-dismiss-btn">×</button>
                </div>
            </div>
        `;
        
        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            .pwa-install-banner {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
                z-index: 9999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                animation: slideDown 0.3s ease-out;
            }
            
            .pwa-banner-content {
                display: flex;
                align-items: center;
                padding: 12px 16px;
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .pwa-banner-icon {
                font-size: 24px;
                margin-right: 12px;
            }
            
            .pwa-banner-text {
                flex: 1;
                display: flex;
                flex-direction: column;
            }
            
            .pwa-banner-text strong {
                font-weight: 600;
                margin-bottom: 2px;
            }
            
            .pwa-banner-text span {
                font-size: 14px;
                opacity: 0.9;
            }
            
            .pwa-banner-actions {
                display: flex;
                gap: 8px;
            }
            
            .pwa-install-btn {
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                min-height: 44px;
            }
            
            .pwa-install-btn:hover {
                background: rgba(255,255,255,0.3);
            }
            
            .pwa-dismiss-btn {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                padding: 8px;
                cursor: pointer;
                border-radius: 50%;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }
            
            .pwa-dismiss-btn:hover {
                background: rgba(255,255,255,0.2);
            }
            
            @keyframes slideDown {
                from { transform: translateY(-100%); }
                to { transform: translateY(0); }
            }
            
            @media (max-width: 640px) {
                .pwa-banner-content {
                    padding: 8px 12px;
                }
                
                .pwa-banner-text span {
                    display: none;
                }
                
                .pwa-install-btn {
                    padding: 6px 12px;
                    font-size: 14px;
                }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(banner);
    }

    /**
     * Install the app
     */
    async installApp() {
        if (!this.installPrompt) {
            return;
        }
        
        try {
            const result = await this.installPrompt.prompt();
            console.log('[PWA] Install prompt result:', result.outcome);
            
            if (result.outcome === 'accepted') {
                console.log('[PWA] User accepted install prompt');
            }
        } catch (error) {
            console.error('[PWA] Install prompt failed:', error);
        }
        
        this.installPrompt = null;
        this.hideInstallBanner();
    }

    /**
     * Dismiss install banner
     */
    dismissInstallBanner() {
        this.hideInstallBanner();
        
        // Remember dismissal for 7 days
        localStorage.setItem('pwa-install-dismissed', Date.now().toString());
    }

    /**
     * Hide install banner
     */
    hideInstallBanner() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.style.animation = 'slideUp 0.3s ease-out forwards';
            setTimeout(() => banner.remove(), 300);
        }
    }

    /**
     * Check if app is installed
     */
    isInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }

    /**
     * Check if install banner was recently dismissed
     */
    wasRecentlyDismissed() {
        const dismissed = localStorage.getItem('pwa-install-dismissed');
        if (!dismissed) return false;
        
        const dismissedTime = parseInt(dismissed);
        const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
        
        return dismissedTime > weekAgo;
    }

    /**
     * Initialize push notifications
     */
    async initializePushNotifications() {
        if (!('Notification' in window) || !this.swRegistration) {
            console.warn('[PWA] Push notifications not supported');
            return;
        }
        
        try {
            // Check current permission
            let permission = Notification.permission;
            
            if (permission === 'default') {
                // Don't auto-request, wait for user action
                console.log('[PWA] Push notifications permission not granted yet');
                return;
            }
            
            if (permission === 'granted') {
                await this.setupPushSubscription();
            }
        } catch (error) {
            console.error('[PWA] Push notification setup failed:', error);
        }
    }

    /**
     * Request push notification permission
     */
    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            throw new Error('Push notifications not supported');
        }
        
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            await this.setupPushSubscription();
            
            if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
                window.hdTicketsUtils.notify(
                    'Push notifications enabled! You\'ll receive ticket alerts.',
                    'success'
                );
            }
        }
        
        return permission;
    }

    /**
     * Setup push subscription
     */
    async setupPushSubscription() {
        try {
            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.getVapidPublicKey()
            });
            
            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
            console.log('[PWA] Push subscription created:', subscription);
        } catch (error) {
            console.error('[PWA] Push subscription failed:', error);
        }
    }

    /**
     * Get VAPID public key (you'll need to generate this)
     */
    getVapidPublicKey() {
        // This should be your VAPID public key
        // Generate with: npx web-push generate-vapid-keys
        return 'BHxvUlLOJZ8JZXyUTaVFnWjkLjHKV7gE8V1MtC6CZ4q5Z4q5Z4q5Z4q5Z4q5Z4q5Z4q5Z4q5Z4q5Z4q5';
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    subscription: subscription
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save subscription');
            }
            
            console.log('[PWA] Subscription saved to server');
        } catch (error) {
            console.error('[PWA] Failed to save subscription:', error);
        }
    }

    /**
     * Trigger background sync
     */
    async triggerBackgroundSync() {
        if (!this.swRegistration || !this.swRegistration.sync) {
            console.warn('[PWA] Background sync not supported');
            return;
        }
        
        try {
            for (const tag of this.backgroundSyncTags) {
                await this.swRegistration.sync.register(tag);
                console.log(`[PWA] Background sync registered: ${tag}`);
            }
        } catch (error) {
            console.error('[PWA] Background sync registration failed:', error);
        }
    }

    /**
     * Add data to sync queue
     */
    addToSyncQueue(type, data) {
        const id = Date.now().toString();
        const item = { id, data, timestamp: Date.now() };
        
        try {
            const key = `hd-tickets-pending-${type}`;
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            existing.push(item);
            localStorage.setItem(key, JSON.stringify(existing));
            
            // Register sync if online
            if (this.isOnline && this.swRegistration && this.swRegistration.sync) {
                this.swRegistration.sync.register(`${type}-sync`);
            }
            
            console.log(`[PWA] Added to sync queue: ${type}`, item);
        } catch (error) {
            console.error(`[PWA] Failed to add to sync queue: ${type}`, error);
        }
    }

    /**
     * Setup mobile-specific features
     */
    setupMobileFeatures() {
        // Prevent zoom on input focus (iOS)
        if (this.isMobile()) {
            const viewport = document.querySelector('meta[name="viewport"]');
            if (viewport) {
                viewport.setAttribute('content', 
                    'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'
                );
            }
        }
        
        // Setup touch gestures
        this.setupTouchGestures();
        
        // Setup mobile navigation
        this.setupMobileNavigation();
        
        // Setup pull-to-refresh
        this.setupPullToRefresh();
    }

    /**
     * Check if device is mobile
     */
    isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Setup touch gestures
     */
    setupTouchGestures() {
        let startX, startY, startTime;
        
        document.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            startTime = Date.now();
        }, { passive: true });
        
        document.addEventListener('touchend', (e) => {
            if (!startX || !startY) return;
            
            const touch = e.changedTouches[0];
            const endX = touch.clientX;
            const endY = touch.clientY;
            const endTime = Date.now();
            
            const deltaX = endX - startX;
            const deltaY = endY - startY;
            const deltaTime = endTime - startTime;
            
            // Swipe detection
            if (Math.abs(deltaX) > 50 && Math.abs(deltaY) < 100 && deltaTime < 300) {
                if (deltaX > 0) {
                    // Swipe right
                    this.handleSwipeRight(e);
                } else {
                    // Swipe left
                    this.handleSwipeLeft(e);
                }
            }
        }, { passive: true });
    }

    /**
     * Handle swipe right gesture
     */
    handleSwipeRight(e) {
        // Could be used for navigation or drawer opening
        console.log('[PWA] Swipe right detected');
        
        // Custom event for components to listen to
        window.dispatchEvent(new CustomEvent('swipe:right', { detail: e }));
    }

    /**
     * Handle swipe left gesture
     */
    handleSwipeLeft(e) {
        // Could be used for navigation or drawer closing
        console.log('[PWA] Swipe left detected');
        
        // Custom event for components to listen to
        window.dispatchEvent(new CustomEvent('swipe:left', { detail: e }));
    }

    /**
     * Setup mobile navigation
     */
    setupMobileNavigation() {
        // Add mobile navigation classes and behavior
        const nav = document.querySelector('nav');
        if (nav && this.isMobile()) {
            nav.classList.add('mobile-nav');
        }
    }

    /**
     * Setup pull-to-refresh
     */
    setupPullToRefresh() {
        if (!this.isMobile()) return;
        
        let startY = 0;
        let refreshing = false;
        
        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (refreshing || window.scrollY > 0) return;
            
            const currentY = e.touches[0].clientY;
            const pullDistance = currentY - startY;
            
            if (pullDistance > 80) {
                this.showPullToRefreshIndicator();
            }
        }, { passive: true });
        
        document.addEventListener('touchend', (e) => {
            if (refreshing || window.scrollY > 0) return;
            
            const currentY = e.changedTouches[0].clientY;
            const pullDistance = currentY - startY;
            
            if (pullDistance > 100) {
                this.triggerRefresh();
            } else {
                this.hidePullToRefreshIndicator();
            }
        }, { passive: true });
    }

    /**
     * Show pull-to-refresh indicator
     */
    showPullToRefreshIndicator() {
        // Implementation for pull-to-refresh UI
        console.log('[PWA] Show pull-to-refresh indicator');
    }

    /**
     * Hide pull-to-refresh indicator
     */
    hidePullToRefreshIndicator() {
        // Implementation for hiding pull-to-refresh UI
        console.log('[PWA] Hide pull-to-refresh indicator');
    }

    /**
     * Trigger refresh
     */
    async triggerRefresh() {
        console.log('[PWA] Triggering refresh...');
        
        // Show loading indicator
        if (window.hdTicketsUtils && window.hdTicketsUtils.loading) {
            window.hdTicketsUtils.loading('Refreshing...');
        }
        
        try {
            // Refresh current page data
            await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate refresh
            window.location.reload();
        } catch (error) {
            console.error('[PWA] Refresh failed:', error);
        } finally {
            this.hidePullToRefreshIndicator();
            if (window.hdTicketsUtils && window.hdTicketsUtils.stopLoading) {
                window.hdTicketsUtils.stopLoading();
            }
        }
    }

    /**
     * Get PWA status
     */
    getStatus() {
        return {
            isOnline: this.isOnline,
            isInstalled: this.isInstalled(),
            hasServiceWorker: !!this.swRegistration,
            notificationPermission: 'Notification' in window ? Notification.permission : 'not-supported',
            isMobile: this.isMobile()
        };
    }
}

// Create global PWA manager instance
const pwaManager = new PWAManager();

// Make it available globally
window.pwaManager = pwaManager;

export default pwaManager;
