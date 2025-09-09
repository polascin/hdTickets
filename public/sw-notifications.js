/**
 * HD Tickets Notification Service Worker
 * 
 * Service worker for handling push notifications, offline functionality,
 * and background synchronization for the sports ticket monitoring system.
 * 
 * Features:
 * - Push notification handling and display
 * - Offline page caching and fallbacks
 * - Background sync for critical data
 * - Notification action handling
 * - Cache management for performance
 * 
 * @version 1.0.0
 */

const CACHE_NAME = 'hd-tickets-v1.2.0';
const STATIC_CACHE = 'hd-tickets-static-v1.2.0';
const DYNAMIC_CACHE = 'hd-tickets-dynamic-v1.2.0';
const NOTIFICATION_TAG = 'hd-tickets-notification';

// Files to cache for offline functionality
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/manifest.json',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
    '/images/icons/notification-icon.png',
    '/images/icons/badge-icon.png',
    '/css/app.css',
    '/js/app.js',
    '/sounds/alert.mp3',
    '/sounds/success.mp3',
    '/sounds/warning.mp3',
    '/sounds/error.mp3'
];

// API endpoints that should be cached
const CACHEABLE_APIS = [
    '/api/notifications/poll',
    '/api/dashboard/stats',
    '/api/tickets/recent',
    '/api/user/preferences'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('🔧 Service Worker installing...');
    
    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE).then((cache) => {
                console.log('📦 Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            }),
            // Skip waiting to activate immediately
            self.skipWaiting()
        ])
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('✅ Service Worker activated');
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== STATIC_CACHE && 
                            cacheName !== DYNAMIC_CACHE && 
                            cacheName !== CACHE_NAME) {
                            console.log('🗑️ Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Claim all clients immediately
            self.clients.claim()
        ])
    );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome extension requests
    if (request.url.startsWith('chrome-extension://')) {
        return;
    }
    
    event.respondWith(handleFetch(request));
});

// Handle fetch requests with caching strategy
async function handleFetch(request) {
    const url = new URL(request.url);
    
    try {
        // Strategy 1: Cache first for static assets
        if (isStaticAsset(url.pathname)) {
            return await cacheFirst(request);
        }
        
        // Strategy 2: Network first for API calls
        if (isApiRequest(url.pathname)) {
            return await networkFirst(request);
        }
        
        // Strategy 3: Stale while revalidate for pages
        if (isPageRequest(request)) {
            return await staleWhileRevalidate(request);
        }
        
        // Default: Network only
        return await fetch(request);
        
    } catch (error) {
        console.error('❌ Fetch failed:', error);
        return await handleFetchError(request, error);
    }
}

// Cache first strategy (for static assets)
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse.status === 200) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
}

// Network first strategy (for API calls)
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        // Cache successful responses
        if (networkResponse.status === 200) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        // Fallback to cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        throw error;
    }
}

// Stale while revalidate strategy (for pages)
async function staleWhileRevalidate(request) {
    const cachedResponse = await caches.match(request);
    
    // Always try to fetch from network in background
    const networkPromise = fetch(request).then((networkResponse) => {
        if (networkResponse.status === 200) {
            const cache = caches.open(DYNAMIC_CACHE);
            cache.then((c) => c.put(request, networkResponse.clone()));
        }
        return networkResponse;
    });
    
    // Return cached version immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // Otherwise wait for network
    return await networkPromise;
}

// Handle fetch errors
async function handleFetchError(request, error) {
    // For page requests, return offline page
    if (isPageRequest(request)) {
        const offlineResponse = await caches.match('/offline');
        if (offlineResponse) {
            return offlineResponse;
        }
    }
    
    // For API requests, return cached version or error response
    if (isApiRequest(new URL(request.url).pathname)) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        return new Response(
            JSON.stringify({
                error: 'Network unavailable',
                message: 'This data is not available offline',
                offline: true
            }),
            {
                status: 503,
                headers: { 'Content-Type': 'application/json' }
            }
        );
    }
    
    throw error;
}

// Push event - handle push notifications
self.addEventListener('push', (event) => {
    console.log('🔔 Push notification received');
    
    if (!event.data) {
        console.warn('⚠️ Push event has no data');
        return;
    }
    
    try {
        const data = event.data.json();
        event.waitUntil(handlePushNotification(data));
    } catch (error) {
        console.error('❌ Failed to parse push data:', error);
    }
});

