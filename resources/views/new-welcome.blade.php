@extends('layouts.guest-v3')

@section('title', 'hdTickets - Professional Sports Event Ticket Monitoring Platform')
@section('description',
  'Advanced sports event ticket monitoring with automated purchasing and real-time analytics
  across multiple platforms. Never miss your favorite events again.')

@section('content')
  <!-- Hero Section -->
  <section
    class="hero-section relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white overflow-hidden"
    aria-label="Welcome to hdTickets">
    <div class="absolute inset-0 bg-black bg-opacity-20" aria-hidden="true"></div>
    <!-- Decorative background pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden" aria-hidden="true">
      <svg class="absolute inset-0 w-full h-full min-w-full min-h-full" viewBox="0 0 100 100"
        preserveAspectRatio="xMidYMid slice">
        <defs>
          <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid)" />
      </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-28">
      <div class="text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
          hdTickets
          <span class="block text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal text-blue-200 mt-2">
            Professional Sports Event Ticket Monitoring
          </span>
        </h1>
        <p class="text-lg sm:text-xl lg:text-2xl text-blue-100 max-w-4xl mx-auto mb-6 sm:mb-8 leading-relaxed">
          Advanced ticket monitoring, automated purchasing, and comprehensive analytics for sports event tickets across
          multiple platforms. <span class="font-semibold text-yellow-300">Never miss your favorite events again.</span>
        </p>

        <!-- Hero Stats - Enhanced with loading states and better accessibility -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 max-w-4xl mx-auto mb-8 sm:mb-10"
          role="region" aria-label="Platform statistics">
          <div
            class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" id="total-tickets"
              data-stat="total_tickets" aria-label="Total tickets monitored">
              {{ number_format($total_tickets ?? 12500) }}
            </div>
            <div class="text-xs sm:text-sm text-blue-200 font-medium">Tickets Monitored</div>
          </div>
          <div
            class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-green-400 mb-1" id="active-events"
              data-stat="active_events" aria-label="Currently active events">
              {{ number_format($active_events ?? 342) }}
            </div>
            <div class="text-xs sm:text-sm text-blue-200 font-medium">Active Events</div>
          </div>
          <div
            class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-purple-400 mb-1" id="satisfied-customers"
              data-stat="satisfied_customers" aria-label="Number of satisfied customers">
              {{ number_format($satisfied_customers ?? 1247) }}
            </div>
            <div class="text-xs sm:text-sm text-blue-200 font-medium">Happy Customers</div>
          </div>
          <div
            class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-orange-400 mb-1" id="avg-savings"
              data-stat="avg_savings" aria-label="Average savings per customer">
              ${{ number_format($avg_savings ?? 127) }}
            </div>
            <div class="text-xs sm:text-sm text-blue-200 font-medium">Avg. Savings</div>
          </div>
        </div>

        <!-- Call-to-action buttons with improved accessibility and design -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-md mx-auto">
          <a href="/register"
            class="w-full sm:w-auto inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 focus:bg-yellow-600 text-black font-semibold px-8 py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 group"
            aria-label="Start monitoring sports events - Free trial available">
            <span class="mr-2">Start Monitoring</span>
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none"
              viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="/login"
            class="w-full sm:w-auto inline-flex items-center justify-center bg-transparent border-2 border-white hover:bg-white hover:text-blue-900 focus:bg-white focus:text-blue-900 text-white font-semibold px-8 py-4 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/50"
            aria-label="Sign in to your existing account">
            Sign In
          </a>
        </div>

        <!-- Trust indicators -->
        <div class="mt-8 sm:mt-12 text-center">
          <p class="text-sm text-blue-200 mb-4 font-medium">Trusted by sports fans worldwide</p>
          <div class="flex justify-center items-center space-x-8 opacity-60">
            <div class="text-xs text-blue-300">🏈 NFL</div>
            <div class="text-xs text-blue-300">🏀 NBA</div>
            <div class="text-xs text-blue-300">⚾ MLB</div>
            <div class="text-xs text-blue-300">🏒 NHL</div>
            <div class="text-xs text-blue-300">⚽ MLS</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Key Features Section -->
  <section class="py-16 sm:py-20 lg:py-24 bg-gray-50 dark:bg-gray-900" aria-label="Platform features">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 sm:mb-16">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6">
          Comprehensive Ticket Monitoring Platform
        </h2>
        <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed">
          Everything you need to monitor, analyze, and purchase sports event tickets across multiple platforms with
          intelligent automation
        </p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
        <!-- Real-time Monitoring -->
        <div class="feature-card group">
          <div class="feature-icon bg-blue-100 dark:bg-blue-900 group-hover:bg-blue-200 dark:group-hover:bg-blue-800">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Real-time Monitoring</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
            24/7 monitoring of ticket availability and prices across multiple platforms with instant notifications when
            your criteria are met
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Ticketmaster integration
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              StubHub monitoring
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              SeatGeek & Vivid Seats tracking
            </li>
          </ul>
        </div>

        <!-- Automated Purchasing -->
        <div class="feature-card group">
          <div
            class="feature-icon bg-green-100 dark:bg-green-900 group-hover:bg-green-200 dark:group-hover:bg-green-800">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Automated Purchasing</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
            Smart automated purchasing based on your preferences, budget, and seat selection criteria with lightning-fast
            execution
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Price threshold triggers
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Smart seat preference matching
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Instant success notifications
            </li>
          </ul>
        </div>

        <!-- Advanced Analytics -->
        <div class="feature-card group sm:col-span-2 lg:col-span-1">
          <div
            class="feature-icon bg-indigo-100 dark:bg-indigo-900 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800">
            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white mb-3">Advanced Analytics</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
            Comprehensive insights into ticket prices, market trends, and purchase patterns with predictive analytics and
            custom dashboards
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Real-time price analysis
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Historical trends & predictions
            </li>
            <li class="flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Customizable dashboards
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing Section -->
  <section class="py-16 sm:py-20 lg:py-24 bg-white dark:bg-gray-800" aria-label="Subscription plans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 sm:mb-16">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6">
          Choose Your Plan
        </h2>
        <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed">
          Select the subscription plan that best fits your ticket monitoring needs. All plans include our core monitoring
          features.
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-6 sm:gap-8 max-w-6xl mx-auto">
        <!-- Basic Plan -->
        <div class="pricing-card">
          <div class="pricing-header">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">Basic</h3>
            <div class="pricing-amount">
              <span class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400">$29</span>
              <span class="text-base sm:text-lg text-gray-500 dark:text-gray-400">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-2 mb-6">Perfect for individual users</p>
          </div>
          <ul class="pricing-features" aria-label="Basic plan features">
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Up to 5 events monitored</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Email notifications</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Basic price alerts</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Email support</span>
            </li>
          </ul>
          <div class="pricing-footer">
            <a href="/register?plan=basic" class="pricing-cta pricing-cta-secondary"
              aria-label="Subscribe to Basic plan for $29 per month">
              Get Started
            </a>
          </div>
        </div>

        <!-- Pro Plan (Most Popular) -->
        <div class="pricing-card pricing-card-featured" aria-labelledby="pro-plan-badge">
          <div class="pricing-badge" id="pro-plan-badge" aria-label="Most popular plan">
            <span class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold">Most Popular</span>
          </div>
          <div class="pricing-header">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">Pro</h3>
            <div class="pricing-amount">
              <span class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400">$79</span>
              <span class="text-base sm:text-lg text-gray-500 dark:text-gray-400">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-2 mb-6">Best for power users</p>
          </div>
          <ul class="pricing-features" aria-label="Pro plan features">
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Up to 25 events monitored</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Advanced notifications (SMS + Email)</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Automated purchasing</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Priority support</span>
            </li>
          </ul>
          <div class="pricing-footer">
            <a href="/register?plan=pro" class="pricing-cta pricing-cta-primary"
              aria-label="Subscribe to Pro plan for $79 per month">
              Get Started
            </a>
          </div>
        </div>

        <!-- Enterprise Plan -->
        <div class="pricing-card">
          <div class="pricing-header">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">Enterprise</h3>
            <div class="pricing-amount">
              <span class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400">$199</span>
              <span class="text-base sm:text-lg text-gray-500 dark:text-gray-400">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-2 mb-6">For organizations</p>
          </div>
          <ul class="pricing-features" aria-label="Enterprise plan features">
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Unlimited events monitored</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Custom integrations & API</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Team collaboration tools</span>
            </li>
            <li class="feature-item">
              <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>24/7 dedicated support</span>
            </li>
          </ul>
          <div class="pricing-footer">
            <a href="/contact?plan=enterprise" class="pricing-cta pricing-cta-secondary"
              aria-label="Contact sales for Enterprise plan pricing">
              Contact Sales
            </a>
          </div>
        </div>
      </div>

      <!-- Money-back guarantee -->
      <div class="text-center mt-8 sm:mt-12">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          💰 30-day money-back guarantee • 🚀 Start your free trial today • 📞 No setup fees
        </p>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-16 sm:py-20 lg:py-24 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white"
    aria-label="Get started with hdTickets">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-4 sm:mb-6">
        Ready to Never Miss Another Event?
      </h2>
      <p class="text-lg sm:text-xl text-blue-100 mb-6 sm:mb-8 max-w-3xl mx-auto leading-relaxed">
        Join thousands of satisfied customers who use hdTickets to secure tickets to their favorite sports events.
        Start your <strong class="text-yellow-300">free 14-day trial</strong> today.
      </p>

      <!-- Social proof -->
      <div class="flex flex-col sm:flex-row justify-center items-center gap-6 sm:gap-8 mb-8 sm:mb-10">
        <div class="flex items-center">
          <div class="flex -space-x-2">
            <div
              class="w-8 h-8 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-gray-900">
              J</div>
            <div
              class="w-8 h-8 bg-green-400 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-gray-900">
              M</div>
            <div
              class="w-8 h-8 bg-purple-400 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-gray-900">
              S</div>
            <div
              class="w-8 h-8 bg-pink-400 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-gray-900">
              A</div>
          </div>
          <span class="ml-3 text-sm text-blue-200">1,247+ happy customers</span>
        </div>
        <div class="flex items-center">
          <div class="flex text-yellow-300">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <span class="ml-2 text-sm text-blue-200">4.9/5 rating</span>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-lg mx-auto">
        <a href="/register"
          class="w-full sm:w-auto inline-flex items-center justify-center bg-white text-blue-600 hover:bg-gray-100 focus:bg-gray-100 font-semibold px-8 py-4 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-white/50 group"
          aria-label="Start your free 14-day trial">
          <span class="mr-2">Start Free Trial</span>
          <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
        <a href="/contact"
          class="w-full sm:w-auto inline-flex items-center justify-center bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 focus:bg-white focus:text-blue-600 text-white font-semibold px-8 py-4 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/50"
          aria-label="Contact our sales team">
          Contact Sales
        </a>
      </div>

      <!-- Security badges -->
      <div class="mt-8 sm:mt-10 pt-6 sm:pt-8 border-t border-blue-400/30">
        <p class="text-xs sm:text-sm text-blue-200 mb-4">Your data is secure with us</p>
        <div class="flex justify-center items-center space-x-6 sm:space-x-8 text-blue-300">
          <div class="flex items-center text-xs">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            SSL Encrypted
          </div>
          <div class="flex items-center text-xs">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            GDPR Compliant
          </div>
          <div class="flex items-center text-xs">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Secure Payments
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection

