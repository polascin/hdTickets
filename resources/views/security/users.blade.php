@extends('layouts.app')

@section('title', 'User Security Management - HD Tickets')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .user-card { transition: all 0.3s ease; }
    .user-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .role-admin { border-left: 4px solid #dc2626; }
    .role-agent { border-left: 4px solid #2563eb; }
    .role-customer { border-left: 4px solid #16a34a; }
    .role-scraper { border-left: 4px solid #7c3aed; }
    .status-active { color: #16a34a; }
    .status-inactive { color: #dc2626; }
    .status-locked { color: #ea580c; }
    .security-high { background-color: #fef3c7; }
    .security-medium { background-color: #dbeafe; }
    .security-low { background-color: #d1fae5; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="userSecurityManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">User Security Management</h1>
                <p class="mt-2 text-lg text-gray-600">Manage user accounts, roles, and security settings</p>
            </div>
            <div class="flex space-x-3">
                <button @click="refreshUsers()" 
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
                <button @click="exportUsers()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                    Export Users
                </button>
            </div>
        </div>

        <!-- User Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900" x-text="stats.total_users"></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-3xl font-bold text-green-600" x-text="stats.active_users"></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">MFA Enabled</p>
                        <p class="text-3xl font-bold text-indigo-600" x-text="stats.mfa_enabled"></p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">High Risk</p>
                        <p class="text-3xl font-bold text-red-600" x-text="stats.high_risk_users"></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Locked Accounts</p>
                        <p class="text-3xl font-bold text-orange-600" x-text="stats.locked_accounts"></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select x-model="filters.role" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                        <option value="customer">Customer</option>
                        <option value="scraper">Scraper</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select x-model="filters.status" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="locked">Locked</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">MFA</label>
                    <select x-model="filters.mfa" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All MFA Status</option>
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>
                    </select>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Login</label>
                    <select x-model="filters.last_login" @change="applyFilters()" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Any Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="never">Never</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input x-model="filters.search" @input="applyFilters()" 
                           type="text" placeholder="Search users..."
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="space-y-4">
            <template x-for="user in paginatedUsers" :key="user.id">
                <div class="user-card bg-white rounded-lg shadow p-6" 
                     :class="`role-${user.role.name}`">
                    
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <img class="h-12 w-12 rounded-full" 
                                     :src="user.avatar || '/images/default-avatar.png'" 
                                     :alt="user.name">
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900" x-text="user.name"></h3>
                                    
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                  ${user.role.name === 'admin' ? 'bg-red-100 text-red-800' :
                                                    user.role.name === 'agent' ? 'bg-blue-100 text-blue-800' :
                                                    user.role.name === 'customer' ? 'bg-green-100 text-green-800' :
                                                    'bg-purple-100 text-purple-800'}`" 
                                          x-text="user.role.name.toUpperCase()"></span>
                                    
                                    <span :class="`status-${user.status} text-xs font-medium`" 
                                          x-text="user.status.toUpperCase()"></span>
                                    
                                    <span x-show="user.mfa_enabled" 
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        MFA
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600" x-text="user.email"></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div :class="`w-3 h-3 rounded-full 
                                         ${user.security_score >= 80 ? 'bg-green-400' :
                                           user.security_score >= 60 ? 'bg-yellow-400' :
                                           'bg-red-400'}`"
                                 :title="`Security Score: ${user.security_score}`"></div>
                            
                            <button @click="viewUserDetails(user)" 
                                    class="text-gray-400 hover:text-blue-600"
                                    title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            
                            <button @click="editUserSecurity(user)" 
                                    class="text-gray-400 hover:text-green-600"
                                    title="Edit Security">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="font-medium text-gray-600">Last Login:</span>
                            <span x-text="user.last_login_at ? formatDate(user.last_login_at) : 'Never'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Security Score:</span>
                            <span x-text="user.security_score + '%'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Failed Logins:</span>
                            <span x-text="user.failed_login_attempts || 0"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Created:</span>
                            <span x-text="formatDate(user.created_at)"></span>
                        </div>
                    </div>

                    <div x-show="user.recent_activities && user.recent_activities.length > 0" 
                         class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Recent Security Activities</h4>
                        <div class="space-y-1">
                            <template x-for="activity in user.recent_activities.slice(0, 3)" :key="activity.id">
                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span x-text="activity.event_type.replace('_', ' ').toUpperCase()"></span>
                                    <span x-text="formatDate(activity.occurred_at)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="flex items-center justify-end space-x-3 mt-4 pt-4 border-t border-gray-200">
                        <button @click="resetMFA(user)" 
                                x-show="user.mfa_enabled"
                                class="text-xs bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full hover:bg-yellow-200">
                            Reset MFA
                        </button>
                        
                        <button @click="toggleUserStatus(user)" 
                                :class="user.status === 'active' ? 
                                       'text-xs bg-red-100 text-red-800 px-3 py-1 rounded-full hover:bg-red-200' :
                                       'text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full hover:bg-green-200'"
                                x-text="user.status === 'active' ? 'Suspend' : 'Activate'">
                        </button>
                        
                        <button @click="changeUserRole(user)" 
                                class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full hover:bg-blue-200">
                            Change Role
                        </button>
                    </div>
                </div>
            </template>

            <div x-show="filteredUsers.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                <p class="mt-1 text-sm text-gray-500">No users match your current filters.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="filteredUsers.length > usersPerPage" 
             class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-b-lg shadow">
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
                        Showing <span x-text="((currentPage - 1) * usersPerPage) + 1"></span> to 
                        <span x-text="Math.min(currentPage * usersPerPage, filteredUsers.length)"></span> of 
                        <span x-text="filteredUsers.length"></span> results
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

    <!-- User Details Modal -->
    <div x-show="showUserModal" x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-medium text-gray-900">User Security Details</h3>
                    <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div x-show="selectedUser" class="space-y-6">
                    <!-- User Overview -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center space-x-4 mb-4">
                            <img class="h-16 w-16 rounded-full" 
                                 :src="selectedUser?.avatar || '/images/default-avatar.png'" 
                                 :alt="selectedUser?.name">
                            <div>
                                <h4 class="text-lg font-semibold" x-text="selectedUser?.name"></h4>
                                <p class="text-gray-600" x-text="selectedUser?.email"></p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" 
                                          x-text="selectedUser?.role?.name.toUpperCase()"></span>
                                    <span :class="`status-${selectedUser?.status} text-xs font-medium`" 
                                          x-text="selectedUser?.status.toUpperCase()"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><span class="font-medium">Security Score:</span> <span x-text="selectedUser?.security_score + '%'"></span></div>
                            <div><span class="font-medium">MFA Enabled:</span> <span x-text="selectedUser?.mfa_enabled ? 'Yes' : 'No'"></span></div>
                            <div><span class="font-medium">Phone Verified:</span> <span x-text="selectedUser?.phone_verified ? 'Yes' : 'No'"></span></div>
                            <div><span class="font-medium">Last Login:</span> <span x-text="selectedUser?.last_login_at ? formatDate(selectedUser.last_login_at) : 'Never'"></span></div>
                        </div>
                    </div>

                    <!-- Recent Security Events -->
                    <div>
                        <h5 class="text-lg font-medium text-gray-900 mb-3">Recent Security Events</h5>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <template x-for="event in selectedUser?.recent_events" :key="event.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium" x-text="event.event_type.replace('_', ' ').toUpperCase()"></span>
                                        <p class="text-sm text-gray-600" x-text="event.details"></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500" x-text="formatDate(event.occurred_at)"></div>
                                        <div class="text-xs" :class="event.threat_score >= 70 ? 'text-red-600' : event.threat_score >= 40 ? 'text-orange-600' : 'text-green-600'">
                                            Score: <span x-text="event.threat_score"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button @click="resetUserMFA(selectedUser)" 
                                x-show="selectedUser?.mfa_enabled"
                                class="px-4 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200">
                            Reset MFA
                        </button>
                        <button @click="lockUserAccount(selectedUser)" 
                                x-show="selectedUser?.status !== 'locked'"
                                class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200">
                            Lock Account
                        </button>
                        <button @click="showUserModal = false" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Change Modal -->
    <div x-show="showRoleModal" x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change User Role</h3>
                
                <div x-show="roleChangeUser" class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Changing role for: <span class="font-medium" x-text="roleChangeUser?.name"></span></p>
                        <p class="text-sm text-gray-500">Current role: <span x-text="roleChangeUser?.role?.name"></span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Role</label>
                        <select x-model="newRole" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Role</option>
                            <option value="customer">Customer</option>
                            <option value="agent">Agent</option>
                            <option value="admin">Admin</option>
                            <option value="scraper">Scraper</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button @click="showRoleModal = false" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button @click="confirmRoleChange()" 
                                :disabled="!newRole || changingRole"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!changingRole">Change Role</span>
                            <span x-show="changingRole">Changing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function userSecurityManager() {
    return {
        users: [],
        filteredUsers: [],
        paginatedUsers: [],
        selectedUser: null,
        roleChangeUser: null,
        stats: {
            total_users: 0,
            active_users: 0,
            mfa_enabled: 0,
            high_risk_users: 0,
            locked_accounts: 0
        },
        filters: {
            role: '',
            status: '',
            mfa: '',
            risk_level: '',
            last_login: '',
            search: ''
        },
        currentPage: 1,
        usersPerPage: 10,
        totalPages: 0,
        visiblePages: [],
        loading: false,
        showUserModal: false,
        showRoleModal: false,
        newRole: '',
        changingRole: false,

        init() {
            this.loadUsers();
            this.loadStats();
        },

        async loadUsers() {
            this.loading = true;
            try {
                const response = await fetch('/security/dashboard/users/api');
                const data = await response.json();
                
                if (data.success) {
                    this.users = data.data;
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Error loading users:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/security/dashboard/users/stats');
                const data = await response.json();
                
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async refreshUsers() {
            await this.loadUsers();
            await this.loadStats();
        },

        applyFilters() {
            let filtered = [...this.users];

            if (this.filters.role) {
                filtered = filtered.filter(user => user.role.name === this.filters.role);
            }

            if (this.filters.status) {
                filtered = filtered.filter(user => user.status === this.filters.status);
            }

            if (this.filters.mfa) {
                filtered = filtered.filter(user => 
                    this.filters.mfa === 'enabled' ? user.mfa_enabled : !user.mfa_enabled
                );
            }

            if (this.filters.risk_level) {
                filtered = filtered.filter(user => {
                    const score = user.security_score;
                    return this.filters.risk_level === 'high' ? score < 60 :
                           this.filters.risk_level === 'medium' ? score >= 60 && score < 80 :
                           score >= 80;
                });
            }

            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(user => 
                    user.name.toLowerCase().includes(search) ||
                    user.email.toLowerCase().includes(search)
                );
            }

            this.filteredUsers = filtered;
            this.updatePagination();
        },

        updatePagination() {
            this.totalPages = Math.ceil(this.filteredUsers.length / this.usersPerPage);
            
            if (this.currentPage > this.totalPages) {
                this.currentPage = 1;
            }
            
            this.updateVisiblePages();
            this.updatePaginatedUsers();
        },

        updatePaginatedUsers() {
            const start = (this.currentPage - 1) * this.usersPerPage;
            const end = start + this.usersPerPage;
            this.paginatedUsers = this.filteredUsers.slice(start, end);
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
                this.updatePaginatedUsers();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePaginatedUsers();
            }
        },

        goToPage(page) {
            this.currentPage = page;
            this.updatePaginatedUsers();
        },

        async viewUserDetails(user) {
            try {
                const response = await fetch(`/security/dashboard/users/${user.id}/details`);
                const data = await response.json();
                
                if (data.success) {
                    this.selectedUser = data.data;
                    this.showUserModal = true;
                }
            } catch (error) {
                console.error('Error loading user details:', error);
            }
        },

        editUserSecurity(user) {
            // Navigate to user security editing page
            window.location.href = `/security/dashboard/users/${user.id}/security`;
        },

        changeUserRole(user) {
            this.roleChangeUser = user;
            this.newRole = '';
            this.showRoleModal = true;
        },

        async confirmRoleChange() {
            this.changingRole = true;
            try {
                const response = await fetch(`/security/dashboard/users/${this.roleChangeUser.id}/role`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ role: this.newRole })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showRoleModal = false;
                    await this.refreshUsers();
                    alert('User role changed successfully');
                } else {
                    alert('Error changing role: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error changing role:', error);
                alert('Error changing user role');
            } finally {
                this.changingRole = false;
            }
        },

        async toggleUserStatus(user) {
            const newStatus = user.status === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'suspend';
            
            if (confirm(`Are you sure you want to ${action} ${user.name}?`)) {
                try {
                    const response = await fetch(`/security/dashboard/users/${user.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: newStatus })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        await this.refreshUsers();
                        alert(`User ${action}d successfully`);
                    } else {
                        alert('Error updating status: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error updating status:', error);
                    alert('Error updating user status');
                }
            }
        },

        async resetMFA(user) {
            if (confirm(`Are you sure you want to reset MFA for ${user.name}?`)) {
                try {
                    const response = await fetch(`/security/dashboard/users/${user.id}/reset-mfa`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        await this.refreshUsers();
                        alert('MFA reset successfully');
                    } else {
                        alert('Error resetting MFA: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error resetting MFA:', error);
                    alert('Error resetting MFA');
                }
            }
        },

        async exportUsers() {
            try {
                const response = await fetch('/security/dashboard/users/export');
                const blob = await response.blob();
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `users_export_${new Date().toISOString().split('T')[0]}.csv`;
                
                document.body.appendChild(a);
                a.click();
                
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                alert('Users exported successfully');
            } catch (error) {
                console.error('Error exporting users:', error);
                alert('Error exporting users');
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
    };
}
</script>
@endsection
