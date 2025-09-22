@extends('layouts.modern-app')

@section('title', 'Purchase Tickets')

@section('meta_description', 'Complete your sports ticket purchase with secure payment processing and seat selection')

@push('head')
    <script src="https://js.stripe.com/v3/"></script>
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Purchase Tickets 🎫
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Secure your seats with our streamlined checkout process
            </p>
        </div>
        
        <!-- Progress Steps -->
        <div class="flex items-center space-x-4 text-sm">
            <template x-data="{currentStep: 1}">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-medium">1</div>
                    <span class="text-gray-900 dark:text-gray-100 font-medium">Selection</span>
                </div>
                <div class="w-8 h-px bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center text-xs font-medium">2</div>
                    <span class="text-gray-500 dark:text-gray-400">Details</span>
                </div>
                <div class="w-8 h-px bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center text-xs font-medium">3</div>
                    <span class="text-gray-500 dark:text-gray-400">Payment</span>
                </div>
            </template>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="purchaseWizard()" x-init="init()" class="max-w-7xl mx-auto">
        
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-2"
                                 :class="currentStep === index + 1 ? 'text-blue-600 dark:text-blue-400' : currentStep > index + 1 ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="currentStep === index + 1 ? 'bg-blue-500 text-white' : currentStep > index + 1 ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'">
                                    <template x-if="currentStep > index + 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="currentStep <= index + 1">
                                        <span x-text="index + 1"></span>
                                    </template>
                                </div>
                                <span class="font-medium" x-text="step.name"></span>
                            </div>
                            <template x-if="index < steps.length - 1">
                                <div class="w-12 h-px bg-gray-300 dark:bg-gray-600 mx-4"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                     :style="`width: ${(currentStep / steps.length) * 100}%`"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Step 1: Ticket Selection -->
                <div x-show="currentStep === 1" x-transition class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select Your Tickets</h3>
                        <span class="hdt-badge hdt-badge--info hdt-badge--sm" x-text="selectedEvent.availableTickets + ' available'"></span>
                    </div>
                    <div class="hdt-card__body">
                        
                        <!-- Event Details -->
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
                            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="selectedEvent.title"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="selectedEvent.venue + ' • ' + selectedEvent.date + ' • ' + selectedEvent.time"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedEvent.platform"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">$<span x-text="selectedEvent.basePrice"></span></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Starting price</p>
                            </div>
                        </div>

                        <!-- Seat Selection -->
                        <div class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Choose Your Seats</h4>
                            
                            <!-- Seat Map Placeholder -->
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                                <div class="text-center mb-4">
                                    <div class="inline-block px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300">
                                        🎪 STAGE / FIELD
                                    </div>
                                </div>
                                
                                <!-- Seat Grid -->
                                <div class="grid grid-cols-12 gap-1 max-w-2xl mx-auto">
                                    <template x-for="seat in availableSeats" :key="seat.id">
                                        <button @click="toggleSeat(seat)"
                                                class="w-6 h-6 rounded text-xs flex items-center justify-center transition-colors"
                                                :class="getSeatClass(seat)"
                                                :disabled="seat.status === 'taken'"
                                                :title="`Section ${seat.section}, Row ${seat.row}, Seat ${seat.number} - $${seat.price}`">
                                            <span x-text="seat.number"></span>
                                        </button>
                                    </template>
                                </div>
                                
                                <!-- Legend -->
                                <div class="flex items-center justify-center space-x-6 mt-6 text-xs">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Available</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Selected</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-gray-400 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400">Taken</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity and Price Selector -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="hdt-form-group">
                                    <label class="hdt-label">Number of Tickets</label>
                                    <select x-model="ticketQuantity" @change="updatePricing()" class="hdt-input">
                                        <option value="1">1 Ticket</option>
                                        <option value="2">2 Tickets</option>
                                        <option value="3">3 Tickets</option>
                                        <option value="4">4 Tickets</option>
                                        <option value="6">6 Tickets</option>
                                        <option value="8">8 Tickets</option>
                                    </select>
                                </div>
                                
                                <div class="hdt-form-group">
                                    <label class="hdt-label">Ticket Type</label>
                                    <select x-model="selectedTicketType" @change="updatePricing()" class="hdt-input">
                                        <template x-for="type in ticketTypes" :key="type.id">
                                            <option :value="type.id" x-text="`${type.name} - $${type.price}`"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 1 Actions -->
                    <div class="hdt-card__footer">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Selected: <span class="font-medium text-gray-900 dark:text-gray-100" x-text="selectedSeats.length + ' seats'"></span>
                                </p>
                            </div>
                            <button @click="nextStep()" 
                                    :disabled="selectedSeats.length === 0"
                                    class="hdt-button hdt-button--primary hdt-button--md"
                                    :class="selectedSeats.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                Continue to Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Customer Details -->
                <div x-show="currentStep === 2" x-transition class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Information</h3>
                    </div>
                    <div class="hdt-card__body">
                        <form @submit.prevent="nextStep()" class="space-y-6">
                            
                            <!-- Personal Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="hdt-form-group">
                                    <label class="hdt-label required">First Name</label>
                                    <input type="text" x-model="customerInfo.firstName" required class="hdt-input">
                                </div>
                                
                                <div class="hdt-form-group">
                                    <label class="hdt-label required">Last Name</label>
                                    <input type="text" x-model="customerInfo.lastName" required class="hdt-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="hdt-form-group">
                                    <label class="hdt-label required">Email Address</label>
                                    <input type="email" x-model="customerInfo.email" required class="hdt-input">
                                </div>
                                
                                <div class="hdt-form-group">
                                    <label class="hdt-label required">Phone Number</label>
                                    <input type="tel" x-model="customerInfo.phone" required class="hdt-input">
                                </div>
                            </div>

                            <!-- Delivery Method -->
                            <div class="hdt-form-group">
                                <label class="hdt-label">Ticket Delivery Method</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                                    <template x-for="method in deliveryMethods" :key="method.id">
                                        <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                               :class="customerInfo.deliveryMethod === method.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" :value="method.id" x-model="customerInfo.deliveryMethod" class="sr-only">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="method.icon"></svg>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="method.name"></span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="method.description"></p>
                                                <p class="text-sm font-medium text-green-600 dark:text-green-400" x-text="method.price === 0 ? 'Free' : '$' + method.price"></p>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            <div class="hdt-form-group">
                                <label class="hdt-label">Special Requests or Notes</label>
                                <textarea x-model="customerInfo.specialRequests" 
                                          rows="3" 
                                          class="hdt-input" 
                                          placeholder="Any accessibility needs, seating preferences, or other requests..."></textarea>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Step 2 Actions -->
                    <div class="hdt-card__footer">
                        <div class="flex justify-between items-center">
                            <button @click="prevStep()" class="hdt-button hdt-button--outline hdt-button--md">
                                Back to Selection
                            </button>
                            <button @click="nextStep()" 
                                    :disabled="!isCustomerInfoValid()"
                                    class="hdt-button hdt-button--primary hdt-button--md"
                                    :class="!isCustomerInfoValid() ? 'opacity-50 cursor-not-allowed' : ''">
                                Continue to Payment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div x-show="currentStep === 3" x-transition class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment Information</h3>
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-sm text-green-600 dark:text-green-400">Secure SSL Encrypted</span>
                        </div>
                    </div>
                    <div class="hdt-card__body">
                        
                        <!-- Price Lock Timer -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Price Lock Active</p>
                                    <p class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Your seats are reserved for <span class="font-medium" x-text="priceLockTime"></span> minutes
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="space-y-6">
                            <div class="hdt-form-group">
                                <label class="hdt-label">Payment Method</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                           :class="paymentMethod === 'card' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                        <input type="radio" value="card" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">Credit/Debit Card</span>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                           :class="paymentMethod === 'paypal' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                        <input type="radio" value="paypal" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.364-.53c-.61-.66-1.474-1.01-2.56-1.01H9.216c-.524 0-.968.382-1.05.9L7.044 12.8c-.082.518.281.95.802.95h2.35c4.298 0 7.664-1.747 8.647-6.797.03-.148.054-.292.077-.437.292-1.868-.002-3.138-1.012-4.288z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">PayPal</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Card Payment Form -->
                            <div x-show="paymentMethod === 'card'" x-transition class="space-y-4">
                                <div class="hdt-form-group">
                                    <label class="hdt-label required">Card Number</label>
                                    <div id="card-number-element" class="hdt-input h-12 flex items-center">
                                        <!-- Stripe Elements will create input field here -->
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="hdt-form-group">
                                        <label class="hdt-label required">Expiry Date</label>
                                        <div id="card-expiry-element" class="hdt-input h-12 flex items-center">
                                            <!-- Stripe Elements will create input field here -->
                                        </div>
                                    </div>
                                    
                                    <div class="hdt-form-group">
                                        <label class="hdt-label required">CVC</label>
                                        <div id="card-cvc-element" class="hdt-input h-12 flex items-center">
                                            <!-- Stripe Elements will create input field here -->
                                        </div>
                                    </div>
                                </div>

                                <div class="hdt-form-group">
                                    <label class="hdt-label required">Cardholder Name</label>
                                    <input type="text" x-model="paymentInfo.cardholderName" required class="hdt-input">
                                </div>
                            </div>

                            <!-- PayPal Notice -->
                            <div x-show="paymentMethod === 'paypal'" x-transition class="text-center p-8 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                <svg class="w-16 h-16 text-blue-600 mx-auto mb-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.364-.53c-.61-.66-1.474-1.01-2.56-1.01H9.216c-.524 0-.968.382-1.05.9L7.044 12.8c-.082.518.281.95.802.95h2.35c4.298 0 7.664-1.747 8.647-6.797.03-.148.054-.292.077-.437.292-1.868-.002-3.138-1.012-4.288z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">Complete Payment with PayPal</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">You'll be redirected to PayPal to complete your purchase securely</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3 Actions -->
                    <div class="hdt-card__footer">
                        <div class="flex justify-between items-center">
                            <button @click="prevStep()" class="hdt-button hdt-button--outline hdt-button--md">
                                Back to Details
                            </button>
                            <button @click="processPayment()" 
                                    :disabled="processing"
                                    class="hdt-button hdt-button--primary hdt-button--md flex items-center space-x-2"
                                    :class="processing ? 'opacity-50 cursor-not-allowed' : ''">
                                <template x-if="processing">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="processing ? 'Processing...' : 'Complete Purchase'"></span>
                                <span x-text="'$' + totalAmount.toFixed(2)"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="hdt-card sticky top-6">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order Summary</h3>
                    </div>
                    <div class="hdt-card__body space-y-4">
                        
                        <!-- Event Info -->
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100" x-text="selectedEvent.title"></h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="selectedEvent.venue"></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="selectedEvent.date + ' • ' + selectedEvent.time"></p>
                        </div>

                        <!-- Selected Seats -->
                        <div x-show="selectedSeats.length > 0" class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Selected Seats</h5>
                            <div class="space-y-1">
                                <template x-for="seat in selectedSeats" :key="seat.id">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400" x-text="`Section ${seat.section}, Row ${seat.row}, Seat ${seat.number}`"></span>
                                        <span class="text-gray-900 dark:text-gray-100">$<span x-text="seat.price"></span></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Tickets (<span x-text="ticketQuantity"></span>)</span>
                                <span class="text-gray-900 dark:text-gray-100">$<span x-text="subtotal.toFixed(2)"></span></span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Service Fee</span>
                                <span class="text-gray-900 dark:text-gray-100">$<span x-text="serviceFee.toFixed(2)"></span></span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Processing Fee</span>
                                <span class="text-gray-900 dark:text-gray-100">$<span x-text="processingFee.toFixed(2)"></span></span>
                            </div>
                            
                            <div x-show="deliveryFee > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Delivery Fee</span>
                                <span class="text-gray-900 dark:text-gray-100">$<span x-text="deliveryFee.toFixed(2)"></span></span>
                            </div>
                            
                            <div class="flex justify-between text-sm text-green-600 dark:text-green-400" x-show="discount > 0">
                                <span>Discount</span>
                                <span>-$<span x-text="discount.toFixed(2)"></span></span>
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total</span>
                                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">$<span x-text="totalAmount.toFixed(2)"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseWizard', () => ({
                currentStep: 1,
                processing: false,
                priceLockTime: 15,
                
                steps: [
                    { name: 'Selection', description: 'Choose seats and quantity' },
                    { name: 'Details', description: 'Customer information' },
                    { name: 'Payment', description: 'Secure checkout' }
                ],
                
                selectedEvent: {
                    id: 1,
                    title: 'Los Angeles Lakers vs Golden State Warriors',
                    venue: 'Crypto.com Arena',
                    date: 'December 25, 2024',
                    time: '8:00 PM',
                    platform: 'Ticketmaster',
                    basePrice: 175,
                    availableTickets: 8
                },
                
                ticketQuantity: 2,
                selectedTicketType: 'standard',
                selectedSeats: [],
                
                ticketTypes: [
                    { id: 'standard', name: 'Standard Admission', price: 175 },
                    { id: 'premium', name: 'Premium Seating', price: 245 },
                    { id: 'vip', name: 'VIP Experience', price: 399 }
                ],
                
                availableSeats: [],
                
                customerInfo: {
                    firstName: '',
                    lastName: '',
                    email: '',
                    phone: '',
                    deliveryMethod: 'digital',
                    specialRequests: ''
                },
                
                deliveryMethods: [
                    {
                        id: 'digital',
                        name: 'Digital Delivery',
                        description: 'Instant email delivery',
                        price: 0,
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'
                    },
                    {
                        id: 'mobile',
                        name: 'Mobile Tickets',
                        description: 'Access via mobile app',
                        price: 0,
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
                    },
                    {
                        id: 'mail',
                        name: 'Physical Mail',
                        description: '5-7 business days',
                        price: 15,
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7m16 0L4 7"/>'
                    }
                ],
                
                paymentMethod: 'card',
                paymentInfo: {
                    cardholderName: ''
                },
                
                async init() {
                    this.generateSeats();
                    this.startPriceLockTimer();
                    this.initializeStripe();
                },
                
                generateSeats() {
                    // Generate sample seats
                    this.availableSeats = [];
                    for (let section = 1; section <= 3; section++) {
                        for (let row = 1; row <= 4; row++) {
                            for (let seat = 1; seat <= 12; seat++) {
                                const basePrice = this.selectedEvent.basePrice;
                                const sectionMultiplier = section === 1 ? 1.5 : section === 2 ? 1.2 : 1;
                                const price = Math.round(basePrice * sectionMultiplier);
                                
                                this.availableSeats.push({
                                    id: `${section}-${row}-${seat}`,
                                    section: section,
                                    row: row,
                                    number: seat,
                                    price: price,
                                    status: Math.random() > 0.7 ? 'taken' : 'available',
                                    selected: false
                                });
                            }
                        }
                    }
                },
                
                toggleSeat(seat) {
                    if (seat.status === 'taken') return;
                    
                    if (seat.selected) {
                        seat.selected = false;
                        this.selectedSeats = this.selectedSeats.filter(s => s.id !== seat.id);
                    } else {
                        if (this.selectedSeats.length < this.ticketQuantity) {
                            seat.selected = true;
                            this.selectedSeats.push(seat);
                        }
                    }
                    this.updatePricing();
                },
                
                getSeatClass(seat) {
                    if (seat.status === 'taken') {
                        return 'bg-gray-400 text-white cursor-not-allowed';
                    } else if (seat.selected) {
                        return 'bg-blue-500 text-white border-2 border-blue-600';
                    } else {
                        return 'bg-green-500 text-white hover:bg-green-600 cursor-pointer';
                    }
                },
                
                updatePricing() {
                    // Recalculate totals when seats change
                },
                
                get subtotal() {
                    return this.selectedSeats.reduce((total, seat) => total + seat.price, 0);
                },
                
                get serviceFee() {
                    return this.subtotal * 0.1;
                },
                
                get processingFee() {
                    return this.paymentMethod === 'card' ? 2.95 : 0;
                },
                
                get deliveryFee() {
                    const method = this.deliveryMethods.find(m => m.id === this.customerInfo.deliveryMethod);
                    return method ? method.price : 0;
                },
                
                get discount() {
                    return 0; // Could apply promo codes here
                },
                
                get totalAmount() {
                    return this.subtotal + this.serviceFee + this.processingFee + this.deliveryFee - this.discount;
                },
                
                nextStep() {
                    if (this.currentStep < this.steps.length) {
                        this.currentStep++;
                    }
                },
                
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },
                
                isCustomerInfoValid() {
                    return this.customerInfo.firstName && 
                           this.customerInfo.lastName && 
                           this.customerInfo.email && 
                           this.customerInfo.phone;
                },
                
                startPriceLockTimer() {
                    const timer = setInterval(() => {
                        this.priceLockTime--;
                        if (this.priceLockTime <= 0) {
                            clearInterval(timer);
                            // Handle price lock expiration
                        }
                    }, 60000); // Update every minute
                },
                
                initializeStripe() {
                    // Initialize Stripe Elements (mock implementation)
                    console.log('Stripe Elements initialized');
                },
                
                async processPayment() {
                    this.processing = true;
                    
                    try {
                        // Simulate payment processing
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        
                        // Redirect to success page
                        window.location.href = '/purchases/success?order=' + Math.random().toString(36).substr(2, 9);
                    } catch (error) {
                        console.error('Payment failed:', error);
                        alert('Payment failed. Please try again.');
                    } finally {
                        this.processing = false;
                    }
                }
            }));
        });
    </script>
@endsection