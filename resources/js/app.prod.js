import './bootstrap';
import Alpine from 'alpinejs';
import { createApp } from 'vue';

// Import Alpine.js plugins
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

// Setup Alpine.js plugins
Alpine.plugin(focus);
Alpine.plugin(persist);
Alpine.plugin(collapse);
Alpine.plugin(intersect);

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine.js
Alpine.start();

// Simple Vue app factory
function createVueApp(rootComponent, props = {}) {
  const app = createApp(rootComponent, props);
  
  // Global error handler
  app.config.errorHandler = (err, instance, info) => {
    console.error('Vue error:', err, info);
  };
  
  return app;
}

// Export for use
window.createVueApp = createVueApp;

console.log('✅ HD Tickets Production App initialized');
