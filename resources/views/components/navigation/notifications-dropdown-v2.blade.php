<div class="py-1">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
            <button class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                Mark all as read
            </button>
        </div>
    </div>

    <!-- Notification Items -->
    <div class="max-h-96 overflow-y-auto">
        <!-- Price Alert Notification -->
        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Price drop alert
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        Lakers vs Warriors tickets dropped to $125
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        2 minutes ago
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- New Ticket Notification -->
        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        New tickets available
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        NBA Finals Game 7 tickets now available
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        15 minutes ago
                    </p>
                </div>
            </div>
        </div>

        <!-- Purchase Success Notification -->
        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Purchase successful
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        Your order for Chiefs vs Bills tickets has been confirmed
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        1 hour ago
                    </p>
                </div>
            </div>
        </div>

        <!-- System Maintenance Notification -->
        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Scheduled maintenance
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        System will be unavailable tonight from 2-4 AM EST
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        2 hours ago
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('notifications.index') }}" 
           class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
            View all notifications →
        </a>
    </div>
</div>