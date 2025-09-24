// Welcome page specific functionality
import Alpine from 'alpinejs';

// WelcomePageManager Class for enhanced functionality
class WelcomePageManager {
  constructor() {
    this.statsUpdateInterval = null;
    this.animationObserver = null;
    this.init();
  }

  init() {
    this.setupStatsUpdater();
    this.setupSmoothScrolling();
    this.setupAnimationObserver();
    this.setupInteractiveElements();
    this.setupAccessibilityFeatures();
  }

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
    }
  }

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

  animateStatUpdate(element) {
    element.style.transform = 'scale(1.05)';
    element.style.transition = 'transform 0.3s ease';

    setTimeout(() => {
      element.style.transform = 'scale(1)';
    }, 300);
  }

  setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();

        const targetId = anchor.getAttribute('href');
        const target = document.querySelector(targetId);

        if (target) {
          const headerHeight = 80;
          const targetPosition = target.offsetTop - headerHeight;

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });

          history.pushState(null, null, targetId);
        }
      });
    });
  }

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

    document.querySelectorAll('.card, section').forEach(el => {
      this.animationObserver.observe(el);
    });
  }

  setupInteractiveElements() {
    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-8px)';
        card.style.transition = 'transform 0.3s ease';
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
      });
    });

    document.querySelectorAll('.btn-primary, .btn-secondary').forEach(btn => {
      btn.addEventListener('click', (e) => {
        this.trackButtonClick(btn.textContent.trim(), btn.getAttribute('href'));
      });
    });

    this.setupPricingInteractions();
  }

  setupPricingInteractions() {
    const pricingCards = document.querySelectorAll('[data-plan]');

    pricingCards.forEach(card => {
      card.addEventListener('click', () => {
        pricingCards.forEach(c => c.classList.remove('ring-2', 'ring-blue-500'));
        card.classList.add('ring-2', 'ring-blue-500');
      });
    });
  }

  setupAccessibilityFeatures() {
    document.querySelectorAll('.card').forEach(card => {
      card.setAttribute('tabindex', '0');

      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          card.click();
        }
      });
    });

    this.setupFocusManagement();
  }

  setupFocusManagement() {
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

  trackButtonClick(buttonText, href) {
    console.log('Button clicked:', {
      text: buttonText,
      href: href,
      timestamp: new Date().toISOString(),
      page: 'welcome'
    });

    if (typeof gtag !== 'undefined') {
      gtag('event', 'click', {
        event_category: 'Welcome Page',
        event_label: buttonText,
        value: 1
      });
    }
  }

  cleanup() {
    if (this.statsUpdateInterval) {
      clearInterval(this.statsUpdateInterval);
    }

    if (this.animationObserver) {
      this.animationObserver.disconnect();
    }
  }
}

// Initialize WelcomePageManager
document.addEventListener('DOMContentLoaded', () => {
  window.welcomePageManager = new WelcomePageManager();
});

window.addEventListener('beforeunload', () => {
  if (window.welcomePageManager) {
    window.welcomePageManager.cleanup();
  }
});

