@extends('layouts.app-v2')

@section('title', 'User Management')
@section('description', 'Manage users, roles, and permissions in the Sports Events Tickets platform')

@push('styles')
<style>
    /* User Management Styles */
    .table-mobile { overflow-x: auto; }
    .search-mobile { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .actions-mobile { flex-wrap: wrap; gap: 0.25rem; }
    .pagination-mobile { flex-direction: column; gap: 1rem; }
    .modal-mobile { width: 90%; max-width: 500px; }
    
    .role-badge {
        @apply px-3 py-1 text-xs font-semibold rounded-full;
    }
    
    .role-customer {
        @apply bg-blue-100 text-blue-800;
    }
    
    .role-agent {
        @apply bg-purple-100 text-purple-800;
    }
    
    .role-admin {
        @apply bg-red-100 text-red-800;
    }
    
    .role-scraper {
        @apply bg-gray-100 text-gray-800;
    }
    
    .subscription-badge {
        @apply px-2 py-1 text-xs font-medium rounded;
    }
    
    .subscription-active {
        @apply bg-green-100 text-green-800;
    }
    
    .subscription-trial {
        @apply bg-yellow-100 text-yellow-800;
    }
    
    .subscription-expired {
        @apply bg-red-100 text-red-800;
    }
    
    .subscription-none {
        @apply bg-gray-100 text-gray-800;
    }
    
    .user-avatar {
        @apply h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm;
    }
    
    .stats-card {
        @apply bg-white rounded-lg shadow p-4 border border-gray-200;
    }
    
    @media (min-width: 768px) {
        .pagination-mobile { flex-direction: row; gap: 0; }
        .modal-mobile { width: 500px; }
    }
</style>
@endpush

@section('content')
        <!-- Simple Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Admin Panel</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('admin.users.index') }}" class="text-blue-600 font-medium">Users</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

