@extends('layouts.guest-v3')

@section('title', 'hdTickets - Professional Sports Event Ticket Monitoring Platform')

@section('content')
  <!-- Hero Section -->
  <section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white overflow-hidden">
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          hdTickets
          <span class="block text-2xl md:text-3xl font-normal text-blue-200">
            Professional Sports Event Ticket Monitoring
          </span>
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-8">
          Advanced ticket monitoring, automated purchasing, and comprehensive analytics for sports event tickets across
          multiple platforms
        </p>

        <!-- Hero Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto mb-8">
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold text-yellow-400" id="total-tickets">
              {{ number_format($total_tickets ?? 12500) }}</div>
            <div class="text-sm text-blue-200">Tickets Monitored</div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold text-green-400" id="active-events">
              {{ number_format($active_events ?? 342) }}</div>
            <div class="text-sm text-blue-200">Active Events</div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold text-purple-400" id="satisfied-customers">
              {{ number_format($satisfied_customers ?? 1247) }}</div>
            <div class="text-sm text-blue-200">Happy Customers</div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold text-orange-400" id="avg-savings">
              ${{ number_format($avg_savings ?? 127) }}</div>
            <div class="text-sm text-blue-200">Avg. Savings</div>
          </div>
        </div>

        <div class="space-x-4">
          <a href="/register"
            class="inline-block bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
            Start Monitoring
          </a>
          <a href="/login"
            class="inline-block bg-transparent border-2 border-white hover:bg-white hover:text-blue-900 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
            Sign In
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Key Features Section -->
  <section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Comprehensive Ticket Monitoring Platform
        </h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
          Everything you need to monitor, analyze, and purchase sports event tickets across multiple platforms
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Real-time Monitoring -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Real-time Monitoring</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            24/7 monitoring of ticket availability and prices across multiple platforms
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• Ticketmaster integration</li>
            <li class="mb-1">• StubHub monitoring</li>
            <li class="mb-1">• SeatGeek tracking</li>
            <li class="mb-1">• Vivid Seats coverage</li>
          </ul>
        </div>

        <!-- Automated Purchasing -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 mb-4">
            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Automated Purchasing</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Smart automated purchasing based on your preferences and budget
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• Price threshold triggers</li>
            <li class="mb-1">• Seat preference matching</li>
            <li class="mb-1">• Budget optimization</li>
            <li class="mb-1">• Instant notifications</li>
          </ul>
        </div>

        <!-- Advanced Analytics -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 mb-4">
            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Advanced Analytics & Reporting</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Comprehensive insights into ticket prices, market trends, and purchase patterns
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• Real-time price volatility analysis</li>
            <li class="mb-1">• Historical price tracking</li>
            <li class="mb-1">• Market trend predictions</li>
            <li class="mb-1">• Custom reporting dashboards</li>
          </ul>
        </div>

        <!-- Security & Compliance -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 dark:bg-red-900 mb-4">
            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Security & Compliance</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Enterprise-grade security with full compliance and audit trails
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• Two-factor authentication</li>
            <li class="mb-1">• Device fingerprinting</li>
            <li class="mb-1">• GDPR compliance</li>
            <li class="mb-1">• PCI DSS certified</li>
          </ul>
        </div>

        <!-- Role-Based Access -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900 mb-4">
            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Multi-Role Architecture</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Comprehensive role-based access control for different user types
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• Customer dashboards</li>
            <li class="mb-1">• Agent monitoring panels</li>
            <li class="mb-1">• Admin control centers</li>
            <li class="mb-1">• Scraper API access</li>
          </ul>
        </div>

        <!-- Platform Integration -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 dark:bg-yellow-900 mb-4">
            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Platform Integration</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Seamless integration with major ticket platforms and payment systems
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            <li class="mb-1">• API-first architecture</li>
            <li class="mb-1">• Real-time synchronization</li>
            <li class="mb-1">• Webhook notifications</li>
            <li class="mb-1">• Third-party integrations</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing Section -->
  <section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Subscription Plans
        </h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
          Choose the plan that best fits your ticket monitoring needs
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        @if (isset($subscription_plans) && is_array($subscription_plans))
          @foreach ($subscription_plans as $plan)
            <div class="card text-center {{ $plan['featured'] ?? false ? 'ring-2 ring-blue-500' : '' }}">
              @if ($plan['featured'] ?? false)
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">Most Popular</span>
                </div>
              @endif

              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan['name'] }}</h3>
              <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-4">
                ${{ number_format($plan['price']) }}
                <span class="text-lg text-gray-500">{{ $plan['billing'] }}</span>
              </div>
              <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $plan['description'] }}</p>

              <ul class="text-left text-gray-600 dark:text-gray-400 mb-8 space-y-2">
                @foreach ($plan['features'] as $feature)
                  <li class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $feature }}
                  </li>
                @endforeach
              </ul>

              <a href="/register?plan={{ strtolower($plan['name']) }}"
                class="block w-full {{ $plan['featured'] ?? false ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-900' }} font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                Get Started
              </a>
            </div>
          @endforeach
        @else
          <!-- Default pricing plans -->
          <div class="card text-center">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Basic</h3>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-4">
              $29<span class="text-lg text-gray-500">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Perfect for individual users</p>
            <ul class="text-left text-gray-600 dark:text-gray-400 mb-8 space-y-2">
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Up to 5 events monitored</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Basic notifications</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Email support</li>
            </ul>
            <a href="/register?plan=basic"
              class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-3 px-6 rounded-lg transition-colors duration-200">Get
              Started</a>
          </div>

          <div class="card text-center ring-2 ring-blue-500">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
              <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">Most Popular</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Pro</h3>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-4">
              $79<span class="text-lg text-gray-500">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Best for power users</p>
            <ul class="text-left text-gray-600 dark:text-gray-400 mb-8 space-y-2">
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Up to 25 events monitored</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Advanced notifications</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Automated purchasing</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Priority support</li>
            </ul>
            <a href="/register?plan=pro"
              class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">Get
              Started</a>
          </div>

          <div class="card text-center">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Enterprise</h3>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-4">
              $199<span class="text-lg text-gray-500">/month</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">For organizations</p>
            <ul class="text-left text-gray-600 dark:text-gray-400 mb-8 space-y-2">
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Unlimited events</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Custom integrations</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>Advanced analytics</li>
              <li class="flex items-center"><svg class="h-5 w-5 text-green-500 mr-2" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>24/7 dedicated support</li>
            </ul>
            <a href="/contact"
              class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-3 px-6 rounded-lg transition-colors duration-200">Contact
              Sales</a>
          </div>
        @endif
      </div>
    </div>
  </section>

  <!-- Platform Integration Section -->
  <section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Platform Integrations
        </h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
          We monitor tickets across all major platforms and secondary markets
        </p>
      </div>

      @if (isset($platform_integrations) && is_array($platform_integrations))
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
          @foreach ($platform_integrations as $platform)
            <div class="text-center">
              <div
                class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md hover:shadow-lg transition-shadow duration-200">
                <img src="{{ $platform['logo'] ?? '/images/platform-placeholder.png' }}" alt="{{ $platform['name'] }}"
                  class="h-12 mx-auto mb-2">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $platform['name'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $platform['status'] ?? 'Active' }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <!-- Default platform integrations -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div
              class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="text-3xl mb-2">🎫</div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Ticketmaster</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Primary Platform</p>
            </div>
          </div>
          <div class="text-center">
            <div
              class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="text-3xl mb-2">📍</div>
              <h3 class="font-semibold text-gray-900 dark:text-white">StubHub</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Secondary Market</p>
            </div>
          </div>
          <div class="text-center">
            <div
              class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="text-3xl mb-2">💺</div>
              <h3 class="font-semibold text-gray-900 dark:text-white">SeatGeek</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Aggregator</p>
            </div>
          </div>
          <div class="text-center">
            <div
              class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="text-3xl mb-2">🎪</div>
              <h3 class="font-semibold text-gray-900 dark:text-white">Vivid Seats</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Reseller Platform</p>
            </div>
          </div>
        </div>
      @endif
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">
        Ready to Start Monitoring?
      </h2>
      <p class="text-xl text-blue-100 mb-8">
        Join thousands of satisfied customers who never miss out on their favorite events
      </p>
      <div class="space-x-4">
        <a href="/register"
          class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
          Start Free Trial
        </a>
        <a href="/contact"
          class="inline-block bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
          Contact Sales
        </a>
      </div>
    </div>
  </section>

  <!-- Footer Information -->
  <section class="py-16 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
          <h3 class="text-2xl font-bold mb-4">hdTickets</h3>
          <p class="text-gray-400 mb-4">
            Professional sports event ticket monitoring platform designed for serious fans, agents, and organizations who
            need reliable access to tickets across multiple platforms.
          </p>
          <div class="flex space-x-4">
            <span class="text-gray-400">📧 support@hdtickets.com</span>
          </div>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Features</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white transition-colors">Real-time Monitoring</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Automated Purchasing</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Analytics Dashboard</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Multi-Platform Support</a></li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Legal & Compliance</h4>
          <ul class="space-y-2 text-gray-400">
            @if (isset($legal_documents) && is_array($legal_documents))
              @foreach ($legal_documents as $doc_key => $doc_url)
                <li><a href="{{ $doc_url }}"
                    class="hover:text-white transition-colors">{{ ucwords(str_replace('_', ' ', $doc_key)) }}</a></li>
              @endforeach
            @else
              <li><a href="/legal/terms-of-service" class="hover:text-white transition-colors">Terms of Service</a></li>
              <li><a href="/legal/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</a></li>
              <li><a href="/legal/cookie-policy" class="hover:text-white transition-colors">Cookie Policy</a></li>
              <li><a href="/legal/gdpr-compliance" class="hover:text-white transition-colors">GDPR Compliance</a></li>
            @endif
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
        <p>&copy; {{ date('Y') }} hdTickets. Professional Sports Event Ticket Monitoring Platform. All rights
          reserved.</p>
        <p class="mt-2 text-sm">Built with Laravel {{ app()->version() }} | PHP {{ phpversion() }} | Running on Ubuntu
          24.04 LTS</p>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">
        Ready to Start Monitoring?
      </h2>
      <p class="text-xl text-blue-100 mb-8">
        Join thousands of satisfied customers who never miss out on their favorite events
      </p>
      <div class="space-x-4">
        <a href="/register"
          class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
          Start Free Trial
        </a>
        <a href="/contact"
          class="inline-block bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
          Contact Sales
        </a>
      </div>
    </div>
  </section>

  <!-- Footer Information -->
  <section class="py-16 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
          <h3 class="text-2xl font-bold mb-4">hdTickets</h3>
          <p class="text-gray-400 mb-4">
            Professional sports event ticket monitoring platform designed for serious fans, agents, and organizations who
            need reliable access to tickets across multiple platforms.
          </p>
          <div class="flex space-x-4">
            <span class="text-gray-400">📧 support@hdtickets.com</span>
          </div>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Features</h4>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white transition-colors">Real-time Monitoring</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Automated Purchasing</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Analytics Dashboard</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Multi-Platform Support</a></li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Legal & Compliance</h4>
          <ul class="space-y-2 text-gray-400">
            @if (isset($legal_documents) && is_array($legal_documents))
              @foreach ($legal_documents as $doc_key => $doc_url)
                <li><a href="{{ $doc_url }}"
                    class="hover:text-white transition-colors">{{ ucwords(str_replace('_', ' ', $doc_key)) }}</a></li>
              @endforeach
            @else
              <li><a href="/legal/terms-of-service" class="hover:text-white transition-colors">Terms of Service</a></li>
              <li><a href="/legal/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</a></li>
              <li><a href="/legal/cookie-policy" class="hover:text-white transition-colors">Cookie Policy</a></li>
              <li><a href="/legal/gdpr-compliance" class="hover:text-white transition-colors">GDPR Compliance</a></li>
            @endif
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
        <p>&copy; {{ date('Y') }} hdTickets. Professional Sports Event Ticket Monitoring Platform. All rights
          reserved.</p>
        <p class="mt-2 text-sm">Built with Laravel {{ app()->version() }} | PHP {{ phpversion() }} | Running on Ubuntu
          24.04 LTS</p>
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
      Alpine.start();
    });
  </script>
