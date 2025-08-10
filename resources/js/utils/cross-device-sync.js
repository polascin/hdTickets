/**
 * Cross-Device Synchronization System
 * Manages profile data synchronization across multiple devices for HD Tickets
 */

class CrossDeviceSync {
    constructor(options = {}) {
        this.options = {
            apiEndpoint: '/api/sync',
            websocketUrl: null, // Optional WebSocket for real-time sync
            pollInterval: 60000, // Poll every minute for changes
            maxRetries: 3,
            retryDelay: 2000,
            storagePrefix: 'hdtickets_sync_',
            debugMode: false,
            enableRealTimeSync: true,
            enableConflictResolution: true,
            syncableData: [
                'preferences',
                'favorites',
                'alerts',
                'dashboard_layout',
                'theme_settings',
                'notification_settings'
            ],
            ...options
        };

        this.syncState = {
            lastSyncTime: null,
            deviceId: this.generateDeviceId(),
            isOnline: navigator.onLine,
            isSyncing: false,
            pendingChanges: new Map(),
            conflicts: new Map()
        };

        this.websocket = null;
        this.pollTimer = null;
        this.retryTimeouts = new Map();
        
        this.init();
    }

    init() {
        this.loadSyncState();
        this.setupOnlineStatusTracking();
        this.setupStorageListener();
        this.setupWebSocketConnection();
        this.setupPeriodicSync();
        this.setupBeforeUnloadSync();

        if (this.options.debugMode) {
            console.log('CrossDeviceSync initialized', this.options);
            this.setupDebugConsole();
        }
    }

    generateDeviceId() {
        let deviceId = localStorage.getItem(this.options.storagePrefix + 'device_id');
        if (!deviceId) {
            deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem(this.options.storagePrefix + 'device_id', deviceId);
        }
        return deviceId;
    }

    loadSyncState() {
        const savedState = localStorage.getItem(this.options.storagePrefix + 'state');
        if (savedState) {
            try {
                const parsed = JSON.parse(savedState);
                this.syncState = { ...this.syncState, ...parsed };
            } catch (error) {
                console.error('Failed to load sync state:', error);
            }
        }
    }

    saveSyncState() {
        try {
            localStorage.setItem(
                this.options.storagePrefix + 'state',
                JSON.stringify({
                    lastSyncTime: this.syncState.lastSyncTime,
                    deviceId: this.syncState.deviceId,
                    pendingChanges: Array.from(this.syncState.pendingChanges.entries()),
                    conflicts: Array.from(this.syncState.conflicts.entries())
                })
            );
        } catch (error) {
            console.error('Failed to save sync state:', error);
        }
    }

    // Online Status Tracking
    setupOnlineStatusTracking() {
        window.addEventListener('online', () => {
            this.syncState.isOnline = true;
            this.onConnectionRestored();
        });

        window.addEventListener('offline', () => {
            this.syncState.isOnline = false;
            this.onConnectionLost();
        });
    }

    onConnectionRestored() {
        if (this.options.debugMode) {
            console.log('Connection restored, syncing pending changes');
        }

        // Sync pending changes
        if (this.syncState.pendingChanges.size > 0) {
            this.syncPendingChanges();
        }

        // Re-establish WebSocket connection
        if (this.options.enableRealTimeSync && !this.websocket) {
            this.setupWebSocketConnection();
        }

        // Perform full sync
        this.performFullSync();
    }

    onConnectionLost() {
        if (this.options.debugMode) {
            console.log('Connection lost, switching to offline mode');
        }

        // Close WebSocket connection
        if (this.websocket) {
            this.websocket.close();
            this.websocket = null;
        }
    }

    // Storage Change Listener
    setupStorageListener() {
        window.addEventListener('storage', (e) => {
            if (e.key && e.key.startsWith(this.options.storagePrefix)) {
                this.handleStorageChange(e);
            }
        });
    }

    handleStorageChange(event) {
        if (event.key.endsWith('_sync_data')) {
            const dataType = event.key
                .replace(this.options.storagePrefix, '')
                .replace('_sync_data', '');
            
            if (this.options.syncableData.includes(dataType)) {
                this.handleLocalDataChange(dataType, event.newValue);
            }
        }
    }

