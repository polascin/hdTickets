/**
 * HD Tickets Authentication Security & UX Enhancement Script
 * 
 * Provides security features and user experience enhancements for the login page:
 * - Password visibility toggle with accessibility
 * - CSRF token management for AJAX requests
 * - Form submission tracking and resubmission prevention
 * - Rate limiting UI with countdown timer
 * - Honeypot field protection
 * - Client-side form validation
 * - Loading state management
 * - Accessibility announcements for screen readers
 */

class AuthSecurity {
  constructor() {
    this.form = null;
    this.submitButton = null;
    this.emailField = null;
    this.passwordField = null;
    this.rememberField = null;
    this.honeypotField = null;
    this.statusRegion = null;
    this.alertRegion = null;
    this.isSubmitting = false;
    this.rateLimitTimer = null;

    this.init();
  }

  init() {
    this.setupElements();
    this.setupCSRFHeaders();
    this.setupPasswordToggle();
    this.setupFormValidation();
    this.setupFormSubmission();
    this.setupHoneypotProtection();
    this.setupAccessibilityFeatures();
    this.setupClientTimestamp();
    this.checkRateLimitStatus();
  }

  setupElements() {
    this.form = document.getElementById('login-form');
    this.submitButton = document.getElementById('login-submit-btn');
    this.emailField = document.getElementById('email');
    this.passwordField = document.getElementById('password');
    this.rememberField = document.getElementById('remember_me');
    this.honeypotField = document.querySelector('input[name="website"]');
    this.statusRegion = document.getElementById('hd-status-region');
    this.alertRegion = document.getElementById('hd-alert-region');
  }

  setupCSRFHeaders() {
    // Setup CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
      window.axios = window.axios || {};
      if (window.axios.defaults) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
      }

