import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';

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

// Initialize real-time notifications if Echo is available
if (typeof window.Echo !== 'undefined' && window.Laravel.user) {
    window.Echo.private(`user.${window.Laravel.user.id}`)
        .notification((notification) => {
            // Show notification
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

            // Update notification counter
            const counter = document.getElementById('notification-counter');
            if (counter) {
                const currentCount = parseInt(counter.textContent) || 0;
                counter.textContent = currentCount + 1;
                counter.style.display = 'inline';
            }
        });
}
