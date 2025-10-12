<x-guest-layout>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="comprehensiveRegistration()">

      {{-- Alert Messages --}}
      @if (session('success'))
        <div class="mb-6">
          <x-alert-success message="{{ session('success') }}" />
        </div>
      @endif

      @if (session('error'))
        <div class="mb-6">
          <x-alert-error message="{{ session('error') }}" />
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6">
          <x-alert-error message="Please correct the following errors:" :errors="$errors->all()" />
        </div>
      @endif

      {{-- Progress Header --}}
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <picture>
              <x-application-logo size="default" class="rounded" alt="HD Tickets" />
            </picture>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Create Your Account</h1>
              <p class="text-sm text-gray-500">Join the sports ticket monitoring revolution</p>
            </div>
          </div>

          <div class="flex items-center space-x-2 text-sm text-gray-500">
            <span>Step</span>
            <span x-text="currentStep" class="font-medium text-indigo-600">1</span>
            <span>of</span>
            <span>5</span>
          </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div class="progress-bar bg-indigo-600 h-2 rounded-full transition-all duration-500"
            :style="`width: ${(currentStep / 5) * 100}%`"></div>
        </div>
      </div>

      <form method="POST" action="{{ route('register.comprehensive') }}" id="registration-form"
        @submit.prevent="submitForm">
        @csrf

        {{-- Step 1: Account Type Selection --}}
        <div x-show="currentStep === 1" x-transition class="animate-fade-in">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Choose Your Account Type</h2>
            <p class="text-lg text-gray-600">Select the plan that best fits your sports ticket monitoring needs</p>
          </div>

          <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            @foreach ($availableRoles ?? [] as $role => $details)
              <div class="relative">
                <input type="radio" id="{{ $role }}" name="role" value="{{ $role }}"
                  x-model="form.role" class="sr-only">
                <label for="{{ $role }}" class="block cursor-pointer">
                  <div
                    class="role-card bg-white rounded-2xl shadow-lg border-2 transition-all duration-200 p-8 h-full hover:shadow-xl"
                    :class="form.role === '{{ $role }}' ? 'border-indigo-500 ring-2 ring-indigo-200' :
                        'border-gray-200 hover:border-indigo-300'">

                    <div class="text-center mb-6">
                      <div
                        class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 {{ $role === 'customer' ? 'bg-indigo-100' : 'bg-purple-100' }}">
                        @if ($role === 'customer')
                          <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                        @else
                          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                          </svg>
                        @endif
                      </div>
                      <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $details['label'] ?? $role }}</h3>
                      <p class="text-gray-600">{{ $details['description'] ?? 'Account type' }}</p>
                    </div>

                    @if (isset($details['features']))
                      <div class="space-y-3">
                        @foreach ($details['features'] as $feature)
                          <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor"
                              viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-gray-700">{{ $feature }}</span>
                          </div>
                        @endforeach
                      </div>
                    @endif

                    @if (isset($details['price']))
                      <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                          <span class="text-2xl font-bold text-gray-900">{{ $details['price'] }}</span>
                        </div>
                        @if (isset($details['trial']))
                          <p class="text-sm text-gray-500 text-center mt-1">{{ $details['trial'] }}</p>
                        @endif
                      </div>
                    @endif
                  </div>
                </label>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Step 2: Personal Information --}}
        <div x-show="currentStep === 2" x-transition class="animate-fade-in">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Personal Information</h2>
            <p class="text-lg text-gray-600">Tell us about yourself to personalize your experience</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- First Name --}}
              <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                <input type="text" id="first_name" name="first_name" x-model="form.first_name" required
                  maxlength="50" autocomplete="given-name"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Enter your first name">
                <div x-show="errors.first_name" class="mt-1 text-sm text-red-600" x-text="errors.first_name"></div>
              </div>

              {{-- Last Name --}}
              <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                <input type="text" id="last_name" name="last_name" x-model="form.last_name" required maxlength="50"
                  autocomplete="family-name"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Enter your last name">
                <div x-show="errors.last_name" class="mt-1 text-sm text-red-600" x-text="errors.last_name"></div>
              </div>

              {{-- Email --}}
              <div class="md:col-span-2">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                <div class="relative">
                  <input type="email" id="email" name="email" x-model="form.email" required
                    autocomplete="email" @blur="checkEmailAvailability"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors pr-12"
                    placeholder="your.email@example.com">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <div x-show="emailCheck.loading" class="animate-spin h-5 w-5 text-gray-400">
                      <svg fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                          stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                          d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                      </svg>
                    </div>
                    <svg x-show="emailCheck.available && !emailCheck.loading" class="h-5 w-5 text-green-500"
                      fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                    <svg x-show="emailCheck.available === false && !emailCheck.loading && form.email"
                      class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <div x-show="emailCheck.message" class="mt-1 text-sm"
                  :class="emailCheck.available ? 'text-green-600' : 'text-red-600'" x-text="emailCheck.message"></div>
                <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
              </div>

              {{-- Username --}}
              <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username (optional)</label>
                <input type="text" id="username" name="username" x-model="form.username" maxlength="50"
                  autocomplete="username"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Choose a username">
                <div x-show="errors.username" class="mt-1 text-sm text-red-600" x-text="errors.username"></div>
                <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate from your name</p>
              </div>

              {{-- Phone --}}
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number
                  (optional)</label>
                <input type="tel" id="phone" name="phone" x-model="form.phone" maxlength="20"
                  autocomplete="tel"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="+1 (555) 123-4567">
                <div x-show="errors.phone" class="mt-1 text-sm text-red-600" x-text="errors.phone"></div>
              </div>
            </div>
          </div>
        </div>

        {{-- Step 3: Security Settings --}}
        <div x-show="currentStep === 3" x-transition class="animate-fade-in">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Secure Your Account</h2>
            <p class="text-lg text-gray-600">Create a strong password to protect your account</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
            <div class="space-y-6">
              {{-- Password --}}
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                <div class="relative">
                  <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                    x-model="form.password" required autocomplete="new-password" @input="checkPasswordStrength"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors pr-12"
                    placeholder="Enter a strong password">
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                    </svg>
                  </button>
                </div>

                {{-- Password Strength Indicator --}}
                <div x-show="form.password" class="mt-3">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">Password Strength</span>
                    <span class="text-sm font-medium" :class="`text-${passwordStrength.color}-600`"
                      x-text="passwordStrength.text"></span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300"
                      :class="`bg-${passwordStrength.color}-500`"
                      :style="`width: ${(passwordStrength.score / passwordStrength.max_score) * 100}%`"></div>
                  </div>
                </div>
                <div x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password"></div>
              </div>

              {{-- Password Confirmation --}}
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm
                  Password *</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                  x-model="form.password_confirmation" required autocomplete="new-password"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Confirm your password">
                <div x-show="form.password_confirmation && form.password !== form.password_confirmation"
                  class="mt-1 text-sm text-red-600">Passwords do not match</div>
                <div x-show="errors.password_confirmation" class="mt-1 text-sm text-red-600"
                  x-text="errors.password_confirmation"></div>
              </div>

              {{-- Security Options --}}
              <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Security Options</h3>
                <div class="space-y-4">
                  <div class="flex items-start space-x-3">
                    <input type="checkbox" id="enable_2fa" name="enable_2fa" x-model="form.enable_2fa"
                      class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <div>
                      <label for="enable_2fa" class="text-sm font-medium text-gray-700">Enable Two-Factor
                        Authentication</label>
                      <p class="text-sm text-gray-500">Add an extra layer of security to your account (recommended)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Step 4: Legal & Terms --}}
        <div x-show="currentStep === 4" x-transition class="animate-fade-in">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Terms & Conditions</h2>
            <p class="text-lg text-gray-600">Please review and accept our terms to continue</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8 max-w-3xl mx-auto">
            @if ($legalDocuments ?? [])
              <div class="space-y-6">
                @foreach ($legalDocuments as $type => $document)
                  <div class="border border-gray-200 rounded-lg">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                      <h3 class="text-lg font-medium text-gray-900">{{ $document->title }}</h3>
                      <p class="text-sm text-gray-600 mt-1">Last updated:
                        {{ $document->updated_at->format('F j, Y') }}</p>
                    </div>

                    <div class="px-6 py-4">
                      <div class="max-h-48 overflow-y-auto text-sm text-gray-700 leading-relaxed">
                        {!! Str::limit(strip_tags($document->content), 500) !!}
                        @if (strlen(strip_tags($document->content)) > 500)
                          <div class="mt-2">
                            <a href="{{ route('legal.document', $document->slug) }}" target="_blank"
                              class="text-indigo-600 hover:text-indigo-800 font-medium">
                              Read full document →
                            </a>
                          </div>
                        @endif
                      </div>

                      <div class="mt-4 flex items-start space-x-3">
                        <input type="checkbox" id="legal_{{ $type }}"
                          name="legal_acceptances[{{ $type }}]"
                          x-model="form.legal_acceptances.{{ $type }}" required
                          class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="legal_{{ $type }}" class="text-sm text-gray-700">
                          I have read and agree to the <strong>{{ $document->title }}</strong> *
                        </label>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <input type="checkbox" id="terms" name="legal_acceptances[terms]"
                    x-model="form.legal_acceptances.terms" required
                    class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                  <label for="terms" class="text-sm text-gray-700">
                    I have read and agree to the <a href="#"
                      class="text-indigo-600 hover:text-indigo-800 font-medium">Terms of Service</a> *
                  </label>
                </div>

                <div class="flex items-start space-x-3">
                  <input type="checkbox" id="privacy" name="legal_acceptances[privacy]"
                    x-model="form.legal_acceptances.privacy" required
                    class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                  <label for="privacy" class="text-sm text-gray-700">
                    I have read and agree to the <a href="#"
                      class="text-indigo-600 hover:text-indigo-800 font-medium">Privacy Policy</a> *
                  </label>
                </div>
              </div>
            @endif

            {{-- Marketing Preferences --}}
            <div class="mt-8 border-t border-gray-200 pt-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Communication Preferences</h3>
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <input type="checkbox" id="marketing_emails" name="marketing_emails"
                    x-model="form.marketing_emails"
                    class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                  <div>
                    <label for="marketing_emails" class="text-sm font-medium text-gray-700">Marketing Emails</label>
                    <p class="text-sm text-gray-500">Receive occasional emails about new features and offers</p>
                  </div>
                </div>

                <div class="flex items-start space-x-3">
                  <input type="checkbox" id="newsletter_subscription" name="newsletter_subscription"
                    x-model="form.newsletter_subscription"
                    class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                  <div>
                    <label for="newsletter_subscription" class="text-sm font-medium text-gray-700">Newsletter</label>
                    <p class="text-sm text-gray-500">Stay updated with sports events and ticket insights</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Step 5: Review & Submit --}}
        <div x-show="currentStep === 5" x-transition class="animate-fade-in">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Review Your Information</h2>
            <p class="text-lg text-gray-600">Please review your details before creating your account</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
            <div class="space-y-6">
              {{-- Account Type --}}
              <div class="flex justify-between py-3 border-b border-gray-200">
                <span class="text-sm font-medium text-gray-500">Account Type</span>
                <span class="text-sm text-gray-900"
                  x-text="form.role === 'customer' ? 'Sports Fan' : 'Business/Professional'"></span>
              </div>

              {{-- Personal Info --}}
              <div class="space-y-3">
                <h3 class="font-medium text-gray-900">Personal Information</h3>
                <div class="space-y-2">
                  <div class="flex justify-between py-2">
                    <span class="text-sm text-gray-500">Name</span>
                    <span class="text-sm text-gray-900" x-text="`${form.first_name} ${form.last_name}`"></span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-sm text-gray-500">Email</span>
                    <span class="text-sm text-gray-900" x-text="form.email"></span>
                  </div>
                  <div class="flex justify-between py-2" x-show="form.username">
                    <span class="text-sm text-gray-500">Username</span>
                    <span class="text-sm text-gray-900" x-text="form.username"></span>
                  </div>
                  <div class="flex justify-between py-2" x-show="form.phone">
                    <span class="text-sm text-gray-500">Phone</span>
                    <span class="text-sm text-gray-900" x-text="form.phone"></span>
                  </div>
                </div>
              </div>

              {{-- Security --}}
              <div class="space-y-3 border-t border-gray-200 pt-6">
                <h3 class="font-medium text-gray-900">Security Settings</h3>
                <div class="flex justify-between py-2">
                  <span class="text-sm text-gray-500">Two-Factor Authentication</span>
                  <span class="text-sm text-gray-900" x-text="form.enable_2fa ? 'Enabled' : 'Disabled'"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="mt-8 flex justify-between max-w-4xl mx-auto">
          <button type="button" @click="previousStep" x-show="currentStep > 1"
            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            ← Previous
          </button>
          <div x-show="currentStep === 1"></div>

          <button type="button" @click="nextStep" x-show="currentStep < 5" x-bind:disabled="!canProceedToNextStep()"
            class="btn-primary px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Next →
          </button>

          <button type="submit" x-show="currentStep === 5" x-bind:disabled="isSubmitting"
            class="btn-primary px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <span x-show="!isSubmitting">Create Account</span>
            <span x-show="isSubmitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                  stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              Creating...
            </span>
          </button>
        </div>
      </form>

      {{-- Already have an account --}}
      <div class="text-center mt-8 pb-8">
        <p class="text-sm text-gray-600">
          Already have an account?
          <a href="{{ route('login') }}"
            class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Sign in here</a>
        </p>
      </div>
    </div>
  </div>

  <!-- Enhanced Styles -->
  <style>
    /* Password strength colors */
    .strength-very-weak {
      background-color: #dc2626;
    }

    .strength-weak {
      background-color: #ea580c;
    }

    .strength-fair {
      background-color: #d97706;
    }

    .strength-good {
      background-color: #ca8a04;
    }

    .strength-strong {
      background-color: #16a34a;
    }

    .strength-very-strong {
      background-color: #059669;
    }

    .strength-excellent {
      background-color: #047857;
    }

    /* Card hover effects */
    .role-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .role-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Progress bar animation */
    .progress-bar {
      transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Button states */
    .btn-primary {
      transition: all 0.2s ease-in-out;
    }

    .btn-primary:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 8px 15px -3px rgba(99, 102, 241, 0.3);
    }
  </style>

  <!-- Alpine.js Data & Methods -->
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('comprehensiveRegistration', () => ({
        currentStep: 1,
        isSubmitting: false,
        showPassword: false,

        // Form data
        form: {
          role: '',
          first_name: '',
          last_name: '',
          email: '',
          username: '',
          phone: '',
          password: '',
          password_confirmation: '',
          enable_2fa: false,
          marketing_emails: false,
          newsletter_subscription: false,
          legal_acceptances: {}
        },

        // Validation states
        errors: {},
        emailCheck: {
          loading: false,
          available: null,
          message: ''
        },
        passwordStrength: {
          score: 0,
          max_score: 6,
          level: 'very-weak',
          text: 'Very Weak',
          color: 'red'
        },

        // Navigation methods
        nextStep() {
          if (this.canProceedToNextStep()) {
            this.currentStep++;
          }
        },

        previousStep() {
          if (this.currentStep > 1) {
            this.currentStep--;
          }
        },

        canProceedToNextStep() {
          switch (this.currentStep) {
            case 1:
              return this.form.role !== '';
            case 2:
              return this.form.first_name && this.form.last_name && this.form.email &&
                this.emailCheck.available !== false;
            case 3:
              return this.form.password && this.form.password_confirmation &&
                this.form.password === this.form.password_confirmation;
            case 4:
              const acceptances = this.form.legal_acceptances;
              return acceptances.terms && acceptances.privacy;
            default:
              return true;
          }
        },

        // Validation methods
        async checkEmailAvailability() {
          if (!this.form.email || !this.form.email.includes('@')) return;

          this.emailCheck.loading = true;
          try {
            const response = await fetch('/register/comprehensive/check-email', {
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
            this.emailCheck.available = data.available;
            this.emailCheck.message = data.message;
          } catch (error) {
            console.error('Error checking email:', error);
          } finally {
            this.emailCheck.loading = false;
          }
        },

        checkPasswordStrength() {
          const password = this.form.password;
          if (!password) {
            this.passwordStrength = {
              score: 0,
              max_score: 6,
              level: 'very-weak',
              text: 'Very Weak',
              color: 'red'
            };
            return;
          }

          const checks = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            numbers: /[0-9]/.test(password),
            special: /[^a-zA-Z0-9]/.test(password),
            long: password.length >= 12
          };

          const score = Object.values(checks).filter(Boolean).length;

          const levels = {
            0: {
              level: 'very-weak',
              text: 'Very Weak',
              color: 'red'
            },
            1: {
              level: 'weak',
              text: 'Weak',
              color: 'red'
            },
            2: {
              level: 'fair',
              text: 'Fair',
              color: 'orange'
            },
            3: {
              level: 'good',
              text: 'Good',
              color: 'yellow'
            },
            4: {
              level: 'strong',
              text: 'Strong',
              color: 'green'
            },
            5: {
              level: 'very-strong',
              text: 'Very Strong',
              color: 'green'
            },
            6: {
              level: 'excellent',
              text: 'Excellent',
              color: 'green'
            }
          };

          this.passwordStrength = {
            score,
            max_score: 6,
            ...levels[score]
          };
        },

        // Form submission
        async submitForm() {
          this.isSubmitting = true;
          this.errors = {};

          try {
            const formData = new FormData();
            Object.keys(this.form).forEach(key => {
              if (key === 'legal_acceptances') {
                Object.keys(this.form[key]).forEach(legal_key => {
                  formData.append(`legal_acceptances[${legal_key}]`, this.form[key][legal_key]);
                });
              } else {
                formData.append(key, this.form[key]);
              }
            });

            const response = await fetch('/register/comprehensive', {
              method: 'POST',
              body: formData,
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            });

            const data = await response.json();

            if (data.success) {
              window.location.href = data.redirect_url || '{{ route('verification.notice') }}';
            } else {
              this.errors = data.errors || {};
              alert(data.message || 'Registration failed. Please check your information and try again.');
            }
          } catch (error) {
            console.error('Registration error:', error);
            alert('An error occurred during registration. Please try again.');
          } finally {
            this.isSubmitting = false;
          }
        }
      }));
    });
  </script>
</x-guest-layout>
