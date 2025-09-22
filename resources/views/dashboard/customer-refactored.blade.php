@extends('layouts.master')

@section('title', 'Customer Dashboard')

@section('meta_description', 'Your personalized sports event tickets monitoring dashboard')

@push('head')
  <meta name="robots" content="noindex, nofollow">
@endpush

@section('page-header')
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
      <h1 class="text-2xl lg:text-3xl font-bold text-hd-gray-900 dark:text-white">
        Welcome back, {{ auth()->user()->first_name ?? auth()->user()->name }}!
      </h1>
      <p class="mt-1 text-sm text-hd-gray-600 dark:text-hd-gray-400">
        Find amazing deals on sports tickets and manage your alerts
      </p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <!-- Subscription Status -->
      @if(auth()->user()->hasActiveSubscription())
        <div class="flex items-center gap-2 text-sm">
          <div class="w-2 h-2 bg-green-500 rounded-full"></div>
          <span class="text-hd-gray-600 dark:text-hd-gray-400">Premium Active</span>
        </div>
      @endif

      <!-- Quick Actions -->
      <x-hdt.button href="/tickets/search" size="sm" variant="primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
        </svg>
        Find Tickets
      </x-hdt.button>

      @unless(auth()->user()->hasActiveSubscription())
        <x-hdt.button href="{{ route('subscription.plans') }}" size="sm" variant="success">
          Upgrade Now
        </x-hdt.button>
      @endunless
    </div>
  </div>
@endsection

