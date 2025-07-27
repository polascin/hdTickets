// HD Tickets PWA Service Worker
// Version 1.3.0 - Enhanced PWA Features with Advanced Caching and Push Notifications

const CACHE_NAME = 'hd-tickets-v1.3';
const STATIC_CACHE = 'hd-tickets-static-v1.3';
const DYNAMIC_CACHE = 'hd-tickets-dynamic-v1.3';
const API_CACHE = 'hd-tickets-api-v1.3';
const IMAGE_CACHE = 'hd-tickets-images-v1.3';
const OFFLINE_URL = '/offline.html';
const FALLBACK_IMAGE = '/assets/images/hdTicketsLogo.png';

// Performance metrics tracking
let installStartTime = null;
let activateStartTime = null;

// Assets to cache for offline functionality
const STATIC_CACHE_URLS = [
  '/',
  '/dashboard',
  '/admin/scraping',
  '/tickets/alerts',
  '/tickets/scraping',
  '/manifest.json',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/images/hdTicketsLogo.png',
  // Add timestamp to prevent caching issues
  `/offline.html?t=${Date.now()}`
];

// Admin-specific API endpoints to cache
const ADMIN_API_ENDPOINTS = [
  '/admin/scraping/stats',
  '/api/admin/platforms',
  '/api/admin/operations',
  '/admin/scraping/configuration'
];

// Dynamic cache patterns
const CACHE_PATTERNS = {
  api: /^\/api\//,
  tickets: /^\/tickets\//,
  assets: /\.(css|js|png|jpg|jpeg|svg|woff|woff2)$/,
  vendor: /^https:\/\/(cdn\.jsdelivr\.net|fonts\.bunny\.net|unpkg\.com)/
};

// Install event - cache essential resources
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Caching app shell');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        // Force activation of new service worker
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('[SW] Cache installation failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        // Take control of all pages
        return self.clients.claim();
      })
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip chrome-extension requests
  if (url.protocol === 'chrome-extension:') {
    return;
  }
  
  event.respondWith(handleFetch(request));
});

async function handleFetch(request) {
  const url = new URL(request.url);
  
  try {
    // Strategy 1: Network First for API calls (fresh data priority)
    if (CACHE_PATTERNS.api.test(url.pathname)) {
      return await networkFirstStrategy(request);
    }
    
    // Strategy 2: Cache First for static assets
    if (CACHE_PATTERNS.assets.test(url.pathname) || CACHE_PATTERNS.vendor.test(url.href)) {
      return await cacheFirstStrategy(request);
    }
    
    // Strategy 3: Stale While Revalidate for pages and ticket data
    if (CACHE_PATTERNS.tickets.test(url.pathname) || url.pathname.startsWith('/dashboard')) {
      return await staleWhileRevalidateStrategy(request);
    }
    
    // Default: Network with cache fallback
    return await networkWithCacheFallback(request);
    
  } catch (error) {
    console.error('[SW] Fetch failed:', error);
    
    // Return offline page for navigation requests
    if (request.destination === 'document') {
      const cache = await caches.open(CACHE_NAME);
      return await cache.match(OFFLINE_URL);
    }
    
    throw error;
  }
}

// Network First Strategy (for API calls)
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    throw error;
  }
}

// Cache First Strategy (for static assets)
async function cacheFirstStrategy(request) {
  const cache = await caches.open(CACHE_NAME);
  const cachedResponse = await cache.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  const networkResponse = await fetch(request);
  
  if (networkResponse.ok) {
    cache.put(request, networkResponse.clone());
  }
  
  return networkResponse;
}

// Stale While Revalidate Strategy (for dynamic content)
async function staleWhileRevalidateStrategy(request) {
  const cache = await caches.open(CACHE_NAME);
  const cachedResponse = await cache.match(request);
  
  // Always try to fetch fresh data in background
  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  }).catch(() => {
    // Silently fail background updates
  });
  
  // Return cached version immediately if available
  return cachedResponse || fetchPromise;
}

