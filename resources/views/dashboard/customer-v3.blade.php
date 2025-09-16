@extends('layouts.app-v2')
@section('title', 'Customer Dashboard')
@section('content')
  <div x-data="customerDashboard()" x-init="init()" class="space-y-8">
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
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br"
              :class="card.color + ' flex items-center justify-center text-white'">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
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
              <div class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center">No tickets available.</div>
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
    stats: @js($statistics ?? []),
    recentTickets: @js($recentTickets ?? []),
    lastUpdate: null,
    
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
      
      try {
        const response = await fetch('/api/v1/dashboard/realtime', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        if (response.ok) {
          const result = await response.json();
          if (result.success && result.data) {
            this.stats = result.data.statistics || result.data.stats || {};
            this.recentTickets = result.data.recent_tickets || result.data.recentTickets || [];
            this.lastUpdate = new Date();
            console.log('Dashboard updated:', { stats: this.stats, tickets: this.recentTickets.length });
          } else {
            console.error('API returned unsuccessful response:', result);
          }
        } else {
          console.error('Failed to fetch dashboard data:', response.status, response.statusText);
        }
      } catch (error) {
        console.warn('Failed to refresh dashboard data:', error);
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
