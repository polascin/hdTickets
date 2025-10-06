import Alpine from 'alpinejs';
import AppLifecycleManager from './app-lifecycle-manager';
import AppShellNav from './app-shell-nav';
import AutoRefreshManager from './auto-refresh-manager';
import BackgroundSyncManager from './background-sync-manager';
import './bootstrap';
import './echo';
import InstallPromptManager from './install-prompt-manager';

window.addEventListener('DOMContentLoaded', () => {
  try {
    // Mark JS-enabled state (for a11y and CSS hooks)
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');

    // Initialize native-like navigation
    const nav = new AppShellNav({
      contentSelector: 'main',
      transition: 'fade',
    });
    window.__appShellNav = nav;

    // Initialize background sync for PWA
    const syncManager = new BackgroundSyncManager();
    window.__syncManager = syncManager;

    // Initialize auto-refresh for real-time updates
    const refreshManager = new AutoRefreshManager();
    window.__refreshManager = refreshManager;

    // Initialize install prompt manager
    const installManager = new InstallPromptManager();
    window.__installManager = installManager;

    // Initialize app lifecycle manager
    const lifecycleManager = new AppLifecycleManager();
    window.__lifecycleManager = lifecycleManager;

    // Setup global event handlers
    setupEventHandlers(
      syncManager,
      refreshManager,
      installManager,
      lifecycleManager
    );

    // HD Tickets PWA initialized successfully
  } catch (error) {
    console.error('Failed to initialize HD Tickets PWA:', error);
  }
});

function setupEventHandlers(
  syncManager,
  refreshManager,
  installManager,
  lifecycleManager
) {
  // Handle data changes that need syncing
  document.addEventListener('data:changed', e => {
    syncManager.scheduleSync(e.detail.type, e.detail.data);
  });

  // Handle manual refresh requests
  document.addEventListener('refresh:request', e => {
    refreshManager.forceRefresh(e.detail.type);
  });

  // Handle sync success notifications
  document.addEventListener('sync:success', (_e) => {
    // Show success notification if notifications system is available
    if (window.showNotification) {
      window.showNotification('Data synced successfully', 'success');
    }
  });

  // Handle offline sync retry
  document.addEventListener('offline:retry-sync', () => {
    if (navigator.onLine) {
      syncManager.forceSyncAll();
      refreshManager.forceRefresh();
    }
  });

  // Handle app focus for immediate refresh
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && navigator.onLine) {
      refreshManager.forceRefresh();
    }
  });

  // Handle install prompts and app lifecycle
  document.addEventListener('beforeinstallprompt', e => {
    document.dispatchEvent(
      new CustomEvent('install:prompt-available', { detail: e })
    );
  });

  document.addEventListener('appinstalled', () => {
    if (window.showNotification) {
      window.showNotification('HD Tickets installed successfully!', 'success');
    }
  });

  // Handle lifecycle state changes
  document.addEventListener('lifecycle:state-change', e => {
    // App lifecycle state changed

    // Pause/resume managers based on lifecycle
    if (e.detail.state === 'hidden' || e.detail.state === 'frozen') {
      refreshManager.pauseNonCriticalRefreshers();
    } else if (e.detail.state === 'active') {
      refreshManager.resumeAllRefreshers();
      if (e.detail.isOnline) {
        syncManager.forceSyncAll();
      }
    }
  });

  // Handle high-value actions for install prompts
  const trackHighValueAction = action => {
    document.dispatchEvent(
      new CustomEvent(`${action}:completed`, {
        detail: { timestamp: Date.now() },
      })
    );
  };

  // Track user interactions that matter for install prompts
  document.addEventListener('submit', e => {
    if (e.target.closest('[data-track="purchase"]')) {
      trackHighValueAction('ticket:purchased');
    } else if (e.target.closest('[data-track="alert"]')) {
      trackHighValueAction('alert:created');
    } else if (e.target.closest('[data-track="watchlist"]')) {
      trackHighValueAction('watchlist:added');
    }
  });

  // Handle unsaved data warnings
  let _hasUnsavedData = false;
  document.addEventListener('data:changed', e => {
    if (e.detail.hasUnsavedChanges) {
      _hasUnsavedData = true;
      lifecycleManager.markUnsavedChanges(true);
    } else {
      _hasUnsavedData = false;
      lifecycleManager.markUnsavedChanges(false);
    }
  });

  // Handle active purchases for unload warnings
  document.addEventListener('purchase:started', () => {
    lifecycleManager.markActivePurchases(true);
  });

  document.addEventListener('purchase:completed', () => {
    lifecycleManager.markActivePurchases(false);
  });

  // Global error handling
  window.addEventListener('error', e => {
    console.error('Global error:', e.error);
    // Could send to error tracking service
  });

  window.addEventListener('unhandledrejection', e => {
    console.error('Unhandled promise rejection:', e.reason);
    // Could send to error tracking service
  });
}

// Make Alpine available globally
window.Alpine = Alpine;

// Framework initialization (conditional loading)
if (document.querySelector('[data-react-component]')) {
  import('./frameworks/react/index.tsx');
}

if (document.querySelector('[data-vue-component]')) {
  import('./frameworks/vue/index.ts');
}

if (document.querySelector('[data-angular-component]')) {
  import('./frameworks/angular/index.ts');
}

// Initialize shared utilities for all frameworks
import('./frameworks/shared/index.ts').then(({ initSharedUtils }) => {
  initSharedUtils();
});

// Alpine components
import './components/charts';
import './components/navigation';
import './components/notification-system';
import './components/real-time-stats';
import './components/theme-switcher';

// Utility components
import './utils/containerQueries';
import './utils/gridLayout';
import './utils/performanceOptimizer';
import './utils/touchSupport';

// Migrated legacy utilities (from public)
import './migrated/accessibility.js';
import './migrated/assets/feature-detection.js';
import './migrated/assets/lazy-loading.js';
import './migrated/assets/performance-monitor.js';
import './migrated/pwa-manager.js';

// Ticket system components (conditional loading)
if (document.querySelector('[data-ticket-system]')) {
  import('./tickets/index.js');
}

// Initialize Alpine
Alpine.start();

// Service Worker Registration for PWA (single source)
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    if (!navigator.serviceWorker.controller) {
      navigator.serviceWorker
        .register('/sw.js')
        .then(registration => {
          console.info('SW registered: ', registration);
        })
        .catch(registrationError => {
          console.error('SW registration failed: ', registrationError);
        });
    }
  });
}

// Install prompt for PWA (single handler)
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  document.dispatchEvent(new CustomEvent('pwa:install-available'));
});

// Optional: hook up a global install button if present
document.addEventListener('pwa:install', () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.finally(() => {
      deferredPrompt = null;
    });
  }
});

// Global error handling
window.addEventListener('error', event => {
  console.error('Global error:', event.error);
  // You could send this to a logging service
});

window.addEventListener('unhandledrejection', event => {
  console.error('Unhandled promise rejection:', event.reason);
  // You could send this to a logging service
});
