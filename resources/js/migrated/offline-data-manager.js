/**
 * HD Tickets Offline Data Manager
 * 
 * Comprehensive offline data management system using IndexedDB
 * Features:
 * - IndexedDB database management
 * - Offline-first data storage
 * - Automatic synchronization
 * - Data versioning and migration
 * - Conflict resolution
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 */

class HDTicketsOfflineDataManager {
    constructor() {
        this.config = {
            // Database configuration
            dbName: 'HDTicketsDB',
            dbVersion: 1,
            
            // Store names
            stores: {
                tickets: 'tickets',
                events: 'events',
                notifications: 'notifications',
                preferences: 'user_preferences',
                alerts: 'pending_alerts',
                analytics: 'pending_analytics',
                cache: 'api_cache',
                sync_queue: 'sync_queue'
            },
            
            // Sync configuration
            sync: {
                maxRetries: 3,
                retryDelay: 5000, // 5 seconds
                batchSize: 50,
                enabled: true
            },
            
            // Cache expiration times (in milliseconds)
            cacheExpiration: {
                tickets: 1000 * 60 * 30, // 30 minutes
                events: 1000 * 60 * 60 * 2, // 2 hours
                notifications: 1000 * 60 * 60 * 24, // 24 hours
                api_cache: 1000 * 60 * 15 // 15 minutes
            }
        };
        
        this.db = null;
        this.isInitialized = false;
        this.syncInProgress = false;
        
        this.init();
    }
    
    /**
     * Initialize the offline data manager
     */
    async init() {
        try {
            console.log('[Offline Data Manager] Initializing...');
            
            await this.openDatabase();
            await this.setupEventListeners();
            await this.startPeriodicSync();
            
            this.isInitialized = true;
            console.log('[Offline Data Manager] Initialization completed');
            
            // Trigger initial sync if online
            if (navigator.onLine) {
                this.triggerSync();
            }
            
        } catch (error) {
            console.error('[Offline Data Manager] Initialization failed:', error);
            throw error;
        }
    }
    
