/* App Lifecycle Manager for HD Tickets PWA */

export default class AppLifecycleManager {
  constructor() {
    this.appVersion = '1.0.0';
    this.isOnline = navigator.onLine;
    this.isVisible = !document.hidden;
    this.serviceWorker = null;
    this.updateAvailable = false;
    this.refreshing = false;
    
    this.config = {
      updateCheckInterval: 30 * 60 * 1000, // 30 minutes
      offlineThreshold: 5000, // 5 seconds
      retryDelay: 2000,
      maxRetries: 3
    };
    
    this.lifecycleState = 'loading'; // loading, active, hidden, frozen, terminated
    this.beforeUnloadListeners = new Set();
    this.init();
  }

  init() {
    this.bindEventListeners();
    this.initServiceWorker();
    this.startUpdateChecker();
    this.detectLifecycleState();
    console.log('App Lifecycle Manager initialized');
  }

  bindEventListeners() {
    // Network status tracking
    window.addEventListener('online', () => this.handleOnline());
    window.addEventListener('offline', () => this.handleOffline());
    
    // App visibility tracking
    document.addEventListener('visibilitychange', () => this.handleVisibilityChange());
    
    // Page lifecycle events (modern browsers)
    document.addEventListener('freeze', () => this.handleFreeze());
    document.addEventListener('resume', () => this.handleResume());
    
    // Traditional lifecycle events
    window.addEventListener('beforeunload', (e) => this.handleBeforeUnload(e));
    window.addEventListener('unload', () => this.handleUnload());
    
    // Focus/blur events for additional lifecycle tracking
    window.addEventListener('focus', () => this.handleFocus());
    window.addEventListener('blur', () => this.handleBlur());
    
    // App installation events
    document.addEventListener('install:complete', () => this.handleAppInstalled());
    
    // Update events
    document.addEventListener('update:available', () => this.handleUpdateAvailable());
    document.addEventListener('update:apply', () => this.applyUpdate());
  }

