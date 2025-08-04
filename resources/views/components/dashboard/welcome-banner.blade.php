@props([
    'user' => null,
    'stats' => []
])

{{-- Dashboard Welcome Banner Component --}}
<div class="dashboard-card mb-6 hero-gradient text-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-full h-full">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)"/>
            </svg>
        </div>
    </div>
    
    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold mb-1">Welcome back, {{ $user ? $user->name : 'User' }}!</h2>
                    <p class="text-white/90 text-sm sm:text-base">Here's what's happening with your ticket monitoring today.</p>
                </div>
            </div>
            <div class="flex items-center space-x-4 text-sm text-white/80">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                    </svg>
                    {{ now()->format('l, F j, Y') }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="currentTime">{{ now()->format('H:i:s') }}</span>
                </div>
                @if(isset($stats['last_login']))
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Last login: {{ $stats['last_login']->diffForHumans() }}
                </div>
                @endif
            </div>
        </div>
        
        <!-- User Stats Summary -->
        <div class="flex items-center space-x-6 text-white/90">
            @if(isset($stats['active_monitors']))
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $stats['active_monitors'] ?? 0 }}</div>
                <div class="text-xs uppercase tracking-wide">Active Monitors</div>
            </div>
            @endif
            
            @if(isset($stats['alerts_today']))
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $stats['alerts_today'] ?? 0 }}</div>
                <div class="text-xs uppercase tracking-wide">Alerts Today</div>
            </div>
            @endif
            
            @if(isset($stats['price_drops']))
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $stats['price_drops'] ?? 0 }}</div>
                <div class="text-xs uppercase tracking-wide">Price Drops</div>
            </div>
            @endif
        </div>
        
        <div class="animate-float hidden sm:block">
            <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
        </div>
    </div>
</div>

