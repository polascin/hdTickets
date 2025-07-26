/**
 * User Preferences Management Service
 * Handles storing and retrieving user preferences for UI settings
 */
class UserPreferences {
    constructor(options = {}) {
        this.options = {
            storageKey: 'hdtickets_user_prefs',
            useServer: false, // Set to true to sync with server
            syncInterval: 30000, // 30 seconds
            ...options
        };

        this.preferences = {};
        this.defaultPreferences = {
            theme: 'light',
            language: 'en',
            dashboard: {
                layout: 'grid',
                cardsPerRow: 4,
                showWelcomeBanner: true,
                autoRefresh: true,
                refreshInterval: 30000
            },
            tables: {
                itemsPerPage: 25,
                compactMode: false,
                showFilters: true,
                stickyHeaders: true
            },
            notifications: {
                enabled: true,
                position: 'top-right',
                duration: 5000,
                showToasts: true,
                playSound: false,
                enableVibration: false
            },
            accessibility: {
                highContrast: false,
                reduceMotion: false,
                largeText: false,
                screenReader: false
            },
            ui: {
                sidebarCollapsed: false,
                showTooltips: true,
                animationsEnabled: true,
                compactMode: false
            }
        };

        this.observers = [];
        this.syncTimer = null;
        
        this.init();
    }

    init() {
        this.loadPreferences();
        if (this.options.useServer) {
            this.setupServerSync();
        }
        this.setupAutoSave();
    }

    loadPreferences() {
        try {
            const stored = localStorage.getItem(this.options.storageKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                this.preferences = this.mergeDefaults(parsed);
            } else {
                this.preferences = { ...this.defaultPreferences };
            }
        } catch (e) {
            console.warn('Failed to load user preferences:', e);
            this.preferences = { ...this.defaultPreferences };
        }
    }

    mergeDefaults(userPrefs) {
        const merged = { ...this.defaultPreferences };
        
        // Deep merge user preferences with defaults
        Object.keys(userPrefs).forEach(key => {
            if (typeof userPrefs[key] === 'object' && !Array.isArray(userPrefs[key])) {
                merged[key] = { ...merged[key], ...userPrefs[key] };
            } else {
                merged[key] = userPrefs[key];
            }
        });
        
        return merged;
    }

    savePreferences() {
        try {
            const toSave = {
                ...this.preferences,
                lastUpdated: Date.now(),
                version: '1.0'
            };
            
            localStorage.setItem(this.options.storageKey, JSON.stringify(toSave));
            
            if (this.options.useServer) {
                this.syncToServer();
            }
            
            this.notifyObservers('preferences:saved', this.preferences);
        } catch (e) {
            console.error('Failed to save user preferences:', e);
        }
    }

    setupAutoSave() {
        // Auto-save preferences when the page unloads
        window.addEventListener('beforeunload', () => {
            this.savePreferences();
        });

        // Periodic auto-save
        setInterval(() => {
            this.savePreferences();
        }, 60000); // Save every minute
    }

    setupServerSync() {
        if (this.syncTimer) {
            clearInterval(this.syncTimer);
        }

        this.syncTimer = setInterval(() => {
            this.syncFromServer();
        }, this.options.syncInterval);
    }