@endpush

@push('styles')
  <style>
    .card {
      @apply bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6;
    }

    .gradient-text {
      @apply bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent;
    }

    /* Smooth scroll behavior */
    html {
      scroll-behavior: smooth;
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
  </style>
@endpush <!-- Advanced Analytics -->
<div class="card text-center">
  <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 mb-4">
    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
      stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  </div>
  <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Advanced Analytics & Reporting</h3>
  <p class="text-gray-600 dark:text-gray-400 mb-4">
    Comprehensive insights into ticket prices, market trends, and purchase patterns.
  </p>
  <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
    <li class="mb-1">• Real-time price volatility analysis</li>
    <li class="mb-1">• Historical price tracking</li>
    <li class="mb-1">• Market trend predictions</li>
    <li class="mb-1">• Custom reporting dashboards</li>
  </ul>
</div>
</div>
</div>
</section>

<!-- Technical Architecture Section -->
<section class="py-20 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Robust Technical Architecture
      </h2>
      <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Built on modern Laravel framework with enterprise-grade infrastructure and microservices architecture.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
      <!-- Backend Framework -->
      <div class="card text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 dark:bg-red-900 mb-4">
          <span class="text-2xl">⚡</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Laravel Framework</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Modern PHP framework with built-in security, ORM, and extensive ecosystem
        </p>
      </div>

      <!-- Database Architecture -->
      <div class="card text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 mb-4">
          <span class="text-2xl">🗄️</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Optimized Database</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          MySQL with advanced indexing, caching layers, and query optimization
        </p>
      </div>

      <!-- API Infrastructure -->
      <div class="card text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 mb-4">
          <span class="text-2xl">🔗</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">RESTful APIs</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Comprehensive API ecosystem with rate limiting and authentication
        </p>
      </div>

      <!-- Queue System -->
      <div class="card text-center">
        <div
          class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900 mb-4">
          <span class="text-2xl">⚙️</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Background Processing</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Redis-powered queue system for scalable background job processing
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Pricing Section -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Simple, Transparent Pricing
      </h2>
      <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Start with our {{ $pricing['free_trial_days'] ?? 7 }}-day free trial. No hidden fees, cancel anytime.
      </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
      <!-- Monthly Plan -->
      <div class="card text-center relative" data-plan="monthly">
        <div class="p-8">
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Monthly Plan</h3>
          <div class="mb-6">
            <span
              class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($pricing['monthly_price'] ?? 29.99, 2) }}</span>
            <span class="text-gray-600 dark:text-gray-400">/month</span>
          </div>
          <ul class="text-left space-y-3 mb-8">
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              {{ $pricing['default_ticket_limit'] ?? 100 }} tickets per month
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              50+ platform monitoring
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              Real-time price alerts
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              Email & SMS notifications
            </li>
          </ul>
          <a href="{{ route('register') }}" class="btn btn-primary w-full">
            Start Free Trial
          </a>
        </div>
      </div>

      <!-- Yearly Plan -->
      <div class="card text-center relative" data-plan="yearly">
        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
          <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">Save 17%</span>
        </div>
        <div class="p-8">
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Yearly Plan</h3>
          <div class="mb-6">
            <span
              class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format(($pricing['yearly_price'] ?? 299.99) / 12, 2) }}</span>
            <span class="text-gray-600 dark:text-gray-400">/month</span>
            <div class="text-sm text-gray-500">Billed annually
              ({{ number_format($pricing['yearly_price'] ?? 299.99, 2) }})</div>
          </div>
          <ul class="text-left space-y-3 mb-8">
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              Unlimited tickets
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              Priority support
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              Advanced analytics
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              API access included
            </li>
          </ul>
          <a href="{{ route('register') }}" class="btn btn-primary w-full">
            Start Free Trial
          </a>
        </div>
      </div>
    </div>

    <!-- Enterprise Notice -->
    <div class="text-center mt-12">
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Need enterprise features? Contact us for custom solutions.
      </p>
      <div class="flex flex-wrap justify-center gap-4 text-sm text-gray-500">
        <span>• Agent roles have unlimited access</span>
        <span>• No money-back guarantee policy</span>
        <span>• Service provided "as-is"</span>
      </div>
    </div>
  </div>
