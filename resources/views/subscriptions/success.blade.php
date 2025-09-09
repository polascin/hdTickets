<x-app-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8" x-data="subscriptionSuccess()">
            {{-- Success Animation --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-green-100 mb-6" 
                     x-show="showAnimation" 
                     x-transition:enter="transition-all duration-1000 ease-out"
                     x-transition:enter-start="scale-0 rotate-180 opacity-0"
                     x-transition:enter-end="scale-100 rotate-0 opacity-100">
                    <svg class="h-16 w-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome to HD Tickets!</h1>
                <p class="text-lg text-gray-600 mb-8">Your subscription has been activated successfully</p>
            </div>

            {{-- Subscription Details --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Subscription Details</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Plan</span>
                        <span class="font-medium text-gray-900">{{ $subscription->plan_name ?? 'Sports Fan Plan' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Price</span>
                        <span class="font-medium text-gray-900">${{ number_format($subscription->monthly_fee ?? 29.99, 2) }}/month</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Ticket Limit</span>
                        <span class="font-medium text-gray-900">{{ $subscription->ticket_limit ?? 100 }} per month</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Next Billing</span>
                        <span class="font-medium text-gray-900">{{ $subscription->next_billing_date?->format('M j, Y') ?? now()->addMonth()->format('M j, Y') }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-3 text-sm text-green-700 bg-green-50 rounded-lg p-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Your subscription is now active and ready to use!</span>
                    </div>
                </div>
            </div>

            {{-- What's Next --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">What's Next?</h2>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-indigo-600">1</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Explore Available Tickets</h3>
                            <p class="text-sm text-gray-600">Browse thousands of sports events and find the perfect tickets for you.</p>
                            <a href="/tickets" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Browse Tickets →</a>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-indigo-600">2</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Set Up Price Alerts</h3>
                            <p class="text-sm text-gray-600">Get notified when ticket prices drop for your favorite teams and events.</p>
                            <a href="/alerts" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Create Alerts →</a>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-indigo-600">3</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Download Mobile App</h3>
                            <p class="text-sm text-gray-600">Get instant notifications and manage your tickets on the go.</p>
                            <div class="flex space-x-2 mt-2">
                                <a href="#" class="inline-block">
                                    <img src="/images/app-store.svg" alt="Download on the App Store" class="h-8">
                                </a>
                                <a href="#" class="inline-block">
                                    <img src="/images/google-play.svg" alt="Get it on Google Play" class="h-8">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscription Benefits --}}
            <div class="bg-indigo-50 border-2 border-indigo-200 rounded-2xl p-6 mb-6">
                <h2 class="text-lg font-semibold text-indigo-900 mb-4">Your Premium Benefits</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">{{ $subscription->ticket_limit ?? 100 }} Monthly Tickets</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">Price Drop Alerts</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">Real-time Notifications</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">Priority Support</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">Mobile App Access</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-indigo-800">Advanced Filters</span>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <a href="/dashboard" 
                   class="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors text-center block">
                    Go to Dashboard
                </a>
                <a href="/tickets" 
                   class="w-full bg-white text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center block border border-gray-300">
                    Browse Tickets
                </a>
            </div>

            {{-- Email Confirmation Notice --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Email Confirmation Sent</p>
                        <p class="text-sm text-yellow-700 mt-1">
                            We've sent a confirmation email to <strong>{{ auth()->user()->email }}</strong> with your subscription details and receipt.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Support Information --}}
            <div class="text-center pt-6">
                <p class="text-sm text-gray-500">
                    Questions? 
                    <a href="/support" class="text-indigo-600 hover:text-indigo-700">Contact our support team</a> 
                    or check our 
                    <a href="/help" class="text-indigo-600 hover:text-indigo-700">Help Center</a>
                </p>
            </div>

            {{-- Social Sharing (Optional) --}}
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-3">Share your excitement!</p>
                <div class="flex justify-center space-x-3">
                    <a href="#" @click.prevent="shareOnTwitter()" 
                       class="bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="#" @click.prevent="shareOnFacebook()" 
                       class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function subscriptionSuccess() {
            return {
                showAnimation: false,

                init() {
                    // Trigger animation after component is mounted
                    setTimeout(() => {
                        this.showAnimation = true;
                    }, 100);

                    // Optional: Track subscription completion for analytics
                    this.trackSubscriptionSuccess();
                },

                shareOnTwitter() {
                    const text = encodeURIComponent("Just subscribed to HD Tickets! Ready to find amazing deals on sports tickets 🎫⚽🏀");
                    const url = encodeURIComponent(window.location.origin);
                    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=550,height=420');
                },

                shareOnFacebook() {
                    const url = encodeURIComponent(window.location.origin);
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=550,height=420');
                },

                trackSubscriptionSuccess() {
                    // Track subscription completion for analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'purchase', {
                            transaction_id: '{{ $subscription->id ?? "unknown" }}',
                            value: {{ $subscription->monthly_fee ?? 29.99 }},
                            currency: 'USD',
                            items: [{
                                item_id: 'sports-fan-plan',
                                item_name: '{{ $subscription->plan_name ?? "Sports Fan Plan" }}',
                                category: 'subscription',
                                quantity: 1,
                                price: {{ $subscription->monthly_fee ?? 29.99 }}
                            }]
                        });
                    }
                    
                    // Facebook Pixel tracking
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'Subscribe', {
                            value: {{ $subscription->monthly_fee ?? 29.99 }},
                            currency: 'USD'
                        });
                    }
                }
            }
        }
    </script>
</x-app-layout>
