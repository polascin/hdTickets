<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Subscription Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage your HD Tickets subscription and billing</p>
                    </div>
                    @if(auth()->user()->role === 'customer' && auth()->user()->hasActiveSubscription())
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Active Subscription
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="subscriptionDashboard()">
            {{-- Subscription Status Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Current Plan</h2>
                        <div class="flex items-center space-x-4">
                            @if(auth()->user()->role === 'customer')
                                @if(auth()->user()->hasActiveSubscription())
                                    <span class="text-2xl font-bold text-indigo-600">Sports Fan Plan</span>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Active</span>
                                @elseif(auth()->user()->isInFreeTrial())
                                    <span class="text-2xl font-bold text-blue-600">Free Trial</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">Trial Period</span>
                                @else
                                    <span class="text-2xl font-bold text-gray-600">No Active Subscription</span>
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Expired</span>
                                @endif
                            @elseif(auth()->user()->role === 'agent')
                                <span class="text-2xl font-bold text-purple-600">Professional Agent</span>
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">Unlimited Access</span>
                            @else
                                <span class="text-2xl font-bold text-gray-900">{{ ucfirst(auth()->user()->role) }}</span>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->role === 'customer')
                        <div class="text-right">
                            @if(auth()->user()->hasActiveSubscription())
                                <div class="text-3xl font-bold text-gray-900">${{ auth()->user()->subscription->monthly_fee ?? '29.99' }}</div>
                                <div class="text-sm text-gray-500">per month</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Next billing: {{ auth()->user()->subscription?->next_billing_date?->format('M j, Y') ?? 'N/A' }}
                                </div>
                            @elseif(auth()->user()->isInFreeTrial())
                                <div class="text-3xl font-bold text-green-600">FREE</div>
                                <div class="text-sm text-gray-500">trial period</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Trial ends: {{ auth()->user()->created_at->addDays(7)->format('M j, Y') }}
                                </div>
                            @else
                                <div class="text-2xl font-bold text-gray-400">--</div>
                                <div class="text-sm text-gray-500">no subscription</div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Usage Progress (for customers only) --}}
                @if(auth()->user()->role === 'customer')
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-700">Monthly Ticket Usage</h3>
                            <span class="text-sm text-gray-500">
                                {{ auth()->user()->getMonthlyTicketUsage() }} / {{ auth()->user()->getMonthlyTicketLimit() }} tickets
                            </span>
                        </div>
                        @php
                            $usage = auth()->user()->getMonthlyTicketUsage();
                            $limit = auth()->user()->getMonthlyTicketLimit();
                            $percentage = $limit > 0 ? ($usage / $limit) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                            <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300" 
                                 style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ $usage }}</div>
                                <div class="text-xs text-gray-500">Used This Month</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ $limit - $usage }}</div>
                                <div class="text-xs text-gray-500">Remaining</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ auth()->user()->ticket_alerts_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Active Alerts</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600">{{ auth()->user()->watchlist_items_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Watched Events</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-8">
                    @if(auth()->user()->role === 'customer' && !auth()->user()->hasActiveSubscription() && !auth()->user()->isInFreeTrial())
                        {{-- Subscription Plans (for users without active subscription) --}}
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Choose Your Plan</h2>
                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Monthly Plan --}}
                                <div class="border-2 border-indigo-200 rounded-xl p-6 relative">
                                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                        <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-xs font-medium">Most Popular</span>
                                    </div>
                                    <div class="text-center mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900">Monthly Plan</h3>
                                        <div class="mt-2">
                                            <span class="text-4xl font-bold text-gray-900">$29.99</span>
                                            <span class="text-gray-500">/month</span>
                                        </div>
                                    </div>
                                    <ul class="space-y-3 mb-6">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">100 ticket purchases per month</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Unlimited price alerts</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Priority customer support</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Real-time notifications</span>
                                        </li>
                                    </ul>
                                    <button @click="selectPlan('monthly')" 
                                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        Subscribe Now
                                    </button>
                                </div>

                                {{-- Annual Plan --}}
                                <div class="border-2 border-gray-200 rounded-xl p-6">
                                    <div class="text-center mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900">Annual Plan</h3>
                                        <div class="mt-2">
                                            <span class="text-4xl font-bold text-gray-900">$299.99</span>
                                            <span class="text-gray-500">/year</span>
                                        </div>
                                        <div class="text-sm text-green-600 font-medium">Save 17%</div>
                                    </div>
                                    <ul class="space-y-3 mb-6">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">100 ticket purchases per month</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Unlimited price alerts</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Priority customer support</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Real-time notifications</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700">Annual savings</span>
                                        </li>
                                    </ul>
                                    <button @click="selectPlan('annual')" 
                                            class="w-full bg-gray-800 text-white py-3 rounded-lg font-medium hover:bg-gray-900 transition-colors">
                                        Subscribe Annually
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Billing History --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Billing History</h2>
                            <button @click="downloadInvoices()" 
                                    class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                Download All
                            </button>
                        </div>

                        @if(auth()->user()->role === 'customer' && auth()->user()->invoices()->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach(auth()->user()->invoices()->latest()->take(10)->get() as $invoice)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $invoice->created_at->format('M j, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $invoice->description ?? 'HD Tickets Monthly Subscription' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ number_format($invoice->amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($invoice->status === 'paid')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Paid</span>
                                                    @elseif($invoice->status === 'pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600">
                                                    <a href="{{ route('subscriptions.invoice', $invoice) }}" class="hover:text-indigo-700">Download</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Billing History</h3>
                                <p class="text-gray-500">Your billing history will appear here once you have an active subscription.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Payment Method --}}
                    @if(auth()->user()->role === 'customer')
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
                            
                            @if(auth()->user()->hasPaymentMethod())
                                <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <div>
                                        <div class="font-medium text-gray-900">•••• •••• •••• {{ auth()->user()->card_last_four ?? '1234' }}</div>
                                        <div class="text-sm text-gray-500">{{ auth()->user()->card_brand ?? 'Visa' }} • Expires {{ auth()->user()->card_expires ?? '12/25' }}</div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="updatePaymentMethod()" 
                                            class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                                        Update Card
                                    </button>
                                    <button @click="removePaymentMethod()" 
                                            class="flex-1 bg-red-100 text-red-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors">
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <h4 class="font-medium text-gray-900 mb-2">No Payment Method</h4>
                                    <p class="text-sm text-gray-500 mb-4">Add a payment method to manage your subscription</p>
                                    <button @click="addPaymentMethod()" 
                                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                                        Add Payment Method
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            @if(auth()->user()->role === 'customer')
                                @if(auth()->user()->hasActiveSubscription())
                                    <button @click="pauseSubscription()" 
                                            class="w-full bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-yellow-200 transition-colors text-left">
                                        Pause Subscription
                                    </button>
                                    <button @click="changeSubscription()" 
                                            class="w-full bg-blue-100 text-blue-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors text-left">
                                        Change Plan
                                    </button>
                                    <button @click="showCancelModal = true" 
                                            class="w-full bg-red-100 text-red-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors text-left">
                                        Cancel Subscription
                                    </button>
                                @endif
                            @endif
                            <a href="/settings" 
                               class="block w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors text-left">
                                Account Settings
                            </a>
                            <a href="/support" 
                               class="block w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors text-left">
                                Contact Support
                            </a>
                        </div>
                    </div>

                    {{-- Subscription Benefits --}}
                    @if(auth()->user()->role === 'agent')
                        <div class="bg-purple-50 border-2 border-purple-200 rounded-2xl p-6">
                            <h3 class="text-lg font-semibold text-purple-900 mb-4">Agent Benefits</h3>
                            <div class="space-y-3 text-sm text-purple-800">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Unlimited ticket purchases</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Advanced monitoring tools</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>No monthly fees</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Priority support</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cancellation Modal --}}
            <div x-show="showCancelModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6" @click.stop>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Cancel Subscription?</h3>
                        <p class="text-gray-600">Are you sure you want to cancel your HD Tickets subscription? You'll lose access to premium features.</p>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">What you'll lose:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Ticket purchase access</li>
                                <li>• Price drop alerts</li>
                                <li>• Watchlist monitoring</li>
                                <li>• Priority customer support</li>
                            </ul>
                        </div>

                        <div>
                            <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Why are you canceling? (Optional)
                            </label>
                            <select x-model="cancellationReason" id="cancellation_reason" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">Select a reason</option>
                                <option value="too_expensive">Too expensive</option>
                                <option value="not_using">Not using enough</option>
                                <option value="found_alternative">Found alternative</option>
                                <option value="technical_issues">Technical issues</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div x-show="cancellationReason === 'other'">
                            <textarea x-model="cancellationComment" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                                      rows="3" 
                                      placeholder="Please tell us more..."></textarea>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button @click="showCancelModal = false" 
                                class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Keep Subscription
                        </button>
                        <button @click="confirmCancellation()" 
                                :disabled="isCancelling"
                                class="flex-1 bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors disabled:opacity-50">
                            <span x-show="!isCancelling">Cancel Subscription</span>
                            <span x-show="isCancelling">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function subscriptionDashboard() {
            return {
                showCancelModal: false,
                cancellationReason: '',
                cancellationComment: '',
                isCancelling: false,

                selectPlan(planType) {
                    // Redirect to subscription checkout
                    window.location.href = `/subscriptions/checkout/${planType}`;
                },

                async pauseSubscription() {
                    if (!confirm('Are you sure you want to pause your subscription?')) return;

                    try {
                        const response = await fetch('/api/v1/subscriptions/pause', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Subscription paused successfully', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showToast(data.message || 'Failed to pause subscription', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('An error occurred. Please try again.', 'error');
                    }
                },

                changeSubscription() {
                    window.location.href = '/subscriptions/change-plan';
                },

                addPaymentMethod() {
                    window.location.href = '/subscriptions/payment-method/add';
                },

                updatePaymentMethod() {
                    window.location.href = '/subscriptions/payment-method/update';
                },

                async removePaymentMethod() {
                    if (!confirm('Are you sure you want to remove your payment method?')) return;

                    try {
                        const response = await fetch('/api/v1/subscriptions/payment-method', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Payment method removed successfully', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showToast(data.message || 'Failed to remove payment method', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('An error occurred. Please try again.', 'error');
                    }
                },

                async confirmCancellation() {
                    this.isCancelling = true;

                    try {
                        const response = await fetch('/api/v1/subscriptions/cancel', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                reason: this.cancellationReason,
                                comment: this.cancellationComment
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Subscription cancelled successfully', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showToast(data.message || 'Failed to cancel subscription', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('An error occurred. Please try again.', 'error');
                    } finally {
                        this.isCancelling = false;
                    }
                },

                downloadInvoices() {
                    window.location.href = '/subscriptions/invoices/download';
                },

                showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform`;
                    toast.textContent = message;
                    
                    document.body.appendChild(toast);
                    
                    // Animate in
                    requestAnimationFrame(() => {
                        toast.classList.remove('translate-x-full');
                    });
                    
                    // Remove after 5 seconds
                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => toast.remove(), 300);
                    }, 5000);
                }
            }
        }
    </script>
</x-app-layout>
