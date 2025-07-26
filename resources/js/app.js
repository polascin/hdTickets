import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';

// Vue Components
import RealTimeMonitoringDashboard from './components/RealTimeMonitoringDashboard.vue';
import AnalyticsDashboard from './components/AnalyticsDashboard.vue';
import UserPreferencesPanel from './components/UserPreferencesPanel.vue';
import TicketDashboard from './components/TicketDashboard.vue';
import AdminDashboard from './components/admin/AdminDashboard.vue';

// Make Alpine available on the window object
window.Alpine = Alpine;

// Initialize Alpine
Alpine.start();

console.log('Alpine.js loaded and initialized:', !!window.Alpine);

// Initialize Vue components where needed
if (document.getElementById('realtime-monitoring-dashboard')) {
    createApp({
        components: {
            RealTimeMonitoringDashboard
        }
    }).mount('#realtime-monitoring-dashboard');
}

if (document.getElementById('analytics-dashboard')) {
    createApp({
        components: {
            AnalyticsDashboard
        }
    }).mount('#analytics-dashboard');
}

if (document.getElementById('user-preferences-panel')) {
    createApp({
        components: {
            UserPreferencesPanel
        }
    }).mount('#user-preferences-panel');
}

if (document.getElementById('ticket-dashboard')) {
    createApp({
        components: {
            TicketDashboard
        }
    }).mount('#ticket-dashboard');
}

if (document.getElementById('admin-dashboard')) {
    createApp({
        components: {
            AdminDashboard
        }
    }).mount('#admin-dashboard');
}

// Global functions for ticket management
window.TicketManager = {
    updateStatus: async function(ticketId, status) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/status`, {
                status: status
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket status updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error updating ticket status:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update ticket status'
            });
        }
    },

    updatePriority: async function(ticketId, priority) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/priority`, {
                priority: priority
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket priority updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error updating ticket priority:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update ticket priority'
            });
        }
    },

    assignTicket: async function(ticketId, agentId) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/assign`, {
                assigned_to: agentId
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket assigned successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error assigning ticket:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to assign ticket'
            });
        }
    }
};

// Initialize basic DOM functionality
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, Alpine should be available:', !!window.Alpine);
});

// Global utility functions
window.hdTicketsUtils = {
    // Show notification shortcut
    notify: (message, type = 'info', options = {}) => {
        if (window.hdTicketsFeedback) {
            return window.hdTicketsFeedback.showToast(message, type, options);
        }
    },
    
    // Show loading shortcut
    loading: (message = 'Loading...', options = {}) => {
        if (window.hdTicketsFeedback) {
            return window.hdTicketsFeedback.showLoading(message, options);
        }
    },
    
    // Hide loading shortcut
    stopLoading: () => {
        if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.hideLoading();
        }
    },
    
    // Get user preference
    getPref: (key, defaultValue = null) => {
        if (window.hdTicketsPrefs) {
            return window.hdTicketsPrefs.get(key, defaultValue);
        }
        return defaultValue;
    },
    
    // Set user preference
    setPref: (key, value) => {
        if (window.hdTicketsPrefs) {
            return window.hdTicketsPrefs.set(key, value);
        }
    },
    
    // Toggle theme
    toggleTheme: () => {
        if (window.hdTicketsTheme) {
            window.hdTicketsTheme.toggleTheme();
        }
    }
};

// Initialize real-time notifications if Echo is available
if (typeof window.Echo !== 'undefined' && window.Laravel.user) {
    window.Echo.private(`user.${window.Laravel.user.id}`)
        .notification((notification) => {
            // Use new UI feedback system if available, fallback to Swal
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.info(
                    notification.title || 'New Notification',
                    {
                        persistent: false,
                        duration: 5000
                    }
                );
            } else {
                // Fallback to original Swal implementation
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });

                toast.fire({
                    icon: 'info',
                    title: notification.title || 'New Notification',
                    text: notification.message || ''
                });
            }

            // Update notification counter
            const counter = document.getElementById('notification-counter');
            if (counter) {
                const currentCount = parseInt(counter.textContent) || 0;
                counter.textContent = currentCount + 1;
                counter.style.display = 'inline';
            }
        });
}
