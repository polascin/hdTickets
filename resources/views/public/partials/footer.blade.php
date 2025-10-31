<footer class="footer-enhanced text-white py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
      {{-- Brand Section --}}
      <div>
        <div class="flex items-center mb-4">
          <img 
            src="{{ asset('assets/branding/hdTicketsLogo.png') }}" 
            alt="HD Tickets" 
            class="h-10 w-10 brightness-0 invert opacity-90"
            width="40"
            height="40">
          <span class="ml-3 text-xl font-bold">HD Tickets</span>
        </div>
        <p class="text-gray-300 text-sm leading-relaxed mb-4">
          Smart sports ticket monitoring for fans who never want to miss a match. Track prices, get alerts, and secure the best seats.
        </p>
      </div>
      
      {{-- Product Links --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Product</h3>
        <ul class="space-y-2 text-sm">
          <li>
            <a href="{{ route('public.pricing') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Pricing
            </a>
          </li>
          <li>
            <a href="{{ route('public.coverage') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Coverage
            </a>
          </li>
          <li>
            <a href="{{ route('tickets.main') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Browse Tickets
            </a>
          </li>
          <li>
            <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Dashboard
            </a>
          </li>
        </ul>
      </div>
      
      {{-- Support Links --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Support</h3>
        <ul class="space-y-2 text-sm">
          <li>
            <a href="{{ route('public.faqs') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              FAQs
            </a>
          </li>
          <li>
            <a href="mailto:support@hdtickets.com" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Contact Support
            </a>
          </li>
          <li>
            <a href="{{ route('health.index') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              System Status
            </a>
          </li>
        </ul>
      </div>
      
      {{-- Legal Links --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Legal</h3>
        <ul class="space-y-2 text-sm">
          <li>
            <a href="{{ route('legal.terms-of-service') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Terms of Service
            </a>
          </li>
          <li>
            <a href="{{ route('legal.privacy-policy') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Privacy Policy
            </a>
          </li>
          <li>
            <a href="{{ route('legal.cookie-policy') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Cookie Policy
            </a>
          </li>
          <li>
            <a href="{{ route('legal.gdpr-compliance') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              GDPR Compliance
            </a>
          </li>
          <li>
            <a href="{{ route('legal.disclaimer') }}" class="text-gray-300 hover:text-white transition-colors hover:underline">
              Disclaimer
            </a>
          </li>
        </ul>
      </div>
    </div>
    
    {{-- Coverage Information --}}
    <div class="border-t border-gray-700 pt-8 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Supported Platforms --}}
        <div>
          <h4 class="text-lg font-semibold mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
            Supported Platforms
          </h4>
          <div class="grid grid-cols-2 gap-2">
            @foreach(['Ticketmaster UK', 'StubHub UK', 'See Tickets', 'Viagogo', 'Eventim UK', 'Live Nation UK', 'Official Club Sites', 'UEFA.com'] as $platform)
              <div class="text-sm text-gray-300 bg-white/10 px-3 py-2 rounded-lg hover:bg-white/20 transition-colors">
                {{ $platform }}
              </div>
            @endforeach
          </div>
        </div>
        
        {{-- Monitored Sports --}}
        <div>
          <h4 class="text-lg font-semibold mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            Monitored Sports
          </h4>
          <div class="grid grid-cols-2 gap-2">
            @foreach(['Premier League', 'Champions League', 'FA Cup', 'Europa League', 'Rugby Union', 'Cricket Tests', 'Formula 1', 'Tennis Grand Slams'] as $sport)
              <div class="text-sm text-gray-300 bg-white/10 px-3 py-2 rounded-lg hover:bg-white/20 transition-colors">
                {{ $sport }}
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    
    {{-- Bottom Bar --}}
    <div class="border-t border-gray-700 pt-8 text-center text-sm text-gray-400">
      <p class="mb-2">&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
      <p class="text-xs">
        HD Tickets is an independent ticket monitoring service. We are not affiliated with any ticket sellers or sports organisations.
      </p>
    </div>
  </div>
</footer>
