<!-- Role-based navigation for modern sidebar -->
@php
    $user = Auth::user();
    $userRole = $user ? $user->role : 'customer';
    $currentRoute = request()->route()->getName();
@endphp

<div class="space-y-1">
    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}" 
       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
              {{ str_contains($currentRoute, 'dashboard') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
        <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
        </svg>
        <span x-show="!sidebarCollapsed">Dashboard</span>
    </a>

    <!-- Sports Tickets -->
    <a href="{{ route('tickets.scraping.index') }}" 
       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
              {{ str_contains($currentRoute, 'tickets') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
        <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <span x-show="!sidebarCollapsed">Sports Tickets</span>
    </a>

    @if(in_array($userRole, ['admin', 'agent']))
        <!-- Price Alerts -->
        <a href="{{ route('tickets.alerts.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'alerts') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Price Alerts</span>
        </a>

        <!-- Purchase Decisions -->
        <a href="{{ route('purchase-decisions.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'purchase-decisions') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Purchase Queue</span>
        </a>

        <!-- Sources -->
        <a href="{{ route('sources.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'sources') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Sources</span>
        </a>
    @endif

    @if($userRole === 'customer')
        <!-- My Alerts -->
        <a href="{{ route('customer.alerts.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'alerts') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">My Alerts</span>
        </a>

        <!-- Purchase History -->
        <a href="{{ route('customer.purchases.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'purchases') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Purchase History</span>
        </a>

        <!-- Subscription -->
        <a href="{{ route('customer.subscription.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'subscription') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Subscription</span>
        </a>
    @endif

    @if($userRole === 'admin')
        <!-- Section Separator -->
        <div class="border-t border-gray-200 dark:border-gray-700 my-4" x-show="!sidebarCollapsed"></div>
        
        <!-- Admin Section -->
        <div x-show="!sidebarCollapsed" class="px-2 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administration</h3>
        </div>

        <!-- Users Management -->
        <a href="{{ route('admin.users.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'admin.users') ? 'bg-purple-100 text-purple-900 dark:bg-purple-900/50 dark:text-purple-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Users</span>
        </a>

        <!-- Analytics -->
        <a href="{{ route('admin.analytics.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'analytics') ? 'bg-purple-100 text-purple-900 dark:bg-purple-900/50 dark:text-purple-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Analytics</span>
        </a>

        <!-- System Settings -->
        <a href="{{ route('admin.settings.index') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
                  {{ str_contains($currentRoute, 'settings') ? 'bg-purple-100 text-purple-900 dark:bg-purple-900/50 dark:text-purple-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-show="!sidebarCollapsed">Settings</span>
        </a>
    @endif

    <!-- Section Separator -->
    <div class="border-t border-gray-200 dark:border-gray-700 my-4" x-show="!sidebarCollapsed"></div>

    <!-- Account Settings -->
    <a href="{{ route('profile.edit') }}" 
       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
              {{ str_contains($currentRoute, 'profile') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
        <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span x-show="!sidebarCollapsed">Profile</span>
    </a>

    <!-- Help & Support -->
    <a href="{{ route('support.index') }}" 
       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200
              {{ str_contains($currentRoute, 'support') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
        <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span x-show="!sidebarCollapsed">Help & Support</span>
    </a>
</div>