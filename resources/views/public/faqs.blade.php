@extends('layouts.marketing')

@section('title', 'FAQs - HD Tickets')
@section('meta_description', 'Frequently asked questions about HD Tickets. Learn about ticket monitoring, pricing, alerts, automation, and more.')

@section('content')
{{-- Hero Section --}}
<section class="py-16 bg-gradient-to-br from-gray-50 to-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
      Frequently Asked Questions
    </h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
      Everything you need to know about HD Tickets
    </p>
  </div>
</section>

{{-- FAQs Content --}}
<section class="py-16">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    {{-- General Questions --}}
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">General</h2>
      <div class="space-y-4" x-data="{ openFaq: null }">
        
        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'g1' ? null : 'g1'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'g1').toString()"
            aria-controls="faq-g1">
            <span class="font-semibold text-gray-900">What is HD Tickets?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'g1' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'g1'" x-collapse id="faq-g1" role="region" aria-labelledby="faq-g1-button" class="px-6 pb-4">
            <p class="text-gray-600">HD Tickets is a comprehensive sports events entry tickets monitoring, scraping and purchase system. We monitor ticket availability across 40+ platforms including Ticketmaster, StubHub, UEFA, and official club stores. You'll receive instant alerts when tickets become available for your favourite teams and events.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'g2' ? null : 'g2'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'g2').toString()"
            aria-controls="faq-g2">
            <span class="font-semibold text-gray-900">How does HD Tickets work?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'g2' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'g2'" x-collapse id="faq-g2" class="px-6 pb-4">
            <p class="text-gray-600 mb-2">HD Tickets works in three simple steps:</p>
            <ol class="list-decimal list-inside text-gray-600 space-y-1 ml-2">
              <li>Set your preferences - choose your favourite teams, venues, and budget</li>
              <li>Get instant alerts - receive notifications when tickets matching your criteria become available</li>
              <li>Secure your tickets - purchase through official platforms or use our automated purchasing (Pro plan)</li>
            </ol>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'g3' ? null : 'g3'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'g3').toString()"
            aria-controls="faq-g3">
            <span class="font-semibold text-gray-900">Is HD Tickets a ticket reseller?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'g3' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'g3'" x-collapse id="faq-g3" class="px-6 pb-4">
            <p class="text-gray-600">No, HD Tickets is not a ticket reseller. We are a monitoring and alert service that helps you find tickets across multiple platforms. You purchase tickets directly from verified platforms like Ticketmaster, club stores, and other official sources. We simply make it easier to find and track ticket availability.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'g4' ? null : 'g4'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'g4').toString()"
            aria-controls="faq-g4">
            <span class="font-semibold text-gray-900">Which sports and leagues do you cover?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'g4' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'g4'" x-collapse id="faq-g4" class="px-6 pb-4">
            <p class="text-gray-600 mb-2">We cover a wide range of sports including:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 ml-2">
              <li>Football: Premier League, Champions League, La Liga, Serie A, Bundesliga, and more</li>
              <li>Rugby: Six Nations, World Cup, Premiership</li>
              <li>Cricket: The Ashes, World Cup, International Tests</li>
              <li>Tennis: Wimbledon, Grand Slams</li>
              <li>And many more sports and competitions</li>
            </ul>
            <p class="text-gray-600 mt-2">See our <a href="{{ route('public.coverage') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">full coverage page</a> for complete details.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Pricing & Plans --}}
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Pricing & Plans</h2>
      <div class="space-y-4" x-data="{ openFaq: null }">
        
        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'p1' ? null : 'p1'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'p1').toString()"
            aria-controls="faq-p1">
            <span class="font-semibold text-gray-900">How much does HD Tickets cost?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'p1' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'p1'" x-collapse id="faq-p1" class="px-6 pb-4">
            <p class="text-gray-600 mb-2">We offer three plans:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 ml-2">
              <li><strong>Free:</strong> £0/month - Monitor up to 3 events with email alerts</li>
              <li><strong>Standard:</strong> £9.99/month - Monitor up to 25 events with email and SMS alerts</li>
              <li><strong>Pro:</strong> £24.99/month - Unlimited events, all alert channels, and automated purchasing</li>
            </ul>
            <p class="text-gray-600 mt-2">All paid plans include a 14-day free trial. <a href="{{ route('public.pricing') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">View full pricing details</a>.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'p2' ? null : 'p2'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'p2').toString()"
            aria-controls="faq-p2">
            <span class="font-semibold text-gray-900">Is there a free plan?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'p2' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'p2'" x-collapse id="faq-p2" class="px-6 pb-4">
            <p class="text-gray-600">Yes! Our free plan lets you monitor up to 3 events with email alerts across 20+ platforms. It's perfect for casual fans who want to try the service. No credit card required to sign up.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'p3' ? null : 'p3'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'p3').toString()"
            aria-controls="faq-p3">
            <span class="font-semibold text-gray-900">Can I cancel anytime?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'p3' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'p3'" x-collapse id="faq-p3" class="px-6 pb-4">
            <p class="text-gray-600">Yes, you can cancel your subscription at any time from your account settings. There are no cancellation fees or penalties. If you cancel, you'll continue to have access until the end of your billing period, then you'll be moved to the free plan.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Alerts & Notifications --}}
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Alerts & Notifications</h2>
      <div class="space-y-4" x-data="{ openFaq: null }">
        
        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'a1' ? null : 'a1'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'a1').toString()"
            aria-controls="faq-a1">
            <span class="font-semibold text-gray-900">How fast are the alerts?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'a1' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'a1'" x-collapse id="faq-a1" class="px-6 pb-4">
            <p class="text-gray-600">Our system monitors platforms 24/7 and sends alerts within seconds of detecting new ticket availability. Email alerts typically arrive within 1-2 minutes, SMS within 30 seconds, and push notifications (Pro plan) are nearly instant.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'a2' ? null : 'a2'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'a2').toString()"
            aria-controls="faq-a2">
            <span class="font-semibold text-gray-900">What types of alerts can I receive?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'a2' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'a2'" x-collapse id="faq-a2" class="px-6 pb-4">
            <p class="text-gray-600 mb-2">You can receive alerts via:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-1 ml-2">
              <li><strong>Email</strong> - Available on all plans</li>
              <li><strong>SMS</strong> - Available on Standard and Pro plans</li>
              <li><strong>Push notifications</strong> - Available on Pro plan only</li>
            </ul>
            <p class="text-gray-600 mt-2">You can customise which types of alerts you receive and set quiet hours in your account settings.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 'a3' ? null : 'a3'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 'a3').toString()"
            aria-controls="faq-a3">
            <span class="font-semibold text-gray-900">Can I set price alerts?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 'a3' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 'a3'" x-collapse id="faq-a3" class="px-6 pb-4">
            <p class="text-gray-600">Yes! You can set maximum price thresholds for each event you're monitoring. You'll only receive alerts when tickets are available within your budget. Standard and Pro plans also get advanced price drop alerts when tickets decrease in price.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Technical Questions --}}
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Technical Questions</h2>
      <div class="space-y-4" x-data="{ openFaq: null }">
        
        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 't1' ? null : 't1'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 't1').toString()"
            aria-controls="faq-t1">
            <span class="font-semibold text-gray-900">How does automated purchasing work?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 't1' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 't1'" x-collapse id="faq-t1" class="px-6 pb-4">
            <p class="text-gray-600">Our Pro plan includes automated purchasing (available for select platforms). You provide your payment and delivery details once, then our system can automatically complete purchases when tickets matching your criteria become available. You maintain full control and can set spending limits and approval requirements.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 't2' ? null : 't2'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 't2').toString()"
            aria-controls="faq-t2">
            <span class="font-semibold text-gray-900">Is my payment information secure?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 't2' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 't2'" x-collapse id="faq-t2" class="px-6 pb-4">
            <p class="text-gray-600">Yes. All payment information is encrypted and processed securely through Stripe, a PCI-compliant payment processor. We never store your full payment card details on our servers. For automated purchasing, credentials are encrypted and stored in compliance with industry standards.</p>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white">
          <button 
            @click="openFaq = openFaq === 't3' ? null : 't3'"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition rounded-lg"
            :aria-expanded="(openFaq === 't3').toString()"
            aria-controls="faq-t3">
            <span class="font-semibold text-gray-900">Do you have a mobile app?</span>
            <svg class="w-5 h-5 text-gray-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': openFaq === 't3' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="openFaq === 't3'" x-collapse id="faq-t3" class="px-6 pb-4">
            <p class="text-gray-600">Currently, HD Tickets is web-based and fully responsive, working seamlessly on mobile browsers. We're developing native iOS and Android apps, which are planned for release in 2025. Pro subscribers will receive push notifications through the web app in the meantime.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

{{-- CTA Section --}}
<section class="py-16 bg-gradient-to-r from-emerald-600 to-teal-600">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Still Have Questions?</h2>
    <p class="text-xl text-white/90 mb-8">Contact our support team or start your free trial today</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-emerald-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg">
        Get Started Free
      </a>
      @if(Route::has('contact'))
      <a href="{{ route('contact') }}" class="inline-block px-8 py-4 bg-white/10 backdrop-blur-sm text-white border-2 border-white rounded-lg font-semibold text-lg hover:bg-white/20 transition">
        Contact Support
      </a>
      @endif
    </div>
  </div>
</section>
@endsection
