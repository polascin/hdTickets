/**
 * HD Tickets Theme Management System
 * 
 * Handles theme switching, persistence, and role-based theming.
 * Integrates with design tokens and provides smooth transitions.
 */

import Alpine from 'alpinejs';

// Theme constants
const THEME_STORAGE_KEY = 'hdt-theme-preference';
const ROLE_STORAGE_KEY = 'hdt-role-theme';
const THEMES = {
  LIGHT: 'light',
  DARK: 'dark',
  AUTO: 'auto'
};

const ROLE_THEMES = {
  ADMIN: 'admin-layout',
  AGENT: 'agent-layout',
  CUSTOMER: 'customer-layout',
  SCRAPER: 'scraper-layout'
};

/**
 * Theme Manager Class
 * Handles all theme-related functionality
 */
class ThemeManager {
  constructor() {
    this.currentTheme = THEMES.AUTO;
    this.currentRole = null;
    this.mediaQuery = null;
    this.transitionTimeout = null;
    
    this.init();
  }

  /**
   * Initialize the theme manager
   */
  init() {
    // Set up media query listener for system theme changes
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.mediaQuery.addEventListener('change', this.handleSystemThemeChange.bind(this));

    // Load saved preferences
    this.loadThemePreference();
    this.loadRoleTheme();

    // Apply initial theme
    this.applyTheme();

    // Set up Alpine.js store
    this.setupAlpineStore();

    // Add keyboard shortcut for theme toggle (Ctrl/Cmd + Shift + T)
    document.addEventListener('keydown', this.handleKeyboardShortcut.bind(this));

    // Add smooth transition classes after initial load
    requestAnimationFrame(() => {
      this.enableTransitions();
    });
  }

  /**
   * Load theme preference from localStorage
   */
  loadThemePreference() {
    const stored = localStorage.getItem(THEME_STORAGE_KEY);
    if (stored && Object.values(THEMES).includes(stored)) {
      this.currentTheme = stored;
    }
  }

  /**
   * Load role theme from localStorage or detect from body class
   */
  loadRoleTheme() {
    // First check localStorage
    const stored = localStorage.getItem(ROLE_STORAGE_KEY);
    if (stored && Object.values(ROLE_THEMES).includes(stored)) {
      this.currentRole = stored;
      return;
    }

    // Otherwise detect from body classes
    const body = document.body;
    for (const [key, className] of Object.entries(ROLE_THEMES)) {
      if (body.classList.contains(className)) {
        this.currentRole = className;
        break;
      }
    }

    // Default to customer if no role detected
    if (!this.currentRole) {
      this.currentRole = ROLE_THEMES.CUSTOMER;
    }
  }

  /**
   * Save theme preference to localStorage
   */
  saveThemePreference() {
    localStorage.setItem(THEME_STORAGE_KEY, this.currentTheme);
  }

  /**
   * Save role theme to localStorage
   */
  saveRoleTheme() {
    localStorage.setItem(ROLE_STORAGE_KEY, this.currentRole);
  }

  /**
   * Get the effective theme (resolving 'auto' to light/dark)
   */
  getEffectiveTheme() {
    if (this.currentTheme === THEMES.AUTO) {
      return this.mediaQuery?.matches ? THEMES.DARK : THEMES.LIGHT;
    }
    return this.currentTheme;
  }

  /**
   * Apply the current theme to the document
   */
  applyTheme() {
    const effectiveTheme = this.getEffectiveTheme();
    const html = document.documentElement;
    const body = document.body;

    // Update theme class on html element
    html.classList.remove(THEMES.LIGHT, THEMES.DARK);
    html.classList.add(effectiveTheme);

    // Update dark class for Tailwind
    if (effectiveTheme === THEMES.DARK) {
      html.classList.add('dark');
    } else {
      html.classList.remove('dark');
    }

    // Apply role theme to body
    body.classList.remove(...Object.values(ROLE_THEMES));
    if (this.currentRole) {
      body.classList.add(this.currentRole);
    }

    // Update meta theme-color for mobile browsers
    this.updateMetaThemeColor(effectiveTheme);

    // Dispatch theme change event
    this.dispatchThemeChangeEvent(effectiveTheme);
  }

