/**
 * Enhanced UI Animation System
 * Provides smooth transitions and micro-interactions for profile features
 */

class AnimationSystem {
    constructor(options = {}) {
        this.options = {
            duration: 300,
            easing: 'cubic-bezier(0.4, 0.0, 0.2, 1)',
            reducedMotion: false,
            debugMode: false,
            ...options
        };
        
        this.animations = new Map();
        this.observers = new Map();
        this.init();
    }

    init() {
        this.checkReducedMotion();
        this.setupIntersectionObserver();
        this.setupResizeObserver();
        this.bindEvents();
        
        if (this.options.debugMode) {
            console.log('AnimationSystem initialized', this.options);
        }
    }

    checkReducedMotion() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            this.options.reducedMotion = true;
            this.options.duration = 0;
        }
    }

    setupIntersectionObserver() {
        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.triggerScrollAnimation(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '20px'
        });
    }

    setupResizeObserver() {
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    this.handleResize(entry.target);
                });
            });
        }
    }

    bindEvents() {
        // Handle page transitions
        document.addEventListener('turbo:before-visit', () => {
            this.fadeOut(document.body, { duration: 150 });
        });

        document.addEventListener('turbo:load', () => {
            this.fadeIn(document.body, { duration: 150 });
            this.initPageAnimations();
        });

        // Handle form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.animated-form')) {
                this.animateFormSubmission(e.target);
            }
        });
    }

    // Core Animation Methods
    animate(element, properties, options = {}) {
        if (this.options.reducedMotion && !options.force) {
            Object.assign(element.style, properties);
            return Promise.resolve();
        }

        const config = {
            duration: options.duration || this.options.duration,
            easing: options.easing || this.options.easing,
            fill: 'forwards',
            ...options.keyframeOptions
        };

        const keyframes = Array.isArray(properties) ? properties : [properties];
        const animation = element.animate(keyframes, config);
        
        const animationId = Date.now() + Math.random();
        this.animations.set(animationId, animation);

        return animation.finished.then(() => {
            this.animations.delete(animationId);
        });
    }

    // Entrance Animations
    fadeIn(element, options = {}) {
        return this.animate(element, [
            { opacity: 0, transform: 'translateY(20px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], options);
    }

    fadeOut(element, options = {}) {
        return this.animate(element, [
            { opacity: 1, transform: 'translateY(0)' },
            { opacity: 0, transform: 'translateY(-20px)' }
        ], options);
    }

    slideInFromRight(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateX(100%)', opacity: 0 },
            { transform: 'translateX(0)', opacity: 1 }
        ], options);
    }

    slideInFromLeft(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateX(-100%)', opacity: 0 },
            { transform: 'translateX(0)', opacity: 1 }
        ], options);
    }

    slideUp(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateY(100%)', opacity: 0 },
            { transform: 'translateY(0)', opacity: 1 }
        ], options);
    }

    slideDown(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateY(-100%)', opacity: 0 },
            { transform: 'translateY(0)', opacity: 1 }
        ], options);
    }

    scaleIn(element, options = {}) {
        return this.animate(element, [
            { transform: 'scale(0.8)', opacity: 0 },
            { transform: 'scale(1)', opacity: 1 }
        ], options);
    }

    scaleOut(element, options = {}) {
        return this.animate(element, [
            { transform: 'scale(1)', opacity: 1 },
            { transform: 'scale(0.8)', opacity: 0 }
        ], options);
    }

    // Micro-interactions
    pulse(element, options = {}) {
        return this.animate(element, [
            { transform: 'scale(1)' },
            { transform: 'scale(1.05)' },
            { transform: 'scale(1)' }
        ], { duration: 200, ...options });
    }

    bounce(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateY(0)' },
            { transform: 'translateY(-10px)' },
            { transform: 'translateY(0)' },
            { transform: 'translateY(-5px)' },
            { transform: 'translateY(0)' }
        ], { duration: 400, ...options });
    }

    shake(element, options = {}) {
        return this.animate(element, [
            { transform: 'translateX(0)' },
            { transform: 'translateX(-10px)' },
            { transform: 'translateX(10px)' },
            { transform: 'translateX(-10px)' },
            { transform: 'translateX(10px)' },
            { transform: 'translateX(0)' }
        ], { duration: 400, ...options });
    }

    wiggle(element, options = {}) {
        return this.animate(element, [
            { transform: 'rotate(0deg)' },
            { transform: 'rotate(5deg)' },
            { transform: 'rotate(-5deg)' },
            { transform: 'rotate(5deg)' },
            { transform: 'rotate(0deg)' }
        ], { duration: 300, ...options });
    }

    // Loading Animations
    spin(element, options = {}) {
        return this.animate(element, [
            { transform: 'rotate(0deg)' },
            { transform: 'rotate(360deg)' }
        ], { 
            duration: 1000, 
            iterations: Infinity,
            ...options 
        });
    }

    shimmer(element, options = {}) {
        const shimmerKeyframes = [
            { backgroundPosition: '-200px 0' },
            { backgroundPosition: 'calc(200px + 100%) 0' }
        ];

        element.style.background = 'linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%)';
        element.style.backgroundSize = '200px 100%';

        return this.animate(element, shimmerKeyframes, {
            duration: 1500,
            iterations: Infinity,
            ...options
        });
    }

    // Specific Profile Animations
    animateProfilePictureUpload(element) {
        const uploadArea = element.querySelector('.upload-area');
        const preview = element.querySelector('.preview-area');

        if (uploadArea) {
            this.pulse(uploadArea);
        }

        return new Promise(resolve => {
            setTimeout(() => {
                if (preview) {
                    this.scaleIn(preview).then(resolve);
                } else {
                    resolve();
                }
            }, 300);
        });
    }

    animateFormValidation(field, isValid) {
        if (isValid) {
            field.classList.add('valid');
            field.classList.remove('invalid');
            return this.pulse(field, { duration: 150 });
        } else {
            field.classList.add('invalid');
            field.classList.remove('valid');
            return this.shake(field);
        }
    }

    animateFormSubmission(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            this.pulse(submitBtn);
            
            // Add loading state
            setTimeout(() => {
                const spinner = document.createElement('div');
                spinner.className = 'spinner';
                submitBtn.appendChild(spinner);
                this.spin(spinner);
            }, 100);
        }
    }

    animateSuccess(element, message) {
        // Show success message with animation
        const successElement = this.createSuccessElement(message);
        element.appendChild(successElement);
        
        return this.slideDown(successElement).then(() => {
            setTimeout(() => {
                this.fadeOut(successElement).then(() => {
                    if (successElement.parentNode) {
                        successElement.parentNode.removeChild(successElement);
                    }
                });
            }, 3000);
        });
    }

    animateError(element, message) {
        // Show error message with animation
        const errorElement = this.createErrorElement(message);
        element.appendChild(errorElement);
        
        this.shake(element);
        
        return this.slideDown(errorElement).then(() => {
            setTimeout(() => {
                this.fadeOut(errorElement).then(() => {
                    if (errorElement.parentNode) {
                        errorElement.parentNode.removeChild(errorElement);
                    }
                });
            }, 5000);
        });
    }

    // Tab Animations
    animateTabSwitch(oldTab, newTab, direction = 'right') {
        const exitAnimation = direction === 'right' ? 
            this.slideInFromLeft : this.slideInFromRight;
        const enterAnimation = direction === 'right' ? 
            this.slideInFromRight : this.slideInFromLeft;

        if (oldTab) {
            exitAnimation(oldTab, { duration: 200 });
        }

        if (newTab) {
            return enterAnimation(newTab, { duration: 200 });
        }
    }

    // Scroll Animations
    triggerScrollAnimation(element) {
        if (element.hasAttribute('data-animate-in')) {
            const animation = element.getAttribute('data-animate-in');
            const delay = parseInt(element.getAttribute('data-animate-delay') || '0');
            
            setTimeout(() => {
                switch (animation) {
                    case 'fade-in':
                        this.fadeIn(element);
                        break;
                    case 'slide-up':
                        this.slideUp(element);
                        break;
                    case 'slide-down':
                        this.slideDown(element);
                        break;
                    case 'scale-in':
                        this.scaleIn(element);
                        break;
                    default:
                        this.fadeIn(element);
                }
            }, delay);
        }
    }

    // Modal Animations
    animateModalOpen(modal) {
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        
        if (backdrop) {
            this.animate(backdrop, [
                { opacity: 0 },
                { opacity: 1 }
            ], { duration: 200 });
        }

        if (content) {
            return this.scaleIn(content, { duration: 250 });
        }
    }

    animateModalClose(modal) {
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        
        const animations = [];
        
        if (backdrop) {
            animations.push(this.animate(backdrop, [
                { opacity: 1 },
                { opacity: 0 }
            ], { duration: 200 }));
        }

        if (content) {
            animations.push(this.scaleOut(content, { duration: 200 }));
        }

        return Promise.all(animations);
    }

    // Helper Methods
    createSuccessElement(message) {
        const element = document.createElement('div');
        element.className = 'alert alert-success animated-alert';
        element.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                ${message}
            </div>
        `;
        return element;
    }

    createErrorElement(message) {
        const element = document.createElement('div');
        element.className = 'alert alert-error animated-alert';
        element.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                ${message}
            </div>
        `;
        return element;
    }

    initPageAnimations() {
        // Auto-animate elements with data attributes
        const animateElements = document.querySelectorAll('[data-animate-in]');
        animateElements.forEach(element => {
            this.intersectionObserver.observe(element);
        });

        // Initialize profile-specific animations
        this.initProfileAnimations();
    }

    initProfileAnimations() {
        // Profile picture animations
        const profilePictureUploads = document.querySelectorAll('.profile-picture-upload');
        profilePictureUploads.forEach(upload => {
            this.setupProfilePictureAnimations(upload);
        });

        // Form field animations
        const formFields = document.querySelectorAll('.form-field');
        formFields.forEach(field => {
            this.setupFormFieldAnimations(field);
        });
    }

    setupProfilePictureAnimations(upload) {
        const input = upload.querySelector('input[type="file"]');
        const dropZone = upload.querySelector('.drop-zone');

        if (input && dropZone) {
            input.addEventListener('change', () => {
                this.animateProfilePictureUpload(upload);
            });

            dropZone.addEventListener('dragenter', () => {
                this.pulse(dropZone, { duration: 150 });
            });
        }
    }

    setupFormFieldAnimations(field) {
        const input = field.querySelector('input, textarea, select');
        if (input) {
            input.addEventListener('focus', () => {
                this.animate(field, [
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.02)' }
                ], { duration: 150 });
            });

            input.addEventListener('blur', () => {
                this.animate(field, [
                    { transform: 'scale(1.02)' },
                    { transform: 'scale(1)' }
                ], { duration: 150 });
            });
        }
    }

    handleResize(element) {
        // Handle responsive animations on resize
        const animations = this.animations.values();
        for (const animation of animations) {
            if (animation.playState === 'running') {
                // Optionally pause or adjust animations during resize
            }
        }
    }

    // Cleanup Methods
    pauseAll() {
        this.animations.forEach(animation => {
            animation.pause();
        });
    }

    resumeAll() {
        this.animations.forEach(animation => {
            animation.play();
        });
    }

    cancelAll() {
        this.animations.forEach(animation => {
            animation.cancel();
        });
        this.animations.clear();
    }

    destroy() {
        this.cancelAll();
        
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
        }
        
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
    }
}

