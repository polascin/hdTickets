@extends('layouts.marketing')

@section('title', 'HD Tickets - Never Miss Your Favourite Sports Events')
@section('meta_description', 'Get instant alerts when tickets become available for Premier League, Champions League and more. Monitor prices across 40+ platforms. Smart automation for serious fans.')

@section('content')
{{-- Hero Section --}}
<section class="relative py-20 md:py-28 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center">
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 animate-fade-in">
        Never Miss Your<br>
        <span class="gradient-text-emerald">Favourite Sports Events</span>
      </h1>
      <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto animate-slide-in">
        Get instant alerts when tickets become available. Monitor prices across 40+ platforms. 
        Secure the best seats automatically.
      </p>
      
      {{-- Hero Search --}}
      <form action="{{ route('tickets.main') }}" method="GET" class="max-w-2xl mx-auto mb-12 animate-scale-in">
        <div class="relative">
          <input 
            type="text" 
            name="q" 
            placeholder="Search for teams, events, or venues..." 
            class="hero-search"
            aria-label="Search for sports events">
          <button 
            type="submit" 
            class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-full hover:from-emerald-700 hover:to-teal-700 transition-all shadow-md">
            Search
          </button>
        </div>
      </form>
      
      {{-- Quick Stats --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <div class="stat-card animate-slide-in">
          <div class="text-4xl font-bold text-emerald-600 mb-2">{{ number_format($stats['total_tickets'] ?? 0) }}</div>
          <div class="text-gray-600 font-medium">Active Tickets</div>
        </div>
        <div class="stat-card animate-slide-in" style="animation-delay: 0.1s;">
          <div class="text-4xl font-bold text-blue-600 mb-2">{{ $stats['platforms'] ?? 40 }}+</div>
          <div class="text-gray-600 font-medium">Platforms Monitored</div>
        </div>
        <div class="stat-card animate-slide-in" style="animation-delay: 0.2s;">
          <div class="text-4xl font-bold text-purple-600 mb-2">{{ $stats['cities'] ?? 50 }}+</div>
          <div class="text-gray-600 font-medium">Cities Covered</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose HD Tickets?</h2>
      <p class="text-xl text-gray-600 max-w-2xl mx-auto">Everything you need to never miss a match</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      {{-- Feature 1 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Smart Alerts</h3>
        <p class="text-gray-600">Get notified instantly when tickets become available for your favourite teams via email, SMS, or push notifications.</p>
      </div>
      
      {{-- Feature 2 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Official Sources</h3>
        <p class="text-gray-600">Monitor verified ticket platforms including Ticketmaster, UEFA, club stores, and 40+ other trusted sources.</p>
      </div>
      
      {{-- Feature 3 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Price Tracking</h3>
        <p class="text-gray-600">Track price changes across multiple platforms and get alerts when prices drop to your target range.</p>
      </div>
      
      {{-- Feature 4 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Auto Purchase</h3>
        <p class="text-gray-600">Automatically secure tickets when they match your criteria with our Pro plan automation features.</p>
      </div>
      
      {{-- Feature 5 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Real-time Monitoring</h3>
        <p class="text-gray-600">24/7 monitoring of ticket availability with updates delivered in real-time to your devices.</p>
      </div>
      
      {{-- Feature 6 --}}
      <div class="feature-card">
        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Analytics Dashboard</h3>
        <p class="text-gray-600">Track your savings, monitor price trends, and analyse ticket availability patterns over time.</p>
      </div>
    </div>
  </div>
</section>

{{-- How It Works Section --}}
<section class="py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
      <p class="text-xl text-gray-600">Get started in three simple steps</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
          1
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Set Your Preferences</h3>
        <p class="text-gray-600">Choose your favourite teams, preferred venues, and set your budget limits.</p>
      </div>
      
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
          2
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Get Instant Alerts</h3>
        <p class="text-gray-600">Receive notifications the moment tickets matching your criteria become available.</p>
      </div>
      
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
          3
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure Your Tickets</h3>
        <p class="text-gray-600">Purchase tickets through official platforms or let our automation do it for you.</p>
      </div>
    </div>
  </div>
</section>

{{-- CTA Section --}}
<section class="py-20 bg-gradient-to-r from-emerald-600 to-teal-600 relative overflow-hidden">
  <div class="absolute inset-0 bg-black opacity-10"></div>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">Ready to Never Miss a Match?</h2>
    <p class="text-xl md:text-2xl text-white/90 mb-8">Join thousands of fans already using HD Tickets</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-emerald-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
        Get Started Free
      </a>
      <a href="{{ route('tickets.main') }}" class="inline-block px-8 py-4 bg-white/10 backdrop-blur-sm text-white border-2 border-white rounded-lg font-semibold text-lg hover:bg-white/20 transition">
        Browse Tickets
      </a>
    </div>
    <p class="mt-6 text-white/80 text-sm">No credit card required • Free plan available • Cancel anytime</p>
  </div>
</section>
@endsection