@push('scripts')
  @vite(['resources/js/welcome.js'])

  <script>
    // Initialize welcome page functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize the welcome page manager
      const welcomeManager = new WelcomePageManager();

      // Initialize Alpine.js components for interactivity
      if (typeof Alpine !== 'undefined') {
        Alpine.start();
      }

      // Progressive enhancement for users without JavaScript
      if (!window.WelcomePageManager) {
        console.warn('Welcome page JavaScript not loaded, some features may be limited');
      }
    });

    // Handle stats loading errors gracefully
    window.addEventListener('error', function(e) {
      if (e.filename && e.filename.includes('welcome.js')) {
        console.warn('Welcome page enhanced features not available');
      }
    });
  </script>
@endpush

@push('styles')
  <style>
    /* Prevent horizontal overflow and ensure usability */
    body {
      overflow-x: hidden;
    }

    .max-w-7xl {
      max-width: min(80rem, calc(100vw - 2rem));
    }

    /* Enhanced card styles */
    .feature-card {
      @apply bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700;
      min-height: 320px;
      /* Increased for better content distribution */
      display: flex;
      flex-direction: column;
    }

    .feature-card:hover {
      @apply transform -translate-y-1;
    }

    .feature-icon {
      @apply mx-auto flex items-center justify-center rounded-xl mb-4 sm:mb-6 transition-colors duration-300;
      width: 3rem !important;
      height: 3rem !important;
      flex-shrink: 0;
    }

    /* Fix potential SVG sizing issues */
    .feature-icon svg,
    .feature-card svg {
      flex-shrink: 0 !important;
      max-width: none !important;
    }

    .feature-icon svg {
      width: 1.5rem !important;
      height: 1.5rem !important;
    }

    /* Ensure consistent icon sizing */
    .w-4 {
      width: 1rem !important;
    }

    .h-4 {
      height: 1rem !important;
    }

    .w-5 {
      width: 1.25rem !important;
    }

    .h-5 {
      height: 1.25rem !important;
    }

    .w-6 {
      width: 1.5rem !important;
    }

    .h-6 {
      height: 1.5rem !important;
    }

    /* Pricing card styles */
    .pricing-card {
      @apply bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 relative;
      min-height: 450px;
      /* Ensure consistent pricing card heights */
      display: flex;
      flex-direction: column;
    }

    .pricing-card-featured {
      @apply ring-2 ring-blue-500 dark:ring-blue-400;
      transform: scale(1.02);
      /* Reduced from 1.05 to prevent layout issues */
    }

    .pricing-badge {
      @apply absolute -top-4 left-1/2 transform -translate-x-1/2 z-10;
    }

    .pricing-header {
      @apply text-center flex-shrink-0;
    }

    .pricing-amount {
      @apply flex items-baseline justify-center mb-4;
    }

    .pricing-features {
      @apply space-y-3 mb-8 flex-1;
      min-height: 160px;
      /* Ensure consistent feature list height */
    }

    .pricing-footer {
      @apply mt-auto flex-shrink-0;
    }

    .feature-item {
      @apply flex items-start;
    }

    .pricing-cta {
      @apply block w-full text-center font-semibold py-4 px-6 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4;
    }

    .pricing-cta-primary {
      @apply bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 text-white focus:ring-blue-300;
    }

    .pricing-cta-secondary {
      @apply bg-gray-100 hover:bg-gray-200 focus:bg-gray-200 text-gray-900 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white dark:focus:ring-gray-500;
    }

    /* Animation utilities */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Loading states for stats */
    [data-stat].loading::after {
      content: '';
      display: inline-block;
      width: 12px;
      height: 12px;
      border: 2px solid currentColor;
      border-radius: 50%;
      border-top-color: transparent;
      animation: spin 1s linear infinite;
      margin-left: 8px;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Focus improvements for better accessibility */
    a:focus-visible,
    button:focus-visible {
      @apply ring-4 ring-blue-300 ring-offset-2 ring-offset-white dark:ring-offset-gray-900;
    }

    /* Reduced motion for users who prefer it */
    @media (prefers-reduced-motion: reduce) {

      .fade-in,
      .feature-card:hover,
      .group-hover\:translate-x-1,
      .group-hover\:translate-x-1 {
        transition: none;
        transform: none;
      }
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {

      .feature-card,
      .pricing-card {
        @apply border-2 border-gray-900 dark:border-white;
      }
    }

    /* Layout stability fixes */
    .hero-section {
      position: relative;
      overflow: hidden;
    }

    /* Prevent horizontal overflow */
    body {
      overflow-x: hidden;
    }

    /* Fix container widths on small screens */
    @media (max-width: 640px) {
      .pricing-card-featured {
        transform: none !important;
        scale: 1 !important;
      }

      .feature-card {
        min-height: auto;
      }

      .pricing-card {
        min-height: auto;
      }
    }

    /* SVG containment */
    svg {
      overflow: visible;
      max-width: 100%;
      height: auto;
    }
  </style>
@endpush
