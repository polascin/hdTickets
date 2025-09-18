@extends('layouts.app-v2')

@section('title', 'Security Events - HD Tickets')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/pusher.min.js') }}"></script>
<style>
    .event-card { transition: all 0.3s ease; }
    .event-card:hover { transform: translateY(-1px); }
    .threat-low { border-left: 4px solid #10b981; }
    .threat-medium { border-left: 4px solid #f59e0b; }
    .threat-high { border-left: 4px solid #ef4444; }
    .threat-critical { border-left: 4px solid #dc2626; }
    .event-new { animation: slideIn 0.5s ease-out; }
    @keyframes slideIn {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .pulse { animation: pulse 2s infinite; }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="securityEvents()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Security Events</h1>
                <p class="mt-2 text-lg text-gray-600">Real-time security event monitoring and analysis</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div :class="liveConnection ? 'bg-green-400' : 'bg-red-400'" 
                         class="w-3 h-3 rounded-full" 
                         :class="liveConnection && 'pulse'"></div>
                    <span class="text-sm font-medium" x-text="liveConnection ? 'Live Connected' : 'Disconnected'"></span>
                </div>
                <button @click="refreshEvents()" 
                        :disabled="loading"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium disabled:opacity-50">
                    <span x-show="!loading">Refresh</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </span>
                </button>
            </div>
        </div>

        <!-- Live Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Events Today</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.events_today"></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">High Risk Events</p>
                        <p class="text-2xl font-bold text-orange-600" x-text="stats.high_risk"></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Failed Logins</p>
                        <p class="text-2xl font-bold text-red-600" x-text="stats.failed_logins"></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Blocked IPs</p>
                        <p class="text-2xl font-bold text-purple-600" x-text="stats.blocked_ips"></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Threat Score</p>
                        <p class="text-2xl font-bold text-indigo-600" x-text="stats.avg_threat_score"></p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Controls -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                        <select x-model="filters.event_type" @change="applyFilters()" 
                                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Types</option>
                            <option value="login_success">Login Success</option>
                            <option value="login_failed">Login Failed</option>
                            <option value="brute_force">Brute Force</option>
                            <option value="suspicious_activity">Suspicious Activity</option>
                            <option value="data_access">Data Access</option>
                            <option value="permission_denied">Permission Denied</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Threat Level</label>
                        <select x-model="filters.threat_level" @change="applyFilters()" 
                                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Levels</option>
                            <option value="low">Low (0-39)</option>
                            <option value="medium">Medium (40-69)</option>
                            <option value="high">High (70-89)</option>
                            <option value="critical">Critical (90-100)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time Range</label>
                        <select x-model="filters.time_range" @change="applyFilters()" 
                                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="1h">Last Hour</option>
                            <option value="6h">Last 6 Hours</option>
                            <option value="24h">Last 24 Hours</option>
                            <option value="7d">Last 7 Days</option>
                            <option value="30d">Last 30 Days</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                        <input x-model="filters.ip_address" @input="applyFilters()" 
                               type="text" placeholder="Filter by IP..."
                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="flex items-center">
                        <input x-model="autoRefresh" @change="toggleAutoRefresh()" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Auto refresh</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Real-time Events Feed -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Live Security Events</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Showing <span x-text="filteredEvents.length"></span> events 
                    <span x-show="newEventsCount > 0" class="ml-2 bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                        <span x-text="newEventsCount"></span> new
                    </span>
                </p>
            </div>

            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                <template x-for="event in paginatedEvents" :key="event.id">
                    <div class="event-card p-6 hover:bg-gray-50" 
                         :class="getThreatClass(event.threat_score)"
                         :data-new="event.is_new">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getEventTypeClass(event.event_type)"
                                          x-text="event.event_type.replace('_', ' ').toUpperCase()"></span>
                                    
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getThreatScoreClass(event.threat_score)">
                                        Score: <span x-text="event.threat_score"></span>
                                    </span>
                                    
                                    <span x-show="event.is_new" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        NEW
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">User:</span>
                                        <span x-text="event.user ? event.user.name : 'Anonymous'"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">IP Address:</span>
                                        <span x-text="event.ip_address"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Location:</span>
                                        <span x-text="event.location || 'Unknown'"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Time:</span>
                                        <span x-text="formatTime(event.occurred_at)"></span>
                                    </div>
                                </div>

                                <div x-show="event.details" class="mt-3">
                                    <p class="text-sm text-gray-700" x-text="event.details"></p>
                                </div>

                                <div x-show="event.metadata" class="mt-3">
                                    <details class="text-sm">
                                        <summary class="cursor-pointer text-gray-600 hover:text-gray-800">View Metadata</summary>
                                        <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto" x-text="JSON.stringify(event.metadata, null, 2)"></pre>
                                    </details>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="createIncidentFromEvent(event)" 
                                        title="Create Incident"
                                        class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                                
                                <button @click="blockIpAddress(event.ip_address)" 
                                        x-show="event.threat_score >= 70"
                                        title="Block IP"
                                        class="text-gray-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredEvents.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
                    <p class="mt-1 text-sm text-gray-500">No security events match your current filters.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div x-show="filteredEvents.length > eventsPerPage" class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="previousEventsPage()" :disabled="currentEventsPage === 1" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        Previous
                    </button>
                    <button @click="nextEventsPage()" :disabled="currentEventsPage === totalEventsPages" 
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span x-text="((currentEventsPage - 1) * eventsPerPage) + 1"></span> to 
                            <span x-text="Math.min(currentEventsPage * eventsPerPage, filteredEvents.length)"></span> of 
                            <span x-text="filteredEvents.length"></span> events
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="previousEventsPage()" :disabled="currentEventsPage === 1" 
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                Previous
                            </button>
                            
                            <template x-for="page in visibleEventsPages" :key="page">
                                <button @click="goToEventsPage(page)" 
                                        :class="page === currentEventsPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                        x-text="page"></button>
                            </template>
                            
                            <button @click="nextEventsPage()" :disabled="currentEventsPage === totalEventsPages" 
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                Next
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function securityEvents() {
    return {
        events: [],
        filteredEvents: [],
        paginatedEvents: [],
        newEventsCount: 0,
        stats: {
            events_today: 0,
            high_risk: 0,
            failed_logins: 0,
            blocked_ips: 0,
            avg_threat_score: 0
        },
        filters: {
            event_type: '',
            threat_level: '',
            time_range: '24h',
            ip_address: ''
        },
        currentEventsPage: 1,
        eventsPerPage: 20,
        totalEventsPages: 0,
        visibleEventsPages: [],
        loading: false,
        liveConnection: false,
        autoRefresh: true,
        pusher: null,
        channel: null,

        init() {
            this.loadEvents();
            this.loadStats();
            this.setupLiveUpdates();
            
            // Auto-refresh every 30 seconds if enabled
            setInterval(() => {
                if (this.autoRefresh) {
                    this.refreshEvents();
                }
            }, 30000);
        },

        async loadEvents() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    time_range: this.filters.time_range,
                    event_type: this.filters.event_type,
                    threat_level: this.filters.threat_level,
                    ip_address: this.filters.ip_address
                });
                
                const response = await fetch(`/security/dashboard/events/api?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.events = data.data;
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Error loading events:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/security/dashboard/events/stats');
                const data = await response.json();
                
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async refreshEvents() {
            await this.loadEvents();
            await this.loadStats();
        },

        setupLiveUpdates() {
            try {
                this.pusher = new Pusher(window.pusherAppKey, {
                    cluster: window.pusherCluster,
                    encrypted: true
                });

                this.channel = this.pusher.subscribe('security-events');
                
                this.channel.bind('new-event', (event) => {
                    this.handleNewEvent(event);
                });

                this.pusher.connection.bind('connected', () => {
                    this.liveConnection = true;
                });

                this.pusher.connection.bind('disconnected', () => {
                    this.liveConnection = false;
                });

            } catch (error) {
                console.error('Error setting up live updates:', error);
            }
        },

        handleNewEvent(event) {
            // Add new event to the beginning of the list
            event.is_new = true;
            this.events.unshift(event);
            
            // Remove the 'new' flag after 10 seconds
            setTimeout(() => {
                event.is_new = false;
            }, 10000);
            
            // Keep only the last 1000 events
            if (this.events.length > 1000) {
                this.events = this.events.slice(0, 1000);
            }
            
            this.newEventsCount++;
            this.applyFilters();
            
            // Reset new events count after 30 seconds
            setTimeout(() => {
                this.newEventsCount = 0;
            }, 30000);
        },

        applyFilters() {
            let filtered = [...this.events];

            if (this.filters.event_type) {
                filtered = filtered.filter(event => event.event_type === this.filters.event_type);
            }

            if (this.filters.threat_level) {
                const ranges = {
                    low: [0, 39],
                    medium: [40, 69],
                    high: [70, 89],
                    critical: [90, 100]
                };
                const [min, max] = ranges[this.filters.threat_level];
                filtered = filtered.filter(event => event.threat_score >= min && event.threat_score <= max);
            }

            if (this.filters.ip_address) {
                filtered = filtered.filter(event => 
                    event.ip_address.includes(this.filters.ip_address)
                );
            }

            this.filteredEvents = filtered;
            this.updateEventsPagination();
        },

        updateEventsPagination() {
            this.totalEventsPages = Math.ceil(this.filteredEvents.length / this.eventsPerPage);
            
            if (this.currentEventsPage > this.totalEventsPages) {
                this.currentEventsPage = 1;
            }
            
            this.updateVisibleEventsPages();
            this.updatePaginatedEvents();
        },

        updatePaginatedEvents() {
            const start = (this.currentEventsPage - 1) * this.eventsPerPage;
            const end = start + this.eventsPerPage;
            this.paginatedEvents = this.filteredEvents.slice(start, end);
        },

        updateVisibleEventsPages() {
            const pages = [];
            const start = Math.max(1, this.currentEventsPage - 2);
            const end = Math.min(this.totalEventsPages, this.currentEventsPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            this.visibleEventsPages = pages;
        },

        previousEventsPage() {
            if (this.currentEventsPage > 1) {
                this.currentEventsPage--;
                this.updatePaginatedEvents();
            }
        },

        nextEventsPage() {
            if (this.currentEventsPage < this.totalEventsPages) {
                this.currentEventsPage++;
                this.updatePaginatedEvents();
            }
        },

        goToEventsPage(page) {
            this.currentEventsPage = page;
            this.updatePaginatedEvents();
        },

        toggleAutoRefresh() {
            // Auto-refresh logic handled in setInterval
        },

        getThreatClass(score) {
            if (score >= 90) return 'threat-critical';
            if (score >= 70) return 'threat-high';
            if (score >= 40) return 'threat-medium';
            return 'threat-low';
        },

        getThreatScoreClass(score) {
            if (score >= 90) return 'bg-red-100 text-red-800';
            if (score >= 70) return 'bg-orange-100 text-orange-800';
            if (score >= 40) return 'bg-yellow-100 text-yellow-800';
            return 'bg-green-100 text-green-800';
        },

        getEventTypeClass(eventType) {
            const classes = {
                login_success: 'bg-green-100 text-green-800',
                login_failed: 'bg-red-100 text-red-800',
                brute_force: 'bg-red-100 text-red-800',
                suspicious_activity: 'bg-orange-100 text-orange-800',
                data_access: 'bg-blue-100 text-blue-800',
                permission_denied: 'bg-purple-100 text-purple-800'
            };
            return classes[eventType] || 'bg-gray-100 text-gray-800';
        },

        async createIncidentFromEvent(event) {
            try {
                const response = await fetch('/security/dashboard/incidents/from-event', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ event_id: event.id })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Incident created successfully');
                } else {
                    alert('Error creating incident: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating incident:', error);
                alert('Error creating incident');
            }
        },

        async blockIpAddress(ipAddress) {
            if (confirm(`Are you sure you want to block IP address ${ipAddress}?`)) {
                try {
                    const response = await fetch('/security/dashboard/block-ip', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ip_address: ipAddress })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        alert('IP address blocked successfully');
                    } else {
                        alert('Error blocking IP: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error blocking IP:', error);
                    alert('Error blocking IP address');
                }
            }
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleString();
        }
    };
}
</script>
@endsection
