<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        {{-- Progress Header --}}
        <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-sm border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <img src="/images/brand/logo.svg" alt="HD Tickets" class="h-8 w-auto">
                        <span class="text-lg font-semibold text-gray-900">Account Registration</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>Step</span>
                        <span x-text="currentStep" class="font-medium text-indigo-600">1</span>
                        <span>of</span>
                        <span>4</span>
                    </div>
                </div>
                
                {{-- Progress Bar --}}
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                         :style="`width: ${(currentStep / 4) * 100}%`"></div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-8" x-data="comprehensiveRegistration()">
            {{-- Step 1: Account Type Selection --}}
            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-8" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Choose Your Account Type</h1>
                    <p class="text-lg text-gray-600">Select the account type that best fits your needs</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    {{-- Customer Account --}}
                    <div class="relative">
                        <input type="radio" id="customer" name="account_type" value="customer" x-model="form.accountType" class="sr-only">
                        <label for="customer" class="block cursor-pointer">
                            <div class="bg-white rounded-2xl shadow-lg border-2 transition-all duration-200 p-8 h-full"
                                 :class="form.accountType === 'customer' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300'">
                                
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Sports Fan</h3>
                                    <p class="text-gray-600">Perfect for individual sports enthusiasts</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">Monitor ticket prices across platforms</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">Set price drop alerts</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">Purchase tickets with ease</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-600">Monthly subscription required after 7-day free trial</span>
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="text-center">
                                        <span class="text-2xl font-bold text-gray-900">$29.99</span>
                                        <span class="text-gray-600">/month</span>
                                    </div>
                                    <p class="text-sm text-gray-500 text-center mt-1">7-day free trial</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Agent Account --}}
                    <div class="relative">
                        <input type="radio" id="agent" name="account_type" value="agent" x-model="form.accountType" class="sr-only">
                        <label for="agent" class="block cursor-pointer">
                            <div class="bg-white rounded-2xl shadow-lg border-2 transition-all duration-200 p-8 h-full"
                                 :class="form.accountType === 'agent' ? 'border-purple-500 ring-2 ring-purple-200' : 'border-gray-200 hover:border-purple-300'">
                                
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V2m0 4v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6m8 0H8"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Professional Agent</h3>
                                    <p class="text-gray-600">For ticket brokers and professionals</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700"><strong>Unlimited</strong> ticket purchases</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">Advanced monitoring tools</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">Priority customer support</span>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">No subscription required</span>
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="text-center">
                                        <span class="text-2xl font-bold text-green-600">FREE</span>
                                    </div>
                                    <p class="text-sm text-gray-500 text-center mt-1">Professional access</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button 
                        @click="nextStep()" 
                        :disabled="!form.accountType"
                        :class="form.accountType ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-8 py-3 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Continue
                    </button>
                </div>
            </div>

            {{-- Step 2: Personal Information --}}
            <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-8" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Personal Information</h1>
                    <p class="text-lg text-gray-600">Please provide your details to create your account</p>
                </div>

                <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
                    <form @submit.prevent="validatePersonalInfo" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            {{-- First Name --}}
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    x-model="form.firstName"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                    placeholder="Enter your first name"
                                >
                                <p x-show="errors.firstName" x-text="errors.firstName" class="mt-1 text-sm text-red-600"></p>
                            </div>

                            {{-- Last Name --}}
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    x-model="form.lastName"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                    placeholder="Enter your last name"
                                >
                                <p x-show="errors.lastName" x-text="errors.lastName" class="mt-1 text-sm text-red-600"></p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address *
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                x-model="form.email"
                                @blur="checkEmailAvailability"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                placeholder="Enter your email address"
                            >
                            <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                            <p x-show="emailStatus === 'checking'" class="mt-1 text-sm text-blue-600">Checking availability...</p>
                            <p x-show="emailStatus === 'available'" class="mt-1 text-sm text-green-600">✓ Email is available</p>
                        </div>

                        {{-- Phone Number (Optional) --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number (Optional)
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                x-model="form.phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                placeholder="Enter your phone number"
                            >
                            <p class="mt-1 text-sm text-gray-500">Phone verification can enhance account security</p>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password *
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password" 
                                    x-model="form.password"
                                    @input="checkPasswordStrength"
                                    required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                    placeholder="Create a strong password"
                                >
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            
                            {{-- Password Strength Indicator --}}
                            <div x-show="form.password.length > 0" class="mt-2">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-300"
                                             :class="passwordStrengthColor"
                                             :style="`width: ${passwordStrengthPercent}%`"></div>
                                    </div>
                                    <span class="text-sm font-medium" :class="passwordStrengthColor.replace('bg-', 'text-')" x-text="passwordStrengthText"></span>
                                </div>
                                <div class="mt-2 space-y-1 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" :class="form.password.length >= 8 ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span :class="form.password.length >= 8 ? 'text-green-600' : 'text-gray-500'">At least 8 characters</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" :class="/[A-Z]/.test(form.password) ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span :class="/[A-Z]/.test(form.password) ? 'text-green-600' : 'text-gray-500'">One uppercase letter</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" :class="/[0-9]/.test(form.password) ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span :class="/[0-9]/.test(form.password) ? 'text-green-600' : 'text-gray-500'">One number</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password *
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                x-model="form.passwordConfirmation"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                placeholder="Confirm your password"
                            >
                            <p x-show="form.passwordConfirmation && form.password !== form.passwordConfirmation" class="mt-1 text-sm text-red-600">Passwords do not match</p>
                        </div>
                    </form>
                </div>

                <div class="flex justify-between mt-8">
                    <button 
                        @click="previousStep()" 
                        class="px-8 py-3 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        Back
                    </button>
                    <button 
                        @click="validatePersonalInfo()" 
                        :disabled="!isPersonalInfoValid"
                        :class="isPersonalInfoValid ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-8 py-3 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Continue
                    </button>
                </div>
            </div>

            {{-- Step 3: Legal Agreements --}}
            <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-8" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Legal Agreements</h1>
                    <p class="text-lg text-gray-600">Please review and accept our terms to continue</p>
                </div>

                <div class="max-w-4xl mx-auto space-y-6">
                    {{-- Terms of Service --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Terms of Service</h3>
                                <p class="text-gray-600 mb-4">Our terms outline your rights and responsibilities when using HD Tickets.</p>
                                <div class="bg-gray-50 rounded-lg p-4 max-h-48 overflow-y-auto text-sm text-gray-700 mb-4">
                                    <p class="mb-2"><strong>Key Points:</strong></p>
                                    <ul class="space-y-1 list-disc list-inside">
                                        <li>Service provided "as-is" without warranty</li>
                                        <li>No money-back guarantee on subscriptions</li>
                                        <li>User responsible for account security</li>
                                        <li>Prohibited from automated scraping</li>
                                        <li>We may terminate accounts for violations</li>
                                    </ul>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input 
                                        type="checkbox" 
                                        id="accept_terms" 
                                        x-model="form.agreements.terms"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                    <label for="accept_terms" class="text-sm text-gray-700">
                                        I agree to the 
                                        <a href="/legal/terms" target="_blank" class="text-indigo-600 hover:text-indigo-500 underline">Terms of Service</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Privacy Policy --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Privacy Policy</h3>
                                <p class="text-gray-600 mb-4">Learn how we collect, use, and protect your personal information.</p>
                                <div class="bg-gray-50 rounded-lg p-4 max-h-48 overflow-y-auto text-sm text-gray-700 mb-4">
                                    <p class="mb-2"><strong>Data We Collect:</strong></p>
                                    <ul class="space-y-1 list-disc list-inside">
                                        <li>Account information (name, email, phone)</li>
                                        <li>Usage patterns and preferences</li>
                                        <li>Device and browser information</li>
                                        <li>Payment information (processed securely)</li>
                                        <li>Communication records</li>
                                    </ul>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input 
                                        type="checkbox" 
                                        id="accept_privacy" 
                                        x-model="form.agreements.privacy"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                    <label for="accept_privacy" class="text-sm text-gray-700">
                                        I agree to the 
                                        <a href="/legal/privacy" target="_blank" class="text-indigo-600 hover:text-indigo-500 underline">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- GDPR Consent (if applicable) --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Data Processing Consent</h3>
                                <p class="text-gray-600 mb-4">Your consent for processing personal data under GDPR.</p>
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <input 
                                            type="checkbox" 
                                            id="consent_processing" 
                                            x-model="form.agreements.dataProcessing"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                        <label for="consent_processing" class="text-sm text-gray-700">
                                            I consent to processing of my personal data for account management
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <input 
                                            type="checkbox" 
                                            id="consent_marketing" 
                                            x-model="form.agreements.marketing"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                        <label for="consent_marketing" class="text-sm text-gray-700">
                                            I consent to receive marketing communications (optional)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button 
                        @click="previousStep()" 
                        class="px-8 py-3 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        Back
                    </button>
                    <button 
                        @click="nextStep()" 
                        :disabled="!areAgreementsValid"
                        :class="areAgreementsValid ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-8 py-3 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Continue
                    </button>
                </div>
            </div>

            {{-- Step 4: Account Creation --}}
            <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-8" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Complete Registration</h1>
                    <p class="text-lg text-gray-600">Review your information and create your account</p>
                </div>

                <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
                    {{-- Registration Summary --}}
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registration Summary</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Type:</span>
                                <span class="font-medium capitalize" x-text="form.accountType"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium" x-text="`${form.firstName} ${form.lastName}`"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium" x-text="form.email"></span>
                            </div>
                            <div x-show="form.phone" class="flex justify-between">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-medium" x-text="form.phone"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Verification Options --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Options</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-blue-900">Email Verification</p>
                                        <p class="text-sm text-blue-700">Required for account activation</p>
                                    </div>
                                </div>
                                <span class="text-blue-600 font-medium">Required</span>
                            </div>

                            <div x-show="form.phone" class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-green-900">SMS Verification</p>
                                        <p class="text-sm text-green-700">Enhance account security</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        id="enable_sms" 
                                        x-model="form.enableSMSVerification"
                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                    >
                                    <label for="enable_sms" class="text-sm text-green-700">Enable</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button 
                        @click="submitRegistration()" 
                        :disabled="isSubmitting"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium py-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <span x-show="!isSubmitting" class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Create My Account
                        </span>
                        <span x-show="isSubmitting" class="flex items-center justify-center">
                            <div class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></div>
                            Creating Account...
                        </span>
                    </button>
                </div>

                <div class="flex justify-between mt-8">
                    <button 
                        @click="previousStep()" 
                        :disabled="isSubmitting"
                        class="px-8 py-3 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function comprehensiveRegistration() {
            return {
                currentStep: 1,
                isSubmitting: false,
                showPassword: false,
                emailStatus: null,
                
                form: {
                    accountType: '',
                    firstName: '',
                    lastName: '',
                    email: '',
                    phone: '',
                    password: '',
                    passwordConfirmation: '',
                    agreements: {
                        terms: false,
                        privacy: false,
                        dataProcessing: false,
                        marketing: false
                    },
                    enableSMSVerification: false
                },
                
                errors: {},
                
                get isPersonalInfoValid() {
                    return this.form.firstName && 
                           this.form.lastName && 
                           this.form.email && 
                           this.form.password && 
                           this.form.password === this.form.passwordConfirmation &&
                           this.form.password.length >= 8 &&
                           this.emailStatus === 'available';
                },
                
                get areAgreementsValid() {
                    return this.form.agreements.terms && 
                           this.form.agreements.privacy && 
                           this.form.agreements.dataProcessing;
                },
                
                get passwordStrengthPercent() {
                    let strength = 0;
                    if (this.form.password.length >= 8) strength += 25;
                    if (/[A-Z]/.test(this.form.password)) strength += 25;
                    if (/[0-9]/.test(this.form.password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(this.form.password)) strength += 25;
                    return strength;
                },
                
                get passwordStrengthText() {
                    const percent = this.passwordStrengthPercent;
                    if (percent < 50) return 'Weak';
                    if (percent < 75) return 'Good';
                    return 'Strong';
                },
                
                get passwordStrengthColor() {
                    const percent = this.passwordStrengthPercent;
                    if (percent < 50) return 'bg-red-500';
                    if (percent < 75) return 'bg-yellow-500';
                    return 'bg-green-500';
                },
                
                nextStep() {
                    if (this.currentStep < 4) {
                        this.currentStep++;
                    }
                },
                
                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },
                
                async checkEmailAvailability() {
                    if (!this.form.email || !/\S+@\S+\.\S+/.test(this.form.email)) return;
                    
                    this.emailStatus = 'checking';
                    
                    try {
                        const response = await fetch('/api/v1/auth/check-email', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ email: this.form.email })
                        });
                        
                        const data = await response.json();
                        this.emailStatus = data.exists ? 'taken' : 'available';
                        
                        if (data.exists) {
                            this.errors.email = 'This email address is already registered';
                        } else {
                            this.errors.email = '';
                        }
                    } catch (error) {
                        console.error('Email check failed:', error);
                        this.emailStatus = null;
                    }
                },
                
                validatePersonalInfo() {
                    this.errors = {};
                    
                    if (!this.form.firstName) this.errors.firstName = 'First name is required';
                    if (!this.form.lastName) this.errors.lastName = 'Last name is required';
                    if (!this.form.email) this.errors.email = 'Email is required';
                    if (!this.form.password) this.errors.password = 'Password is required';
                    if (this.form.password !== this.form.passwordConfirmation) {
                        this.errors.passwordConfirmation = 'Passwords do not match';
                    }
                    
                    if (Object.keys(this.errors).length === 0 && this.isPersonalInfoValid) {
                        this.nextStep();
                    }
                },
                
                checkPasswordStrength() {
                    // Password strength is reactive through computed properties
                },
                
                async submitRegistration() {
                    this.isSubmitting = true;
                    
                    try {
                        const response = await fetch('/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                account_type: this.form.accountType,
                                first_name: this.form.firstName,
                                last_name: this.form.lastName,
                                email: this.form.email,
                                phone: this.form.phone,
                                password: this.form.password,
                                password_confirmation: this.form.passwordConfirmation,
                                agreements: this.form.agreements,
                                enable_sms_verification: this.form.enableSMSVerification
                            })
                        });
                        
                        if (response.ok) {
                            // Registration successful - redirect to verification page
                            window.location.href = '/account/verify';
                        } else {
                            const data = await response.json();
                            console.error('Registration failed:', data);
                            // Handle validation errors
                        }
                    } catch (error) {
                        console.error('Registration error:', error);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</x-guest-layout>
