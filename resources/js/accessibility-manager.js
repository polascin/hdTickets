/**
 * HD Tickets Accessibility Manager
 * 
 * Provides utilities and helpers for WCAG 2.1 AA compliance including:
 * - Focus management and trapping
 * - Keyboard navigation
 * - Screen reader announcements
 * - Skip links and landmarks
 * - Motion and animation controls
 */

import Alpine from 'alpinejs';

/**
 * Accessibility Manager Class
 * Handles global accessibility features and utilities
 */
class AccessibilityManager {
  constructor() {
    this.focusStack = [];
    this.trapStack = [];
    this.announceRegion = null;
    this.skipLink = null;
    this.reducedMotion = false;

    this.init();
  }

  /**
   * Initialize accessibility manager
   */
  init() {
    this.createSkipLink();
    this.createAnnounceRegion();
    this.setupKeyboardNavigation();
    this.setupMotionPreferences();
    this.setupFocusManagement();
    this.setupAlpineStore();
    this.addAccessibilityCSS();

    // Monitor for reduced motion changes
    this.watchMotionPreference();

    // Set up global keyboard shortcuts
    this.setupGlobalShortcuts();
  }

  /**
   * Create skip to main content link
   */
  createSkipLink() {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.className = 'hdt-skip-link sr-only-focusable';
    skipLink.setAttribute('role', 'navigation');
    skipLink.setAttribute('aria-label', 'Skip to main content');

    // Add keyboard interaction
    skipLink.addEventListener('click', (e) => {
      e.preventDefault();
      this.skipToMainContent();
    });

    skipLink.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        this.skipToMainContent();
      }
    });

    document.body.insertBefore(skipLink, document.body.firstChild);
    this.skipLink = skipLink;
  }

  /**
   * Skip to main content functionality
   */
  skipToMainContent() {
    const mainContent = document.getElementById('main-content') ||
      document.querySelector('main[role="main"]') ||
      document.querySelector('main') ||
      document.querySelector('[role="main"]');

    if (mainContent) {
      mainContent.focus();
      mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
      this.announce('Skipped to main content');
    }
  }

  /**
   * Create screen reader announcement region
   */
  createAnnounceRegion() {
    const announceRegion = document.createElement('div');
    announceRegion.setAttribute('aria-live', 'polite');
    announceRegion.setAttribute('aria-atomic', 'true');
    announceRegion.setAttribute('role', 'status');
    announceRegion.className = 'sr-only';
    announceRegion.id = 'hdt-announce-region';

    document.body.appendChild(announceRegion);
    this.announceRegion = announceRegion;
  }

  /**
   * Announce message to screen readers
   */
  announce(message, priority = 'polite') {
    if (!this.announceRegion) return;

    // Clear previous message
    this.announceRegion.textContent = '';

    // Set priority
    this.announceRegion.setAttribute('aria-live', priority);

    // Announce new message with slight delay for screen readers
    setTimeout(() => {
      this.announceRegion.textContent = message;
    }, 100);

    // Clear message after announcement
    setTimeout(() => {
      this.announceRegion.textContent = '';
    }, 1000);
  }

  /**
   * Set up global keyboard navigation
   */
  setupKeyboardNavigation() {
    document.addEventListener('keydown', (e) => {
      // Escape key handling
      if (e.key === 'Escape') {
        this.handleEscape();
      }

      // Tab key management for focus traps
      if (e.key === 'Tab') {
        this.handleTabKey(e);
      }

      // Arrow key navigation for components
      if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
        this.handleArrowKeys(e);
      }
    });
  }

  /**
   * Handle escape key press
   */
  handleEscape() {
    // Close any open modals or overlays
    const openModals = document.querySelectorAll('[role="dialog"][aria-hidden="false"]');
    const openMenus = document.querySelectorAll('[role="menu"][aria-expanded="true"]');
    const _openDropdowns = document.querySelectorAll('[aria-expanded="true"]');

    openModals.forEach(modal => {
      const closeButton = modal.querySelector('[data-close], .modal-close');
      if (closeButton) closeButton.click();
    });

    openMenus.forEach(menu => {
      const trigger = document.querySelector(`[aria-controls="${menu.id}"]`);
      if (trigger) trigger.click();
    });

    // Restore focus to last focused element
    this.restoreFocus();
  }

  /**
   * Handle tab key for focus trapping
   */
  handleTabKey(e) {
    const currentTrap = this.getCurrentFocusTrap();
    if (!currentTrap) return;

    const focusableElements = this.getFocusableElements(currentTrap);
    if (focusableElements.length === 0) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (e.shiftKey) {
      // Shift + Tab
      if (document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
      }
    } else {
      // Tab
      if (document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
      }
    }
  }

  /**
   * Handle arrow key navigation
   */
  handleArrowKeys(e) {
    const target = e.target;
    const role = target.getAttribute('role');

    // Menu navigation
    if (role === 'menuitem' || target.closest('[role="menu"]')) {
      this.handleMenuNavigation(e);
    }

    // Tab navigation
    if (role === 'tab' || target.closest('[role="tablist"]')) {
      this.handleTabNavigation(e);
    }

    // Grid navigation
    if (role === 'gridcell' || target.closest('[role="grid"]')) {
      this.handleGridNavigation(e);
    }
  }

  /**
   * Menu navigation with arrow keys
   */
  handleMenuNavigation(e) {
    if (!['ArrowUp', 'ArrowDown'].includes(e.key)) return;

    e.preventDefault();
    const menu = e.target.closest('[role="menu"]');
    if (!menu) return;

    const menuItems = Array.from(menu.querySelectorAll('[role="menuitem"]:not([disabled])'));
    const currentIndex = menuItems.indexOf(e.target);

    let nextIndex;
    if (e.key === 'ArrowDown') {
      nextIndex = (currentIndex + 1) % menuItems.length;
    } else {
      nextIndex = (currentIndex - 1 + menuItems.length) % menuItems.length;
    }

    menuItems[nextIndex].focus();
  }

  /**
   * Tab navigation with arrow keys
   */
  handleTabNavigation(e) {
    if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;

    e.preventDefault();
    const tablist = e.target.closest('[role="tablist"]');
    if (!tablist) return;

    const tabs = Array.from(tablist.querySelectorAll('[role="tab"]:not([disabled])'));
    const currentIndex = tabs.indexOf(e.target);

    let nextIndex;
    if (e.key === 'ArrowRight') {
      nextIndex = (currentIndex + 1) % tabs.length;
    } else {
      nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
    }

    tabs[nextIndex].focus();
    tabs[nextIndex].click(); // Activate the tab
  }

  /**
   * Grid navigation with arrow keys
   */
  handleGridNavigation(e) {
    const grid = e.target.closest('[role="grid"]');
    if (!grid) return;

    e.preventDefault();
    const rows = Array.from(grid.querySelectorAll('[role="row"]'));
    const currentCell = e.target;
    const currentRow = currentCell.closest('[role="row"]');
    const currentRowIndex = rows.indexOf(currentRow);
    const cellsInRow = Array.from(currentRow.querySelectorAll('[role="gridcell"]'));
    const currentCellIndex = cellsInRow.indexOf(currentCell);

    let targetCell;

    switch (e.key) {
      case 'ArrowRight':
        targetCell = cellsInRow[currentCellIndex + 1];
        break;
      case 'ArrowLeft':
        targetCell = cellsInRow[currentCellIndex - 1];
        break;
      case 'ArrowDown':
        if (rows[currentRowIndex + 1]) {
          const nextRowCells = rows[currentRowIndex + 1].querySelectorAll('[role="gridcell"]');
          targetCell = nextRowCells[currentCellIndex];
        }
        break;
      case 'ArrowUp':
        if (rows[currentRowIndex - 1]) {
          const prevRowCells = rows[currentRowIndex - 1].querySelectorAll('[role="gridcell"]');
          targetCell = prevRowCells[currentCellIndex];
        }
        break;
    }

    if (targetCell) {
      targetCell.focus();
    }
  }

  /**
   * Setup motion preferences monitoring
   */
  setupMotionPreferences() {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    this.reducedMotion = mediaQuery.matches;

    mediaQuery.addEventListener('change', (e) => {
      this.reducedMotion = e.matches;
      this.updateMotionSettings();
    });

    this.updateMotionSettings();
  }

  /**
   * Watch for motion preference changes
   */
  watchMotionPreference() {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    mediaQuery.addEventListener('change', () => {
      this.updateMotionSettings();
      this.announce('Animation preferences updated');
    });
  }

  /**
   * Update motion settings based on preference
   */
  updateMotionSettings() {
    const root = document.documentElement;

    if (this.reducedMotion) {
      root.classList.add('hdt-reduced-motion');
      // Override all duration tokens to 0ms
      root.style.setProperty('--hdt-duration-75', '0ms');
      root.style.setProperty('--hdt-duration-100', '0ms');
      root.style.setProperty('--hdt-duration-150', '0ms');
      root.style.setProperty('--hdt-duration-200', '0ms');
      root.style.setProperty('--hdt-duration-300', '0ms');
      root.style.setProperty('--hdt-duration-500', '0ms');
    } else {
      root.classList.remove('hdt-reduced-motion');
      // Restore original durations
      root.style.removeProperty('--hdt-duration-75');
      root.style.removeProperty('--hdt-duration-100');
      root.style.removeProperty('--hdt-duration-150');
      root.style.removeProperty('--hdt-duration-200');
      root.style.removeProperty('--hdt-duration-300');
      root.style.removeProperty('--hdt-duration-500');
    }
  }

  /**
   * Setup global accessibility shortcuts
   */
  setupGlobalShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Alt + 1: Skip to main content
      if (e.altKey && e.key === '1') {
        e.preventDefault();
        this.skipToMainContent();
      }

      // Alt + 2: Skip to navigation
      if (e.altKey && e.key === '2') {
        e.preventDefault();
        this.skipToNavigation();
      }

      // Alt + R: Announce current page/section
      if (e.altKey && e.key === 'r') {
        e.preventDefault();
        this.announceCurrentLocation();
      }
    });
  }

  /**
   * Skip to navigation
   */
  skipToNavigation() {
    const nav = document.querySelector('nav[role="navigation"]') ||
      document.querySelector('nav') ||
      document.querySelector('[role="navigation"]');

    if (nav) {
      const firstLink = nav.querySelector('a, button, [tabindex="0"]');
      if (firstLink) {
        firstLink.focus();
        this.announce('Navigated to main navigation');
      }
    }
  }

  /**
   * Announce current location
   */
  announceCurrentLocation() {
    const title = document.title;
    const heading = document.querySelector('h1');
    const breadcrumbs = document.querySelector('[aria-label="breadcrumb"]');

    let announcement = `Current page: ${title}`;

    if (heading) {
      announcement += `. Main heading: ${heading.textContent.trim()}`;
    }

    if (breadcrumbs) {
      const breadcrumbText = breadcrumbs.textContent.trim().replace(/\s+/g, ' ');
      announcement += `. Location: ${breadcrumbText}`;
    }

    this.announce(announcement, 'assertive');
  }

  /**
   * Focus management
   */
  setupFocusManagement() {
    // Store focus before modals/overlays open
    document.addEventListener('focusin', (e) => {
      if (!this.isInFocusTrap(e.target)) {
        this.lastFocusedElement = e.target;
      }
    });
  }

  /**
   * Save current focus
   */
  saveFocus(element = document.activeElement) {
    this.focusStack.push(element);
  }

  /**
   * Restore focus to last saved element
   */
  restoreFocus() {
    if (this.focusStack.length > 0) {
      const element = this.focusStack.pop();
      if (element && element.focus) {
        element.focus();
      }
    } else if (this.lastFocusedElement && this.lastFocusedElement.focus) {
      this.lastFocusedElement.focus();
    }
  }

  /**
   * Create focus trap for modals/overlays
   */
  createFocusTrap(container) {
    if (!container) return null;

    const trap = {
      container,
      active: false,
      previousFocus: document.activeElement
    };

    this.trapStack.push(trap);
    this.activateFocusTrap(trap);

    return trap;
  }

  /**
   * Activate focus trap
   */
  activateFocusTrap(trap) {
    trap.active = true;

    // Focus first focusable element
    const focusableElements = this.getFocusableElements(trap.container);
    if (focusableElements.length > 0) {
      focusableElements[0].focus();
    }
  }

  /**
   * Remove focus trap
   */
  removeFocusTrap(trap) {
    if (!trap) return;

    const index = this.trapStack.indexOf(trap);
    if (index > -1) {
      this.trapStack.splice(index, 1);
    }

    // Restore focus to previous element
    if (trap.previousFocus && trap.previousFocus.focus) {
      trap.previousFocus.focus();
    }
  }

  /**
   * Get current active focus trap
   */
  getCurrentFocusTrap() {
    const activeTrap = this.trapStack.find(trap => trap.active);
    return activeTrap ? activeTrap.container : null;
  }

  /**
   * Check if element is within a focus trap
   */
  isInFocusTrap(element) {
    return this.trapStack.some(trap =>
      trap.active && trap.container.contains(element)
    );
  }

  /**
   * Get all focusable elements within container
   */
  getFocusableElements(container) {
    const focusableSelectors = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled]):not([type="hidden"])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable]:not([contenteditable="false"])'
    ].join(', ');

    return Array.from(container.querySelectorAll(focusableSelectors))
      .filter(element => {
        return element.offsetWidth > 0 &&
          element.offsetHeight > 0 &&
          !element.hasAttribute('hidden') &&
          window.getComputedStyle(element).visibility !== 'hidden';
      });
  }

  /**
   * Add accessibility-specific CSS
   */
  addAccessibilityCSS() {
    const styles = `
      /* Screen reader only content */
      .sr-only {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
      }

      /* Screen reader only that becomes visible on focus */
      .sr-only-focusable:focus {
        position: static !important;
        width: auto !important;
        height: auto !important;
        padding: inherit !important;
        margin: inherit !important;
        overflow: visible !important;
        clip: auto !important;
        white-space: normal !important;
      }

      /* Skip link styling */
      .hdt-skip-link {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        padding: var(--hdt-spacing-2) var(--hdt-spacing-4);
        background: var(--hdt-color-primary-600);
        color: white;
        text-decoration: none;
        border-radius: 0 0 var(--hdt-radius-md) 0;
        font-weight: var(--hdt-font-weight-medium);
        transform: translateY(-100%);
        transition: transform var(--hdt-duration-200) var(--hdt-ease-out);
      }

      .hdt-skip-link:focus {
        transform: translateY(0);
      }

      /* High contrast mode support */
      @media (prefers-contrast: high) {
        :root {
          --hdt-color-border-primary: var(--hdt-gray-900);
          --hdt-color-text-primary: var(--hdt-gray-950);
        }
        
        .dark {
          --hdt-color-border-primary: var(--hdt-gray-100);
          --hdt-color-text-primary: var(--hdt-gray-50);
        }
      }

      /* Reduced motion support */
      .hdt-reduced-motion,
      .hdt-reduced-motion *,
      .hdt-reduced-motion *::before,
      .hdt-reduced-motion *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }

      /* Focus indicators */
      .hdt-focus-visible {
        outline: 2px solid var(--hdt-color-focus-ring);
        outline-offset: 2px;
      }

      /* Ensure interactive elements have minimum size */
      button, input, select, textarea, a, [role="button"], [tabindex] {
        min-height: 44px;
        min-width: 44px;
      }

      /* Exception for icons and small elements */
      .hdt-icon-button, .hdt-small-interactive {
        min-height: 32px;
        min-width: 32px;
      }
    `;

    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
  }

  /**
   * Setup Alpine.js store for accessibility
   */
  setupAlpineStore() {
    Alpine.store('a11y', {
      reducedMotion: this.reducedMotion,

      // Methods
      announce: (message, priority = 'polite') => {
        this.announce(message, priority);
      },

      saveFocus: () => {
        this.saveFocus();
      },

      restoreFocus: () => {
        this.restoreFocus();
      },

      createFocusTrap: (container) => {
        return this.createFocusTrap(container);
      },

      removeFocusTrap: (trap) => {
        this.removeFocusTrap(trap);
      },

      skipToMain: () => {
        this.skipToMainContent();
      },

      // State getters
      get hasReducedMotion() {
        return this.reducedMotion;
      }
    });

    // Update store when motion preference changes
    window.addEventListener('motionchange', () => {
      Alpine.store('a11y').reducedMotion = this.reducedMotion;
    });
  }

  /**
   * Validate color contrast
   */
  validateColorContrast(foreground, background) {
    // Simple contrast ratio calculation
    // In production, use a proper color contrast library
    const getLuminance = (color) => {
      const rgb = parseInt(color.replace('#', ''), 16);
      const r = (rgb >> 16) & 0xff;
      const g = (rgb >> 8) & 0xff;
      const b = (rgb >> 0) & 0xff;

      const [rs, gs, bs] = [r, g, b].map(c => {
        c = c / 255;
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
      });

      return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    };

    const l1 = getLuminance(foreground);
    const l2 = getLuminance(background);
    const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);

    return {
      ratio,
      aa: ratio >= 4.5,      // WCAG AA standard
      aaa: ratio >= 7,       // WCAG AAA standard
      aaLarge: ratio >= 3    // WCAG AA for large text
    };
  }
}

