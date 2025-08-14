{{--
    Footer Partial - Unified Layout System
    Responsive footer with contextual information
--}}
<div class="footer-content text-center">
    <div class="flex flex-col lg:flex-row items-center justify-between space-y-2 lg:space-y-0">
        {{-- Left Section: Copyright --}}
        <div class="text-sm text-gray-600 dark:text-gray-400">
            © {{ date('Y') }} HD Tickets. All rights reserved.
        </div>

        {{-- Center Section: Links (Desktop) --}}
        <div class="hidden lg:flex items-center space-x-6">
            <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                Privacy Policy
            </a>
            <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                Terms of Service
            </a>
            <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                Support
            </a>
            <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                Contact
            </a>
        </div>

        {{-- Right Section: Status and Version --}}
        <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
            {{-- System Status Indicator --}}
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span>All Systems Operational</span>
            </div>
            
            {{-- Version --}}
            <span class="text-xs">v2.1.0</span>
        </div>
    </div>

    {{-- Mobile Links --}}
    <div class="lg:hidden mt-4 flex flex-wrap items-center justify-center space-x-4">
        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
            Privacy
        </a>
        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
            Terms
        </a>
        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
            Support
        </a>
        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
            Contact
        </a>
    </div>
</div>