    /**
     * Open IndexedDB database
     */
    async openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.config.dbName, this.config.dbVersion);
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Database open error:', request.error);
                reject(new Error('Failed to open database'));
            };
            
            request.onsuccess = () => {
                this.db = request.result;
                console.log('[Offline Data Manager] Database opened successfully');
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                console.log('[Offline Data Manager] Database upgrade needed');
                this.db = event.target.result;
                this.createObjectStores();
            };
        });
    }
    
    /**
     * Create object stores for the database
     */
    createObjectStores() {
        try {
            // Tickets store
            if (!this.db.objectStoreNames.contains(this.config.stores.tickets)) {
                const ticketsStore = this.db.createObjectStore(this.config.stores.tickets, {
                    keyPath: 'id',
                    autoIncrement: false
                });
                ticketsStore.createIndex('event_id', 'event_id', { unique: false });
                ticketsStore.createIndex('price', 'price', { unique: false });
                ticketsStore.createIndex('updated_at', 'updated_at', { unique: false });
            }
            
            // Events store
            if (!this.db.objectStoreNames.contains(this.config.stores.events)) {
                const eventsStore = this.db.createObjectStore(this.config.stores.events, {
                    keyPath: 'id',
                    autoIncrement: false
                });
                eventsStore.createIndex('date', 'event_date', { unique: false });
                eventsStore.createIndex('venue', 'venue_name', { unique: false });
                eventsStore.createIndex('sport', 'sport_type', { unique: false });
            }
            
            // Notifications store
            if (!this.db.objectStoreNames.contains(this.config.stores.notifications)) {
                const notificationsStore = this.db.createObjectStore(this.config.stores.notifications, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                notificationsStore.createIndex('read', 'is_read', { unique: false });
                notificationsStore.createIndex('created_at', 'created_at', { unique: false });
                notificationsStore.createIndex('type', 'type', { unique: false });
            }
            
            // User preferences store
            if (!this.db.objectStoreNames.contains(this.config.stores.preferences)) {
                const preferencesStore = this.db.createObjectStore(this.config.stores.preferences, {
                    keyPath: 'key'
                });
                preferencesStore.createIndex('category', 'category', { unique: false });
                preferencesStore.createIndex('updated_at', 'updated_at', { unique: false });
            }
            
            // Pending alerts store (for offline alerts)
            if (!this.db.objectStoreNames.contains(this.config.stores.alerts)) {
                const alertsStore = this.db.createObjectStore(this.config.stores.alerts, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                alertsStore.createIndex('created_at', 'created_at', { unique: false });
                alertsStore.createIndex('sync_status', 'sync_status', { unique: false });
            }
            
            // Pending analytics store
            if (!this.db.objectStoreNames.contains(this.config.stores.analytics)) {
                const analyticsStore = this.db.createObjectStore(this.config.stores.analytics, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                analyticsStore.createIndex('event_type', 'event_type', { unique: false });
                analyticsStore.createIndex('created_at', 'created_at', { unique: false });
            }
            
            // API cache store
            if (!this.db.objectStoreNames.contains(this.config.stores.cache)) {
                const cacheStore = this.db.createObjectStore(this.config.stores.cache, {
                    keyPath: 'url'
                });
                cacheStore.createIndex('expires_at', 'expires_at', { unique: false });
                cacheStore.createIndex('created_at', 'created_at', { unique: false });
            }
            
            // Sync queue store
            if (!this.db.objectStoreNames.contains(this.config.stores.sync_queue)) {
                const syncStore = this.db.createObjectStore(this.config.stores.sync_queue, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                syncStore.createIndex('priority', 'priority', { unique: false });
                syncStore.createIndex('created_at', 'created_at', { unique: false });
                syncStore.createIndex('retry_count', 'retry_count', { unique: false });
            }
            
            console.log('[Offline Data Manager] Object stores created successfully');
            
        } catch (error) {
            console.error('[Offline Data Manager] Error creating object stores:', error);
            throw error;
        }
    }
    
    /**
     * Set up event listeners for network and visibility changes
     */
    async setupEventListeners() {
        // Network status changes
        window.addEventListener('online', () => {
            console.log('[Offline Data Manager] Network connection restored');
            this.triggerSync();
        });
        
        window.addEventListener('offline', () => {
            console.log('[Offline Data Manager] Network connection lost');
        });
        
        // Page visibility changes (for background sync)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && navigator.onLine) {
                this.triggerSync();
            }
        });
        
        // Storage events for cross-tab synchronization
        window.addEventListener('storage', (event) => {
            if (event.key === 'hd-tickets-sync-trigger') {
                this.triggerSync();
            }
        });
    }
    
    /**
     * Start periodic synchronization
     */
    async startPeriodicSync() {
        setInterval(() => {
            if (navigator.onLine && !this.syncInProgress) {
                this.triggerSync();
            }
        }, 60000); // Sync every minute when online
    }
    
    /**
     * Store data in IndexedDB
     */
    async storeData(storeName, data, options = {}) {
        if (!this.isInitialized) {
            throw new Error('Offline Data Manager not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            
            // Add metadata
            const dataWithMetadata = {
                ...data,
                stored_at: new Date().toISOString(),
                expires_at: options.expiresIn ? 
                    new Date(Date.now() + options.expiresIn).toISOString() : null,
                sync_status: options.syncStatus || 'pending'
            };
            
            const request = store.put(dataWithMetadata);
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Store data error:', request.error);
                reject(new Error('Failed to store data'));
            };
            
            request.onsuccess = () => {
                console.log(`[Offline Data Manager] Data stored in ${storeName}:`, data.id || data.key);
                resolve(request.result);
            };
            
            transaction.onerror = () => {
                console.error('[Offline Data Manager] Transaction error:', transaction.error);
                reject(new Error('Transaction failed'));
            };
        });
    }
    
    /**
     * Retrieve data from IndexedDB
     */
    async getData(storeName, key) {
        if (!this.isInitialized) {
            throw new Error('Offline Data Manager not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(key);
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Get data error:', request.error);
                reject(new Error('Failed to retrieve data'));
            };
            
            request.onsuccess = () => {
                const result = request.result;
                
                // Check expiration
                if (result && result.expires_at && new Date(result.expires_at) < new Date()) {
                    console.log(`[Offline Data Manager] Data expired for key: ${key}`);
                    this.deleteData(storeName, key);
                    resolve(null);
                } else {
                    resolve(result);
                }
            };
        });
    }
    
    /**
     * Get all data from a store
     */
    async getAllData(storeName, filter = null) {
        if (!this.isInitialized) {
            throw new Error('Offline Data Manager not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Get all data error:', request.error);
                reject(new Error('Failed to retrieve data'));
            };
            
            request.onsuccess = () => {
                let results = request.result;
                
                // Filter expired data
                results = results.filter(item => {
                    if (item.expires_at && new Date(item.expires_at) < new Date()) {
                        this.deleteData(storeName, item.id || item.key);
                        return false;
                    }
                    return true;
                });
                
                // Apply custom filter
                if (filter && typeof filter === 'function') {
                    results = results.filter(filter);
                }
                
                resolve(results);
            };
        });
    }
    
    /**
     * Delete data from IndexedDB
     */
    async deleteData(storeName, key) {
        if (!this.isInitialized) {
            throw new Error('Offline Data Manager not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(key);
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Delete data error:', request.error);
                reject(new Error('Failed to delete data'));
            };
            
            request.onsuccess = () => {
                console.log(`[Offline Data Manager] Data deleted from ${storeName}:`, key);
                resolve(true);
            };
        });
    }
    
    /**
     * Clear all data from a store
     */
    async clearStore(storeName) {
        if (!this.isInitialized) {
            throw new Error('Offline Data Manager not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.clear();
            
            request.onerror = () => {
                console.error('[Offline Data Manager] Clear store error:', request.error);
                reject(new Error('Failed to clear store'));
            };
            
            request.onsuccess = () => {
                console.log(`[Offline Data Manager] Store cleared: ${storeName}`);
                resolve(true);
            };
        });
    }
    
    /**
     * Cache API response
     */
    async cacheApiResponse(url, data, expirationTime = null) {
        const cacheData = {
            url: url,
            data: data,
            cached_at: new Date().toISOString(),
            expires_at: expirationTime || 
                new Date(Date.now() + this.config.cacheExpiration.api_cache).toISOString()
        };
        
        return this.storeData(this.config.stores.cache, cacheData);
    }
    
    /**
     * Get cached API response
     */
    async getCachedApiResponse(url) {
        const cached = await this.getData(this.config.stores.cache, url);
        
        if (cached && new Date(cached.expires_at) > new Date()) {
            console.log(`[Offline Data Manager] Serving cached API response: ${url}`);
            return cached.data;
        }
        
        return null;
    }
    
    /**
     * Add item to sync queue
     */
    async addToSyncQueue(operation, data, priority = 1) {
        const syncItem = {
            operation: operation,
            data: data,
            priority: priority,
            created_at: new Date().toISOString(),
            retry_count: 0,
            last_attempt: null,
            error_message: null
        };
        
        return this.storeData(this.config.stores.sync_queue, syncItem);
    }
    
    /**
     * Process sync queue
     */
    async processSyncQueue() {
        if (this.syncInProgress || !navigator.onLine) {
            return;
        }
        
        this.syncInProgress = true;
        console.log('[Offline Data Manager] Processing sync queue...');
        
        try {
            const syncItems = await this.getAllData(this.config.stores.sync_queue);
            
            // Sort by priority (higher priority first)
            syncItems.sort((a, b) => b.priority - a.priority);
            
            for (const item of syncItems) {
                try {
                    await this.processSyncItem(item);
                    await this.deleteData(this.config.stores.sync_queue, item.id);
                } catch (error) {
                    console.error('[Offline Data Manager] Sync item failed:', error);
                    await this.handleSyncFailure(item, error.message);
                }
            }
            
        } catch (error) {
            console.error('[Offline Data Manager] Sync queue processing failed:', error);
        } finally {
            this.syncInProgress = false;
            console.log('[Offline Data Manager] Sync queue processing completed');
        }
    }
    
    /**
     * Process individual sync item
     */
    async processSyncItem(item) {
        console.log(`[Offline Data Manager] Processing sync item: ${item.operation}`);
        
        switch (item.operation) {
            case 'create_alert':
                await this.syncCreateAlert(item.data);
                break;
                
            case 'update_preferences':
                await this.syncUpdatePreferences(item.data);
                break;
                
            case 'track_analytics':
                await this.syncAnalytics(item.data);
                break;
                
            case 'create_notification':
                await this.syncNotification(item.data);
                break;
                
            default:
                console.warn(`[Offline Data Manager] Unknown sync operation: ${item.operation}`);
        }
    }
    
    /**
     * Handle sync failure
     */
    async handleSyncFailure(item, errorMessage) {
        item.retry_count = (item.retry_count || 0) + 1;
        item.last_attempt = new Date().toISOString();
        item.error_message = errorMessage;
        
        if (item.retry_count < this.config.sync.maxRetries) {
            // Update the item with new retry information
            await this.storeData(this.config.stores.sync_queue, item);
            console.log(`[Offline Data Manager] Sync item will be retried: ${item.operation} (attempt ${item.retry_count})`);
        } else {
            // Max retries reached, move to failed items or delete
            console.error(`[Offline Data Manager] Sync item failed permanently: ${item.operation}`);
            await this.deleteData(this.config.stores.sync_queue, item.id);
        }
    }
    
    /**
     * Sync create alert operation
     */
    async syncCreateAlert(alertData) {
        const response = await fetch('/api/v1/alerts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(alertData)
        });
        
        if (!response.ok) {
            throw new Error(`Failed to create alert: ${response.statusText}`);
        }
        
        return response.json();
    }
    
    /**
     * Sync update preferences operation
     */
    async syncUpdatePreferences(preferencesData) {
        const response = await fetch('/api/v1/preferences', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(preferencesData)
        });
        
        if (!response.ok) {
            throw new Error(`Failed to update preferences: ${response.statusText}`);
        }
        
        return response.json();
    }
    
    /**
     * Sync analytics operation
     */
    async syncAnalytics(analyticsData) {
        const response = await fetch('/api/v1/analytics/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(analyticsData)
        });
        
        if (!response.ok) {
            throw new Error(`Failed to sync analytics: ${response.statusText}`);
        }
        
        return response.json();
    }
    
    /**
     * Sync notification operation
     */
    async syncNotification(notificationData) {
        const response = await fetch('/api/v1/notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(notificationData)
        });
        
        if (!response.ok) {
            throw new Error(`Failed to sync notification: ${response.statusText}`);
        }
        
        return response.json();
    }
    
    /**
     * Trigger synchronization
     */
    async triggerSync() {
        if (!navigator.onLine) {
            console.log('[Offline Data Manager] Cannot sync: offline');
            return;
        }
        
        try {
            // Process sync queue
            await this.processSyncQueue();
            
            // Clean up expired cache entries
            await this.cleanupExpiredCache();
            
            // Trigger cross-tab sync
            localStorage.setItem('hd-tickets-sync-trigger', Date.now().toString());
            
            console.log('[Offline Data Manager] Synchronization completed');
            
        } catch (error) {
            console.error('[Offline Data Manager] Synchronization failed:', error);
        }
    }
    
    /**
     * Clean up expired cache entries
     */
    async cleanupExpiredCache() {
        try {
            const cacheItems = await this.getAllData(this.config.stores.cache);
            const now = new Date();
            
            for (const item of cacheItems) {
                if (item.expires_at && new Date(item.expires_at) < now) {
                    await this.deleteData(this.config.stores.cache, item.url);
                }
            }
            
        } catch (error) {
            console.error('[Offline Data Manager] Cache cleanup failed:', error);
        }
    }
    
    /**
     * Get offline storage statistics
     */
    async getStorageStats() {
        const stats = {};
        
        try {
            for (const [key, storeName] of Object.entries(this.config.stores)) {
                const data = await this.getAllData(storeName);
                stats[key] = {
                    count: data.length,
                    store_name: storeName
                };
            }
            
            return stats;
        } catch (error) {
            console.error('[Offline Data Manager] Failed to get storage stats:', error);
            return {};
        }
    }
    
    /**
     * Export all offline data
     */
    async exportData() {
        const exportData = {
            exported_at: new Date().toISOString(),
            version: this.config.dbVersion,
            data: {}
        };
        
        try {
            for (const [key, storeName] of Object.entries(this.config.stores)) {
                exportData.data[key] = await this.getAllData(storeName);
            }
            
            return exportData;
        } catch (error) {
            console.error('[Offline Data Manager] Data export failed:', error);
            throw error;
        }
    }
    
    /**
     * Check if manager is ready
     */
    isReady() {
        return this.isInitialized && this.db !== null;
    }
    
    /**
     * Get current sync status
     */
    getSyncStatus() {
        return {
            isOnline: navigator.onLine,
            syncInProgress: this.syncInProgress,
            isInitialized: this.isInitialized,
            lastSync: localStorage.getItem('hd-tickets-last-sync') || 'Never'
        };
    }
}

// Initialize offline data manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.hdOfflineDataManager = new HDTicketsOfflineDataManager();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HDTicketsOfflineDataManager;
}