    async syncToServer() {
        try {
            const response = await fetch('/api/user/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    preferences: this.preferences
                })
            });

            if (!response.ok) {
                throw new Error('Failed to sync preferences to server');
            }
        } catch (e) {
            console.warn('Server sync failed:', e);
        }
    }

    async syncFromServer() {
        try {
            const response = await fetch('/api/user/preferences');
            if (response.ok) {
                const data = await response.json();
                if (data.preferences) {
                    const serverPrefs = data.preferences;
                    const localLastUpdated = this.preferences.lastUpdated || 0;
                    const serverLastUpdated = serverPrefs.lastUpdated || 0;

                    if (serverLastUpdated > localLastUpdated) {
                        this.preferences = this.mergeDefaults(serverPrefs);
                        this.savePreferences();
                        this.notifyObservers('preferences:synced', this.preferences);
                    }
                }
            }
        } catch (e) {
            console.warn('Server sync failed:', e);
        }
    }

    // Preference getters and setters
    get(key, defaultValue = null) {
        const keys = key.split('.');
        let value = this.preferences;
        
        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return defaultValue;
            }
        }
        
        return value;
    }

    set(key, value) {
        const keys = key.split('.');
        let target = this.preferences;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const k = keys[i];
            if (!(k in target) || typeof target[k] !== 'object') {
                target[k] = {};
            }
            target = target[k];
        }
        
        const finalKey = keys[keys.length - 1];
        const oldValue = target[finalKey];
        target[finalKey] = value;
        
        this.savePreferences();
        this.notifyObservers('preference:changed', { key, value, oldValue });
        
        return this;
    }

    // Bulk operations
    setBulk(preferences) {
        Object.entries(preferences).forEach(([key, value]) => {
            this.set(key, value);
        });
        
        return this;
    }

    reset(key = null) {
        if (key) {
            this.set(key, this.get(key, null, this.defaultPreferences));
        } else {
            this.preferences = { ...this.defaultPreferences };
            this.savePreferences();
        }
        
        this.notifyObservers('preferences:reset', { key });
        return this;
    }

    // Theme-specific methods
    getTheme() {
        return this.get('theme', 'light');
    }

    setTheme(theme) {
        return this.set('theme', theme);
    }

    // Dashboard-specific methods
    getDashboardLayout() {
        return this.get('dashboard.layout', 'grid');
    }

    setDashboardLayout(layout) {
        return this.set('dashboard.layout', layout);
    }

    getDashboardCardsPerRow() {
        return this.get('dashboard.cardsPerRow', 4);
    }

    setDashboardCardsPerRow(count) {
        return this.set('dashboard.cardsPerRow', Math.max(1, Math.min(6, count)));
    }

    // Table-specific methods
    getTableItemsPerPage() {
        return this.get('tables.itemsPerPage', 25);
    }

    setTableItemsPerPage(count) {
        return this.set('tables.itemsPerPage', count);
    }

    isTableCompactMode() {
        return this.get('tables.compactMode', false);
    }

    setTableCompactMode(enabled) {
        return this.set('tables.compactMode', enabled);
    }

    // Notification-specific methods
    areNotificationsEnabled() {
        return this.get('notifications.enabled', true);
    }

    setNotificationsEnabled(enabled) {
        return this.set('notifications.enabled', enabled);
    }

    getNotificationPosition() {
        return this.get('notifications.position', 'top-right');
    }

    setNotificationPosition(position) {
        const validPositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        if (validPositions.includes(position)) {
            return this.set('notifications.position', position);
        }
        return this;
    }

    // Accessibility methods
    isHighContrastEnabled() {
        return this.get('accessibility.highContrast', false);
    }

    setHighContrast(enabled) {
        this.set('accessibility.highContrast', enabled);
        
        // Apply high contrast immediately
        if (enabled) {
            document.body.classList.add('high-contrast');
        } else {
            document.body.classList.remove('high-contrast');
        }
        
        return this;
    }

    isReduceMotionEnabled() {
        return this.get('accessibility.reduceMotion', false);
    }

    setReduceMotion(enabled) {
        this.set('accessibility.reduceMotion', enabled);
        
        // Apply reduced motion immediately
        if (enabled) {
            document.body.classList.add('reduce-motion');
        } else {
            document.body.classList.remove('reduce-motion');
        }
        
        return this;
    }

    // UI-specific methods
    isSidebarCollapsed() {
        return this.get('ui.sidebarCollapsed', false);
    }

    setSidebarCollapsed(collapsed) {
        return this.set('ui.sidebarCollapsed', collapsed);
    }

    areAnimationsEnabled() {
        return this.get('ui.animationsEnabled', true);
    }

    setAnimationsEnabled(enabled) {
        this.set('ui.animationsEnabled', enabled);
        
        // Apply animation settings immediately
        if (!enabled) {
            document.body.classList.add('no-animations');
        } else {
            document.body.classList.remove('no-animations');
        }
        
        return this;
    }

    // Export/Import
    export() {
        return {
            preferences: this.preferences,
            exported: new Date().toISOString(),
            version: '1.0'
        };
    }

    import(data) {
        try {
            if (data.preferences) {
                this.preferences = this.mergeDefaults(data.preferences);
                this.savePreferences();
                this.notifyObservers('preferences:imported', data);
                return true;
            }
        } catch (e) {
            console.error('Failed to import preferences:', e);
        }
        return false;
    }

    // Observer pattern
    addObserver(callback) {
        this.observers.push(callback);
        return () => this.removeObserver(callback);
    }

    removeObserver(callback) {
        this.observers = this.observers.filter(obs => obs !== callback);
    }

    notifyObservers(event, data) {
        this.observers.forEach(callback => {
            try {
                callback(event, data);
            } catch (e) {
                console.error('Preference observer error:', e);
            }
        });
    }

    // Utility methods
    getAllPreferences() {
        return { ...this.preferences };
    }

    getPreferenceKeys() {
        return this.flattenKeys(this.preferences);
    }

    flattenKeys(obj, prefix = '') {
        let keys = [];
        
        Object.keys(obj).forEach(key => {
            const newKey = prefix ? `${prefix}.${key}` : key;
            
            if (typeof obj[key] === 'object' && !Array.isArray(obj[key])) {
                keys = keys.concat(this.flattenKeys(obj[key], newKey));
            } else {
                keys.push(newKey);
            }
        });
        
        return keys;
    }

    // Validation
    isValidPreference(key, value) {
        // Add validation logic here
        const validations = {
            'theme': ['light', 'dark', 'auto'],
            'dashboard.layout': ['grid', 'list', 'compact'],
            'dashboard.cardsPerRow': (val) => Number.isInteger(val) && val >= 1 && val <= 6,
            'tables.itemsPerPage': (val) => [10, 25, 50, 100].includes(val),
            'notifications.position': ['top-left', 'top-right', 'bottom-left', 'bottom-right']
        };

        if (key in validations) {
            const validation = validations[key];
            if (Array.isArray(validation)) {
                return validation.includes(value);
            } else if (typeof validation === 'function') {
                return validation(value);
            }
        }

        return true; // Default to valid if no validation rule
    }

    destroy() {
        if (this.syncTimer) {
            clearInterval(this.syncTimer);
        }
        this.observers = [];
    }
}

// Auto-initialize if not in module environment
if (typeof module === 'undefined') {
    window.UserPreferences = UserPreferences;
    
    // Auto-initialize preferences manager
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.hdTicketsPrefs) {
            window.hdTicketsPrefs = new UserPreferences();
        }
    });
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserPreferences;
}
