/**
 * Enhanced Mobile Navigation Manager for HD Tickets
 * Provides swipe gestures, touch interactions, and advanced mobile UX features
 */

class MobileNavigationManager {
    constructor() {
        this.isOpen = false;
        this.isAnimating = false;
        this.startY = 0;
        this.currentY = 0;
        this.isDragging = false;
        this.velocity = 0;
        this.lastY = 0;
        this.lastTime = 0;
        
        // Configuration
        this.config = {
            swipeThreshold: 50,
            velocityThreshold: 0.3,
            animationDuration: 300,
            dampingFactor: 0.7,
            snapThreshold: 0.3
        };
        
        // DOM elements
        this.hamburger = null;
        this.menu = null;
        this.backdrop = null;
        this.swipeIndicator = null;
        
        this.init();
    }

    init() {
        this.findElements();
        this.createSwipeIndicator();
        this.setupEventListeners();
        this.setupAccessibility();
        this.handleInitialState();
    }

    /**
     * Find required DOM elements
     */
    findElements() {
        this.hamburger = document.querySelector('.hd-mobile-hamburger');
        this.menu = document.querySelector('.mobile-nav-menu');
        
        if (!this.hamburger || !this.menu) {
            console.warn('Mobile navigation elements not found');
            return;
        }
        
        // Create backdrop element
        this.createBackdrop();
    }

