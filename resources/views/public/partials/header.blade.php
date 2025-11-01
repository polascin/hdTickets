<nav class="glass-nav" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      {{-- Logo --}}
      <div class="flex items-center flex-shrink-0">
        <a href="{{ route('public.home') }}" class="flex items-center group" aria-label="HD Tickets Home">
          <picture>
            <source srcset="{{ asset('assets/branding/hdTicketsLogo.webp') }}" type="image/webp">
            <img 
              src="{{ asset('assets/branding/hdTicketsLogo.png') }}" 
              alt="HD Tickets" 
              class="h-10 w-10 transition-transform group-hover:scale-105" 
              width="40" 
              height="40"
              loading="eager"
              fetchpriority="high">
          </picture>
          <span class="ml-3 text-xl font-bold gradient-text-emerald">HD Tickets</span>
        </a>
      </div>
      
      {{-- Desktop Navigation --}}
      <nav class="hidden md:flex items-center space-x-8" role="navigation" aria-label="Main navigation">
        <a href="{{ route('public.home') }}" 
           class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors {{ request()->routeIs('public.home') ? 'text-emerald-600' : '' }}">
          Home
        </a>
        <a href="{{ route('public.pricing') }}" 
           class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors {{ request()->routeIs('public.pricing') ? 'text-emerald-600' : '' }}">
          Pricing
        </a>
        <a href="{{ route('public.coverage') }}" 
           class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors {{ request()->routeIs('public.coverage') ? 'text-emerald-600' : '' }}">
          Coverage
        </a>
        <a href="{{ route('public.faqs') }}" 
           class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors {{ request()->routeIs('public.faqs') ? 'text-emerald-600' : '' }}">
          FAQs
        </a>
        <a href="{{ route('tickets.main') }}" 
           class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors">
          Browse Tickets
        </a>
      </nav>
      
      {{-- Auth Buttons - Desktop --}}
      <div class="hidden md:flex items-center space-x-4">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-marketing-secondary text-sm">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" class="btn-marketing-secondary text-sm">
            Sign In
          </a>
          <a href="{{ route('register') }}" class="btn-marketing-primary text-sm">
            Get Started
          </a>
        @endguest
      </div>
      
      {{-- Mobile Menu Button --}}
      <button 
        @click="mobileOpen = !mobileOpen" 
        type="button"
        class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors"
        :aria-expanded="mobileOpen.toString()"
        aria-label="Toggle navigation menu">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    {{-- Mobile Menu --}}
    <div 
      x-show="mobileOpen" 
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="md:hidden pb-4 border-t border-gray-200 mt-2"
      @click.away="mobileOpen = false">
      <div class="space-y-1 pt-2">
        <a href="{{ route('public.home') }}" 
           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-emerald-600 hover:bg-gray-50 rounded-lg transition-colors">
          Home
        </a>
        <a href="{{ route('public.pricing') }}" 
           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-emerald-600 hover:bg-gray-50 rounded-lg transition-colors">
          Pricing
        </a>
        <a href="{{ route('public.coverage') }}" 
           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-emerald-600 hover:bg-gray-50 rounded-lg transition-colors">
          Coverage
        </a>
        <a href="{{ route('public.faqs') }}" 
           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-emerald-600 hover:bg-gray-50 rounded-lg transition-colors">
          FAQs
        </a>
        <a href="{{ route('tickets.main') }}" 
           class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-emerald-600 hover:bg-gray-50 rounded-lg transition-colors">
          Browse Tickets
        </a>
        
        <div class="border-t border-gray-200 pt-4 mt-4 space-y-2">
          @auth
            <a href="{{ route('dashboard') }}" class="block w-full btn-marketing-secondary text-center">
              Dashboard
            </a>
          @else
            <a href="{{ route('login') }}" class="block w-full btn-marketing-secondary text-center">
              Sign In
            </a>
            <a href="{{ route('register') }}" class="block w-full btn-marketing-primary text-center">
              Get Started
            </a>
          @endguest
        </div>
      </div>
    </div>
  </div>
</nav>