</section>
<section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
  <div class="absolute inset-0 bg-black bg-opacity-20"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
    <div class="text-center">
      <h1 class="text-4xl md:text-6xl font-bold mb-6">
        Professional Sports Event
        <span class="block text-blue-200">Ticket Monitoring Platform</span>
      </h1>
      <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
        Monitor {{ $stats['platforms'] ?? '50+' }} ticket platforms in real-time with automated purchasing,
        intelligent price alerts, and enterprise-grade security. Join {{ $stats['users'] ?? '15K+' }} active users.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
          Start {{ $pricing['free_trial_days'] ?? 7 }}-Day Free Trial
        </a>
        <a href="#features" class="btn btn-secondary btn-lg">
          Explore Features
        </a>
      </div>

      <!-- Live Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
        <div class="text-center">
          <div class="text-2xl md:text-3xl font-bold" data-stat="platforms">{{ $stats['platforms'] ?? '50+' }}</div>
          <div class="text-sm text-blue-200">Platforms Monitored</div>
        </div>
        <div class="text-center">
          <div class="text-2xl md:text-3xl font-bold" data-stat="tickets_tracked">
            {{ $stats['tickets_tracked'] ?? '5M+' }}</div>
          <div class="text-sm text-blue-200">Tickets Tracked</div>
        </div>
        <div class="text-center">
          <div class="text-2xl md:text-3xl font-bold" data-stat="monitoring">{{ $stats['monitoring'] ?? '24/7' }}
          </div>
          <div class="text-sm text-blue-200">Real-time Monitoring</div>
        </div>
        <div class="text-center">
          <div class="text-2xl md:text-3xl font-bold" data-stat="success_rate">
            {{ $stats['success_rate'] ?? '99.5%' }}</div>
          <div class="text-sm text-blue-200">Success Rate</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Core Backend Features Section -->