    /**
     * Create backdrop overlay
     */
    createBackdrop() {
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'mobile-nav-backdrop';
        this.backdrop.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 39;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        `;
        
        document.body.appendChild(this.backdrop);
    }

    /**
     * Create swipe indicator
     */
    createSwipeIndicator() {
        this.swipeIndicator = document.createElement('div');
        this.swipeIndicator.className = 'swipe-indicator';
        this.menu.appendChild(this.swipeIndicator);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (!this.hamburger || !this.menu) return;

        // Hamburger click
        this.hamburger.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggle();
        });

        // Backdrop click
        this.backdrop.addEventListener('click', () => {
            this.close();
        });

        // Touch events for swipe gestures
        this.menu.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
        this.menu.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        this.menu.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });

        // Keyboard navigation
        document.addEventListener('keydown', this.handleKeyDown.bind(this));

        // Window resize
        window.addEventListener('resize', this.handleResize.bind(this));

        // Prevent scroll on mobile when menu is open
        document.addEventListener('touchmove', this.preventScroll.bind(this), { passive: false });

        // Focus trap
        this.menu.addEventListener('keydown', this.handleFocusTrap.bind(this));

        // Navigation link clicks
        this.setupNavigationLinks();
    }

    /**
     * Setup navigation link interactions
     */
    setupNavigationLinks() {
        const links = this.menu.querySelectorAll('.mobile-nav-link');
        
        links.forEach(link => {
            // Add ripple effect on touch
            link.addEventListener('touchstart', this.addRippleEffect.bind(this));
            
            // Close menu on navigation
            link.addEventListener('click', () => {
                setTimeout(() => this.close(), 150);
            });
        });
    }

    /**
     * Add ripple effect to navigation links
     */
    addRippleEffect(event) {
        const link = event.currentTarget;
        const ripple = document.createElement('span');
        const rect = link.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.touches[0].clientX - rect.left - size / 2;
        const y = event.touches[0].clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            top: ${y}px;
            left: ${x}px;
            width: ${size}px;
            height: ${size}px;
            background: rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
            pointer-events: none;
            z-index: 1;
        `;

        ripple.className = 'ripple-effect';
        link.appendChild(ripple);

        // Trigger animation
        requestAnimationFrame(() => {
            ripple.style.transform = 'scale(1)';
        });

        // Remove ripple after animation
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        }, 300);
    }

    /**
     * Handle touch start for swipe gestures
     */
    handleTouchStart(event) {
        if (this.isAnimating) return;

        const touch = event.touches[0];
        this.startY = touch.clientY;
        this.currentY = touch.clientY;
        this.lastY = touch.clientY;
        this.lastTime = Date.now();
        this.isDragging = true;
        this.velocity = 0;

        // Add swiping class
        this.menu.classList.add('swiping');
    }

    /**
     * Handle touch move for swipe gestures
     */
    handleTouchMove(event) {
        if (!this.isDragging || this.isAnimating) return;

        const touch = event.touches[0];
        this.currentY = touch.clientY;
        const deltaY = this.currentY - this.startY;
        const currentTime = Date.now();
        const deltaTime = currentTime - this.lastTime;

        // Calculate velocity
        if (deltaTime > 0) {
            this.velocity = (this.currentY - this.lastY) / deltaTime;
        }

        this.lastY = this.currentY;
        this.lastTime = currentTime;

        // Only handle upward swipes when menu is open
        if (this.isOpen && deltaY < 0) {
            event.preventDefault();
            
            const progress = Math.abs(deltaY) / this.config.swipeThreshold;
            const clampedProgress = Math.min(progress, 1);
            
            // Update swipe indicator
            this.updateSwipeIndicator(clampedProgress);
            
            // Apply transform to menu
            const translateY = Math.max(deltaY * this.config.dampingFactor, -this.config.swipeThreshold);
            this.menu.style.transform = `translateY(${translateY}px)`;
            
            // Update opacity
            const opacity = 1 - (clampedProgress * 0.3);
            this.menu.style.opacity = opacity;
        }
    }

    /**
     * Handle touch end for swipe gestures
     */
    handleTouchEnd(event) {
        if (!this.isDragging) return;

        this.isDragging = false;
        this.menu.classList.remove('swiping');

        const deltaY = this.currentY - this.startY;
        const shouldClose = Math.abs(deltaY) > this.config.swipeThreshold || 
                          Math.abs(this.velocity) > this.config.velocityThreshold;

        if (this.isOpen && deltaY < 0 && shouldClose) {
            // Swipe up to close
            this.close();
        } else {
            // Snap back to original position
            this.snapBack();
        }

        this.resetSwipeIndicator();
    }

    /**
     * Update swipe indicator
     */
    updateSwipeIndicator(progress) {
        if (this.swipeIndicator) {
            this.swipeIndicator.style.transform = `scaleX(${progress})`;
            this.swipeIndicator.classList.toggle('active', progress > 0);
        }
    }

    /**
     * Reset swipe indicator
     */
    resetSwipeIndicator() {
        if (this.swipeIndicator) {
            this.swipeIndicator.style.transform = 'scaleX(0)';
            this.swipeIndicator.classList.remove('active');
        }
    }

    /**
     * Snap menu back to original position
     */
    snapBack() {
        this.menu.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        this.menu.style.transform = 'translateY(0)';
        this.menu.style.opacity = '1';

        setTimeout(() => {
            this.menu.style.transition = '';
        }, 300);
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyDown(event) {
        if (event.key === 'Escape' && this.isOpen) {
            this.close();
        }
    }

    /**
     * Handle focus trapping within mobile menu
     */
    handleFocusTrap(event) {
        if (!this.isOpen) return;

        const focusableElements = this.menu.querySelectorAll(
            'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
            } else if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        }
    }

    /**
     * Prevent scroll when menu is open
     */
    preventScroll(event) {
        if (this.isOpen && !this.menu.contains(event.target)) {
            event.preventDefault();
        }
    }

    /**
     * Handle window resize
     */
    handleResize() {
        // Close menu on resize to desktop
        if (window.innerWidth >= 768 && this.isOpen) {
            this.close(false); // Don't animate on resize
        }
    }

    /**
     * Setup accessibility features
     */
    setupAccessibility() {
        if (!this.hamburger || !this.menu) return;

        // Set ARIA attributes
        this.hamburger.setAttribute('aria-controls', 'mobile-menu');
        this.hamburger.setAttribute('aria-expanded', 'false');
        
        this.menu.setAttribute('id', 'mobile-menu');
        this.menu.setAttribute('role', 'navigation');
        this.menu.setAttribute('aria-label', 'Mobile navigation');

        // Add screen reader text
        const srText = document.createElement('span');
        srText.className = 'sr-only';
        srText.textContent = 'Toggle navigation menu';
        this.hamburger.appendChild(srText);
    }

    /**
     * Handle initial state
     */
    handleInitialState() {
        // Ensure menu is closed initially
        this.isOpen = false;
        this.updateState();
    }

    /**
     * Toggle menu state
     */
    toggle() {
        if (this.isAnimating) return;

        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    /**
     * Open mobile menu
     */
    open() {
        if (this.isOpen || this.isAnimating) return;

        this.isAnimating = true;
        this.isOpen = true;

        // Update states
        this.updateState();

        // Show backdrop
        this.backdrop.style.visibility = 'visible';
        this.backdrop.style.opacity = '1';

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        document.body.classList.add('mobile-nav-open');

        // Focus first menu item
        setTimeout(() => {
            const firstLink = this.menu.querySelector('.mobile-nav-link');
            if (firstLink) {
                firstLink.focus();
            }
            this.isAnimating = false;
        }, this.config.animationDuration);

        // Announce to screen readers
        this.announceStateChange('Navigation menu opened');

        // Analytics tracking
        this.trackInteraction('open');
    }

    /**
     * Close mobile menu
     */
    close(animate = true) {
        if (!this.isOpen || this.isAnimating) return;

        this.isAnimating = true;
        this.isOpen = false;

        // Update states
        this.updateState();

        // Hide backdrop
        this.backdrop.style.opacity = '0';
        this.backdrop.style.visibility = 'hidden';

        // Restore body scroll
        document.body.style.overflow = '';
        document.body.classList.remove('mobile-nav-open');

        // Focus hamburger button
        setTimeout(() => {
            this.hamburger.focus();
            this.isAnimating = false;
        }, animate ? this.config.animationDuration : 0);

        // Reset any transforms
        this.menu.style.transform = '';
        this.menu.style.opacity = '';

        // Announce to screen readers
        this.announceStateChange('Navigation menu closed');

        // Analytics tracking
        this.trackInteraction('close');
    }

    /**
     * Update visual state
     */
    updateState() {
        // Update hamburger
        this.hamburger.classList.toggle('hd-mobile-hamburger--open', this.isOpen);
        this.hamburger.setAttribute('aria-expanded', this.isOpen.toString());

        // Update menu
        this.menu.classList.toggle('open', this.isOpen);
        this.menu.setAttribute('aria-hidden', (!this.isOpen).toString());

        // Update header
        const header = document.querySelector('.main-header');
        if (header) {
            header.classList.toggle('expanded', this.isOpen);
        }
    }

    /**
     * Announce state changes to screen readers
     */
    announceStateChange(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;

        document.body.appendChild(announcement);

        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    /**
     * Track user interactions for analytics
     */
    trackInteraction(action) {
        if (window.gtag) {
            gtag('event', 'mobile_navigation_interaction', {
                action: action,
                location: window.location.pathname
            });
        }

        console.debug('Mobile navigation:', action, {
            timestamp: new Date().toISOString(),
            path: window.location.pathname,
            userAgent: navigator.userAgent
        });
    }

    /**
     * Add breadcrumb navigation
     */
    addBreadcrumb(items) {
        const existingBreadcrumb = this.menu.querySelector('.mobile-breadcrumb');
        if (existingBreadcrumb) {
            existingBreadcrumb.remove();
        }

        if (!items || items.length === 0) return;

        const breadcrumb = document.createElement('nav');
        breadcrumb.className = 'mobile-breadcrumb';
        breadcrumb.setAttribute('aria-label', 'Breadcrumb');

        const breadcrumbList = document.createElement('div');
        breadcrumbList.className = 'mobile-breadcrumb-list';

        items.forEach((item, index) => {
            const breadcrumbItem = document.createElement('a');
            breadcrumbItem.href = item.url;
            breadcrumbItem.className = 'mobile-breadcrumb-item';
            breadcrumbItem.textContent = item.title;

            if (index === items.length - 1) {
                breadcrumbItem.classList.add('active');
                breadcrumbItem.setAttribute('aria-current', 'page');
            }

            breadcrumbList.appendChild(breadcrumbItem);

            // Add separator
            if (index < items.length - 1) {
                const separator = document.createElement('span');
                separator.className = 'mobile-breadcrumb-separator';
                separator.textContent = '›';
                separator.setAttribute('aria-hidden', 'true');
                breadcrumbList.appendChild(separator);
            }
        });

        breadcrumb.appendChild(breadcrumbList);
        this.menu.insertBefore(breadcrumb, this.menu.firstChild);
    }

    /**
     * Add search functionality to mobile menu
     */
    addSearch(placeholder = 'Search...', onSearch = null) {
        const existingSearch = this.menu.querySelector('.mobile-nav-search');
        if (existingSearch) {
            existingSearch.remove();
        }

        const searchContainer = document.createElement('div');
        searchContainer.className = 'mobile-nav-search';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'mobile-nav-search-input';
        searchInput.placeholder = placeholder;
        searchInput.setAttribute('aria-label', 'Search navigation');

        // Handle search input
        let searchTimeout;
        searchInput.addEventListener('input', (event) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (onSearch) {
                    onSearch(event.target.value);
                } else {
                    this.filterMenuItems(event.target.value);
                }
            }, 300);
        });

        searchContainer.appendChild(searchInput);
        this.menu.insertBefore(searchContainer, this.menu.firstChild);
    }

    /**
     * Filter menu items based on search
     */
    filterMenuItems(query) {
        const links = this.menu.querySelectorAll('.mobile-nav-link');
        const lowerQuery = query.toLowerCase();

        links.forEach(link => {
            const text = link.textContent.toLowerCase();
            const shouldShow = text.includes(lowerQuery) || query === '';
            
            link.style.display = shouldShow ? '' : 'none';
        });
    }

    /**
     * Get current state
     */
    getState() {
        return {
            isOpen: this.isOpen,
            isAnimating: this.isAnimating
        };
    }

    /**
     * Destroy the navigation manager
     */
    destroy() {
        // Remove event listeners
        if (this.hamburger) {
            this.hamburger.removeEventListener('click', this.toggle);
        }

        if (this.backdrop) {
            this.backdrop.removeEventListener('click', this.close);
            this.backdrop.remove();
        }

        // Remove backdrop
        if (this.backdrop && this.backdrop.parentNode) {
            this.backdrop.parentNode.removeChild(this.backdrop);
        }

        // Restore body styles
        document.body.style.overflow = '';
        document.body.classList.remove('mobile-nav-open');

        // Clear references
        this.hamburger = null;
        this.menu = null;
        this.backdrop = null;
        this.swipeIndicator = null;
    }
}

// Initialize mobile navigation
let mobileNavManager = null;

document.addEventListener('DOMContentLoaded', () => {
    mobileNavManager = new MobileNavigationManager();
});

// Alpine.js integration
if (window.Alpine) {
    document.addEventListener('alpine:init', () => {
        Alpine.data('mobileNavigation', () => ({
            manager: mobileNavManager,
            
            toggle() {
                if (this.manager) {
                    this.manager.toggle();
                }
            },
            
            open() {
                if (this.manager) {
                    this.manager.open();
                }
            },
            
            close() {
                if (this.manager) {
                    this.manager.close();
                }
            },
            
            getState() {
                return this.manager ? this.manager.getState() : { isOpen: false, isAnimating: false };
            }
        }));
    });
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileNavigationManager;
}

// Make available globally
window.MobileNavigationManager = MobileNavigationManager;
window.mobileNavManager = mobileNavManager;
