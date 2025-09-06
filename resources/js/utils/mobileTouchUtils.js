/**
 * Mobile Touch Utilities for HD Tickets Sports Events Platform
 * Comprehensive touch interaction and mobile-specific functionality
 * 
 * Features:
 * - Touch gesture detection (swipe, pinch, tap)
 * - Mobile-friendly interactions
 * - Drag and drop for mobile
 * - Pull-to-refresh functionality
 * - Touch feedback and haptics
 * - Mobile keyboard handling
 * - Viewport management
 */

(function() {
    'use strict';

    /**
     * Main mobile touch utilities class
     */
    class MobileTouchUtils {
        constructor() {
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchEndX = 0;
            this.touchEndY = 0;
            this.touchStartTime = 0;
            this.touchEndTime = 0;
            this.isTouch = false;
            this.activeElement = null;
            this.swipeThreshold = 50; // Minimum distance for swipe
            this.tapThreshold = 10; // Maximum movement for tap
            this.longPressDelay = 500; // Long press duration
            this.doubleTapDelay = 300; // Double tap max interval
            
            // Gesture states
            this.gestureState = {
                isSwipeActive: false,
                isPinchActive: false,
                isLongPressActive: false,
                lastTap: 0,
                tapCount: 0
            };

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }

        /**
         * Initialize all mobile touch utilities
         */
        init() {
            this.detectTouchDevice();
            this.setupGlobalTouchEvents();
            this.setupViewportManager();
            this.setupKeyboardHandling();
            this.setupPullToRefresh();
            this.setupTouchFeedback();
            this.setupAccessibilityEnhancements();
            this.initializeSwipeableElements();
            this.initializeMobileModals();
            
            console.log('🏈 HD Tickets Mobile Touch Utils initialized');
        }

        /**
         * Detect if device supports touch
         */
        detectTouchDevice() {
            this.isTouch = (
                'ontouchstart' in window ||
                navigator.maxTouchPoints > 0 ||
                navigator.msMaxTouchPoints > 0
            );

            if (this.isTouch) {
                document.body.classList.add('touch-device');
            } else {
                document.body.classList.add('no-touch-device');
            }

            // Add mobile class for smaller screens
            if (window.innerWidth <= 768) {
                document.body.classList.add('mobile-device');
            }
        }

        /**
         * Setup global touch event listeners
         */
        setupGlobalTouchEvents() {
            // Touch start
            document.addEventListener('touchstart', (e) => {
                this.handleTouchStart(e);
            }, { passive: false });

            // Touch move
            document.addEventListener('touchmove', (e) => {
                this.handleTouchMove(e);
            }, { passive: false });

            // Touch end
            document.addEventListener('touchend', (e) => {
                this.handleTouchEnd(e);
            }, { passive: false });

            // Prevent context menu on long press
            document.addEventListener('contextmenu', (e) => {
                if (this.isTouch && this.gestureState.isLongPressActive) {
                    e.preventDefault();
                }
            });

            // Handle orientation change
            window.addEventListener('orientationchange', () => {
                setTimeout(() => this.handleOrientationChange(), 100);
            });

            // Handle resize
            window.addEventListener('resize', () => {
                this.debounce(() => this.handleResize(), 250)();
            });
        }

        /**
         * Handle touch start events
         */
        handleTouchStart(e) {
            const touch = e.touches[0];
            this.touchStartX = touch.clientX;
            this.touchStartY = touch.clientY;
            this.touchStartTime = Date.now();
            this.activeElement = e.target;

            // Add touch active class
            if (this.activeElement.classList) {
                this.activeElement.classList.add('touch-active');
            }

            // Setup long press detection
            this.setupLongPress();
            
            // Handle double tap detection
            this.handleDoubleTap();

            // Emit custom event
            this.emitCustomEvent('touchstart-custom', {
                element: this.activeElement,
                position: { x: this.touchStartX, y: this.touchStartY }
            });
        }

        /**
         * Handle touch move events
         */
        handleTouchMove(e) {
            if (!this.activeElement) return;

            const touch = e.touches[0];
            const deltaX = Math.abs(touch.clientX - this.touchStartX);
            const deltaY = Math.abs(touch.clientY - this.touchStartY);

            // Cancel long press if moved too much
            if (deltaX > this.tapThreshold || deltaY > this.tapThreshold) {
                this.cancelLongPress();
            }

            // Handle swipe gesture
            this.handleSwipeGesture(touch);

            // Handle pinch gesture (multi-touch)
            if (e.touches.length === 2) {
                this.handlePinchGesture(e.touches);
            }

            // Prevent scroll on certain elements
            if (this.shouldPreventScroll(e.target)) {
                e.preventDefault();
            }
        }

        /**
         * Handle touch end events
         */
        handleTouchEnd(e) {
            this.touchEndTime = Date.now();
            const duration = this.touchEndTime - this.touchStartTime;

            // Remove touch active class
            if (this.activeElement && this.activeElement.classList) {
                this.activeElement.classList.remove('touch-active');
            }

            // Determine gesture type
            this.processGesture(duration);

            // Clear states
            this.clearTouchState();

            // Emit custom event
            this.emitCustomEvent('touchend-custom', {
                element: this.activeElement,
                duration: duration
            });
        }

        /**
         * Process the completed gesture
         */
        processGesture(duration) {
            const deltaX = this.touchEndX - this.touchStartX;
            const deltaY = this.touchEndY - this.touchStartY;
            const absX = Math.abs(deltaX);
            const absY = Math.abs(deltaY);

            // Determine if it's a swipe
            if (absX > this.swipeThreshold || absY > this.swipeThreshold) {
                let direction;
                if (absX > absY) {
                    direction = deltaX > 0 ? 'right' : 'left';
                } else {
                    direction = deltaY > 0 ? 'down' : 'up';
                }
                
                this.handleSwipe(direction, { deltaX, deltaY, duration });
            } 
            // Or if it's a tap
            else if (absX < this.tapThreshold && absY < this.tapThreshold && duration < 300) {
                this.handleTap();
            }
        }

        /**
         * Handle swipe gestures
         */
        handleSwipe(direction, data) {
            this.emitCustomEvent('swipe', {
                direction,
                element: this.activeElement,
                data
            });

            // Handle specific swipe actions
            if (this.activeElement) {
                const swipeHandler = this.activeElement.dataset.swipeHandler;
                if (swipeHandler) {
                    this.executeSwipeHandler(swipeHandler, direction, data);
                }
            }

            console.log(`Swipe detected: ${direction}`, data);
        }

        /**
         * Handle tap gestures
         */
        handleTap() {
            this.gestureState.tapCount++;
            this.gestureState.lastTap = Date.now();

            // Add visual feedback
            this.addTapFeedback();

            this.emitCustomEvent('tap', {
                element: this.activeElement,
                count: this.gestureState.tapCount
            });

            // Reset tap count after delay
            setTimeout(() => {
                this.gestureState.tapCount = 0;
            }, this.doubleTapDelay);
        }

        /**
         * Handle long press setup
         */
        setupLongPress() {
            this.longPressTimeout = setTimeout(() => {
                this.gestureState.isLongPressActive = true;
                this.handleLongPress();
            }, this.longPressDelay);
        }

        /**
         * Handle long press gesture
         */
        handleLongPress() {
            // Add haptic feedback if available
            this.triggerHapticFeedback('medium');

            this.emitCustomEvent('longpress', {
                element: this.activeElement,
                position: { x: this.touchStartX, y: this.touchStartY }
            });

            // Visual feedback
            if (this.activeElement && this.activeElement.classList) {
                this.activeElement.classList.add('long-press-active');
                setTimeout(() => {
                    this.activeElement.classList.remove('long-press-active');
                }, 200);
            }

            console.log('Long press detected on:', this.activeElement);
        }

        /**
         * Cancel long press
         */
        cancelLongPress() {
            if (this.longPressTimeout) {
                clearTimeout(this.longPressTimeout);
                this.longPressTimeout = null;
            }
            this.gestureState.isLongPressActive = false;
        }

        /**
         * Handle double tap detection
         */
        handleDoubleTap() {
            const now = Date.now();
            if (now - this.gestureState.lastTap < this.doubleTapDelay) {
                this.emitCustomEvent('doubletap', {
                    element: this.activeElement
                });
                
                // Prevent zoom on double tap
                if (this.activeElement) {
                    this.preventZoom();
                }
            }
        }

        /**
         * Setup viewport management
         */
        setupViewportManager() {
            // Set viewport height custom property
            this.updateViewportHeight();

            // Handle viewport changes
            window.addEventListener('resize', () => {
                this.debounce(() => this.updateViewportHeight(), 100)();
            });

            // Handle virtual keyboard
            this.handleVirtualKeyboard();
        }

        /**
         * Update viewport height for mobile
         */
        updateViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        /**
         * Handle virtual keyboard appearance
         */
        handleVirtualKeyboard() {
            let initialViewportHeight = window.innerHeight;

            window.addEventListener('resize', () => {
                const currentViewportHeight = window.innerHeight;
                const heightDifference = initialViewportHeight - currentViewportHeight;

                if (heightDifference > 150) { // Keyboard is likely open
                    document.body.classList.add('keyboard-open');
                    this.emitCustomEvent('keyboard-open', { heightDifference });
                } else {
                    document.body.classList.remove('keyboard-open');
                    this.emitCustomEvent('keyboard-closed');
                }
            });
        }

        /**
         * Setup keyboard handling
         */
        setupKeyboardHandling() {
            // Handle input focus
            document.addEventListener('focusin', (e) => {
                if (this.isFormInput(e.target)) {
                    this.handleInputFocus(e.target);
                }
            });

            // Handle input blur
            document.addEventListener('focusout', (e) => {
                if (this.isFormInput(e.target)) {
                    this.handleInputBlur(e.target);
                }
            });
        }

        /**
         * Check if element is a form input
         */
        isFormInput(element) {
            const inputTypes = ['INPUT', 'TEXTAREA', 'SELECT'];
            return inputTypes.includes(element.tagName);
        }

        /**
         * Handle input focus
         */
        handleInputFocus(input) {
            // Scroll to input if needed
            setTimeout(() => {
                input.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 300); // Wait for keyboard animation

            // Add focused class
            input.classList.add('mobile-focused');
        }

        /**
         * Handle input blur
         */
        handleInputBlur(input) {
            input.classList.remove('mobile-focused');
        }

        /**
         * Setup pull to refresh
         */
        setupPullToRefresh() {
            const pullToRefreshElements = document.querySelectorAll('[data-pull-to-refresh]');
            
            pullToRefreshElements.forEach(element => {
                this.initializePullToRefresh(element);
            });
        }

        /**
         * Initialize pull to refresh for an element
         */
        initializePullToRefresh(element) {
            let startY = 0;
            let currentY = 0;
            let isPulling = false;
            let canRefresh = false;

            element.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
            });

            element.addEventListener('touchmove', (e) => {
                currentY = e.touches[0].clientY;
                const pullDistance = currentY - startY;

                if (element.scrollTop === 0 && pullDistance > 0) {
                    isPulling = true;
                    canRefresh = pullDistance > 60;
                    
                    element.style.transform = `translateY(${Math.min(pullDistance * 0.5, 60)}px)`;
                    element.classList.toggle('can-refresh', canRefresh);
                    
                    e.preventDefault();
                }
            });

            element.addEventListener('touchend', () => {
                if (isPulling) {
                    element.style.transform = '';
                    element.classList.remove('can-refresh');

                    if (canRefresh) {
                        this.triggerRefresh(element);
                    }

                    isPulling = false;
                    canRefresh = false;
                }
            });
        }

        /**
         * Trigger refresh action
         */
        triggerRefresh(element) {
            const refreshCallback = element.dataset.pullToRefresh;
            
            if (refreshCallback && window[refreshCallback]) {
                window[refreshCallback]();
            }

            this.emitCustomEvent('pull-to-refresh', { element });
        }

        /**
         * Setup touch feedback
         */
        setupTouchFeedback() {
            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('button, [role="button"], .btn');
            
            buttons.forEach(button => {
                button.addEventListener('touchstart', (e) => {
                    this.addRippleEffect(button, e);
                });
            });
        }

        /**
         * Add ripple effect
         */
        addRippleEffect(element, event) {
            const rect = element.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.touches[0].clientX - rect.left - size / 2;
            const y = event.touches[0].clientY - rect.top - size / 2;

            const ripple = document.createElement('div');
            ripple.className = 'ripple-effect';
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
                z-index: 1000;
            `;

            // Ensure button has relative position
            const buttonPosition = getComputedStyle(element).position;
            if (buttonPosition === 'static') {
                element.style.position = 'relative';
            }

            element.appendChild(ripple);

            // Remove ripple after animation
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        }

        /**
         * Add tap feedback
         */
        addTapFeedback() {
            if (this.activeElement) {
                this.activeElement.classList.add('tap-feedback');
                setTimeout(() => {
                    if (this.activeElement) {
                        this.activeElement.classList.remove('tap-feedback');
                    }
                }, 150);
            }
        }

        /**
         * Trigger haptic feedback
         */
        triggerHapticFeedback(type = 'light') {
            if (navigator.vibrate) {
                const patterns = {
                    light: [10],
                    medium: [20],
                    heavy: [30],
                    double: [10, 10, 10]
                };
                
                navigator.vibrate(patterns[type] || patterns.light);
            }
        }

        /**
         * Setup swipeable elements
         */
        initializeSwipeableElements() {
            const swipeableElements = document.querySelectorAll('[data-swipeable]');
            
            swipeableElements.forEach(element => {
                this.makeElementSwipeable(element);
            });
        }

        /**
         * Make element swipeable
         */
        makeElementSwipeable(element) {
            let startX = 0;
            let currentX = 0;
            let isSwipingX = false;

            element.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                element.style.transition = '';
            });

            element.addEventListener('touchmove', (e) => {
                currentX = e.touches[0].clientX;
                const deltaX = currentX - startX;

                if (Math.abs(deltaX) > 10) {
                    isSwipingX = true;
                    element.style.transform = `translateX(${deltaX}px)`;
                    e.preventDefault();
                }
            });

            element.addEventListener('touchend', () => {
                if (isSwipingX) {
                    const deltaX = currentX - startX;
                    element.style.transition = 'transform 0.3s ease';

                    if (Math.abs(deltaX) > element.offsetWidth * 0.3) {
                        // Complete the swipe
                        const direction = deltaX > 0 ? 1 : -1;
                        element.style.transform = `translateX(${direction * element.offsetWidth}px)`;
                        
                        setTimeout(() => {
                            this.emitCustomEvent('swipe-complete', {
                                element,
                                direction: deltaX > 0 ? 'right' : 'left'
                            });
                        }, 300);
                    } else {
                        // Snap back
                        element.style.transform = 'translateX(0)';
                    }

                    isSwipingX = false;
                }
            });
        }

        /**
         * Initialize mobile modals
         */
        initializeMobileModals() {
            const modals = document.querySelectorAll('.modal, [data-modal]');
            
            modals.forEach(modal => {
                this.enhanceModalForMobile(modal);
            });
        }

        /**
         * Enhance modal for mobile
         */
        enhanceModalForMobile(modal) {
            // Add swipe to close
            let startY = 0;
            let currentY = 0;

            modal.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
            });

            modal.addEventListener('touchmove', (e) => {
                currentY = e.touches[0].clientY;
                const deltaY = currentY - startY;

                if (deltaY > 0) {
                    modal.style.transform = `translateY(${deltaY}px)`;
                }
            });

            modal.addEventListener('touchend', () => {
                const deltaY = currentY - startY;
                
                if (deltaY > 100) {
                    // Close modal
                    this.closeModal(modal);
                } else {
                    // Snap back
                    modal.style.transform = '';
                }
            });
        }

        /**
         * Close modal
         */
        closeModal(modal) {
            const closeEvent = new CustomEvent('modal-close', { 
                bubbles: true,
                detail: { modal }
            });
            modal.dispatchEvent(closeEvent);
        }

        /**
         * Setup accessibility enhancements
         */
        setupAccessibilityEnhancements() {
            // Improve focus management
            this.setupFocusManagement();
            
            // Add touch labels
            this.addTouchLabels();
        }

        /**
         * Setup focus management
         */
        setupFocusManagement() {
            // Track focus for better touch interaction
            let lastFocusedElement = null;

            document.addEventListener('focusin', (e) => {
                lastFocusedElement = e.target;
            });

            // Return focus after touch interaction
            document.addEventListener('touchend', () => {
                if (lastFocusedElement && document.activeElement === document.body) {
                    lastFocusedElement.focus();
                }
            });
        }

        /**
         * Add touch labels for accessibility
         */
        addTouchLabels() {
            const touchElements = document.querySelectorAll('[data-touch-label]');
            
            touchElements.forEach(element => {
                const label = element.dataset.touchLabel;
                element.setAttribute('aria-label', label);
            });
        }

        /**
         * Handle orientation change
         */
        handleOrientationChange() {
            // Update viewport height
            this.updateViewportHeight();
            
            // Emit event
            this.emitCustomEvent('orientation-change', {
                orientation: screen.orientation?.angle || window.orientation
            });

            // Force layout recalculation
            document.body.style.display = 'none';
            document.body.offsetHeight; // Trigger reflow
            document.body.style.display = '';
        }

        /**
         * Handle resize
         */
        handleResize() {
            const width = window.innerWidth;
            
            // Update mobile class
            if (width <= 768) {
                document.body.classList.add('mobile-device');
                document.body.classList.remove('tablet-device', 'desktop-device');
            } else if (width <= 1024) {
                document.body.classList.add('tablet-device');
                document.body.classList.remove('mobile-device', 'desktop-device');
            } else {
                document.body.classList.add('desktop-device');
                document.body.classList.remove('mobile-device', 'tablet-device');
            }

            this.updateViewportHeight();
        }

        /**
         * Prevent zoom on specific actions
         */
        preventZoom() {
            const viewport = document.querySelector('meta[name="viewport"]');
            if (viewport) {
                const originalContent = viewport.content;
                viewport.content = originalContent + ', user-scalable=no';
                
                setTimeout(() => {
                    viewport.content = originalContent;
                }, 500);
            }
        }

        /**
         * Should prevent scroll for element
         */
        shouldPreventScroll(element) {
            return element.dataset.preventScroll === 'true' ||
                   element.classList.contains('prevent-scroll') ||
                   element.closest('.prevent-scroll');
        }

        /**
         * Execute swipe handler
         */
        executeSwipeHandler(handlerName, direction, data) {
            if (window[handlerName]) {
                window[handlerName](direction, data);
            }
        }

        /**
         * Clear touch state
         */
        clearTouchState() {
            this.activeElement = null;
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchEndX = 0;
            this.touchEndY = 0;
            this.cancelLongPress();
        }

        /**
         * Emit custom event
         */
        emitCustomEvent(eventName, detail = {}) {
            const event = new CustomEvent(eventName, {
                bubbles: true,
                detail: detail
            });
            
            const target = detail.element || document;
            target.dispatchEvent(event);
        }

        /**
         * Debounce function
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        /**
         * Show mobile notification
         */
        showMobileNotification(message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            notification.className = `mobile-notification mobile-notification--${type}`;
            notification.textContent = message;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: ${this.getNotificationColor(type)};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 10000;
                opacity: 0;
                transition: all 0.3s ease;
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(-50%) translateY(0)';
            }, 10);

            // Remove after duration
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(-50%) translateY(-20px)';
                
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, duration);
        }

        /**
         * Get notification color by type
         */
        getNotificationColor(type) {
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };
            return colors[type] || colors.info;
        }

        /**
         * Initialize all features (public method)
         */
        initializeAllFeatures() {
            // Re-scan for new elements
            this.setupPullToRefresh();
            this.initializeSwipeableElements();
            this.initializeMobileModals();
            this.setupTouchFeedback();
            
            console.log('🔄 Mobile touch features re-initialized');
        }
    }

    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple-animation {
            from {
                transform: scale(0);
                opacity: 1;
            }
            to {
                transform: scale(2);
                opacity: 0;
            }
        }

        .touch-active {
            transform: scale(0.98);
            transition: transform 0.1s ease;
        }

        .tap-feedback {
            background-color: rgba(59, 130, 246, 0.1) !important;
            transition: background-color 0.15s ease;
        }

        .long-press-active {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .keyboard-open {
            --vh: calc(var(--original-vh, 1vh) * 0.75);
        }

        .mobile-focused {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }

        .can-refresh {
            background: linear-gradient(to bottom, #f0f9ff 0%, transparent 50px);
        }
    `;
    
    document.head.appendChild(style);

    // Initialize and expose globally
    const mobileTouchUtils = new MobileTouchUtils();
    
    // Make available globally
    window.mobileTouchUtils = mobileTouchUtils;

    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = MobileTouchUtils;
    }

})();
