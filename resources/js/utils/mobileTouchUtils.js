/**
 * Mobile Touch and Swipe Utilities for HD Tickets
 * Provides advanced touch interactions, swipe gestures, and mobile-specific optimizations
 */

class MobileTouchUtils {
    constructor(options = {}) {
        this.config = {
            swipeThreshold: 50,
            swipeTimeout: 300,
            longPressTimeout: 500,
            doubleTapTimeout: 300,
            tapTolerance: 10,
            preventScroll: false,
            enableHapticFeedback: true,
            enableVisualFeedback: true,
            enableDebugMode: false,
            ...options
        };

        this.state = {
            isTouch: false,
            touchStartTime: 0,
            touchEndTime: 0,
            startX: 0,
            startY: 0,
            endX: 0,
            endY: 0,
            lastTapTime: 0,
            longPressTimer: null,
            activeElement: null,
            gestureInProgress: false
        };

        this.handlers = new Map();
        this.activeSwipeHandlers = new Map();
        
        this.init();
    }

    /**
     * Initialize touch utilities
     */
    init() {
        if (!this.isTouchDevice()) {
            console.log('Touch utilities disabled - not a touch device');
            return;
        }

        this.setupGlobalListeners();
        this.setupVisualFeedback();
        this.detectIOSDevice();
        
        console.log('🤏 Mobile Touch Utils initialized');
    }

    /**
     * Check if device supports touch
     */
    isTouchDevice() {
        return ('ontouchstart' in window) || 
               (navigator.maxTouchPoints > 0) || 
               (navigator.msMaxTouchPoints > 0);
    }

    /**
     * Detect iOS device for specific optimizations
     */
    detectIOSDevice() {
        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        if (this.isIOS) {
            document.body.classList.add('ios-device');
            // Prevent bounce scrolling
            document.addEventListener('touchmove', this.preventBounce.bind(this), { passive: false });
        }
    }

    /**
     * Prevent iOS bounce scrolling when needed
     */
    preventBounce(e) {
        if (!this.config.preventScroll) return;
        
        const target = e.target.closest('[data-prevent-scroll]');
        if (target) {
            e.preventDefault();
        }
    }

