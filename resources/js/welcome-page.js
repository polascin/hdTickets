/**
 * Welcome Page Interactive Features
 * 
 * This file handles dynamic interactions for the hdTickets welcome page,
 * including live stats updates, smooth scrolling, and animation effects.
 */

class WelcomePageManager {
  constructor() {
    this.statsUpdateInterval = null;
    this.animationObserver = null;
    this.init();
  }

  /**
   * Initialize all welcome page features
   */
  init() {
    this.setupStatsUpdater();
    this.setupSmoothScrolling();
    this.setupAnimationObserver();
    this.setupInteractiveElements();
    this.setupAccessibilityFeatures();
  }

  /**
   * Setup live stats updates via API
   */
  setupStatsUpdater() {
    // Update stats every 30 seconds
    this.statsUpdateInterval = setInterval(() => {
      this.updateStats();
    }, 30000);

    // Initial stats load after 2 seconds
    setTimeout(() => {
      this.updateStats();
    }, 2000);
  }

  /**
   * Fetch and update live statistics
   */
  async updateStats() {
    try {
      const response = await fetch('/api/welcome-stats', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      this.updateStatsElements(data);
    } catch (error) {
      console.log('Stats update failed:', error);
      // Fail silently to not disrupt user experience
    }
  }

  /**
   * Update DOM elements with new stats data
   */
  updateStatsElements(data) {
    const statsMapping = {
      'platforms': '[data-stat="platforms"]',
      'tickets_tracked': '[data-stat="tickets_tracked"]',
      'users': '[data-stat="users"]',
      'success_rate': '[data-stat="success_rate"]',
      'monitoring': '[data-stat="monitoring"]',
      'events_monitored': '[data-stat="events_monitored"]'
    };

    Object.keys(statsMapping).forEach(key => {
      const elements = document.querySelectorAll(statsMapping[key]);
      if (data[key]) {
        elements.forEach(element => {
          if (element) {
            element.textContent = data[key];
            this.animateStatUpdate(element);
          }
        });
      }
    });
  }

  /**
   * Animate stat updates with a subtle effect
   */
  animateStatUpdate(element) {
    element.style.transform = 'scale(1.05)';
    element.style.transition = 'transform 0.3s ease';

    setTimeout(() => {
      element.style.transform = 'scale(1)';
    }, 300);
  }

  /**
   * Setup smooth scrolling for anchor links
   */
  setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();

        const targetId = anchor.getAttribute('href');
        const target = document.querySelector(targetId);

        if (target) {
          const headerHeight = 80; // Account for fixed header
          const targetPosition = target.offsetTop - headerHeight;

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });

          // Update URL without jumping
          history.pushState(null, null, targetId);
        }
      });
    });
  }

  /**
   * Setup intersection observer for scroll animations
   */
  setupAnimationObserver() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -100px 0px'
    };

    this.animationObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fade-in-up');
        }
      });
    }, observerOptions);

    // Observe all cards and sections
    document.querySelectorAll('.card, section').forEach(el => {
      this.animationObserver.observe(el);
    });
  }

  /**
   * Setup interactive elements (hover effects, click handlers)
   */
  setupInteractiveElements() {
    // Add hover effects to feature cards
    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-8px)';
        card.style.transition = 'transform 0.3s ease';
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
      });
    });

    // Add click tracking for CTA buttons
    document.querySelectorAll('.btn-primary, .btn-secondary').forEach(btn => {
      btn.addEventListener('click', (e) => {
        this.trackButtonClick(btn.textContent.trim(), btn.getAttribute('href'));
      });
    });

    // Add interactive pricing cards
    this.setupPricingInteractions();
  }

  /**
   * Setup pricing card interactions
   */
  setupPricingInteractions() {
    const pricingCards = document.querySelectorAll('[data-plan]');

    pricingCards.forEach(card => {
      card.addEventListener('click', () => {
        // Remove active class from all cards
        pricingCards.forEach(c => c.classList.remove('ring-2', 'ring-blue-500'));

        // Add active class to clicked card
        card.classList.add('ring-2', 'ring-blue-500');
      });
    });
  }

  /**
   * Setup accessibility features
   */
  setupAccessibilityFeatures() {
    // Keyboard navigation for cards
    document.querySelectorAll('.card').forEach(card => {
      card.setAttribute('tabindex', '0');

      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          card.click();
        }
      });
    });

    // Focus management for modal/dialog elements
    this.setupFocusManagement();
  }

  /**
   * Setup focus management for better accessibility
   */
  setupFocusManagement() {
    // Add focus visible styles
    const focusableElements = document.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');

    focusableElements.forEach(element => {
      element.addEventListener('focus', () => {
        element.classList.add('focus-visible');
      });

      element.addEventListener('blur', () => {
        element.classList.remove('focus-visible');
      });
    });
  }

  /**
   * Track button clicks for analytics
   */
  trackButtonClick(buttonText, href) {
    // This can be extended to send data to analytics services
    console.log('Button clicked:', {
      text: buttonText,
      href: href,
      timestamp: new Date().toISOString(),
      page: 'welcome'
    });

    // Example: Send to Google Analytics if available
    if (typeof gtag !== 'undefined') {
      gtag('event', 'click', {
        event_category: 'Welcome Page',
        event_label: buttonText,
        value: 1
      });
    }
  }

  /**
   * Cleanup when page is unloaded
   */
  cleanup() {
    if (this.statsUpdateInterval) {
      clearInterval(this.statsUpdateInterval);
    }

    if (this.animationObserver) {
      this.animationObserver.disconnect();
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.welcomePageManager = new WelcomePageManager();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
  if (window.welcomePageManager) {
    window.welcomePageManager.cleanup();
  }
});

// Add CSS animations via JavaScript (fallback if not in CSS)
const style = document.createElement('style');
style.textContent = `
    .animate-fade-in-up {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    
    .animate-fade-in-up:not(.animate-fade-in-up) {
        opacity: 0;
        transform: translateY(30px);
    }
    
    .focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }
    
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
`;
document.head.appendChild(style);