<section id="features" class="py-20 bg-gray-50 dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Enterprise-Grade Backend Features
      </h2>
      <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Our powerful backend infrastructure provides the robust foundation your business needs.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      @if (isset($features['role_based_access']))
        <!-- Role-Based Access Control -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 mb-4">
            <span class="text-2xl">👥</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $features['role_based_access']['title'] }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $features['role_based_access']['description'] }}
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            @foreach ($features['role_based_access']['features'] as $feature)
              <li class="mb-1">• {{ $feature }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (isset($features['subscription_system']))
        <!-- Subscription System -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 mb-4">
            <span class="text-2xl">💳</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $features['subscription_system']['title'] }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $features['subscription_system']['description'] }}
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            @foreach ($features['subscription_system']['features'] as $feature)
              <li class="mb-1">• {{ $feature }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (isset($features['enhanced_security']))
        <!-- Enhanced Security -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 dark:bg-red-900 mb-4">
            <span class="text-2xl">🔒</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $features['enhanced_security']['title'] }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $features['enhanced_security']['description'] }}
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            @foreach ($features['enhanced_security']['features'] as $feature)
              <li class="mb-1">• {{ $feature }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (isset($features['legal_compliance']))
        <!-- Legal Compliance -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900 mb-4">
            <span class="text-2xl">⚖️</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $features['legal_compliance']['title'] }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $features['legal_compliance']['description'] }}
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            @foreach ($features['legal_compliance']['features'] as $feature)
              <li class="mb-1">• {{ $feature }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (isset($features['monitoring_automation']))
        <!-- Monitoring & Automation -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 dark:bg-yellow-900 mb-4">
            <span class="text-2xl">📊</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $features['monitoring_automation']['title'] }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $features['monitoring_automation']['description'] }}
          </p>
          <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
            @foreach ($features['monitoring_automation']['features'] as $feature)
              <li class="mb-1">• {{ $feature }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Advanced Analytics -->
      <div class="card text-center">
        <div
          class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 mb-4">
          <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Advanced Analytics & Reporting</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Comprehensive insights into ticket prices, market trends, and purchase patterns.
        </p>
        <ul class="text-sm text-gray-500 dark:text-gray-400 text-left">
          <li class="mb-1">• Real-time price volatility analysis</li>
          <li class="mb-1">• Historical price tracking</li>
          <li class="mb-1">• Market trend predictions</li>
          <li class="mb-1">• Custom reporting dashboards</li>
        </ul>
      </div>
    </div>
  </div>
</section>
Comprehensive reporting and insights to optimize your support operations.
</p>
</div>

<!-- Feature 5 -->
<div class="card text-center">
  <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 dark:bg-red-900 mb-4">
    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
  </div>
  <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Mobile Ready</h3>
  <p class="text-gray-600 dark:text-gray-400">
    Full mobile support so your team can stay productive on any device.
  </p>
</div>

<!-- Feature 6 -->
<div class="card text-center">
  <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 mb-4">
    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
      stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  </div>
  <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Easy Integration</h3>
  <p class="text-gray-600 dark:text-gray-400">
    Connect with your favorite tools and services seamlessly.
  </p>
</div>
</div>
</div>
</section>

<!-- Legal Compliance & Trust Section -->
<section class="py-20 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Legal Compliance & Trust
      </h2>
      <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Our platform is built with comprehensive legal compliance and transparent policies.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      @if (isset($legal_docs['terms_of_service']))
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 mb-4">
            <span class="text-2xl">📋</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ $legal_docs['terms_of_service']['title'] ?? 'Terms of Service' }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $legal_docs['terms_of_service']['description'] ?? 'Service conditions and user obligations' }}
          </p>
          <a href="{{ $legal_docs['terms_of_service']['url'] ?? '#' }}"
            class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
            Read Document →
          </a>
        </div>
      @endif

      @if (isset($legal_docs['privacy_policy']))
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 mb-4">
            <span class="text-2xl">🔒</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ $legal_docs['privacy_policy']['title'] ?? 'Privacy Policy' }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $legal_docs['privacy_policy']['description'] ?? 'Data collection and privacy practices' }}
          </p>
          <a href="{{ $legal_docs['privacy_policy']['url'] ?? '#' }}"
            class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
            Read Document →
          </a>
        </div>
      @endif

      @if (isset($legal_docs['service_disclaimer']))
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 dark:bg-yellow-900 mb-4">
            <span class="text-2xl">⚠️</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ $legal_docs['service_disclaimer']['title'] ?? 'Service Disclaimer' }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $legal_docs['service_disclaimer']['description'] ?? 'Service limitations and warranty disclaimers' }}
          </p>
          <a href="{{ $legal_docs['service_disclaimer']['url'] ?? '#' }}"
            class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
            Read Document →
          </a>
        </div>
      @endif
    </div>

    <!-- GDPR Compliance Notice -->
    <div class="mt-12 text-center">
      <div class="inline-flex items-center px-6 py-3 bg-green-100 dark:bg-green-900 rounded-lg">
        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd" />
        </svg>
        <span class="text-green-800 dark:text-green-200 font-medium">GDPR Compliant & Privacy by Design</span>
      </div>
    </div>
  </div>