  /**
   * Update meta theme-color based on current theme
   */
  updateMetaThemeColor(theme) {
    let metaThemeColor = document.querySelector('meta[name="theme-color"]');
    
    if (!metaThemeColor) {
      metaThemeColor = document.createElement('meta');
      metaThemeColor.name = 'theme-color';
      document.head.appendChild(metaThemeColor);
    }

    // Get color from CSS custom properties
    const computedStyle = getComputedStyle(document.documentElement);
    const surfaceColor = computedStyle.getPropertyValue('--hdt-color-surface-secondary').trim();
    
    // Fallback colors if CSS variables aren't available
    const fallbackColors = {
      [THEMES.LIGHT]: '#ffffff',
      [THEMES.DARK]: '#1f2937'
    };

    metaThemeColor.content = surfaceColor || fallbackColors[theme];
  }

  /**
   * Dispatch custom theme change event
   */
  dispatchThemeChangeEvent(effectiveTheme) {
    const event = new CustomEvent('theme-changed', {
      detail: {
        theme: this.currentTheme,
        effectiveTheme,
        role: this.currentRole,
        timestamp: Date.now()
      }
    });
    window.dispatchEvent(event);
  }

  /**
   * Handle system theme preference changes
   */
  handleSystemThemeChange(e) {
    if (this.currentTheme === THEMES.AUTO) {
      this.applyTheme();
    }
  }

  /**
   * Handle keyboard shortcut for theme toggle
   */
  handleKeyboardShortcut(e) {
    // Ctrl/Cmd + Shift + T to toggle theme
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 't') {
      e.preventDefault();
      this.cycleTheme();
    }
  }

  /**
   * Enable smooth transitions for theme changes
   */
  enableTransitions() {
    const body = document.body;
    body.classList.add('hdt-theme-transitions');
  }

  /**
   * Temporarily disable transitions during theme change
   */
  disableTransitionsDuring(callback) {
    const body = document.body;
    const hadTransitions = body.classList.contains('hdt-theme-transitions');
    
    if (hadTransitions) {
      body.classList.remove('hdt-theme-transitions');
    }

    callback();

    if (hadTransitions) {
      // Re-enable after a brief delay
      requestAnimationFrame(() => {
        body.classList.add('hdt-theme-transitions');
      });
    }
  }

  /**
   * Set theme to specific value
   */
  setTheme(theme) {
    if (!Object.values(THEMES).includes(theme)) {
      console.warn(`Invalid theme: ${theme}`);
      return;
    }

    this.currentTheme = theme;
    this.saveThemePreference();
    
    this.disableTransitionsDuring(() => {
      this.applyTheme();
    });
  }

  /**
   * Set role theme
   */
  setRoleTheme(role) {
    if (!Object.values(ROLE_THEMES).includes(role)) {
      console.warn(`Invalid role theme: ${role}`);
      return;
    }

    this.currentRole = role;
    this.saveRoleTheme();
    this.applyTheme();
  }

  /**
   * Cycle through themes (light -> dark -> auto)
   */
  cycleTheme() {
    const themes = [THEMES.LIGHT, THEMES.DARK, THEMES.AUTO];
    const currentIndex = themes.indexOf(this.currentTheme);
    const nextTheme = themes[(currentIndex + 1) % themes.length];
    
    this.setTheme(nextTheme);
  }

  /**
   * Toggle between light and dark (ignoring auto)
   */
  toggleTheme() {
    const effectiveTheme = this.getEffectiveTheme();
    const newTheme = effectiveTheme === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT;
    this.setTheme(newTheme);
  }

  /**
   * Check if reduced motion is preferred
   */
  prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  /**
   * Get theme information for components
   */
  getThemeInfo() {
    return {
      current: this.currentTheme,
      effective: this.getEffectiveTheme(),
      role: this.currentRole,
      supportsAuto: this.mediaQuery !== null,
      prefersReducedMotion: this.prefersReducedMotion()
    };
  }

  /**
   * Setup Alpine.js global store for theme management
   */
  setupAlpineStore() {
    Alpine.store('theme', {
      // State
      current: this.currentTheme,
      effective: this.getEffectiveTheme(),
      role: this.currentRole,
      isTransitioning: false,

      // Getters
      get isDark() {
        return this.effective === THEMES.DARK;
      },

      get isLight() {
        return this.effective === THEMES.LIGHT;
      },

      get isAuto() {
        return this.current === THEMES.AUTO;
      },

      get roleClass() {
        return this.role;
      },

      get themeIcon() {
        return this.effective === THEMES.DARK ? 'moon' : 'sun';
      },

      get themeLabel() {
        const labels = {
          [THEMES.LIGHT]: 'Light',
          [THEMES.DARK]: 'Dark',
          [THEMES.AUTO]: 'Auto'
        };
        return labels[this.current] || 'Unknown';
      },

      // Actions
      setTheme(theme) {
        themeManager.setTheme(theme);
        this.updateState();
      },

      setRole(role) {
        themeManager.setRoleTheme(role);
        this.updateState();
      },

      toggle() {
        themeManager.toggleTheme();
        this.updateState();
      },

      cycle() {
        themeManager.cycleTheme();
        this.updateState();
      },

      // Internal method to update Alpine store state
      updateState() {
        const info = themeManager.getThemeInfo();
        this.current = info.current;
        this.effective = info.effective;
        this.role = info.role;
      }
    });

    // Listen for theme changes to update Alpine store
    window.addEventListener('theme-changed', () => {
      Alpine.store('theme').updateState();
    });
  }
}

