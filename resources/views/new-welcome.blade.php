@extends('layouts.guest-v3')

@section('title', 'HD Tickets - Modern Help Desk Solution')

@section('content')
  <!-- Hero Section -->
  <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          Modern Help Desk
          <span class="block text-blue-200">Made Simple</span>
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
          Streamline your customer support with our powerful, intuitive help desk platform.
          Built for teams that demand excellence.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
            Start Free Trial
          </a>
          <a href="#features" class="btn btn-secondary btn-lg">
            Learn More
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Everything you need to succeed
        </h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
          Powerful features designed to help your team deliver exceptional customer support.
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Lightning Fast</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Optimized performance ensures your team can work efficiently without delays.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="card text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900 mb-4">
            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Reliable & Secure</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Enterprise-grade security with 99.9% uptime guarantee.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900 mb-4">
            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Team Collaboration</h3>
          <p class="text-gray-600 dark:text-gray-400">
            Built-in tools for seamless team communication and knowledge sharing.
          </p>
        </div>

        <!-- Feature 4 -->
        <div class="card text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 dark:bg-yellow-900 mb-4">
            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Advanced Analytics</h3>
          <p class="text-gray-600 dark:text-gray-400">
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
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 mb-4">
            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
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

  <!-- CTA Section -->
  <section class="py-20 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">
        Ready to transform your support?
      </h2>
      <p class="text-xl text-blue-100 mb-8">
        Join thousands of teams already using HD Tickets to deliver exceptional customer experiences.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
          Start Your Free Trial
        </a>
        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
          Sign In
        </a>
      </div>
    </div>
  </section>
@endsection
