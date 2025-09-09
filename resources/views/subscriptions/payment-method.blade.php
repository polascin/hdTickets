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
                        <h1 class="text-2xl font-bold text-gray-900">Payment Method</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ isset($paymentMethod) ? 'Update your payment method' : 'Add a payment method to your account' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="paymentMethodManager()">
            {{-- Current Payment Method (if exists) --}}
            @if(isset($paymentMethod))
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Payment Method</h2>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                @if(strtolower($paymentMethod->brand ?? '') === 'visa')
                                    <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                        <rect width="36" height="24" rx="4" fill="#1A73E8"/>
                                        <text x="18" y="14" font-family="Arial" font-size="6" fill="white" text-anchor="middle">VISA</text>
                                    </svg>
                                @elseif(strtolower($paymentMethod->brand ?? '') === 'mastercard')
                                    <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                        <rect width="36" height="24" rx="4" fill="#EB001B"/>
                                        <circle cx="14" cy="12" r="7" fill="#EB001B"/>
                                        <circle cx="22" cy="12" r="7" fill="#FF5F00"/>
                                        <circle cx="18" cy="12" r="7" fill="#F79E1B"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">•••• •••• •••• {{ $paymentMethod->last_four ?? '1234' }}</div>
                                <div class="text-sm text-gray-500">{{ ucfirst($paymentMethod->brand ?? 'Card') }} • Expires {{ $paymentMethod->exp_month ?? '12' }}/{{ $paymentMethod->exp_year ?? '25' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Active</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex space-x-3">
                        <button @click="showUpdateForm = true" 
                                class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                            Update Card
                        </button>
                        <button @click="showRemoveConfirmation = true" 
                                class="flex-1 bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition-colors">
                            Remove Card
                        </button>
                    </div>
                </div>
            @endif

            {{-- Add/Update Payment Method Form --}}
            <div class="bg-white rounded-2xl shadow-lg p-6" 
                 x-show="{{ !isset($paymentMethod) ? 'true' : 'showUpdateForm' }}">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">
                    {{ isset($paymentMethod) ? 'Update Payment Method' : 'Add Payment Method' }}
                </h2>
                
                <form @submit.prevent="processPaymentMethod()" id="payment-method-form">
                    {{-- Cardholder Information --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Cardholder Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="cardholder_name" class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                                <input type="text" 
                                       id="cardholder_name" 
                                       x-model="cardholderInfo.name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="Full name on card"
                                       required>
                            </div>
                            <div>
                                <label for="cardholder_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" 
                                       id="cardholder_email" 
                                       x-model="cardholderInfo.email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       required>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Address --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Billing Address</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                <input type="text" 
                                       id="address_line1" 
                                       x-model="billingAddress.line1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       required>
                            </div>
                            <div>
                                <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
                                <input type="text" 
                                       id="address_line2" 
                                       x-model="billingAddress.line2"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" 
                                           id="city" 
                                           x-model="billingAddress.city"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           required>
                                </div>
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                    <input type="text" 
                                           id="state" 
                                           x-model="billingAddress.state"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           required>
                                </div>
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">ZIP/Postal Code</label>
                                    <input type="text" 
                                           id="postal_code" 
                                           x-model="billingAddress.postalCode"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           required>
                                </div>
                            </div>
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select id="country" 
                                        x-model="billingAddress.country"
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

                    {{-- Card Details --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Card Details</h3>
                        
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
                                    <text x="18" y="14" font-family="Arial" font-size="6" fill="white" text-anchor="middle">VISA</text>
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
                                <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                    <rect width="36" height="24" rx="4" fill="#0079df"/>
                                    <text x="18" y="14" font-family="Arial" font-size="5" fill="white" text-anchor="middle">AMEX</text>
                                </svg>
                            </div>
                            <span>Secured by Stripe</span>
                        </div>
                    </div>

                    {{-- Save as Default --}}
                    <div class="mb-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   x-model="setAsDefault"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Set as default payment method for future subscriptions</span>
                        </label>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex space-x-3">
                        @if(isset($paymentMethod))
                            <button type="button" @click="showUpdateForm = false" 
                                    class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                        @endif
                        <button type="submit" 
                                :disabled="isProcessing"
                                class="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isProcessing" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ isset($paymentMethod) ? 'Update Payment Method' : 'Add Payment Method' }}
                            </span>
                            <span x-show="isProcessing" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Remove Payment Method Confirmation Modal --}}
            <div x-show="showRemoveConfirmation" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6" @click.stop>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Remove Payment Method?</h3>
                        <p class="text-gray-600">Are you sure you want to remove this payment method? This action cannot be undone.</p>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-red-800 mb-2">Important:</h4>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li>• Your subscription will be paused if no other payment method is available</li>
                            <li>• You'll need to add a new payment method to continue service</li>
                            <li>• Any scheduled payments will fail until a new method is added</li>
                        </ul>
                    </div>

                    <div class="flex space-x-3">
                        <button @click="showRemoveConfirmation = false" 
                                class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Keep Payment Method
                        </button>
                        <button @click="confirmRemovePaymentMethod()" 
                                :disabled="isRemoving"
                                class="flex-1 bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors disabled:opacity-50">
                            <span x-show="!isRemoving">Remove Payment Method</span>
                            <span x-show="isRemoving">Removing...</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Security Notice --}}
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 mt-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Your Payment Information is Secure</p>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li>• All payment data is encrypted using industry-standard SSL</li>
                            <li>• We never store your complete credit card number</li>
                            <li>• Payment processing is handled securely by Stripe</li>
                            <li>• Your data is protected by PCI DSS compliance standards</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stripe.js --}}
    <script src="https://js.stripe.com/v3/"></script>
    
    <script>
        function paymentMethodManager() {
            return {
                showUpdateForm: {{ isset($paymentMethod) ? 'false' : 'true' }},
                showRemoveConfirmation: false,
                isProcessing: false,
                isRemoving: false,
                setAsDefault: true,
                
                cardholderInfo: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}'
                },
                
                billingAddress: {
                    line1: '',
                    line2: '',
                    city: '',
                    state: '',
                    postalCode: '',
                    country: 'US'
                },
                
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

                async processPaymentMethod() {
                    if (this.isProcessing) return;
                    
                    this.isProcessing = true;
                    
                    try {
                        // Create payment method with Stripe
                        const {paymentMethod, error} = await this.stripe.createPaymentMethod({
                            type: 'card',
                            card: this.cardElement,
                            billing_details: {
                                name: this.cardholderInfo.name,
                                email: this.cardholderInfo.email,
                                address: {
                                    line1: this.billingAddress.line1,
                                    line2: this.billingAddress.line2,
                                    city: this.billingAddress.city,
                                    state: this.billingAddress.state,
                                    postal_code: this.billingAddress.postalCode,
                                    country: this.billingAddress.country,
                                }
                            },
                        });

                        if (error) {
                            document.getElementById('card-errors').textContent = error.message;
                            this.isProcessing = false;
                            return;
                        }

                        // Submit to backend
                        const response = await fetch('/api/v1/subscriptions/payment-method', {
                            method: '{{ isset($paymentMethod) ? "PUT" : "POST" }}',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                payment_method_id: paymentMethod.id,
                                set_as_default: this.setAsDefault,
                                billing_address: this.billingAddress
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Payment method {{ isset($paymentMethod) ? "updated" : "added" }} successfully!', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("subscriptions.dashboard") }}';
                            }, 1500);
                        } else {
                            this.showError(data.message || 'Failed to process payment method. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error processing payment method:', error);
                        this.showError('An error occurred. Please try again.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async confirmRemovePaymentMethod() {
                    if (this.isRemoving) return;
                    
                    this.isRemoving = true;
                    
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
                            setTimeout(() => {
                                window.location.href = '{{ route("subscriptions.dashboard") }}';
                            }, 1500);
                        } else {
                            this.showError(data.message || 'Failed to remove payment method.');
                            this.showRemoveConfirmation = false;
                        }
                    } catch (error) {
                        console.error('Error removing payment method:', error);
                        this.showError('An error occurred. Please try again.');
                        this.showRemoveConfirmation = false;
                    } finally {
                        this.isRemoving = false;
                    }
                },

                showError(message) {
                    document.getElementById('card-errors').textContent = message;
                    
                    // Scroll to error message
                    document.getElementById('card-errors').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
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
