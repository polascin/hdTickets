/**
 * HD Tickets - Touch Interactions and Gesture Support
 * 
 * Essential touch interaction utilities for mobile optimization
 */

class TouchSupport {
    constructor(options = {}) {
        this.options = {
            pullToRefreshThreshold: 60,
            swipeThreshold: 50,
            longPressThreshold: 500,
            rippleEnabled: true,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.addTouchOptimizations();
        this.addPullToRefreshSupport();
        this.addSwipeSupport();
        this.addTouchFeedback();
    }
    
    addTouchOptimizations() {
        // Add touch-friendly classes
        document.body.classList.add('touch-optimized');
        
        // Ensure touch targets meet minimum size
        const touchTargets = document.querySelectorAll('button, a, input, [role="button"]');
        touchTargets.forEach(el => {
            if (!el.classList.contains('touch-target')) {
                el.classList.add('touch-target');
            }
        });
    }
    
    addPullToRefreshSupport() {
        const containers = document.querySelectorAll('.pull-to-refresh');
        
        containers.forEach(container => {
            let startY = 0;
            let pulling = false;
            
            container.addEventListener('touchstart', (e) => {
                if (container.scrollTop === 0) {
                    startY = e.touches[0].clientY;
                    pulling = true;
                }
            });
            
            container.addEventListener('touchmove', (e) => {
                if (!pulling) return;
                
                const currentY = e.touches[0].clientY;
                const pullDistance = currentY - startY;
                
                if (pullDistance > 0) {
                    e.preventDefault();
                    
                    if (pullDistance > this.options.pullToRefreshThreshold) {
                        container.classList.add('pulling');
                    }
                }
            });
            
            container.addEventListener('touchend', () => {
                if (pulling && container.classList.contains('pulling')) {
                    container.dispatchEvent(new CustomEvent('pullrefresh'));
                }
                
                pulling = false;
                container.classList.remove('pulling');
            });
        });
    }
    
    addSwipeSupport() {
        const swipeElements = document.querySelectorAll('.swipeable');
        
        swipeElements.forEach(element => {
            let startX = 0;
            
            element.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            });
            
            element.addEventListener('touchend', (e) => {
                const endX = e.changedTouches[0].clientX;
                const deltaX = endX - startX;
                
                if (Math.abs(deltaX) > this.options.swipeThreshold) {
                    const direction = deltaX > 0 ? 'right' : 'left';
                    
                    element.dispatchEvent(new CustomEvent('swipe', {
                        detail: { direction, distance: Math.abs(deltaX) }
                    }));
                }
            });
        });
    }
    
    addTouchFeedback() {
        if (!this.options.rippleEnabled) return;
        
        const elements = document.querySelectorAll('button:not(.no-ripple), .touch-feedback');
        
        elements.forEach(element => {
            element.addEventListener('touchstart', (e) => {
                const rect = element.getBoundingClientRect();
                const x = e.touches[0].clientX - rect.left;
                const y = e.touches[0].clientY - rect.top;
                
                this.createRipple(element, x, y);
            });
        });
    }
    
    createRipple(element, x, y) {
        const ripple = document.createElement('span');
        ripple.className = 'touch-ripple';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
}

// Auto-initialize
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        window.touchSupport = new TouchSupport();
    });
}

export default TouchSupport;