</section>

<!-- Platform Integration Showcase -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Comprehensive Platform Integration
      </h2>
      <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Monitor tickets across all major sports and entertainment platforms with our advanced scraping technology.
      </p>
    </div>

    <!-- Platform Categories -->
    <div class="grid md:grid-cols-3 gap-8 mb-12">
      <div class="card text-center">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Sports Platforms</h3>
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
          <div>Ticketmaster • StubHub • SeatGeek</div>
          <div>Vivid Seats • Gametime • TickPick</div>
          <div>TicketCity • Barry's Tickets • More...</div>
        </div>
      </div>

      <div class="card text-center">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Entertainment Venues</h3>
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
          <div>Concerts • Theater • Comedy Shows</div>
          <div>Music Festivals • Opera • Dance</div>
          <div>Art Exhibitions • Special Events</div>
        </div>
      </div>

      <div class="card text-center">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Secondary Markets</h3>
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
          <div>Team Official Sites • Venue Sites</div>
          <div>Season Ticket Exchanges</div>
          <div>Local & Regional Platforms</div>
        </div>
      </div>
    </div>

    <!-- Real-time Monitoring Features -->
    <div class="text-center">
      <div class="inline-flex items-center space-x-6 text-sm text-gray-600 dark:text-gray-400">
        <span class="flex items-center">
          <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
          Real-time Price Updates
        </span>
        <span class="flex items-center">
          <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
          Availability Alerts
        </span>
        <span class="flex items-center">
          <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
          Price Drop Notifications
        </span>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
  <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl md:text-4xl font-bold mb-4">
      Ready to Start Monitoring?
    </h2>
    <p class="text-xl text-blue-100 mb-8">
      Join {{ $stats['users'] ?? '15,000+' }} users already saving money and time with hdTickets.
      Start your {{ $pricing['free_trial_days'] ?? 7 }}-day free trial today.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
      <a href="{{ route('register') }}" class="btn btn-primary btn-lg bg-white text-blue-600 hover:bg-gray-100">
        Start Free Trial Now
      </a>
      <a href="{{ route('login') }}"
        class="btn btn-secondary btn-lg border-white text-white hover:bg-white hover:text-blue-600">
        Sign In to Dashboard
      </a>
    </div>

    <!-- Trust Indicators -->
    <div class="flex flex-wrap justify-center items-center gap-6 text-sm text-blue-200">
      <span>✓ No setup fees</span>
      <span>✓ Cancel anytime</span>
      <span>✓ {{ $pricing['free_trial_days'] ?? 7 }} days free</span>
      <span>✓ Secure payments</span>
    </div>
  </div>