      // For jQuery if available
      if (window.$) {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': token.getAttribute('content')
          }
        });
      }
    }
  }

  setupPasswordToggle() {
    const toggleButton = document.getElementById('password-toggle');
    const passwordIcon = document.getElementById('password-icon');
    const toggleDescription = document.getElementById('password-toggle-description');

    if (!toggleButton || !this.passwordField) return;

    toggleButton.addEventListener('click', (e) => {
      e.preventDefault();
      this.togglePasswordVisibility();
    });

    // Keyboard support for password toggle
    toggleButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        this.togglePasswordVisibility();
      }
    });
  }

  togglePasswordVisibility() {
    const toggleButton = document.getElementById('password-toggle');
    const passwordIcon = document.getElementById('password-icon');
    const toggleDescription = document.getElementById('password-toggle-description');

    if (this.passwordField.type === 'password') {
      this.passwordField.type = 'text';
      toggleButton.setAttribute('aria-label', 'Hide password');
      if (toggleDescription) {
        toggleDescription.textContent = 'Click to toggle password visibility. Current state: visible';
      }
      if (passwordIcon) {
        passwordIcon.innerHTML = `
                    <title>Password visibility toggle</title>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
      }
      this.announceToScreenReader('Password is now visible');
    } else {
      this.passwordField.type = 'password';
      toggleButton.setAttribute('aria-label', 'Show password');
      if (toggleDescription) {
        toggleDescription.textContent = 'Click to toggle password visibility. Current state: hidden';
      }
      if (passwordIcon) {
        passwordIcon.innerHTML = `
                    <title>Password visibility toggle</title>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.274 4.057-5.065 7-9.543 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
      }
      this.announceToScreenReader('Password is now hidden');
    }
  }

  setupFormValidation() {
    if (!this.emailField || !this.passwordField) return;

    // Real-time email validation
    this.emailField.addEventListener('blur', () => {
      this.validateEmail();
    });

    this.emailField.addEventListener('input', () => {
      this.clearFieldError('email');
    });

    // Password field validation
    this.passwordField.addEventListener('input', () => {
      this.clearFieldError('password');
    });

    // Form-wide validation
    if (this.form) {
      this.form.addEventListener('submit', (e) => {
        if (!this.validateForm()) {
          e.preventDefault();
        }
      });
    }
  }

  validateEmail() {
    const email = this.emailField.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!email) {
      this.showFieldError('email', 'Email address is required');
      return false;
    }

    if (!emailRegex.test(email)) {
      this.showFieldError('email', 'Please enter a valid email address');
      return false;
    }

    this.clearFieldError('email');
    return true;
  }

  validateForm() {
    let isValid = true;

    if (!this.validateEmail()) {
      isValid = false;
    }

    if (!this.passwordField.value.trim()) {
      this.showFieldError('password', 'Password is required');
      isValid = false;
    }

    return isValid;
  }

  showFieldError(fieldName, message) {
    const field = document.getElementById(fieldName);
    const errorContainer = document.getElementById(`${fieldName}-error`);

    if (field) {
      field.setAttribute('aria-invalid', 'true');
      field.classList.add('error');
    }

    if (errorContainer) {
      errorContainer.textContent = message;
      errorContainer.style.display = 'block';
    } else {
      // Create error container if it doesn't exist
      const newErrorContainer = document.createElement('div');
      newErrorContainer.id = `${fieldName}-error`;
      newErrorContainer.className = 'hd-error-message mt-2 text-sm text-red-600';
      newErrorContainer.setAttribute('role', 'alert');
      newErrorContainer.setAttribute('aria-live', 'polite');
      newErrorContainer.innerHTML = `<span class="hd-sr-only">${fieldName} error: </span>${message}`;

      if (field && field.parentNode) {
        field.parentNode.appendChild(newErrorContainer);
      }
    }

    this.announceToScreenReader(`${fieldName} error: ${message}`);
  }

  clearFieldError(fieldName) {
    const field = document.getElementById(fieldName);
    const errorContainer = document.getElementById(`${fieldName}-error`);

    if (field) {
      field.setAttribute('aria-invalid', 'false');
      field.classList.remove('error');
    }

    if (errorContainer) {
      errorContainer.style.display = 'none';
      errorContainer.textContent = '';
    }
  }

  setupFormSubmission() {
    if (!this.form || !this.submitButton) return;

    this.form.addEventListener('submit', (e) => {
      if (this.isSubmitting) {
        e.preventDefault();
        return;
      }

      this.startSubmissionState();
    });
  }

  startSubmissionState() {
    this.isSubmitting = true;

    if (this.submitButton) {
      this.submitButton.disabled = true;
      this.submitButton.setAttribute('aria-label', 'Signing in, please wait');

      // Add loading spinner
      const loadingSpan = this.submitButton.querySelector('span:last-child');
      if (loadingSpan) {
        loadingSpan.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing In...
                `;
      }
    }

    this.announceToScreenReader('Signing in, please wait');

    // Reset submission state after timeout (in case of errors)
    setTimeout(() => {
      this.resetSubmissionState();
    }, 10000);
  }

  resetSubmissionState() {
    this.isSubmitting = false;

    if (this.submitButton) {
      this.submitButton.disabled = false;
      this.submitButton.setAttribute('aria-label', 'Sign in to HD Tickets');

      const loadingSpan = this.submitButton.querySelector('span:last-child');
      if (loadingSpan) {
        loadingSpan.textContent = 'Sign In';
      }
    }
  }

  setupHoneypotProtection() {
    if (!this.honeypotField) return;

    // Monitor honeypot field for bot activity
    this.honeypotField.addEventListener('input', () => {
      // If honeypot field is filled, it's likely a bot
      console.warn('Honeypot field triggered - potential bot detected');
      this.announceToScreenReader('Security validation failed');
    });
  }

  setupAccessibilityFeatures() {
    // Setup focus management
    this.setupFocusManagement();

    // Setup keyboard navigation
    this.setupKeyboardNavigation();
  }

  setupFocusManagement() {
    // Ensure proper focus indicators
    const focusableElements = [
      this.emailField,
      this.passwordField,
      this.rememberField,
      this.submitButton,
      document.getElementById('password-toggle')
    ].filter(el => el);

    focusableElements.forEach(element => {
      element.addEventListener('focus', () => {
        element.classList.add('hd-focused');
      });

      element.addEventListener('blur', () => {
        element.classList.remove('hd-focused');
      });
    });
  }

  setupKeyboardNavigation() {
    // Enhanced keyboard navigation for form
    if (this.form) {
      this.form.addEventListener('keydown', (e) => {
        // Submit form with Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
          if (this.validateForm()) {
            this.form.submit();
          }
        }
      });
    }
  }

  setupClientTimestamp() {
    const timestampField = document.getElementById('client_timestamp');
    if (timestampField) {
      timestampField.value = new Date().toISOString();
    }
  }

  checkRateLimitStatus() {
    // Check if there's a rate limit error and start countdown
    const urlParams = new URLSearchParams(window.location.search);
    const rateLimitSeconds = urlParams.get('rate_limit_seconds');

    if (rateLimitSeconds) {
      this.startRateLimitCountdown(parseInt(rateLimitSeconds));
    }
  }

  startRateLimitCountdown(seconds) {
    if (this.rateLimitTimer) {
      clearInterval(this.rateLimitTimer);
    }

    this.disableForm();

    const countdownElement = this.createCountdownElement();
    let remainingSeconds = seconds;

    this.rateLimitTimer = setInterval(() => {
      remainingSeconds--;

      if (remainingSeconds <= 0) {
        clearInterval(this.rateLimitTimer);
        this.enableForm();
        countdownElement.remove();
        this.announceToScreenReader('Rate limit expired. You may now try logging in again.');
      } else {
        this.updateCountdown(countdownElement, remainingSeconds);
      }
    }, 1000);

    this.announceToScreenReader(`Too many login attempts. Please wait ${seconds} seconds before trying again.`);
  }

  createCountdownElement() {
    const countdownContainer = document.createElement('div');
    countdownContainer.className = 'hd-rate-limit-countdown mt-4 p-4 bg-red-50 border border-red-200 rounded-lg';
    countdownContainer.setAttribute('role', 'status');
    countdownContainer.setAttribute('aria-live', 'polite');

    countdownContainer.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-red-800">Rate Limited</h4>
                    <p class="text-sm text-red-600 mt-1">
                        Please wait <span class="countdown-time font-mono font-bold"></span> before trying again.
                    </p>
                    <div class="mt-2">
                        <div class="w-full bg-red-200 rounded-full h-2">
                            <div class="countdown-progress bg-red-600 h-2 rounded-full transition-all duration-1000 ease-linear"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

    if (this.submitButton && this.submitButton.parentNode) {
      this.submitButton.parentNode.insertBefore(countdownContainer, this.submitButton);
    }

    return countdownContainer;
  }

  updateCountdown(countdownElement, remainingSeconds) {
    const timeSpan = countdownElement.querySelector('.countdown-time');
    const progressBar = countdownElement.querySelector('.countdown-progress');

    if (timeSpan) {
      const minutes = Math.floor(remainingSeconds / 60);
      const seconds = remainingSeconds % 60;
      timeSpan.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    if (progressBar) {
      const totalSeconds = 300; // 5 minutes default
      const progressPercent = ((totalSeconds - remainingSeconds) / totalSeconds) * 100;
      progressBar.style.width = `${progressPercent}%`;
    }
  }

  disableForm() {
    const elements = [this.emailField, this.passwordField, this.rememberField, this.submitButton];
    elements.forEach(element => {
      if (element) {
        element.disabled = true;
      }
    });
  }

  enableForm() {
    const elements = [this.emailField, this.passwordField, this.rememberField, this.submitButton];
    elements.forEach(element => {
      if (element) {
        element.disabled = false;
      }
    });
  }

  announceToScreenReader(message) {
    if (this.statusRegion) {
      this.statusRegion.textContent = message;

      // Clear after a delay to allow for new announcements
      setTimeout(() => {
        this.statusRegion.textContent = '';
      }, 3000);
    }
  }

  announceError(message) {
    if (this.alertRegion) {
      this.alertRegion.textContent = message;

      setTimeout(() => {
        this.alertRegion.textContent = '';
      }, 5000);
    }
  }
}

// Performance monitoring integration
class LoginPerformanceMonitor {
  constructor() {
    this.startTime = performance.now();
    this.init();
  }

  init() {
    this.measurePageLoad();
    this.setupFormPerformanceTracking();
    this.setupNetworkMonitoring();
  }

  measurePageLoad() {
    window.addEventListener('load', () => {
      const loadTime = performance.now() - this.startTime;
      this.logMetric('page_load_time', loadTime);

      // Check Core Web Vitals
      this.measureCoreWebVitals();
    });
  }

  setupFormPerformanceTracking() {
    const form = document.getElementById('login-form');
    if (form) {
      form.addEventListener('submit', () => {
        this.logMetric('form_submission_start', performance.now());
      });
    }
  }

  setupNetworkMonitoring() {
    // Monitor network conditions if available
    if ('connection' in navigator) {
      const connection = navigator.connection;
      this.logMetric('network_type', connection.effectiveType);
      this.logMetric('network_downlink', connection.downlink);
    }
  }

  measureCoreWebVitals() {
    // Largest Contentful Paint (LCP)
    if ('PerformanceObserver' in window) {
      new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        this.logMetric('lcp', lastEntry.startTime);
      }).observe({ entryTypes: ['largest-contentful-paint'] });

      // First Input Delay (FID)
      new PerformanceObserver((list) => {
        const entries = list.getEntries();
        entries.forEach(entry => {
          this.logMetric('fid', entry.processingStart - entry.startTime);
        });
      }).observe({ entryTypes: ['first-input'] });

      // Cumulative Layout Shift (CLS)
      new PerformanceObserver((list) => {
        let clsValue = 0;
        const entries = list.getEntries();
        entries.forEach(entry => {
          if (!entry.hadRecentInput) {
            clsValue += entry.value;
          }
        });
        this.logMetric('cls', clsValue);
      }).observe({ entryTypes: ['layout-shift'] });
    }
  }

  logMetric(name, value) {
    // Send to performance monitoring service
    if (window.console && window.console.log) {
      console.log(`HD Tickets Performance - ${name}:`, value);
    }

    // Send to analytics if available
    if (window.gtag) {
      window.gtag('event', 'timing_complete', {
        'name': name,
        'value': Math.round(value)
      });
    }
  }
}

// Global function for password toggle (referenced in the HTML)
function togglePasswordVisibility(fieldId) {
  if (window.authSecurity) {
    window.authSecurity.togglePasswordVisibility();
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Initialize authentication security
  window.authSecurity = new AuthSecurity();

  // Initialize performance monitoring
  window.loginPerformanceMonitor = new LoginPerformanceMonitor();

  // Accessibility enhancements
  document.body.classList.add('hd-js-enabled');

  // Focus management for better accessibility
  const emailField = document.getElementById('email');
  if (emailField) {
    emailField.focus();
  }
});

// Error handling for graceful degradation
window.addEventListener('error', function (e) {
  console.error('HD Tickets Login Error:', e.error);

  // Ensure form still works even if JavaScript fails
  const form = document.getElementById('login-form');
  if (form) {
    form.style.opacity = '1';
    form.style.pointerEvents = 'auto';
  }
});