    // WebSocket Connection
    setupWebSocketConnection() {
        if (!this.options.enableRealTimeSync || !this.options.websocketUrl || !this.syncState.isOnline) {
            return;
        }

        try {
            this.websocket = new WebSocket(this.options.websocketUrl);

            this.websocket.onopen = () => {
                if (this.options.debugMode) {
                    console.log('WebSocket connected for real-time sync');
                }

                // Authenticate WebSocket connection
                this.websocket.send(JSON.stringify({
                    type: 'authenticate',
                    deviceId: this.syncState.deviceId,
                    userId: this.getCurrentUserId()
                }));
            };

            this.websocket.onmessage = (event) => {
                this.handleWebSocketMessage(event.data);
            };

            this.websocket.onclose = () => {
                if (this.options.debugMode) {
                    console.log('WebSocket disconnected');
                }
                this.websocket = null;

                // Attempt to reconnect after delay
                if (this.syncState.isOnline) {
                    setTimeout(() => {
                        this.setupWebSocketConnection();
                    }, 5000);
                }
            };

            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
            };

        } catch (error) {
            console.error('Failed to establish WebSocket connection:', error);
        }
    }

    handleWebSocketMessage(data) {
        try {
            const message = JSON.parse(data);

            switch (message.type) {
                case 'data_updated':
                    this.handleRemoteDataUpdate(message.dataType, message.data, message.deviceId);
                    break;
                case 'conflict_detected':
                    this.handleSyncConflict(message.dataType, message.localData, message.remoteData);
                    break;
                case 'sync_request':
                    this.handleSyncRequest(message.dataTypes);
                    break;
                default:
                    if (this.options.debugMode) {
                        console.log('Unknown WebSocket message:', message);
                    }
            }
        } catch (error) {
            console.error('Failed to parse WebSocket message:', error);
        }
    }

    // Periodic Synchronization
    setupPeriodicSync() {
        if (this.options.pollInterval <= 0) return;

        this.pollTimer = setInterval(() => {
            if (this.syncState.isOnline && !this.syncState.isSyncing) {
                this.performIncrementalSync();
            }
        }, this.options.pollInterval);
    }

    // Data Synchronization Methods
    async syncData(dataType, localData, options = {}) {
        if (this.syncState.isSyncing && !options.force) {
            this.queuePendingChange(dataType, localData);
            return;
        }

        const syncKey = `${dataType}_${Date.now()}`;
        
        try {
            this.syncState.isSyncing = true;

            if (!this.syncState.isOnline) {
                this.queuePendingChange(dataType, localData);
                return;
            }

            const response = await this.makeApiRequest('/sync/data', {
                method: 'POST',
                body: JSON.stringify({
                    dataType,
                    data: localData,
                    deviceId: this.syncState.deviceId,
                    timestamp: Date.now(),
                    version: this.getDataVersion(dataType)
                })
            });

            if (response.success) {
                this.updateDataVersion(dataType, response.version);
                this.syncState.lastSyncTime = Date.now();
                
                if (response.hasConflicts) {
                    this.handleSyncConflict(dataType, localData, response.remoteData);
                }

                this.triggerSyncEvent('data_synced', { dataType, success: true });
            } else {
                throw new Error(response.error || 'Sync failed');
            }

        } catch (error) {
            console.error(`Failed to sync ${dataType}:`, error);
            this.queuePendingChange(dataType, localData);
            this.handleSyncError(error, syncKey);
        } finally {
            this.syncState.isSyncing = false;
            this.saveSyncState();
        }
    }

    async performFullSync() {
        if (this.syncState.isSyncing) return;

        try {
            this.syncState.isSyncing = true;
            this.triggerSyncEvent('full_sync_started');

            const response = await this.makeApiRequest('/sync/full', {
                method: 'GET',
                params: {
                    deviceId: this.syncState.deviceId,
                    lastSyncTime: this.syncState.lastSyncTime
                }
            });

            if (response.success) {
                for (const [dataType, data] of Object.entries(response.data)) {
                    if (this.options.syncableData.includes(dataType)) {
                        await this.updateLocalData(dataType, data);
                    }
                }

                this.syncState.lastSyncTime = Date.now();
                this.triggerSyncEvent('full_sync_completed');
            }

        } catch (error) {
            console.error('Full sync failed:', error);
            this.triggerSyncEvent('full_sync_failed', { error });
        } finally {
            this.syncState.isSyncing = false;
            this.saveSyncState();
        }
    }

    async performIncrementalSync() {
        if (!this.syncState.lastSyncTime) {
            return this.performFullSync();
        }

        try {
            const response = await this.makeApiRequest('/sync/incremental', {
                method: 'GET',
                params: {
                    deviceId: this.syncState.deviceId,
                    since: this.syncState.lastSyncTime
                }
            });

            if (response.success && response.changes.length > 0) {
                for (const change of response.changes) {
                    if (this.options.syncableData.includes(change.dataType)) {
                        await this.applyRemoteChange(change);
                    }
                }

                this.syncState.lastSyncTime = Date.now();
            }

        } catch (error) {
            console.error('Incremental sync failed:', error);
        }
    }

    // Conflict Resolution
    handleSyncConflict(dataType, localData, remoteData) {
        if (!this.options.enableConflictResolution) {
            // Always prefer remote data if conflict resolution is disabled
            this.updateLocalData(dataType, remoteData);
            return;
        }

        const conflictId = `${dataType}_${Date.now()}`;
        this.syncState.conflicts.set(conflictId, {
            dataType,
            localData,
            remoteData,
            timestamp: Date.now()
        });

        this.triggerSyncEvent('conflict_detected', {
            conflictId,
            dataType,
            localData,
            remoteData
        });

        // Attempt automatic resolution
        const resolution = this.attemptAutomaticResolution(localData, remoteData);
        if (resolution) {
            this.resolveConflict(conflictId, resolution);
        }
    }

    attemptAutomaticResolution(localData, remoteData) {
        // Merge strategy: combine non-conflicting changes
        try {
            if (typeof localData === 'object' && typeof remoteData === 'object') {
                const merged = { ...remoteData, ...localData };
                
                // Special handling for arrays
                if (Array.isArray(localData) && Array.isArray(remoteData)) {
                    // Combine and deduplicate arrays
                    const combined = [...remoteData, ...localData];
                    return combined.filter((item, index, arr) => 
                        arr.findIndex(i => JSON.stringify(i) === JSON.stringify(item)) === index
                    );
                }
                
                return merged;
            }
        } catch (error) {
            console.error('Automatic conflict resolution failed:', error);
        }

        return null;
    }

    resolveConflict(conflictId, resolvedData) {
        const conflict = this.syncState.conflicts.get(conflictId);
        if (!conflict) return;

        this.updateLocalData(conflict.dataType, resolvedData);
        this.syncData(conflict.dataType, resolvedData, { force: true });
        
        this.syncState.conflicts.delete(conflictId);
        this.triggerSyncEvent('conflict_resolved', { conflictId, resolvedData });
    }

    // Data Management
    async updateLocalData(dataType, data) {
        try {
            // Store in localStorage for persistence
            localStorage.setItem(
                this.options.storagePrefix + dataType + '_sync_data',
                JSON.stringify(data)
            );

            // Update version
            this.updateDataVersion(dataType, Date.now());

            // Trigger local update event
            this.triggerSyncEvent('local_data_updated', { dataType, data });

            // Update UI if needed
            this.updateUI(dataType, data);

        } catch (error) {
            console.error(`Failed to update local data for ${dataType}:`, error);
        }
    }

    getLocalData(dataType) {
        try {
            const data = localStorage.getItem(
                this.options.storagePrefix + dataType + '_sync_data'
            );
            return data ? JSON.parse(data) : null;
        } catch (error) {
            console.error(`Failed to get local data for ${dataType}:`, error);
            return null;
        }
    }

    getDataVersion(dataType) {
        return parseInt(localStorage.getItem(
            this.options.storagePrefix + dataType + '_version'
        ) || '0');
    }

    updateDataVersion(dataType, version) {
        localStorage.setItem(
            this.options.storagePrefix + dataType + '_version',
            version.toString()
        );
    }

    // UI Updates
    updateUI(dataType, data) {
        // Dispatch custom events for UI components to listen to
        const event = new CustomEvent('sync-data-updated', {
            detail: { dataType, data }
        });
        document.dispatchEvent(event);

        // Specific UI updates based on data type
        switch (dataType) {
            case 'preferences':
                this.updatePreferencesUI(data);
                break;
            case 'theme_settings':
                this.updateThemeUI(data);
                break;
            case 'dashboard_layout':
                this.updateDashboardUI(data);
                break;
        }
    }

    updatePreferencesUI(preferences) {
        // Update preference forms and settings
        const forms = document.querySelectorAll('.preferences-form');
        forms.forEach(form => {
            this.populateFormData(form, preferences);
        });
    }

    updateThemeUI(themeSettings) {
        // Apply theme changes
        if (themeSettings.darkMode !== undefined) {
            document.body.classList.toggle('dark-mode', themeSettings.darkMode);
        }
        if (themeSettings.primaryColor) {
            document.documentElement.style.setProperty('--primary-color', themeSettings.primaryColor);
        }
    }

    updateDashboardUI(layout) {
        // Update dashboard layout
        const dashboard = document.querySelector('.dashboard-container');
        if (dashboard && layout) {
            this.applyDashboardLayout(dashboard, layout);
        }
    }

    // Utility Methods
    queuePendingChange(dataType, data) {
        this.syncState.pendingChanges.set(dataType, {
            data,
            timestamp: Date.now()
        });
        this.saveSyncState();
    }

    async syncPendingChanges() {
        const pendingEntries = Array.from(this.syncState.pendingChanges.entries());
        
        for (const [dataType, change] of pendingEntries) {
            try {
                await this.syncData(dataType, change.data, { force: true });
                this.syncState.pendingChanges.delete(dataType);
            } catch (error) {
                console.error(`Failed to sync pending change for ${dataType}:`, error);
            }
        }
    }

    async makeApiRequest(endpoint, options = {}) {
        const url = this.options.apiEndpoint + endpoint;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                ...options.headers
            },
            ...options
        };

        if (options.params) {
            const params = new URLSearchParams(options.params);
            return fetch(`${url}?${params}`, config).then(r => r.json());
        }

        return fetch(url, config).then(r => r.json());
    }

    handleSyncError(error, syncKey) {
        const retryCount = this.getRetryCount(syncKey);
        
        if (retryCount < this.options.maxRetries) {
            const delay = this.options.retryDelay * Math.pow(2, retryCount);
            
            this.retryTimeouts.set(syncKey, setTimeout(() => {
                this.incrementRetryCount(syncKey);
                // Retry logic would go here
            }, delay));
        } else {
            this.triggerSyncEvent('sync_failed', { error, syncKey });
        }
    }

    getRetryCount(syncKey) {
        return parseInt(sessionStorage.getItem(`retry_${syncKey}`) || '0');
    }

    incrementRetryCount(syncKey) {
        const current = this.getRetryCount(syncKey);
        sessionStorage.setItem(`retry_${syncKey}`, (current + 1).toString());
    }

    getCurrentUserId() {
        // Get from meta tag or global variable
        return document.querySelector('meta[name="user-id"]')?.content || 
               window.Laravel?.user?.id;
    }

    triggerSyncEvent(eventType, data = {}) {
        const event = new CustomEvent(`cross-device-sync-${eventType}`, {
            detail: data
        });
        document.dispatchEvent(event);

        if (this.options.debugMode) {
            console.log(`Sync event: ${eventType}`, data);
        }
    }

    // Before Unload Sync
    setupBeforeUnloadSync() {
        window.addEventListener('beforeunload', () => {
            if (this.syncState.pendingChanges.size > 0) {
                // Use sendBeacon for reliable sync on page unload
                const data = Array.from(this.syncState.pendingChanges.entries()).map(([key, value]) => ({
                    dataType: key,
                    ...value
                }));

                navigator.sendBeacon(
                    this.options.apiEndpoint + '/sync/batch',
                    JSON.stringify({
                        deviceId: this.syncState.deviceId,
                        changes: data
                    })
                );
            }
        });
    }

    // Public API Methods
    async syncNow(dataTypes = null) {
        const typesToSync = dataTypes || this.options.syncableData;
        
        for (const dataType of typesToSync) {
            const localData = this.getLocalData(dataType);
            if (localData) {
                await this.syncData(dataType, localData);
            }
        }
    }

    getConflicts() {
        return Array.from(this.syncState.conflicts.entries()).map(([id, conflict]) => ({
            id,
            ...conflict
        }));
    }

    getPendingChanges() {
        return Array.from(this.syncState.pendingChanges.entries()).map(([dataType, change]) => ({
            dataType,
            ...change
        }));
    }

    // Debug Console
    setupDebugConsole() {
        window.crossDeviceSyncDebug = {
            getState: () => this.syncState,
            getConflicts: () => this.getConflicts(),
            getPendingChanges: () => this.getPendingChanges(),
            syncNow: (dataTypes) => this.syncNow(dataTypes),
            performFullSync: () => this.performFullSync(),
            clearPendingChanges: () => {
                this.syncState.pendingChanges.clear();
                this.saveSyncState();
            },
            simulateConflict: (dataType) => {
                const localData = { test: 'local' };
                const remoteData = { test: 'remote' };
                this.handleSyncConflict(dataType, localData, remoteData);
            }
        };

        console.log('Cross-Device Sync Debug Console available at window.crossDeviceSyncDebug');
    }

    // Cleanup
    destroy() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }

        if (this.websocket) {
            this.websocket.close();
        }

        this.retryTimeouts.forEach(timeout => clearTimeout(timeout));
        this.retryTimeouts.clear();

        // Save final state
        this.saveSyncState();
    }
}

// Export for use in modules
export default CrossDeviceSync;

// Global instance
window.CrossDeviceSync = CrossDeviceSync;

// Auto-initialize with default options
document.addEventListener('DOMContentLoaded', () => {
    if (!window.crossDeviceSync) {
        window.crossDeviceSync = new CrossDeviceSync({
            debugMode: document.body.hasAttribute('data-debug-sync')
        });
    }
});

// Helper functions for common sync operations
window.syncProfileData = (dataType, data) => {
    if (window.crossDeviceSync) {
        return window.crossDeviceSync.syncData(dataType, data);
    }
};

window.getSyncedData = (dataType) => {
    if (window.crossDeviceSync) {
        return window.crossDeviceSync.getLocalData(dataType);
    }
    return null;
};