// Handle push notification display
async function handlePushNotification(data) {
    const {
        title = 'HD Tickets',
        message = 'New notification',
        type = 'info',
        priority = 'medium',
        actions = [],
        image,
        icon = '/images/icons/notification-icon.png',
        badge = '/images/icons/badge-icon.png',
        data: notificationData = {},
        vibrate = [200, 100, 200]
    } = data;
    
    const options = {
        body: message,
        icon: icon,
        badge: badge,
        image: image,
        tag: notificationData.id || NOTIFICATION_TAG,
        requireInteraction: priority === 'high',
        silent: false,
        vibrate: vibrate,
        data: notificationData,
        actions: formatNotificationActions(actions),
        timestamp: Date.now()
    };
    
    // Add type-specific styling
    if (type === 'price_alert') {
        options.requireInteraction = true;
        options.icon = '/images/icons/price-alert-icon.png';
    } else if (type === 'availability') {
        options.requireInteraction = true;
        options.icon = '/images/icons/ticket-available-icon.png';
    }
    
    await self.registration.showNotification(title, options);
    
    // Track notification statistics
    await trackNotificationStats(data);
}

// Format notification actions for display
function formatNotificationActions(actions) {
    return actions.slice(0, 2).map((action) => ({
        action: action.action,
        title: action.title,
        icon: action.icon
    }));
}

// Handle notification click events
self.addEventListener('notificationclick', (event) => {
    console.log('🔔 Notification clicked:', event.notification.tag);
    
    event.notification.close();
    
    event.waitUntil(handleNotificationClick(event));
});

// Handle notification click
async function handleNotificationClick(event) {
    const { notification, action } = event;
    const data = notification.data;
    
    let targetUrl = '/';
    
    // Determine target URL based on action or notification type
    if (action) {
        targetUrl = getActionUrl(action, data);
    } else if (data.url) {
        targetUrl = data.url;
    } else if (data.type) {
        targetUrl = getTypeUrl(data.type, data);
    }
    
    // Focus or open window
    const clients = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    });
    
    // Check if there's already a window open with the target URL
    for (const client of clients) {
        if (client.url === targetUrl && 'focus' in client) {
            await client.focus();
            return;
        }
    }
    
    // Open new window if no matching window found
    await self.clients.openWindow(targetUrl);
    
    // Track click analytics
    await trackNotificationClick(notification.tag, action, data);
}

// Get URL based on action
function getActionUrl(action, data) {
    const actionUrls = {
        'view': data.viewUrl || '/',
        'purchase': data.purchaseUrl || '/tickets',
        'settings': '/settings/notifications',
        'dismiss': null
    };
    
    return actionUrls[action] || '/';
}

// Get URL based on notification type
function getTypeUrl(type, data) {
    const typeUrls = {
        'price_alert': `/monitoring/alerts/${data.alert_id}`,
        'availability': `/tickets/${data.ticket_id}`,
        'purchase_update': `/purchases/${data.purchase_id}`,
        'system': '/notifications',
        'maintenance': '/status',
        'platform_alert': '/platforms'
    };
    
    return typeUrls[type] || '/notifications';
}

// Handle notification close events
self.addEventListener('notificationclose', (event) => {
    console.log('🔔 Notification closed:', event.notification.tag);
    
    event.waitUntil(trackNotificationDismiss(event.notification.tag, event.notification.data));
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('🔄 Background sync triggered:', event.tag);
    
    if (event.tag === 'notification-sync') {
        event.waitUntil(syncNotifications());
    } else if (event.tag === 'preference-sync') {
        event.waitUntil(syncPreferences());
    } else if (event.tag === 'analytics-sync') {
        event.waitUntil(syncAnalytics());
    }
});

// Sync notifications when back online
async function syncNotifications() {
    try {
        const response = await fetch('/api/notifications/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            console.log('✅ Notifications synced');
        }
    } catch (error) {
        console.error('❌ Notification sync failed:', error);
    }
}

// Sync user preferences
async function syncPreferences() {
    try {
        const stored = localStorage.getItem('hd_notification_preferences_pending');
        if (!stored) return;
        
        const preferences = JSON.parse(stored);
        
        const response = await fetch('/api/user/notification-preferences', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(preferences)
        });
        
        if (response.ok) {
            localStorage.removeItem('hd_notification_preferences_pending');
            console.log('✅ Preferences synced');
        }
    } catch (error) {
        console.error('❌ Preference sync failed:', error);
    }
}