@section('content')
  <div class="space-y-8" x-data="customerDashboard()" x-init="init()">
    
    <!-- Stats Grid -->
    <div class="dashboard-stats-grid">
      <x-hdt.stat-card 
        label="Tickets This Month" 
        :value="auth()->user()->getMonthlyTicketUsage() ?? 0"
        trend="up" 
        trendValue="+3" 
        trendLabel="vs last month"
        :loading="false">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="Total Saved" 
        x-text="'$' + totalSaved.toFixed(2)"
        trend="up" 
        trendValue="+$127" 
        trendLabel="this month">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="Active Alerts" 
        x-text="activeAlerts"
        variant="warning"
        description="Price & availability tracking">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="Watchlist Items" 
        x-text="watchlistItems"
        description="Events you're tracking">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Left Column: Main Content -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Featured Tickets -->
        <x-hdt.card>
          <x-slot:title>Recommended For You</x-slot:title>
          <x-slot:subtitle>Based on your interests and search history</x-slot:subtitle>
          
          <div class="space-y-4" x-show="featuredTickets.length > 0">
            <template x-for="ticket in featuredTickets" :key="ticket.id">
              <div class="flex items-center gap-4 p-4 border border-hd-gray-200 dark:border-hd-gray-700 rounded-lg hover:border-hd-primary-300 dark:hover:border-hd-primary-600 transition-colors cursor-pointer"
                   @click="viewTicket(ticket.id)">
                <div class="w-16 h-16 bg-gradient-to-br from-hd-primary-500 to-hd-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                  <span x-text="ticket.event_title.substring(0, 2).toUpperCase()"></span>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-hd-gray-900 dark:text-hd-gray-100 truncate" x-text="ticket.event_title"></h4>
                  <p class="text-sm text-hd-gray-600 dark:text-hd-gray-400" x-text="ticket.venue + ' • ' + ticket.date"></p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-lg font-bold text-hd-success-600" x-text="'$' + ticket.price"></span>
                    <span x-show="ticket.original_price" class="text-sm text-hd-gray-500 line-through" x-text="ticket.original_price ? '$' + ticket.original_price : ''"></span>
                    <span x-show="ticket.original_price" class="text-xs bg-hd-success-100 text-hd-success-700 px-2 py-1 rounded-full" 
                          x-text="ticket.original_price ? Math.round((1 - ticket.price / ticket.original_price) * 100) + '% off' : ''"></span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-hd-gray-600 dark:text-hd-gray-400" x-text="ticket.quantity + ' available'"></div>
                  <div class="flex items-center gap-1 mt-1">
                    <div class="w-2 h-2 rounded-full" 
                         :class="ticket.status === 'available' ? 'bg-green-500' : ticket.status === 'limited' ? 'bg-yellow-500' : 'bg-red-500'"></div>
                    <span class="text-xs capitalize" x-text="ticket.status"></span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div x-show="featuredTickets.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-hd-gray-300 dark:text-hd-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            <h3 class="text-lg font-medium text-hd-gray-900 dark:text-hd-gray-100 mb-2">No recommendations yet</h3>
            <p class="text-hd-gray-600 dark:text-hd-gray-400 mb-4">Start searching for tickets to see personalized recommendations</p>
            <x-hdt.button href="/tickets/search" size="sm">Browse Tickets</x-hdt.button>
          </div>
        </x-hdt.card>

        <!-- Recent Activity -->
        <x-hdt.card>
          <x-slot:title>Recent Activity</x-slot:title>
          
          <div class="space-y-3" x-show="recentActivity.length > 0">
            <template x-for="activity in recentActivity" :key="activity.id">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                     :class="activity.type === 'purchase' ? 'bg-green-100 text-green-600' : activity.type === 'alert' ? 'bg-yellow-100 text-yellow-600' : 'bg-blue-100 text-blue-600'">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="activity.type === 'purchase'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="activity.type === 'alert'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
                  </svg>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="activity.type === 'watchlist'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-hd-gray-900 dark:text-hd-gray-100" x-text="activity.description"></p>
                  <p class="text-xs text-hd-gray-600 dark:text-hd-gray-400" x-text="activity.time_ago"></p>
                </div>
              </div>
            </template>
          </div>

          <div x-show="recentActivity.length === 0" class="text-center py-6">
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">No recent activity</p>
          </div>
        </x-hdt.card>
      </div>

      <!-- Right Column: Sidebar -->
      <div class="space-y-6">
        
        <!-- Subscription Status -->
        @unless(auth()->user()->hasActiveSubscription())
          <x-hdt.card variant="primary" padding="sm">
            <div class="text-center">
              <svg class="w-12 h-12 mx-auto text-white mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              <h3 class="font-semibold text-white mb-2">Upgrade to Premium</h3>
              <p class="text-sm text-white/80 mb-4">Unlock unlimited alerts and advanced features</p>
              <x-hdt.button href="{{ route('subscription.plans') }}" variant="ghost" size="sm" 
                            class="bg-white/20 hover:bg-white/30 text-white border-white/30">
                View Plans
              </x-hdt.button>
            </div>
          </x-hdt.card>
        @endunless

        <!-- Quick Actions -->
        <x-hdt.card>
          <x-slot:title>Quick Actions</x-slot:title>
          
          <div class="space-y-3">
            <x-hdt.button href="/alerts/create" variant="secondary" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
              </svg>
              Create Price Alert
            </x-hdt.button>
            
            <x-hdt.button href="/watchlist" variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              View Watchlist
            </x-hdt.button>
            
            <x-hdt.button href="/settings" variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Account Settings
            </x-hdt.button>
          </div>
        </x-hdt.card>

        <!-- Recent Alerts -->
        <x-hdt.card>
          <x-slot:title>Recent Alerts</x-slot:title>
          <x-slot:subtitle>
            <a href="/alerts" class="text-hd-primary-600 hover:text-hd-primary-700 text-sm font-medium">View All</a>
          </x-slot:subtitle>
          
          <div class="space-y-3" x-show="recentAlerts.length > 0">
            <template x-for="alert in recentAlerts" :key="alert.id">
              <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200" x-text="alert.event_title"></p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-300 mt-1" x-text="alert.message"></p>
                  </div>
                  <span class="text-xs text-yellow-600 dark:text-yellow-400 ml-3" x-text="alert.time_ago"></span>
                </div>
              </div>
            </template>
          </div>

          <div x-show="recentAlerts.length === 0" class="text-center py-6">
            <svg class="w-8 h-8 text-hd-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
            </svg>
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">No recent alerts</p>
          </div>
        </x-hdt.card>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function customerDashboard() {
    return {
      // Stats data
      totalSaved: 247.50,
      activeAlerts: 3,
      watchlistItems: 8,

      // Featured tickets
      featuredTickets: [
        {
          id: 1,
          event_title: 'Lakers vs Warriors',
          venue: 'Crypto.com Arena',
          date: 'Dec 15, 2024',
          price: 89,
          original_price: 120,
          status: 'available',
          quantity: 8
        },
        {
          id: 2,
          event_title: 'Cowboys vs Giants',
          venue: 'AT&T Stadium',
          date: 'Dec 18, 2024',
          price: 145,
          original_price: null,
          status: 'limited',
          quantity: 3
        }
      ],

      // Recent activity
      recentActivity: [
        {
          id: 1,
          type: 'purchase',
          description: 'Successfully found Lakers vs Warriors tickets',
          time_ago: '2 hours ago'
        },
        {
          id: 2,
          type: 'alert',
          description: 'Price drop alert: Cowboys vs Giants',
          time_ago: '4 hours ago'
        },
        {
          id: 3,
          type: 'watchlist',
          description: 'Added Chiefs vs Bills to watchlist',
          time_ago: '1 day ago'
        }
      ],

      // Recent alerts
      recentAlerts: [
        {
          id: 1,
          event_title: 'Lakers vs Warriors',
          message: 'Price dropped to $89 (-26%)',
          time_ago: '2h ago'
        },
        {
          id: 2,
          event_title: 'Cowboys vs Giants',
          message: 'New tickets available',
          time_ago: '4h ago'
        }
      ],

      async init() {
        await this.loadDashboardData();
        this.startRealTimeUpdates();
      },

      async loadDashboardData() {
        try {
          const response = await fetch('/api/v1/dashboard/stats', {
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
            }
          });

          if (response.ok) {
            const data = await response.json();
            
            this.totalSaved = data.total_saved || this.totalSaved;
            this.activeAlerts = data.active_alerts || this.activeAlerts;
            this.watchlistItems = data.watchlist_items || this.watchlistItems;
            
            if (data.featured_tickets) {
              this.featuredTickets = data.featured_tickets;
            }
            if (data.recent_activity) {
              this.recentActivity = data.recent_activity;
            }
            if (data.recent_alerts) {
              this.recentAlerts = data.recent_alerts;
            }
          }
        } catch (error) {
          console.error('Error loading dashboard data:', error);
        }
      },

      startRealTimeUpdates() {
        // Refresh dashboard data every 5 minutes
        setInterval(() => {
          this.loadDashboardData();
        }, 300000);

        // Real-time updates via WebSocket if available
        if (typeof Echo !== 'undefined') {
          Echo.private(`user.{{ auth()->id() }}`)
            .listen('PriceDropAlert', (e) => {
              this.recentAlerts.unshift({
                id: Date.now(),
                event_title: e.event_title,
                message: e.message,
                time_ago: 'just now'
              });

              // Keep only latest 5 alerts
              if (this.recentAlerts.length > 5) {
                this.recentAlerts = this.recentAlerts.slice(0, 5);
              }
            });
        }
      },

      viewTicket(ticketId) {
        window.location.href = `/tickets/${ticketId}`;
      }
    }
  }
</script>
@endpush

@push('styles')
<style>
  /* Customer dashboard specific styles */
  .dashboard-stats-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: 1fr;
  }

  @media (min-width: 640px) {
    .dashboard-stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 1024px) {
    .dashboard-stats-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }
</style>
@endpush