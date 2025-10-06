/**
 * HD Tickets Enhanced Welcome Page JavaScript
 * Systematic interactive enhancements and animations
 */

// Enhanced scroll reveal animation system
class ScrollReveal {
    constructor() {
        this.elements = document.querySelectorAll('.scroll-reveal, .card, .testimonial-card, .feature-icon');
        this.observer = new IntersectionObserver(this.handleIntersect.bind(this), {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        this.init();
    }
    
    init() {
        // Add scroll reveal class to elements that don't have it
        this.elements.forEach(element => {
            if (!element.classList.contains('scroll-reveal')) {
                element.classList.add('scroll-reveal');
            }
            this.observer.observe(element);
        });
    }
    
    handleIntersect(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Add staggered delay for multiple elements in the same container
                const siblings = Array.from(entry.target.parentElement.children)
                    .filter(el => el.classList.contains('scroll-reveal'));
                const index = siblings.indexOf(entry.target);
                entry.target.style.transitionDelay = `${index * 0.1}s`;
            }
        });
    }
}

// Enhanced navbar scroll behavior
class NavbarEnhancer {
    constructor() {
        this.navbar = document.querySelector('.navbar, .navbar-enhanced');
        this.navLinks = document.querySelectorAll('.nav-link, .nav-link-enhanced');
        this.lastScrollY = window.scrollY;
        this.ticking = false;
        
        this.init();
    }
    
    init() {
        if (this.navbar) {
            window.addEventListener('scroll', this.handleScroll.bind(this));
            this.setupSmoothScrolling();
        }
    }
    
    handleScroll() {
        if (!this.ticking) {
            requestAnimationFrame(() => {
                const currentScrollY = window.scrollY;
                
                // Add/remove scrolled class
                if (currentScrollY > 50) {
                    this.navbar.classList.add('scrolled');
                } else {
                    this.navbar.classList.remove('scrolled');
                }
                
                // Hide navbar on scroll down, show on scroll up
                if (currentScrollY > this.lastScrollY && currentScrollY > 100) {
                    this.navbar.style.transform = 'translateY(-100%)';
                } else {
                    this.navbar.style.transform = 'translateY(0)';
                }
                
                this.lastScrollY = currentScrollY;
                this.ticking = false;
            });
            this.ticking = true;
        }
    }
    
    setupSmoothScrolling() {
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }
}

// Enhanced stats counter animation
class StatsCounter {
    constructor() {
        this.counters = document.querySelectorAll('.stats-counter, .stats-counter-enhanced');
        this.observer = new IntersectionObserver(this.animate.bind(this), {
            threshold: 0.5
        });
        
        this.init();
    }
    
    init() {
        this.counters.forEach(counter => {
            this.observer.observe(counter.parentElement);
        });
    }
    
    animate(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target.querySelector('.stats-counter, .stats-counter-enhanced');
                if (counter && !counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    this.animateCounter(counter);
                }
            }
        });
    }
    
    animateCounter(element) {
        const target = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
        const suffix = element.textContent.replace(/[\d,]/g, '');
        let current = 0;
        const increment = target / 100;
        const duration = 2000;
        const stepTime = duration / 100;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            const value = Math.floor(current);
            element.textContent = value.toLocaleString() + suffix;
        }, stepTime);
    }
}

// Enhanced card hover effects
class CardEnhancer {
    constructor() {
        this.cards = document.querySelectorAll('.card, .card-enhanced, .testimonial-card, .testimonial-card-enhanced, .pricing-card, .pricing-card-enhanced');
        this.init();
    }
    
    init() {
        this.cards.forEach(card => {
            this.addHoverEffects(card);
            this.addTiltEffect(card);
        });
    }
    
    addHoverEffects(card) {
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    }
    
    addTiltEffect(card) {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const tiltX = (y - centerY) / centerY * -5;
            const tiltY = (x - centerX) / centerX * 5;
            
            card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-8px)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    }
}

