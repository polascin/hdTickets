/**
 * HD Tickets Service Worker - Enhanced PWA Implementation
 * 
 * Advanced Progressive Web App service worker for the HD Tickets platform.
 * Implements comprehensive caching strategies, background sync, push notifications,
 * and offline functionality for sports events ticket monitoring.
 * 
 * @version 2.0.0
 * @author HD Tickets Development Team
 */

const CACHE_VERSION = 'v2.1.0';
const STATIC_CACHE = `hdtickets-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `hdtickets-dynamic-${CACHE_VERSION}`;
const API_CACHE = `hdtickets-api-${CACHE_VERSION}`;
const IMAGE_CACHE = `hdtickets-images-${CACHE_VERSION}`;

// Cache configuration
const CACHE_CONFIG = {
    static: {
        maxEntries: 100,
        maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
    },
    dynamic: {
        maxEntries: 50,
        maxAgeSeconds: 24 * 60 * 60, // 24 hours
    },
    api: {
        maxEntries: 200,
        maxAgeSeconds: 60 * 60, // 1 hour
    },
    images: {
        maxEntries: 150,
        maxAgeSeconds: 7 * 24 * 60 * 60, // 7 days
    }
};

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/css/app.css',
    '/css/responsive-design.css',
    '/css/accessibility.css',
    '/css/theme-toggle.css',
    '/js/app.js',
    '/js/alpine.js',
    '/js/responsive-design.js',
    '/js/accessibility-manager.js',
    '/assets/images/hdTicketsLogo.png',
    '/assets/images/pwa/icon-192x192.png',
    '/assets/images/pwa/icon-512x512.png',
];

// Background sync tags
const SYNC_TAGS = {
    PURCHASE_FORM: 'purchase-form-sync',
    ALERT_FORM: 'alert-form-sync',
    PROFILE_UPDATE: 'profile-update-sync',
    PREFERENCE_UPDATE: 'preference-update-sync'
};

// Notification configuration
const NOTIFICATION_CONFIG = {
    badge: '/assets/images/pwa/icon-96x96.png',
    icon: '/assets/images/pwa/icon-192x192.png',
    vibrate: [200, 100, 200],
    actions: [
        {
            action: 'view',
            title: 'View Details',
            icon: '/assets/images/pwa/action-view.png'
        },
        {
            action: 'dismiss',
            title: 'Dismiss',
            icon: '/assets/images/pwa/action-dismiss.png'
        }
    ]
};

/**
 * Installation Event - Cache static assets
 */
self.addEventListener('install', event => {
    console.log('[SW] Installing HD Tickets Service Worker v2.0.0');
    
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
                console.error('[SW] Error caching static assets:', error);
            })
    );
});

/**
 * Activation Event - Clean up old caches and claim clients
 */
self.addEventListener('activate', event => {
    console.log('[SW] Activating HD Tickets Service Worker v2.0.0');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                const deletePromises = cacheNames
                    .filter(cacheName => {
                        return cacheName.startsWith('hdtickets-') && 
                               ![STATIC_CACHE, DYNAMIC_CACHE, API_CACHE, IMAGE_CACHE].includes(cacheName);
                    })
                    .map(cacheName => {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    });
                
                return Promise.all(deletePromises);
            })
            .then(() => {
                console.log('[SW] Cache cleanup completed');
                return self.clients.claim();
            })
            .catch(error => {
                console.error('[SW] Error during activation:', error);
            })
    );
});

/**
 * Fetch Event - Implement caching strategies
 */
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests for caching
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip Chrome extension requests
    if (url.protocol === 'chrome-extension:') {
        return;
    }
    
    event.respondWith(handleRequest(request));
});

/**
 * Handle different types of requests with appropriate caching strategies
 */
async function handleRequest(request) {
    const url = new URL(request.url);
    
    try {
        // API requests - Network first with cache fallback
        if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/ajax/')) {
            return await handleApiRequest(request);
        }
        
        // Image requests - Cache first with network fallback
        if (request.destination === 'image' || url.pathname.match(/\.(png|jpg|jpeg|gif|webp|svg)$/i)) {
            return await handleImageRequest(request);
        }
        
        // Static assets - Cache first
        if (url.pathname.match(/\.(css|js|woff2|woff|ttf|eot)$/i)) {
            return await handleStaticRequest(request);
        }
        
        // Navigation requests - Network first with offline fallback
        if (request.mode === 'navigate') {
            return await handleNavigationRequest(request);
        }
        
        // Default: Network first
        return await handleDefaultRequest(request);
        
    } catch (error) {
        console.error('[SW] Error handling request:', error);
        return await handleOfflineRequest(request);
    }
}

/**
 * Handle API requests with network-first strategy
 */
async function handleApiRequest(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(API_CACHE);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
            await cleanupCache(API_CACHE, CACHE_CONFIG.api);
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] API network failed, trying cache:', request.url);
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline API response for critical endpoints
        if (request.url.includes('/dashboard/stats') || request.url.includes('/tickets/load')) {
            return new Response(
                JSON.stringify({
                    error: 'Offline',
                    message: 'This data is not available offline. Please check your connection.',
                    cached: false
                }),
                {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: {
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache'
                    }
                }
            );
        }
        
        throw error;
    }
}

/**
 * Handle image requests with cache-first strategy
 */
async function handleImageRequest(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(IMAGE_CACHE);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
            await cleanupCache(IMAGE_CACHE, CACHE_CONFIG.images);
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Image network failed:', request.url);
        
        // Return placeholder image for failed image requests
        return new Response(
            createPlaceholderSVG('Image not available offline'),
            {
                headers: {
                    'Content-Type': 'image/svg+xml',
                    'Cache-Control': 'no-cache'
                }
            }
        );
    }
}

/**
 * Handle static asset requests with cache-first strategy
 */
async function handleStaticRequest(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
            await cleanupCache(STATIC_CACHE, CACHE_CONFIG.static);
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Static asset network failed:', request.url);
        throw error;
    }
}

/**
 * Handle navigation requests with network-first strategy and offline fallback
 */
async function handleNavigationRequest(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
            await cleanupCache(DYNAMIC_CACHE, CACHE_CONFIG.dynamic);
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Navigation network failed, trying cache:', request.url);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page for navigation requests
        const offlineResponse = await caches.match('/offline.html');
        if (offlineResponse) {
            return offlineResponse;
        }
        
        // Fallback offline page
        return new Response(
            createOfflineHTML(),
            {
                status: 200,
                statusText: 'OK',
                headers: {
                    'Content-Type': 'text/html'
                }
            }
        );
    }
}

/**
 * Handle default requests with network-first strategy
 */
async function handleDefaultRequest(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
            await cleanupCache(DYNAMIC_CACHE, CACHE_CONFIG.dynamic);
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        throw error;
    }
}

/**
 * Handle offline requests
 */
async function handleOfflineRequest(request) {
    if (request.mode === 'navigate') {
        const offlineResponse = await caches.match('/offline.html');
        return offlineResponse || new Response(createOfflineHTML(), {
            headers: { 'Content-Type': 'text/html' }
        });
    }
    
    return new Response('Offline', {
        status: 503,
        statusText: 'Service Unavailable'
    });
}

/**
 * Background Sync Event - Handle failed form submissions
 */
self.addEventListener('sync', event => {
    console.log('[SW] Background sync event:', event.tag);
    
    switch (event.tag) {
        case SYNC_TAGS.PURCHASE_FORM:
            event.waitUntil(syncPurchaseForm());
            break;
        case SYNC_TAGS.ALERT_FORM:
            event.waitUntil(syncAlertForm());
            break;
        case SYNC_TAGS.PROFILE_UPDATE:
            event.waitUntil(syncProfileUpdate());
            break;
        case SYNC_TAGS.PREFERENCE_UPDATE:
            event.waitUntil(syncPreferenceUpdate());
            break;
        default:
            console.log('[SW] Unknown sync tag:', event.tag);
    }
});

/**
 * Push Event - Handle push notifications
 */
self.addEventListener('push', event => {
    console.log('[SW] Push notification received');
    
    let notificationData = {
        title: 'HD Tickets',
        body: 'You have a new notification',
        ...NOTIFICATION_CONFIG
    };
    
    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = { ...notificationData, ...data };
        } catch (error) {
            console.error('[SW] Error parsing push data:', error);
            notificationData.body = event.data.text() || notificationData.body;
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

/**
 * Notification Click Event - Handle notification interactions
 */
self.addEventListener('notificationclick', event => {
    console.log('[SW] Notification clicked:', event.notification.tag);
    
    event.notification.close();
    
    if (event.action === 'dismiss') {
        return;
    }
    
    const urlToOpen = event.notification.data?.url || '/dashboard';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Check if there's already a window/tab open with the target URL
                for (const client of clientList) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Open new window/tab
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

/**
 * Message Event - Handle messages from the main thread
 */
self.addEventListener('message', event => {
    console.log('[SW] Message received:', event.data);
    
    const { type, payload } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: CACHE_VERSION });
            break;
        case 'CACHE_URLS':
            event.waitUntil(cacheUrls(payload.urls));
            break;
        case 'CLEAR_CACHE':
            event.waitUntil(clearSpecificCache(payload.cacheName));
            break;
        case 'GET_CACHE_SIZE':
            event.waitUntil(getCacheSize().then(size => {
                event.ports[0].postMessage({ cacheSize: size });
            }));
            break;
        default:
            console.log('[SW] Unknown message type:', type);
    }
});

/**
 * Sync purchase form data when online
 */
async function syncPurchaseForm() {
    try {
        const db = await openIDB();
        const pendingForms = await getAllFromIDB(db, 'pendingPurchases');
        
        for (const form of pendingForms) {
            try {
                const response = await fetch('/api/tickets/purchase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.csrfToken
                    },
                    body: JSON.stringify(form.data)
                });
                
                if (response.ok) {
                    await deleteFromIDB(db, 'pendingPurchases', form.id);
                    
                    // Show success notification
                    await self.registration.showNotification('Purchase Completed', {
                        body: 'Your ticket purchase has been processed successfully.',
                        ...NOTIFICATION_CONFIG,
                        tag: 'purchase-success',
                        data: { url: '/tickets/purchase-history' }
                    });
                }
            } catch (error) {
                console.error('[SW] Error syncing purchase form:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Error in syncPurchaseForm:', error);
    }
}

/**
 * Sync alert form data when online
 */
async function syncAlertForm() {
    try {
        const db = await openIDB();
        const pendingAlerts = await getAllFromIDB(db, 'pendingAlerts');
        
        for (const alert of pendingAlerts) {
            try {
                const response = await fetch('/api/tickets/alerts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': alert.csrfToken
                    },
                    body: JSON.stringify(alert.data)
                });
                
                if (response.ok) {
                    await deleteFromIDB(db, 'pendingAlerts', alert.id);
                    
                    await self.registration.showNotification('Alert Created', {
                        body: 'Your price alert has been created successfully.',
                        ...NOTIFICATION_CONFIG,
                        tag: 'alert-success'
                    });
                }
            } catch (error) {
                console.error('[SW] Error syncing alert form:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Error in syncAlertForm:', error);
    }
}

/**
 * Sync profile update data when online
 */
async function syncProfileUpdate() {
    // Implementation for profile update sync
    console.log('[SW] Syncing profile updates...');
}

/**
 * Sync preference update data when online
 */
async function syncPreferenceUpdate() {
    // Implementation for preference update sync
    console.log('[SW] Syncing preference updates...');
}

/**
 * Clean up cache entries to maintain size limits
 */
async function cleanupCache(cacheName, config) {
    try {
        const cache = await caches.open(cacheName);
        const requests = await cache.keys();
        
        if (requests.length > config.maxEntries) {
            const entriesToDelete = requests.slice(0, requests.length - config.maxEntries);
            await Promise.all(entriesToDelete.map(request => cache.delete(request)));
        }
        
        // Clean up expired entries
        const now = Date.now();
        for (const request of requests) {
            const response = await cache.match(request);
            const dateHeader = response.headers.get('date');
            
            if (dateHeader) {
                const responseDate = new Date(dateHeader).getTime();
                const ageInSeconds = (now - responseDate) / 1000;
                
                if (ageInSeconds > config.maxAgeSeconds) {
                    await cache.delete(request);
                }
            }
        }
    } catch (error) {
        console.error('[SW] Error cleaning up cache:', error);
    }
}

/**
 * Cache specific URLs
 */
async function cacheUrls(urls) {
    const cache = await caches.open(DYNAMIC_CACHE);
    await cache.addAll(urls);
}

/**
 * Clear specific cache
 */
async function clearSpecificCache(cacheName) {
    await caches.delete(cacheName);
}

/**
 * Get total cache size
 */
async function getCacheSize() {
    const cacheNames = await caches.keys();
    let totalSize = 0;
    
    for (const cacheName of cacheNames) {
        if (cacheName.startsWith('hdtickets-')) {
            const cache = await caches.open(cacheName);
            const requests = await cache.keys();
            totalSize += requests.length;
        }
    }
    
    return totalSize;
}

/**
 * Create placeholder SVG for failed image requests
 */
function createPlaceholderSVG(text) {
    return `
        <svg width="200" height="150" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="150" fill="#f3f4f6"/>
            <text x="100" y="75" font-family="Arial, sans-serif" font-size="12" 
                  fill="#6b7280" text-anchor="middle" dominant-baseline="central">
                ${text}
            </text>
        </svg>
    `;
}

/**
 * Create offline HTML page
 */
function createOfflineHTML() {
    return `
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>HD Tickets - Offline</title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    margin: 0; padding: 2rem; text-align: center; background: #f9fafb;
                }
                .container { max-width: 600px; margin: 0 auto; }
                .icon { font-size: 4rem; margin-bottom: 1rem; }
                h1 { color: #1f2937; margin-bottom: 1rem; }
                p { color: #6b7280; line-height: 1.5; margin-bottom: 2rem; }
                .retry-btn { 
                    background: #2563eb; color: white; border: none; 
                    padding: 0.75rem 1.5rem; border-radius: 0.375rem;
                    font-size: 1rem; cursor: pointer;
                }
                .retry-btn:hover { background: #1d4ed8; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon">📱</div>
                <h1>You're Offline</h1>
                <p>
                    HD Tickets requires an internet connection to function properly. 
                    Please check your connection and try again.
                </p>
                <button class="retry-btn" onclick="location.reload()">
                    Try Again
                </button>
            </div>
            <script>
                // Auto-retry when online
                window.addEventListener('online', () => location.reload());
            </script>
        </body>
        </html>
    `;
}

/**
 * IndexedDB utilities for background sync
 */
async function openIDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('HDTicketsDB', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            if (!db.objectStoreNames.contains('pendingPurchases')) {
                db.createObjectStore('pendingPurchases', { keyPath: 'id', autoIncrement: true });
            }
            
            if (!db.objectStoreNames.contains('pendingAlerts')) {
                db.createObjectStore('pendingAlerts', { keyPath: 'id', autoIncrement: true });
            }
            
            if (!db.objectStoreNames.contains('pendingProfileUpdates')) {
                db.createObjectStore('pendingProfileUpdates', { keyPath: 'id', autoIncrement: true });
            }
            
            if (!db.objectStoreNames.contains('pendingPreferences')) {
                db.createObjectStore('pendingPreferences', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

async function getAllFromIDB(db, storeName) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

async function deleteFromIDB(db, storeName, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

console.log('[SW] HD Tickets Service Worker v2.0.0 loaded successfully');
