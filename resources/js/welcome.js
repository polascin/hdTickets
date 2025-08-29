// Welcome page specific functionality
import Alpine from 'alpinejs';

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
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
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
