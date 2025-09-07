import Alpine from 'alpinejs';
import './bootstrap';
import './echo';

// Make Alpine available globally
window.Alpine = Alpine;

// Alpine components
import './components/charts';
import './components/navigation';
import './components/notification-system';
import './components/real-time-stats';
import './components/theme-switcher';

// Utility components
import './utils/containerQueries';
import './utils/gridLayout';
import './utils/touchSupport';
import './utils/performanceOptimizer';

// Ticket system components (conditional loading)
if (document.querySelector('[data-ticket-system]')) {
    import('./tickets/index.js');
}

// Initialize Alpine
Alpine.start();

// Service Worker Registration for PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/sw.js')
      .then(registration => {
        console.info('SW registered: ', registration);
      })
      .catch(registrationError => {
        console.error('SW registration failed: ', registrationError);
      });
  });
}

// Install prompt for PWA
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;

  // Show install button
  const installBtn = document.getElementById('install-app-btn');
  if (installBtn) {
    installBtn.style.display = 'block';
    installBtn.addEventListener('click', () => {
      installBtn.style.display = 'none';
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(choiceResult => {
        if (choiceResult.outcome === 'accepted') {
          console.info('User accepted the install prompt');
        } else {
          console.warn('User dismissed the install prompt');
        }
        deferredPrompt = null;
      });
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