    /**
     * Setup global touch event listeners
     */
    setupGlobalListeners() {
        // Use passive listeners where possible for better performance
        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: false });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
        document.addEventListener('touchcancel', this.handleTouchCancel.bind(this), { passive: true });

        // Handle orientation changes
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.recalculateViewport();
            }, 100);
        });
    }

    /**
     * Setup visual feedback styles
     */
    setupVisualFeedback() {
        if (!this.config.enableVisualFeedback) return;

        const style = document.createElement('style');
        style.id = 'mobile-touch-feedback';
        style.textContent = `
            .touch-active {
                background-color: rgba(0, 0, 0, 0.05);
                transform: scale(0.98);
                transition: all 0.1s ease-out;
            }
            
            .touch-ripple {
                position: relative;
                overflow: hidden;
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
            }
            
            .touch-ripple.ripple-active::before {
                width: 300px;
                height: 300px;
            }
            
            .long-press-indicator {
                position: relative;
                overflow: hidden;
            }
            
            .long-press-indicator::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2));
                transform: translateX(-100%);
                transition: transform 0.5s ease-out;
                pointer-events: none;
            }
            
            .long-press-indicator.long-pressing::after {
                transform: translateX(0);
            }
            
            @media (prefers-reduced-motion: reduce) {
                .touch-active {
                    transform: none;
                    transition: none;
                }
                
                .touch-ripple::before {
                    transition: none;
                }
                
                .long-press-indicator::after {
                    transition: none;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Handle touch start
     */
    handleTouchStart(e) {
        if (e.touches.length > 1) return; // Only handle single touch

        this.state.isTouch = true;
        this.state.touchStartTime = Date.now();
        this.state.startX = e.touches[0].clientX;
        this.state.startY = e.touches[0].clientY;
        this.state.gestureInProgress = true;
        this.state.activeElement = e.target;

        // Add visual feedback
        this.addTouchFeedback(e.target);

        // Setup long press detection
        this.setupLongPress(e);

        // Trigger haptic feedback
        this.triggerHapticFeedback('light');

        // Debug logging
        if (this.config.enableDebugMode) {
            console.log('Touch start:', { x: this.state.startX, y: this.state.startY });
        }
    }

    /**
     * Handle touch move
     */
    handleTouchMove(e) {
        if (!this.state.gestureInProgress) return;

        this.state.endX = e.touches[0].clientX;
        this.state.endY = e.touches[0].clientY;

        const deltaX = Math.abs(this.state.endX - this.state.startX);
        const deltaY = Math.abs(this.state.endY - this.state.startY);

        // Cancel long press if moved too much
        if ((deltaX > this.config.tapTolerance || deltaY > this.config.tapTolerance) && this.state.longPressTimer) {
            clearTimeout(this.state.longPressTimer);
            this.state.longPressTimer = null;
            this.removeLongPressFeedback();
        }

        // Handle swipe prevention if needed
        const target = e.target.closest('[data-prevent-scroll]');
        if (target) {
            e.preventDefault();
        }
    }

    /**
     * Handle touch end
     */
    handleTouchEnd(e) {
        if (!this.state.gestureInProgress) return;

        this.state.touchEndTime = Date.now();
        this.state.endX = e.changedTouches[0].clientX;
        this.state.endY = e.changedTouches[0].clientY;

        const touchDuration = this.state.touchEndTime - this.state.touchStartTime;
        const deltaX = this.state.endX - this.state.startX;
        const deltaY = this.state.endY - this.state.startY;
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

        // Clear long press timer
        if (this.state.longPressTimer) {
            clearTimeout(this.state.longPressTimer);
            this.state.longPressTimer = null;
        }

        // Remove visual feedback
        this.removeTouchFeedback();

        // Determine gesture type
        if (distance < this.config.tapTolerance) {
            this.handleTap(e);
        } else if (touchDuration < this.config.swipeTimeout && distance > this.config.swipeThreshold) {
            this.handleSwipe(e, deltaX, deltaY);
        }

        // Reset state
        this.resetState();

        // Debug logging
        if (this.config.enableDebugMode) {
            console.log('Touch end:', { deltaX, deltaY, distance, duration: touchDuration });
        }
    }

    /**
     * Handle touch cancel
     */
    handleTouchCancel(e) {
        this.removeTouchFeedback();
        this.removeLongPressFeedback();
        
        if (this.state.longPressTimer) {
            clearTimeout(this.state.longPressTimer);
            this.state.longPressTimer = null;
        }
        
        this.resetState();
    }

    /**
     * Handle tap gesture
     */
    handleTap(e) {
        const currentTime = Date.now();
        const isDoubleTap = currentTime - this.state.lastTapTime < this.config.doubleTapTimeout;
        
        if (isDoubleTap) {
            this.triggerCustomEvent('doubletap', e);
            this.triggerHapticFeedback('medium');
        } else {
            this.triggerCustomEvent('tap', e);
        }
        
        this.state.lastTapTime = currentTime;
    }

    /**
     * Handle swipe gesture
     */
    handleSwipe(e, deltaX, deltaY) {
        const absX = Math.abs(deltaX);
        const absY = Math.abs(deltaY);
        
        let direction;
        if (absX > absY) {
            direction = deltaX > 0 ? 'right' : 'left';
        } else {
            direction = deltaY > 0 ? 'down' : 'up';
        }

        this.triggerCustomEvent('swipe', e, {
            direction,
            deltaX,
            deltaY,
            distance: Math.sqrt(deltaX * deltaX + deltaY * deltaY)
        });

        this.triggerHapticFeedback('light');
    }

    /**
     * Setup long press detection
     */
    setupLongPress(e) {
        this.state.longPressTimer = setTimeout(() => {
            this.triggerCustomEvent('longpress', e);
            this.triggerHapticFeedback('heavy');
            this.addLongPressFeedback(e.target);
        }, this.config.longPressTimeout);
    }

    /**
     * Add visual touch feedback
     */
    addTouchFeedback(element) {
        if (!this.config.enableVisualFeedback) return;

        const target = element.closest('button, a, .touch-target, [data-touch-feedback]');
        if (target) {
            target.classList.add('touch-active');
            
            // Add ripple effect
            if (target.classList.contains('touch-ripple') || target.dataset.touchRipple !== undefined) {
                target.classList.add('ripple-active');
            }
        }
    }

    /**
     * Remove visual touch feedback
     */
    removeTouchFeedback() {
        setTimeout(() => {
            document.querySelectorAll('.touch-active').forEach(el => {
                el.classList.remove('touch-active');
            });
            
            document.querySelectorAll('.ripple-active').forEach(el => {
                el.classList.remove('ripple-active');
            });
        }, 150);
    }

    /**
     * Add long press visual feedback
     */
    addLongPressFeedback(element) {
        const target = element.closest('button, a, .touch-target, [data-long-press]');
        if (target) {
            target.classList.add('long-pressing');
        }
    }

    /**
     * Remove long press visual feedback
     */
    removeLongPressFeedback() {
        document.querySelectorAll('.long-pressing').forEach(el => {
            el.classList.remove('long-pressing');
        });
    }

    /**
     * Trigger haptic feedback
     */
    triggerHapticFeedback(intensity = 'light') {
        if (!this.config.enableHapticFeedback || !navigator.vibrate) return;

        const patterns = {
            light: 10,
            medium: 20,
            heavy: 50,
            success: [10, 50, 10],
            error: [100, 50, 100]
        };

        const pattern = patterns[intensity] || patterns.light;
        navigator.vibrate(pattern);
    }

    /**
     * Trigger custom touch event
     */
    triggerCustomEvent(eventName, originalEvent, details = {}) {
        const customEvent = new CustomEvent(`mobile${eventName}`, {
            detail: {
                originalEvent,
                element: this.state.activeElement,
                startPos: { x: this.state.startX, y: this.state.startY },
                endPos: { x: this.state.endX, y: this.state.endY },
                duration: this.state.touchEndTime - this.state.touchStartTime,
                ...details
            },
            bubbles: true,
            cancelable: true
        });

        this.state.activeElement.dispatchEvent(customEvent);

        // Execute registered handlers
        const handlers = this.handlers.get(eventName) || [];
        handlers.forEach(handler => {
            try {
                handler(customEvent);
            } catch (error) {
                console.error(`Error in ${eventName} handler:`, error);
            }
        });
    }

    /**
     * Reset touch state
     */
    resetState() {
        this.state.isTouch = false;
        this.state.gestureInProgress = false;
        this.state.activeElement = null;
        this.state.startX = 0;
        this.state.startY = 0;
        this.state.endX = 0;
        this.state.endY = 0;
    }

    /**
     * Recalculate viewport for orientation changes
     */
    recalculateViewport() {
        // Update viewport height for mobile browsers
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
        
        // Trigger custom event
        window.dispatchEvent(new CustomEvent('mobileorientationchange', {
            detail: {
                width: window.innerWidth,
                height: window.innerHeight,
                orientation: screen.orientation?.angle || 0
            }
        }));
    }

    /**
     * Register event handler
     */
    on(eventName, handler) {
        if (!this.handlers.has(eventName)) {
            this.handlers.set(eventName, []);
        }
        
        this.handlers.get(eventName).push(handler);
        
        return () => {
            const handlers = this.handlers.get(eventName) || [];
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        };
    }

    /**
     * Enable swipe gestures for an element
     */
    enableSwipe(element, callbacks = {}) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return null;

        const swipeHandler = (e) => {
            const { direction } = e.detail;
            const callback = callbacks[`swipe${direction.charAt(0).toUpperCase() + direction.slice(1)}`] || callbacks.onSwipe;
            
            if (callback) {
                callback(e);
            }
        };

        element.addEventListener('mobileswipe', swipeHandler);
        element.setAttribute('data-swipe-enabled', 'true');
        
        this.activeSwipeHandlers.set(element, swipeHandler);

        return () => {
            element.removeEventListener('mobileswipe', swipeHandler);
            element.removeAttribute('data-swipe-enabled');
            this.activeSwipeHandlers.delete(element);
        };
    }

    /**
     * Enable pull-to-refresh on an element
     */
    enablePullToRefresh(element, callback) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return null;

        let startY = 0;
        let pullDistance = 0;
        let isRefreshing = false;
        const threshold = 100;

        const handleTouchStart = (e) => {
            if (element.scrollTop === 0) {
                startY = e.touches[0].pageY;
            }
        };

        const handleTouchMove = (e) => {
            if (element.scrollTop === 0 && !isRefreshing) {
                pullDistance = e.touches[0].pageY - startY;
                
                if (pullDistance > 0) {
                    // Visual feedback
                    const progress = Math.min(pullDistance / threshold, 1);
                    element.style.transform = `translateY(${pullDistance * 0.3}px)`;
                    element.style.opacity = 1 - (progress * 0.1);
                    
                    // Prevent default scroll
                    e.preventDefault();
                }
            }
        };

        const handleTouchEnd = () => {
            if (pullDistance > threshold && !isRefreshing) {
                isRefreshing = true;
                element.style.transform = `translateY(${threshold * 0.3}px)`;
                
                // Trigger refresh callback
                Promise.resolve(callback()).finally(() => {
                    isRefreshing = false;
                    element.style.transform = '';
                    element.style.opacity = '';
                    pullDistance = 0;
                });
            } else {
                element.style.transform = '';
                element.style.opacity = '';
                pullDistance = 0;
            }
        };

        element.addEventListener('touchstart', handleTouchStart, { passive: false });
        element.addEventListener('touchmove', handleTouchMove, { passive: false });
        element.addEventListener('touchend', handleTouchEnd);

        return () => {
            element.removeEventListener('touchstart', handleTouchStart);
            element.removeEventListener('touchmove', handleTouchMove);
            element.removeEventListener('touchend', handleTouchEnd);
        };
    }

    /**
     * Create mobile-friendly click handler
     */
    makeTouchFriendly(element, callback) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return null;

        // Add touch feedback attributes
        element.setAttribute('data-touch-feedback', 'true');
        element.classList.add('touch-target');

        const tapHandler = (e) => {
            callback(e);
        };

        element.addEventListener('mobiletap', tapHandler);

        return () => {
            element.removeEventListener('mobiletap', tapHandler);
            element.removeAttribute('data-touch-feedback');
            element.classList.remove('touch-target');
        };
    }

    /**
     * Get touch capabilities info
     */
    getTouchCapabilities() {
        return {
            hasTouch: this.isTouchDevice(),
            maxTouchPoints: navigator.maxTouchPoints || 0,
            isIOS: this.isIOS,
            supportsHaptics: 'vibrate' in navigator,
            supportsForceTouch: 'TouchEvent' in window && 'force' in TouchEvent.prototype
        };
    }

    /**
     * Destroy touch utilities
     */
    destroy() {
        // Remove event listeners
        document.removeEventListener('touchstart', this.handleTouchStart);
        document.removeEventListener('touchmove', this.handleTouchMove);
        document.removeEventListener('touchend', this.handleTouchEnd);
        document.removeEventListener('touchcancel', this.handleTouchCancel);

        // Clear timers
        if (this.state.longPressTimer) {
            clearTimeout(this.state.longPressTimer);
        }

        // Remove styles
        const styles = document.getElementById('mobile-touch-feedback');
        if (styles) {
            styles.remove();
        }

        // Clean up handlers
        this.handlers.clear();
        this.activeSwipeHandlers.clear();

        console.log('Mobile Touch Utils destroyed');
    }
}

// Auto-initialize on touch devices
document.addEventListener('DOMContentLoaded', () => {
    if (('ontouchstart' in window) || (navigator.maxTouchPoints > 0)) {
        window.mobileTouchUtils = new MobileTouchUtils();
    }
});

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileTouchUtils;
}

export default MobileTouchUtils;
