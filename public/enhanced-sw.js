// Enhanced Service Worker for HD Tickets PWA
// Provides offline capability, caching, and background sync

const CACHE_NAME = 'hd-tickets-v1.2.0';
const STATIC_CACHE = `${CACHE_NAME}-static`;
const DYNAMIC_CACHE = `${CACHE_NAME}-dynamic`;
const API_CACHE = `${CACHE_NAME}-api`;

// Resources to cache immediately
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/offline.html',
    '/manifest.json',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
    '/images/logo.svg'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/api/tickets',
    '/api/alerts',
    '/api/user/profile'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('[SW] Installing Service Worker');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('[SW] Failed to cache static assets:', error);
            })
    );
});

// Activate event - cleanup old caches
self.addEventListener('activate', event => {
    console.log('[SW] Activating Service Worker');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && 
                            cacheName !== DYNAMIC_CACHE && 
                            cacheName !== API_CACHE) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const { url, method } = request;
    
    // Only handle GET requests
    if (method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other non-http requests
    if (!url.startsWith('http')) {
        return;
    }
    
    // Different strategies for different types of requests
    if (isStaticAsset(url)) {
        event.respondWith(handleStaticAssets(request));
    } else if (isAPIRequest(url)) {
        event.respondWith(handleAPIRequests(request));
    } else if (isPageRequest(request)) {
        event.respondWith(handlePageRequests(request));
    } else {
        event.respondWith(handleOtherRequests(request));
    }
});

// Handle static assets - Cache First strategy
async function handleStaticAssets(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const fetchResponse = await fetch(request);
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, fetchResponse.clone());
        
        return fetchResponse;
    } catch (error) {
        console.error('[SW] Static asset fetch failed:', error);
        // Return offline fallback for images
        if (request.destination === 'image') {
            return new Response(getOfflineImageSVG(), {
                headers: { 'Content-Type': 'image/svg+xml' }
            });
        }
        throw error;
    }
}

// Handle API requests - Network First with cache fallback
async function handleAPIRequests(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] API request failed, trying cache:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            // Add custom header to indicate offline response
            const response = cachedResponse.clone();
            response.headers.append('X-Served-From-Cache', 'true');
            return response;
        }
        
        // Return offline API response
        return new Response(
            JSON.stringify({
                error: 'Offline',
                message: 'No network connection available',
                cached: false
            }),
            {
                status: 503,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Offline-Response': 'true'
                }
            }
        );
    }
}

// Handle page requests - Network First with cache fallback
async function handlePageRequests(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Page request failed, trying cache:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page
        return caches.match('/offline.html');
    }
}

// Handle other requests - Cache First
async function handleOtherRequests(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const fetchResponse = await fetch(request);
        const cache = await caches.open(DYNAMIC_CACHE);
        cache.put(request, fetchResponse.clone());
        
        return fetchResponse;
    } catch (error) {
        console.error('[SW] Other request failed:', error);
        throw error;
    }
}

// Background sync for failed requests
self.addEventListener('sync', event => {
    console.log('[SW] Background sync triggered:', event.tag);
    
    if (event.tag === 'background-sync-tickets') {
        event.waitUntil(syncTickets());
    } else if (event.tag === 'background-sync-alerts') {
        event.waitUntil(syncAlerts());
    } else if (event.tag === 'background-sync-purchases') {
        event.waitUntil(syncPurchases());
    }
});

// Push notifications
self.addEventListener('push', event => {
    console.log('[SW] Push notification received');
    
    let notificationData = {
        title: 'HD Tickets',
        body: 'You have new updates!',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/badge-72x72.png',
        tag: 'default'
    };
    
    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = { ...notificationData, ...data };
        } catch (error) {
            console.error('[SW] Failed to parse push data:', error);
        }
    }
    
    const options = {
        body: notificationData.body,
        icon: notificationData.icon,
        badge: notificationData.badge,
        tag: notificationData.tag,
        data: notificationData.data || {},
        actions: notificationData.actions || [],
        requireInteraction: notificationData.requireInteraction || false,
        silent: notificationData.silent || false,
        vibrate: notificationData.vibrate || [200, 100, 200]
    };
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    console.log('[SW] Notification clicked:', event.notification.data);
    
    event.notification.close();
    
    const data = event.notification.data;
    const action = event.action;
    
    let url = '/';
    
    if (data.type === 'price_alert') {
        url = `/tickets/${data.ticketId}`;
    } else if (data.type === 'purchase_complete') {
        url = `/orders/${data.orderId}`;
    } else if (data.url) {
        url = data.url;
    }
    
    // Handle notification actions
    if (action === 'view_ticket') {
        url = `/tickets/${data.ticketId}`;
    } else if (action === 'dismiss') {
        return; // Just close notification
    }
    
    event.waitUntil(
        clients.openWindow(url)
    );
});

// Message handling for communication with main thread
self.addEventListener('message', event => {
    console.log('[SW] Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    } else if (event.data && event.data.type === 'CLEAR_CACHE') {
        clearAllCaches().then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }
});

// Utility functions
function isStaticAsset(url) {
    return url.includes('/css/') || 
           url.includes('/js/') || 
           url.includes('/images/') || 
           url.includes('/fonts/') || 
           url.endsWith('.css') || 
           url.endsWith('.js') || 
           url.endsWith('.png') || 
           url.endsWith('.jpg') || 
           url.endsWith('.jpeg') || 
           url.endsWith('.svg') || 
           url.endsWith('.woff') || 
           url.endsWith('.woff2');
}

