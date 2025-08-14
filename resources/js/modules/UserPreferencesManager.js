/**
 * Enhanced User Preferences Manager
 * Handles all user preference interactions with AJAX support and instant feedback
 */

class UserPreferencesManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/preferences',
            debounceDelay: 500,
            autoSave: true,
            showNotifications: true,
            ...options
        };

        this.preferences = {};
        this.changeQueue = new Map();
        this.debounceTimers = new Map();
        this.isOnline = navigator.onLine;
        this.pendingChanges = new Set();

        this.init();
    }

    /**
     * Initialize the preferences manager
     */
    init() {
        this.setupEventListeners();
        this.setupOnlineStatusTracking();
        this.loadPreferences();
        
        // Auto-save on page unload if there are pending changes
        window.addEventListener('beforeunload', (e) => {
            if (this.pendingChanges.size > 0) {
                this.saveAllPendingChanges();
                e.returnValue = 'You have unsaved preferences. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    }

    /**
     * Setup event listeners for preferences form
     */
    setupEventListeners() {
        document.addEventListener('change', (e) => {
            if (e.target.hasAttribute('data-preference')) {
                this.handlePreferenceChange(e.target);
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.hasAttribute('data-preference-input')) {
                this.handlePreferenceInput(e.target);
            }
        });

        // Theme preview functionality
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-theme-preview]')) {
                this.previewTheme(e.target.dataset.themePreview);
            }
        });

        // Export/Import functionality
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-export-preferences]')) {
                this.exportPreferences();
            }
            if (e.target.matches('[data-import-preferences]')) {
                this.importPreferences();
            }
            if (e.target.matches('[data-reset-preferences]')) {
                this.resetPreferences(e.target.dataset.resetCategory);
            }
        });
    }

    /**
     * Setup online/offline status tracking
     */
    setupOnlineStatusTracking() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.syncPendingChanges();
            this.showNotification('Connection restored. Syncing preferences...', 'success');
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showNotification('Working offline. Changes will sync when connection is restored.', 'warning');
        });
    }

    /**
     * Load initial preferences from server
     */
    async loadPreferences() {
        try {
            const response = await this.makeRequest('GET', `${this.options.baseUrl}/export`);
            if (response.success) {
                this.preferences = response.data;
                this.applyPreferencesToUI();
            }
        } catch (error) {
            console.error('Failed to load preferences:', error);
            this.showNotification('Failed to load preferences', 'error');
        }
    }

    /**
     * Handle preference change events
     */
    handlePreferenceChange(element) {
        const key = element.getAttribute('data-preference');
        const value = this.getElementValue(element);
        
        this.updatePreference(key, value, element);
    }

    /**
     * Handle preference input events (with debouncing)
     */
    handlePreferenceInput(element) {
        const key = element.getAttribute('data-preference-input');
        
        // Clear existing timer
        if (this.debounceTimers.has(key)) {
            clearTimeout(this.debounceTimers.get(key));
        }

        // Set new timer
        const timer = setTimeout(() => {
            const value = this.getElementValue(element);
            this.updatePreference(key, value, element);
        }, this.options.debounceDelay);

        this.debounceTimers.set(key, timer);
    }

    /**
     * Update a single preference
     */
    async updatePreference(key, value, element = null) {
        // Update local preferences immediately for UI responsiveness
        this.preferences[key] = value;
        
        if (element) {
            this.showElementFeedback(element, 'saving');
        }

        try {
            if (this.isOnline) {
                const response = await this.makeRequest('POST', `${this.options.baseUrl}/update-single`, {
                    key,
                    value,
                    type: typeof value
                });

                if (response.success) {
                    this.showElementFeedback(element, 'success');
                    this.pendingChanges.delete(key);
                    
                    // Apply any side effects
                    this.applyPreferenceSideEffects(key, value);
                } else {
                    throw new Error(response.message);
                }
            } else {
                // Store for later sync
                this.pendingChanges.add(key);
                this.showElementFeedback(element, 'offline');
            }
        } catch (error) {
            console.error(`Failed to update preference ${key}:`, error);
            this.showElementFeedback(element, 'error');
            this.showNotification(`Failed to save ${key}: ${error.message}`, 'error');
        }
    }

    /**
     * Update multiple preferences at once
     */
    async updateMultiplePreferences(preferences) {
        Object.assign(this.preferences, preferences);

        try {
            const response = await this.makeRequest('POST', `${this.options.baseUrl}/update`, {
                preferences: this.categorizePreferences(preferences)
            });

            if (response.success) {
                this.showNotification('All preferences saved successfully', 'success');
                // Remove from pending changes
                Object.keys(preferences).forEach(key => {
                    this.pendingChanges.delete(key);
                });
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Failed to update multiple preferences:', error);
            this.showNotification(`Failed to save preferences: ${error.message}`, 'error');
        }
    }

    /**
     * Auto-detect and update timezone
     */
    async detectTimezone() {
        try {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const response = await this.makeRequest('POST', `${this.options.baseUrl}/detect-timezone`, {
                timezone
            });

            if (response.success) {
                this.preferences.user_timezone = response.timezone;
                this.updateTimezoneUI(response.timezone, response.display_name);
                this.showNotification('Timezone detected and updated successfully', 'success');
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Failed to detect timezone:', error);
            this.showNotification('Failed to detect timezone', 'error');
        }
    }

    /**
     * Preview theme changes without saving
     */
    previewTheme(theme) {
        const root = document.documentElement;
        
        // Remove existing theme classes
        root.classList.remove('theme-light', 'theme-dark', 'theme-auto');
        
        // Add new theme class
        root.classList.add(`theme-${theme}`);
        
        // Update theme based on system preference if auto
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            root.classList.toggle('theme-dark-active', prefersDark);
            root.classList.toggle('theme-light-active', !prefersDark);
        } else {
            root.classList.add(`theme-${theme}-active`);
        }

        // Show preview notification
        this.showNotification(`Previewing ${theme} theme. Change will be saved automatically.`, 'info', 3000);
    }

    /**
     * Export preferences to file
     */
    async exportPreferences() {
        try {
            const response = await this.makeRequest('GET', `${this.options.baseUrl}/export`);
            
            if (response.success) {
                const dataStr = JSON.stringify(response.data, null, 2);
                const dataBlob = new Blob([dataStr], { type: 'application/json' });
                const url = URL.createObjectURL(dataBlob);
                
                const link = document.createElement('a');
                link.href = url;
                link.download = `hd-tickets-preferences-${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                URL.revokeObjectURL(url);
                this.showNotification('Preferences exported successfully', 'success');
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Failed to export preferences:', error);
            this.showNotification('Failed to export preferences', 'error');
        }
    }

    /**
     * Import preferences from file
     */
    async importPreferences() {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.json';
        
        fileInput.onchange = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            try {
                const text = await file.text();
                const preferences = JSON.parse(text);
                
                const confirmed = confirm('This will replace your current preferences. Are you sure?');
                if (!confirmed) return;

                const response = await this.makeRequest('POST', `${this.options.baseUrl}/import`, {
                    preferences,
                    overwrite: true
                });

                if (response.success) {
                    this.preferences = preferences;
                    this.applyPreferencesToUI();
                    this.showNotification('Preferences imported successfully', 'success');
                    
                    // Reload page to apply all changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error('Failed to import preferences:', error);
                this.showNotification('Failed to import preferences. Please check the file format.', 'error');
            }
        };

        fileInput.click();
    }

    /**
     * Reset preferences to defaults
     */
    async resetPreferences(category = null) {
        const message = category 
            ? `Reset ${category} preferences to defaults?`
            : 'Reset ALL preferences to defaults? This cannot be undone.';
            
        const confirmed = confirm(message);
        if (!confirmed) return;

        try {
            const response = await this.makeRequest('POST', `${this.options.baseUrl}/reset`, {
                categories: category ? [category] : []
            });

            if (response.success) {
                this.preferences = response.preferences;
                this.applyPreferencesToUI();
                this.showNotification('Preferences reset successfully', 'success');
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Failed to reset preferences:', error);
            this.showNotification('Failed to reset preferences', 'error');
        }
    }

    /**
     * Sync pending changes when coming back online
     */
    async syncPendingChanges() {
        if (this.pendingChanges.size === 0) return;

        const pendingPreferences = {};
        this.pendingChanges.forEach(key => {
            pendingPreferences[key] = this.preferences[key];
        });

        try {
            await this.updateMultiplePreferences(pendingPreferences);
            this.showNotification('Offline changes synced successfully', 'success');
        } catch (error) {
            this.showNotification('Failed to sync offline changes', 'error');
        }
    }

    /**
     * Save all pending changes immediately
     */
    async saveAllPendingChanges() {
        if (this.pendingChanges.size === 0) return;

        const pendingPreferences = {};
        this.pendingChanges.forEach(key => {
            pendingPreferences[key] = this.preferences[key];
        });

        // Use synchronous request for beforeunload
        const formData = new FormData();
        formData.append('preferences', JSON.stringify(this.categorizePreferences(pendingPreferences)));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        navigator.sendBeacon(`${this.options.baseUrl}/update`, formData);
    }

    /**
     * Apply preferences to UI elements
     */
    applyPreferencesToUI() {
        Object.entries(this.preferences).forEach(([key, value]) => {
            const elements = document.querySelectorAll(`[data-preference="${key}"], [data-preference-input="${key}"]`);
            elements.forEach(element => {
                this.setElementValue(element, value);
            });

            // Apply side effects
            this.applyPreferenceSideEffects(key, value);
        });
    }

    /**
     * Apply side effects of preference changes
     */
    applyPreferenceSideEffects(key, value) {
        switch (key) {
            case 'theme':
                this.previewTheme(value);
                break;
            case 'display_density':
                document.documentElement.setAttribute('data-density', value);
                break;
            case 'sidebar_collapsed':
                document.body.classList.toggle('sidebar-collapsed', value);
                break;
            case 'animation_enabled':
                document.body.classList.toggle('no-animations', !value);
                break;
            case 'high_contrast':
                document.body.classList.toggle('high-contrast', value);
                break;
            case 'dashboard_refresh_interval':
                this.updateDashboardRefreshInterval(value);
                break;
        }
    }

    /**
     * Update dashboard refresh interval
     */
    updateDashboardRefreshInterval(seconds) {
        if (window.DashboardManager) {
            window.DashboardManager.setRefreshInterval(seconds * 1000);
        }
    }

    /**
     * Update timezone display in UI
     */
    updateTimezoneUI(timezone, displayName) {
        const timezoneElements = document.querySelectorAll('[data-current-timezone]');
        timezoneElements.forEach(element => {
            element.textContent = displayName || timezone;
        });

        const timezoneSelect = document.querySelector('select[data-preference="user_timezone"]');
        if (timezoneSelect) {
            timezoneSelect.value = timezone;
        }
    }

    /**
     * Show visual feedback on form elements
     */
    showElementFeedback(element, status) {
        if (!element) return;

        // Remove existing feedback classes
        element.classList.remove('saving', 'success', 'error', 'offline');
        
        // Add new status class
        element.classList.add(status);

        // Show temporary icon or indicator
        this.showElementIcon(element, status);

        // Remove feedback after delay
        if (status !== 'error') {
            setTimeout(() => {
                element.classList.remove(status);
                this.hideElementIcon(element);
            }, 2000);
        }
    }

    /**
     * Show status icon next to element
     */
    showElementIcon(element, status) {
        let icon = element.parentNode.querySelector('.preference-status-icon');
        if (!icon) {
            icon = document.createElement('span');
            icon.className = 'preference-status-icon';
            element.parentNode.appendChild(icon);
        }

        const icons = {
            saving: '⏳',
            success: '✓',
            error: '✗',
            offline: '📴'
        };

        const colors = {
            saving: '#f59e0b',
            success: '#10b981',
            error: '#ef4444',
            offline: '#6b7280'
        };

        icon.textContent = icons[status] || '';
        icon.style.color = colors[status] || '#6b7280';
        icon.style.marginLeft = '8px';
    }

    /**
     * Hide status icon
     */
    hideElementIcon(element) {
        const icon = element.parentNode.querySelector('.preference-status-icon');
        if (icon) {
            icon.remove();
        }
    }

    /**
     * Get value from form element
     */
    getElementValue(element) {
        switch (element.type) {
            case 'checkbox':
                return element.checked;
            case 'radio':
                return element.checked ? element.value : null;
            case 'number':
            case 'range':
                return parseFloat(element.value);
            default:
                return element.value;
        }
    }

    /**
     * Set value to form element
     */
    setElementValue(element, value) {
        switch (element.type) {
            case 'checkbox':
                element.checked = Boolean(value);
                break;
            case 'radio':
                element.checked = element.value === value;
                break;
            default:
                element.value = value;
        }
    }

    /**
     * Categorize preferences for API submission
     */
    categorizePreferences(preferences) {
        const categories = {
            notifications: {},
            display: {},
            alerts: {},
            performance: {}
        };

        Object.entries(preferences).forEach(([key, value]) => {
            if (key.includes('notification') || key.includes('quiet_hours') || key.includes('sms') || key.includes('email') || key.includes('push')) {
                categories.notifications[key] = value;
            } else if (key.includes('theme') || key.includes('display') || key.includes('sidebar') || key.includes('animation') || key.includes('tooltip')) {
                categories.display[key] = value;
            } else if (key.includes('alert') || key.includes('threshold') || key.includes('escalation')) {
                categories.alerts[key] = value;
            } else if (key.includes('dashboard') || key.includes('lazy') || key.includes('compression') || key.includes('bandwidth')) {
                categories.performance[key] = value;
            }
        });

        return categories;
    }

    /**
     * Make HTTP request with error handling
     */
    async makeRequest(method, url, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info', duration = 5000) {
        if (!this.options.showNotifications) return;

        // Try to use existing notification system first
        if (window.NotificationManager) {
            window.NotificationManager.show(message, type, duration);
            return;
        }

        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;

        // Add to page
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        container.appendChild(notification);

        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    /**
     * Get current preferences
     */
    getPreferences() {
        return { ...this.preferences };
    }

    /**
     * Set preferences programmatically
     */
    setPreferences(preferences) {
        Object.assign(this.preferences, preferences);
        this.applyPreferencesToUI();
    }

    /**
     * Check if there are unsaved changes
     */
    hasUnsavedChanges() {
        return this.pendingChanges.size > 0;
    }
}

// Export for use in other modules
export default UserPreferencesManager;

// Global initialization if not using modules
if (typeof window !== 'undefined') {
    window.UserPreferencesManager = UserPreferencesManager;
}
