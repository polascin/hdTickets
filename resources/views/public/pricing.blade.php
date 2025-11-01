@extends('layouts.marketing')

@section('title', 'Pricing - HD Tickets')
@section('meta_description', 'Simple, transparent pricing for HD Tickets. Choose the plan that fits your needs. Free plan available with no credit card required.')

@section('content')
{{-- Hero Section --}}
<section class="py-16 bg-gradient-to-br from-gray-50 to-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
      Simple, Transparent Pricing
    </h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
      Choose the plan that fits your needs. Upgrade, downgrade, or cancel anytime.
    </p>
  </div>
</section>

{{-- Pricing Cards --}}
<section class="py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
      
      {{-- Free Plan --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex flex-col">
        <div class="mb-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Free</h3>
          <div class="flex items-baseline mb-4">
            <span class="text-5xl font-bold text-gray-900">£0</span>
            <span class="text-gray-600 ml-2">/month</span>
          </div>
          <p class="text-gray-600">Perfect for casual fans trying out the service</p>
        </div>
        
        <ul class="space-y-4 mb-8 flex-grow">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Monitor up to <strong>3 events</strong></span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Email alerts</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">20+ platforms monitored</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Price tracking</span>
          </li>
        </ul>
        
        <a href="{{ route('register') }}" class="w-full py-3 px-6 text-center rounded-lg font-semibold bg-gray-100 text-gray-900 hover:bg-gray-200 transition">
          Get Started Free
        </a>
      </div>
      
      {{-- Standard Plan --}}
      <div class="bg-white rounded-2xl shadow-lg border-2 border-emerald-500 p-8 flex flex-col relative">
        <div class="absolute top-0 right-0 bg-emerald-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
          Popular
        </div>
        
        <div class="mb-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Standard</h3>
          <div class="flex items-baseline mb-4">
            <span class="text-5xl font-bold text-gray-900">£9.99</span>
            <span class="text-gray-600 ml-2">/month</span>
          </div>
          <p class="text-gray-600">For dedicated fans following multiple teams</p>
        </div>
        
        <ul class="space-y-4 mb-8 flex-grow">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Monitor up to <strong>25 events</strong></span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Email + SMS alerts</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">40+ platforms monitored</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Advanced price alerts</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Priority support</span>
          </li>
        </ul>
        
        <a href="{{ route('register') }}" class="w-full py-3 px-6 text-center rounded-lg font-semibold bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-700 hover:to-teal-700 transition shadow-md">
          Start Free Trial
        </a>
      </div>
      
      {{-- Pro Plan --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex flex-col">
        <div class="mb-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Pro</h3>
          <div class="flex items-baseline mb-4">
            <span class="text-5xl font-bold text-gray-900">£24.99</span>
            <span class="text-gray-600 ml-2">/month</span>
          </div>
          <p class="text-gray-600">For power users who need automation</p>
        </div>
        
        <ul class="space-y-4 mb-8 flex-grow">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700"><strong>Unlimited</strong> event monitoring</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">All alert channels + Push</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">40+ platforms + VIP access</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-purple-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700"><strong>Automated purchasing</strong></span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Advanced analytics</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-700">Dedicated support</span>
          </li>
        </ul>
        
        <a href="{{ route('register') }}" class="w-full py-3 px-6 text-center rounded-lg font-semibold bg-gray-900 text-white hover:bg-gray-800 transition">
          Start Free Trial
        </a>
      </div>
    </div>
    
    {{-- Small Print --}}
    <div class="mt-12 text-center">
      <p class="text-sm text-gray-600">
        All plans include a 14-day free trial. No credit card required. Cancel anytime.
      </p>
    </div>
  </div>
</section>

{{-- Comparison Table --}}
<section class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Feature Comparison</h2>
    
    <div class="overflow-x-auto">
      <table class="w-full bg-white rounded-lg shadow-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Feature</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Free</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Standard</th>
            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Pro</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr>
            <td class="px-6 py-4 text-sm text-gray-700">Events monitored</td>
            <td class="px-6 py-4 text-sm text-gray-700 text-center">3</td>
            <td class="px-6 py-4 text-sm text-gray-700 text-center">25</td>
            <td class="px-6 py-4 text-sm text-gray-700 text-center">Unlimited</td>
          </tr>
          <tr class="bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-700">Email alerts</td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
          </tr>
          <tr>
            <td class="px-6 py-4 text-sm text-gray-700">SMS alerts</td>
            <td class="px-6 py-4 text-center"><span class="text-gray-400">—</span></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
          </tr>
          <tr class="bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-700">Push notifications</td>
            <td class="px-6 py-4 text-center"><span class="text-gray-400">—</span></td>
            <td class="px-6 py-4 text-center"><span class="text-gray-400">—</span></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
          </tr>
          <tr>
            <td class="px-6 py-4 text-sm text-gray-700">Automated purchasing</td>
            <td class="px-6 py-4 text-center"><span class="text-gray-400">—</span></td>
            <td class="px-6 py-4 text-center"><span class="text-gray-400">—</span></td>
            <td class="px-6 py-4 text-center"><svg class="w-5 h-5 text-emerald-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

{{-- Pricing FAQs --}}
<section class="py-16">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Pricing FAQs</h2>
    
    <div class="space-y-6" x-data="{ openFaq: null }">
      {{-- FAQ 1 --}}
      <div class="border border-gray-200 rounded-lg">
        <button 
          @click="openFaq = openFaq === 1 ? null : 1"
          class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
          :aria-expanded="(openFaq === 1).toString()">
          <span class="font-semibold text-gray-900">How does the free trial work?</span>
          <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div x-show="openFaq === 1" x-collapse class="px-6 pb-4">
          <p class="text-gray-600">You get 14 days to try any paid plan completely free. No credit card required. If you don't upgrade after the trial, you'll automatically move to the free plan.</p>
        </div>
      </div>
      
      {{-- FAQ 2 --}}
      <div class="border border-gray-200 rounded-lg">
        <button 
          @click="openFaq = openFaq === 2 ? null : 2"
          class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
          :aria-expanded="(openFaq === 2).toString()">
          <span class="font-semibold text-gray-900">Can I change plans later?</span>
          <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div x-show="openFaq === 2" x-collapse class="px-6 pb-4">
          <p class="text-gray-600">Yes! You can upgrade, downgrade, or cancel your plan at any time from your account settings. Changes take effect immediately.</p>
        </div>
      </div>
      
      {{-- FAQ 3 --}}
      <div class="border border-gray-200 rounded-lg">
        <button 
          @click="openFaq = openFaq === 3 ? null : 3"
          class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
          :aria-expanded="(openFaq === 3).toString()">
          <span class="font-semibold text-gray-900">What payment methods do you accept?</span>
          <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div x-show="openFaq === 3" x-collapse class="px-6 pb-4">
          <p class="text-gray-600">We accept all major credit and debit cards (Visa, Mastercard, American Express) as well as PayPal. All payments are processed securely through Stripe.</p>
        </div>
      </div>
      
      {{-- FAQ 4 --}}
      <div class="border border-gray-200 rounded-lg">
        <button 
          @click="openFaq = openFaq === 4 ? null : 4"
          class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
          :aria-expanded="(openFaq === 4).toString()">
          <span class="font-semibold text-gray-900">Is there a refund policy?</span>
          <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div x-show="openFaq === 4" x-collapse class="px-6 pb-4">
          <p class="text-gray-600">Yes, we offer a 30-day money-back guarantee. If you're not satisfied with HD Tickets, contact support within 30 days of your purchase for a full refund.</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- CTA Section --}}
<section class="py-16 bg-gradient-to-r from-emerald-600 to-teal-600">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to Get Started?</h2>
    <p class="text-xl text-white/90 mb-8">Start your 14-day free trial today. No credit card required.</p>
    <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-emerald-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg">
      Start Free Trial
    </a>
  </div>
</section>
@endsection