</section>

@push('styles')
  <style>
    .card {
      @apply bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-1;
    }

    .btn {
      @apply px-6 py-3 rounded-lg font-semibold text-center transition-all duration-300 inline-block;
    }

    .btn-primary {
      @apply bg-blue-600 text-white hover:bg-blue-700 shadow-lg hover:shadow-xl;
    }

    .btn-secondary {
      @apply bg-transparent border-2 border-white text-white hover:bg-white hover:text-blue-600;
    }

    .btn-lg {
      @apply px-8 py-4 text-lg;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card {
      animation: fadeInUp 0.6s ease-out;
    }
  </style>
@endpush

@push('scripts')
  <!-- Welcome Page JavaScript -->
  @vite(['resources/js/welcome.js'])

  <script>
    // Additional inline functionality for immediate page enhancements
    document.addEventListener('DOMContentLoaded', function() {
      // Add loading states for CTA buttons
      document.querySelectorAll('.btn-primary, .btn-secondary').forEach(button => {
        button.addEventListener('click', function(e) {
          if (this.href && !this.href.startsWith('#')) {
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';

            const originalText = this.textContent;
            this.textContent = 'Loading...';

            // Reset after 3 seconds as fallback
            setTimeout(() => {
              this.textContent = originalText;
              this.style.opacity = '1';
              this.style.pointerEvents = 'auto';
            }, 3000);
          }
        });
      });

      // Enhance platform integration showcase with dynamic content
      const platformStatus = document.querySelectorAll('[class*="animate-pulse"]');
      platformStatus.forEach((indicator, index) => {
        setInterval(() => {
          indicator.style.opacity = indicator.style.opacity === '0.5' ? '1' : '0.5';
        }, 1000 + (index * 200));
      });

      // Add intersection observer for progressive enhancement
      if ('IntersectionObserver' in window) {
        const sections = document.querySelectorAll('section');
        const sectionObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('section-visible');
            }
          });
        }, {
          threshold: 0.1
        });

        sections.forEach(section => sectionObserver.observe(section));
      }
    });
  </script>
@endpush