  async initServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      console.warn('Service workers not supported');
      return;
    }

    try {
      const registration = await navigator.serviceWorker.ready;
      this.serviceWorker = registration;
      
      // Listen for service worker updates
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            console.log('New service worker installed, update available');
            this.updateAvailable = true;
            this.showUpdatePrompt();
          }
        });
      });

      // Handle service worker messages
      navigator.serviceWorker.addEventListener('message', (e) => {
        this.handleServiceWorkerMessage(e.data);
      });

      console.log('Service worker initialized');
    } catch (error) {
      console.error('Failed to initialize service worker:', error);
    }
  }

  handleServiceWorkerMessage(message) {
    switch (message.type) {
      case 'UPDATE_AVAILABLE':
        this.updateAvailable = true;
        this.showUpdatePrompt();
        break;
      case 'CACHE_UPDATED':
        console.log('App cache updated');
        break;
      case 'SYNC_COMPLETE':
        document.dispatchEvent(new CustomEvent('lifecycle:sync-complete', { 
          detail: message.data 
        }));
        break;
      default:
        console.log('Service worker message:', message);
    }
  }

  startUpdateChecker() {
    // Check for updates periodically
    setInterval(() => {
      if (this.isOnline && !document.hidden) {
        this.checkForUpdates();
      }
    }, this.config.updateCheckInterval);

    // Check immediately if app becomes visible
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && this.isOnline) {
        setTimeout(() => this.checkForUpdates(), 1000);
      }
    });
  }

  async checkForUpdates() {
    if (!this.serviceWorker) return;

    try {
      await this.serviceWorker.update();
      console.log('Checked for service worker updates');
    } catch (error) {
      console.error('Failed to check for updates:', error);
    }
  }

  detectLifecycleState() {
    // Detect initial lifecycle state
    if (document.hidden) {
      this.lifecycleState = 'hidden';
    } else {
      this.lifecycleState = 'active';
    }
    
    this.notifyLifecycleChange(this.lifecycleState);
  }

  handleOnline() {
    this.isOnline = true;
    console.log('App went online');
    
    document.dispatchEvent(new CustomEvent('lifecycle:online', {
      detail: { timestamp: Date.now() }
    }));
    
    // Trigger sync and refresh when back online
    this.triggerOnlineRecovery();
  }

  handleOffline() {
    this.isOnline = false;
    console.log('App went offline');
    
    document.dispatchEvent(new CustomEvent('lifecycle:offline', {
      detail: { timestamp: Date.now() }
    }));
  }

  handleVisibilityChange() {
    const wasVisible = this.isVisible;
    this.isVisible = !document.hidden;
    
    if (this.isVisible && !wasVisible) {
      // App became visible
      this.lifecycleState = 'active';
      console.log('App became visible/active');
      
      document.dispatchEvent(new CustomEvent('lifecycle:visible', {
        detail: { timestamp: Date.now() }
      }));
      
      this.handleAppResume();
    } else if (!this.isVisible && wasVisible) {
      // App became hidden
      this.lifecycleState = 'hidden';
      console.log('App became hidden');
      
      document.dispatchEvent(new CustomEvent('lifecycle:hidden', {
        detail: { timestamp: Date.now() }
      }));
      
      this.handleAppPause();
    }
    
    this.notifyLifecycleChange(this.lifecycleState);
  }

  handleFreeze() {
    this.lifecycleState = 'frozen';
    console.log('App frozen by browser');
    
    document.dispatchEvent(new CustomEvent('lifecycle:frozen', {
      detail: { timestamp: Date.now() }
    }));
    
    this.saveAppState();
  }

  handleResume() {
    this.lifecycleState = 'active';
    console.log('App resumed from frozen state');
    
    document.dispatchEvent(new CustomEvent('lifecycle:resumed', {
      detail: { timestamp: Date.now() }
    }));
    
    this.restoreAppState();
  }

  handleFocus() {
    if (this.isVisible) {
      this.lifecycleState = 'active';
      this.handleAppResume();
    }
  }

  handleBlur() {
    if (this.isVisible) {
      this.handleAppPause();
    }
  }

  handleBeforeUnload(event) {
    console.log('App about to unload');
    
    // Execute all registered before unload callbacks
    this.beforeUnloadListeners.forEach(callback => {
      try {
        callback(event);
      } catch (error) {
        console.error('Before unload callback error:', error);
      }
    });
    
    // Save critical app state
    this.saveAppState();
    
    // Check if we need to show confirmation dialog
    if (this.shouldShowUnloadConfirmation()) {
      const message = 'You have unsaved changes. Are you sure you want to leave?';
      event.returnValue = message;
      return message;
    }
  }

  handleUnload() {
    this.lifecycleState = 'terminated';
    console.log('App unloaded');
    
    document.dispatchEvent(new CustomEvent('lifecycle:terminated', {
      detail: { timestamp: Date.now() }
    }));
  }

  handleAppInstalled() {
    console.log('App installed successfully');
    
    // Update app behavior for installed state
    this.enableInstallFeatures();
    
    document.dispatchEvent(new CustomEvent('lifecycle:installed', {
      detail: { timestamp: Date.now() }
    }));
  }

  handleAppResume() {
    console.log('App resumed/activated');
    
    // Check for updates when app resumes
    if (this.isOnline) {
      setTimeout(() => this.checkForUpdates(), 500);
    }
    
    // Trigger data refresh
    document.dispatchEvent(new CustomEvent('lifecycle:resume', {
      detail: { 
        timestamp: Date.now(),
        shouldRefresh: this.isOnline 
      }
    }));
  }

  handleAppPause() {
    console.log('App paused/deactivated');
    
    // Save current state
    this.saveAppState();
    
    document.dispatchEvent(new CustomEvent('lifecycle:pause', {
      detail: { timestamp: Date.now() }
    }));
  }

  triggerOnlineRecovery() {
    // Trigger various recovery actions when coming back online
    document.dispatchEvent(new CustomEvent('lifecycle:online-recovery', {
      detail: { timestamp: Date.now() }
    }));
    
    // Request sync and refresh with slight delay to avoid overwhelming
    setTimeout(() => {
      document.dispatchEvent(new CustomEvent('sync:request', {
        detail: { tag: 'online-recovery', force: true }
      }));
    }, 1000);
  }

  saveAppState() {
    try {
      const appState = {
        version: this.appVersion,
        timestamp: Date.now(),
        url: window.location.href,
        scrollPosition: {
          x: window.scrollX,
          y: window.scrollY
        },
        lifecycleState: this.lifecycleState,
        isOnline: this.isOnline
      };
      
      localStorage.setItem('hd_tickets_app_state', JSON.stringify(appState));
      console.log('App state saved');
    } catch (error) {
      console.error('Failed to save app state:', error);
    }
  }

  restoreAppState() {
    try {
      const stored = localStorage.getItem('hd_tickets_app_state');
      if (!stored) return;
      
      const appState = JSON.parse(stored);
      
      // Restore scroll position if on same page
      if (appState.url === window.location.href && appState.scrollPosition) {
        window.scrollTo(appState.scrollPosition.x, appState.scrollPosition.y);
      }
      
      console.log('App state restored');
      
      document.dispatchEvent(new CustomEvent('lifecycle:state-restored', {
        detail: appState
      }));
    } catch (error) {
      console.error('Failed to restore app state:', error);
    }
  }

  shouldShowUnloadConfirmation() {
    // Check if there are unsaved changes or active operations
    const hasUnsavedData = localStorage.getItem('hd_tickets_unsaved_data') === 'true';
    const hasActivePurchases = localStorage.getItem('hd_tickets_active_purchases') === 'true';
    
    return hasUnsavedData || hasActivePurchases;
  }

  enableInstallFeatures() {
    // Enable features only available to installed apps
    document.body.classList.add('app-installed');
    
    // Enable advanced caching
    if (this.serviceWorker) {
      this.serviceWorker.postMessage({
        type: 'ENABLE_INSTALL_FEATURES'
      });
    }
    
    // Show installed app UI elements
    document.querySelectorAll('.show-when-installed').forEach(el => {
      el.style.display = 'block';
    });
  }

  showUpdatePrompt() {
    if (this.refreshing) return;
    
    const updatePrompt = this.createUpdatePromptUI();
    document.body.appendChild(updatePrompt);
    
    // Auto-dismiss after 30 seconds if no action
    setTimeout(() => {
      if (document.contains(updatePrompt)) {
        updatePrompt.remove();
      }
    }, 30000);
  }

  createUpdatePromptUI() {
    const prompt = document.createElement('div');
    prompt.className = 'fixed top-4 right-4 z-50 bg-blue-500 text-white p-4 rounded-lg shadow-lg max-w-sm transform translate-x-full transition-transform duration-300';
    prompt.setAttribute('data-update-prompt', 'true');
    
    prompt.innerHTML = `
      <div class="flex items-start space-x-3">
        <svg class="w-6 h-6 text-blue-200 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-1">
          <p class="font-medium text-sm">Update Available</p>
          <p class="text-xs text-blue-100 mt-1">A new version of HD Tickets is ready. Restart to apply updates.</p>
          <div class="flex space-x-2 mt-3">
            <button data-update-action="apply" class="px-3 py-1 bg-white text-blue-500 text-xs font-medium rounded hover:bg-blue-50 transition-colors">
              Update Now
            </button>
            <button data-update-action="dismiss" class="px-3 py-1 text-blue-100 text-xs font-medium rounded hover:text-white transition-colors">
              Later
            </button>
          </div>
        </div>
        <button data-update-action="close" class="text-blue-200 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    `;
    
    // Animate in
    requestAnimationFrame(() => {
      prompt.classList.remove('translate-x-full');
    });
    
    // Bind actions
    prompt.addEventListener('click', (e) => {
      const action = e.target.closest('[data-update-action]')?.dataset.updateAction;
      
      switch (action) {
        case 'apply':
          this.applyUpdate();
          break;
        case 'dismiss':
        case 'close':
          prompt.classList.add('translate-x-full');
          setTimeout(() => prompt.remove(), 300);
          break;
      }
    });
    
    return prompt;
  }

  async applyUpdate() {
    if (!this.serviceWorker || this.refreshing) return;
    
    this.refreshing = true;
    
    try {
      console.log('Applying app update...');
      
      // Show loading indicator
      this.showUpdateProgress();
      
      // Skip waiting and activate new service worker
      const registration = await navigator.serviceWorker.getRegistration();
      if (registration && registration.waiting) {
        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
      }
      
      // Reload the page to activate new version
      setTimeout(() => {
        window.location.reload();
      }, 1000);
      
    } catch (error) {
      console.error('Failed to apply update:', error);
      this.refreshing = false;
    }
  }

  showUpdateProgress() {
    const progress = document.createElement('div');
    progress.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50';
    progress.innerHTML = `
      <div class="bg-white rounded-lg p-6 max-w-sm mx-4 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="font-medium text-gray-900">Updating HD Tickets...</p>
        <p class="text-sm text-gray-500 mt-1">This will only take a moment</p>
      </div>
    `;
    
    document.body.appendChild(progress);
  }

  handleUpdateAvailable() {
    this.updateAvailable = true;
    this.showUpdatePrompt();
  }

  notifyLifecycleChange(state) {
    document.dispatchEvent(new CustomEvent('lifecycle:state-change', {
      detail: { 
        state, 
        timestamp: Date.now(),
        isOnline: this.isOnline,
        isVisible: this.isVisible
      }
    }));
  }

  // Public API methods
  addBeforeUnloadListener(callback) {
    this.beforeUnloadListeners.add(callback);
  }

  removeBeforeUnloadListener(callback) {
    this.beforeUnloadListeners.delete(callback);
  }

  getAppState() {
    return {
      version: this.appVersion,
      lifecycleState: this.lifecycleState,
      isOnline: this.isOnline,
      isVisible: this.isVisible,
      updateAvailable: this.updateAvailable,
      serviceWorkerReady: !!this.serviceWorker
    };
  }

  markUnsavedChanges(hasChanges = true) {
    localStorage.setItem('hd_tickets_unsaved_data', hasChanges.toString());
  }

  markActivePurchases(hasActive = true) {
    localStorage.setItem('hd_tickets_active_purchases', hasActive.toString());
  }

  forceUpdate() {
    this.applyUpdate();
  }

  destroy() {
    this.beforeUnloadListeners.clear();
    this.refreshing = false;
    console.log('App Lifecycle Manager destroyed');
  }
}