// CSS-in-JS for animation styles
const animationStyles = `
    .animated-alert {
        transform: translateY(-20px);
        opacity: 0;
        margin: 10px 0;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid;
    }

    .alert-success {
        background-color: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
    }

    .form-field {
        transition: all 0.15s ease;
    }

    .form-field.valid input,
    .form-field.valid textarea,
    .form-field.valid select {
        border-color: #10b981;
        box-shadow: 0 0 0 1px #10b981;
    }

    .form-field.invalid input,
    .form-field.invalid textarea,
    .form-field.invalid select {
        border-color: #ef4444;
        box-shadow: 0 0 0 1px #ef4444;
    }

    .drop-zone {
        transition: all 0.15s ease;
    }

    .drop-zone:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
`;

// Inject styles
if (!document.querySelector('#animation-system-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'animation-system-styles';
    styleElement.textContent = animationStyles;
    document.head.appendChild(styleElement);
}

// Export for use in modules
export default AnimationSystem;

// Global instance for immediate use
window.AnimationSystem = AnimationSystem;

// Auto-initialize with default options
document.addEventListener('DOMContentLoaded', () => {
    if (!window.animationSystem) {
        window.animationSystem = new AnimationSystem({
            debugMode: document.body.hasAttribute('data-debug-animations')
        });
    }
});