// Enhanced typing animation for hero text
class TypingAnimation {
    constructor() {
        this.element = document.querySelector('.gradient-text');
        this.texts = [
            'Perfect Ticket',
            'Best Deal',
            'Dream Event',
            'Great Seats'
        ];
        this.currentIndex = 0;
        this.currentText = '';
        this.isDeleting = false;
        this.typeSpeed = 100;
        this.deleteSpeed = 50;
        this.pauseTime = 2000;
        
        if (this.element) {
            this.init();
        }
    }
    
    init() {
        this.type();
    }
    
    type() {
        const fullText = this.texts[this.currentIndex];
        
        if (this.isDeleting) {
            this.currentText = fullText.substring(0, this.currentText.length - 1);
        } else {
            this.currentText = fullText.substring(0, this.currentText.length + 1);
        }
        
        this.element.textContent = this.currentText;
        
        let speed = this.isDeleting ? this.deleteSpeed : this.typeSpeed;
        
        if (!this.isDeleting && this.currentText === fullText) {
            speed = this.pauseTime;
            this.isDeleting = true;
        } else if (this.isDeleting && this.currentText === '') {
            this.isDeleting = false;
            this.currentIndex = (this.currentIndex + 1) % this.texts.length;
            speed = 500;
        }
        
        setTimeout(() => this.type(), speed);
    }
}

// Enhanced button interactions
class ButtonEnhancer {
    constructor() {
        this.buttons = document.querySelectorAll('.btn, .btn-enhanced, .btn-primary-enhanced, .btn-accent-enhanced');
        this.init();
    }
    
    init() {
        this.buttons.forEach(button => {
            this.addRippleEffect(button);
            this.addLoadingState(button);
        });
    }
    
