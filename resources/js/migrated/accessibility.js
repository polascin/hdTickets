/**
 * HD Tickets - Accessibility Manager
 * Comprehensive JavaScript for WCAG 2.1 AA compliance and accessibility enhancements
 * 
 * Features:
 * - Focus management and keyboard navigation
 * - Screen reader announcements and live regions
 * - Skip links and landmark navigation
 * - Modal and dropdown accessibility
 * - High contrast mode detection
 * - Reduced motion respect
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 * @license MIT
 */

class AccessibilityManager {
    constructor(options = {}) {
        this.options = {
            announcePageChanges: true,
            enableFocusTrap: true,
            enableSkipLinks: true,
            enableKeyboardNavigation: true,
            enableScreenReader: true,
            debugMode: false,
            ...options
        };

        this.focusHistory = [];
        this.trapStack = [];
        this.liveRegion = null;
        this.skipLinksContainer = null;
        this.currentModalId = null;
        this.keyboardUser = false;

        this.init();
    }

    /**
     * Initialize the accessibility manager
     */
    init() {
        this.detectKeyboardUser();
        this.createSkipLinks();
        this.createLiveRegion();
        this.setupGlobalKeyboardHandlers();
        this.setupFocusManagement();
        this.setupHighContrastDetection();
        this.setupReducedMotionDetection();
        this.enhanceExistingElements();
        this.observeDOMChanges();
        
        if (this.options.debugMode) {
            this.setupDebugMode();
        }

        this.log('Accessibility Manager initialized');
    }

