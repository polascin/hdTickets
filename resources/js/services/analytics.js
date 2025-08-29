/**
 * HD Tickets Analytics Service
 * Handles Google Analytics 4, custom events, and A/B testing
 */

class AnalyticsService {
  constructor() {
    this.initialized = false;
    this.userId = null;
    this.sessionId = this.generateSessionId();
    this.experimentVariants = {};
    this.conversionFunnels = new Map();

    // Sports-specific event categories
    this.eventCategories = {
      SPORTS: 'sports_interaction',
      TICKETS: 'ticket_interaction',
      USER: 'user_behavior',
      PERFORMANCE: 'performance',
      ERROR: 'error_tracking',
      PWA: 'pwa_interaction',
      THEME: 'theme_change',
      SOCIAL: 'social_sharing',
    };

    this.init();
  }

  /**
   * Initialize analytics services
   */
  async init() {
    try {
      // Initialize Google Analytics 4
      await this.initializeGA4();

      // Initialize custom analytics
      this.initializeCustomAnalytics();

      // Set up performance monitoring
      this.initializePerformanceMonitoring();

      // Set up error tracking
      this.initializeErrorTracking();

      this.initialized = true;
      console.info('✅ HD Tickets Analytics initialized');
    } catch (error) {
      console.error('❌ Analytics initialization failed:', error);
    }
  }