    addRippleEffect(button) {
        button.addEventListener('click', (e) => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                transform: scale(0);
                animation: ripple 0.6s linear;
                left: ${x}px;
                top: ${y}px;
                width: 20px;
                height: 20px;
                margin-left: -10px;
                margin-top: -10px;
            `;
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
        
        // Add ripple animation CSS
        if (!document.querySelector('#ripple-animation')) {
            const style = document.createElement('style');
            style.id = 'ripple-animation';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    addLoadingState(button) {
        if (button.type === 'submit' || button.closest('form')) {
            button.addEventListener('click', () => {
                button.classList.add('loading');
                button.disabled = true;
                
                setTimeout(() => {
                    button.classList.remove('loading');
                    button.disabled = false;
                }, 3000);
            });
        }
    }
}

// Enhanced parallax effects
class ParallaxEffect {
    constructor() {
        this.elements = document.querySelectorAll('.floating-animation, .animate-float-enhanced');
        this.init();
    }
    
    init() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            this.elements.forEach((element, index) => {
                const rate = scrolled * -0.5 * (index + 1) * 0.1;
                element.style.transform = `translateY(${rate}px)`;
            });
        });
    }
}

// Enhanced form validation
class FormValidator {
    constructor() {
        this.forms = document.querySelectorAll('form');
        this.init();
    }
    
    init() {
        this.forms.forEach(form => {
            this.setupFormValidation(form);
        });
    }
    
    setupFormValidation(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateField(input);
                }
            });
        });
        
        form.addEventListener('submit', (e) => {
            let isValid = true;
            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        } else if (field.type === 'password' && value && value.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters';
        }
        
        this.showValidationState(field, isValid, errorMessage);
        return isValid;
    }
    
    showValidationState(field, isValid, message) {
        const errorElement = field.parentElement.querySelector('.error-message') ||
            (() => {
                const error = document.createElement('div');
                error.className = 'error-message text-sm text-red-600 mt-1';
                field.parentElement.appendChild(error);
                return error;
            })();
        
        if (isValid) {
            field.classList.remove('error');
            field.classList.add('valid');
            errorElement.textContent = '';
        } else {
            field.classList.remove('valid');
            field.classList.add('error');
            errorElement.textContent = message;
        }
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
}

// Enhanced accessibility features
class AccessibilityEnhancer {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupScreenReaderSupport();
    }
    
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes mobile menu
            if (e.key === 'Escape') {
                const mobileMenu = document.querySelector('[x-show="mobileMenuOpen"]');
                if (mobileMenu && !mobileMenu.hidden) {
                    // Trigger Alpine.js to close menu
                    window.dispatchEvent(new CustomEvent('close-mobile-menu'));
                }
            }
            
            // Tab navigation enhancements
            if (e.key === 'Tab') {
                this.highlightFocusedElement();
            }
        });
    }
    
    setupFocusManagement() {
        // Trap focus in modals and mobile menus
        const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        
        document.querySelectorAll('.modal, .mobile-menu').forEach(container => {
            container.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    const focusableContent = container.querySelectorAll(focusableElements);
                    const firstElement = focusableContent[0];
                    const lastElement = focusableContent[focusableContent.length - 1];
                    
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            e.preventDefault();
                        }
                    }
                }
            });
        });
    }
    
    setupScreenReaderSupport() {
        // Add live regions for dynamic content
        const liveRegions = document.querySelectorAll('.stats-counter, .stats-counter-enhanced');
        liveRegions.forEach(region => {
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('aria-atomic', 'true');
        });
        
        // Enhance button labels
        document.querySelectorAll('button').forEach(button => {
            if (!button.getAttribute('aria-label') && !button.textContent.trim()) {
                const icon = button.querySelector('i[class*="fa-"]');
                if (icon) {
                    const iconClass = Array.from(icon.classList).find(cls => cls.startsWith('fa-'));
                    if (iconClass) {
                        const label = iconClass.replace('fa-', '').replace('-', ' ');
                        button.setAttribute('aria-label', label);
                    }
                }
            }
        });
    }
    
    highlightFocusedElement() {
        // Add visual focus indicators
        document.activeElement.style.outline = '2px solid #2563eb';
        document.activeElement.style.outlineOffset = '2px';
        
        setTimeout(() => {
            if (document.activeElement) {
                document.activeElement.style.outline = '';
                document.activeElement.style.outlineOffset = '';
            }
        }, 3000);
    }
}

// Performance optimizer
class PerformanceOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.lazyLoadImages();
        this.preloadCriticalResources();
        this.optimizeScrollEvents();
    }
    
    lazyLoadImages() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    preloadCriticalResources() {
        // Preload critical CSS and fonts
        const criticalResources = [
            '/css/welcome-enhanced.css',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap'
        ];
        
        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = resource.endsWith('.css') ? 'style' : 'font';
            link.href = resource;
            if (resource.includes('font')) {
                link.crossOrigin = 'anonymous';
            }
            document.head.appendChild(link);
        });
    }
    
    optimizeScrollEvents() {
        // Throttle scroll events for better performance
        let scrollTimeout;
        const originalScrollHandlers = [];
        
        // Wrap existing scroll handlers
        ['scroll', 'resize'].forEach(event => {
            const handlers = [];
            document.addEventListener(event, (e) => {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    handlers.forEach(handler => handler(e));
                }, 16); // ~60fps
            });
        });
    }
}

// Initialize all enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all enhancement classes
    new ScrollReveal();
    new NavbarEnhancer();
    new StatsCounter();
    new CardEnhancer();
    new TypingAnimation();
    new ButtonEnhancer();
    new ParallaxEffect();
    new FormValidator();
    new AccessibilityEnhancer();
    new PerformanceOptimizer();
    
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to external links
    document.querySelectorAll('a[href^="http"]').forEach(link => {
        link.addEventListener('click', () => {
            link.style.opacity = '0.7';
            setTimeout(() => {
                link.style.opacity = '1';
            }, 1000);
        });
    });
    
    // Enhanced mobile menu handling
    const mobileMenuToggle = document.querySelector('[x-on\\:click*="mobileMenuOpen"]');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', () => {
            document.body.classList.toggle('menu-open');
        });
    }
    
    console.log('🎫 HD Tickets enhanced welcome page initialized successfully!');
});

// Add CSS for enhanced features
const enhancedStyles = `
    .error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    .valid {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }
    
    .loading {
        position: relative;
        color: transparent !important;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .menu-open {
        overflow: hidden;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;

// Inject enhanced styles
const styleSheet = document.createElement('style');
styleSheet.textContent = enhancedStyles;
document.head.appendChild(styleSheet);