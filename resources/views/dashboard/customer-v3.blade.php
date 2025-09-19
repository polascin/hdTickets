@extends('layouts.app-v2')
@section('title', 'Customer Dashboard')
@push('styles')
  <meta name="dashboard-realtime-url" content="{{ route('api.dashboard.realtime') }}">
@endpush

@section('content')
  <div x-data="customerDashboard()" x-init="init()" class="space-y-8">
    {{-- Error notification --}}
    <div x-show="errorMessage" x-transition 
         class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-red-800 dark:text-red-200" x-text="errorMessage"></p>
        </div>
        <div class="ml-auto pl-3">
          <button @click="errorMessage = ''" class="inline-flex text-red-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
          </button>
        </div>
      </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
      <template
        x-for="card in [
      {k:'available_tickets',label:'Available',icon:'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9',color:'from-blue-500 to-indigo-600'},
      {k:'new_today',label:'New Today',icon:'M12 8v4l3 3',color:'from-emerald-500 to-teal-600'},
      {k:'monitored_events',label:'Events',icon:'M5 3l14 0-2 18H7L5 3z',color:'from-cyan-500 to-sky-600'},
      {k:'active_alerts',label:'Alerts',icon:'M12 9v2m0 4h.01',color:'from-amber-500 to-orange-600'},
      {k:'price_alerts',label:'Price Alerts',icon:'M12 6v6l4 2',color:'from-fuchsia-500 to-pink-600'},
      {k:'triggered_today',label:'Triggered',icon:'M5 13l4 4L19 7',color:'from-rose-500 to-red-600'}]"
        :key="card.k">
        <div
          class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400" x-text="card.label">
              </p>
              <p class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-50" 
                 x-text="formatNumber(stats[card.k]) || '0'"
                 x-show="!loading"></p>
              <div x-show="loading" class="mt-1 h-7 w-12 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br"
              :class="card.color + ' flex items-center justify-center text-white'">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path :d="card.icon" />
              </svg>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Recent Tickets
            </h2>
            <a href="{{ route('tickets.scraping.index') }}"
              class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">View all</a>
          </div>
          <div id="recentTickets" class="grid gap-3">
            <template x-if="recentTickets.length===0 && !loading">
              <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">No tickets available</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Check back later for new sports event tickets.</p>
                <div class="mt-4">
                  <a href="{{ route('tickets.scraping.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Browse All Tickets
                  </a>
                </div>
              </div>
            </template>
            <template x-if="loading">
              <div class="grid gap-3">
                <template x-for="i in 3" :key="i">
                  <div class="rounded-lg border border-slate-200/60 dark:border-slate-700/60 p-3 animate-pulse">
                    <div class="flex items-center justify-between">
                      <div class="min-w-0 pr-3 space-y-2">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-32"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-24"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-20"></div>
                      </div>
                      <div class="text-right flex-shrink-0 space-y-2">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-12"></div>
                        <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-16"></div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </template>
            <template x-for="t in recentTickets.slice(0, 8)" :key="t.id">
              <a :href="'/tickets/' + t.id + '/purchase'"
                class="group rounded-lg border border-slate-200/60 dark:border-slate-700/60 p-3 flex items-center justify-between hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                <div class="min-w-0 pr-3">
                  <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate" x-text="t.title || 'Sports Event'"></p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="t.venue || 'TBD'"></p>
                  <p class="mt-0.5 text-[11px] uppercase tracking-wide text-slate-400"
                    x-text="(t.sport || 'Sports') + ' • ' + (t.platform || 'Unknown')"></p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm font-semibold text-slate-900 dark:text-slate-50"
                    x-text="'$'+(t.price ? Number(t.price).toFixed(0) : '0')"></p>
                  <span
                    class="inline-flex mt-1 items-center px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-[10px] font-medium text-slate-600 dark:text-slate-300"
                    x-text="t.event_date || 'TBD'"></span>
                </div>
              </a>
            </template>
          </div>
        </div>
      </div>
      <div class="space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 p-5">
          <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-4">Quick Actions
          </h2>
          <div class="grid gap-2">
            <a href="{{ route('tickets.scraping.index') }}" class="uiv2-action-btn">Browse Tickets</a>
            <a href="{{ route('monitoring.index') }}" class="uiv2-action-btn">Monitoring</a>
            <a href="{{ route('purchase-decisions.index') }}" class="uiv2-action-btn">Purchase Queue</a>
            <a href="{{ route('ticket-sources.index') }}" class="uiv2-action-btn">Sources</a>
            <a href="{{ route('dashboard.analytics') }}" class="uiv2-action-btn">Analytics</a>
          </div>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 p-5">
          <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-4">System</h2>
          <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
            <li>Version: {{ config('ui.app.version') }}</li>
            <li>PHP: {{ PHP_VERSION }}</li>
            <li>Environment: {{ app()->environment() }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

<script>
function customerDashboard() {
  return {
    loading: false,
    errorMessage: '',
    stats: @js($statistics ?? []),
    recentTickets: @js($recentTickets ?? []),
    lastUpdate: null,
    retryCount: 0,
    maxRetries: 3,
    
    init() {
      console.log('Customer Dashboard initialized');
      console.log('Initial stats:', this.stats);
      console.log('Initial tickets:', this.recentTickets);
      
      // Start periodic updates
      this.startPeriodicUpdates();
    },
    
    formatNumber(value) {
      if (value === null || value === undefined) return '0';
      if (typeof value === 'object') {
        console.warn('formatNumber received object:', value);
        return '0';
      }
      return Number(value).toLocaleString();
    },
    
    async refreshData() {
      if (this.loading) return;
      
      this.loading = true;
      this.errorMessage = ''; // Clear previous errors
      
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
          throw new Error('CSRF token not found. Please refresh the page.');
        }

        const realtimeUrl = document.querySelector('meta[name="dashboard-realtime-url"]')?.getAttribute('content') || '/api/v1/dashboard/realtime';
        const response = await fetch(realtimeUrl, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin'
        });
        
        if (response.ok) {
          const result = await response.json();
          if (result.success && result.data) {
            this.stats = result.data.statistics || result.data.stats || {};
            this.recentTickets = result.data.recent_tickets || result.data.recentTickets || [];
            this.lastUpdate = new Date();
            this.retryCount = 0; // Reset retry count on success
            console.log('Dashboard updated successfully:', { 
              stats: Object.keys(this.stats).length, 
              tickets: this.recentTickets.length 
            });
          } else {
            throw new Error(result.error || 'Invalid response format from server');
          }
        } else if (response.status === 401) {
          this.errorMessage = 'Authentication expired. Please refresh the page and log in again.';
          console.error('Authentication failed');
        } else if (response.status >= 500) {
          throw new Error('Server error. Please try again later.');
        } else {
          throw new Error(`Request failed with status ${response.status}`);
        }
      } catch (error) {
        console.error('Dashboard refresh failed:', error);
        
        this.retryCount++;
        if (this.retryCount <= this.maxRetries) {
          this.errorMessage = `Update failed (attempt ${this.retryCount}/${this.maxRetries}). Retrying...`;
          setTimeout(() => this.refreshData(), 5000); // Retry after 5 seconds
        } else {
          this.errorMessage = error.message || 'Failed to update dashboard data. Please refresh the page.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    startPeriodicUpdates() {
      // Update every 2 minutes
      setInterval(() => {
        this.refreshData();
      }, 120000);
    }
  };
}
</script>
@endsection
