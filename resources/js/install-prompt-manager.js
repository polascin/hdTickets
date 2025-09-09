/* Smart Install Prompt Manager for HD Tickets PWA */

export default class InstallPromptManager {
  constructor() {
    this.deferredPrompt = null;
    this.isInstalled = false;
    this.installPromptShown = false;
    this.config = {
      // Criteria for showing install prompt
      minVisits: 3,
      minTimeOnSite: 60000, // 1 minute
      minInteractions: 5,
      cooldownPeriod: 7 * 24 * 60 * 60 * 1000, // 7 days
      // Event thresholds
      purchaseThreshold: 1,
      alertThreshold: 2,
      watchlistThreshold: 3
    };
    
    this.userStats = this.loadUserStats();
    this.sessionStart = Date.now();
    this.interactionCount = 0;
    this.init();
  }

  init() {
    this.detectInstallState();
    this.bindEventListeners();
    this.trackUserEngagement();
    console.log('Install Prompt Manager initialized');
  }

  detectInstallState() {
    // Check if already installed via multiple methods
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
      this.isInstalled = true;
      console.log('PWA is running in standalone mode');
    }
    
    if (window.navigator.standalone === true) {
      this.isInstalled = true;
      console.log('PWA is running in iOS standalone mode');
    }
    
    if (document.referrer.startsWith('android-app://')) {
      this.isInstalled = true;
      console.log('PWA is running via Android app intent');
    }

