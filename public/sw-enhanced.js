/**
 * HD Tickets Enhanced Service Worker
 * 
 * Comprehensive Progressive Web App service worker with:
 * - Advanced caching strategies
 * - Offline functionality
 * - Background sync
 * - Push notifications
 * - Performance optimization
 * 
 * @version 2.0.0
 * @author HD Tickets Development Team
 */

// Service Worker version for cache busting
const SW_VERSION = '2.0.0';
const CACHE_NAME = `hd-tickets-v${SW_VERSION}`;

// Cache configurations
const CACHE_CONFIG = {
    // Static resources cache (long-term storage)
    static: {
        name: `${CACHE_NAME}-static`,
        urls: [
            '/',
            '/manifest.json',
            '/offline.html',
            '/css/app.css',
            '/js/app.js',
            '/images/logo.png',
            '/images/icons/icon-192x192.png',
            '/images/icons/icon-512x512.png',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
            'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'
        ]
    },
    
    // API data cache (medium-term with refresh strategy)
    api: {
        name: `${CACHE_NAME}-api`,
        maxAge: 1000 * 60 * 15, // 15 minutes
        maxEntries: 100
    },
    
    // Images cache (long-term storage)
    images: {
        name: `${CACHE_NAME}-images`,
        maxAge: 1000 * 60 * 60 * 24 * 7, // 7 days
        maxEntries: 200
    },
    
    // Pages cache (short-term with network-first strategy)
    pages: {
        name: `${CACHE_NAME}-pages`,
        maxAge: 1000 * 60 * 60 * 2, // 2 hours
        maxEntries: 50
    }
};

// Background sync configurations
const SYNC_CONFIG = {
    tags: {
        TICKET_ALERTS: 'ticket-alerts-sync',
        USER_PREFERENCES: 'user-preferences-sync',
        ANALYTICS: 'analytics-sync',
        NOTIFICATIONS: 'notifications-sync'
    },
    maxRetries: 3,
    retryDelay: 1000 * 60 * 5 // 5 minutes
};

// Push notification configuration
const NOTIFICATION_CONFIG = {
    tag: 'hd-tickets-notification',
    badge: '/images/icons/badge-icon.png',
    icon: '/images/icons/icon-192x192.png',
    vibrate: [200, 100, 200],
    actions: [
        {
            action: 'view',
            title: 'View Details',
            icon: '/images/icons/view-icon.png'
        },
        {
            action: 'dismiss',
            title: 'Dismiss',
            icon: '/images/icons/dismiss-icon.png'
        }
    ]
};

// Installation event - Cache essential resources
self.addEventListener('install', event => {
    console.log(`[SW ${SW_VERSION}] Installing service worker...`);
    
    event.waitUntil(
        Promise.all([
            // Cache static resources
            caches.open(CACHE_CONFIG.static.name)
                .then(cache => cache.addAll(CACHE_CONFIG.static.urls)),
            
            // Initialize other caches
            caches.open(CACHE_CONFIG.api.name),
            caches.open(CACHE_CONFIG.images.name),
            caches.open(CACHE_CONFIG.pages.name)
        ])
        .then(() => {
            console.log(`[SW ${SW_VERSION}] Installation completed successfully`);
            // Skip waiting to activate immediately
            return self.skipWaiting();
        })
        .catch(error => {
            console.error(`[SW ${SW_VERSION}] Installation failed:`, error);
        })
    );
});

// Activation event - Clean up old caches
self.addEventListener('activate', event => {
    console.log(`[SW ${SW_VERSION}] Activating service worker...`);
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            cleanupOldCaches(),
            
            // Claim all clients immediately
            self.clients.claim()
        ])
        .then(() => {
            console.log(`[SW ${SW_VERSION}] Activation completed successfully`);
            // Notify all clients about the update
            notifyClientsAboutUpdate();
        })
        .catch(error => {
            console.error(`[SW ${SW_VERSION}] Activation failed:`, error);
        })
    );
});

// Fetch event - Handle all network requests with caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests for caching
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension requests
    if (url.protocol === 'chrome-extension:') {
        return;
    }
    
    // Apply different caching strategies based on request type
    if (isApiRequest(url)) {
        event.respondWith(handleApiRequest(request));
    } else if (isImageRequest(url)) {
        event.respondWith(handleImageRequest(request));
    } else if (isPageRequest(url)) {
        event.respondWith(handlePageRequest(request));
    } else if (isStaticAsset(url)) {
        event.respondWith(handleStaticRequest(request));
    } else {
        // Default fallback strategy
        event.respondWith(handleDefaultRequest(request));
    }
});

