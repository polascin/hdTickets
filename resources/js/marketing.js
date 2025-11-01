/**
 * Marketing Pages JavaScript
 * Alpine.js utilities for public-facing pages
 */

// Mobile menu state
document.addEventListener('alpine:init', () => {
  Alpine.store('mobileMenu', {
    open: false,
    toggle() {
      this.open = !this.open;
      // Prevent body scroll when menu is open
      document.body.style.overflow = this.open ? 'hidden' : '';
    },
    close() {
      this.open = false;
      document.body.style.overflow = '';
    }
  });

  // FAQ accordion component
  Alpine.data('faqAccordion', () => ({
    activeItem: null,
    toggle(itemId) {
      this.activeItem = this.activeItem === itemId ? null : itemId;
    },
    isOpen(itemId) {
      return this.activeItem === itemId;
    }
  }));

  // Search functionality
  Alpine.data('heroSearch', () => ({
    query: '',
    suggestions: [],
    showSuggestions: false,
    async fetchSuggestions() {
      if (this.query.length < 2) {
        this.suggestions = [];
        this.showSuggestions = false;
        return;
      }

      try {
        // This would connect to your actual search API
        // For now, it's a placeholder
        this.showSuggestions = true;
      } catch (error) {
        console.error('Search suggestions error:', error);
      }
    },
    selectSuggestion(suggestion) {
      this.query = suggestion;
      this.showSuggestions = false;
      this.submitSearch();
    },
    submitSearch() {
      if (this.query.trim()) {
        window.location.href = `/tickets?q=${encodeURIComponent(this.query)}`;
      }
    }
  }));

  // Stats counter animation
  Alpine.data('statsCounter', (target, duration = 2000) => ({
    count: 0,
    target: target,
    init() {
      const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          this.animateCount();
          observer.disconnect();
        }
      });
      observer.observe(this.$el);
    },
    animateCount() {
      const increment = this.target / (duration / 16);
      const timer = setInterval(() => {
        this.count += increment;
        if (this.count >= this.target) {
          this.count = this.target;
          clearInterval(timer);
        }
      }, 16);
    },
    formattedCount() {
      return Math.round(this.count).toLocaleString();
    }
  }));
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;

      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
        // Update URL without page jump
        history.pushState(null, '', href);
      }
    });
  });
});

// Add a class to header on scroll
let lastScroll = 0;
window.addEventListener('scroll', () => {
  const header = document.querySelector('.glass-nav');
  if (!header) return;

  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 100) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
  
  lastScroll = currentScroll;
});

// Close mobile menu on escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (Alpine.store('mobileMenu').open) {
      Alpine.store('mobileMenu').close();
    }
  }
});

// External link indicator (accessibility)
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a[target="_blank"]').forEach(link => {
    if (!link.querySelector('.sr-only')) {
      const srText = document.createElement('span');
      srText.className = 'sr-only';
      srText.textContent = ' (opens in new window)';
      link.appendChild(srText);
    }
  });
});