function isAPIRequest(url) {
    return url.includes('/api/') || 
           API_ENDPOINTS.some(endpoint => url.includes(endpoint));
}

function isPageRequest(request) {
    return request.destination === 'document';
}

function getOfflineImageSVG() {
    return `<svg width="200" height="150" xmlns="http://www.w3.org/2000/svg">
        <rect width="200" height="150" fill="#f3f4f6"/>
        <text x="100" y="75" font-family="Arial, sans-serif" font-size="14" 
              fill="#9ca3af" text-anchor="middle" dominant-baseline="middle">
            Image unavailable offline
        </text>
    </svg>`;
}

// Background sync functions
async function syncTickets() {
    try {
        console.log('[SW] Syncing tickets in background');
        
        const pendingRequests = await getStoredRequests('tickets');
        
        for (const request of pendingRequests) {
            try {
                const response = await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: request.body
                });
                
                if (response.ok) {
                    await removeStoredRequest('tickets', request.id);
                    console.log('[SW] Successfully synced ticket request:', request.id);
                }
            } catch (error) {
                console.error('[SW] Failed to sync ticket request:', request.id, error);
            }
        }
    } catch (error) {
        console.error('[SW] Background ticket sync failed:', error);
    }
}

async function syncAlerts() {
    try {
        console.log('[SW] Syncing alerts in background');
        
        const pendingRequests = await getStoredRequests('alerts');
        
        for (const request of pendingRequests) {
            try {
                const response = await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: request.body
                });
                
                if (response.ok) {
                    await removeStoredRequest('alerts', request.id);
                    console.log('[SW] Successfully synced alert request:', request.id);
                    
                    // Notify user of successful alert creation
                    self.registration.showNotification('Alert Created', {
                        body: 'Your price alert has been set up successfully!',
                        icon: '/images/icons/icon-192x192.png',
                        tag: 'alert-created'
                    });
                }
            } catch (error) {
                console.error('[SW] Failed to sync alert request:', request.id, error);
            }
        }
    } catch (error) {
        console.error('[SW] Background alert sync failed:', error);
    }
}

async function syncPurchases() {
    try {
        console.log('[SW] Syncing purchases in background');
        
        const pendingRequests = await getStoredRequests('purchases');
        
        for (const request of pendingRequests) {
            try {
                const response = await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: request.body
                });
                
                if (response.ok) {
                    await removeStoredRequest('purchases', request.id);
                    console.log('[SW] Successfully synced purchase request:', request.id);
                    
                    // Notify user of successful purchase
                    const data = JSON.parse(request.body);
                    self.registration.showNotification('Purchase Confirmed!', {
                        body: `Your tickets for ${data.eventTitle} have been confirmed.`,
                        icon: '/images/icons/icon-192x192.png',
                        tag: 'purchase-confirmed',
                        actions: [
                            {
                                action: 'view_order',
                                title: 'View Order'
                            },
                            {
                                action: 'dismiss',
                                title: 'Dismiss'
                            }
                        ]
                    });
                }
            } catch (error) {
                console.error('[SW] Failed to sync purchase request:', request.id, error);
            }
        }
    } catch (error) {
        console.error('[SW] Background purchase sync failed:', error);
    }
}

// IndexedDB helpers for storing failed requests
async function getStoredRequests(type) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('HDTicketsOfflineDB', 1);
        
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['requests'], 'readonly');
            const store = transaction.objectStore('requests');
            const getRequest = store.getAll();
            
            getRequest.onsuccess = () => {
                const requests = getRequest.result.filter(req => req.type === type);
                resolve(requests);
            };
            
            getRequest.onerror = () => reject(getRequest.error);
        };
        
        request.onerror = () => reject(request.error);
        
        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('requests')) {
                db.createObjectStore('requests', { keyPath: 'id' });
            }
        };
    });
}

async function removeStoredRequest(type, id) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('HDTicketsOfflineDB', 1);
        
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['requests'], 'readwrite');
            const store = transaction.objectStore('requests');
            const deleteRequest = store.delete(id);
            
            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };
        
        request.onerror = () => reject(request.error);
    });
}

async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
    );
    console.log('[SW] All caches cleared');
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', event => {
    if (event.tag === 'price-updates') {
        event.waitUntil(syncPriceUpdates());
    }
});

async function syncPriceUpdates() {
    try {
        console.log('[SW] Syncing price updates');
        
        const response = await fetch('/api/tickets/price-updates');
        if (response.ok) {
            const updates = await response.json();
            
            // Show notification for significant price drops
            updates.forEach(update => {
                if (update.priceChange < -10) { // Price dropped by more than $10
                    self.registration.showNotification('Price Drop Alert! 🎯', {
                        body: `${update.eventTitle} tickets dropped to $${update.newPrice} (was $${update.oldPrice})`,
                        icon: '/images/icons/icon-192x192.png',
                        tag: `price-drop-${update.ticketId}`,
                        data: {
                            type: 'price_alert',
                            ticketId: update.ticketId
                        },
                        actions: [
                            {
                                action: 'view_ticket',
                                title: 'View Tickets'
                            },
                            {
                                action: 'dismiss',
                                title: 'Dismiss'
                            }
                        ]
                    });
                }
            });
        }
    } catch (error) {
        console.error('[SW] Failed to sync price updates:', error);
    }
}

console.log('[SW] Enhanced Service Worker loaded successfully');