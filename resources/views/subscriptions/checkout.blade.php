<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center">
                    <a href="{{ route('subscriptions.dashboard') }}" class="text-gray-400 hover:text-gray-600 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Complete Your Subscription</h1>
                        <p class="mt-1 text-sm text-gray-500">Secure checkout powered by Stripe</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="subscriptionCheckout()">
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Main Checkout Form --}}
                <div class="lg:col-span-2">
                    {{-- Plan Summary --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Selected Plan</h2>
                            <button @click="changePlan()" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                Change Plan
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900" x-text="planDetails.name">Sports Fan Plan</h3>
                                    <p class="text-sm text-gray-600" x-text="planDetails.description">100 tickets per month + premium features</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900" x-text="planDetails.displayPrice">$29.99</div>
                                <div class="text-sm text-gray-500" x-text="planDetails.interval">per month</div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Form --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Payment Information</h2>
                        
                        <form @submit.prevent="processPayment()" id="payment-form">
                            {{-- Personal Information --}}
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-4">Billing Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                        <input type="text" 
                                               id="first_name" 
                                               x-model="billingInfo.firstName"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                        <input type="text" 
                                               id="last_name" 
                                               x-model="billingInfo.lastName"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" 
                                       id="email" 
                                       x-model="billingInfo.email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       required>
                            </div>

                            {{-- Address Information --}}
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-4">Billing Address</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                        <input type="text" 
                                               id="address" 
                                               x-model="billingInfo.address"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" 
                                                   id="city" 
                                                   x-model="billingInfo.city"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                   required>
                                        </div>
                                        <div>
                                            <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                            <input type="text" 
                                                   id="state" 
                                                   x-model="billingInfo.state"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                   required>
                                        </div>
                                        <div>
                                            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">ZIP/Postal Code</label>
                                            <input type="text" 
                                                   id="postal_code" 
                                                   x-model="billingInfo.postalCode"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                   required>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                        <select id="country" 
                                                x-model="billingInfo.country"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                required>
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="ES">Spain</option>
                                            <option value="IT">Italy</option>
                                            <option value="NL">Netherlands</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-4">Payment Method</h3>
                                
                                {{-- Stripe Elements Container --}}
                                <div class="border border-gray-300 rounded-lg p-4">
                                    <div id="card-element" class="min-h-[40px]">
                                        <!-- Stripe Elements will create form elements here -->
                                    </div>
                                    <div id="card-errors" class="text-red-600 text-sm mt-2" role="alert"></div>
                                </div>

                                <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                            <rect width="36" height="24" rx="4" fill="#1A73E8"/>
                                            <path d="M12.97 10.336v3.328h-.816v-3.328h-.576v-.696h1.968v.696h-.576zm1.584-.696h.816l1.008 2.592L17.346 9.64h.816l-1.488 4.024h-.768l-1.344-4.024zm2.736 0h.768v4.024h-.768V9.64zm2.112 0v.696h-1.344v.864h1.248v.672h-1.248v.888h1.392v.704h-2.16V9.64h2.112z" fill="white"/>
                                            <text x="18" y="14" font-family="Arial" font-size="6" fill="white">VISA</text>
                                        </svg>
                                        <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                            <rect width="36" height="24" rx="4" fill="#EB001B"/>
                                            <circle cx="14" cy="12" r="7" fill="#EB001B"/>
                                            <circle cx="22" cy="12" r="7" fill="#FF5F00"/>
                                            <circle cx="18" cy="12" r="7" fill="#F79E1B"/>
                                        </svg>
                                        <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                            <rect width="36" height="24" rx="4" fill="#006FCF"/>
                                            <path d="M8 8h20v8H8z" fill="#006FCF"/>
                                        </svg>
                                    </div>
                                    <span>Secured by Stripe</span>
                                </div>
                            </div>

                            {{-- Terms Acceptance --}}
                            <div class="mb-6">
                                <label class="flex items-start space-x-3">
                                    <input type="checkbox" 
                                           x-model="agreedToTerms"
                                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-0.5"
                                           required>
                                    <span class="text-sm text-gray-600">
                                        I agree to the 
                                        <a href="/legal/terms" target="_blank" class="text-indigo-600 hover:text-indigo-700">Terms of Service</a>,
                                        <a href="/legal/privacy" target="_blank" class="text-indigo-600 hover:text-indigo-700">Privacy Policy</a>,
                                        and authorize HD Tickets to charge my payment method for the subscription.
                                    </span>
                                </label>
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" 
                                    :disabled="!agreedToTerms || isProcessing"
                                    class="w-full bg-indigo-600 text-white py-4 px-6 rounded-lg font-semibold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isProcessing" class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Complete Subscription - <span x-text="planDetails.displayPrice">$29.99</span><span x-text="planDetails.interval">per month</span>
                                </span>
                                <span x-show="isProcessing" class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing Payment...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Order Summary Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600" x-text="planDetails.name">Sports Fan Plan</span>
                                <span class="font-medium" x-text="planDetails.displayPrice">$29.99</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Billing frequency</span>
                                <span class="text-gray-700" x-text="planDetails.interval">Monthly</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Ticket limit</span>
                                <span class="text-gray-700" x-text="planDetails.ticketLimit + ' per month'">100 per month</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between font-semibold">
                                <span>Total</span>
                                <span x-text="planDetails.displayPrice">$29.99</span>
                            </div>
                        </div>

                        {{-- Plan Features --}}
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">What's Included</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="planDetails.ticketLimit + ' ticket purchases'">100 ticket purchases</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Unlimited price alerts</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Real-time notifications</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Priority customer support</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Mobile app access</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Security Notice --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Secure Payment</p>
                                    <p class="text-xs text-gray-500 mt-1">Your payment information is encrypted and secure. We never store your credit card details.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Support Contact --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-xs text-gray-500 text-center">
                                Need help? 
                                <a href="/support" class="text-indigo-600 hover:text-indigo-700">Contact Support</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stripe.js --}}
    <script src="https://js.stripe.com/v3/"></script>
    
    <script>
        function subscriptionCheckout() {
            const planType = '{{ $planType ?? "monthly" }}';
            const planConfig = {
                monthly: {
                    name: 'Sports Fan Plan',
                    description: '100 tickets per month + premium features',
                    price: 2999, // cents
                    displayPrice: '$29.99',
                    interval: 'per month',
                    ticketLimit: 100
                },
                annual: {
                    name: 'Sports Fan Plan',
                    description: '100 tickets per month + premium features',
                    price: 29999, // cents
                    displayPrice: '$299.99',
                    interval: 'per year',
                    ticketLimit: 100
                }
            };

            return {
                planDetails: planConfig[planType],
                billingInfo: {
                    firstName: '{{ auth()->user()->first_name ?? "" }}',
                    lastName: '{{ auth()->user()->last_name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}',
                    address: '',
                    city: '',
                    state: '',
                    postalCode: '',
                    country: 'US'
                },
                agreedToTerms: false,
                isProcessing: false,
                stripe: null,
                cardElement: null,

                init() {
                    this.initializeStripe();
                },

                initializeStripe() {
                    this.stripe = Stripe('{{ env("STRIPE_KEY") }}');
                    const elements = this.stripe.elements({
                        fonts: [{
                            cssSrc: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap'
                        }]
                    });

                    this.cardElement = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#424770',
                                fontFamily: '"Inter", sans-serif',
                                '::placeholder': {
                                    color: '#aab7c4',
                                },
                                iconColor: '#666EE8',
                            },
                            invalid: {
                                color: '#e53e3e',
                                iconColor: '#e53e3e'
                            }
                        }
                    });

                    this.cardElement.mount('#card-element');

                    // Handle real-time validation errors from the card Element
                    this.cardElement.on('change', ({error}) => {
                        const displayError = document.getElementById('card-errors');
                        if (error) {
                            displayError.textContent = error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                },

                changePlan() {
                    window.location.href = '{{ route("subscriptions.dashboard") }}';
                },

                async processPayment() {
                    if (this.isProcessing) return;
                    
                    this.isProcessing = true;
                    
                    try {
                        // Create payment method with Stripe
                        const {token, error} = await this.stripe.createToken(this.cardElement, {
                            name: `${this.billingInfo.firstName} ${this.billingInfo.lastName}`,
                            address_line1: this.billingInfo.address,
                            address_city: this.billingInfo.city,
                            address_state: this.billingInfo.state,
                            address_zip: this.billingInfo.postalCode,
                            address_country: this.billingInfo.country,
                        });

                        if (error) {
                            document.getElementById('card-errors').textContent = error.message;
                            this.isProcessing = false;
                            return;
                        }

                        // Submit to backend
                        const response = await fetch('/api/v1/subscriptions/create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                plan_type: planType,
                                payment_token: token.id,
                                billing_info: this.billingInfo
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            // Handle successful subscription creation
                            if (data.requires_action) {
                                // Handle 3D Secure authentication
                                const {error: confirmError} = await this.stripe.confirmCardPayment(
                                    data.payment_intent_client_secret
                                );
                                
                                if (confirmError) {
                                    this.showError(confirmError.message);
                                    this.isProcessing = false;
                                    return;
                                }
                            }
                            
                            // Redirect to success page
                            window.location.href = data.redirect_url || '/subscriptions/success';
                        } else {
                            this.showError(data.message || 'Payment failed. Please try again.');
                            this.isProcessing = false;
                        }
                    } catch (error) {
                        console.error('Error processing payment:', error);
                        this.showError('An error occurred. Please try again.');
                        this.isProcessing = false;
                    }
                },

                showError(message) {
                    document.getElementById('card-errors').textContent = message;
                    
                    // Scroll to error message
                    document.getElementById('card-errors').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
            }
        }
    </script>
</x-app-layout>
