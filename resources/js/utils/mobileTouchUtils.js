/**
 * Mobile Touch Utilities
 * Handles touch interactions, gestures, and mobile-specific optimizations
 */

class MobileTouchUtils {
    constructor(config = {}) {
        // Configuration
        this.config = {
            enableSwipe: true,
            enablePinch: true,
            enableDoubleTap: true,
            enableLongPress: true,
            enablePullToRefresh: false,
            hapticFeedback: true,
            visualFeedback: true,
            preventBounce: true,
            swipeThreshold: 30,
            longPressDelay: 500,
            doubleTapDelay: 300,
            ...config
        };

        // Device capabilities
        this.capabilities = {
            hasTouch: this.detectTouchSupport(),
            hasHaptic: 'vibrate' in navigator,
            isMobile: /Mobi|Android/i.test(navigator.userAgent),
            isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent),
            isAndroid: /Android/.test(navigator.userAgent),
            supportsPointerEvents: 'PointerEvent' in window,
            supportsPassiveListeners: this.detectPassiveSupport()
        };

        // Touch state tracking
        this.touchState = {
            isActive: false,
            startX: 0,
            startY: 0,
            currentX: 0,
            currentY: 0,
            deltaX: 0,
            deltaY: 0,
            startTime: 0,
            lastTapTime: 0,
            touches: 0
        };

        // Event handlers storage
        this.handlers = new Map();
        this.swipeHandlers = new Map();
        this.longPressTimer = null;