// Background sync event - Handle offline actions
self.addEventListener('sync', event => {
    console.log(`[SW ${SW_VERSION}] Background sync triggered:`, event.tag);
    
    switch (event.tag) {
        case SYNC_CONFIG.tags.TICKET_ALERTS:
            event.waitUntil(syncTicketAlerts());
            break;
            
        case SYNC_CONFIG.tags.USER_PREFERENCES:
            event.waitUntil(syncUserPreferences());
            break;
            
        case SYNC_CONFIG.tags.ANALYTICS:
            event.waitUntil(syncAnalytics());
            break;
            
        case SYNC_CONFIG.tags.NOTIFICATIONS:
            event.waitUntil(syncNotifications());
            break;
            
        default:
            console.warn(`[SW ${SW_VERSION}] Unknown sync tag:`, event.tag);
    }
});

// Push notification event
self.addEventListener('push', event => {
    console.log(`[SW ${SW_VERSION}] Push notification received`);
    
    const options = {
        ...NOTIFICATION_CONFIG,
        data: {}
    };
    
    if (event.data) {
        try {
            const payload = event.data.json();
            options.title = payload.title || 'HD Tickets Alert';
            options.body = payload.body || 'New ticket information available';
            options.data = payload.data || {};
            
            // Add custom icon based on notification type
            if (payload.type === 'price-alert') {
                options.icon = '/images/icons/price-alert.png';
            } else if (payload.type === 'availability') {
                options.icon = '/images/icons/availability.png';
            }
            
        } catch (error) {
            console.error(`[SW ${SW_VERSION}] Error parsing push payload:`, error);
            options.title = 'HD Tickets Alert';
            options.body = 'New notification available';
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(options.title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', event => {
    console.log(`[SW ${SW_VERSION}] Notification clicked:`, event.action);
    
    event.notification.close();
    
    if (event.action === 'dismiss') {
        return;
    }
    
    // Handle notification click
    const urlToOpen = event.notification.data?.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Check if there's already a window open
                for (const client of clientList) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Open new window if none found
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Message event - Handle communication with main thread
self.addEventListener('message', event => {
    const { type, payload } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: SW_VERSION });
            break;
            
        case 'CACHE_URLS':
            event.waitUntil(cacheUrls(payload.urls));
            break;
            
        case 'CLEAR_CACHE':
            event.waitUntil(clearSpecificCache(payload.cacheName));
            break;
            
        case 'SYNC_DATA':
            event.waitUntil(registerBackgroundSync(payload.tag));
            break;
            
        default:
            console.warn(`[SW ${SW_VERSION}] Unknown message type:`, type);
    }
});

// ================================
// CACHING STRATEGIES IMPLEMENTATION
// ================================

/**
 * Handle API requests with network-first, cache fallback
 */
async function handleApiRequest(request) {
    try {
        // Try network first
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful responses
            const cache = await caches.open(CACHE_CONFIG.api.name);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        // Network failed, try cache
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            console.log(`[SW ${SW_VERSION}] Serving API request from cache:`, request.url);
            return cachedResponse;
        }
        
        // Both network and cache failed
        throw error;
    }
}

/**
 * Handle image requests with cache-first strategy
 */
async function handleImageRequest(request) {
    const cache = await caches.open(CACHE_CONFIG.images.name);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        // Return placeholder image if available
        return caches.match('/images/placeholder.png') || 
               new Response('', { status: 404, statusText: 'Image not found' });
    }
}

/**
 * Handle page requests with network-first, stale-while-revalidate
 */
async function handlePageRequest(request) {
    const cache = await caches.open(CACHE_CONFIG.pages.name);
    
    try {
        // Try network first
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Update cache in background
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        // Network failed, serve from cache
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            console.log(`[SW ${SW_VERSION}] Serving page from cache:`, request.url);
            return cachedResponse;
        }
        
        // Serve offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline.html');
        }
        
        throw error;
    }
}

/**
 * Handle static asset requests with cache-first strategy
 */
async function handleStaticRequest(request) {
    const cache = await caches.open(CACHE_CONFIG.static.name);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // Not in cache, fetch from network
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        throw error;
    }
}

/**
 * Default request handler
 */
async function handleDefaultRequest(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return cachedResponse || new Response('', { status: 404 });
    }
}

// ================================
// UTILITY FUNCTIONS
// ================================

/**
 * Check if request is an API call
 */
function isApiRequest(url) {
    return url.pathname.startsWith('/api/') || 
           url.pathname.includes('/api/') ||
           url.searchParams.has('api');
}

/**
 * Check if request is for an image
 */
function isImageRequest(url) {
    return /\.(png|jpg|jpeg|gif|svg|webp|ico)$/i.test(url.pathname);
}

/**
 * Check if request is for a page
 */
function isPageRequest(url) {
    return url.pathname.endsWith('/') || 
           url.pathname.includes('.html') ||
           (!url.pathname.includes('.') && !url.pathname.startsWith('/api/'));
}

/**
 * Check if request is for a static asset
 */
function isStaticAsset(url) {
    return /\.(css|js|woff|woff2|ttf|eot)$/i.test(url.pathname) ||
           url.pathname.startsWith('/css/') ||
           url.pathname.startsWith('/js/') ||
           url.pathname.startsWith('/fonts/');
}

/**
 * Clean up old caches
 */