// Sync analytics data
async function syncAnalytics() {
    try {
        const stored = localStorage.getItem('hd_notification_analytics_pending');
        if (!stored) return;
        
        const analytics = JSON.parse(stored);
        
        const response = await fetch('/api/analytics/notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(analytics)
        });
        
        if (response.ok) {
            localStorage.removeItem('hd_notification_analytics_pending');
            console.log('✅ Analytics synced');
        }
    } catch (error) {
        console.error('❌ Analytics sync failed:', error);
    }
}

// Track notification statistics
async function trackNotificationStats(data) {
    try {
        const stats = {
            type: data.type,
            priority: data.priority,
            timestamp: Date.now(),
            shown: true
        };
        
        await fetch('/api/analytics/notification-shown', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(stats)
        });
    } catch (error) {
        // Store for later sync if offline
        const pending = JSON.parse(localStorage.getItem('hd_notification_analytics_pending') || '[]');
        pending.push({ action: 'shown', data: data, timestamp: Date.now() });
        localStorage.setItem('hd_notification_analytics_pending', JSON.stringify(pending));
    }
}

// Track notification clicks
async function trackNotificationClick(tag, action, data) {
    try {
        const clickData = {
            tag: tag,
            action: action,
            type: data.type,
            timestamp: Date.now()
        };
        
        await fetch('/api/analytics/notification-click', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(clickData)
        });
    } catch (error) {
        // Store for later sync if offline
        const pending = JSON.parse(localStorage.getItem('hd_notification_analytics_pending') || '[]');
        pending.push({ action: 'click', tag: tag, actionType: action, data: data, timestamp: Date.now() });
        localStorage.setItem('hd_notification_analytics_pending', JSON.stringify(pending));
    }
}

// Track notification dismissals
async function trackNotificationDismiss(tag, data) {
    try {
        const dismissData = {
            tag: tag,
            type: data.type,
            timestamp: Date.now()
        };
        
        await fetch('/api/analytics/notification-dismiss', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dismissData)
        });
    } catch (error) {
        // Store for later sync if offline
        const pending = JSON.parse(localStorage.getItem('hd_notification_analytics_pending') || '[]');
        pending.push({ action: 'dismiss', tag: tag, data: data, timestamp: Date.now() });
        localStorage.setItem('hd_notification_analytics_pending', JSON.stringify(pending));
    }
}

// Utility functions
function isStaticAsset(pathname) {
    const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.svg', '.ico', '.woff', '.woff2', '.mp3'];
    return staticExtensions.some(ext => pathname.endsWith(ext)) || STATIC_ASSETS.includes(pathname);
}

function isApiRequest(pathname) {
    return pathname.startsWith('/api/') || CACHEABLE_APIS.includes(pathname);
}

function isPageRequest(request) {
    return request.headers.get('Accept')?.includes('text/html');
}

// Handle messages from main thread
self.addEventListener('message', (event) => {
    const { type, payload } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: CACHE_NAME });
            break;
            
        case 'CLEAR_CACHE':
            clearAllCaches().then(() => {
                event.ports[0].postMessage({ success: true });
            });
            break;
            
        case 'TEST_NOTIFICATION':
            handlePushNotification({
                title: 'Test Notification',
                message: 'This is a test notification from the service worker',
                type: 'system',
                priority: 'medium'
            });
            break;
            
        default:
            console.warn('⚠️ Unknown message type:', type);
    }
});

// Clear all caches
async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(cacheNames.map(name => caches.delete(name)));
    console.log('🗑️ All caches cleared');
}

// Periodic cleanup (called during activate)
async function performMaintenance() {
    // Clean up old notification analytics
    const pending = JSON.parse(localStorage.getItem('hd_notification_analytics_pending') || '[]');
    const oneWeekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
    
    const recentAnalytics = pending.filter(item => item.timestamp > oneWeekAgo);
    
    if (recentAnalytics.length !== pending.length) {
        localStorage.setItem('hd_notification_analytics_pending', JSON.stringify(recentAnalytics));
        console.log('🧹 Cleaned up old analytics data');
    }
    
    // Clean up old notification history
    const history = JSON.parse(localStorage.getItem('hd_notification_history') || '[]');
    const oneMonthAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
    
    const recentHistory = history.filter(item => item.timestamp > oneMonthAgo);
    
    if (recentHistory.length !== history.length) {
        localStorage.setItem('hd_notification_history', JSON.stringify(recentHistory));
        console.log('🧹 Cleaned up old notification history');
    }
}

// Run maintenance on activation
self.addEventListener('activate', (event) => {
    event.waitUntil(performMaintenance());
});

console.log('🚀 HD Tickets Notification Service Worker loaded');
