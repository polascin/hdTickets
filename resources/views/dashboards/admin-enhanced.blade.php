<x-unified-layout title="Admin Dashboard" subtitle="Sports Events Tickets Platform Administration">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- System Health Indicator -->
      @php
        $systemHealth = 98; // This should come from system monitoring
        $healthColor = $systemHealth >= 95 ? 'green' : ($systemHealth >= 85 ? 'yellow' : 'red');
      @endphp
      <x-ui.badge variant="success" dot="true">System {{ $systemHealth }}%</x-ui.badge>

      <!-- Quick Actions -->
      <div class="hidden md:flex items-center space-x-2">
        <x-ui.button href="{{ route('admin.users.create') }}" variant="ghost" size="sm" title="Add New User">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          User
        </x-ui.button>
        <x-ui.button href="{{ route('admin.system.maintenance') }}" variant="ghost" size="sm" title="System Maintenance">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </x-ui.button>
      </div>

      <!-- Real-time Alerts -->
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
          </svg>
          <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
            {{ $systemAlerts ?? 3 }}
          </span>
        </button>

        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border z-50">
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-3">System Alerts</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
              <div class="flex items-start space-x-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-3 h-3 bg-red-500 rounded-full mt-1"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">High Memory Usage</p>
                  <p class="text-xs text-gray-600">Server memory usage at 87%</p>
                  <p class="text-xs text-gray-500">2 minutes ago</p>
                </div>
              </div>
              <div class="flex items-start space-x-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Queue Backlog</p>
                  <p class="text-xs text-gray-600">245 jobs pending in scraping queue</p>
                  <p class="text-xs text-gray-500">5 minutes ago</p>
                </div>
              </div>
              <div class="flex items-start space-x-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-3 h-3 bg-blue-500 rounded-full mt-1"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">New User Registration</p>
                  <p class="text-xs text-gray-600">12 new users registered in last hour</p>
                  <p class="text-xs text-gray-500">15 minutes ago</p>
                </div>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t">
              <a href="{{ route('admin.system.alerts') }}" class="text-sm text-blue-600 hover:text-blue-700">View all system alerts →</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Refresh Dashboard -->
      <x-ui.button variant="ghost" size="sm" onclick="refreshAdminDashboard()" title="Refresh Dashboard">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
      </x-ui.button>
    </div>
  </x-slot>

  <div x-data="adminDashboard()" x-init="init()">
    @php
      // Admin Dashboard Data
      $totalUsers = \App\Models\User::count();
      $activeUsers = \App\Models\User::where('last_activity', '>=', now()->subHours(24))->count();
      $totalTickets = \App\Models\ScrapedTicket::count();
      $activeSubs = \App\Models\UserSubscription::active()->count();
      $monthlyRevenue = \App\Models\UserSubscription::monthlyRevenue();
      $totalPurchases = \App\Models\TicketPurchase::successful()->count();
      $scraperJobs = \App\Models\ScrapingJob::where('status', 'running')->count() ?? 0;
    @endphp

    <!-- Admin Performance Banner -->
    <x-ui.card class="mb-6" variant="flat">
      <x-ui.card-content class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 text-white rounded-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <defs>
              <pattern id="admin-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
              </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#admin-grid)"/>
          </svg>
        </div>
        
        <div class="relative z-10 p-6">
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="hd-heading-2 !text-white !mb-1">Welcome back, {{ Auth::user()->name }}!</h2>
                  <p class="text-white/90 hd-text-base">HD Tickets Administrative Control Center</p>
                </div>
              </div>
              
              <!-- Platform Overview -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Total Users</p>
                  <p class="text-white font-bold text-xl">{{ number_format($totalUsers) }}</p>
                  <p class="text-white/70 text-xs">{{ number_format($activeUsers) }} active today</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Active Subscriptions</p>
                  <p class="text-white font-bold text-xl">{{ number_format($activeSubs) }}</p>
                  <p class="text-white/70 text-xs">{{ number_format(($activeSubs / max($totalUsers, 1)) * 100, 1) }}% conversion</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Monthly Revenue</p>
                  <p class="text-white font-bold text-xl">${{ number_format($monthlyRevenue) }}</p>
                  <p class="text-white/70 text-xs">This month</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Ticket Purchases</p>
                  <p class="text-white font-bold text-xl">{{ number_format($totalPurchases) }}</p>
                  <p class="text-white/70 text-xs">All time</p>
                </div>
              </div>
            </div>
            
            <!-- System Status Overview -->
            <div class="hidden lg:block">
              <div class="bg-white/20 rounded-lg p-4 min-w-[200px]">
                <p class="text-white/80 text-sm mb-3">System Status</p>
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-white text-sm">Platform</span>
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                      <span class="text-white text-sm">Online</span>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-white text-sm">Scrapers</span>
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                      <span class="text-white text-sm">{{ $scraperJobs }} active</span>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-white text-sm">Health</span>
                    <span class="text-white text-sm">{{ $systemHealth }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Admin Stats Cards -->
    <div class="hd-grid hd-grid-1 hd-md-grid-2 hd-lg-grid-4 mb-8">
      <!-- User Management -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white cursor-pointer" onclick="window.location.href='{{ route('admin.users.index') }}'">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 hd-text-small font-medium">User Management</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ number_format($totalUsers) }}</p>
              <div class="mt-1">
                <x-ui.badge variant="info" size="xs">{{ number_format($activeUsers) }} active</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Revenue & Subscriptions -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-green-500 to-green-600 text-white cursor-pointer" onclick="window.location.href='{{ route('admin.subscriptions.index') }}'">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 hd-text-small font-medium">Monthly Revenue</p>
              <p class="hd-heading-2 !text-white !mb-0">${{ number_format($monthlyRevenue) }}</p>
              <div class="mt-1">
                <x-ui.badge variant="success" size="xs">{{ number_format($activeSubs) }} active subs</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Ticket Management -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white cursor-pointer" onclick="window.location.href='{{ route('admin.tickets.index') }}'">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100 hd-text-small font-medium">Sports Tickets</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ number_format($totalTickets) }}</p>
              <div class="mt-1">
                <x-ui.badge variant="warning" size="xs">{{ $scraperJobs }} scrapers active</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- System Health -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white cursor-pointer" onclick="window.location.href='{{ route('admin.system.index') }}'">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 hd-text-small font-medium">System Health</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ $systemHealth }}%</p>
              <div class="mt-1">
                @if($systemHealth >= 95)
                  <x-ui.badge variant="success" size="xs" dot="true">Excellent</x-ui.badge>
                @elseif($systemHealth >= 85)
                  <x-ui.badge variant="warning" size="xs" dot="true">Good</x-ui.badge>
                @else
                  <x-ui.badge variant="error" size="xs" dot="true">Attention needed</x-ui.badge>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Main Admin Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Main Admin Tools -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Quick Admin Actions -->
        <x-ui.card>
          <x-ui.card-header title="Administrative Tools">
            <x-ui.button href="{{ route('admin.tools.index') }}" variant="outline" size="sm">All Tools</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <a href="{{ route('admin.users.index') }}" class="group p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">User Management</h4>
                    <p class="text-sm text-gray-600">Manage users, roles & permissions</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('admin.reports.index') }}" class="group p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Analytics & Reports</h4>
                    <p class="text-sm text-gray-600">Revenue, usage & performance data</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('admin.scraping.index') }}" class="group p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Scraping Control</h4>
                    <p class="text-sm text-gray-600">Monitor & control ticket scrapers</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('admin.system.index') }}" class="group p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">System Configuration</h4>
                    <p class="text-sm text-gray-600">Settings, maintenance & monitoring</p>
                  </div>
                </div>
              </a>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Recent Admin Activity -->
        <x-ui.card>
          <x-ui.card-header title="Recent Administrative Activity">
            <x-ui.button href="{{ route('admin.activity-logs.index') }}" variant="ghost" size="sm">View All</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              @forelse($recentActivity ?? [] as $activity)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ $activity['action'] ?? 'User Role Updated' }}</h4>
                      <p class="text-sm text-gray-600">{{ $activity['description'] ?? 'Updated user permissions for agent role' }}</p>
                      <p class="text-xs text-gray-500">{{ $activity['user'] ?? 'Admin' }} • {{ $activity['time'] ?? '5 minutes ago' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">{{ $activity['status'] ?? 'Completed' }}</span>
                  </div>
                </div>
              @empty
                <div class="text-center py-8 text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                  <p>No recent administrative activity</p>
                </div>
              @endforelse
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>

      <!-- Admin Sidebar -->
      <div class="space-y-6">
        <!-- System Status -->
        <x-ui.card>
          <x-ui.card-header title="System Status">
            <x-ui.button href="{{ route('admin.system.monitoring') }}" variant="ghost" size="sm">Monitor</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <!-- System Health Chart -->
              <div class="text-center">
                <div class="relative w-24 h-24 mx-auto mb-3">
                  <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="text-green-500" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ $systemHealth }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                  </svg>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold text-gray-900">{{ $systemHealth }}%</span>
                  </div>
                </div>
                <p class="text-sm font-medium text-gray-900">Overall Health</p>
                <p class="text-xs text-gray-500">All systems operational</p>
              </div>

              <!-- Service Status -->
              <div class="space-y-2 pt-4 border-t">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Database</span>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-xs text-green-600">Online</span>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Queue System</span>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-xs text-green-600">Active</span>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Scrapers</span>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                    <span class="text-xs text-orange-600">{{ $scraperJobs }} running</span>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">API</span>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-xs text-green-600">Responding</span>
                  </div>
                </div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Quick Stats -->
        <x-ui.card>
          <x-ui.card-header title="Platform Overview"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <!-- Key Metrics -->
              <div class="grid grid-cols-2 gap-3">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                  <div class="text-lg font-bold text-blue-600">{{ number_format($totalUsers) }}</div>
                  <div class="text-xs text-gray-500">Total Users</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                  <div class="text-lg font-bold text-green-600">{{ number_format($activeSubs) }}</div>
                  <div class="text-xs text-gray-500">Active Subs</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                  <div class="text-lg font-bold text-purple-600">{{ number_format($totalTickets) }}</div>
                  <div class="text-xs text-gray-500">Tickets</div>
                </div>
                <div class="text-center p-3 bg-orange-50 rounded-lg">
                  <div class="text-lg font-bold text-orange-600">{{ number_format($totalPurchases) }}</div>
                  <div class="text-xs text-gray-500">Purchases</div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="pt-4 border-t">
                <div class="space-y-2">
                  <a href="{{ route('admin.backup.create') }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition text-center block">
                    Create Backup
                  </a>
                  <a href="{{ route('admin.maintenance.mode') }}" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-700 transition text-center block">
                    Maintenance Mode
                  </a>
                  <a href="{{ route('admin.settings') }}" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition text-center block">
                    System Settings
                  </a>
                </div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function adminDashboard() {
        return {
          stats: {
            total_users: {{ $totalUsers }},
            active_users: {{ $activeUsers }},
            active_subscriptions: {{ $activeSubs }},
            monthly_revenue: {{ $monthlyRevenue }},
            system_health: {{ $systemHealth }}
          },
          systemAlerts: [],
          refreshInterval: null,

          init() {
            this.loadDashboardData();
            this.setupRealTimeUpdates();
            this.setupPeriodicRefresh();
          },

          loadDashboardData() {
            fetch('/api/admin/dashboard-stats')
              .then(response => response.json())
              .then(data => {
                this.stats = { ...this.stats, ...data };
                this.systemAlerts = data.alerts || [];
              })
              .catch(error => {
                console.error('Failed to load dashboard data:', error);
              });
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.private('admin.dashboard')
                .listen('SystemAlertTriggered', (e) => {
                  this.systemAlerts.unshift(e.alert);
                  this.showNotification('System Alert', e.alert.message, 'warning');
                })
                .listen('UserRegistered', (e) => {
                  this.stats.total_users++;
                  this.showNotification('New User', `${e.user.name} registered`, 'success');
                })
                .listen('SubscriptionCreated', (e) => {
                  this.stats.active_subscriptions++;
                  this.showNotification('New Subscription', `Revenue increased by $${e.amount}`, 'success');
                })
                .listen('SystemHealthUpdated', (e) => {
                  this.stats.system_health = e.health;
                  if (e.health < 85) {
                    this.showNotification('System Health Alert', `Health dropped to ${e.health}%`, 'error');
                  }
                });
            }
          },

          setupPeriodicRefresh() {
            // Refresh dashboard data every 5 minutes
            this.refreshInterval = setInterval(() => {
              this.loadDashboardData();
            }, 300000);
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          },

          destroy() {
            if (this.refreshInterval) {
              clearInterval(this.refreshInterval);
            }
          }
        };
      }

      function refreshAdminDashboard() {
        // Add visual feedback
        const button = event.target.closest('button');
        const icon = button.querySelector('svg');
        
        icon.classList.add('animate-spin');
        
        // Simulate refresh
        setTimeout(() => {
          location.reload();
        }, 1000);
      }

      // Auto-refresh page every 10 minutes
      setTimeout(() => {
        location.reload();
      }, 600000);
    </script>
  @endpush
</x-unified-layout>
