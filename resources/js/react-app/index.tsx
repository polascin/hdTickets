/**
 * HD Tickets - React Application Entry Point
 * Bootstrap and initialize the modern React sports ticketing application
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

// Import global styles
import '../../css/app.css';

// Initialize application
function initializeApp() {
  const container = document.getElementById('react-app-root');
  
  if (!container) {
    console.error('React application root element not found. Make sure there is an element with id="react-app-root" in the HTML.');
    return;
  }

  const root = createRoot(container);
  
  root.render(<App />);
  
  console.log('🚀 HD Tickets React Application initialized successfully');
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeApp);
} else {
  initializeApp();
}

// Hot module replacement for development
if (import.meta.hot) {
  import.meta.hot.accept('./App', () => {
    console.log('🔥 React App hot reloaded');
    initializeApp();
  });
}

// Export for manual initialization
export { initializeApp };