    /**
     * Detect if user is using keyboard navigation
     */
    detectKeyboardUser() {
        // Initially assume mouse user
        document.body.classList.add('mouse-user');
        
        // Detect first Tab key press
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                this.keyboardUser = true;
                document.body.classList.remove('mouse-user');
                document.body.classList.add('keyboard-user');
            }
        }, { once: true });

        // Reset on mouse interaction
        document.addEventListener('mousedown', () => {
            if (this.keyboardUser) {
                this.keyboardUser = false;
                document.body.classList.remove('keyboard-user');
                document.body.classList.add('mouse-user');
            }
        });
    }

    /**
     * Create skip navigation links
     */
    createSkipLinks() {
        if (!this.options.enableSkipLinks) return;

        // Suppress skip links when requested (e.g., minimal auth pages)
        const suppress = document.body && (
            document.body.dataset.suppressA11ySkipLinks === 'true' ||
            document.body.classList.contains('suppress-skip-links') ||
            document.querySelector('meta[name="suppress-skip-links"][content="true"]')
        );
        if (suppress) {
            return;
        }

        this.skipLinksContainer = document.createElement('nav');
        this.skipLinksContainer.className = 'skip-links';
        this.skipLinksContainer.setAttribute('aria-label', 'Skip navigation');

        const skipLinks = [
            { href: '#main-content', text: 'Skip to main content' },
            { href: '#navigation', text: 'Skip to navigation' },
            { href: '#search', text: 'Skip to search' },
            { href: '#footer', text: 'Skip to footer' }
        ];

        skipLinks.forEach(link => {
            const skipLink = document.createElement('a');
            skipLink.href = link.href;
            skipLink.className = 'skip-link';
            skipLink.textContent = link.text;
            
            // Handle skip link activation
            skipLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.skipToContent(link.href);
            });

            this.skipLinksContainer.appendChild(skipLink);
        });

        document.body.insertBefore(this.skipLinksContainer, document.body.firstChild);
    }

    /**
     * Skip to content functionality
     */
    skipToContent(target) {
        const element = document.querySelector(target);
        if (element) {
            // Make element focusable if it isn't already
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '-1');
            }
            
            element.focus();
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            this.announce(`Skipped to ${element.textContent || element.getAttribute('aria-label') || 'content'}`);
        }
    }

    /**
     * Create live region for announcements
     */
    createLiveRegion() {
        if (!this.options.enableScreenReader) return;

        this.liveRegion = document.createElement('div');
        this.liveRegion.className = 'live-region';
        this.liveRegion.setAttribute('aria-live', 'polite');
        this.liveRegion.setAttribute('aria-atomic', 'true');
        this.liveRegion.setAttribute('aria-relevant', 'additions text');
        
        document.body.appendChild(this.liveRegion);
    }

    /**
     * Announce message to screen readers
     */
    announce(message, priority = 'polite') {
        if (!this.liveRegion || !message) return;

        // Clear previous announcement
        this.liveRegion.textContent = '';
        
        // Set priority
        this.liveRegion.setAttribute('aria-live', priority);
        
        // Add new announcement after a brief delay
        setTimeout(() => {
            this.liveRegion.textContent = message;
            this.log(`Announced: ${message}`);
        }, 100);

        // Clear announcement after it's been read
        setTimeout(() => {
            this.liveRegion.textContent = '';
        }, 5000);
    }

    /**
     * Setup global keyboard handlers
     */
    setupGlobalKeyboardHandlers() {
        if (!this.options.enableKeyboardNavigation) return;

        document.addEventListener('keydown', (e) => {
            // Escape key handling
            if (e.key === 'Escape') {
                this.handleEscapeKey(e);
            }

            // Tab key handling for focus management
            if (e.key === 'Tab') {
                this.handleTabKey(e);
            }

            // Arrow key navigation for custom components
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                this.handleArrowKeys(e);
            }

            // Enter and Space for custom interactive elements
            if (e.key === 'Enter' || e.key === ' ') {
                this.handleActivationKeys(e);
            }

            // Alt + number keys for landmark navigation
            if (e.altKey && e.key >= '1' && e.key <= '9') {
                this.handleLandmarkNavigation(e);
            }
        });
    }

    /**
     * Handle Escape key presses
     */
    handleEscapeKey(e) {
        // Close modal if open
        if (this.currentModalId) {
            this.closeModal(this.currentModalId);
            return;
        }

        // Close dropdowns
        const openDropdowns = document.querySelectorAll('.dropdown.open, [aria-expanded="true"]');
        openDropdowns.forEach(dropdown => {
            dropdown.classList.remove('open');
            dropdown.setAttribute('aria-expanded', 'false');
        });

        // Exit focus trap
        if (this.trapStack.length > 0) {
            this.releaseFocusTrap();
        }
    }

    /**
     * Handle Tab key for focus management
     */
    handleTabKey(e) {
        if (this.trapStack.length > 0) {
            this.manageFocusTrap(e);
        }
    }

    /**
     * Handle arrow key navigation
     */
    handleArrowKeys(e) {
        const activeElement = document.activeElement;
        
        // Handle menu navigation
        if (activeElement.closest('[role="menu"]')) {
            this.handleMenuNavigation(e, activeElement);
        }
        
        // Handle tab navigation
        if (activeElement.closest('[role="tablist"]')) {
            this.handleTabNavigation(e, activeElement);
        }
        
        // Handle listbox navigation
        if (activeElement.closest('[role="listbox"]')) {
            this.handleListboxNavigation(e, activeElement);
        }
    }

    /**
     * Handle Enter and Space key activation
     */
    handleActivationKeys(e) {
        const activeElement = document.activeElement;
        
        // Handle custom buttons
        if (activeElement.getAttribute('role') === 'button' && !activeElement.disabled) {
            e.preventDefault();
            activeElement.click();
        }
        
        // Handle tab activation
        if (activeElement.getAttribute('role') === 'tab') {
            e.preventDefault();
            this.activateTab(activeElement);
        }
        
        // Handle menu item activation
        if (activeElement.getAttribute('role') === 'menuitem') {
            e.preventDefault();
            activeElement.click();
        }
    }

    /**
     * Handle landmark navigation (Alt + number keys)
     */
    handleLandmarkNavigation(e) {
        const landmarks = [
            'main',
            'nav[role="navigation"], nav',
            'aside',
            'form[role="search"], [role="search"]',
            'footer',
            '[role="banner"], header',
            '[role="complementary"]',
            '[role="contentinfo"]'
        ];

        const index = parseInt(e.key) - 1;
        if (landmarks[index]) {
            const landmark = document.querySelector(landmarks[index]);
            if (landmark) {
                e.preventDefault();
                this.focusElement(landmark);
                this.announce(`Navigated to ${landmark.getAttribute('aria-label') || landmark.tagName.toLowerCase()}`);
            }
        }
    }

    /**
     * Menu navigation with arrow keys
     */
    handleMenuNavigation(e, activeElement) {
        const menu = activeElement.closest('[role="menu"]');
        const menuItems = Array.from(menu.querySelectorAll('[role="menuitem"]'));
        const currentIndex = menuItems.indexOf(activeElement);

        let targetIndex = currentIndex;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                targetIndex = (currentIndex + 1) % menuItems.length;
                break;
            case 'ArrowUp':
                e.preventDefault();
                targetIndex = currentIndex === 0 ? menuItems.length - 1 : currentIndex - 1;
                break;
            case 'Home':
                e.preventDefault();
                targetIndex = 0;
                break;
            case 'End':
                e.preventDefault();
                targetIndex = menuItems.length - 1;
                break;
        }

        if (targetIndex !== currentIndex) {
            menuItems[targetIndex].focus();
        }
    }

    /**
     * Tab navigation with arrow keys
     */
    handleTabNavigation(e, activeElement) {
        const tabList = activeElement.closest('[role="tablist"]');
        const tabs = Array.from(tabList.querySelectorAll('[role="tab"]'));
        const currentIndex = tabs.indexOf(activeElement);

        let targetIndex = currentIndex;

        switch (e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                targetIndex = currentIndex === 0 ? tabs.length - 1 : currentIndex - 1;
                break;
            case 'ArrowRight':
                e.preventDefault();
                targetIndex = (currentIndex + 1) % tabs.length;
                break;
            case 'Home':
                e.preventDefault();
                targetIndex = 0;
                break;
            case 'End':
                e.preventDefault();
                targetIndex = tabs.length - 1;
                break;
        }

        if (targetIndex !== currentIndex) {
            tabs[targetIndex].focus();
        }
    }

    /**
     * Activate tab
     */
    activateTab(tab) {
        const tabList = tab.closest('[role="tablist"]');
        const tabs = tabList.querySelectorAll('[role="tab"]');
        const tabPanels = document.querySelectorAll('[role="tabpanel"]');

        // Deactivate all tabs
        tabs.forEach(t => {
            t.setAttribute('aria-selected', 'false');
            t.classList.remove('active');
        });

        // Hide all tab panels
        tabPanels.forEach(panel => {
            panel.hidden = true;
            panel.setAttribute('aria-hidden', 'true');
        });

        // Activate selected tab
        tab.setAttribute('aria-selected', 'true');
        tab.classList.add('active');

        // Show corresponding panel
        const panelId = tab.getAttribute('aria-controls');
        if (panelId) {
            const panel = document.getElementById(panelId);
            if (panel) {
                panel.hidden = false;
                panel.setAttribute('aria-hidden', 'false');
            }
        }

        this.announce(`${tab.textContent} tab selected`);
    }

    /**
     * Focus management utilities
     */
    focusElement(element) {
        if (!element.hasAttribute('tabindex')) {
            element.setAttribute('tabindex', '-1');
        }
        element.focus();
        
        // Store focus history
        this.focusHistory.push(element);
        if (this.focusHistory.length > 10) {
            this.focusHistory.shift();
        }
    }

    /**
     * Get all focusable elements
     */
    getFocusableElements(container = document) {
        return Array.from(container.querySelectorAll(
            'a[href], button, input, textarea, select, details, [tabindex]:not([tabindex="-1"])'
        )).filter(el => {
            return !el.disabled && 
                   !el.hidden && 
                   el.offsetParent !== null &&
                   window.getComputedStyle(el).visibility !== 'hidden';
        });
    }

    /**
     * Create focus trap
     */
    createFocusTrap(container) {
        if (!this.options.enableFocusTrap) return null;

        const focusableElements = this.getFocusableElements(container);
        if (focusableElements.length === 0) return null;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const trapData = {
            container,
            firstElement,
            lastElement,
            previousActiveElement: document.activeElement
        };

        this.trapStack.push(trapData);
        container.classList.add('focus-trap-active');
        
        // Focus first element
        setTimeout(() => {
            firstElement.focus();
        }, 100);

        this.log('Focus trap created', trapData);
        return trapData;
    }

    /**
     * Manage focus trap
     */
    manageFocusTrap(e) {
        if (this.trapStack.length === 0) return;

        const trapData = this.trapStack[this.trapStack.length - 1];
        const { firstElement, lastElement } = trapData;

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
     * Release focus trap
     */
    releaseFocusTrap() {
        if (this.trapStack.length === 0) return;

        const trapData = this.trapStack.pop();
        trapData.container.classList.remove('focus-trap-active');
        
        // Return focus to previous element
        if (trapData.previousActiveElement && trapData.previousActiveElement.focus) {
            trapData.previousActiveElement.focus();
        }

        this.log('Focus trap released', trapData);
    }

    /**
     * Modal accessibility management
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Set up modal ARIA attributes
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        
        // Set focus to modal
        if (!modal.hasAttribute('tabindex')) {
            modal.setAttribute('tabindex', '-1');
        }

        // Create focus trap
        this.createFocusTrap(modal);
        
        // Update state
        this.currentModalId = modalId;
        document.body.classList.add('modal-open');

        // Announce modal opening
        const modalTitle = modal.querySelector('h1, h2, h3, [role="heading"], .modal-title');
        if (modalTitle) {
            this.announce(`${modalTitle.textContent} dialog opened`);
        }

        this.log('Modal opened', modalId);
    }

    /**
     * Close modal
     */
    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Release focus trap
        this.releaseFocusTrap();
        
        // Update state
        this.currentModalId = null;
        document.body.classList.remove('modal-open');

        // Announce modal closing
        this.announce('Dialog closed');

        this.log('Modal closed', modalId);
    }

    /**
     * Setup high contrast mode detection
     */
    setupHighContrastDetection() {
        // Detect high contrast mode
        const testElement = document.createElement('div');
        testElement.style.position = 'absolute';
        testElement.style.left = '-9999px';
        testElement.style.background = 'red';
        testElement.style.color = 'green';
        document.body.appendChild(testElement);

        const styles = window.getComputedStyle(testElement);
        const isHighContrast = styles.backgroundColor === styles.color;

        document.body.removeChild(testElement);

        if (isHighContrast) {
            document.body.classList.add('high-contrast-mode');
            this.announce('High contrast mode detected');
        }
    }

    /**
     * Setup reduced motion detection
     */
    setupReducedMotionDetection() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        
        const handleReducedMotionChange = (e) => {
            if (e.matches) {
                document.body.classList.add('reduced-motion');
                this.announce('Reduced motion preference detected');
            } else {
                document.body.classList.remove('reduced-motion');
            }
        };

        // Initial check
        handleReducedMotionChange(prefersReducedMotion);
        
        // Listen for changes
        prefersReducedMotion.addEventListener('change', handleReducedMotionChange);
    }

    /**
     * Enhance existing elements with accessibility features
     */
    enhanceExistingElements() {
        // Enhance buttons without proper labels
        document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])').forEach(button => {
            if (!button.textContent.trim()) {
                button.setAttribute('aria-label', 'Button');
            }
        });

        // Enhance form inputs without labels
        document.querySelectorAll('input:not([aria-label]):not([aria-labelledby])').forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (!label && !input.placeholder) {
                input.setAttribute('aria-label', input.name || input.type || 'Input field');
            }
        });

        // Enhance images without alt text
        document.querySelectorAll('img:not([alt])').forEach(img => {
            img.setAttribute('alt', '');
        });

        // Enhance links without text content
        document.querySelectorAll('a:empty, a:not([aria-label]):not([aria-labelledby])').forEach(link => {
            if (!link.textContent.trim()) {
                const href = link.getAttribute('href');
                if (href) {
                    link.setAttribute('aria-label', `Link to ${href}`);
                }
            }
        });

        // Enhance custom interactive elements
        document.querySelectorAll('[role="button"], [role="link"], [role="menuitem"]').forEach(element => {
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }
        });
    }

    /**
     * Observe DOM changes and enhance new elements
     */
    observeDOMChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        this.enhanceElement(node);
                        
                        // Enhance child elements
                        const childElements = node.querySelectorAll('*');
                        childElements.forEach(child => this.enhanceElement(child));
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Enhance individual element
     */
    enhanceElement(element) {
        // Add ARIA labels to buttons without them
        if (element.tagName === 'BUTTON' && 
            !element.getAttribute('aria-label') && 
            !element.getAttribute('aria-labelledby') &&
            !element.textContent.trim()) {
            element.setAttribute('aria-label', 'Button');
        }

        // Add alt attributes to images
        if (element.tagName === 'IMG' && !element.hasAttribute('alt')) {
            element.setAttribute('alt', '');
        }

        // Make custom interactive elements focusable
        if (element.hasAttribute('role') && 
            ['button', 'link', 'menuitem', 'tab'].includes(element.getAttribute('role')) &&
            !element.hasAttribute('tabindex')) {
            element.setAttribute('tabindex', '0');
        }
    }

    /**
     * Setup debug mode
     */
    setupDebugMode() {
        console.log('Accessibility Debug Mode Enabled');
        
        // Add visual indicators for accessibility features
        const style = document.createElement('style');
        style.textContent = `
            .accessibility-debug [aria-label]::after {
                content: " [" attr(aria-label) "]";
                background: yellow;
                color: black;
                font-size: 12px;
                padding: 2px;
            }
            
            .accessibility-debug [role]::before {
                content: "ROLE: " attr(role);
                background: blue;
                color: white;
                font-size: 10px;
                padding: 1px 3px;
                margin-right: 5px;
            }
        `;
        document.head.appendChild(style);
        document.body.classList.add('accessibility-debug');

        // Log focus changes
        document.addEventListener('focusin', (e) => {
            console.log('Focus:', e.target, {
                tagName: e.target.tagName,
                role: e.target.getAttribute('role'),
                ariaLabel: e.target.getAttribute('aria-label'),
                textContent: e.target.textContent?.substring(0, 50)
            });
        });
    }

    /**
     * Public API methods
     */
    
    // Programmatically announce message
    say(message, priority = 'polite') {
        this.announce(message, priority);
    }

    // Focus management
    focus(selector) {
        const element = document.querySelector(selector);
        if (element) {
            this.focusElement(element);
        }
    }

    // Modal management
    modal(action, modalId) {
        if (action === 'open') {
            this.openModal(modalId);
        } else if (action === 'close') {
            this.closeModal(modalId);
        }
    }

    // Get accessibility status
    getStatus() {
        return {
            keyboardUser: this.keyboardUser,
            highContrast: document.body.classList.contains('high-contrast-mode'),
            reducedMotion: document.body.classList.contains('reduced-motion'),
            focusTrapActive: this.trapStack.length > 0,
            modalOpen: this.currentModalId !== null
        };
    }

    /**
     * Utility methods
     */
    log(...args) {
        if (this.options.debugMode) {
            console.log('[A11Y]', ...args);
        }
    }

    error(...args) {
        console.error('[A11Y Error]', ...args);
    }
}

// Auto-initialize if not in module environment
if (typeof window !== 'undefined') {
    window.AccessibilityManager = AccessibilityManager;
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.a11y = new AccessibilityManager();
        });
    } else {
        window.a11y = new AccessibilityManager();
    }

    // Alpine.js integration
    if (window.Alpine) {
        document.addEventListener('alpine:init', () => {
            Alpine.data('accessibility', () => ({
                announce: (message, priority = 'polite') => window.a11y?.announce(message, priority),
                focus: (selector) => window.a11y?.focus(selector),
                openModal: (id) => window.a11y?.openModal(id),
                closeModal: (id) => window.a11y?.closeModal(id),
                getStatus: () => window.a11y?.getStatus() || {}
            }));
        });
    }
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityManager;
}