// Welcome page Alpine component
document.addEventListener('alpine:init', () => {
  Alpine.data('welcomePage', () => ({
    // State
    isLoading: true,
    darkMode: false,
    currentTheme: 'football',
    stats: {
      platforms: 50,
      monitoring: '24/7',
      users: '15K+',
    },
    showParticles: false,

    // Initialize
    init() {
      this.detectTheme();
      this.loadStats();
      this.setupParticleEffect();
      this.initIntersectionObserver();

      // Simulate loading completion
      setTimeout(() => {
        this.isLoading = false;
        this.animateStatsCount();
      }, 1000);
    },

    // Theme detection and management
    detectTheme() {
      const savedTheme = localStorage.getItem('theme');
      const systemTheme = window.matchMedia('(prefers-color-scheme: dark)')
        .matches
        ? 'dark'
        : 'light';

      this.darkMode =
        savedTheme === 'dark' || (!savedTheme && systemTheme === 'dark');
      this.applyTheme();
    },

    toggleTheme() {
      this.darkMode = !this.darkMode;
      this.applyTheme();
      localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
    },

    applyTheme() {
      if (this.darkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },

    // Stats functionality
    async loadStats() {
      try {
        const response = await fetch('/api/welcome-stats');
        if (response.ok) {
          const data = await response.json();
          this.stats = { ...this.stats, ...data };
        }
      } catch {
        console.warn('Could not load real-time stats, using defaults');
      }
    },

    animateStatsCount() {
      const statElements = document.querySelectorAll('.stat-number');
      statElements.forEach((element, _index) => {
        const finalValue = element.textContent;
        const duration = 2000;
        const increment = 50;
        let current = 0;

        const timer = setInterval(
          () => {
            current += increment;
            if (
              current >= parseInt(finalValue) ||
              isNaN(parseInt(finalValue))
            ) {
              element.textContent = finalValue;
              clearInterval(timer);
            } else {
              element.textContent = current.toLocaleString();
            }
          },
          duration / (parseInt(finalValue) / increment) || 50
        );
      });
    },

    // Team theme cycling
    cycleTeamTheme() {
      const themes = ['football', 'basketball', 'baseball', 'hockey'];
      const currentIndex = themes.indexOf(this.currentTheme);
      const nextIndex = (currentIndex + 1) % themes.length;
      this.currentTheme = themes[nextIndex];

      // Apply theme to feature cards
      document.querySelectorAll('.feature-card').forEach(card => {
        card.className = card.className.replace(
          /team-color-\\w+/,
          `team-color-${this.currentTheme}`
        );
      });
    },

    // Particle celebration effect
    setupParticleEffect() {
      // Create particle container if it doesn't exist
      if (!document.querySelector('.celebration-particles')) {
        const container = document.createElement('div');
        container.className = 'celebration-particles';
        document.body.appendChild(container);
      }
    },

    triggerCelebration() {
      this.showParticles = true;
      this.createParticles();

      setTimeout(() => {
        this.showParticles = false;
      }, 3000);
    },

    createParticles() {
      const container = document.querySelector('.celebration-particles');
      const colors = ['#fbbf24', '#f59e0b', '#dc2626', '#16a34a', '#2563eb'];

      for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.backgroundColor =
          colors[Math.floor(Math.random() * colors.length)];
        particle.style.animationDelay = Math.random() * 3 + 's';
        particle.style.animationDuration = Math.random() * 2 + 2 + 's';

        container.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
          if (container.contains(particle)) {
            container.removeChild(particle);
          }
        }, 5000);
      }
    },

    // Intersection Observer for animations
    initIntersectionObserver() {
      const observer = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('animate-fade-in');

              // Trigger specific animations based on element
              if (entry.target.classList.contains('stats-bar')) {
                this.animateStatsCount();
              }

              if (entry.target.classList.contains('hero-logo')) {
                entry.target.classList.add('animate-bounce-gentle');
              }
            }
          });
        },
        {
          threshold: 0.1,
          rootMargin: '0px 0px -100px 0px',
        }
      );

      // Observe elements
      document
        .querySelectorAll('.feature-card, .stats-bar, .hero-logo')
        .forEach(el => {
          observer.observe(el);
        });
    },

    // Enhanced click handlers
    handleFeatureClick(featureName) {
      // Debug info only in development
      if (
        window.location.hostname === 'localhost' ||
        window.location.hostname === '127.0.0.1'
      ) {
        console.info(`Feature clicked: ${featureName}`);
      }

      // Add haptic feedback if supported
      if (navigator.vibrate) {
        navigator.vibrate(50);
      }

      // Track analytics if available
      if (typeof gtag !== 'undefined') {
        gtag('event', 'feature_click', {
          feature_name: featureName,
          page_location: window.location.href,
        });
      }
    },

    // PWA install prompt
    showInstallPrompt() {
      const event = new CustomEvent('show-install-prompt');
      window.dispatchEvent(event);
    },

    // Online/offline detection
    handleOnlineStatus() {
      if (navigator.onLine) {
        this.loadStats(); // Refresh stats when back online
        this.showNotification('Back online! Stats refreshed.', 'success');
      } else {
        this.showNotification('You are currently offline.', 'warning');
      }
    },

    // Notification system
    showNotification(message, type = 'info') {
      const event = new CustomEvent('show-notification', {
        detail: { message, type },
      });
      window.dispatchEvent(event);
    },
  }));

  // Role Comparison Interactive Selector
  Alpine.data('roleComparison', () => ({
    selectedRole: 'customer',
    roles: {
      customer: {
        name: 'Customer',
        icon: '👤',
        price: '$29.99',
        period: '/month',
        features: [
          '7-day free trial',
          '100 tickets/month',
          'Email verification',
          'Optional 2FA',
          'Legal document compliance',
          'Purchase access',
          'Basic monitoring',
        ],
        description: 'Perfect for regular ticket buyers',
        color: 'green',
      },
      agent: {
        name: 'Agent',
        icon: '🏆',
        price: 'Unlimited',
        period: 'Access',
        features: [
          'Unlimited tickets',
          'No subscription required',
          'Advanced monitoring',
          'Performance metrics',
          'Priority support',
          'Automation features',
          'Professional tools',
        ],
        description: 'For ticket professionals & agents',
        color: 'orange',
      },
      admin: {
        name: 'Administrator',
        icon: '👑',
        price: 'Full',
        period: 'Control',
        features: [
          'Complete system access',
          'User management',
          'Financial reports',
          'Analytics dashboard',
          'API management',
          'System configuration',
          'White-label options',
        ],
        description: 'Enterprise administration control',
        color: 'red',
      },
    },

    selectRole(role) {
      this.selectedRole = role;
      // Track analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', 'role_interest', { role_name: role });
      }
    },

    getCurrentRole() {
      return this.roles[this.selectedRole];
    },
  }));

  // Subscription Calculator
  Alpine.data('subscriptionCalculator', () => ({
    billingCycle: 'monthly',
    quantity: 1,
    monthlyPrice: 29.99,
    yearlyPrice: 299.99,

    get totalPrice() {
      if (this.billingCycle === 'monthly') {
        return (this.monthlyPrice * this.quantity).toFixed(2);
      } else {
        return (this.yearlyPrice * this.quantity).toFixed(2);
      }
    },

    get savings() {
      if (this.billingCycle === 'yearly') {
        const yearlyTotal = this.monthlyPrice * 12 * this.quantity;
        const actualYearly = this.yearlyPrice * this.quantity;
        return (yearlyTotal - actualYearly).toFixed(2);
      }
      return 0;
    },

    get savingsPercentage() {
      if (this.billingCycle === 'yearly') {
        const yearlyTotal = this.monthlyPrice * 12 * this.quantity;
        const actualYearly = this.yearlyPrice * this.quantity;
        return Math.round(((yearlyTotal - actualYearly) / yearlyTotal) * 100);
      }
      return 0;
    },
  }));

  // Cookie Consent Banner
  Alpine.data('cookieConsent', () => ({
    show: false,

    init() {
      const consent = localStorage.getItem('cookieConsent');
      if (!consent) {
        setTimeout(() => {
          this.show = true;
        }, 2000);
      }
    },

    acceptCookies() {
      localStorage.setItem('cookieConsent', 'accepted');
      this.show = false;
      this.trackEvent('cookie_consent', 'accepted');
    },

    declineCookies() {
      localStorage.setItem('cookieConsent', 'declined');
      this.show = false;
      this.trackEvent('cookie_consent', 'declined');
    },

    trackEvent(action, value) {
      if (typeof gtag !== 'undefined') {
        gtag('event', action, { custom_parameter: value });
      }
    },
  }));
});

// Global welcome page utilities
window.WelcomePage = {
  // Smooth scroll to elements
  scrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
      element.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      });
    }
  },

  // Copy text to clipboard
  async copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch (err) {
      console.error('Failed to copy text: ', err);
      return false;
    }
  },

  // Share functionality
  async share(data) {
    if (navigator.share) {
      try {
        await navigator.share(data);
        return true;
      } catch (err) {
        console.warn('Error sharing:', err);
      }
    }
    return false;
  },
};

// Online/offline event listeners
window.addEventListener('online', () => {
  document.dispatchEvent(
    new CustomEvent('online-status-change', { detail: true })
  );
});

window.addEventListener('offline', () => {
  document.dispatchEvent(
    new CustomEvent('online-status-change', { detail: false })
  );
});

// Preload critical images
const criticalImages = [
  '/assets/images/hdTicketsLogo.png',
  '/assets/images/hdTicketsLogo.webp',
];

criticalImages.forEach(src => {
  const link = document.createElement('link');
  link.rel = 'preload';
  link.as = 'image';
  link.href = src;
  document.head.appendChild(link);
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
  
  .section-visible {
    opacity: 1;
    transform: translateY(0);
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
