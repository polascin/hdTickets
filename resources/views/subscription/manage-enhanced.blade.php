<x-unified-layout title="Subscription Management" subtitle="Manage your HD Tickets subscription and billing">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Subscription Status -->
      <div class="flex items-center bg-{{ $subscription_status === 'active' ? 'green' : ($subscription_status === 'trial' ? 'blue' : 'red') }}-100 text-{{ $subscription_status === 'active' ? 'green' : ($subscription_status === 'trial' ? 'blue' : 'red') }}-800 px-3 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ ucfirst($subscription_status ?? 'inactive') }}
      </div>
      
      <a href="{{ route('billing.invoices') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
        View Invoices
      </a>
    </div>
  </x-slot>

  <div x-data="subscriptionManagement()" x-init="init()">
    <div class="max-w-6xl mx-auto space-y-8">
      
      <!-- Current Subscription Status -->
      <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-white rounded-lg p-6">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-2xl font-bold mb-2">Your HD Tickets Subscription</h2>
            <p class="text-blue-100 mb-4">Monitor sports events and purchase tickets with confidence</p>
            
            @if($subscription_status === 'trial')
              <div class="bg-white/20 rounded-lg p-4 max-w-md">
                <div class="flex items-center mb-2">
                  <svg class="w-5 h-5 text-yellow-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span class="font-medium">Free Trial Active</span>
                </div>
                <p class="text-blue-100 text-sm mb-2">
                  Your free trial expires in <span class="font-bold" x-text="trialDaysRemaining">{{ $trial_days_remaining ?? 0 }}</span> days
                </p>
                <div class="w-full bg-white/20 rounded-full h-2">
                  <div class="bg-yellow-400 h-2 rounded-full transition-all duration-300" 
                       :style="`width: ${Math.max(0, (trialDaysRemaining / 7) * 100)}%`"></div>
                </div>
              </div>
            @endif
          </div>
          
          <!-- Subscription Stats -->
          <div class="text-right">
            <div class="bg-white/20 rounded-lg p-4 min-w-[160px]">
              <p class="text-white/80 text-sm mb-1">Monthly Usage</p>
              <div class="text-3xl font-bold text-white mb-1">{{ $current_usage ?? 0 }}/{{ $monthly_limit ?? 100 }}</div>
              <div class="w-full bg-white/20 rounded-full h-2 mb-2">
                <div class="bg-white h-2 rounded-full transition-all duration-300" 
                     style="width: {{ min(100, (($current_usage ?? 0) / ($monthly_limit ?? 100)) * 100) }}%"></div>
              </div>
              <p class="text-white/70 text-xs">Tickets Available</p>
            </div>
          </div>
        </div>
      </div>

      @if($subscription_status === 'trial' || $subscription_status === 'inactive')
        <!-- Subscription Plans -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Basic Plan -->
          <x-ui.card class="relative">
            <x-ui.card-header title="Basic Plan">
              <x-ui.badge variant="info">Most Popular</x-ui.badge>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="text-center mb-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">$29.99</div>
                <div class="text-gray-600 text-sm">per month</div>
              </div>
              
              <div class="space-y-4 mb-8">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">100 tickets per month</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Email price alerts</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Basic event monitoring</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Standard support</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  <span class="text-gray-400">SMS notifications</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  <span class="text-gray-400">Priority support</span>
                </div>
              </div>
              
              <button @click="selectPlan('basic', 29.99)" 
                      :disabled="processingPayment"
                      class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span x-show="!processingPayment || selectedPlan !== 'basic'">Choose Basic</span>
                <span x-show="processingPayment && selectedPlan === 'basic'" class="flex items-center justify-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </span>
              </button>
            </x-ui.card-content>
          </x-ui.card>

          <!-- Pro Plan -->
          <x-ui.card class="relative border-2 border-purple-500 shadow-lg">
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
              <x-ui.badge variant="primary" class="px-4 py-1">Recommended</x-ui.badge>
            </div>
            <x-ui.card-header title="Pro Plan">
              <x-ui.badge variant="success">Best Value</x-ui.badge>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="text-center mb-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">$49.99</div>
                <div class="text-gray-600 text-sm">per month</div>
              </div>
              
              <div class="space-y-4 mb-8">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">500 tickets per month</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Email + SMS alerts</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Advanced event monitoring</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Priority support</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Custom price thresholds</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Analytics dashboard</span>
                </div>
              </div>
              
              <button @click="selectPlan('pro', 49.99)" 
                      :disabled="processingPayment"
                      class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span x-show="!processingPayment || selectedPlan !== 'pro'">Choose Pro</span>
                <span x-show="processingPayment && selectedPlan === 'pro'" class="flex items-center justify-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </span>
              </button>
            </x-ui.card-content>
          </x-ui.card>

          <!-- Enterprise Plan -->
          <x-ui.card class="relative">
            <x-ui.card-header title="Enterprise Plan">
              <x-ui.badge variant="warning">Custom</x-ui.badge>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="text-center mb-6">
                <div class="text-4xl font-bold text-gray-900 mb-2">Custom</div>
                <div class="text-gray-600 text-sm">contact us</div>
              </div>
              
              <div class="space-y-4 mb-8">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Unlimited tickets</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">All notification channels</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">API access</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Dedicated support</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">Custom integrations</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-gray-700">White-label options</span>
                </div>
              </div>
              
              <a href="{{ route('contact.enterprise') }}" 
                 class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-700 transition text-center block">
                Contact Sales
              </a>
            </x-ui.card-content>
          </x-ui.card>
        </div>
      @else
        <!-- Active Subscription Management -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Current Plan Details -->
          <x-ui.card>
            <x-ui.card-header title="Current Plan Details">
              <x-ui.badge variant="success" dot="true">Active</x-ui.badge>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                  <div>
                    <h3 class="text-lg font-semibold text-green-900">{{ ucfirst($current_plan ?? 'Basic') }} Plan</h3>
                    <p class="text-sm text-green-700">${{ $plan_price ?? '29.99' }}/month</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-green-600">Next billing</p>
                    <p class="font-medium text-green-900">{{ $next_billing_date ?? 'Jan 15, 2024' }}</p>
                  </div>
                </div>

                <!-- Usage Statistics -->
                <div class="space-y-4">
                  <div>
                    <div class="flex justify-between text-sm mb-1">
                      <span class="text-gray-600">Monthly Tickets</span>
                      <span class="text-gray-900 font-medium">{{ $current_usage ?? 0 }} / {{ $monthly_limit ?? 100 }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                           style="width: {{ min(100, (($current_usage ?? 0) / ($monthly_limit ?? 100)) * 100) }}%"></div>
                    </div>
                  </div>

                  <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-900">{{ $tickets_purchased ?? 0 }}</div>
                      <div class="text-xs text-gray-500">Purchased</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-900">{{ $alerts_sent ?? 0 }}</div>
                      <div class="text-xs text-gray-500">Alerts Sent</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-900">{{ $events_monitored ?? 0 }}</div>
                      <div class="text-xs text-gray-500">Events Tracked</div>
                    </div>
                  </div>
                </div>

                <!-- Plan Actions -->
                <div class="space-y-3 pt-4 border-t border-gray-200">
                  <button @click="showUpgradeModal = true" 
                          class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                    Upgrade Plan
                  </button>
                  
                  <button @click="showCancelModal = true" 
                          class="w-full text-red-600 hover:text-red-700 py-2 px-4 rounded-lg font-medium border border-red-300 hover:border-red-400 transition">
                    Cancel Subscription
                  </button>
                </div>
              </div>
            </x-ui.card-content>
          </x-ui.card>

          <!-- Billing Information -->
          <x-ui.card>
            <x-ui.card-header title="Billing Information">
              <button @click="showUpdateBillingModal = true" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Update
              </button>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="space-y-6">
                <!-- Payment Method -->
                <div class="p-4 bg-gray-50 rounded-lg">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900">Payment Method</h3>
                    <x-ui.badge variant="success" dot="true">Verified</x-ui.badge>
                  </div>
                  
                  <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center mr-3">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">•••• •••• •••• {{ $card_last_four ?? '4242' }}</p>
                      <p class="text-sm text-gray-600">Expires {{ $card_expiry ?? '12/26' }}</p>
                    </div>
                  </div>
                </div>

                <!-- Billing Address -->
                <div class="space-y-3">
                  <h3 class="font-medium text-gray-900">Billing Address</h3>
                  <div class="text-sm text-gray-600 space-y-1">
                    <p>{{ $billing_name ?? Auth::user()->name }}</p>
                    <p>{{ $billing_address ?? '123 Main St' }}</p>
                    <p>{{ $billing_city ?? 'New York' }}, {{ $billing_state ?? 'NY' }} {{ $billing_zip ?? '10001' }}</p>
                    <p>{{ $billing_country ?? 'United States' }}</p>
                  </div>
                </div>

                <!-- Recent Invoices -->
                <div class="pt-4 border-t border-gray-200">
                  <h3 class="font-medium text-gray-900 mb-3">Recent Invoices</h3>
                  <div class="space-y-2">
                    @forelse($recent_invoices ?? [] as $invoice)
                      <div class="flex items-center justify-between py-2">
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ $invoice['date'] }}</p>
                          <p class="text-xs text-gray-600">${{ $invoice['amount'] }} - {{ $invoice['status'] }}</p>
                        </div>
                        <a href="{{ $invoice['download_url'] }}" class="text-blue-600 hover:text-blue-700 text-sm">
                          Download
                        </a>
                      </div>
                    @empty
                      <div class="flex items-center justify-between py-2">
                        <div>
                          <p class="text-sm font-medium text-gray-900">Dec 15, 2024</p>
                          <p class="text-xs text-gray-600">${{ $plan_price ?? '29.99' }} - Paid</p>
                        </div>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm">
                          Download
                        </a>
                      </div>
                    @endforelse
                  </div>
                  
                  <a href="{{ route('billing.invoices') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-3 block">
                    View all invoices →
                  </a>
                </div>
              </div>
            </x-ui.card-content>
          </x-ui.card>
        </div>
      @endif

      <!-- Usage Analytics -->
      <x-ui.card>
        <x-ui.card-header title="Usage Analytics">
          <div class="flex items-center space-x-2">
            <select class="text-sm border border-gray-300 rounded px-2 py-1">
              <option>Last 30 days</option>
              <option>Last 3 months</option>
              <option>Last year</option>
            </select>
          </div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ $total_tickets_purchased ?? 0 }}</div>
              <div class="text-sm text-gray-600">Total Tickets Purchased</div>
            </div>
            
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-3xl font-bold text-green-600 mb-2">${{ number_format($money_saved ?? 0, 2) }}</div>
              <div class="text-sm text-gray-600">Money Saved</div>
            </div>
            
            <div class="text-center p-4 bg-purple-50 rounded-lg">
              <div class="text-3xl font-bold text-purple-600 mb-2">{{ $alerts_received ?? 0 }}</div>
              <div class="text-sm text-gray-600">Price Alerts Received</div>
            </div>
            
            <div class="text-center p-4 bg-orange-50 rounded-lg">
              <div class="text-3xl font-bold text-orange-600 mb-2">{{ $avg_savings_percent ?? 0 }}%</div>
              <div class="text-sm text-gray-600">Average Savings</div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Upgrade Modal -->
      <div x-show="showUpgradeModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showUpgradeModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Upgrade Your Plan</h3>
            <p class="text-sm text-gray-600">Choose a new plan to unlock more features</p>
          </div>
          <div class="px-6 py-4">
            <div class="space-y-4">
              <div class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition" 
                   @click="selectedUpgradePlan = 'pro'"
                   :class="selectedUpgradePlan === 'pro' ? 'border-blue-500 bg-blue-50' : ''">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium text-gray-900">Pro Plan</h4>
                    <p class="text-sm text-gray-600">500 tickets/month + SMS alerts</p>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-gray-900">$49.99/mo</div>
                    <div class="text-xs text-green-600">+$20/mo</div>
                  </div>
                </div>
              </div>
              
              <div class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition" 
                   @click="selectedUpgradePlan = 'enterprise'"
                   :class="selectedUpgradePlan === 'enterprise' ? 'border-blue-500 bg-blue-50' : ''">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium text-gray-900">Enterprise Plan</h4>
                    <p class="text-sm text-gray-600">Unlimited tickets + API access</p>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-gray-900">Custom</div>
                    <div class="text-xs text-blue-600">Contact us</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
            <button @click="showUpgradeModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
              Cancel
            </button>
            <button @click="upgradePlan()" 
                    :disabled="!selectedUpgradePlan || upgradingPlan"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
              <span x-show="!upgradingPlan">Upgrade Now</span>
              <span x-show="upgradingPlan" class="flex items-center">
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Cancel Modal -->
      <div x-show="showCancelModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showCancelModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Cancel Subscription</h3>
            <p class="text-sm text-gray-600">Are you sure you want to cancel your subscription?</p>
          </div>
          <div class="px-6 py-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
              <h4 class="font-medium text-red-900 mb-2">What happens when you cancel:</h4>
              <ul class="text-sm text-red-800 space-y-1">
                <li>• Your subscription will remain active until {{ $next_billing_date ?? 'Jan 15, 2024' }}</li>
                <li>• You'll lose access to premium features after that date</li>
                <li>• Your data will be preserved for 30 days</li>
                <li>• You can reactivate anytime</li>
              </ul>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Reason for cancelling (optional)
              </label>
              <select x-model="cancellationReason" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Select a reason...</option>
                <option value="too_expensive">Too expensive</option>
                <option value="not_using">Not using enough</option>
                <option value="missing_features">Missing features</option>
                <option value="found_alternative">Found alternative</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
            <button @click="showCancelModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
              Keep Subscription
            </button>
            <button @click="cancelSubscription()" 
                    :disabled="cancellingSubscription"
                    class="bg-red-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
              <span x-show="!cancellingSubscription">Cancel Subscription</span>
              <span x-show="cancellingSubscription" class="flex items-center">
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Cancelling...
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function subscriptionManagement() {
        return {
          trialDaysRemaining: {{ $trial_days_remaining ?? 0 }},
          selectedPlan: null,
          selectedUpgradePlan: null,
          processingPayment: false,
          showUpgradeModal: false,
          showCancelModal: false,
          showUpdateBillingModal: false,
          upgradingPlan: false,
          cancellingSubscription: false,
          cancellationReason: '',

          init() {
            // Initialize any required data or event listeners
            this.updateTrialProgress();
          },

          updateTrialProgress() {
            if (this.trialDaysRemaining > 0) {
              // Update trial countdown if needed
              setInterval(() => {
                // Could implement real-time countdown here
              }, 86400000); // Once per day
            }
          },

          async selectPlan(planName, price) {
            this.selectedPlan = planName;
            this.processingPayment = true;

            try {
              const response = await fetch('{{ route("subscription.create") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  plan: planName,
                  price: price
                })
              });

              const data = await response.json();

              if (data.success) {
                if (data.requires_payment_method) {
                  // Redirect to payment method setup
                  window.location.href = data.setup_intent_url;
                } else {
                  this.showNotification('Success!', 'Your subscription has been activated', 'success');
                  window.location.reload();
                }
              } else {
                this.showNotification('Error', data.message || 'Failed to create subscription', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Network error. Please try again.', 'error');
            } finally {
              this.processingPayment = false;
              this.selectedPlan = null;
            }
          },

          async upgradePlan() {
            if (!this.selectedUpgradePlan) return;

            this.upgradingPlan = true;

            try {
              const response = await fetch('{{ route("subscription.upgrade") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  plan: this.selectedUpgradePlan
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('Upgraded!', 'Your plan has been upgraded successfully', 'success');
                this.showUpgradeModal = false;
                setTimeout(() => window.location.reload(), 2000);
              } else {
                this.showNotification('Error', data.message || 'Failed to upgrade plan', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Network error. Please try again.', 'error');
            } finally {
              this.upgradingPlan = false;
            }
          },

          async cancelSubscription() {
            this.cancellingSubscription = true;

            try {
              const response = await fetch('{{ route("subscription.cancel") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  reason: this.cancellationReason
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('Subscription Cancelled', 'Your subscription will remain active until the end of the billing period', 'info');
                this.showCancelModal = false;
                setTimeout(() => window.location.reload(), 2000);
              } else {
                this.showNotification('Error', data.message || 'Failed to cancel subscription', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Network error. Please try again.', 'error');
            } finally {
              this.cancellingSubscription = false;
            }
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }
    </script>
  @endpush
</x-unified-layout>
