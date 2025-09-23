// Dedicated dashboard entrypoint for customer dashboard
import Alpine from 'alpinejs';
import '../bootstrap';
import { customerDashboard } from './customer-v3';

// Register component
Alpine.data('customerDashboard', customerDashboard);

// Start Alpine when DOM ready
window.addEventListener('DOMContentLoaded', () => {
  window.Alpine = Alpine;
  Alpine.start();
});
