{{-- Admin User Management Interface --}}
{{-- Comprehensive user management with search, filters, role assignment, and account controls --}}

<div x-data="adminUserManagement()" x-init="init()" class="admin-user-management">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                    </svg>
                    User Management
                </h1>
                <p class="text-gray-600 mt-1">Manage user accounts, roles, permissions, and activity</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    @click="exportUsers()"
                    :disabled="isLoading"
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 disabled:opacity-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export
                </button>
                <button 
                    @click="showCreateModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Add User
                </button>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            {{-- Search Input --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        @input.debounce.300ms="fetchUsers()"
                        placeholder="Search by name, email, or ID..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button 
                        x-show="searchQuery"
                        @click="searchQuery = ''; fetchUsers();"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Role Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select 
                    x-model="filters.role"
                    @change="fetchUsers()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="agent">Agent</option>
                    <option value="customer">Customer</option>
                    <option value="scraper">Scraper</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select 
                    x-model="filters.status"
                    @change="fetchUsers()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending">Pending</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
        </div>

        {{-- Advanced Filters Toggle --}}
        <div class="mt-4">
            <button 
                @click="showAdvancedFilters = !showAdvancedFilters"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
            >
                <span x-text="showAdvancedFilters ? 'Hide' : 'Show'"></span> Advanced Filters
                <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': showAdvancedFilters }" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>

        {{-- Advanced Filters --}}
        <div x-show="showAdvancedFilters" x-collapse class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                {{-- Registration Date Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registered From</label>
                    <input 
                        type="date" 
                        x-model="filters.registeredFrom"
                        @change="fetchUsers()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registered To</label>
                    <input 
                        type="date" 
                        x-model="filters.registeredTo"
                        @change="fetchUsers()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                {{-- Last Login Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Login</label>
                    <select 
                        x-model="filters.lastLogin"
                        @change="fetchUsers()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Any time</option>
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                        <option value="3months">Last 3 months</option>
                        <option value="never">Never</option>
                    </select>
                </div>

                {{-- Email Verified --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Verified</label>
                    <select 
                        x-model="filters.emailVerified"
                        @change="fetchUsers()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All</option>
                        <option value="yes">Verified</option>
                        <option value="no">Unverified</option>
                    </select>
                </div>

                {{-- Clear Filters --}}
                <div class="flex items-end">
                    <button 
                        @click="clearFilters()"
                        class="w-full bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {{-- Table Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-700">
                        <span class="font-medium" x-text="totalUsers"></span> total users
                        <span x-show="filteredUsers !== totalUsers">
                            (<span x-text="filteredUsers"></span> filtered)
                        </span>
                    </div>
                    
                    {{-- Bulk Actions --}}
                    <div x-show="selectedUsers.length > 0" class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">
                            <span x-text="selectedUsers.length"></span> selected
                        </span>
                        <button 
                            @click="showBulkActionsMenu = !showBulkActionsMenu"
                            class="relative bg-blue-100 text-blue-700 px-3 py-1 rounded-lg hover:bg-blue-200 text-sm"
                        >
                            Bulk Actions
                            <div x-show="showBulkActionsMenu" @click.away="showBulkActionsMenu = false" 
                                 class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                <button @click="bulkAction('suspend')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Suspend Users</button>
                                <button @click="bulkAction('activate')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Activate Users</button>
                                <button @click="bulkAction('change-role')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Change Role</button>
                                <button @click="bulkAction('export')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Export Selected</button>
                                <button @click="bulkAction('delete')" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete Users</button>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Sort Controls --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Sort by:</label>
                    <select 
                        x-model="sortBy"
                        @change="fetchUsers()"
                        class="text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="created_at">Registration Date</option>
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                        <option value="last_login">Last Login</option>
                        <option value="role">Role</option>
                    </select>
                    <button 
                        @click="sortOrder = (sortOrder === 'asc') ? 'desc' : 'asc'; fetchUsers()"
                        class="p-1 rounded hover:bg-gray-200"
                    >
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': sortOrder === 'desc' }" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Loading State --}}
        <div x-show="isLoading" class="p-8 text-center">
            <div class="inline-flex items-center px-4 py-2 text-sm text-gray-600">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading users...
            </div>
        </div>

        {{-- Users Table --}}
        <div x-show="!isLoading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input 
                                type="checkbox" 
                                :checked="users.length > 0 && selectedUsers.length === users.length"
                                @change="toggleSelectAll()"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in users" :key="user.id">
                        <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedUsers.includes(user.id) }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input 
                                    type="checkbox" 
                                    :value="user.id"
                                    x-model="selectedUsers"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="relative">
                                        <img 
                                            :src="user.avatar || getDefaultAvatar(user.name)" 
                                            :alt="user.name"
                                            class="h-10 w-10 rounded-full"
                                            @error="$event.target.src = getDefaultAvatar(user.name)"
                                        >
                                        <div 
                                            x-show="user.is_online"
                                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
                                        ></div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="user.name"></div>
                                        <div class="text-sm text-gray-500" x-text="user.email"></div>
                                        <div class="text-xs text-gray-400">
                                            ID: <span x-text="user.id"></span> • 
                                            Joined <span x-text="formatDate(user.created_at)"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      :class="getRoleBadgeClass(user.role)" x-text="user.role"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          :class="getStatusBadgeClass(user.status)" x-text="user.status"></span>
                                    <div x-show="user.email_verified_at" class="ml-2" title="Email verified">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div x-text="user.last_login ? formatDateTime(user.last_login) : 'Never'"></div>
                                <div x-show="user.last_login" class="text-xs text-gray-400" x-text="formatRelativeTime(user.last_login)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center gap-4">
                                    <div x-show="user.tickets_purchased" class="text-center">
                                        <div class="text-sm font-medium text-gray-900" x-text="user.tickets_purchased || 0"></div>
                                        <div class="text-xs text-gray-500">Tickets</div>
                                    </div>
                                    <div x-show="user.total_spent" class="text-center">
                                        <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(user.total_spent || 0)"></div>
                                        <div class="text-xs text-gray-500">Spent</div>
                                    </div>
                                    <div x-show="user.login_count" class="text-center">
                                        <div class="text-sm font-medium text-gray-900" x-text="user.login_count || 0"></div>
                                        <div class="text-xs text-gray-500">Logins</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="viewUser(user)"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                                        title="View Details"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        @click="editUser(user)"
                                        class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50"
                                        title="Edit User"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <div class="relative">
                                        <button 
                                            @click="toggleActionMenu(user.id)"
                                            class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50"
                                            title="More Actions"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            </svg>
                                        </button>
                                        <div x-show="activeActionMenu === user.id" @click.away="activeActionMenu = null"
                                             class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <button @click="loginAsUser(user)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Login as User</button>
                                            <button @click="viewActivityLog(user)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Activity Log</button>
                                            <button @click="resetPassword(user)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset Password</button>
                                            <div class="border-t border-gray-100"></div>
                                            <button 
                                                @click="toggleUserStatus(user)" 
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50"
                                                :class="user.status === 'active' ? 'text-yellow-700' : 'text-green-700'"
                                                x-text="user.status === 'active' ? 'Suspend User' : 'Activate User'"
                                            ></button>
                                            <button @click="deleteUser(user)" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete User</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Empty State --}}
            <div x-show="!isLoading && users.length === 0" class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-600">Try adjusting your search or filter criteria.</p>
            </div>
        </div>

        {{-- Pagination --}}
        <div x-show="!isLoading && users.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                    <span x-text="Math.min(currentPage * perPage, filteredUsers)"></span> of 
                    <span x-text="filteredUsers"></span> results
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    
                    <template x-for="page in getPageNumbers()" :key="page">
                        <button 
                            @click="goToPage(page)"
                            :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg"
                            x-text="page"
                        ></button>
                    </template>
                    
                    <button 
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage >= totalPages"
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- User Detail Modal --}}
    <div x-show="showUserModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUserModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                <div>
                    <div class="flex items-start justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">User Details</h3>
                        <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div x-show="selectedUser" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- User Info --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <img 
                                    :src="selectedUser?.avatar || getDefaultAvatar(selectedUser?.name)" 
                                    :alt="selectedUser?.name"
                                    class="h-16 w-16 rounded-full"
                                >
                                <div>
                                    <h4 class="text-xl font-semibold" x-text="selectedUser?.name"></h4>
                                    <p class="text-gray-600" x-text="selectedUser?.email"></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              :class="getRoleBadgeClass(selectedUser?.role)" x-text="selectedUser?.role"></span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              :class="getStatusBadgeClass(selectedUser?.status)" x-text="selectedUser?.status"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">User ID</dt>
                                    <dd class="text-sm text-gray-900" x-text="selectedUser?.id"></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Joined</dt>
                                    <dd class="text-sm text-gray-900" x-text="formatDate(selectedUser?.created_at)"></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                                    <dd class="text-sm text-gray-900" x-text="selectedUser?.last_login ? formatDateTime(selectedUser?.last_login) : 'Never'"></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                                    <dd class="text-sm text-gray-900" x-text="selectedUser?.email_verified_at ? 'Yes' : 'No'"></dd>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Activity Stats --}}
                        <div class="space-y-4">
                            <h5 class="font-medium text-gray-900">Activity Summary</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <dt class="text-sm font-medium text-blue-600">Tickets Purchased</dt>
                                    <dd class="text-2xl font-bold text-blue-900" x-text="selectedUser?.tickets_purchased || 0"></dd>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <dt class="text-sm font-medium text-green-600">Total Spent</dt>
                                    <dd class="text-2xl font-bold text-green-900" x-text="formatCurrency(selectedUser?.total_spent || 0)"></dd>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-600">Login Count</dt>
                                    <dd class="text-2xl font-bold text-purple-900" x-text="selectedUser?.login_count || 0"></dd>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <dt class="text-sm font-medium text-orange-600">Support Tickets</dt>
                                    <dd class="text-2xl font-bold text-orange-900" x-text="selectedUser?.support_tickets || 0"></dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                        <button 
                            @click="showUserModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Close
                        </button>
                        <button 
                            @click="editUser(selectedUser)"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700"
                        >
                            Edit User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adminUserManagement() {
    return {
        // Data
        users: [],
        selectedUser: null,
        selectedUsers: [],
        totalUsers: 0,
        filteredUsers: 0,
        
        // UI State
        isLoading: false,
        showUserModal: false,
        showCreateModal: false,
        showAdvancedFilters: false,
        showBulkActionsMenu: false,
        activeActionMenu: null,
        
        // Search and Filters
        searchQuery: '',
        filters: {
            role: '',
            status: '',
            registeredFrom: '',
            registeredTo: '',
            lastLogin: '',
            emailVerified: ''
        },
        
        // Pagination
        currentPage: 1,
        perPage: 25,
        totalPages: 1,
        
        // Sorting
        sortBy: 'created_at',
        sortOrder: 'desc',
        
        init() {
            this.fetchUsers();
            console.log('[AdminUserMgmt] Initialized');
        },
        
        async fetchUsers() {
            this.isLoading = true;
            
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort_by: this.sortBy,
                    sort_order: this.sortOrder,
                    search: this.searchQuery,
                    ...this.filters
                });
                
                const response = await fetch(`/api/admin/users?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.users = data.users;
                    this.totalUsers = data.total;
                    this.filteredUsers = data.filtered;
                    this.totalPages = Math.ceil(data.filtered / this.perPage);
                    this.selectedUsers = []; // Clear selection on new fetch
                } else {
                    console.error('[AdminUserMgmt] Failed to fetch users:', data.error);
                }
            } catch (error) {
                console.error('[AdminUserMgmt] Error fetching users:', error);
                
                // Generate sample data for demo
                this.generateSampleUsers();
            } finally {
                this.isLoading = false;
            }
        },
        
        generateSampleUsers() {
            const roles = ['admin', 'agent', 'customer', 'scraper'];
            const statuses = ['active', 'suspended', 'pending', 'banned'];
            const names = ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Wilson', 'David Brown', 'Lisa Davis', 'Tom Wilson', 'Emma Taylor'];
            
            this.users = Array.from({ length: this.perPage }, (_, i) => {
                const name = names[i % names.length];
                const email = `${name.toLowerCase().replace(/\s+/g, '.')}${i + 1}@example.com`;
                
                return {
                    id: (this.currentPage - 1) * this.perPage + i + 1,
                    name: name,
                    email: email,
                    avatar: null,
                    role: roles[Math.floor(Math.random() * roles.length)],
                    status: statuses[Math.floor(Math.random() * statuses.length)],
                    is_online: Math.random() > 0.7,
                    created_at: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000),
                    last_login: Math.random() > 0.2 ? new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000) : null,
                    email_verified_at: Math.random() > 0.3 ? new Date() : null,
                    tickets_purchased: Math.floor(Math.random() * 50),
                    total_spent: Math.floor(Math.random() * 5000),
                    login_count: Math.floor(Math.random() * 100),
                    support_tickets: Math.floor(Math.random() * 10)
                };
            });
            
            this.totalUsers = 1247; // Sample total
            this.filteredUsers = this.searchQuery || Object.values(this.filters).some(f => f) ? 
                Math.floor(Math.random() * 500) + 50 : this.totalUsers;
            this.totalPages = Math.ceil(this.filteredUsers / this.perPage);
        },
        
        // Search and Filter Methods
        clearFilters() {
            this.searchQuery = '';
            this.filters = {
                role: '',
                status: '',
                registeredFrom: '',
                registeredTo: '',
                lastLogin: '',
                emailVerified: ''
            };
            this.currentPage = 1;
            this.fetchUsers();
        },
        
        // Pagination Methods
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.fetchUsers();
            }
        },
        
        getPageNumbers() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
        
        // Selection Methods
        toggleSelectAll() {
            if (this.selectedUsers.length === this.users.length) {
                this.selectedUsers = [];
            } else {
                this.selectedUsers = this.users.map(user => user.id);
            }
        },
        
        // User Actions
        viewUser(user) {
            this.selectedUser = user;
            this.showUserModal = true;
            this.activeActionMenu = null;
        },
        
        editUser(user) {
            // Implement edit user functionality
            console.log('Edit user:', user);
            this.showUserModal = false;
            this.activeActionMenu = null;
        },
        
        toggleActionMenu(userId) {
            this.activeActionMenu = this.activeActionMenu === userId ? null : userId;
        },
        
        async loginAsUser(user) {
            try {
                const response = await fetch(`/api/admin/users/${user.id}/login-as`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    window.open(data.url, '_blank');
                }
            } catch (error) {
                console.error('[AdminUserMgmt] Error logging in as user:', error);
            }
            this.activeActionMenu = null;
        },
        
        viewActivityLog(user) {
            // Implement activity log viewer
            console.log('View activity log for user:', user);
            this.activeActionMenu = null;
        },
        
        async resetPassword(user) {
            if (confirm(`Reset password for ${user.name}? They will receive an email with reset instructions.`)) {
                try {
                    const response = await fetch(`/api/admin/users/${user.id}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        alert('Password reset email sent successfully.');
                    }
                } catch (error) {
                    console.error('[AdminUserMgmt] Error resetting password:', error);
                }
            }
            this.activeActionMenu = null;
        },
        
        async toggleUserStatus(user) {
            const newStatus = user.status === 'active' ? 'suspended' : 'active';
            const action = newStatus === 'suspended' ? 'suspend' : 'activate';
            
            if (confirm(`Are you sure you want to ${action} ${user.name}?`)) {
                try {
                    user.status = newStatus; // Optimistic update
                    
                    const response = await fetch(`/api/admin/users/${user.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    
                    const data = await response.json();
                    if (!data.success) {
                        user.status = user.status === 'active' ? 'suspended' : 'active'; // Revert on error
                    }
                } catch (error) {
                    console.error('[AdminUserMgmt] Error updating user status:', error);
                    user.status = user.status === 'active' ? 'suspended' : 'active'; // Revert on error
                }
            }
            this.activeActionMenu = null;
        },
        
        async deleteUser(user) {
            if (confirm(`Are you sure you want to permanently delete ${user.name}? This action cannot be undone.`)) {
                try {
                    const response = await fetch(`/api/admin/users/${user.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        this.users = this.users.filter(u => u.id !== user.id);
                        this.filteredUsers--;
                        this.totalUsers--;
                    }
                } catch (error) {
                    console.error('[AdminUserMgmt] Error deleting user:', error);
                }
            }
            this.activeActionMenu = null;
        },
        
        // Bulk Actions
        async bulkAction(action) {
            if (this.selectedUsers.length === 0) return;
            
            const actionNames = {
                suspend: 'suspend',
                activate: 'activate',
                'change-role': 'change roles for',
                export: 'export',
                delete: 'delete'
            };
            
            const confirmMessage = `Are you sure you want to ${actionNames[action]} ${this.selectedUsers.length} user(s)?`;
            if (!confirm(confirmMessage)) return;
            
            try {
                const response = await fetch(`/api/admin/users/bulk-action`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: action,
                        user_ids: this.selectedUsers
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    if (action === 'export') {
                        // Handle file download
                        window.location.href = data.download_url;
                    } else {
                        this.fetchUsers(); // Refresh the list
                    }
                }
            } catch (error) {
                console.error('[AdminUserMgmt] Error performing bulk action:', error);
            }
            
            this.showBulkActionsMenu = false;
            this.selectedUsers = [];
        },
        
        async exportUsers() {
            try {
                const params = new URLSearchParams({
                    search: this.searchQuery,
                    ...this.filters
                });
                
                window.location.href = `/api/admin/users/export?${params}`;
            } catch (error) {
                console.error('[AdminUserMgmt] Error exporting users:', error);
            }
        },
        
        // Utility Methods
        getDefaultAvatar(name) {
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&size=40&background=random`;
        },
        
        getRoleBadgeClass(role) {
            const classes = {
                admin: 'bg-red-100 text-red-800',
                agent: 'bg-green-100 text-green-800',
                customer: 'bg-blue-100 text-blue-800',
                scraper: 'bg-purple-100 text-purple-800'
            };
            return classes[role] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusBadgeClass(status) {
            const classes = {
                active: 'bg-green-100 text-green-800',
                suspended: 'bg-yellow-100 text-yellow-800',
                pending: 'bg-orange-100 text-orange-800',
                banned: 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(date) {
            return new Date(date).toLocaleDateString();
        },
        
        formatDateTime(date) {
            return new Date(date).toLocaleString();
        },
        
        formatRelativeTime(date) {
            const diff = Date.now() - new Date(date).getTime();
            const days = Math.floor(diff / (24 * 60 * 60 * 1000));
            const hours = Math.floor(diff / (60 * 60 * 1000));
            const minutes = Math.floor(diff / (60 * 1000));
            
            if (days > 0) return `${days}d ago`;
            if (hours > 0) return `${hours}h ago`;
            if (minutes > 0) return `${minutes}m ago`;
            return 'Just now';
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }
    };
}
</script>