@extends('layouts.modern')
@section('title', 'Security Audit Logs - HD Tickets')

@push('styles')
<style>
    .audit-entry { transition: all 0.3s ease; }
    .audit-entry:hover { background-color: #f9fafb; }
    .action-create { border-left: 4px solid #10b981; }
    .action-update { border-left: 4px solid #3b82f6; }
    .action-delete { border-left: 4px solid #ef4444; }
    .action-login { border-left: 4px solid #8b5cf6; }
    .action-security { border-left: 4px solid #f59e0b; }
    .risk-low { background-color: #f0fdf4; }
    .risk-medium { background-color: #fffbeb; }
    .risk-high { background-color: #fef2f2; }
    .timeline-dot { position: absolute; left: -6px; top: 50%; transform: translateY(-50%); width: 12px; height: 12px; border-radius: 50%; }
</style>
@endpush

@push('scripts')
@vite('resources/js/vendor/chart.js')
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="auditLogManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Security Audit Logs</h1>
                <p class="mt-2 text-lg text-gray-600">Complete audit trail of system activities and security events</p>
            </div>
            <div class="flex space-x-3">
                <button @click="refreshLogs()" 
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
                <button @click="exportLogs()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                    Export Logs
                </button>
                <button @click="showReportModal = true" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium">
                    Generate Report
                </button>
            </div>
        </div>

        <!-- Activity Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-3xl font-bold text-gray-900" x-text="stats.total_activities"></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Last 24 hours: <span x-text="stats.activities_24h"></span></p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">User Actions</p>
                        <p class="text-3xl font-bold text-green-600" x-text="stats.user_actions"></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Unique users: <span x-text="stats.unique_users"></span></p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Admin Actions</p>
                        <p class="text-3xl font-bold text-purple-600" x-text="stats.admin_actions"></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">High risk: <span x-text="stats.high_risk_actions"></span></p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Failed Actions</p>
                        <p class="text-3xl font-bold text-red-600" x-text="stats.failed_actions"></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Error rate: <span x-text="stats.error_rate + '%'"></span></p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Data Changes</p>
                        <p class="text-3xl font-bold text-indigo-600" x-text="stats.data_changes"></p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Critical: <span x-text="stats.critical_changes"></span></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                    <select x-model="filters.action" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="security">Security</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                    <select x-model="filters.resource_type" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Resources</option>
                        <option value="user">User</option>
                        <option value="ticket">Ticket</option>
                        <option value="incident">Incident</option>
                        <option value="role">Role</option>
                        <option value="permission">Permission</option>
                        <option value="system">System</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Role</label>
                    <select x-model="filters.user_role" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                        <option value="customer">Customer</option>
                        <option value="scraper">Scraper</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Range</label>
                    <select x-model="filters.time_range" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="1h">Last Hour</option>
                        <option value="6h">Last 6 Hours</option>
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                    <input x-model="filters.ip_address" @input="applyFilters()" 
                           type="text" placeholder="Filter by IP..."
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <input x-model="filters.user" @input="applyFilters()" 
                           type="text" placeholder="Filter by user..."
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input x-model="filters.search" @input="applyFilters()" 
                           type="text" placeholder="Search logs..."
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Advanced Filters Toggle -->
            <div class="mt-4">
                <button @click="showAdvancedFilters = !showAdvancedFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <span x-text="showAdvancedFilters ? 'Hide Advanced Filters' : 'Show Advanced Filters'"></span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div x-show="showAdvancedFilters" x-collapse class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input x-model="filters.date_from" @change="applyFilters()" 
                               type="datetime-local"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input x-model="filters.date_to" @change="applyFilters()" 
                               type="datetime-local"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Risk Level</label>
                        <select x-model="filters.risk_level" @change="applyFilters()" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Risk Levels</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Success Status</label>
                        <select x-model="filters.success" @change="applyFilters()" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="true">Success</option>
                            <option value="false">Failed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Activity Timeline</h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            Showing <span x-text="filteredLogs.length"></span> activities
                        </span>
                        <label class="flex items-center">
                            <input x-model="viewMode" @change="updateView()" value="timeline" type="radio" name="view" class="sr-only">
                            <span :class="viewMode === 'timeline' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'" 
                                  class="px-3 py-1 rounded-l-md text-sm font-medium cursor-pointer">Timeline</span>
                        </label>
                        <label class="flex items-center">
                            <input x-model="viewMode" @change="updateView()" value="table" type="radio" name="view" class="sr-only">
                            <span :class="viewMode === 'table' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'" 
                                  class="px-3 py-1 rounded-r-md text-sm font-medium cursor-pointer">Table</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Timeline View -->
            <div x-show="viewMode === 'timeline'" class="p-6">
                <div class="relative">
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <div class="space-y-6">
                        <template x-for="(log, index) in paginatedLogs" :key="log.id">
                            <div class="relative flex items-start space-x-4">
                                <div :class="`timeline-dot ${getActionClass(log.action)} bg-white border-4 border-current`"></div>
                                
                                <div class="min-w-0 flex-1">
                                    <div class="audit-entry bg-white border border-gray-200 rounded-lg p-4" 
                                         :class="getActionBorderClass(log.action)">
                                        
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3 mb-1">
                                                    <h4 class="text-sm font-semibold text-gray-900" x-text="formatAction(log.action)"></h4>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800" 
                                                          x-text="log.resource_type"></span>
                                                    <span x-show="log.risk_level" 
                                                          :class="`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                                  ${log.risk_level === 'high' ? 'bg-red-100 text-red-800' :
                                                                    log.risk_level === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                                                    'bg-green-100 text-green-800'}`"
                                                          x-text="log.risk_level.toUpperCase()"></span>
                                                </div>
                                                
                                                <p class="text-sm text-gray-700" x-text="log.description"></p>
                                            </div>
                                            
                                            <div class="text-right text-xs text-gray-500">
                                                <div x-text="formatDateTime(log.created_at)"></div>
                                                <div x-show="log.ip_address" x-text="log.ip_address"></div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs text-gray-600 mb-3">
                                            <div>
                                                <span class="font-medium">User:</span>
                                                <span x-text="log.user ? log.user.name : 'System'"></span>
                                            </div>
                                            <div x-show="log.resource_id">
                                                <span class="font-medium">Resource ID:</span>
                                                <span x-text="log.resource_id"></span>
                                            </div>
                                            <div x-show="log.user_agent">
                                                <span class="font-medium">User Agent:</span>
                                                <span x-text="log.user_agent" class="truncate block max-w-32" :title="log.user_agent"></span>
                                            </div>
                                            <div x-show="log.session_id">
                                                <span class="font-medium">Session:</span>
                                                <span x-text="log.session_id.substring(0, 8) + '...'"></span>
                                            </div>
                                        </div>

                                        <!-- Changes Details -->
                                        <div x-show="log.changes && Object.keys(log.changes).length > 0" class="mt-3">
                                            <details class="text-xs">
                                                <summary class="cursor-pointer text-gray-600 hover:text-gray-800 font-medium">
                                                    View Changes (<span x-text="Object.keys(log.changes).length"></span> fields)
                                                </summary>
                                                <div class="mt-2 p-3 bg-gray-50 rounded border">
                                                    <template x-for="[key, value] in Object.entries(log.changes)" :key="key">
                                                        <div class="flex justify-between items-start py-1">
                                                            <span class="font-medium text-gray-700" x-text="key"></span>
                                                            <div class="text-right ml-4">
                                                                <div x-show="value.old !== undefined" class="text-red-600">
                                                                    Old: <span x-text="formatChangeValue(value.old)"></span>
                                                                </div>
                                                                <div x-show="value.new !== undefined" class="text-green-600">
                                                                    New: <span x-text="formatChangeValue(value.new)"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div x-show="viewMode === 'table'" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="log in paginatedLogs" :key="log.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDateTime(log.created_at)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="log.user ? log.user.name : 'System'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getActionClass(log.action)}`" 
                                          x-text="formatAction(log.action)"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div x-text="log.resource_type"></div>
                                    <div x-show="log.resource_id" class="text-xs text-gray-400" x-text="'ID: ' + log.resource_id"></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" x-text="log.description" :title="log.description"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="log.ip_address || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span x-show="log.risk_level" 
                                          :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                  ${log.risk_level === 'high' ? 'bg-red-100 text-red-800' :
                                                    log.risk_level === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-green-100 text-green-800'}`"
                                          x-text="log.risk_level"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="filteredLogs.length > logsPerPage" 
                 class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
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
                            Showing <span x-text="((currentPage - 1) * logsPerPage) + 1"></span> to 
                            <span x-text="Math.min(currentPage * logsPerPage, filteredLogs.length)"></span> of 
                            <span x-text="filteredLogs.length"></span> results
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

            <div x-show="filteredLogs.length === 0" class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No audit logs found</h3>
                <p class="mt-1 text-sm text-gray-500">No activities match your current filters.</p>
            </div>
        </div>
    </div>

    <!-- Report Generation Modal -->
    <div x-show="showReportModal" x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-2/3 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Generate Audit Report</h3>
                    <button @click="showReportModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="generateReport()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                            <select x-model="reportConfig.type" required 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Type</option>
                                <option value="compliance">Compliance Report</option>
                                <option value="security">Security Summary</option>
                                <option value="user_activity">User Activity</option>
                                <option value="system_changes">System Changes</option>
                                <option value="failed_actions">Failed Actions</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                            <select x-model="reportConfig.format" required 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Format</option>
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input x-model="reportConfig.start_date" type="datetime-local" required
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input x-model="reportConfig.end_date" type="datetime-local" required
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Include Sections</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input x-model="reportConfig.include_summary" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Executive Summary</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="reportConfig.include_charts" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Charts and Graphs</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="reportConfig.include_details" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Detailed Activity Log</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="reportConfig.include_recommendations" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Security Recommendations</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button @click="showReportModal = false" type="button" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="generatingReport" 
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 disabled:opacity-50">
                            <span x-show="!generatingReport">Generate Report</span>
                            <span x-show="generatingReport">Generating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function auditLogManager() {
    return {
        logs: [],
        filteredLogs: [],
        paginatedLogs: [],
        stats: {
            total_activities: 0,
            activities_24h: 0,
            user_actions: 0,
            unique_users: 0,
            admin_actions: 0,
            high_risk_actions: 0,
            failed_actions: 0,
            error_rate: 0,
            data_changes: 0,
            critical_changes: 0
        },
        filters: {
            action: '',
            resource_type: '',
            user_role: '',
            time_range: '24h',
            ip_address: '',
            user: '',
            search: '',
            date_from: '',
            date_to: '',
            risk_level: '',
            success: ''
        },
        currentPage: 1,
        logsPerPage: 20,
        totalPages: 0,
        visiblePages: [],
        loading: false,
        viewMode: 'timeline',
        showAdvancedFilters: false,
        showReportModal: false,
        generatingReport: false,
        reportConfig: {
            type: '',
            format: '',
            start_date: '',
            end_date: '',
            include_summary: true,
            include_charts: true,
            include_details: false,
            include_recommendations: true
        },

        init() {
            this.loadLogs();
            this.loadStats();
            
            // Set default report dates to last 30 days
            const now = new Date();
            const thirtyDaysAgo = new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000));
            
            this.reportConfig.end_date = now.toISOString().slice(0, 16);
            this.reportConfig.start_date = thirtyDaysAgo.toISOString().slice(0, 16);
        },

        async loadLogs() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    time_range: this.filters.time_range,
                    action: this.filters.action,
                    resource_type: this.filters.resource_type,
                    user_role: this.filters.user_role,
                    ip_address: this.filters.ip_address,
                    user: this.filters.user,
                    search: this.filters.search,
                    date_from: this.filters.date_from,
                    date_to: this.filters.date_to,
                    risk_level: this.filters.risk_level,
                    success: this.filters.success
                });
                
                const response = await fetch(`/security/dashboard/audit/api?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.logs = data.data;
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Error loading logs:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/security/dashboard/audit/stats');
                const data = await response.json();
                
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async refreshLogs() {
            await this.loadLogs();
            await this.loadStats();
        },

        applyFilters() {
            let filtered = [...this.logs];

            // Apply all filters
            if (this.filters.action) {
                filtered = filtered.filter(log => log.action === this.filters.action);
            }

            if (this.filters.resource_type) {
                filtered = filtered.filter(log => log.resource_type === this.filters.resource_type);
            }

            if (this.filters.user_role && this.filters.user_role !== '') {
                filtered = filtered.filter(log => log.user && log.user.role === this.filters.user_role);
            }

            if (this.filters.ip_address) {
                filtered = filtered.filter(log => 
                    log.ip_address && log.ip_address.includes(this.filters.ip_address)
                );
            }

            if (this.filters.user) {
                const search = this.filters.user.toLowerCase();
                filtered = filtered.filter(log => 
                    log.user && log.user.name.toLowerCase().includes(search)
                );
            }

            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(log => 
                    log.description.toLowerCase().includes(search) ||
                    log.action.toLowerCase().includes(search) ||
                    log.resource_type.toLowerCase().includes(search)
                );
            }

            if (this.filters.risk_level) {
                filtered = filtered.filter(log => log.risk_level === this.filters.risk_level);
            }

            if (this.filters.success !== '') {
                const success = this.filters.success === 'true';
                filtered = filtered.filter(log => log.success === success);
            }

            this.filteredLogs = filtered;
            this.updatePagination();
        },

        updatePagination() {
            this.totalPages = Math.ceil(this.filteredLogs.length / this.logsPerPage);
            
            if (this.currentPage > this.totalPages) {
                this.currentPage = 1;
            }
            
            this.updateVisiblePages();
            this.updatePaginatedLogs();
        },

        updatePaginatedLogs() {
            const start = (this.currentPage - 1) * this.logsPerPage;
            const end = start + this.logsPerPage;
            this.paginatedLogs = this.filteredLogs.slice(start, end);
        },

        updateVisiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            this.visiblePages = pages;
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updatePaginatedLogs();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePaginatedLogs();
            }
        },

        goToPage(page) {
            this.currentPage = page;
            this.updatePaginatedLogs();
        },

        updateView() {
            // View mode changed, refresh pagination
            this.updatePaginatedLogs();
        },

        getActionClass(action) {
            const classes = {
                create: 'text-green-600',
                update: 'text-blue-600',
                delete: 'text-red-600',
                login: 'text-purple-600',
                logout: 'text-gray-600',
                security: 'text-orange-600'
            };
            return classes[action] || 'text-gray-600';
        },

        getActionBorderClass(action) {
            const classes = {
                create: 'action-create',
                update: 'action-update',
                delete: 'action-delete',
                login: 'action-login',
                logout: 'action-login',
                security: 'action-security'
            };
            return classes[action] || '';
        },

        formatAction(action) {
            return action.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString();
        },

        formatChangeValue(value) {
            if (value === null) return 'null';
            if (value === undefined) return 'undefined';
            if (typeof value === 'object') return JSON.stringify(value);
            return String(value);
        },

        async exportLogs() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/security/dashboard/audit/export?${params}`);
                const blob = await response.blob();
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `audit_logs_${new Date().toISOString().split('T')[0]}.csv`;
                
                document.body.appendChild(a);
                a.click();
                
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                alert('Audit logs exported successfully');
            } catch (error) {
                console.error('Error exporting logs:', error);
                alert('Error exporting logs');
            }
        },

        async generateReport() {
            this.generatingReport = true;
            try {
                const response = await fetch('/security/dashboard/audit/report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.reportConfig)
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `audit_report_${this.reportConfig.type}_${new Date().toISOString().split('T')[0]}.${this.reportConfig.format}`;
                    
                    document.body.appendChild(a);
                    a.click();
                    
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showReportModal = false;
                    alert('Report generated successfully');
                } else {
                    const data = await response.json();
                    alert('Error generating report: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error generating report:', error);
                alert('Error generating report');
            } finally {
                this.generatingReport = false;
            }
        }
    };
}
</script>
@endsection