// Create global instance
const accessibilityManager = new AccessibilityManager();

// Export for use in other modules
export default accessibilityManager;

// Alpine.js component for accessible modals
Alpine.data('accessibleModal', () => ({
  open: false,
  focusTrap: null,

  show() {
    this.open = true;
    this.$nextTick(() => {
      this.focusTrap = accessibilityManager.createFocusTrap(this.$el);
      accessibilityManager.announce('Modal opened', 'assertive');
    });
  },

  hide() {
    if (this.focusTrap) {
      accessibilityManager.removeFocusTrap(this.focusTrap);
      this.focusTrap = null;
    }
    this.open = false;
    accessibilityManager.announce('Modal closed');
  },

  onKeydown(event) {
    if (event.key === 'Escape') {
      this.hide();
    }
  }
}));

// Alpine.js component for accessible tabs
Alpine.data('accessibleTabs', (defaultTab = 0) => ({
  activeTab: defaultTab,

  selectTab(index) {
    this.activeTab = index;
    accessibilityManager.announce(`Tab ${index + 1} selected`);
  },

  onKeydown(event, index) {
    const tabs = this.$el.querySelectorAll('[role="tab"]');

    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
      event.preventDefault();

      let nextIndex;
      if (event.key === 'ArrowRight') {
        nextIndex = (index + 1) % tabs.length;
      } else {
        nextIndex = (index - 1 + tabs.length) % tabs.length;
      }

      this.selectTab(nextIndex);
      tabs[nextIndex].focus();
    }
  }
}));

// Alpine.js component for accessible dropdowns
Alpine.data('accessibleDropdown', () => ({
  open: false,

  toggle() {
    this.open = !this.open;

    if (this.open) {
      this.$nextTick(() => {
        const firstItem = this.$el.querySelector('[role="menuitem"]');
        if (firstItem) firstItem.focus();
        accessibilityManager.announce('Menu opened');
      });
    } else {
      accessibilityManager.announce('Menu closed');
    }
  },

  close() {
    this.open = false;
  },

  onKeydown(event) {
    if (event.key === 'Escape') {
      this.close();
      this.$refs.trigger.focus();
    }
  }
}));

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    // Accessibility manager initializes itself in constructor
  });
}