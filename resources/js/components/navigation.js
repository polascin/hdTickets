/**
 * Enhanced Navigation Component for HD Tickets
 * Provides robust navigation state management with accessibility features
 */

window.navigationData = function () {
  return {
    // State management
    mobileMenuOpen: false,
    adminDropdownOpen: false,
    profileDropdownOpen: false,
    isScrolled: false,

    // Accessibility
    announcer: null,

    // Performance
    scrollThrottle: null,

    init() {
      console.log('🔧 Enhanced NavigationData component initialized');

      // Setup accessibility announcer
      this.setupAnnouncer();

      // Setup scroll detection
      this.setupScrollDetection();

      // Setup keyboard navigation
      this.setupKeyboardNavigation();

      // Setup click outside detection
      this.setupClickOutside();

      // Setup escape key handling
      this.setupEscapeKey();

      // Setup focus management
      this.setupFocusManagement();

      // Setup route change detection
      this.setupRouteChangeDetection();
    },

    // Accessibility announcer setup
    setupAnnouncer() {
      this.announcer = document.createElement('div');
      this.announcer.setAttribute('aria-live', 'polite');
      this.announcer.setAttribute('aria-atomic', 'true');
      this.announcer.style.position = 'absolute';
      this.announcer.style.left = '-10000px';
      this.announcer.style.width = '1px';
      this.announcer.style.height = '1px';
      this.announcer.style.overflow = 'hidden';
      document.body.appendChild(this.announcer);
    },

    // Announce changes for screen readers
    announce(message) {
      if (this.announcer) {
        this.announcer.textContent = message;
      }
    },

    // Scroll detection for header styling
    setupScrollDetection() {
      const handleScroll = () => {
        this.isScrolled = window.scrollY > 10;

        // Update header appearance based on scroll
        const nav = document.querySelector('nav[role="navigation"]');
        if (nav) {
          nav.classList.toggle('nav-scrolled', this.isScrolled);
        }
      };

      // Throttle scroll events for performance
      window.addEventListener('scroll', () => {
        if (this.scrollThrottle) {
          cancelAnimationFrame(this.scrollThrottle);
        }
        this.scrollThrottle = requestAnimationFrame(handleScroll);
      }, { passive: true });
    },

    // Keyboard navigation setup
    setupKeyboardNavigation() {
      document.addEventListener('keydown', (e) => {
        // Tab navigation improvements
        if (e.key === 'Tab') {
          this.handleTabNavigation(e);
        }

        // Arrow key navigation in dropdowns
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          this.handleArrowNavigation(e);
        }

        // Enter key handling
        if (e.key === 'Enter') {
          this.handleEnterKey(e);
        }
      });
    },

    // Tab navigation handler
    handleTabNavigation(e) {
      const focusableElements = this.getFocusableElements();
      const currentIndex = focusableElements.indexOf(document.activeElement);

      if (currentIndex === -1) return;

      // Trap focus within open dropdowns
      if (this.adminDropdownOpen || this.profileDropdownOpen) {
        const dropdown = this.adminDropdownOpen ?
          document.querySelector('[data-dropdown="admin"]') :
          document.querySelector('[data-dropdown="profile"]');

        if (dropdown) {
          const dropdownFocusable = this.getFocusableElements(dropdown);
          if (dropdownFocusable.length > 0) {
            e.preventDefault();
            const nextIndex = e.shiftKey ?
              (currentIndex - 1 + dropdownFocusable.length) % dropdownFocusable.length :
              (currentIndex + 1) % dropdownFocusable.length;
            dropdownFocusable[nextIndex].focus();
          }
        }
      }
    },

    // Arrow key navigation in dropdowns
    handleArrowNavigation(e) {
      if (this.adminDropdownOpen || this.profileDropdownOpen) {
        e.preventDefault();

        const dropdown = this.adminDropdownOpen ?
          document.querySelector('[data-dropdown="admin"]') :
          document.querySelector('[data-dropdown="profile"]');

        if (dropdown) {
          const links = dropdown.querySelectorAll('a, button');
          const currentIndex = Array.from(links).indexOf(document.activeElement);

          let nextIndex;
          if (e.key === 'ArrowDown') {
            nextIndex = (currentIndex + 1) % links.length;
          } else {
            nextIndex = (currentIndex - 1 + links.length) % links.length;
          }

          links[nextIndex].focus();
        }
      }
    },

    // Enter key handler
    handleEnterKey(e) {
      const target = e.target;

      // Handle button clicks via keyboard
      if (target.tagName === 'BUTTON' && target.getAttribute('role') !== 'menuitem') {
        target.click();
      }
    },

    // Get focusable elements
    getFocusableElements(container = document) {
      const selector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
      return Array.from(container.querySelectorAll(selector));
    },

    // Click outside detection
    setupClickOutside() {
      document.addEventListener('click', (e) => {
        const nav = document.querySelector('nav[role="navigation"]');
        if (nav && !nav.contains(e.target)) {
          this.closeAll();
        }
      });
    },

    // Escape key handling
    setupEscapeKey() {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          this.closeAll();
          this.announce('Navigation menus closed');
        }
      });
    },

    // Focus management
    setupFocusManagement() {
      // Return focus to trigger when dropdown closes
      this.previousFocus = null;
    },

    // Route change detection
    setupRouteChangeDetection() {
      // Close dropdowns when navigating (if using SPA routing)
      window.addEventListener('popstate', () => {
        this.closeAll();
      });

      // Close dropdowns on link clicks
      document.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' && e.target.href) {
          setTimeout(() => this.closeAll(), 100);
        }
      });
    },

    // Close all dropdowns
    closeAll() {
      this.adminDropdownOpen = false;
      this.profileDropdownOpen = false;
      this.mobileMenuOpen = false;

      // Return focus if needed
      if (this.previousFocus) {
        this.previousFocus.focus();
        this.previousFocus = null;
      }
    },

    // Mobile menu toggle
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen;

      // Close desktop dropdowns when opening mobile menu
      if (this.mobileMenuOpen) {
        this.adminDropdownOpen = false;
        this.profileDropdownOpen = false;
        this.announce('Mobile menu opened');

        // Focus first menu item
        this.$nextTick(() => {
          const firstLink = document.querySelector('#mobile-menu a, #mobile-menu button');
          if (firstLink) {
            firstLink.focus();
          }
        });
      } else {
        this.announce('Mobile menu closed');
      }

      // Prevent body scroll when mobile menu is open
      document.body.style.overflow = this.mobileMenuOpen ? 'hidden' : '';

      console.log('📱 Mobile menu:', this.mobileMenuOpen ? 'OPEN' : 'CLOSED');
    },

    // Admin dropdown toggle
    toggleAdminDropdown() {
      this.previousFocus = this.adminDropdownOpen ? null : document.activeElement;
      this.adminDropdownOpen = !this.adminDropdownOpen;

      // Close other dropdowns
      this.profileDropdownOpen = false;
      this.mobileMenuOpen = false;

      if (this.adminDropdownOpen) {
        this.announce('Admin menu opened');

        // Focus first menu item
        this.$nextTick(() => {
          const firstLink = document.querySelector('[data-dropdown="admin"] a, [data-dropdown="admin"] button');
          if (firstLink) {
            firstLink.focus();
          }
        });
      } else {
        this.announce('Admin menu closed');
      }

      console.log('🔧 Admin dropdown:', this.adminDropdownOpen ? 'OPEN' : 'CLOSED');
    },

    // Profile dropdown toggle
    toggleProfileDropdown() {
      this.previousFocus = this.profileDropdownOpen ? null : document.activeElement;
      this.profileDropdownOpen = !this.profileDropdownOpen;

      // Close other dropdowns
      this.adminDropdownOpen = false;
      this.mobileMenuOpen = false;

      if (this.profileDropdownOpen) {
        this.announce('Profile menu opened');

        // Focus first menu item
        this.$nextTick(() => {
          const firstLink = document.querySelector('[data-dropdown="profile"] a, [data-dropdown="profile"] button');
          if (firstLink) {
            firstLink.focus();
          }
        });
      } else {
        this.announce('Profile menu closed');
      }

      console.log('👤 Profile dropdown:', this.profileDropdownOpen ? 'OPEN' : 'CLOSED');
    },

    // Helper method for dropdown links to close dropdown after click
    handleDropdownItemClick(callback = null) {
      if (callback && typeof callback === 'function') {
        callback();
      }

      // Close dropdowns after a brief delay to allow for navigation
      setTimeout(() => {
        this.closeAll();
      }, 100);
    },

    // Theme toggle helper
    toggleTheme() {
      const isDark = document.documentElement.classList.contains('dark');
      document.documentElement.classList.toggle('dark', !isDark);
      localStorage.setItem('darkMode', !isDark);

      this.announce(`Switched to ${!isDark ? 'dark' : 'light'} theme`);
    },

    // Cleanup on destroy
    destroy() {
      if (this.announcer) {
        document.body.removeChild(this.announcer);
      }

      if (this.scrollThrottle) {
        cancelAnimationFrame(this.scrollThrottle);
      }

      // Reset body overflow
      document.body.style.overflow = '';
    }
  };
};