        // Initialize if touch is supported
        if (this.capabilities.hasTouch) {
            this.init();
        }
    }

    /**
     * Initialize touch utilities
     */
    init() {
        this.setupGlobalTouchHandlers();
        this.setupHapticFeedback();
        this.setupVisualFeedback();
        this.setupKeyboardDetection();
        this.setupOrientationHandling();
        this.injectMobileStyles();
        
        if (process.env.NODE_ENV === 'development') {
            console.log('Mobile Touch Utils initialized with capabilities:', this.capabilities);
        }
    }

    /**
     * Detect touch support
     */
    detectTouchSupport() {
        return ('ontouchstart' in window) ||
               (navigator.maxTouchPoints > 0) ||
               (navigator.msMaxTouchPoints > 0);
    }

    /**
     * Detect passive event listener support
     */
    detectPassiveSupport() {
        let supportsPassive = false;
        try {
            const opts = Object.defineProperty({}, 'passive', {
                get: function() {
                    supportsPassive = true;
                    return supportsPassive;
                }
            });
            window.addEventListener('testPassive', null, opts);
            window.removeEventListener('testPassive', null, opts);
        } catch (_e) {
            // Passive not supported
        }
        return supportsPassive;
    }

    /**
     * Setup global touch event handlers
     */
    setupGlobalTouchHandlers() {
        const options = this.capabilities.supportsPassiveListeners ? { passive: false } : false;

        document.addEventListener('touchstart', this.handleTouchStart.bind(this), options);
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), options);
        document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
        document.addEventListener('touchcancel', this.handleTouchCancel.bind(this), { passive: true });

        // Prevent default touch behaviors where needed
        if (this.config.preventBounce) {
            document.addEventListener('touchmove', this.preventBounce.bind(this), { passive: false });
        }
    }

    /**
     * Handle touch start events
     */
    handleTouchStart(e) {
        if (!this.config.enableSwipe && !this.config.enableLongPress && !this.config.enableDoubleTap) {
            return;
        }

        const touch = e.touches[0];
        const now = Date.now();

        this.touchState = {
            isActive: true,
            startX: touch.clientX,
            startY: touch.clientY,
            currentX: touch.clientX,
            currentY: touch.clientY,
            deltaX: 0,
            deltaY: 0,
            startTime: now,
            touches: e.touches.length
        };

        // Handle double tap
        if (this.config.enableDoubleTap) {
            const timeSinceLastTap = now - this.touchState.lastTapTime;
            if (timeSinceLastTap < this.config.doubleTapDelay) {
                this.handleDoubleTap(e, touch);
                return;
            }
            this.touchState.lastTapTime = now;
        }

        // Start long press timer
        if (this.config.enableLongPress) {
            this.longPressTimer = setTimeout(() => {
                this.handleLongPress(e, touch);
            }, this.config.longPressDelay);
        }

        // Add visual feedback
        if (this.config.visualFeedback) {
            this.addTouchFeedback(e.target);
        }
    }

    /**
     * Handle touch move events
     */
    handleTouchMove(e) {
        if (!this.touchState.isActive) return;

        const touch = e.touches[0];
        this.touchState.currentX = touch.clientX;
        this.touchState.currentY = touch.clientY;
        this.touchState.deltaX = touch.clientX - this.touchState.startX;
        this.touchState.deltaY = touch.clientY - this.touchState.startY;

        // Cancel long press if finger moves too much
        const distance = Math.sqrt(
            this.touchState.deltaX ** 2 + this.touchState.deltaY ** 2
        );

        if (distance > 10 && this.longPressTimer) {
            clearTimeout(this.longPressTimer);
            this.longPressTimer = null;
        }

        // Handle swipe detection
        if (this.config.enableSwipe && distance > this.config.swipeThreshold) {
            this.handleSwipeMove(e);
        }
    }

    /**
     * Handle touch end events
     */
    handleTouchEnd(e) {
        if (!this.touchState.isActive) return;

        // Clear long press timer
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer);
            this.longPressTimer = null;
        }

        // Handle swipe end
        if (this.config.enableSwipe) {
            this.handleSwipeEnd(e);
        }

        // Remove visual feedback
        if (this.config.visualFeedback) {
            this.removeTouchFeedback(e.target);
        }

        this.touchState.isActive = false;
    }

    /**
     * Handle touch cancel events
     */
    handleTouchCancel(_e) {
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer);
            this.longPressTimer = null;
        }
        this.touchState.isActive = false;
    }

    /**
     * Handle swipe movement
     */
    handleSwipeMove(e) {
        const direction = this.getSwipeDirection();
        const distance = Math.abs(this.touchState.deltaX) + Math.abs(this.touchState.deltaY);
        
        // Emit swipe move event
        this.emitCustomEvent(e.target, 'swipemove', {
            direction,
            distance,
            deltaX: this.touchState.deltaX,
            deltaY: this.touchState.deltaY
        });
    }

    /**
     * Handle swipe end
     */
    handleSwipeEnd(e) {
        const distance = Math.sqrt(
            this.touchState.deltaX ** 2 + this.touchState.deltaY ** 2
        );

        if (distance > this.config.swipeThreshold) {
            const direction = this.getSwipeDirection();
            const velocity = distance / (Date.now() - this.touchState.startTime);

            this.emitCustomEvent(e.target, 'swipe', {
                direction,
                distance,
                velocity,
                deltaX: this.touchState.deltaX,
                deltaY: this.touchState.deltaY
            });

            // Emit directional swipe events
            this.emitCustomEvent(e.target, `swipe${direction}`, {
                distance,
                velocity,
                deltaX: this.touchState.deltaX,
                deltaY: this.touchState.deltaY
            });
        }
    }

    /**
     * Handle double tap
     */
    handleDoubleTap(e, touch) {
        this.emitCustomEvent(e.target, 'doubletap', {
            x: touch.clientX,
            y: touch.clientY
        });

        if (this.config.hapticFeedback) {
            this.triggerHapticFeedback('medium');
        }
    }

    /**
     * Handle long press
     */
    handleLongPress(e, touch) {
        this.emitCustomEvent(e.target, 'longpress', {
            x: touch.clientX,
            y: touch.clientY
        });

        if (this.config.hapticFeedback) {
            this.triggerHapticFeedback('heavy');
        }
    }

    /**
     * Get swipe direction
     */
    getSwipeDirection() {
        const { deltaX, deltaY } = this.touchState;
        
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            return deltaX > 0 ? 'right' : 'left';
        } else {
            return deltaY > 0 ? 'down' : 'up';
        }
    }

    /**
     * Emit custom touch events
     */
    emitCustomEvent(element, eventName, detail) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true,
            cancelable: true
        });
        element.dispatchEvent(event);
    }

    /**
     * Add touch feedback visual effects
     */
    addTouchFeedback(element) {
        if (!element || !this.config.visualFeedback) return;

        element.classList.add('touch-active');
        
        // Add ripple effect for buttons
        if (element.matches('button, .btn, [role="button"]')) {
            element.classList.add('touch-ripple');
            setTimeout(() => element.classList.add('ripple-active'), 10);
        }
    }

    /**
     * Remove touch feedback visual effects
     */
    removeTouchFeedback(element) {
        if (!element) return;

        setTimeout(() => {
            element.classList.remove('touch-active', 'touch-ripple', 'ripple-active');
        }, 150);
    }

    /**
     * Trigger haptic feedback
     */
    triggerHapticFeedback(intensity = 'light') {
        if (!this.capabilities.hasHaptic || !this.config.hapticFeedback) return;

        const patterns = {
            light: 10,
            medium: [10, 10, 10],
            heavy: [20, 10, 20]
        };

        try {
            navigator.vibrate(patterns[intensity] || patterns.light);
        } catch (_error) {
            // Silently handle vibration errors
        }
    }

    /**
     * Setup haptic feedback
     */
    setupHapticFeedback() {
        if (!this.config.hapticFeedback || !this.capabilities.hasHaptic) return;

        // Add haptic feedback to interactive elements
        document.addEventListener('click', (e) => {
            if (e.target.matches('button, .btn, [role="button"], input[type="submit"], input[type="button"]')) {
                this.triggerHapticFeedback('light');
            }
        }, true);
    }

    /**
     * Setup visual feedback styles
     */
    setupVisualFeedback() {
        if (!this.config.visualFeedback) return;

        const styleId = 'mobile-touch-feedback-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .touch-active {
                background-color: rgba(0, 0, 0, 0.05) !important;
                transform: scale(0.98) !important;
                transition: all 0.1s ease-out !important;
            }
            
            .touch-ripple {
                position: relative !important;
                overflow: hidden !important;
            }
            
            .touch-ripple::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
                pointer-events: none;
                z-index: 1;
            }
            
            .touch-ripple.ripple-active::before {
                width: 300px;
                height: 300px;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup keyboard detection for better mobile UX
     */
    setupKeyboardDetection() {
        let initialViewportHeight = window.innerHeight;

        window.addEventListener('resize', () => {
            const currentHeight = window.innerHeight;
            const heightDifference = initialViewportHeight - currentHeight;

            if (heightDifference > 150) {
                document.body.classList.add('keyboard-open');
            } else {
                document.body.classList.remove('keyboard-open');
            }
        });
    }

    /**
     * Setup orientation change handling
     */
    setupOrientationHandling() {
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                // Trigger resize to recalculate layouts
                window.dispatchEvent(new Event('resize'));
            }, 100);
        });
    }

    /**
     * Prevent bounce scrolling
     */
    preventBounce(e) {
        if (!this.config.preventBounce) return;
        
        const target = e.target.closest('[data-prevent-scroll]');
        if (target) {
            e.preventDefault();
        }
    }

    /**
     * Inject mobile-specific styles
     */
    injectMobileStyles() {
        const styleId = 'mobile-touch-utils-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            /* Touch optimization */
            * {
                -webkit-touch-callout: none;
                -webkit-tap-highlight-color: transparent;
            }
            
            /* Better touch targets */
            button, .btn, [role="button"] {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* Keyboard open styles */
            body.keyboard-open {
                position: fixed;
                width: 100%;
            }
            
            /* iOS specific fixes */
            @supports (-webkit-touch-callout: none) {
                input[type="text"], input[type="email"], input[type="password"], textarea {
                    font-size: 16px !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Register swipe handler for element
     */
    registerSwipeHandler(element, callback, direction = null) {
        if (!element) return () => {};

        const handler = (e) => {
            if (!direction || e.detail.direction === direction) {
                callback(e);
            }
        };

        element.addEventListener('swipe', handler);
        
        // Store handler for cleanup
        if (!this.swipeHandlers.has(element)) {
            this.swipeHandlers.set(element, []);
        }
        this.swipeHandlers.get(element).push({ handler, direction });

        return () => {
            element.removeEventListener('swipe', handler);
        };
    }

    /**
     * Cleanup method
     */
    destroy() {
        // Clear timers
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer);
        }

        // Remove event listeners
        document.removeEventListener('touchstart', this.handleTouchStart.bind(this));
        document.removeEventListener('touchmove', this.handleTouchMove.bind(this));
        document.removeEventListener('touchend', this.handleTouchEnd.bind(this));
        document.removeEventListener('touchcancel', this.handleTouchCancel.bind(this));

        // Clear handlers
        this.handlers.clear();
        this.swipeHandlers.clear();

        // Remove injected styles
        const styles = ['mobile-touch-feedback-styles', 'mobile-touch-utils-styles'];
        styles.forEach(id => {
            const style = document.getElementById(id);
            if (style) style.remove();
        });
    }
}

// Export for use
export default MobileTouchUtils;

// Initialize if not in a module environment
if (typeof window !== 'undefined' && !window.mobileTouchUtils) {
    window.mobileTouchUtils = new MobileTouchUtils();
    
    if (process.env.NODE_ENV === 'development') {
        console.log('🤏 Mobile Touch Utils ready');
    }
}
