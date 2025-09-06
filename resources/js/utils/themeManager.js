/**
 * Theme Manager for HD Tickets Sports Events Platform
 * Complete theme switching functionality with localStorage persistence,
 * system preference detection, and smooth transitions
 */

(function() {
    'use strict';

    /**
     * Theme Manager Class
     */
    class ThemeManager {
        constructor() {
            this.themes = ['light', 'dark', 'auto'];
            this.currentTheme = 'auto';
            this.prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            this.storageKey = 'hd-tickets-theme';
            this.transitionClass = 'theme-transitioning';
            this.transitionDuration = 400;
            
            // Theme change callbacks
            this.callbacks = [];
            
            this.init();
        }

        /**
         * Initialize theme manager
         */
        init() {
            // Load saved theme or detect system preference
            this.loadTheme();
            
            // Apply initial theme
            this.applyTheme(this.currentTheme);
            
            // Listen for system preference changes
            this.prefersDarkScheme.addEventListener('change', () => {
                if (this.currentTheme === 'auto') {
                    this.applyTheme('auto');
                }
            });

            // Setup Alpine.js theme data if available
            this.setupAlpineIntegration();
            
            console.log('🎨 HD Tickets Theme Manager initialized');
        }

        /**
         * Load theme from localStorage
         */
        loadTheme() {
            try {
                const savedTheme = localStorage.getItem(this.storageKey);
                if (savedTheme && this.themes.includes(savedTheme)) {
                    this.currentTheme = savedTheme;
                }
            } catch (e) {
                console.warn('Failed to load theme from localStorage:', e);
            }
        }

        /**
         * Save theme to localStorage
         */
        saveTheme(theme) {
            try {
                localStorage.setItem(this.storageKey, theme);
            } catch (e) {
                console.warn('Failed to save theme to localStorage:', e);
            }
        }

        /**
         * Get effective theme (resolve 'auto' to actual theme)
         */
        getEffectiveTheme(theme = this.currentTheme) {
            if (theme === 'auto') {
                return this.prefersDarkScheme.matches ? 'dark' : 'light';
            }
            return theme;
        }

        /**
         * Apply theme to document
         */
        applyTheme(theme, animate = true) {
            const effectiveTheme = this.getEffectiveTheme(theme);
            
            if (animate) {
                this.startTransition();
            }

            // Set data attribute on html element
            document.documentElement.setAttribute('data-theme', effectiveTheme);
            
            // Update meta theme-color for mobile browsers
            this.updateMetaThemeColor(effectiveTheme);
            
            // Store the selected theme preference (not effective theme)
            this.currentTheme = theme;
            this.saveTheme(theme);

            if (animate) {
                setTimeout(() => {
                    this.endTransition();
                }, this.transitionDuration);
            }

            // Notify callbacks
            this.notifyCallbacks(theme, effectiveTheme);

            // Emit custom event
            window.dispatchEvent(new CustomEvent('themeChanged', {
                detail: { 
                    theme: theme, 
                    effectiveTheme: effectiveTheme 
                }
            }));
        }

        /**
         * Toggle to next theme
         */
        toggleTheme() {
            const currentIndex = this.themes.indexOf(this.currentTheme);
            const nextIndex = (currentIndex + 1) % this.themes.length;
            const nextTheme = this.themes[nextIndex];
            
            this.setTheme(nextTheme);
        }

        /**
         * Set specific theme
         */
        setTheme(theme, animate = true) {
            if (!this.themes.includes(theme)) {
                console.warn(`Invalid theme: ${theme}. Available themes:`, this.themes);
                return;
            }

            this.applyTheme(theme, animate);
        }

        /**
         * Start theme transition animation
         */
        startTransition() {
            document.body.classList.add(this.transitionClass);
        }

        /**
         * End theme transition animation
         */
        endTransition() {
            document.body.classList.remove(this.transitionClass);
        }

        /**
         * Update meta theme-color for mobile browsers
         */
        updateMetaThemeColor(effectiveTheme) {
            let themeColor = '#ffffff'; // Default light theme color
            
            if (effectiveTheme === 'dark') {
                themeColor = '#1f2937'; // Dark theme color
            }

            let metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (!metaThemeColor) {
                metaThemeColor = document.createElement('meta');
                metaThemeColor.name = 'theme-color';
                document.head.appendChild(metaThemeColor);
            }
            metaThemeColor.content = themeColor;
        }

        /**
         * Setup Alpine.js integration
         */
        setupAlpineIntegration() {
            // Wait for Alpine to be available
            document.addEventListener('alpine:init', () => {
                // Register theme data component
                Alpine.data('themeManager', () => ({
                    theme: this.currentTheme,
                    effectiveTheme: this.getEffectiveTheme(),
                    isTransitioning: false,

                    init() {
                        // Update component when theme changes
                        window.addEventListener('themeChanged', (e) => {
                            this.theme = e.detail.theme;
                            this.effectiveTheme = e.detail.effectiveTheme;
                        });

                        // Listen for transition events
                        document.body.addEventListener('transitionstart', () => {
                            this.isTransitioning = true;
                        });

                        document.body.addEventListener('transitionend', () => {
                            this.isTransitioning = false;
                        });
                    },

                    toggleTheme() {
                        window.themeManager.toggleTheme();
                    },

                    setTheme(theme) {
                        window.themeManager.setTheme(theme);
                    },

                    isDark() {
                        return this.effectiveTheme === 'dark';
                    },

                    isLight() {
                        return this.effectiveTheme === 'light';
                    },

                    isAuto() {
                        return this.theme === 'auto';
                    },

                    getThemeIcon() {
                        if (this.theme === 'auto') {
                            return this.isDark() ? 'fa-moon' : 'fa-sun';
                        }
                        return this.isDark() ? 'fa-moon' : 'fa-sun';
                    },

                    getThemeLabel() {
                        const labels = {
                            light: 'Light Mode',
                            dark: 'Dark Mode',
                            auto: 'Auto Mode'
                        };
                        return labels[this.theme] || 'Unknown';
                    },

                    getNextThemeLabel() {
                        const themes = ['light', 'dark', 'auto'];
                        const currentIndex = themes.indexOf(this.theme);
                        const nextIndex = (currentIndex + 1) % themes.length;
                        const nextTheme = themes[nextIndex];
                        
                        const labels = {
                            light: 'Switch to Light Mode',
                            dark: 'Switch to Dark Mode',
                            auto: 'Switch to Auto Mode'
                        };
                        return labels[nextTheme] || 'Switch Theme';
                    }
                }));

                console.log('✅ Theme Manager Alpine.js integration ready');
            });

            // Fallback for immediate availability
            if (window.Alpine) {
                this.setupAlpineIntegration();
            }
        }

        /**
         * Add theme change callback
         */
        onThemeChange(callback) {
            if (typeof callback === 'function') {
                this.callbacks.push(callback);
            }
        }

        /**
         * Remove theme change callback
         */
        offThemeChange(callback) {
            const index = this.callbacks.indexOf(callback);
            if (index > -1) {
                this.callbacks.splice(index, 1);
            }
        }

        /**
         * Notify all callbacks
         */
        notifyCallbacks(theme, effectiveTheme) {
            this.callbacks.forEach(callback => {
                try {
                    callback(theme, effectiveTheme);
                } catch (e) {
                    console.error('Theme callback error:', e);
                }
            });
        }

        /**
         * Get current theme info
         */
        getThemeInfo() {
            return {
                theme: this.currentTheme,
                effectiveTheme: this.getEffectiveTheme(),
                systemPreference: this.prefersDarkScheme.matches ? 'dark' : 'light',
                availableThemes: this.themes
            };
        }

        /**
         * Check if dark mode is active
         */
        isDarkMode() {
            return this.getEffectiveTheme() === 'dark';
        }

        /**
         * Check if light mode is active
         */
        isLightMode() {
            return this.getEffectiveTheme() === 'light';
        }

        /**
         * Force refresh theme (useful after DOM changes)
         */
        refreshTheme() {
            this.applyTheme(this.currentTheme, false);
        }

        /**
         * Reset to system preference
         */
        resetToSystem() {
            this.setTheme('auto');
        }

        /**
         * Get CSS custom property value
         */
        getCSSProperty(property) {
            return getComputedStyle(document.documentElement)
                .getPropertyValue(property).trim();
        }

        /**
         * Update CSS custom property
         */
        setCSSProperty(property, value) {
            document.documentElement.style.setProperty(property, value);
        }

        /**
         * Initialize theme for a specific element
         */
        initializeElement(element) {
            if (!element) return;

            // Apply theme-aware classes
            const effectiveTheme = this.getEffectiveTheme();
            element.classList.add(`theme-${effectiveTheme}`);
            element.setAttribute('data-theme', effectiveTheme);
        }

        /**
         * Create theme toggle button
         */
        createToggleButton(options = {}) {
            const defaults = {
                className: 'theme-toggle',
                showLabel: false,
                position: 'top-right'
            };
            
            const config = Object.assign(defaults, options);
            
            const button = document.createElement('button');
            button.className = config.className;
            button.type = 'button';
            button.setAttribute('aria-label', 'Toggle theme');
            button.setAttribute('data-theme-toggle', 'true');
            
            // Create icon
            const icon = document.createElement('i');
            icon.className = 'fas fa-sun theme-icon';
            button.appendChild(icon);
            
            // Create label if requested
            if (config.showLabel) {
                const label = document.createElement('span');
                label.className = 'theme-label';
                label.textContent = 'Light';
                button.appendChild(label);
            }
            
            // Add click handler
            button.addEventListener('click', () => {
                this.toggleTheme();
            });
            
            // Update button on theme change
            this.onThemeChange((theme, effectiveTheme) => {
                icon.className = `fas ${effectiveTheme === 'dark' ? 'fa-moon' : 'fa-sun'} theme-icon`;
                if (config.showLabel) {
                    button.querySelector('.theme-label').textContent = 
                        effectiveTheme === 'dark' ? 'Dark' : 'Light';
                }
                button.setAttribute('aria-label', 
                    `Switch to ${effectiveTheme === 'dark' ? 'light' : 'dark'} theme`);
            });
            
            return button;
        }

        /**
         * Add transition styles to head
         */
        addTransitionStyles() {
            const styleId = 'theme-transition-styles';
            if (document.getElementById(styleId)) return;

            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .${this.transitionClass} * {
                    transition: none !important;
                }
                
                .theme-toggle.transitioning {
                    animation: theme-toggle-pulse 0.3s ease-in-out;
                }
                
                @keyframes theme-toggle-pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
            `;
            
            document.head.appendChild(style);
        }

        /**
         * Detect and handle theme preference changes
         */
        watchSystemTheme() {
            this.prefersDarkScheme.addEventListener('change', (e) => {
                if (this.currentTheme === 'auto') {
                    console.log(`System theme changed to: ${e.matches ? 'dark' : 'light'}`);
                    this.applyTheme('auto');
                }
            });
        }

        /**
         * Initialize theme for all existing elements
         */
        initializeAllElements() {
            // Find all elements that need theme initialization
            const elements = document.querySelectorAll('[data-theme-target]');
            elements.forEach(element => {
                this.initializeElement(element);
            });
        }

        /**
         * Cleanup resources
         */
        destroy() {
            // Remove event listeners
            this.prefersDarkScheme.removeEventListener('change', this.watchSystemTheme);
            
            // Clear callbacks
            this.callbacks = [];
            
            // Remove custom styles
            const customStyles = document.getElementById('theme-transition-styles');
            if (customStyles) {
                customStyles.remove();
            }
        }
    }

    // Initialize global theme manager
    const themeManager = new ThemeManager();

    // Make available globally
    window.themeManager = themeManager;

    // Legacy support - also expose as HDTickets.ThemeManager
    if (!window.HDTickets) {
        window.HDTickets = {};
    }
    window.HDTickets.ThemeManager = themeManager;

    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = ThemeManager;
    }

    // Expose theme helper functions globally
    window.toggleTheme = () => themeManager.toggleTheme();
    window.setTheme = (theme) => themeManager.setTheme(theme);
    window.getTheme = () => themeManager.getThemeInfo();

})();