async function cleanupOldCaches() {
    const cacheNames = await caches.keys();
    const currentCaches = Object.values(CACHE_CONFIG).map(config => config.name);
    
    return Promise.all(
        cacheNames.map(cacheName => {
            if (!cacheName.includes(SW_VERSION) && !currentCaches.includes(cacheName)) {
                console.log(`[SW ${SW_VERSION}] Deleting old cache:`, cacheName);
                return caches.delete(cacheName);
            }
        })
    );
}

/**
 * Cache specific URLs
 */
async function cacheUrls(urls) {
    const cache = await caches.open(CACHE_CONFIG.static.name);
    return cache.addAll(urls);
}

/**
 * Clear specific cache
 */
async function clearSpecificCache(cacheName) {
    return caches.delete(cacheName);
}

/**
 * Register background sync
 */
async function registerBackgroundSync(tag) {
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
        return self.registration.sync.register(tag);
    }
}

/**
 * Notify clients about service worker update
 */
async function notifyClientsAboutUpdate() {
    const clients = await self.clients.matchAll({ includeUncontrolled: true });
    
    clients.forEach(client => {
        client.postMessage({
            type: 'SW_UPDATED',
            version: SW_VERSION
        });
    });
}

// ================================
// BACKGROUND SYNC HANDLERS
// ================================

/**
 * Sync ticket alerts
 */
async function syncTicketAlerts() {
    try {
        // Get pending alerts from IndexedDB
        const pendingAlerts = await getStoredData('pendingAlerts');
        
        if (pendingAlerts && pendingAlerts.length > 0) {
            // Send to server
            const response = await fetch('/api/v1/alerts/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ alerts: pendingAlerts })
            });
            
            if (response.ok) {
                // Clear pending alerts
                await clearStoredData('pendingAlerts');
                console.log(`[SW ${SW_VERSION}] Ticket alerts synced successfully`);
            }
        }
    } catch (error) {
        console.error(`[SW ${SW_VERSION}] Failed to sync ticket alerts:`, error);
        throw error;
    }
}

/**
 * Sync user preferences
 */
async function syncUserPreferences() {
    try {
        // Get pending preferences from IndexedDB
        const pendingPrefs = await getStoredData('pendingPreferences');
        
        if (pendingPrefs) {
            // Send to server
            const response = await fetch('/api/v1/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ preferences: pendingPrefs })
            });
            
            if (response.ok) {
                // Clear pending preferences
                await clearStoredData('pendingPreferences');
                console.log(`[SW ${SW_VERSION}] User preferences synced successfully`);
            }
        }
    } catch (error) {
        console.error(`[SW ${SW_VERSION}] Failed to sync user preferences:`, error);
        throw error;
    }
}

/**
 * Sync analytics data
 */
async function syncAnalytics() {
    try {
        // Get pending analytics from IndexedDB
        const pendingAnalytics = await getStoredData('pendingAnalytics');
        
        if (pendingAnalytics && pendingAnalytics.length > 0) {
            // Send to server
            const response = await fetch('/api/v1/analytics/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ events: pendingAnalytics })
            });
            
            if (response.ok) {
                // Clear pending analytics
                await clearStoredData('pendingAnalytics');
                console.log(`[SW ${SW_VERSION}] Analytics synced successfully`);
            }
        }
    } catch (error) {
        console.error(`[SW ${SW_VERSION}] Failed to sync analytics:`, error);
        throw error;
    }
}

/**
 * Sync notifications
 */
async function syncNotifications() {
    try {
        // Fetch latest notifications from server
        const response = await fetch('/api/v1/notifications');
        
        if (response.ok) {
            const notifications = await response.json();
            
            // Store notifications in IndexedDB
            await storeData('notifications', notifications);
            console.log(`[SW ${SW_VERSION}] Notifications synced successfully`);
        }
    } catch (error) {
        console.error(`[SW ${SW_VERSION}] Failed to sync notifications:`, error);
        throw error;
    }
}

// ================================
// INDEXEDDB HELPER FUNCTIONS
// ================================

/**
 * Store data in IndexedDB
 */
async function storeData(key, data) {
    // Placeholder for IndexedDB implementation
    // This will be implemented in the offline data management component
    return new Promise((resolve) => {
        // Simulate async operation
        setTimeout(() => resolve(true), 100);
    });
}

/**
 * Get data from IndexedDB
 */
async function getStoredData(key) {
    // Placeholder for IndexedDB implementation
    // This will be implemented in the offline data management component
    return new Promise((resolve) => {
        // Simulate async operation
        setTimeout(() => resolve(null), 100);
    });
}

/**
 * Clear data from IndexedDB
 */
async function clearStoredData(key) {
    // Placeholder for IndexedDB implementation
    // This will be implemented in the offline data management component
    return new Promise((resolve) => {
        // Simulate async operation
        setTimeout(() => resolve(true), 100);
    });
}

console.log(`[SW ${SW_VERSION}] Service Worker script loaded successfully`);