    // Check localStorage flag for manual tracking
    if (localStorage.getItem('hd_tickets_installed') === 'true') {
      this.isInstalled = true;
    }
  }

  bindEventListeners() {
    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
      console.log('Install prompt available');
      e.preventDefault();
      this.deferredPrompt = e;
      this.evaluateInstallPrompt();
    });

    // Listen for appinstalled event
    window.addEventListener('appinstalled', () => {
      console.log('PWA installed successfully');
      this.isInstalled = true;
      localStorage.setItem('hd_tickets_installed', 'true');
      this.deferredPrompt = null;
      this.onInstallComplete();
    });

    // Track user interactions
    const interactionEvents = ['click', 'touchstart', 'keydown', 'scroll'];
    interactionEvents.forEach(event => {
      document.addEventListener(event, () => this.trackInteraction(), { passive: true });
    });

    // Track page visibility changes
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        this.trackVisit();
      }
    });

    // Track high-value actions
    document.addEventListener('ticket:purchased', () => this.trackHighValueAction('purchase'));
    document.addEventListener('alert:created', () => this.trackHighValueAction('alert'));
    document.addEventListener('watchlist:added', () => this.trackHighValueAction('watchlist'));
    
    // Listen for manual install triggers
    document.addEventListener('install:request', () => this.showInstallPrompt());
    document.addEventListener('install:dismiss', () => this.dismissInstallPrompt());
  }

  trackUserEngagement() {
    // Update visit count
    this.userStats.visits++;
    this.userStats.lastVisit = Date.now();
    this.saveUserStats();
    
    // Track session time
    setInterval(() => {
      this.userStats.totalTimeOnSite += 30000; // Add 30 seconds
      this.saveUserStats();
    }, 30000);
  }

  trackInteraction() {
    this.interactionCount++;
    this.userStats.totalInteractions++;
    this.saveUserStats();
  }

  trackVisit() {
    this.userStats.visits++;
    this.saveUserStats();
  }

  trackHighValueAction(type) {
    if (!this.userStats.highValueActions[type]) {
      this.userStats.highValueActions[type] = 0;
    }
    this.userStats.highValueActions[type]++;
    this.userStats.lastHighValueAction = Date.now();
    this.saveUserStats();
    
    // High-value actions can trigger immediate install prompt evaluation
    setTimeout(() => this.evaluateInstallPrompt(), 2000);
  }

  async evaluateInstallPrompt() {
    if (this.isInstalled || this.installPromptShown || !this.deferredPrompt) {
      return false;
    }

    const criteria = this.calculateInstallCriteria();
    console.log('Install criteria evaluation:', criteria);

    if (criteria.shouldShow) {
      await this.showSmartInstallPrompt(criteria.reason);
      return true;
    }

    return false;
  }

  calculateInstallCriteria() {
    const stats = this.userStats;
    const sessionTime = Date.now() - this.sessionStart;
    
    // Check if in cooldown period
    if (stats.lastPromptDismissed && 
        (Date.now() - stats.lastPromptDismissed) < this.config.cooldownPeriod) {
      return { shouldShow: false, reason: 'cooldown' };
    }

    // High-value action triggers
    if (stats.highValueActions.purchase >= this.config.purchaseThreshold) {
      return { shouldShow: true, reason: 'purchase_activity' };
    }

    if (stats.highValueActions.alert >= this.config.alertThreshold) {
      return { shouldShow: true, reason: 'alert_engagement' };
    }

    if (stats.highValueActions.watchlist >= this.config.watchlistThreshold) {
      return { shouldShow: true, reason: 'watchlist_usage' };
    }

    // Regular engagement criteria
    const engagementScore = (
      (stats.visits >= this.config.minVisits ? 1 : 0) +
      (stats.totalTimeOnSite >= this.config.minTimeOnSite ? 1 : 0) +
      (this.interactionCount >= this.config.minInteractions ? 1 : 0) +
      (sessionTime >= this.config.minTimeOnSite ? 1 : 0)
    );

    if (engagementScore >= 3) {
      return { shouldShow: true, reason: 'engagement_threshold' };
    }

    return { shouldShow: false, reason: 'insufficient_engagement', score: engagementScore };
  }

  async showSmartInstallPrompt(reason = 'engagement') {
    if (!this.deferredPrompt) return false;

    this.installPromptShown = true;
    
    try {
      // Create custom install prompt UI
      const promptUI = this.createInstallPromptUI(reason);
      document.body.appendChild(promptUI);
      
      // Track that we showed a prompt
      this.userStats.promptsShown++;
      this.userStats.lastPromptShown = Date.now();
      this.saveUserStats();
      
      return true;
    } catch (error) {
      console.error('Failed to show install prompt:', error);
      return false;
    }
  }

  createInstallPromptUI(reason) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm';
    overlay.setAttribute('data-install-prompt', 'true');
    
    const reasonMessages = {
      purchase_activity: 'You\'re actively using HD Tickets for purchases! Install the app for faster checkout and real-time alerts.',
      alert_engagement: 'Stay on top of price drops! Install HD Tickets for instant notifications even when your browser is closed.',
      watchlist_usage: 'Your watchlist is growing! Get the HD Tickets app for quick access and background monitoring.',
      engagement_threshold: 'You\'re getting great value from HD Tickets! Install the app for the best experience.',
      default: 'Get instant access to sports ticket deals! Install HD Tickets for real-time alerts and faster browsing.'
    };

    const message = reasonMessages[reason] || reasonMessages.default;

    overlay.innerHTML = `
      <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md mx-4 p-6 transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95" data-prompt-content>
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <img src="/images/icons/icon-96x96.png" alt="HD Tickets" class="w-16 h-16 rounded-2xl shadow-lg">
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Install HD Tickets</h3>
            <p class="text-sm text-gray-600 mb-4">${message}</p>
            
            <div class="flex flex-col space-y-2">
              <button data-install-action="install" class="w-full bg-indigo-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <span class="flex items-center justify-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add to Home Screen
                </span>
              </button>
              <button data-install-action="dismiss" class="w-full text-gray-500 px-4 py-2 rounded-lg font-medium hover:text-gray-700 transition-colors">
                Not now
              </button>
            </div>
          </div>
          
          <button data-install-action="close" class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mt-4 flex items-center space-x-4 text-xs text-gray-500">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span>Works offline</span>
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path>
            </svg>
            <span>Push notifications</span>
          </div>
        </div>
      </div>
    `;

    // Animate in
    requestAnimationFrame(() => {
      const content = overlay.querySelector('[data-prompt-content]');
      content.classList.remove('translate-y-full', 'sm:scale-95');
      content.classList.add('translate-y-0', 'sm:scale-100');
    });

    // Bind actions
    this.bindInstallPromptActions(overlay);
    
    return overlay;
  }

  bindInstallPromptActions(overlay) {
    overlay.addEventListener('click', (e) => {
      const action = e.target.closest('[data-install-action]')?.dataset.installAction;
      
      switch (action) {
        case 'install':
          this.executeInstall();
          break;
        case 'dismiss':
          this.dismissInstallPrompt(false);
          break;
        case 'close':
          this.dismissInstallPrompt(true);
          break;
      }
      
      // Close on backdrop click
      if (e.target === overlay) {
        this.dismissInstallPrompt(true);
      }
    });
  }

  async executeInstall() {
    if (!this.deferredPrompt) return;

    try {
      const result = await this.deferredPrompt.prompt();
      console.log('Install prompt result:', result);
      
      this.userStats.installAttempts++;
      
      if (result.outcome === 'accepted') {
        this.userStats.installAccepted = true;
        this.userStats.installDate = Date.now();
        console.log('User accepted the install prompt');
      } else {
        console.log('User dismissed the install prompt');
      }
      
      this.deferredPrompt = null;
      this.saveUserStats();
      this.removeInstallPromptUI();
      
    } catch (error) {
      console.error('Install prompt error:', error);
      this.removeInstallPromptUI();
    }
  }

  dismissInstallPrompt(permanent = false) {
    this.userStats.promptsDismissed++;
    this.userStats.lastPromptDismissed = Date.now();
    
    if (permanent) {
      this.userStats.installPermanentlyDismissed = true;
    }
    
    this.saveUserStats();
    this.removeInstallPromptUI();
  }

  removeInstallPromptUI() {
    const prompt = document.querySelector('[data-install-prompt]');
    if (prompt) {
      const content = prompt.querySelector('[data-prompt-content]');
      if (content) {
        content.classList.add('translate-y-full', 'sm:scale-95');
        setTimeout(() => prompt.remove(), 300);
      } else {
        prompt.remove();
      }
    }
    this.installPromptShown = false;
  }

  showInstallPrompt() {
    if (this.deferredPrompt && !this.isInstalled && !this.installPromptShown) {
      this.showSmartInstallPrompt('manual');
    }
  }

  onInstallComplete() {
    // Show thank you message
    this.showInstallThankYou();
    
    // Track installation analytics
    if (window.gtag) {
      gtag('event', 'app_install', {
        method: 'pwa',
        content_type: 'sports_tickets'
      });
    }
    
    // Enable push notifications prompt after short delay
    setTimeout(() => {
      document.dispatchEvent(new CustomEvent('install:complete'));
    }, 2000);
  }

  showInstallThankYou() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white p-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300';
    notification.innerHTML = `
      <div class="flex items-center space-x-3">
        <svg class="w-6 h-6 text-green-100" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <p class="font-medium">HD Tickets installed!</p>
          <p class="text-sm text-green-100">Access from your home screen</p>
        </div>
      </div>
    `;
    
    document.body.appendChild(notification);
    
    requestAnimationFrame(() => {
      notification.classList.remove('translate-x-full');
    });
    
    setTimeout(() => {
      notification.classList.add('translate-x-full');
      setTimeout(() => notification.remove(), 300);
    }, 5000);
  }

  loadUserStats() {
    const defaultStats = {
      visits: 0,
      totalTimeOnSite: 0,
      totalInteractions: 0,
      highValueActions: {
        purchase: 0,
        alert: 0,
        watchlist: 0
      },
      promptsShown: 0,
      promptsDismissed: 0,
      installAttempts: 0,
      installAccepted: false,
      installPermanentlyDismissed: false,
      firstVisit: Date.now(),
      lastVisit: null,
      lastPromptShown: null,
      lastPromptDismissed: null,
      lastHighValueAction: null,
      installDate: null
    };

    try {
      const stored = localStorage.getItem('hd_tickets_install_stats');
      return stored ? { ...defaultStats, ...JSON.parse(stored) } : defaultStats;
    } catch (error) {
      console.error('Failed to load install stats:', error);
      return defaultStats;
    }
  }

  saveUserStats() {
    try {
      localStorage.setItem('hd_tickets_install_stats', JSON.stringify(this.userStats));
    } catch (error) {
      console.error('Failed to save install stats:', error);
    }
  }

  // Public API methods
  canShowInstallPrompt() {
    return !this.isInstalled && !!this.deferredPrompt && !this.installPromptShown &&
           !this.userStats.installPermanentlyDismissed;
  }

  getInstallStats() {
    return {
      isInstalled: this.isInstalled,
      canInstall: this.canShowInstallPrompt(),
      userStats: this.userStats,
      sessionInteractions: this.interactionCount,
      sessionTime: Date.now() - this.sessionStart
    };
  }

  forceShowPrompt() {
    if (this.canShowInstallPrompt()) {
      this.showSmartInstallPrompt('manual');
    }
  }

  resetInstallStats() {
    localStorage.removeItem('hd_tickets_install_stats');
    localStorage.removeItem('hd_tickets_installed');
    this.userStats = this.loadUserStats();
    console.log('Install stats reset');
  }

  destroy() {
    this.deferredPrompt = null;
    this.removeInstallPromptUI();
    console.log('Install Prompt Manager destroyed');
  }
}
