<template>
    <div class="ticket-dashboard">
        <div class="dashboard-header">
            <h2 class="text-2xl font-bold text-gray-800">Agent Dashboard</h2>
            <div class="stats-grid">
                <div class="stat-card bg-blue-100">
                    <h3 class="text-lg font-semibold text-blue-800">Open Tickets</h3>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.open }}</p>
                </div>
                <div class="stat-card bg-yellow-100">
                    <h3 class="text-lg font-semibold text-yellow-800">In Progress</h3>
                    <p class="text-2xl font-bold text-yellow-900">{{ stats.in_progress }}</p>
                </div>
                <div class="stat-card bg-green-100">
                    <h3 class="text-lg font-semibold text-green-800">Resolved Today</h3>
                    <p class="text-2xl font-bold text-green-900">{{ stats.resolved_today }}</p>
                </div>
                <div class="stat-card bg-red-100">
                    <h3 class="text-lg font-semibold text-red-800">Overdue</h3>
                    <p class="text-2xl font-bold text-red-900">{{ stats.overdue }}</p>
                </div>
            </div>
        </div>

        <div class="tickets-section">
            <h3 class="text-xl font-semibold mb-4">My Assigned Tickets</h3>
            <div v-if="loading" class="text-center py-4">
                <p>Loading tickets...</p>
            </div>
            <div v-else-if="tickets.length">
                <div class="ticket-grid">
                    <div v-for="ticket in tickets" :key="ticket.id" class="ticket-card">
                        <div class="ticket-header">
                            <span class="ticket-id">#{{ ticket.id }}</span>
                            <span :class="getStatusClass(ticket.status)" class="status-badge">
                                {{ ticket.status }}
                            </span>
                        </div>
                        <h4 class="ticket-title">{{ ticket.title }}</h4>
                        <p class="ticket-meta">
                            <span>{{ ticket.priority }} priority</span> • 
                            <span>{{ formatDate(ticket.created_at) }}</span>
                        </p>
                        <div class="ticket-actions">
                            <button @click="openTicket(ticket.id)" class="btn-primary">View</button>
                            <button @click="updateTicketStatus(ticket.id, 'in_progress')" class="btn-secondary">
                                Start Work
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8">
                <p class="text-gray-600">No tickets assigned to you at the moment.</p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'TicketDashboard',
    data() {
        return {
            tickets: [],
            stats: {
                open: 0,
                in_progress: 0,
                resolved_today: 0,
                overdue: 0
            },
            loading: true
        };
    },
    mounted() {
        this.fetchDashboardData();
    },
    methods: {
        async fetchDashboardData() {
            try {
                this.loading = true;
                const [ticketsResponse, statsResponse] = await Promise.all([
                    axios.get('/api/tickets/agent'),
                    axios.get('/api/dashboard/stats')
                ]);
                
                this.tickets = ticketsResponse.data;
                this.stats = statsResponse.data;
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                this.$emit('error', 'Failed to load dashboard data');
            } finally {
                this.loading = false;
            }
        },
        
        openTicket(ticketId) {
            window.location.href = `/tickets/${ticketId}`;
        },
        
        async updateTicketStatus(ticketId, status) {
            try {
                await axios.patch(`/tickets/${ticketId}/status`, { status });
                await this.fetchDashboardData(); // Refresh data
                this.$emit('success', 'Ticket status updated successfully');
            } catch (error) {
                console.error('Error updating ticket status:', error);
                this.$emit('error', 'Failed to update ticket status');
            }
        },
        
        getStatusClass(status) {
            const statusClasses = {
                open: 'bg-blue-100 text-blue-800',
                in_progress: 'bg-yellow-100 text-yellow-800',
                resolved: 'bg-green-100 text-green-800',
                closed: 'bg-gray-100 text-gray-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }
    }
};
</script>

<style scoped>
.ticket-dashboard {
    padding: 1.5rem;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    padding: 1.5rem;
    rounded: 0.5rem;
    text-align: center;
}

.ticket-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.ticket-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.ticket-id {
    font-weight: 600;
    color: #374151;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.ticket-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #111827;
}

.ticket-meta {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.ticket-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary:hover {
    background-color: #4b5563;
}
</style>
