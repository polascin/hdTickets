@extends('layouts.guest-v3')

@section('title', 'Create Account - HD Tickets')
@section('description',
  'Register for HD Tickets - Professional sports event ticket monitoring platform. 7-day free
  trial, subscription-based access.')

@section('content')
  <div
    class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-2xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-12">
          <a href="{{ route('home') }}" class="inline-flex items-center space-x-3 mb-6">
            <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="w-10 h-10 rounded-lg">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">HD Tickets</span>
          </a>

          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Create Your Account
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            Join HD Tickets and start monitoring sports events
          </p>

          <!-- Features badges -->
          <div class="flex justify-center space-x-4 mt-4 text-sm">
            <div class="flex items-center text-emerald-600">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              7-day free trial
            </div>
            <div class="flex items-center text-blue-600">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              GDPR compliant
            </div>
          </div>
        </div>

        <!-- Registration Card -->
        <div
          class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-200 dark:border-gray-700 p-8 lg:p-12">

          <!-- OAuth Options -->
          @php
            $oauthService = app('App\Services\OAuthUserService');
            $providers = $oauthService->getSupportedProviders();
          @endphp

          @if (collect($providers)->where('enabled', true)->isNotEmpty())
            <div class="space-y-4 mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-4">Quick Registration</h3>
              @foreach ($providers as $provider => $config)
                @if ($config['enabled'])
                  <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}"
                    class="w-full flex justify-center items-center px-6 py-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-base font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-blue-300 dark:hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md">
                    @if ($provider === 'google')
                      <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                          d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                          d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                          d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                          d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                      </svg>
                    @else
                      <i class="{{ $config['icon'] }} text-lg mr-3"></i>
                    @endif
                    Continue with {{ $config['name'] }}
                  </a>
                @endif
              @endforeach
            </div>

            <div class="relative my-8">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">Or register with
                  email</span>
              </div>
            </div>
          @endif

          <!-- Registration Form -->
          <form method="POST" action="{{ route('register.public.store') }}" x-data="registrationForm()"
            @submit.prevent="submitForm()" novalidate>
            @csrf

            <!-- Honeypot field -->
            <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off" />

            <!-- Personal Information -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Personal Information</h3>
              <div class="space-y-4">
                <!-- First Name -->
                <div>
                  <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    First Name <span class="text-red-500">*</span>
                  </label>
                  <input type="text" id="first_name" name="first_name" x-model="form.first_name"
                    @blur="touched.first_name = true" required autocomplete="given-name" placeholder="John"
                    class="w-full px-4 py-4 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                  @error('first_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Last Name -->
                <div>
                  <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Last Name <span class="text-red-500">*</span>
                  </label>
                  <input type="text" id="last_name" name="last_name" x-model="form.last_name"
                    @blur="touched.last_name = true" required autocomplete="family-name" placeholder="Doe"
                    class="w-full px-4 py-4 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                  @error('last_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Address <span class="text-red-500">*</span>
                  </label>
                  <input type="email" id="email" name="email" x-model="form.email"
                    @blur="touched.email = true; checkEmailAvailability()" required autocomplete="email"
                    placeholder="you@example.com"
                    class="w-full px-4 py-4 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                  @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                  <div x-show="emailStatus" class="mt-1">
                    <p x-show="emailStatus === 'checking'" class="text-sm text-gray-500">Checking availability...</p>
                    <p x-show="emailStatus === 'available'" class="text-sm text-green-600">✓ Email available</p>
                    <p x-show="emailStatus === 'taken'" class="text-sm text-red-600">✗ Email already registered</p>
                  </div>
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Phone Number <span class="text-gray-400">(optional)</span>
                  </label>
                  <input type="tel" id="phone" name="phone" x-model="form.phone"
                    @blur="touched.phone = true" autocomplete="tel" placeholder="+1234567890"
                    class="w-full px-4 py-4 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Include country code for SMS notifications</p>
                  @error('phone')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Password -->
                <div x-data="{ showPassword: false }">
                  <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Password <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                      x-model="form.password" @input="checkPasswordStrength()" @blur="touched.password = true" required
                      autocomplete="new-password" placeholder="Create a strong password"
                      class="w-full px-4 py-4 pr-12 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                    <button type="button" @click="showPassword = !showPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center">
                      <svg x-show="!showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg x-show="showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                      </svg>
                    </button>
                  </div>
                  @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror

                  <!-- Password Strength Indicator -->
                  <div x-show="form.password.length > 0" class="mt-2">
                    <div class="flex items-center space-x-2">
                      <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300"
                          :class="{
                              'bg-red-500 w-1/4': passwordStrength === 'weak',
                              'bg-yellow-500 w-2/4': passwordStrength === 'medium',
                              'bg-green-500 w-3/4': passwordStrength === 'strong',
                              'bg-green-600 w-full': passwordStrength === 'very-strong'
                          }">
                        </div>
                      </div>
                      <span class="text-xs font-medium min-w-[60px]"
                        :class="{
                            'text-red-600': passwordStrength === 'weak',
                            'text-yellow-600': passwordStrength === 'medium',
                            'text-green-600': passwordStrength === 'strong',
                            'text-green-700': passwordStrength === 'very-strong'
                        }"
                        x-text="passwordStrength.charAt(0).toUpperCase() + passwordStrength.slice(1)"></span>
                    </div>
                    <div x-show="passwordRequirements.length > 0" class="mt-1">
                      <p class="text-xs text-gray-600 dark:text-gray-400">Required:</p>
                      <ul class="text-xs text-gray-600 dark:text-gray-400 list-disc list-inside ml-2">
                        <template x-for="requirement in passwordRequirements">
                          <li x-text="requirement"></li>
                        </template>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Password Confirmation -->
                <div x-data="{ showPasswordConfirm: false }">
                  <label for="password_confirmation"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Confirm Password <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input :type="showPasswordConfirm ? 'text' : 'password'" id="password_confirmation"
                      name="password_confirmation" x-model="form.password_confirmation"
                      @blur="touched.password_confirmation = true" required autocomplete="new-password"
                      placeholder="Confirm your password"
                      class="w-full px-4 py-4 pr-12 text-base border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                    <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center">
                      <svg x-show="!showPasswordConfirm" class="w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg x-show="showPasswordConfirm" class="w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                      </svg>
                    </button>
                  </div>
                  @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                  <div x-show="touched.password_confirmation && form.password_confirmation" class="mt-1">
                    <p x-show="form.password !== form.password_confirmation" class="text-sm text-red-600">✗ Passwords do
                      not match</p>
                    <p x-show="form.password === form.password_confirmation && form.password_confirmation.length > 0"
                      class="text-sm text-green-600">✓ Passwords match</p>
                  </div>
                </div>
              </div>

              <!-- Legal Acceptances -->
              <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Terms & Conditions</h3>

                <!-- Terms of Service -->
                <div class="space-y-4">
                  <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="accept_terms" x-model="form.accept_terms" required
                      class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                      I agree to the
                      <a href="{{ route('legal.terms-of-service') }}" target="_blank"
                        class="text-blue-600 hover:text-blue-500 underline">
                        Terms of Service
                      </a> and
                      <a href="{{ route('legal.privacy-policy') }}" target="_blank"
                        class="text-blue-600 hover:text-blue-500 underline">
                        Privacy Policy
                      </a>
                    </span>
                  </label>
                  @error('accept_terms')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror

                  <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="marketing_opt_in" x-model="form.marketing_opt_in"
                      class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                      I would like to receive marketing emails about new features and ticket opportunities
                    </span>
                  </label>
                </div>
              </div>

              <!-- Security Options -->
              <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Security Options</h3>

                <label class="flex items-start space-x-3 cursor-pointer">
                  <input type="checkbox" name="enable_2fa" x-model="form.enable_2fa"
                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Enable Two-Factor Authentication
                      (2FA)</span>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                      Add an extra layer of security to your account with Google Authenticator or similar apps.
                    </p>
                  </div>
                </label>
              </div>

              <!-- Important Notice -->
              <div
                class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd" />
                  </svg>
                  <div>
                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notice</h4>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                      This service is provided "as-is" with no warranty or money-back guarantee. You will receive a 7-day
                      free trial, after which subscription fees apply.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="mt-8">
                <button type="submit" :disabled="!isFormValid() || submitting"
                  :class="isFormValid() && !submitting ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' :
                      'bg-gray-400 cursor-not-allowed'"
                  class="w-full flex justify-center items-center py-3 px-4 rounded-xl text-white font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors">
                  <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                      stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span x-text="submitting ? 'Creating Account...' : 'Create My Account'"></span>
                </button>
              </div>

              <!-- Login Link -->
              <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Already have an account?
                  <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">Sign in here</a>
                </p>
              </div>
          </form>
        </div>

        <!-- Features Section -->
        <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-700">
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 text-center mb-6">What you get with HD Tickets
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="text-gray-600 dark:text-gray-400">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-3 bg-blue-50 dark:bg-blue-900/20">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 class="font-medium text-sm text-gray-700 dark:text-gray-300">Real-Time Monitoring</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Track prices across 50+ platforms</p>
            </div>
            <div class="text-gray-600 dark:text-gray-400">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-3 bg-emerald-50 dark:bg-emerald-900/20">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5 5v-5zM9 15v-1.5A2.5 2.5 0 0111.5 11h1A2.5 2.5 0 0115 13.5V15" />
                </svg>
              </div>
              <h3 class="font-medium text-sm text-gray-700 dark:text-gray-300">Automated Alerts</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified when prices drop</p>
            </div>
            <div class="text-gray-600 dark:text-gray-400">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-3 bg-purple-50 dark:bg-purple-900/20">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h3 class="font-medium text-sm text-gray-700 dark:text-gray-300">Enterprise Security</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">GDPR compliant with 2FA support</p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script>
      function registrationForm() {
        return {
          submitting: false,
          emailStatus: null, // null, 'checking', 'available', 'taken'
          passwordStrength: 'weak',
          passwordRequirements: [],
          touched: {
            first_name: false,
            last_name: false,
            email: false,
            phone: false,
            password: false,
            password_confirmation: false
          },
          form: {
            first_name: @json(old('first_name', '')),
            last_name: @json(old('last_name', '')),
            email: @json(old('email', '')),
            phone: @json(old('phone', '')),
            password: '',
            password_confirmation: '',
            accept_terms: @json(old('accept_terms', false)),
            marketing_opt_in: @json(old('marketing_opt_in', false)),
            enable_2fa: @json(old('enable_2fa', false))
          },

          isFormValid() {
            return this.form.first_name.length > 0 &&
              this.form.last_name.length > 0 &&
              this.form.email.length > 0 &&
              this.form.password.length >= 8 &&
              this.form.password === this.form.password_confirmation &&
              this.form.accept_terms &&
              this.emailStatus !== 'taken';
          },

          async checkEmailAvailability() {
            if (!this.form.email || this.form.email.length < 3) {
              this.emailStatus = null;
              return;
            }

            this.emailStatus = 'checking';

            try {
              const response = await fetch('/register/public/check-email', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  email: this.form.email
                })
              });

              const data = await response.json();
              this.emailStatus = data.available ? 'available' : 'taken';
            } catch (error) {
              console.error('Email check failed:', error);
              this.emailStatus = null;
            }
          },

          checkPasswordStrength() {
            const password = this.form.password;
            const requirements = [];
            let score = 0;

            if (password.length < 8) {
              requirements.push('At least 8 characters');
            } else {
              score += 25;
            }

            if (!/[a-z]/.test(password)) {
              requirements.push('One lowercase letter');
            } else {
              score += 25;
            }

            if (!/[A-Z]/.test(password)) {
              requirements.push('One uppercase letter');
            } else {
              score += 25;
            }

            if (!/\d/.test(password)) {
              requirements.push('One number');
            } else {
              score += 25;
            }

            if (!/[^\w\s]/.test(password)) {
              requirements.push('One special character');
            } else {
              score += 25;
            }

            this.passwordRequirements = requirements;

            if (score < 50) {
              this.passwordStrength = 'weak';
            } else if (score < 75) {
              this.passwordStrength = 'medium';
            } else if (score < 100) {
              this.passwordStrength = 'strong';
            } else {
              this.passwordStrength = 'very-strong';
            }
          },

          async submitForm() {
            if (!this.isFormValid() || this.submitting) {
              return;
            }

            this.submitting = true;

            // Let the form submit naturally
            this.$el.submit();
          }
        }
      }
    </script>
  @endsection
