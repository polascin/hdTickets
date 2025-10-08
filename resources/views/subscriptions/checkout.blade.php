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
                        <p class="mt-1 text-sm text-gray-500">Secure checkout powered by Stripe & PayPal</p>
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
                                
                                {{-- Payment Method Selection --}}
                                <div class="space-y-4 mb-4">
                                    <div class="flex space-x-4">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" 
                                                   name="payment_method" 
                                                   value="stripe"
                                                   x-model="paymentMethod"
                                                   class="sr-only">
                                            <div class="border-2 rounded-lg p-4 transition-colors" 
                                                 :class="paymentMethod === 'stripe' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400'">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-6 h-6 rounded-full border-2 transition-colors"
                                                             :class="paymentMethod === 'stripe' ? 'border-indigo-500 bg-indigo-500' : 'border-gray-300'">
                                                            <div class="w-2 h-2 bg-white rounded-full mx-auto mt-1" x-show="paymentMethod === 'stripe'"></div>
                                                        </div>
                                                        <span class="font-medium text-gray-900">Credit/Debit Card</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                                            <rect width="36" height="24" rx="4" fill="#1A73E8"/>
                                                            <text x="6" y="14" font-family="Arial" font-size="6" fill="white" font-weight="bold">VISA</text>
                                                        </svg>
                                                        <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                                            <rect width="36" height="24" rx="4" fill="#EB001B"/>
                                                            <circle cx="14" cy="12" r="6" fill="#EB001B"/>
                                                            <circle cx="22" cy="12" r="6" fill="#FF5F00"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" 
                                                   name="payment_method" 
                                                   value="paypal"
                                                   x-model="paymentMethod"
                                                   class="sr-only">
                                            <div class="border-2 rounded-lg p-4 transition-colors" 
                                                 :class="paymentMethod === 'paypal' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400'">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-6 h-6 rounded-full border-2 transition-colors"
                                                             :class="paymentMethod === 'paypal' ? 'border-indigo-500 bg-indigo-500' : 'border-gray-300'">
                                                            <div class="w-2 h-2 bg-white rounded-full mx-auto mt-1" x-show="paymentMethod === 'paypal'"></div>
                                                        </div>
                                                        <span class="font-medium text-gray-900">PayPal</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <svg class="w-16 h-6" viewBox="0 0 124 33" fill="none">
                                                            <path d="M46.211 6.749h-6.839a.95.95 0 00-.939.802l-2.766 17.537a.57.57 0 00.564.658h3.265a.95.95 0 00.939-.803l.746-4.73a.95.95 0 01.938-.803h2.165c4.505 0 7.105-2.18 7.784-6.5.306-1.89.013-3.375-.872-4.415-.972-1.142-2.696-1.746-4.985-1.746zM47.117 13.154c-.374 2.454-2.249 2.454-4.062 2.454h-1.032l.724-4.583a.57.57 0 01.563-.481h.473c1.235 0 2.4 0 3.002.704.359.42.469 1.044.332 1.906zM66.654 13.075h-3.275a.57.57 0 00-.563.481l-.145.916-.229-.332c-.709-1.029-2.29-1.373-3.868-1.373-3.619 0-6.71 2.741-7.312 6.586-.313 1.918.132 3.752 1.22 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 00.562.66h2.95a.95.95 0 00.939-.803l1.77-11.209a.568.568 0 00-.561-.658zm-4.565 6.374c-.316 1.871-1.801 3.127-3.695 3.127-.951 0-1.711-.305-2.199-.883-.484-.574-.668-1.391-.514-2.301.295-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.499.589.697 1.411.554 2.317zM84.096 13.075h-3.291a.954.954 0 00-.787.417l-4.539 6.686-1.924-6.425a.953.953 0 00-.912-.678h-3.234a.57.57 0 00-.541.754l3.625 10.638-3.408 4.811a.57.57 0 00.465.9h3.287a.949.949 0 00.781-.408l10.946-15.8a.57.57 0 00-.468-.895z" fill="#253B80"/>
                                                            <path d="M94.992 6.749h-6.84a.95.95 0 00-.938.802l-2.766 17.537a.569.569 0 00.562.658h3.51a.665.665 0 00.656-.562l.785-4.971a.95.95 0 01.938-.803h2.164c4.506 0 7.105-2.18 7.785-6.5.307-1.89.012-3.375-.873-4.415-.971-1.142-2.694-1.746-4.983-1.746zm.789 6.405c-.373 2.454-2.248 2.454-4.062 2.454h-1.031l.725-4.583a.568.568 0 01.562-.481h.473c1.234 0 2.4 0 3.002.704.359.42.468 1.044.331 1.906zM115.434 13.075h-3.273a.567.567 0 00-.562.481l-.145.916-.230-.332c-.709-1.029-2.289-1.373-3.867-1.373-3.619 0-6.709 2.741-7.311 6.586-.312 1.918.131 3.752 1.219 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 00.564.66h2.949a.95.95 0 00.938-.803l1.771-11.209a.571.571 0 00-.565-.658zm-4.565 6.374c-.314 1.871-1.801 3.127-3.695 3.127-.949 0-1.711-.305-2.199-.883-.484-.574-.666-1.391-.514-2.301.297-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.501.589.699 1.411.554 2.317z" fill="#179BD7"/>
                                                            <path d="M119.295 7.23l-2.807 17.858a.569.569 0 00.562.658h2.822c.469 0 .867-.34.939-.803l2.768-17.536a.57.57 0 00-.562-.659h-3.16a.571.571 0 00-.562.482z" fill="#179BD7"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                {{-- Stripe Elements Container --}}
                                <div x-show="paymentMethod === 'stripe'" class="border border-gray-300 rounded-lg p-4">
                                    <div id="card-element" class="min-h-[40px]">
                                        <!-- Stripe Elements will create form elements here -->
                                    </div>
                                    <div id="card-errors" class="text-red-600 text-sm mt-2" role="alert"></div>
                                </div>
                                
                                {{-- PayPal Container --}}
                                <div x-show="paymentMethod === 'paypal'" class="border border-gray-300 rounded-lg p-4">
                                    <div id="paypal-button-container" class="min-h-[50px]">
                                        <!-- PayPal button will be rendered here -->
                                    </div>
                                    <div id="paypal-errors" class="text-red-600 text-sm mt-2" role="alert"></div>
                                </div>

                                <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500" x-show="paymentMethod === 'stripe'">
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
                                <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500" x-show="paymentMethod === 'paypal'">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Secured by PayPal</span>
                                    </div>
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
                                    <p class="text-xs text-gray-500 mt-1" x-show="paymentMethod === 'stripe'">Your payment information is encrypted and secure. We never store your credit card details.</p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="paymentMethod === 'paypal'">Your payment is processed securely through PayPal. We never see or store your financial information.</p>
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
    
    {{-- PayPal SDK --}}
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.sandbox.client_id') }}&vault=true&intent=subscription&currency=USD"></script>
    
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
                paymentMethod: 'stripe',
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
                paypalButtonRendered: false,

                init() {
                    this.initializeStripe();
                    this.$watch('paymentMethod', (value) => {
                        if (value === 'paypal' && !this.paypalButtonRendered) {
                            this.initializePayPal();
                        }
                    });
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

                initializePayPal() {
                    if (typeof paypal === 'undefined') {
                        console.error('PayPal SDK not loaded');
                        return;
                    }

                    // Clear any existing PayPal buttons
                    document.getElementById('paypal-button-container').innerHTML = '';
                    
                    paypal.Buttons({
                        style: {
                            layout: 'vertical',
                            color: 'blue',
                            shape: 'rect',
                            label: 'subscribe'
                        },
                        
                        createSubscription: (data, actions) => {
                            return actions.subscription.create({
                                'plan_id': this.getPayPalPlanId(),
                                'subscriber': {
                                    'name': {
                                        'given_name': this.billingInfo.firstName,
                                        'surname': this.billingInfo.lastName
                                    },
                                    'email_address': this.billingInfo.email,
                                    'shipping_address': {
                                        'address_line_1': this.billingInfo.address,
                                        'admin_area_2': this.billingInfo.city,
                                        'admin_area_1': this.billingInfo.state,
                                        'postal_code': this.billingInfo.postalCode,
                                        'country_code': this.billingInfo.country
                                    }
                                }
                            });
                        },
                        
                        onApprove: (data, actions) => {
                            return this.handlePayPalApproval(data.subscriptionID);
                        },
                        
                        onError: (err) => {
                            console.error('PayPal error:', err);
                            this.showError('PayPal payment failed. Please try again.');
                        },
                        
                        onCancel: (data) => {
                            console.log('PayPal payment cancelled:', data);
                            document.getElementById('paypal-errors').textContent = 'Payment cancelled.';
                        }
                    }).render('#paypal-button-container');
                    
                    this.paypalButtonRendered = true;
                },

                getPayPalPlanId() {
                    // This should return the appropriate PayPal plan ID based on the selected plan
                    return planType === 'monthly' ? '{{ config("services.paypal.monthly_plan_id") }}' : '{{ config("services.paypal.annual_plan_id") }}';
                },

                async handlePayPalApproval(subscriptionId) {
                    this.isProcessing = true;
                    
                    try {
                        const response = await fetch('/api/v1/subscriptions/paypal/approve', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                subscription_id: subscriptionId,
                                plan_type: planType,
                                billing_info: this.billingInfo
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = data.redirect_url || '/subscriptions/success';
                        } else {
                            this.showError(data.message || 'PayPal subscription failed. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error processing PayPal subscription:', error);
                        this.showError('An error occurred. Please try again.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                changePlan() {
                    window.location.href = '{{ route("subscriptions.dashboard") }}';
                },

                async processPayment() {
                    if (this.isProcessing) return;
                    
                    // PayPal payments are handled through their own flow
                    if (this.paymentMethod === 'paypal') {
                        this.showError('Please use the PayPal button above to complete your subscription.');
                        return;
                    }
                    
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
