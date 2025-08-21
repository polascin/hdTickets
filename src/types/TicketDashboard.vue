<template>
    <div class="sports-monitor-dashboard">
        <div class="dashboard-header">
            <h2 class="text-2xl font-bold text-gray-800">Sports Event Monitoring Dashboard</h2>
            <div class="stats-grid">
                <div class="stat-card bg-blue-100">
                    <h3 class="text-lg font-semibold text-blue-800">Active Monitors</h3>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.active_monitors }}</p>
                </div>
                <div class="stat-card bg-green-100">
                    <h3 class="text-lg font-semibold text-green-800">Tickets Found Today</h3>
                    <p class="text-2xl font-bold text-green-900">{{ stats.tickets_found }}</p>
                </div>
                <div class="stat-card bg-yellow-100">
                    <h3 class="text-lg font-semibold text-yellow-800">Price Alerts</h3>
                    <p class="text-2xl font-bold text-yellow-900">{{ stats.price_alerts }}</p>
                </div>
                <div class="stat-card bg-purple-100">
                    <h3 class="text-lg font-semibold text-purple-800">Success Rate</h3>
                    <p class="text-2xl font-bold text-purple-900">{{ stats.success_rate }}%</p>
                </div>
            </div>
        </div>

        <div class="monitors-section">
            <h3 class="text-xl font-semibold mb-4">Active Event Monitors</h3>
            <div v-if="loading" class="text-center py-4">
                <p>Loading monitors...</p>
            </div>
            <div v-else-if="monitors.length">
                <div class="monitor-grid">
                    <div v-for="monitor in monitors" :key="monitor.id" class="monitor-card">
                        <div class="monitor-header">
                            <span class="monitor-id">#{{ monitor.id }}</span>
                            <span :class="getMonitorStatusClass(monitor.status)" class="status-badge">
                                {{ monitor.status }}
                            </span>
                        </div>
                        <h4 class="monitor-title">{{ monitor.event_name }}</h4>
                        <p class="monitor-meta">
                            <span>{{ monitor.venue_name }}</span> • 
                            <span>{{ formatDate(monitor.event_date) }}</span>
                        </p>
                        <div class="monitor-details">
                            <div class="price-range">
                                <span class="label">Price Range:</span>
                                <span class="value">${{ monitor.min_price }} - ${{ monitor.max_price }}</span>
                            </div>
                            <div class="tickets-needed">
                                <span class="label">Tickets Needed:</span>
                                <span class="value">{{ monitor.quantity_needed }}</span>
                            </div>
                            <div class="last-check">
                                <span class="label">Last Check:</span>
                                <span class="value">{{ formatDateTime(monitor.last_checked_at) }}</span>
                            </div>
                        </div>
                        <div class="monitor-actions">
                            <button @click="viewMonitor(monitor.id)" class="btn-primary">View Details</button>
                            <button @click="checkNow(monitor.id)" class="btn-secondary">
                                Check Now
                            </button>
                            <button @click="toggleMonitor(monitor.id)" :class="monitor.is_active ? 'btn-warning' : 'btn-success'">
                                {{ monitor.is_active ? 'Pause' : 'Resume' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8">
                <p class="text-gray-600">No event monitors configured. <a href="/monitors/create" class="text-blue-600 hover:text-blue-800">Create your first monitor</a></p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'SportsMonitorDashboard',
    data() {
        return {
            monitors: [],
            stats: {
                active_monitors: 0,
                tickets_found: 0,
                price_alerts: 0,
                success_rate: 0
            },
            loading: true
        };
    },
    mounted() {
        this.fetchDashboardData();
        this.startAutoRefresh();
        this.listenForUpdates();
    },
    beforeUnmount() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        if (this.updateChannel) {
            Echo.leave('ticket-updates');
        }
    },
    methods: {
        async fetchDashboardData() {
            try {
                this.loading = true;
                const [monitorsResponse, statsResponse] = await Promise.all([
                    axios.get('/api/v1/dashboard/monitors'),
                    axios.get('/api/v1/dashboard/stats')
                ]);
                
                this.monitors = monitorsResponse.data.data || [];
                this.stats = statsResponse.data.data || this.stats;
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                this.$emit('error', 'Failed to load dashboard data');
            } finally {
                this.loading = false;
            }
        },
        
        listenForUpdates() {
            this.updateChannel = Echo.channel('ticket-updates')
                .listen('.ticket.availability.updated', (event) => {
                    if (event) {
                        // Update relevant data based on the event
                        console.log(`Update received for ticket: ${event.ticket_uuid}`);
                        this.fetchDashboardData();
                    }
                });
        },
        
        startAutoRefresh() {
            // Refresh data every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.fetchDashboardData();
            }, 30000);
        },
        
        viewMonitor(monitorId) {
            window.location.href = `/monitors/${monitorId}`;
        },
        
        async checkNow(monitorId) {
            try {
                await axios.post(`/api/v1/dashboard/monitors/${monitorId}/check-now`);
                this.$emit('success', 'Manual check initiated successfully');
                // Refresh data after a short delay
                setTimeout(() => this.fetchDashboardData(), 2000);
            } catch (error) {
                console.error('Error initiating manual check:', error);
                this.$emit('error', 'Failed to initiate manual check');
            }
        },
        
        async toggleMonitor(monitorId) {
            try {
                await axios.post(`/api/v1/dashboard/monitors/${monitorId}/toggle`);
                await this.fetchDashboardData(); // Refresh data
                this.$emit('success', 'Monitor status updated successfully');
            } catch (error) {
                console.error('Error updating monitor status:', error);
                this.$emit('error', 'Failed to update monitor status');
            }
        },
        
        getMonitorStatusClass(status) {
            const statusClasses = {
                active: 'bg-green-100 text-green-800',
                paused: 'bg-yellow-100 text-yellow-800',
                error: 'bg-red-100 text-red-800',
                checking: 'bg-blue-100 text-blue-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            if (!dateString) return 'Never';
            return new Date(dateString).toLocaleDateString();
        },
        
        formatDateTime(dateString) {
            if (!dateString) return 'Never';
            return new Date(dateString).toLocaleString();
        }
    }
};
</script>

<style scoped>
.sports-monitor-dashboard {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    padding: 1.5rem;
    border-radius: 0.5rem;
    text-align: center;
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.monitors-section {
    margin-top: 2rem;
}

.monitor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.monitor-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.monitor-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.monitor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.monitor-id {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.monitor-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #111827;
    line-height: 1.3;
}

.monitor-meta {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
    font-weight: 500;
}

.monitor-details {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #f3f4f6;
}

.monitor-details > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.monitor-details > div:last-child {
    margin-bottom: 0;
}

.monitor-details .label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.monitor-details .value {
    font-size: 0.875rem;
    color: #111827;
    font-weight: 600;
}

.monitor-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary, .btn-success, .btn-warning {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    flex: 1;
    min-width: fit-content;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-warning {
    background-color: #f59e0b;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary:hover {
    background-color: #4b5563;
    transform: translateY(-1px);
}

.btn-success:hover {
    background-color: #059669;
    transform: translateY(-1px);
}

.btn-warning:hover {
    background-color: #d97706;
    transform: translateY(-1px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .monitor-grid {
        grid-template-columns: 1fr;
    }
    
    .monitor-actions {
        flex-direction: column;
    }
    
    .btn-primary, .btn-secondary, .btn-success, .btn-warning {
        flex: none;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .sports-monitor-dashboard {
        padding: 1rem;
    }
}
</style>