<div class="py-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600 mt-2">Manage users, roles, and permissions for the Sports Events Tickets platform</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 lg:mt-0">
            <button onclick="exportUsers()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                Export
            </button>
            <button onclick="openQuickCreateModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Quick Add
            </button>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create User
            </a>
        </div>
    </div>
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm animate-pulse">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm animate-pulse">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Advanced Search & Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow-lg border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                                </svg>
                                Search & Filters
                            </h3>
                            <div class="flex items-center space-x-2">
                                @if(request()->hasAny(['search', 'role', 'status', 'date_from', 'date_to']))
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Clear All
                                    </a>
                                @endif
                                <button type="button" onclick="toggleFilters()" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span id="filter-toggle-text">Show Filters</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="relative mb-4">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, username, or phone..." class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm shadow-sm transition duration-200">
                        </div>
                        
                        <!-- Advanced Filters -->
                        <div id="advanced-filters" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 search-mobile">
                                <!-- Role Filter -->
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                    <select name="role" id="role" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="all" {{ request('role', 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                                {{ ucfirst($role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Status Filter -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Email Verified</option>
                                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Email Unverified</option>
                                    </select>
                                </div>
                                
                                <!-- Date From -->
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Created From</label>
                                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <!-- Date To -->
                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Created To</label>
                                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            
                            <!-- Per Page Selection -->
                            <div class="flex items-center space-x-4">
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Items per page</label>
                                    <select name="per_page" id="per_page" class="block w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preserve sorting parameters -->
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                        
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @php
            $totalUsers = $users->total() ?? 0;
            $activeUsers = \App\Models\User::where('is_active', true)->count() ?? 0;
            $customers = \App\Models\User::where('role', 'customer')->count() ?? 0;
            $verifiedUsers = \App\Models\User::whereNotNull('email_verified_at')->count() ?? 0;
        @endphp

        <div class="stats-card">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</h3>
                    <p class="text-sm text-gray-600">Total Users</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($activeUsers) }}</h3>
                    <p class="text-sm text-gray-600">Active Users</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($customers) }}</h3>
                    <p class="text-sm text-gray-600">Customers</p>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center">
                <div class="p-3 bg-teal-100 rounded-lg">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($verifiedUsers) }}</h3>
                    <p class="text-sm text-gray-600">Verified Emails</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bulk Actions (Hidden by default) -->
    <div id="bulkActions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm font-medium text-blue-900 mr-4">
                    <span id="selectedCount">0</span> user(s) selected
                </span>
                <div class="flex flex-wrap gap-2">
                    <button onclick="submitBulkAction('activate')" class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Enable
                    </button>
                    <button onclick="submitBulkAction('deactivate')" class="inline-flex items-center px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Disable
                    </button>
                    <button onclick="showRoleModal()" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Update Role
                    </button>
                    <button onclick="submitBulkAction('export')" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
            <button onclick="clearBulkSelection()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

            <!-- Users Table Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Users Directory
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                            {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
                        </span>
                    </h3>
                </div>
                
                <div class="overflow-x-auto table-mobile">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
<tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" onclick="toggleSelectAll()">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button onclick="sortTable('name')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200">
                                        <span>Full Name</span>
                                        <svg class="w-4 h-4 {{ request('sort_by') === 'name' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(request('sort_by') === 'name' && request('sort_order') === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            @elseif(request('sort_by') === 'name' && request('sort_order') === 'desc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            @endif
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button onclick="sortTable('email')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200">
                                        <span>Email</span>
                                        <svg class="w-4 h-4 {{ request('sort_by') === 'email' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(request('sort_by') === 'email' && request('sort_order') === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            @elseif(request('sort_by') === 'email' && request('sort_order') === 'desc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            @endif
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button onclick="sortTable('role')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200">
                                        <span>Role</span>
                                        <svg class="w-4 h-4 {{ request('sort_by') === 'role' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(request('sort_by') === 'role' && request('sort_order') === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            @elseif(request('sort_by') === 'role' && request('sort_order') === 'desc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            @endif
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button onclick="sortTable('is_active')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200">
                                        <span>Status</span>
                                        <svg class="w-4 h-4 {{ request('sort_by') === 'is_active' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(request('sort_by') === 'is_active' && request('sort_order') === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            @elseif(request('sort_by') === 'is_active' && request('sort_order') === 'desc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            @endif
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <button onclick="sortTable('created_at')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200">
                                        <span>Joined</span>
                                        <svg class="w-4 h-4 {{ request('sort_by') === 'created_at' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if(request('sort_by') === 'created_at' && request('sort_order') === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            @elseif(request('sort_by') === 'created_at' && request('sort_order') === 'desc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            @endif
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <span>Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
<td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 1) . substr($user->surname ?? '', 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 rounded px-2 py-1" onclick="enableInlineEditing('name', {{ $user->id }}, '{{ $user->name }}')">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 cursor-pointer hover:bg-gray-100 rounded px-2 py-1" onclick="enableInlineEditing('email', {{ $user->id }}, '{{ $user->email }}')">{{ $user->email }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center text-green-600">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-amber-600">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Unverified
                                            </span>
                                        @endif
                                    </div>
                                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold role-badge role-{{ $user->role }}">
                                        @if($user->role === 'admin')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($user->role === 'agent')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($user->role === 'scraper')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13 10V3L4 14h7v7l9-11h-7z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if($user->role === 'customer')
                                        @php
                                            // Determine subscription status - sample logic, adjust based on your app
                                            $hasSub = false; // Should check actual subscription
                                            $inFreeTrial = $user->created_at->diffInDays(now()) <= 7;
                                            $subscriptionStatus = $hasSub ? 'active' : ($inFreeTrial ? 'trial' : 'none');
                                        @endphp
                                        <div class="mt-1">
                                            <span class="subscription-badge subscription-{{ $subscriptionStatus }}">
                                                @if($hasSub)
                                                    Active Subscription
                                                @elseif($inFreeTrial)
                                                    Free Trial
                                                @else
                                                    No Subscription
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        @if($user->is_active) bg-gradient-to-r from-green-100 to-green-200 text-green-800 @else bg-gradient-to-r from-red-100 to-red-200 text-red-800 @endif">
                                        @if($user->is_active)
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs">{{ $user->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-wrap items-center gap-1 actions-mobile">
                                        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-md transition-colors duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-2 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded-md transition-colors duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium rounded-md transition-colors duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                </svg>
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        @if($user->id !== auth()->id())
                                            <button type="button" onclick="impersonateUser({{ $user->id }})" class="inline-flex items-center px-2 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium rounded-md transition-colors duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Impersonate
                                            </button>
                                            
                                            @if(!$user->email_verified_at)
                                                <form method="POST" action="{{ route('admin.users.send-verification', $user) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-orange-100 hover:bg-orange-200 text-orange-700 text-xs font-medium rounded-md transition-colors duration-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                        Send Verification
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-teal-100 hover:bg-teal-200 text-teal-700 text-xs font-medium rounded-md transition-colors duration-200" onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                    </svg>
                                                    Reset Password
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition-colors duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                        </svg>
                                        <h3 class="text-sm font-medium text-gray-900 mb-1">No users found</h3>
                                        <p class="text-sm text-gray-500 mb-4">Get started by creating your first user.</p>
                                        <button onclick="window.location.href='{{ route('admin.users.create') }}'" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Add First User
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
</tbody>
                    </table>
                    <div class="flex items-center space-x-2 mt-4 p-4 bg-gray-50 border-t">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
                            <button type="button" onclick="submitBulkAction('activate')" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Activate
                            </button>
                            <button type="button" onclick="submitBulkAction('deactivate')" class="inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Deactivate
                            </button>
                            <button type="button" onclick="submitBulkAction('delete')" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                            <button type="button" onclick="showRoleModal()" class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                </svg>
                                Assign Role
                            </button>
                            <button type="button" onclick="submitBulkAction('export')" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pagination-mobile">
                            <div class="text-sm text-gray-700">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                            </div>
                            <div>
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>

    <!-- Role Assignment Modal -->
    <div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-mobile">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Role to Selected Users</h3>
                <div class="mb-4">
                    <label for="bulk-role" class="block text-sm font-medium text-gray-700 mb-2">Select Role:</label>
                    <select id="bulk-role" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($availableRoles as $role)
                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-center space-x-3">
                    <button onclick="submitRoleAssignment()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        Assign Role
                    </button>
                    <button onclick="closeRoleModal()" class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
            const selectAllCheckbox = document.getElementById('select-all');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function submitBulkAction(action) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.users.bulk-action') }}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_users[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        // Toggle advanced filters visibility
        function toggleFilters() {
            const filtersDiv = document.getElementById('advanced-filters');
            const toggleText = document.getElementById('filter-toggle-text');
            const toggleIcon = document.querySelector('button[onclick="toggleFilters()"] svg');
            
            if (filtersDiv.classList.contains('hidden')) {
                filtersDiv.classList.remove('hidden');
                toggleText.textContent = 'Hide Filters';
                toggleIcon.style.transform = 'rotate(180deg)';
            } else {
                filtersDiv.classList.add('hidden');
                toggleText.textContent = 'Show Filters';
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        }

        // Handle table sorting
        function sortTable(field) {
            const currentSortBy = '{{ request("sort_by") }}';
            const currentSortOrder = '{{ request("sort_order") }}';
            
            let newSortOrder = 'asc';
            
            // If clicking the same field, toggle order
            if (currentSortBy === field) {
                newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            }
            
            // Build URL with current parameters
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', field);
            url.searchParams.set('sort_order', newSortOrder);
            
            // Navigate to new URL
            window.location.href = url.toString();
        }

        // Auto-show filters if any filter is active
        document.addEventListener('DOMContentLoaded', function() {
            const hasActiveFilters = '{{ request()->hasAny(["role", "status", "date_from", "date_to"]) ? "true" : "false" }}' === 'true';
            
            if (hasActiveFilters) {
                toggleFilters();
            }

            // Add loading state to sort buttons
            const sortButtons = document.querySelectorAll('button[onclick^="sortTable"]');
            sortButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.opacity = '0.7';
                    this.style.pointerEvents = 'none';
                });
            });

            // Add loading state to pagination links
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function() {
                    this.style.opacity = '0.7';
                    this.style.pointerEvents = 'none';
                });
            });
        });

        // Add smooth transitions for filter toggle
        document.getElementById('advanced-filters').style.transition = 'all 0.3s ease';

        // Role modal functions
        function showRoleModal() {
            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one user.');
                return;
            }
            document.getElementById('roleModal').classList.remove('hidden');
        }

        function closeRoleModal() {
            document.getElementById('roleModal').classList.add('hidden');
        }

        function submitRoleAssignment() {
            const selectedRole = document.getElementById('bulk-role').value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.users.bulk-action') }}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'assign_role';
            form.appendChild(actionInput);

            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = selectedRole;
            form.appendChild(roleInput);

            const checkboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_users[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        // Close modal when clicking outside
        document.getElementById('roleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRoleModal();
            }
        });
        
        // Quick User Creation Modal Functions
        function openQuickCreateModal() {
            document.getElementById('quickCreateUserModal').classList.remove('hidden');
        }
        
        function closeQuickCreateModal() {
            document.getElementById('quickCreateUserModal').classList.add('hidden');
        }
        
        // User Impersonation Function
        function impersonateUser(userId) {
            if (confirm('Are you sure you want to impersonate this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('admin.users.impersonate', ':userId') }}`.replace(':userId', userId);
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Inline Editing Functions
        function enableInlineEditing(field, userId, currentValue) {
            const cell = event.target.closest('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            input.className = 'w-full px-2 py-1 border border-gray-300 rounded text-sm';
            
            input.addEventListener('blur', function() {
                saveInlineEdit(field, userId, input.value, cell);
            });
            
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    input.blur();
                }
            });
            
            cell.innerHTML = '';
            cell.appendChild(input);
            input.focus();
        }
        
        function saveInlineEdit(field, userId, newValue, cell) {
            fetch(`{{ route('admin.users.inline-update', ':userId') }}`.replace(':userId', userId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    field: field,
                    value: newValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cell.innerHTML = `<div class="text-sm text-gray-900 cursor-pointer hover:bg-gray-100 rounded px-2 py-1" onclick="enableInlineEditing('${field}', ${userId}, '${newValue}')">` + newValue + '</div>';
                } else {
                    alert('Error updating field: ' + (data.message || 'Unknown error'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating field');
                location.reload();
            });
        }
    </script>
    
    <!-- Quick User Creation Modal -->
    <div id="quickCreateUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-mobile">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick User Creation</h3>
                <form method="POST" action="{{ route('admin.users.store') }}" id="quickCreateForm">
                    @csrf
                    <div class="mb-4">
                        <input type="text" name="name" placeholder="First Name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <input type="text" name="surname" placeholder="Last Name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <input type="email" name="email" placeholder="Email Address" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <select name="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Role</option>
                            @foreach($availableRoles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <input type="password" name="password" placeholder="Password (auto-generated if empty)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <input type="password" name="password_confirmation" placeholder="Confirm Password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeQuickCreateModal()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
@endsection

@push('scripts')
<script>
    // Enhance select-all functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                toggleSelectAll();
                updateBulkActionsPanel();
            });
        }
        
        // Update user checkboxes to trigger bulk actions panel
        const userCheckboxes = document.querySelectorAll('input[name="selected_users[]"]');
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsPanel);
        });
        
        // Initialize filters if active
        if ('{{ request()->hasAny(["role", "status", "date_from", "date_to"]) ? "true" : "false" }}' === 'true') {
            toggleFilters();
        }
    });
    
    // Update bulk actions panel
    function updateBulkActionsPanel() {
        const selectedCheckboxes = document.querySelectorAll('input[name="selected_users[]"]:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedCheckboxes.length > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkActions.classList.add('hidden');
        }
    }
    
    // Handle user selection and bulk actions
    function clearBulkSelection() {
        const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
        const selectAllCheckbox = document.getElementById('select-all');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        updateBulkActionsPanel();
    }
    
    // Export users function
    function exportUsers() {
        window.location.href = '{{ route("admin.users.export") }}';
    }
</script>
@endpush