  /**
   * Initialize Google Analytics 4
   */
  async initializeGA4() {
    const GA_MEASUREMENT_ID =
      window.HD_TICKETS_CONFIG?.ga4MeasurementId || 'G-XXXXXXXXXX';

    // Load gtag script
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_MEASUREMENT_ID}`;
    document.head.appendChild(script);

    // Initialize gtag
    window.dataLayer = window.dataLayer || [];
    function gtag() {
      dataLayer.push(arguments);
    }
    window.gtag = gtag;

    gtag('js', new Date());
    gtag('config', GA_MEASUREMENT_ID, {
      // Enhanced ecommerce for ticket tracking
      send_page_view: true,
      allow_google_signals: true,
      allow_ad_personalization_signals: false, // Privacy-focused
      cookie_flags: 'secure;samesite=strict',

      // Custom parameters for HD Tickets
      custom_map: {
        custom_parameter_1: 'sport_type',
        custom_parameter_2: 'team_preference',
        custom_parameter_3: 'price_range',
      },
    });

    // Set user properties
    this.setUserProperties();
  }

  /**
   * Initialize custom analytics for sports-specific tracking
   */
  initializeCustomAnalytics() {
    // Track page load performance
    this.trackPageLoad();

    // Set up conversion funnels
    this.setupConversionFunnels();

    // Track viewport and device info
    this.trackDeviceInfo();

    // Set up sports-specific event listeners
    this.setupSportsEventListeners();
  }

  /**
   * Track custom events with sports context
   */
  trackEvent(eventName, category, parameters = {}) {
    if (!this.initialized) {
      console.warn('Analytics not initialized, queuing event:', eventName);
      return;
    }

    const eventData = {
      event_category: category,
      event_label: parameters.label || '',
      value: parameters.value || 0,
      custom_parameter_1: parameters.sport_type || '',
      custom_parameter_2: parameters.team_preference || '',
      custom_parameter_3: parameters.price_range || '',
      session_id: this.sessionId,
      timestamp: new Date().toISOString(),
      page_location: window.location.href,
      ...parameters,
    };

    // Send to Google Analytics
    if (window.gtag) {
      window.gtag('event', eventName, eventData);
    }

    // Send to custom analytics endpoint
    this.sendCustomEvent(eventName, eventData);

    // Update conversion funnels
    this.updateConversionFunnel(eventName, eventData);

    if (this.debugMode) {
      console.info('📊 Analytics Event:', eventName, eventData);
    }
  }

  /**
   * Sports-specific event tracking methods
   */
  trackSportsInteraction(action, sport, team = null, additionalData = {}) {
    this.trackEvent('sports_interaction', this.eventCategories.SPORTS, {
      action,
      sport_type: sport,
      team_preference: team,
      label: `${sport}${team ? `_${team}` : ''}`,
      ...additionalData,
    });
  }

  trackTicketInteraction(action, ticketData = {}) {
    this.trackEvent('ticket_interaction', this.eventCategories.TICKETS, {
      action,
      sport_type: ticketData.sport,
      price_range: ticketData.priceRange,
      venue: ticketData.venue,
      label: `ticket_${action}`,
      value: ticketData.price || 0,
      ...ticketData,
    });
  }

  trackUserBehavior(action, details = {}) {
    this.trackEvent('user_behavior', this.eventCategories.USER, {
      action,
      label: `user_${action}`,
      ...details,
    });
  }

  trackFeatureUsage(featureName, interactionType = 'click', details = {}) {
    this.trackEvent('feature_usage', this.eventCategories.USER, {
      feature_name: featureName,
      interaction_type: interactionType,
      label: `feature_${featureName}`,
      ...details,
    });
  }

  trackThemeChange(fromTheme, toTheme) {
    this.trackEvent('theme_change', this.eventCategories.THEME, {
      from_theme: fromTheme,
      to_theme: toTheme,
      label: `${fromTheme}_to_${toTheme}`,
    });
  }

  trackSocialShare(platform, content = '') {
    this.trackEvent('social_share', this.eventCategories.SOCIAL, {
      platform,
      content_type: content,
      label: `share_${platform}`,
    });
  }

  trackPWAInteraction(action, details = {}) {
    this.trackEvent('pwa_interaction', this.eventCategories.PWA, {
      action,
      label: `pwa_${action}`,
      ...details,
    });
  }

  /**
   * Performance monitoring
   */
  initializePerformanceMonitoring() {
    // Track Core Web Vitals
    this.trackWebVitals();

    // Track custom performance metrics
    this.trackCustomPerformance();
  }

  trackWebVitals() {
    // Largest Contentful Paint (LCP)
    new PerformanceObserver(entryList => {
      for (const entry of entryList.getEntries()) {
        if (entry.entryType === 'largest-contentful-paint') {
          this.trackEvent('web_vitals_lcp', this.eventCategories.PERFORMANCE, {
            value: Math.round(entry.startTime),
            label: 'largest_contentful_paint',
          });
        }
      }
    }).observe({ entryTypes: ['largest-contentful-paint'] });

    // First Input Delay (FID)
    new PerformanceObserver(entryList => {
      for (const entry of entryList.getEntries()) {
        if (entry.entryType === 'first-input') {
          this.trackEvent('web_vitals_fid', this.eventCategories.PERFORMANCE, {
            value: Math.round(entry.processingStart - entry.startTime),
            label: 'first_input_delay',
          });
        }
      }
    }).observe({ entryTypes: ['first-input'] });

    // Cumulative Layout Shift (CLS)
    let clsValue = 0;
    new PerformanceObserver(entryList => {
      for (const entry of entryList.getEntries()) {
        if (!entry.hadRecentInput) {
          clsValue += entry.value;
        }
      }
    }).observe({ entryTypes: ['layout-shift'] });

    // Send CLS on page unload
    window.addEventListener('beforeunload', () => {
      this.trackEvent('web_vitals_cls', this.eventCategories.PERFORMANCE, {
        value: Math.round(clsValue * 1000),
        label: 'cumulative_layout_shift',
      });
    });
  }

  trackCustomPerformance() {
    // Track API response times
    this.interceptFetch();

    // Track component load times
    this.trackComponentPerformance();
  }

  /**
   * A/B Testing functionality
   */
  initializeABTesting() {
    this.loadExperiments();
    this.assignUserToVariants();
  }

  loadExperiments() {
    // Define active experiments
    this.experiments = {
      hero_design: {
        variants: ['original', 'sports_focused', 'minimal'],
        weights: [34, 33, 33],
        active: true,
      },
      cta_button_text: {
        variants: ['Get Started', 'Start Monitoring', 'Find Tickets'],
        weights: [34, 33, 33],
        active: true,
      },
      stats_display: {
        variants: ['horizontal', 'vertical', 'cards'],
        weights: [34, 33, 33],
        active: true,
      },
    };
  }

  assignUserToVariants() {
    Object.keys(this.experiments).forEach(experimentKey => {
      const experiment = this.experiments[experimentKey];
      if (experiment.active) {
        const variant = this.getVariantForUser(experimentKey, experiment);
        this.experimentVariants[experimentKey] = variant;

        // Track assignment
        this.trackEvent('ab_test_assignment', 'experiment', {
          experiment_name: experimentKey,
          variant_name: variant,
          label: `${experimentKey}_${variant}`,
        });
      }
    });
  }

  getVariantForUser(experimentKey, experiment) {
    // Use user ID or session ID for consistent assignment
    const userId = this.userId || this.sessionId;
    const hash = this.hashString(userId + experimentKey);
    const bucket = hash % 100;

    let cumulativeWeight = 0;
    for (let i = 0; i < experiment.variants.length; i++) {
      cumulativeWeight += experiment.weights[i];
      if (bucket < cumulativeWeight) {
        return experiment.variants[i];
      }
    }

    return experiment.variants[0]; // Fallback
  }

  getVariant(experimentKey) {
    return this.experimentVariants[experimentKey] || null;
  }

  trackConversion(experimentKey, conversionType = 'goal', value = 1) {
    const variant = this.getVariant(experimentKey);
    if (variant) {
      this.trackEvent('ab_test_conversion', 'experiment', {
        experiment_name: experimentKey,
        variant_name: variant,
        conversion_type: conversionType,
        value: value,
        label: `${experimentKey}_${variant}_${conversionType}`,
      });
    }
  }

  /**
   * Error tracking
   */
  initializeErrorTracking() {
    window.addEventListener('error', event => {
      this.trackError('javascript_error', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        stack: event.error?.stack,
      });
    });

    window.addEventListener('unhandledrejection', event => {
      this.trackError('promise_rejection', {
        reason: event.reason?.toString(),
        stack: event.reason?.stack,
      });
    });
  }

  trackError(errorType, errorData) {
    this.trackEvent('error_occurred', this.eventCategories.ERROR, {
      error_type: errorType,
      error_message: errorData.message || errorData.reason,
      error_stack: errorData.stack,
      label: `error_${errorType}`,
      ...errorData,
    });
  }

  /**
   * Conversion funnel tracking
   */
  setupConversionFunnels() {
    this.conversionFunnels.set('user_registration', {
      steps: [
        'welcome_view',
        'signup_click',
        'form_start',
        'form_submit',
        'registration_complete',
      ],
      currentStep: 0,
    });

    this.conversionFunnels.set('ticket_purchase', {
      steps: [
        'ticket_view',
        'ticket_click',
        'purchase_intent',
        'checkout_start',
        'purchase_complete',
      ],
      currentStep: 0,
    });
  }

  updateConversionFunnel(eventName, _eventData) {
    this.conversionFunnels.forEach((funnel, funnelName) => {
      const stepIndex = funnel.steps.indexOf(eventName);
      if (stepIndex !== -1 && stepIndex === funnel.currentStep) {
        funnel.currentStep = stepIndex + 1;

        this.trackEvent('funnel_step', 'conversion', {
          funnel_name: funnelName,
          step_name: eventName,
          step_number: stepIndex + 1,
          label: `${funnelName}_step_${stepIndex + 1}`,
        });
      }
    });
  }

  /**
   * Utility methods
   */
  generateSessionId() {
    return (
      'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
    );
  }

  hashString(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = (hash << 5) - hash + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return Math.abs(hash);
  }

  setUserId(userId) {
    this.userId = userId;
    if (window.gtag) {
      window.gtag('config', 'GA_MEASUREMENT_ID', {
        user_id: userId,
      });
    }
  }

  setUserProperties(properties = {}) {
    if (window.gtag) {
      window.gtag('set', {
        user_properties: {
          sport_preference: properties.sportPreference || 'not_set',
          user_tier: properties.userTier || 'basic',
          registration_date: properties.registrationDate || 'unknown',
          ...properties,
        },
      });
    }
  }

  /**
   * Send events to custom analytics endpoint
   */
  async sendCustomEvent(eventName, eventData) {
    try {
      const response = await fetch('/api/v1/analytics/event', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          event: eventName,
          data: {
            event_category:
              this.eventCategories[eventData.category] || 'general',
            ...eventData,
            session_id: this.sessionId,
            user_agent: navigator.userAgent,
            page_url: window.location.href,
          },
          timestamp: new Date().toISOString(),
        }),
      });

      if (!response.ok) {
        console.warn(
          `Analytics event failed: ${response.status} ${response.statusText}`
        );
      }
    } catch (error) {
      console.error('Failed to send custom analytics event:', error);
    }
  }

  /**
   * Track specific page and component events
   */
  trackPageLoad() {
    this.trackEvent('page_view', this.eventCategories.USER, {
      page_title: document.title,
      page_location: window.location.href,
      referrer: document.referrer,
    });
  }

  trackDeviceInfo() {
    this.trackEvent('device_info', this.eventCategories.USER, {
      viewport_width: window.innerWidth,
      viewport_height: window.innerHeight,
      screen_width: screen.width,
      screen_height: screen.height,
      device_pixel_ratio: window.devicePixelRatio,
      user_agent: navigator.userAgent,
      language: navigator.language,
    });
  }

  setupSportsEventListeners() {
    // Auto-track clicks on sports-related elements
    document.addEventListener('click', event => {
      const target = event.target.closest(
        '[data-sport], [data-team], [data-analytics]'
      );
      if (target) {
        const sport = target.dataset.sport;
        const team = target.dataset.team;
        const analyticsAction = target.dataset.analytics;

        if (sport || team) {
          this.trackSportsInteraction(analyticsAction || 'click', sport, team, {
            element_type: target.tagName.toLowerCase(),
            element_text: target.textContent?.trim(),
            element_id: target.id,
          });
        }
      }
    });
  }

  interceptFetch() {
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
      const startTime = performance.now();
      try {
        const response = await originalFetch(...args);
        const endTime = performance.now();

        this.trackEvent('api_request', this.eventCategories.PERFORMANCE, {
          url: args[0],
          method: args[1]?.method || 'GET',
          status: response.status,
          duration: Math.round(endTime - startTime),
          label: `api_${response.status}`,
        });

        return response;
      } catch (error) {
        const endTime = performance.now();

        this.trackEvent('api_error', this.eventCategories.ERROR, {
          url: args[0],
          method: args[1]?.method || 'GET',
          duration: Math.round(endTime - startTime),
          error_message: error.message,
          label: 'api_error',
        });

        throw error;
      }
    };
  }

  trackComponentPerformance() {
    // Track Alpine.js component initialization
    document.addEventListener('alpine:init', () => {
      this.trackEvent('alpine_init', this.eventCategories.PERFORMANCE, {
        label: 'alpine_components_initialized',
      });
    });
  }
}

// Initialize analytics service
window.HDAnalytics = new AnalyticsService();

export default AnalyticsService;
