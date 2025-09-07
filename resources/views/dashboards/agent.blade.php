<x-unified-layout title="Agent Dashboard" subtitle="Professional Sports Ticket Monitoring & Selection">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Agent Performance Indicator -->
      <x-ui.badge variant="info" dot="true">Agent Pro</x-ui.badge>
      
      <!-- Quick Stats -->
      <div class="hidden md:flex items-center space-x-4 bg-white/10 rounded-lg px-4 py-2">
        <div class="text-center">
          <div class="text-sm font-semibold text-gray-700">{{ Auth::user()->agentStats()->monthly_selections ?? 0 }}</div>
          <div class="text-xs text-gray-500">Selections</div>
        </div>
        <div class="text-center">
          <div class="text-sm font-semibold text-green-600">${{ number_format(Auth::user()->agentStats()->monthly_savings ?? 0) }}</div>
          <div class="text-xs text-gray-500">Saved</div>
        </div>
      </div>

      <!-- Auto-Purchase Toggle -->
      <div class="flex items-center space-x-2" x-data="{ autoPurchase: {{ Auth::user()->agent_auto_purchase ?? false ? 'true' : 'false' }} }">
        <label class="text-sm font-medium text-gray-700">Auto-Purchase</label>
        <button @click="autoPurchase = !autoPurchase; toggleAutoPurchase(autoPurchase)" 
                :class="autoPurchase ? 'bg-green-600' : 'bg-gray-200'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
          <span :class="autoPurchase ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
        </button>
      </div>

      <!-- Notifications -->
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
          </svg>
          <span class="absolute -top-1 -right-1 h-4 w-4 bg-orange-500 text-white text-xs rounded-full flex items-center justify-center">
            {{ Auth::user()->agentAlerts()->unread()->count() }}
          </span>
        </button>

        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border z-50">
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Agent Alerts & Opportunities</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
              @forelse(Auth::user()->agentAlerts()->recent()->take(5)->get() as $alert)
                <div class="flex items-start space-x-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                  <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-orange-500 rounded-full mt-1"></div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $alert->title }}</p>
                    <p class="text-xs text-gray-600">{{ $alert->description ?? 'New opportunity detected' }}</p>
                    <p class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</p>
                  </div>
                </div>
              @empty
                <p class="text-sm text-gray-500 text-center py-4">No recent alerts</p>
              @endforelse
            </div>
            <div class="mt-3 pt-3 border-t">
              <a href="{{ route('agent.alerts.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View all agent alerts →</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </x-slot>

  <div x-data="agentDashboard()" x-init="init()">
    <!-- Agent Performance Banner -->
    <x-ui.card class="mb-6" variant="flat">
      <x-ui.card-content class="bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 text-white rounded-lg relative overflow-hidden">
        <div class="relative z-10 p-6">
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="hd-heading-2 !text-white !mb-1">Agent Dashboard - {{ Auth::user()->name }}</h2>
                  <p class="text-white/90 hd-text-base">Professional Sports Ticket Selection & Monitoring</p>
                </div>
              </div>
              
              <!-- Performance Summary -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">This Month</p>
                  <p class="text-white font-bold text-xl">{{ Auth::user()->agentStats()->monthly_selections ?? 0 }}</p>
                  <p class="text-white/70 text-xs">Tickets Selected</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Success Rate</p>
                  <p class="text-white font-bold text-xl">{{ Auth::user()->agentStats()->success_rate ?? 95 }}%</p>
                  <p class="text-white/70 text-xs">Purchase Success</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Total Saved</p>
                  <p class="text-white font-bold text-xl">${{ number_format(Auth::user()->agentStats()->total_savings ?? 0) }}</p>
                  <p class="text-white/70 text-xs">Client Savings</p>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                  <p class="text-white/80 text-sm">Queue Status</p>
                  <p class="text-white font-bold text-xl">{{ Auth::user()->purchaseQueue()->pending()->count() }}</p>
                  <p class="text-white/70 text-xs">Pending Purchases</p>
                </div>
              </div>
            </div>
            
            <!-- Agent Tools Quick Access -->
            <div class="hidden lg:block">
              <div class="bg-white/20 rounded-lg p-4 min-w-[200px]">
                <p class="text-white/80 text-sm mb-3">Quick Tools</p>
                <div class="space-y-2">
                  <a href="{{ route('agent.bulk-select') }}" class="block bg-white/20 hover:bg-white/30 px-3 py-2 rounded text-white text-sm transition">
                    Bulk Selection
                  </a>
                  <a href="{{ route('agent.price-analyzer') }}" class="block bg-white/20 hover:bg-white/30 px-3 py-2 rounded text-white text-sm transition">
                    Price Analyzer
                  </a>
                  <a href="{{ route('agent.reports') }}" class="block bg-white/20 hover:bg-white/30 px-3 py-2 rounded text-white text-sm transition">
                    Performance Reports
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Agent Stats Cards -->
    <div class="hd-grid hd-grid-1 hd-md-grid-2 hd-lg-grid-4 mb-8">
      <!-- Active Monitors -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 hd-text-small font-medium">Active Monitors</p>
              <p class="hd-heading-2 !text-white !mb-0" x-text="stats.active_monitors || '{{ Auth::user()->ticketMonitors()->active()->count() }}'">
                {{ Auth::user()->ticketMonitors()->active()->count() }}
              </p>
              <div class="mt-1">
                <x-ui.badge variant="success" size="xs" dot="true">All operational</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Purchase Queue -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100 hd-text-small font-medium">Purchase Queue</p>
              <p class="hd-heading-2 !text-white !mb-0" x-text="stats.queue_items || '{{ Auth::user()->purchaseQueue()->pending()->count() }}'">
                {{ Auth::user()->purchaseQueue()->pending()->count() }}
              </p>
              <div class="mt-1">
                @if(Auth::user()->purchaseQueue()->pending()->count() > 0)
                  <x-ui.badge variant="warning" size="xs" dot="true">{{ Auth::user()->purchaseQueue()->pending()->count() }} pending</x-ui.badge>
                @else
                  <span class="hd-text-small text-white/70">Queue clear</span>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Price Alerts -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-green-500 to-green-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 hd-text-small font-medium">Price Alerts</p>
              <p class="hd-heading-2 !text-white !mb-0" x-text="stats.price_alerts || '{{ Auth::user()->priceAlerts()->active()->count() }}'">
                {{ Auth::user()->priceAlerts()->active()->count() }}
              </p>
              <div class="mt-1">
                @if(Auth::user()->priceAlerts()->triggered()->today()->count() > 0)
                  <x-ui.badge variant="info" size="xs" dot="true">{{ Auth::user()->priceAlerts()->triggered()->today()->count() }} triggered</x-ui.badge>
                @else
                  <span class="hd-text-small text-white/70">No triggers today</span>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Success Rate -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 hd-text-small font-medium">Success Rate</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ Auth::user()->agentStats()->success_rate ?? 95 }}%</p>
              <div class="mt-1">
                <span class="hd-text-small text-white/70">Last 30 days</span>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Agent Tools & Actions -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Professional Tools -->
        <x-ui.card>
          <x-ui.card-header title="Professional Tools">
            <x-ui.button href="{{ route('agent.tools.index') }}" variant="outline" size="sm">All Tools</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <a href="{{ route('agent.bulk-select') }}" class="group p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Bulk Selection</h4>
                    <p class="text-sm text-gray-600">Select multiple tickets efficiently</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('agent.price-analyzer') }}" class="group p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Price Analyzer</h4>
                    <p class="text-sm text-gray-600">Advanced price trend analysis</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('agent.auto-purchase') }}" class="group p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Auto Purchase</h4>
                    <p class="text-sm text-gray-600">Configure automated buying</p>
                  </div>
                </div>
              </a>

              <a href="{{ route('agent.market-insights') }}" class="group p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">Market Insights</h4>
                    <p class="text-sm text-gray-600">Real-time market analysis</p>
                  </div>
                </div>
              </a>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Recent Opportunities -->
        <x-ui.card>
          <x-ui.card-header title="High-Value Opportunities">
            <x-ui.button href="{{ route('agent.opportunities') }}" variant="ghost" size="sm">View All</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              @forelse($opportunities ?? [] as $opportunity)
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-yellow-50 border border-green-200 rounded-lg hover:from-green-100 hover:to-yellow-100 transition-colors">
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-yellow-500 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ $opportunity['title'] ?? 'Lakers vs Warriors' }}</h4>
                      <p class="text-sm text-gray-600">{{ $opportunity['details'] ?? 'Below market price - Premium seats' }}</p>
                      <p class="text-xs text-gray-500">{{ $opportunity['venue'] ?? 'Staples Center' }} • {{ $opportunity['date'] ?? 'Tonight' }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="flex items-center space-x-2">
                      <div class="text-right">
                        <p class="font-semibold text-green-600">${{ $opportunity['price'] ?? '285' }}</p>
                        <p class="text-xs text-gray-500 line-through">${{ $opportunity['original_price'] ?? '350' }}</p>
                      </div>
                      <div class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                        {{ $opportunity['savings'] ?? '18' }}% OFF
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-8 text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                  <p>No high-value opportunities at the moment</p>
                  <a href="{{ route('tickets.scraping.index') }}" class="text-blue-600 hover:text-blue-700 text-sm">Browse all tickets →</a>
                </div>
              @endforelse
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>

      <!-- Agent Sidebar -->
      <div class="space-y-6">
        <!-- Purchase Queue Status -->
        <x-ui.card>
          <x-ui.card-header title="Purchase Queue">
            <x-ui.button href="{{ route('purchase-decisions.index') }}" variant="ghost" size="sm">Manage</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-3">
              @forelse(Auth::user()->purchaseQueue()->pending()->take(4)->get() as $purchase)
                <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded-lg">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ $purchase->ticket->title ?? 'Event Ticket' }}</p>
                    <p class="text-xs text-gray-500">${{ $purchase->price ?? '0' }} • Qty: {{ $purchase->quantity ?? 1 }}</p>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">Pending</span>
                  </div>
                </div>
              @empty
                <div class="text-center py-6 text-gray-500">
                  <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <p class="text-sm">Queue is empty</p>
                  <a href="{{ route('tickets.scraping.index') }}" class="text-blue-600 hover:text-blue-700 text-sm">Find tickets to queue →</a>
                </div>
              @endforelse
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Performance Metrics -->
        <x-ui.card>
          <x-ui.card-header title="Performance Overview"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <!-- Success Rate Chart -->
              <div class="text-center">
                <div class="relative w-24 h-24 mx-auto mb-3">
                  <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="text-green-500" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ Auth::user()->agentStats()->success_rate ?? 95 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                  </svg>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold text-gray-900">{{ Auth::user()->agentStats()->success_rate ?? 95 }}%</span>
                  </div>
                </div>
                <p class="text-sm font-medium text-gray-900">Success Rate</p>
                <p class="text-xs text-gray-500">Last 30 days</p>
              </div>

              <!-- Monthly Stats -->
              <div class="grid grid-cols-2 gap-3 pt-4 border-t">
                <div class="text-center">
                  <div class="text-lg font-bold text-blue-600">{{ Auth::user()->agentStats()->monthly_selections ?? 0 }}</div>
                  <div class="text-xs text-gray-500">Selections</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-bold text-green-600">${{ number_format(Auth::user()->agentStats()->monthly_savings ?? 0) }}</div>
                  <div class="text-xs text-gray-500">Savings</div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="pt-4 border-t">
                <div class="space-y-2">
                  <a href="{{ route('agent.reports') }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition text-center block">
                    Detailed Reports
                  </a>
                  <a href="{{ route('agent.settings') }}" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition text-center block">
                    Agent Settings
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
      function agentDashboard() {
        return {
          stats: {
            active_monitors: 0,
            queue_items: 0,
            price_alerts: 0,
            success_rate: 95
          },

          init() {
            this.loadStats();
            this.setupRealTimeUpdates();
            this.setupAgentTools();
          },

          loadStats() {
            fetch('/api/agent/dashboard-stats')
              .then(response => response.json())
              .then(data => {
                this.stats = data;
              })
              .catch(() => {
                // Fallback to static data
                this.stats = {
                  active_monitors: {{ Auth::user()->ticketMonitors()->active()->count() }},
                  queue_items: {{ Auth::user()->purchaseQueue()->pending()->count() }},
                  price_alerts: {{ Auth::user()->priceAlerts()->active()->count() }},
                  success_rate: {{ Auth::user()->agentStats()->success_rate ?? 95 }}
                };
              });
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.private('agent.{{ Auth::id() }}')
                .listen('HighValueOpportunityDetected', (e) => {
                  this.showNotification('High-Value Opportunity!', e.message, 'success');
                  this.playAlertSound();
                })
                .listen('PurchaseQueueUpdated', (e) => {
                  this.stats.queue_items = e.queue_count;
                  this.showNotification('Queue Updated', e.message, 'info');
                })
                .listen('AutoPurchaseCompleted', (e) => {
                  this.showNotification('Auto-Purchase Success!', e.message, 'success');
                });
            }
          },

          setupAgentTools() {
            // Initialize agent-specific tools
            this.initializeBulkSelection();
            this.initializePriceAnalyzer();
          },

          initializeBulkSelection() {
            // Setup bulk selection keyboard shortcuts
            document.addEventListener('keydown', (e) => {
              if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                window.location.href = '{{ route("agent.bulk-select") }}';
              }
            });
          },

          initializePriceAnalyzer() {
            // Setup price analyzer shortcuts
            document.addEventListener('keydown', (e) => {
              if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.location.href = '{{ route("agent.price-analyzer") }}';
              }
            });
          },

          playAlertSound() {
            // Play alert sound for high-value opportunities
            try {
              const audio = new Audio('/assets/sounds/opportunity-alert.mp3');
              audio.volume = 0.3;
              audio.play();
            } catch (e) {
              console.log('Audio playback failed:', e);
            }
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }

      function toggleAutoPurchase(enabled) {
        fetch('/api/agent/auto-purchase/toggle', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ enabled: enabled })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Auto-purchase toggled:', enabled);
          }
        })
        .catch(error => console.error('Error toggling auto-purchase:', error));
      }
    </script>
  @endpush
</x-unified-layout>
