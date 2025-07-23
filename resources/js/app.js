import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';

// Import UX/UI enhancement modules
import ThemeManager from './modules/ThemeManager.js';
import TableCustomizer from './modules/TableCustomizer.js';
import UIFeedbackManager from './modules/UIFeedbackManager.js';
import UserPreferences from './modules/UserPreferences.js';

// Vue Components
import TicketSearch from './components/TicketSearch.vue';
import TicketDashboard from './components/TicketDashboard.vue';
import TicketForm from './components/TicketForm.vue';
import KnowledgeBase from './components/KnowledgeBase.vue';
import NotificationCenter from './components/NotificationCenter.vue';

// Make Alpine available on the window object
window.Alpine = Alpine;

// Initialize Alpine
Alpine.start();

// Initialize Vue app if there are Vue components on the page
const app = createApp({});

// Register Vue components
app.component('ticket-search', TicketSearch);
app.component('ticket-dashboard', TicketDashboard);
app.component('ticket-form', TicketForm);
app.component('knowledge-base', KnowledgeBase);
app.component('notification-center', NotificationCenter);

// Mount Vue app
if (document.getElementById('app')) {
    app.mount('#app');
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

// Initialize UX/UI enhancement modules
document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme management
    if (!window.hdTicketsTheme) {
        window.hdTicketsTheme = new ThemeManager({
            transitions: true,
            autoDetect: true
        });
    }
    
    // Initialize UI feedback system
    if (!window.hdTicketsFeedback) {
        window.hdTicketsFeedback = new UIFeedbackManager({
            enableSounds: false,
            enableVibration: false
        });
    }
    
    // Initialize user preferences
    if (!window.hdTicketsPrefs) {
        window.hdTicketsPrefs = new UserPreferences({
            useServer: false // Set to true when server sync is implemented
        });
    }
    
    // Auto-initialize table customizers for existing tables
    document.querySelectorAll('table[data-customizable="true"]').forEach(table => {
        if (!table.dataset.customized) {
            new TableCustomizer('#' + table.id, {
                enableColumnReorder: true,
                enableColumnResize: true,
                enableColumnToggle: true,
                enableSort: true,
                enableFilters: true
            });
            table.dataset.customized = 'true';
        }
    });
    
    // Apply user preferences to body classes
    const prefs = window.hdTicketsPrefs;
    if (prefs) {
        // Apply accessibility preferences
        if (prefs.isHighContrastEnabled()) {
            document.body.classList.add('high-contrast');
        }
        
        if (prefs.isReduceMotionEnabled()) {
            document.body.classList.add('reduce-motion');
        }
        
        if (!prefs.areAnimationsEnabled()) {
            document.body.classList.add('no-animations');
        }
        
        // Listen for preference changes
        prefs.addObserver((event, data) => {
            if (event === 'preference:changed') {
                // Handle specific preference changes
                switch (data.key) {
                    case 'accessibility.highContrast':
                        document.body.classList.toggle('high-contrast', data.value);
                        break;
                    case 'accessibility.reduceMotion':
                        document.body.classList.toggle('reduce-motion', data.value);
                        break;
                    case 'ui.animationsEnabled':
                        document.body.classList.toggle('no-animations', !data.value);
                        break;
                }
            }
        });
    }
    
    // Theme integration with preferences
    if (window.hdTicketsTheme && window.hdTicketsPrefs) {
        const savedTheme = window.hdTicketsPrefs.getTheme();
        if (savedTheme && savedTheme !== window.hdTicketsTheme.getCurrentTheme()) {
            window.hdTicketsTheme.setTheme(savedTheme, false);
        }
        
        // Sync theme changes with preferences
        window.hdTicketsTheme.addObserver((newTheme, previousTheme) => {
            window.hdTicketsPrefs.setTheme(newTheme);
        });
    }
    
    console.log('HD Tickets UX/UI enhancements loaded successfully');
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
