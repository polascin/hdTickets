<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-xl md:text-2xl text-gray-800 leading-tight">
                    {{ __('Register User with Payment Plan') }}
                </h2>
                <p class="text-sm text-gray-600">Create a new user account and assign a payment plan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.register-with-payment.store') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- User Information -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                User Information
                            </h3>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Name Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name
                                    </label>
                                    <input type="text" id="surname" name="surname" value="{{ old('surname') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('surname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number
                                    </label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                    Username
                                </label>
                                <input type="text" id="username" name="username" value="{{ old('username') }}"
                                       placeholder="Leave blank to auto-generate from name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('username')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" id="password" name="password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Role and Settings -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                        User Role
                                    </label>
                                    <select id="role" name="role" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                                        <option value="agent" {{ old('role') == 'agent' ? 'selected' : '' }}>Agent</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Active Account</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="require_2fa" value="1" {{ old('require_2fa') ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Require 2FA</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Plan Selection -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Payment Plan Selection
                            </h3>
                        </div>

                        <div class="p-6">
                            <!-- Subscription Type -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Subscription Type <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="subscription_type" value="trial" {{ old('subscription_type', 'trial') == 'trial' ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Trial Period</span>
                                            <p class="text-sm text-gray-500">Start with a free trial</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="subscription_type" value="paid" {{ old('subscription_type') == 'paid' ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Paid Subscription</span>
                                            <p class="text-sm text-gray-500">Full access with payment</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="subscription_type" value="admin_granted" {{ old('subscription_type') == 'admin_granted' ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Admin Granted</span>
                                            <p class="text-sm text-gray-500">Free access granted by admin</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Trial Days (shown when trial is selected) -->
                            <div id="trial-options" class="mb-6" style="display: none;">
                                <label for="trial_days" class="block text-sm font-medium text-gray-700 mb-2">
                                    Trial Duration (Days)
                                </label>
                                <input type="number" id="trial_days" name="trial_days" value="{{ old('trial_days', 14) }}" min="1" max="30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Payment Plans -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Select Payment Plan <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 gap-4">
                                    @foreach($paymentPlans as $plan)
                                        <label class="relative flex p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer plan-option">
                                            <input type="radio" name="payment_plan_id" value="{{ $plan->id }}" 
                                                   {{ old('payment_plan_id') == $plan->id ? 'checked' : '' }}
                                                   class="mt-1 text-blue-600 focus:ring-blue-500">
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-base font-medium text-gray-900">{{ $plan->name }}</span>
                                                    <span class="text-lg font-semibold text-blue-600">{{ $plan->formatted_price }}</span>
                                                </div>
                                                @if($plan->description)
                                                    <p class="text-sm text-gray-500 mt-1">{{ $plan->description }}</p>
                                                @endif
                                                @if($plan->features)
                                                    <div class="mt-2">
                                                        <ul class="text-sm text-gray-600 space-y-1">
                                                            @foreach($plan->features as $feature)
                                                                <li class="flex items-center">
                                                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $feature }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('payment_plan_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Address (shown when paid subscription is selected) -->
                <div id="billing-address" class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden" style="display: none;">
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Billing Address
                        </h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <label for="billing_street" class="block text-sm font-medium text-gray-700 mb-2">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="billing_street" name="billing_address[street]" value="{{ old('billing_address.street') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('billing_address.street')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_city" class="block text-sm font-medium text-gray-700 mb-2">
                                    City <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="billing_city" name="billing_address[city]" value="{{ old('billing_address.city') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('billing_address.city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="billing_state" class="block text-sm font-medium text-gray-700 mb-2">
                                    State/Province <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="billing_state" name="billing_address[state]" value="{{ old('billing_address.state') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('billing_address.state')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Postal Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="billing_postal_code" name="billing_address[postal_code]" value="{{ old('billing_address.postal_code') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('billing_address.postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="billing_country" class="block text-sm font-medium text-gray-700 mb-2">
                                    Country <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="billing_country" name="billing_address[country]" value="{{ old('billing_address.country', 'United States') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('billing_address.country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection (shown only for paid subscription) -->
                <div id="payment-method-section" class="mb-6" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Method <span class="text-red-500">*</span></label>
                    <div class="flex space-x-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="payment_method" value="stripe" checked class="text-blue-600 focus:ring-blue-500 payment-method-radio">
                            <span class="ml-2 text-sm text-gray-900">Credit/Debit Card (Stripe)</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="payment_method" value="paypal" class="text-blue-600 focus:ring-blue-500 payment-method-radio">
                            <span class="ml-2 text-sm text-gray-900">PayPal</span>
                        </label>
                    </div>
                </div>

                <!-- Stripe Card Element -->
                <div id="card-element" class="mb-4"></div>
                <input type="hidden" name="stripe_payment_method_id" id="stripe_payment_method_id">
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let stripe = Stripe("{{ config('services.stripe.key') }}");
                    let elements = stripe.elements();
                    let card = elements.create('card');
                    card.mount('#card-element');

                    let form = document.querySelector('form');
                    form.addEventListener('submit', async function(e) {
                        const type = document.querySelector('input[name="subscription_type"]:checked').value;
                        const method = document.querySelector('input[name="payment_method"]:checked')?.value;
                        if (type === 'paid' && method === 'stripe') {
                            e.preventDefault();
                            const {paymentMethod, error} = await stripe.createPaymentMethod({
                                type: 'card',
                                card: card,
                            });
                            if (error) {
                                alert(error.message);
                                return;
                            }
                            document.getElementById('stripe_payment_method_id').value = paymentMethod.id;
                            form.submit();
                        }
                    });
                });
                </script>

                <!-- PayPal Button -->
                <div id="paypal-button-container" class="mb-4" style="display:none;"></div>
                <input type="hidden" name="paypal_payment_id" id="paypal_payment_id">
                <input type="hidden" name="paypal_payer_id" id="paypal_payer_id">
                <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
                <script>
                function getSelectedPlanPrice() {
                    const selectedPlanRadio = document.querySelector('input[name="payment_plan_id"]:checked');
                    if (!selectedPlanRadio) return '0';
                    const planLabel = selectedPlanRadio.closest('label');
                    if (!planLabel) return '0';
                    const priceSpan = planLabel.querySelector('.text-lg.font-semibold.text-blue-600');
                    if (!priceSpan) return '0';
                    return priceSpan.textContent.replace(/[^\d.]/g, '');
                }

                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: { value: getSelectedPlanPrice() },
                                currency: 'USD',
                           