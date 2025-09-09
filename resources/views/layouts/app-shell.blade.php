<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#7C3AED">

    <link rel="manifest" href="/manifest-enhanced.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">

    <title>{{ $title ?? 'HD Tickets' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="appShell()" class="h-full bg-gray-50 text-gray-900">
    <!-- App splash / skeleton loader -->
    <div x-show="showSplash" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-white">
        <div class="flex flex-col items-center space-y-6">
            <img src="/images/brand/logo-mark.svg" alt="HD Tickets" class="h-16 w-16 animate-pulse" />
            <div class="w-56 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full animate-[loading_1.4s_ease_infinite]" style="width: 45%"></div>
            </div>
            <p class="text-sm text-gray-500">Preparing your experience…</p>
        </div>
    </div>

    <!-- Top app bar -->
    <header class="sticky top-0 z-40 backdrop-blur supports-[backdrop-filter]:bg-white/80 bg-white border-b border-gray-200" :class="{ 'pt-safe': isIOS }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="h-14 flex items-center justify-between">
                <button @click="toggleNav()" class="p-2 -m-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="/dashboard" class="flex items-center space-x-2">
                    <img src="/images/brand/logo-mark.svg" alt="HD Tickets" class="h-6 w-6">
                    <span class="font-semibold">HD Tickets</span>
                </a>
                <div class="flex items-center space-x-2">
                    <a href="/notifications" class="relative p-2 -m-2 rounded-lg hover:bg-gray-100">
                        <span x-show="hasUnread" class="absolute right-1 top-1 inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </a>
                    <div class="relative">
                        <button @click="userOpen = !userOpen" class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-100">
                            <img src="{{ Auth::user()->avatar_url ?? '/images/brand/avatar-default.png' }}" class="h-8 w-8 rounded-full" alt="avatar">
                            <span class="hidden sm:block text-sm">{{ Auth::user()->name ?? 'Account' }}</span>
                        </button>
                        <div x-show="userOpen" @click.away="userOpen=false" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg p-2">
                            <a href="/profile" class="block px-3 py-2 rounded-md hover:bg-gray-50">Profile</a>
                            <a href="/settings" class="block px-3 py-2 rounded-md hover:bg-gray-50">Settings</a>
                            <a href="/subscriptions" class="block px-3 py-2 rounded-md hover:bg-gray-50">Billing</a>
                            <form method="POST" action="/logout" class="mt-1">@csrf
                                <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Offline banner -->
    <div x-show="!online" x-transition class="sticky top-14 z-40 bg-amber-100 border-y border-amber-200 text-amber-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-2 text-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M12 2.25a.75.75 0 01.671.415l9 18a.75.75 0 01-1.342.67L17.94 15H6.06l-2.39 6.335a.75.75 0 01-1.342-.67l9-18A.75.75 0 0112 2.25zM7.108 13.5h9.784L12 4.964 7.108 13.5z" clip-rule="evenodd" />
                </svg>
                <span>You're offline. Some features are limited. Changes will sync when back online.</span>
            </div>
            <button @click="retrySync()" class="text-indigo-700 hover:underline">Retry sync</button>
        </div>
    </div>

    <!-- Side navigation drawer -->
    <div x-show="navOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/30" @click="toggleNav()"></div>
    <aside x-show="navOpen" x-transition:enter="transition transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-80 bg-white border-r border-gray-200">
        <div class="h-14 border-b border-gray-200 flex items-center px-4">
            <span class="font-semibold">Navigation</span>
        </div>
        <nav class="p-3 space-y-1">
            <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">
                <span class="i-heroicons-home"></span>
                <span>Dashboard</span>
            </a>
            <a href="/tickets" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Tickets</a>
            <a href="/watchlist" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Watchlist</a>
            <a href="/alerts" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Alerts</a>
            <a href="/analytics" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Analytics</a>
            <a href="/recommendations" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Recommendations</a>
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isAgent()))
                <div class="pt-2 mt-2 border-t border-gray-200 text-xs uppercase text-gray-500 px-3">Pro Tools</div>
                <a href="/monitoring" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Monitoring</a>
                <a href="/purchase-decisions" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Purchase Decisions</a>
            @endif
            @if(auth()->user() && auth()->user()->isAdmin())
                <div class="pt-2 mt-2 border-t border-gray-200 text-xs uppercase text-gray-500 px-3">Admin</div>
                <a href="/admin/users" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Users</a>
                <a href="/admin/settings" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">Settings</a>
            @endif
        </nav>
    </aside>

    <!-- Content area -->
    <main class="min-h-[calc(100vh-3.5rem)]">
        {{ $slot }}
    </main>

    <!-- PWA Install Badge -->
    <x-install-badge />

    <!-- Bottom navigation (mobile) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 border-t border-gray-200 bg-white md:hidden" :class="{ 'pb-safe': isIOS }">
        <div class="grid grid-cols-5 text-xs">
            <a href="/dashboard" class="flex flex-col items-center py-2 {{ request()->is('dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Home</span>
            </a>
            <a href="/tickets" class="flex flex-col items-center py-2 {{ request()->is('tickets*') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path d="M3.375 6a.375.375 0 00-.375.375v3.026a2.249 2.249 0 010 4.198v3.026c0 .207.168.375.375.375h17.25a.375.375 0 00.375-.375v-3.026a2.249 2.249 0 010-4.198V6.375A.375.375 0 0020.625 6H3.375z" />
                </svg>
                <span>Tickets</span>
            </a>
            <a href="/watchlist" class="flex flex-col items-center py-2 {{ request()->is('watchlist') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0111.36 0c.973.118 1.79.78 2.082 1.707l.259.813c.147.46.54.82 1.017.931.352.082.695.185 1.028.31.94.353 1.497 1.37 1.29 2.35-.625 2.969-2.468 5.173-5.585 6.813-2.2 1.17-4.781 1.49-7.771 1.49-2.99 0-5.571-.32-7.771-1.49C.963 13.86-.88 11.656-1.505 8.687c-.206-.98.35-1.997 1.29-2.35.333-.125.676-.228 1.028-.31.477-.111.87-.47 1.017-.931l.26-.813c.291-.927 1.108-1.589 2.08-1.707z" clip-rule="evenodd" />
                    <path d="M9 14.25c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-4.5a.75.75 0 00-.75.75z" />
                </svg>
                <span>Watch</span>
            </a>
            <a href="/alerts" class="flex flex-col items-center py-2 {{ request()->is('alerts') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path fill-rule="evenodd" d="M5.25 2.25A.75.75 0 016 3v2.25h12V3a.75.75 0 011.5 0v2.25a3 3 0 01-3 3H6a3 3 0 01-3-3V3a.75.75 0 01.75-.75zM3.75 21a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5H3.75z" clip-rule="evenodd" />
                </svg>
                <span>Alerts</span>
            </a>
            <a href="/analytics" class="flex flex-col items-center py-2 {{ request()->is('analytics') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path fill-rule="evenodd" d="M3 3.75A.75.75 0 013.75 3h16.5a.75.75 0 01.75.75v16.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 20.25V3.75zm3.75 3a.75.75 0 00-.75.75v9.75c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V7.5a.75.75 0 00-.75-.75h-1.5zM10.5 9a.75.75 0 00-.75.75v7.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-7.5A.75.75 0 0012 9h-1.5zM15.75 12.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75h-1.5z" clip-rule="evenodd" />
                </svg>
                <span>Analytics</span>
            </a>
        </div>
    </nav>

    <script>
        document.documentElement.classList.add('js');
        function appShell() {
            return {
                showSplash: true,
                navOpen: false,
                userOpen: false,
                online: navigator.onLine,
                hasUnread: false,
                isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
                init() {
                    // Hide splash when main CSS and JS are ready
                    window.addEventListener('load', () => {
                        setTimeout(() => this.showSplash = false, 250);
                    });

                    window.addEventListener('online', () => this.online = true);
                    window.addEventListener('offline', () => this.online = false);

                    // check notifications unread count (if available globally)
                    document.addEventListener('notifications:unread', (e) => {
                        this.hasUnread = (e.detail?.count ?? 0) > 0;
                    });
                },
                toggleNav() { this.navOpen = !this.navOpen },
                retrySync() { document.dispatchEvent(new CustomEvent('offline:retry-sync')) }
            }
        }
    </script>
</body>
</html>

