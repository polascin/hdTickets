/**
 * Theme Management System
 * Handles dark/light theme switching, user preferences storage, and theme initialization
 */
class ThemeManager {
    constructor(options = {}) {
        this.options = {
            storageKey: 'hdtickets_theme',
            defaultTheme: 'light',
            transitions: true,
            autoDetect: true,
            ...options
        };
        
        this.currentTheme = null;
        this.systemTheme = null;
        this.observers = [];
        
        this.init();
    }

    init() {
        // Detect system theme preference
        if (this.options.autoDetect) {
            this.detectSystemTheme();
            this.watchSystemChanges();
        }

        // Load user preference or use default
        const savedTheme = this.getSavedTheme();
        const initialTheme = savedTheme || (this.options.autoDetect ? this.systemTheme : this.options.defaultTheme);
        
        this.setTheme(initialTheme, false);
        this.setupThemeToggle();
        this.setupThemePresets();
    }

    detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            this.systemTheme = 'dark';
        } else {
            this.systemTheme = 'light';
        }
    }

    watchSystemChanges() {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                this.systemTheme = e.matches ? 'dark' : 'light';
                
                // Auto-switch if user hasn't set a preference
                if (!this.getSavedTheme()) {
                    this.setTheme(this.systemTheme);
                }
            });
        }
    }

    setTheme(theme, save = true, animate = true) {
        if (this.currentTheme === theme) return;

        const previousTheme = this.currentTheme;
        this.currentTheme = theme;

        // Add transition class if animations are enabled
        if (animate && this.options.transitions) {
            document.documentElement.classList.add('theme-transitioning');
        }

        // Remove previous theme classes
        document.documentElement.classList.remove('theme-light', 'theme-dark', 'theme-auto');
        document.body.classList.remove('light-mode', 'dark-mode');

        // Apply new theme
        document.documentElement.classList.add(`theme-${theme}`);
        document.body.classList.add(`${theme}-mode`);

        // Update CSS custom properties
        this.updateThemeProperties(theme);

        // Save preference
        if (save) {
            this.saveTheme(theme);
        }

        // Notify observers
        this.notifyObservers(theme, previousTheme);

        // Update theme toggle buttons
        this.updateThemeToggles(theme);

        // Remove transition class after animation
        if (animate && this.options.transitions) {
            setTimeout(() => {
                document.documentElement.classList.remove('theme-transitioning');
            }, 300);
        }

        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(theme);
    }

    updateThemeProperties(theme) {
        const root = document.documentElement;
        
        if (theme === 'dark') {
            // Dark theme variables
            root.style.setProperty('--bg-primary', '#0f172a');
            root.style.setProperty('--bg-secondary', '#1e293b');
            root.style.setProperty('--bg-card', '#334155');
            root.style.setProperty('--text-primary', '#f8fafc');
            root.style.setProperty('--text-secondary', '#cbd5e1');
            root.style.setProperty('--text-muted', '#94a3b8');
            root.style.setProperty('--border-color', '#475569');
            root.style.setProperty('--shadow-color', 'rgba(0, 0, 0, 0.5)');
            root.style.setProperty('--accent-color', '#3b82f6');
            root.style.setProperty('--success-color', '#10b981');
            root.style.setProperty('--warning-color', '#f59e0b');
            root.style.setProperty('--error-color', '#ef4444');
        } else {
            // Light theme variables
            root.style.setProperty('--bg-primary', '#ffffff');
            root.style.setProperty('--bg-secondary', '#f8fafc');
            root.style.setProperty('--bg-card', '#ffffff');
            root.style.setProperty('--text-primary', '#0f172a');
            root.style.setProperty('--text-secondary', '#334155');
            root.style.setProperty('--text-muted', '#64748b');
            root.style.setProperty('--border-color', '#e2e8f0');
            root.style.setProperty('--shadow-color', 'rgba(0, 0, 0, 0.1)');
            root.style.setProperty('--accent-color', '#3b82f6');
            root.style.setProperty('--success-color', '#059669');
            root.style.setProperty('--warning-color', '#d97706');
            root.style.setProperty('--error-color', '#dc2626');
        }
    }

    updateMetaThemeColor(theme) {
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', theme === 'dark' ? '#0f172a' : '#ffffff');
        }
    }

    setupThemeToggle() {
        // Find theme toggle buttons
        const themeToggles = document.querySelectorAll('[data-theme-toggle]');
        
        themeToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleTheme();
            });
        });

        // Setup dropdown theme selectors
        const themeSelectors = document.querySelectorAll('[data-theme-select]');
        
        themeSelectors.forEach(selector => {
            selector.addEventListener('change', (e) => {
                this.setTheme(e.target.value);
            });
        });
    }

    setupThemePresets() {
        // Setup preset theme buttons
        const presetButtons = document.querySelectorAll('[data-theme-preset]');
        
        presetButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const preset = button.getAttribute('data-theme-preset');
                this.setTheme(preset);
            });
        });
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    updateThemeToggles(theme) {
        // Update toggle button states
        const themeToggles = document.querySelectorAll('[data-theme-toggle]');
        
        themeToggles.forEach(toggle => {
            const icon = toggle.querySelector('i, svg');
            const text = toggle.querySelector('.theme-text');
            
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
            
            if (text) {
                text.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }
            
            toggle.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
        });

        // Update dropdown selectors
        const themeSelectors = document.querySelectorAll('[data-theme-select]');
        themeSelectors.forEach(selector => {
            selector.value = theme;
        });
    }

    saveTheme(theme) {
        try {
            localStorage.setItem(this.options.storageKey, theme);
        } catch (e) {
            console.warn('Failed to save theme preference:', e);
        }
    }

    getSavedTheme() {
        try {
            return localStorage.getItem(this.options.storageKey);
        } catch (e) {
            console.warn('Failed to load theme preference:', e);
            return null;
        }
    }

    // Observer pattern for theme changes
    addObserver(callback) {
        this.observers.push(callback);
    }

    removeObserver(callback) {
        this.observers = this.observers.filter(obs => obs !== callback);
    }

    notifyObservers(newTheme, previousTheme) {
        this.observers.forEach(callback => {
            try {
                callback(newTheme, previousTheme);
            } catch (e) {
                console.error('Theme observer error:', e);
            }
        });
    }

    // Public API
    getCurrentTheme() {
        return this.currentTheme;
    }

    getSystemTheme() {
        return this.systemTheme;
    }

    isDarkMode() {
        return this.currentTheme === 'dark';
    }

    isLightMode() {
        return this.currentTheme === 'light';
    }

    // Utility methods
    getThemeColor(colorName) {
        return getComputedStyle(document.documentElement).getPropertyValue(`--${colorName}`);
    }

    setCustomProperty(property, value) {
        document.documentElement.style.setProperty(`--${property}`, value);
    }
}

// Auto-initialize if not in module environment
if (typeof module === 'undefined') {
    window.ThemeManager = ThemeManager;
    
    // Auto-initialize theme manager
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.hdTicketsTheme) {
            window.hdTicketsTheme = new ThemeManager();
        }
    });
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
