@extends('layouts.app')

@section('title', 'Security Incidents - HD Tickets')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .incident-card { transition: all 0.3s ease; }
    .incident-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .severity-critical { border-left: 4px solid #dc2626; }
    .severity-high { border-left: 4px solid #ea580c; }
    .severity-medium { border-left: 4px solid #d97706; }
    .severity-low { border-left: 4px solid #65a30d; }
    .status-open { background-color: #fef3c7; }
    .status-investigating { background-color: #dbeafe; }
    .status-resolved { background-color: #d1fae5; }
    .status-closed { background-color: #f3f4f6; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="incidentManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Security Incidents</h1>
                <p class="mt-2 text-lg text-gray-600">Monitor and manage security incidents</p>
            </div>
            <div class="flex space-x-3">
                <button @click="refreshIncidents()" 
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
                <button @click="showCreateModal = true" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                    Create Incident
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Incidents</p>
                        <p class="text-3xl font-bold text-gray-900" x-text="stats.total"></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Open Incidents</p>
                        <p class="text-3xl font-bold text-orange-600" x-text="stats.open"></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Critical Incidents</p>
                        <p class="text-3xl font-bold text-red-600" x-text="stats.critical"></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Response Time</p>
                        <p class="text-3xl font-bold text-green-600" x-text="stats.avg_response_time"></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.status" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="investigating">Investigating</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Severity</label>
                    <select x-model="filters.severity" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
                    <select x-model="filters.assignee" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Assignees</option>
                        <option value="unassigned">Unassigned</option>
                        <template x-for="user in users" :key="user.id">
                            <option :value="user.id" x-text="user.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select x-model="filters.date_range" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input x-model="filters.search" @input="applyFilters()" 
                           type="text" placeholder="Search incidents..."
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Incidents List -->
        <div class="space-y-4">
            <template x-for="incident in filteredIncidents" :key="incident.id">
                <div class="incident-card bg-white rounded-lg shadow p-6" 
                     :class="`severity-${incident.severity}`"
                     @click="selectIncident(incident)">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" x-text="incident.title"></h3>
                                <p class="text-sm text-gray-600">
                                    ID: <span x-text="incident.incident_id"></span> • 
                                    Created: <span x-text="formatDate(incident.created_at)"></span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <span :class="`px-2 py-1 rounded-full text-xs font-medium 
                                          ${incident.severity === 'critical' ? 'bg-red-100 text-red-800' : 
                                            incident.severity === 'high' ? 'bg-orange-100 text-orange-800' :
                                            incident.severity === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-green-100 text-green-800'}`" 
                                  x-text="incident.severity.toUpperCase()"></span>
                            
                            <span :class="`status-${incident.status} px-2 py-1 rounded-full text-xs font-medium`" 
                                  x-text="incident.status.toUpperCase()"></span>
                        </div>
                    </div>

                    <p class="text-gray-700 mb-4" x-text="incident.description"></p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Assigned to:</span>
                            <span x-text="incident.assigned_to ? incident.assigned_to.name : 'Unassigned'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Related Events:</span>
                            <span x-text="incident.related_events_count || 0"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Last Updated:</span>
                            <span x-text="formatDate(incident.updated_at)"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredIncidents.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No incidents found</h3>
                <p class="mt-1 text-sm text-gray-500">No incidents match your current filters.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-b-lg shadow">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" :disabled="currentPage === 1" 
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                    Previous
                </button>
                <button @click="nextPage()" :disabled="currentPage === totalPages" 
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                        <span x-text="Math.min(currentPage * perPage, total)"></span> of 
                        <span x-text="total"></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button @click="previousPage()" :disabled="currentPage === 1" 
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            Previous
                        </button>
                        
                        <template x-for="page in visiblePages" :key="page">
                            <button @click="goToPage(page)" 
                                    :class="page === currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    x-text="page"></button>
                        </template>
                        
                        <button @click="nextPage()" :disabled="currentPage === totalPages" 
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            Next
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Incident Modal -->
    <div x-show="showCreateModal" x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Incident</h3>
                
                <form @submit.prevent="createIncident()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input x-model="newIncident.title" type="text" required 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea x-model="newIncident.description" rows="3" required
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Severity</label>
                        <select x-model="newIncident.severity" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Severity</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to</label>
                        <select x-model="newIncident.assigned_to" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Unassigned</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button @click="showCreateModal = false" type="button" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="creatingIncident" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!creatingIncident">Create Incident</span>
                            <span x-show="creatingIncident">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function incidentManager() {
    return {
        incidents: [],
        filteredIncidents: [],
        users: [],
        stats: {
            total: 0,
            open: 0,
            critical: 0,
            avg_response_time: '0 min'
        },
        filters: {
            status: '',
            severity: '',
            assignee: '',
            date_range: '',
            search: ''
        },
        currentPage: 1,
        perPage: 10,
        total: 0,
        totalPages: 0,
        visiblePages: [],
        loading: false,
        showCreateModal: false,
        creatingIncident: false,
        newIncident: {
            title: '',
            description: '',
            severity: '',
            assigned_to: ''
        },

        init() {
            this.loadIncidents();
            this.loadUsers();
            this.loadStats();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.refreshIncidents();
            }, 30000);
        },

        async loadIncidents() {
            this.loading = true;
            try {
                const response = await fetch(`/security/dashboard/incidents/api?page=${this.currentPage}&per_page=${this.perPage}`);
                const data = await response.json();
                
                if (data.success) {
                    this.incidents = data.data;
                    this.total = data.meta.total;
                    this.totalPages = data.meta.last_page;
                    this.currentPage = data.meta.current_page;
                    this.updateVisiblePages();
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Error loading incidents:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadUsers() {
            try {
                const response = await fetch('/security/dashboard/users/api');
                const data = await response.json();
                
                if (data.success) {
                    this.users = data.data;
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/security/dashboard/incidents/stats');
                const data = await response.json();
                
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async refreshIncidents() {
            await this.loadIncidents();
            await this.loadStats();
        },

        applyFilters() {
            let filtered = [...this.incidents];

            if (this.filters.status) {
                filtered = filtered.filter(incident => incident.status === this.filters.status);
            }

            if (this.filters.severity) {
                filtered = filtered.filter(incident => incident.severity === this.filters.severity);
            }

            if (this.filters.assignee) {
                if (this.filters.assignee === 'unassigned') {
                    filtered = filtered.filter(incident => !incident.assigned_to);
                } else {
                    filtered = filtered.filter(incident => incident.assigned_to && incident.assigned_to.id == this.filters.assignee);
                }
            }

            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(incident => 
                    incident.title.toLowerCase().includes(search) ||
                    incident.description.toLowerCase().includes(search) ||
                    incident.incident_id.toLowerCase().includes(search)
                );
            }

            this.filteredIncidents = filtered;
        },

        async createIncident() {
            this.creatingIncident = true;
            try {
                const response = await fetch('/security/dashboard/incidents', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.newIncident)
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showCreateModal = false;
                    this.resetNewIncident();
                    await this.refreshIncidents();
                } else {
                    alert('Error creating incident: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating incident:', error);
                alert('Error creating incident');
            } finally {
                this.creatingIncident = false;
            }
        },

        resetNewIncident() {
            this.newIncident = {
                title: '',
                description: '',
                severity: '',
                assigned_to: ''
            };
        },

        selectIncident(incident) {
            window.location.href = `/security/dashboard/incidents/${incident.id}`;
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        },

        // Pagination methods
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadIncidents();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadIncidents();
            }
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadIncidents();
        },

        updateVisiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            this.visiblePages = pages;
        }
    };
}
</script>
@endsection