// Network with Cache Fallback (default strategy)
async function networkWithCacheFallback(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cache = await caches.open(CACHE_NAME);
    return await cache.match(request);
  }
}

// Background Sync for offline actions
self.addEventListener('sync', event => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  if (event.tag === 'ticket-alerts-sync') {
    event.waitUntil(syncTicketAlerts());
  }
  
  if (event.tag === 'user-preferences-sync') {
    event.waitUntil(syncUserPreferences());
  }
  
  if (event.tag === 'analytics-sync') {
    event.waitUntil(syncAnalytics());
  }
  
  // Admin-specific sync events
  if (event.tag === 'admin-scraping-config-sync') {
    event.waitUntil(syncAdminScrapingConfig());
  }
  
  if (event.tag === 'admin-rotation-test-sync') {
    event.waitUntil(syncAdminRotationTests());
  }
  
  if (event.tag === 'admin-anti-detection-sync') {
    event.waitUntil(syncAdminAntiDetection());
  }
});

// Sync ticket alerts when online
async function syncTicketAlerts() {
  try {
    console.log('[SW] Syncing ticket alerts...');
    
    // Get pending alerts from IndexedDB or localStorage
    const pendingAlerts = await getPendingAlerts();
    
    for (const alert of pendingAlerts) {
      try {
        const response = await fetch('/api/tickets/alerts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(alert.data)
        });
        
        if (response.ok) {
          await removePendingAlert(alert.id);
          console.log('[SW] Alert synced successfully:', alert.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync alert:', alert.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Ticket alerts sync failed:', error);
  }
}

// Sync user preferences when online
async function syncUserPreferences() {
  try {
    console.log('[SW] Syncing user preferences...');
    
    const pendingPrefs = await getPendingPreferences();
    
    for (const pref of pendingPrefs) {
      try {
        const response = await fetch('/api/user/preferences', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(pref.data)
        });
        
        if (response.ok) {
          await removePendingPreference(pref.id);
          console.log('[SW] Preference synced successfully:', pref.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync preference:', pref.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] User preferences sync failed:', error);
  }
}

// Sync analytics data when online
async function syncAnalytics() {
  try {
    console.log('[SW] Syncing analytics data...');
    
    const pendingAnalytics = await getPendingAnalytics();
    
    for (const analytics of pendingAnalytics) {
      try {
        const response = await fetch('/api/analytics/events', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(analytics.data)
        });
        
        if (response.ok) {
          await removePendingAnalytics(analytics.id);
          console.log('[SW] Analytics synced successfully:', analytics.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync analytics:', analytics.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Analytics sync failed:', error);
  }
}

// Push notification handling
self.addEventListener('push', event => {
  console.log('[SW] Push notification received');
  
  const options = {
    body: 'New ticket alert available!',
    icon: '/assets/images/pwa/icon-192x192.png',
    badge: '/assets/images/pwa/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'view',
        title: 'View Alert',
        icon: '/assets/images/pwa/action-view.png'
      },
      {
        action: 'dismiss',
        title: 'Dismiss',
        icon: '/assets/images/pwa/action-dismiss.png'
      }
    ]
  };
  
  if (event.data) {
    try {
      const payload = event.data.json();
      options.title = payload.title || 'HD Tickets Alert';
      options.body = payload.body || options.body;
      options.data = { ...options.data, ...payload.data };
    } catch (error) {
      console.error('[SW] Failed to parse push payload:', error);
      options.title = 'HD Tickets Alert';
    }
  } else {
    options.title = 'HD Tickets Alert';
  }
  
  event.waitUntil(
    self.registration.showNotification(options.title, options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  console.log('[SW] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow('/tickets/alerts')
    );
  } else if (event.action === 'dismiss') {
    // Just close the notification
    return;
  } else {
    // Default action - open main dashboard
    event.waitUntil(
      clients.openWindow('/dashboard')
    );
  }
});

// Helper functions for IndexedDB operations
async function getPendingAlerts() {
  // Implement IndexedDB operations or use localStorage as fallback
  const pending = localStorage.getItem('hd-tickets-pending-alerts');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingAlert(id) {
  const pending = await getPendingAlerts();
  const filtered = pending.filter(alert => alert.id !== id);
  localStorage.setItem('hd-tickets-pending-alerts', JSON.stringify(filtered));
}

async function getPendingPreferences() {
  const pending = localStorage.getItem('hd-tickets-pending-preferences');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingPreference(id) {
  const pending = await getPendingPreferences();
  const filtered = pending.filter(pref => pref.id !== id);
  localStorage.setItem('hd-tickets-pending-preferences', JSON.stringify(filtered));
}

async function getPendingAnalytics() {
  const pending = localStorage.getItem('hd-tickets-pending-analytics');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingAnalytics(id) {
  const pending = await getPendingAnalytics();
  const filtered = pending.filter(analytics => analytics.id !== id);
  localStorage.setItem('hd-tickets-pending-analytics', JSON.stringify(filtered));
}

// Message handling from main thread
self.addEventListener('message', event => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(CACHE_NAME).then(cache => {
        return cache.addAll(event.data.payload);
      })
    );
  }
});

// Admin-specific sync functions
async function syncAdminScrapingConfig() {
  try {
    console.log('[SW] Syncing admin scraping configurations...');
    
    const pendingConfigs = await getPendingAdminConfigs();
    
    for (const config of pendingConfigs) {
      try {
        const response = await fetch('/admin/scraping/configuration', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(config.data)
        });
        
        if (response.ok) {
          await removePendingAdminConfig(config.id);
          console.log('[SW] Admin config synced successfully:', config.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync admin config:', config.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Admin scraping config sync failed:', error);
  }
}

async function syncAdminRotationTests() {
  try {
    console.log('[SW] Syncing admin rotation tests...');
    
    const pendingTests = await getPendingAdminRotationTests();
    
    for (const test of pendingTests) {
      try {
        const response = await fetch('/admin/scraping/rotation-test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(test.data)
        });
        
        if (response.ok) {
          await removePendingAdminRotationTest(test.id);
          console.log('[SW] Admin rotation test synced successfully:', test.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync admin rotation test:', test.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Admin rotation test sync failed:', error);
  }
}

async function syncAdminAntiDetection() {
  try {
    console.log('[SW] Syncing admin anti-detection settings...');
    
    const pendingSettings = await getPendingAdminAntiDetection();
    
    for (const setting of pendingSettings) {
      try {
        const response = await fetch('/admin/scraping/configure-anti-detection', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(setting.data)
        });
        
        if (response.ok) {
          await removePendingAdminAntiDetection(setting.id);
          console.log('[SW] Admin anti-detection setting synced successfully:', setting.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync admin anti-detection setting:', setting.id, error);
      }
    }
  } catch (error) {
    console.error('[SW] Admin anti-detection sync failed:', error);
  }
}

// Admin-specific helper functions
async function getPendingAdminConfigs() {
  const pending = localStorage.getItem('hd-tickets-pending-admin-configs');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingAdminConfig(id) {
  const pending = await getPendingAdminConfigs();
  const filtered = pending.filter(config => config.id !== id);
  localStorage.setItem('hd-tickets-pending-admin-configs', JSON.stringify(filtered));
}

async function getPendingAdminRotationTests() {
  const pending = localStorage.getItem('hd-tickets-pending-admin-rotation-tests');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingAdminRotationTest(id) {
  const pending = await getPendingAdminRotationTests();
  const filtered = pending.filter(test => test.id !== id);
  localStorage.setItem('hd-tickets-pending-admin-rotation-tests', JSON.stringify(filtered));
}

async function getPendingAdminAntiDetection() {
  const pending = localStorage.getItem('hd-tickets-pending-admin-anti-detection');
  return pending ? JSON.parse(pending) : [];
}

async function removePendingAdminAntiDetection(id) {
  const pending = await getPendingAdminAntiDetection();
  const filtered = pending.filter(setting => setting.id !== id);
  localStorage.setItem('hd-tickets-pending-admin-anti-detection', JSON.stringify(filtered));
}

// Periodic Background Sync for ticket updates
self.addEventListener('periodicsync', event => {
  console.log('[SW] Periodic sync triggered:', event.tag);
  
  if (event.tag === 'ticket-updates') {
    event.waitUntil(fetchLatestTickets());
  }
  
  if (event.tag === 'admin-monitoring') {
    event.waitUntil(performAdminHealthCheck());
  }
});

// Fetch latest tickets in background
async function fetchLatestTickets() {
  try {
    console.log('[SW] Fetching latest tickets in background...');
    
    const response = await fetch('/api/tickets/latest', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.ok) {
      const tickets = await response.json();
      
      // Check for high-demand tickets and send notifications
      const highDemandTickets = tickets.filter(ticket => ticket.priority === 'high');
      
      for (const ticket of highDemandTickets) {
        await self.registration.showNotification('High Demand Ticket Found!', {
          body: `${ticket.event} - ${ticket.venue}`,
          icon: '/assets/images/pwa/icon-192x192.png',
          badge: '/assets/images/pwa/icon-72x72.png',
          tag: `ticket-${ticket.id}`,
          data: { ticketId: ticket.id, url: `/tickets/${ticket.id}` },
          actions: [
            {
              action: 'view',
              title: 'View Details'
            },
            {
              action: 'purchase',
              title: 'Purchase Now'
            }
          ]
        });
      }
      
      console.log(`[SW] Found ${highDemandTickets.length} high-demand tickets`);
    }
  } catch (error) {
    console.error('[SW] Failed to fetch latest tickets:', error);
  }
}

// Admin health check
async function performAdminHealthCheck() {
  try {
    console.log('[SW] Performing admin health check...');
    
    const response = await fetch('/api/admin/health', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.ok) {
      const health = await response.json();
      
      // Send notification if any services are down
      if (health.status !== 'healthy') {
        await self.registration.showNotification('System Alert', {
          body: `Some services need attention: ${health.issues.join(', ')}`,
          icon: '/assets/images/pwa/icon-192x192.png',
          badge: '/assets/images/pwa/icon-72x72.png',
          tag: 'admin-health',
          data: { url: '/admin/system' },
          requireInteraction: true
        });
      }
    }
  } catch (error) {
    console.error('[SW] Admin health check failed:', error);
  }
}

// Web Share Target API handling
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Handle shared content
  if (url.pathname === '/admin/import-shared' && event.request.method === 'POST') {
    event.respondWith(handleSharedContent(event.request));
  }
});

async function handleSharedContent(request) {
  try {
    const formData = await request.formData();
    const sharedData = formData.get('data');
    
    if (sharedData) {
      // Store shared data for processing
      const id = Date.now().toString();
      const item = {
        id,
        data: sharedData,
        timestamp: Date.now(),
        processed: false
      };
      
      const existing = JSON.parse(localStorage.getItem('hd-tickets-shared-data') || '[]');
      existing.push(item);
      localStorage.setItem('hd-tickets-shared-data', JSON.stringify(existing));
      
      // Redirect to import page
      return Response.redirect('/admin/scraping?shared=true', 302);
    }
  } catch (error) {
    console.error('[SW] Failed to handle shared content:', error);
  }
  
  return new Response('Shared content processing failed', { status: 400 });
}

// File handling for CSV and JSON imports
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  if (url.pathname === '/admin/import' && event.request.method === 'POST') {
    event.respondWith(handleFileImport(event.request));
  }
});

async function handleFileImport(request) {
  try {
    const formData = await request.formData();
    const file = formData.get('file');
    
    if (file) {
      // Validate file type
      const allowedTypes = ['text/csv', 'application/json'];
      if (!allowedTypes.includes(file.type)) {
        return new Response('Invalid file type', { status: 400 });
      }
      
      // Store file data for processing
      const fileData = await file.text();
      const id = Date.now().toString();
      const item = {
        id,
        filename: file.name,
        type: file.type,
        data: fileData,
        timestamp: Date.now(),
        processed: false
      };
      
      const existing = JSON.parse(localStorage.getItem('hd-tickets-imported-files') || '[]');
      existing.push(item);
      localStorage.setItem('hd-tickets-imported-files', JSON.stringify(existing));
      
      // Redirect to processing page
      return Response.redirect(`/admin/scraping?import=${id}`, 302);
    }
  } catch (error) {
    console.error('[SW] Failed to handle file import:', error);
  }
  
  return new Response('File import failed', { status: 400 });
}

// Enhanced notification actions
self.addEventListener('notificationclick', event => {
  console.log('[SW] Notification clicked:', event.action, event.notification.tag);
  
  event.notification.close();
  
  const data = event.notification.data || {};
  
  switch (event.action) {
    case 'view':
      event.waitUntil(clients.openWindow(data.url || '/dashboard'));
      break;
      
    case 'purchase':
      if (data.ticketId) {
        event.waitUntil(clients.openWindow(`/purchase-decisions/select-tickets?ticket=${data.ticketId}`));
      }
      break;
      
    case 'dismiss':
      // Just close, no action needed
      break;
      
    default:
      // Default action based on notification tag
      if (event.notification.tag.startsWith('ticket-')) {
        event.waitUntil(clients.openWindow('/tickets/scraping'));
      } else if (event.notification.tag === 'admin-health') {
        event.waitUntil(clients.openWindow('/admin/system'));
      } else {
        event.waitUntil(clients.openWindow('/dashboard'));
      }
  }
});

// Cache management and cleanup
self.addEventListener('message', event => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(CACHE_NAME).then(cache => {
        return cache.addAll(event.data.payload);
      })
    );
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(clearOldCaches());
  }
  
  if (event.data && event.data.type === 'GET_CACHE_SIZE') {
    event.waitUntil(getCacheSize().then(size => {
      event.ports[0].postMessage({ type: 'CACHE_SIZE', size });
    }));
  }
});

// Clear old caches
async function clearOldCaches() {
  const cacheNames = await caches.keys();
  const currentCaches = [CACHE_NAME, STATIC_CACHE, DYNAMIC_CACHE, API_CACHE, IMAGE_CACHE];
  
  return Promise.all(
    cacheNames.map(cacheName => {
      if (!currentCaches.includes(cacheName)) {
        console.log('[SW] Deleting old cache:', cacheName);
        return caches.delete(cacheName);
      }
    })
  );
}

// Get cache size for diagnostics
async function getCacheSize() {
  const cacheNames = await caches.keys();
  let totalSize = 0;
  
  for (const cacheName of cacheNames) {
    const cache = await caches.open(cacheName);
    const requests = await cache.keys();
    
    for (const request of requests) {
      const response = await cache.match(request);
      if (response) {
        const size = response.headers.get('content-length');
        if (size) {
          totalSize += parseInt(size);
        }
      }
    }
  }
  
  return totalSize;
}

// Performance monitoring
self.addEventListener('install', event => {
  installStartTime = performance.now();
});

self.addEventListener('activate', event => {
  activateStartTime = performance.now();
  
  // Send performance metrics
  event.waitUntil(
    self.clients.matchAll().then(clients => {
      clients.forEach(client => {
        client.postMessage({
          type: 'SW_PERFORMANCE',
          installTime: installStartTime,
          activateTime: activateStartTime
        });
      });
    })
  );
});

console.log('[SW] Service Worker v1.3.0 loaded with advanced PWA features');
console.log('[SW] Features: Push notifications, Background sync, File handling, Web Share, Cache management');