// Create global instance
const themeManager = new ThemeManager();

// Export for use in other modules
export default themeManager;

// Alpine.js component for theme switcher
Alpine.data('themeSwitcher', () => ({
  open: false,
  
  get themes() {
    return [
      { value: THEMES.LIGHT, label: 'Light', icon: 'sun' },
      { value: THEMES.DARK, label: 'Dark', icon: 'moon' },
      { value: THEMES.AUTO, label: 'Auto', icon: 'computer-desktop' }
    ];
  },

  get currentTheme() {
    return this.$store.theme.current;
  },

  get effectiveTheme() {
    return this.$store.theme.effective;
  },

  selectTheme(theme) {
    this.$store.theme.setTheme(theme);
    this.open = false;
  },

  toggleQuick() {
    this.$store.theme.toggle();
  },

  // Keyboard navigation
  onKeydown(event) {
    if (event.key === 'Escape') {
      this.open = false;
    } else if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      this.toggleQuick();
    }
  }
}));

// Alpine.js component for role theme indicator
Alpine.data('roleThemeIndicator', () => ({
  get roleInfo() {
    const roleLabels = {
      [ROLE_THEMES.ADMIN]: { label: 'Admin', color: 'purple' },
      [ROLE_THEMES.AGENT]: { label: 'Agent', color: 'cyan' },
      [ROLE_THEMES.CUSTOMER]: { label: 'Customer', color: 'green' },
      [ROLE_THEMES.SCRAPER]: { label: 'Scraper', color: 'yellow' }
    };

    return roleLabels[this.$store.theme.role] || { label: 'Unknown', color: 'gray' };
  }
}));

// Auto-initialize when DOM is loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    // Theme manager already initializes itself
  });
}

// CSS for smooth theme transitions
const transitionStyles = `
.hdt-theme-transitions,
.hdt-theme-transitions *,
.hdt-theme-transitions *::before,
.hdt-theme-transitions *::after {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: var(--hdt-ease-in-out);
  transition-duration: var(--hdt-duration-200);
}

@media (prefers-reduced-motion: reduce) {
  .hdt-theme-transitions,
  .hdt-theme-transitions *,
  .hdt-theme-transitions *::before,
  .hdt-theme-transitions *::after {
    transition: none !important;
  }
}
`;

// Inject transition styles
const styleSheet = document.createElement('style');
styleSheet.textContent = transitionStyles;
document.head.appendChild(styleSheet);