<x-unified-layout title="Account Verification" subtitle="Complete your HD Tickets account setup">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Progress Indicator -->
      <div class="flex items-center bg-blue-100 text-blue-800 px-3 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span x-text="`Step ${currentStep} of 3`">Step 1 of 3</span>
      </div>
      
      <a href="{{ route('logout') }}" 
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         class="text-red-600 hover:text-red-700 text-sm font-medium">
        Sign Out
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
    </div>
  </x-slot>

  <div x-data="accountVerification()" x-init="init()">
    <div class="max-w-4xl mx-auto">
      
      <!-- Welcome Banner -->
      <div class="mb-8 bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 text-white rounded-lg p-6">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-2xl font-bold mb-2">Welcome to HD Tickets!</h2>
            <p class="text-green-100 mb-4">Just a few quick steps to activate your sports ticket monitoring account</p>
            <div class="flex items-center space-x-6 text-sm">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email Verification
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Optional Phone Setup
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"></path>
                </svg>
                Quick Setup
              </div>
            </div>
          </div>
          
          <!-- Account Status -->
          <div class="text-right">
            <div class="bg-white/20 rounded-lg p-4 min-w-[140px]">
              <p class="text-white/80 text-sm mb-1">Account Status</p>
              <div class="relative w-16 h-16 mx-auto mb-2">
                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                  <path class="text-white/20" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                  <path class="text-white" stroke="currentColor" stroke-width="3" fill="none" :stroke-dasharray="`${completionPercentage}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-lg font-bold text-white" x-text="completionPercentage + '%'">33%</span>
                </div>
              </div>
              <p class="text-white/70 text-xs">Complete Setup</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Steps -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <!-- Step 1: Email -->
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center mr-3 transition"
                 :class="currentStep >= 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 text-gray-400'">
              <svg x-show="emailVerified" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span x-show="!emailVerified" class="font-medium">1</span>
            </div>
            <div>
              <p class="font-medium text-gray-900">Email Verification</p>
              <p class="text-sm text-gray-600">Confirm your email address</p>
            </div>
          </div>
          
          <!-- Progress Line 1 -->
          <div class="flex-1 mx-8">
            <div class="h-1 bg-gray-200 rounded-full">
              <div class="h-1 bg-blue-600 rounded-full transition-all duration-500" 
                   :style="`width: ${emailVerified ? 100 : 0}%`"></div>
            </div>
          </div>
          
          <!-- Step 2: Phone -->
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center mr-3 transition"
                 :class="currentStep >= 2 ? 'border-purple-600 bg-purple-600 text-white' : 'border-gray-300 text-gray-400'">
              <svg x-show="phoneVerified" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span x-show="!phoneVerified" class="font-medium">2</span>
            </div>
            <div>
              <p class="font-medium text-gray-900">Phone Setup</p>
              <p class="text-sm text-gray-600">SMS alerts (optional)</p>
            </div>
          </div>
          
          <!-- Progress Line 2 -->
          <div class="flex-1 mx-8">
            <div class="h-1 bg-gray-200 rounded-full">
              <div class="h-1 bg-purple-600 rounded-full transition-all duration-500" 
                   :style="`width: ${currentStep >= 3 ? 100 : 0}%`"></div>
            </div>
          </div>
          
          <!-- Step 3: Complete -->
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center mr-3 transition"
                 :class="currentStep >= 3 ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 text-gray-400'">
              <svg x-show="currentStep >= 3" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span x-show="currentStep < 3" class="font-medium">3</span>
            </div>
            <div>
              <p class="font-medium text-gray-900">All Set!</p>
              <p class="text-sm text-gray-600">Ready to use HD Tickets</p>
            </div>
          </div>
        </div>
      </div>

      @if (!Auth::user()->hasVerifiedEmail())
        <!-- Step 1: Email Verification -->
        <div x-show="currentStep === 1" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <div class="lg:col-span-2">
            <x-ui.card>
              <x-ui.card-header title="Verify Your Email Address">
                <x-ui.badge variant="warning" dot="true">Required</x-ui.badge>
              </x-ui.card-header>
              <x-ui.card-content>
                <div class="text-center py-6">
                  <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  
                  <h3 class="text-lg font-semibold text-gray-900 mb-2">Check Your Email</h3>
                  <p class="text-gray-600 mb-4">
                    We've sent a verification email to:<br>
                    <span class="font-medium text-gray-900">{{ Auth::user()->email }}</span>
                  </p>
                  
                  <!-- Timer -->
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-center">
                      <div class="text-center">
                        <div class="text-2xl font-mono font-bold text-yellow-900" x-text="formatTime(emailTimer)">05:00</div>
                        <div class="text-xs text-yellow-700">Resend available in</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <button @click="resendVerificationEmail()" 
                            :disabled="resendingEmail || emailTimer > 0"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                      <span x-show="!resendingEmail">
                        <span x-show="emailTimer > 0">Resend Available in <span x-text="formatTime(emailTimer)"></span></span>
                        <span x-show="emailTimer <= 0">Resend Verification Email</span>
                      </span>
                      <span x-show="resendingEmail" class="flex items-center">
                        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                      </span>
                    </button>
                    
                    <button @click="checkEmailVerification()" 
                            class="w-full text-blue-600 hover:text-blue-700 py-2 text-sm font-medium transition">
                      I've verified my email →
                    </button>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
          </div>

          <!-- Email Benefits -->
          <div class="space-y-6">
            <x-ui.card>
              <x-ui.card-header title="Why Verify Email?"></x-ui.card-header>
              <x-ui.card-content>
                <div class="space-y-4">
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Price Alerts</p>
                      <p class="text-sm text-gray-600">Get notified when ticket prices drop</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Account Security</p>
                      <p class="text-sm text-gray-600">Secure password resets and login alerts</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Event Updates</p>
                      <p class="text-sm text-gray-600">Breaking news about your favorite teams</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Full Access</p>
                      <p class="text-sm text-gray-600">Unlock all HD Tickets features</p>
                    </div>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card>
              <x-ui.card-header title="Email Troubleshooting"></x-ui.card-header>
              <x-ui.card-content>
                <div class="space-y-3 text-sm text-gray-600">
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Check your spam/junk folder</span>
                  </div>
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Email delivery can take up to 5 minutes</span>
                  </div>
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Make sure {{ Auth::user()->email }} is correct</span>
                  </div>
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Contact support if issues persist</span>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
          </div>
        </div>
      @else
        <!-- Step 2: Phone Verification -->
        <div x-show="currentStep === 2" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <div class="lg:col-span-2">
            <x-ui.card>
              <x-ui.card-header title="Add Phone Number">
                <x-ui.badge variant="info" dot="true">Optional</x-ui.badge>
              </x-ui.card-header>
              <x-ui.card-content>
                <div x-show="!showPhoneVerification">
                  <div class="text-center py-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add Your Phone Number</h3>
                    <p class="text-gray-600 mb-6">Get instant SMS alerts for urgent ticket updates</p>
                    
                    <form @submit.prevent="sendPhoneVerification()" class="space-y-4">
                      <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                          Phone Number
                        </label>
                        <div class="flex">
                          <select x-model="countryCode" class="block w-24 px-3 py-3 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="+1">🇺🇸 +1</option>
                            <option value="+44">🇬🇧 +44</option>
                            <option value="+33">🇫🇷 +33</option>
                            <option value="+49">🇩🇪 +49</option>
                            <option value="+39">🇮🇹 +39</option>
                            <option value="+34">🇪🇸 +34</option>
                            <option value="+31">🇳🇱 +31</option>
                          </select>
                          <input type="tel" 
                                 x-model="phoneNumber"
                                 id="phone"
                                 placeholder="(555) 123-4567"
                                 class="flex-1 px-4 py-3 border border-l-0 border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                 required>
                        </div>
                        <p x-show="phoneError" x-text="phoneError" class="text-red-600 text-sm mt-1" x-transition></p>
                        <p class="text-xs text-gray-500 mt-2">We'll send a verification code to this number</p>
                      </div>
                      
                      <div class="space-y-3">
                        <button type="submit" 
                                :disabled="sendingCode || !phoneNumber"
                                class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                          <span x-show="!sendingCode">Send Verification Code</span>
                          <span x-show="sendingCode" class="flex items-center">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending Code...
                          </span>
                        </button>
                        
                        <button type="button" @click="skipPhoneVerification()" class="w-full text-gray-600 hover:text-gray-800 py-2 text-sm font-medium transition">
                          Skip for now →
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Phone Verification Code Input -->
                <div x-show="showPhoneVerification" x-transition>
                  <div class="text-center py-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Enter Verification Code</h3>
                    <p class="text-gray-600 mb-4">
                      We've sent a 6-digit code to:<br>
                      <span class="font-medium text-gray-900" x-text="countryCode + ' ' + phoneNumber"></span>
                    </p>
                    
                    <form @submit.prevent="verifyPhone()" class="space-y-4">
                      <div>
                        <input type="text" 
                               x-model="smsCode"
                               placeholder="123456"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               class="block w-full max-w-xs mx-auto px-4 py-3 text-center text-2xl font-mono tracking-wider border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               autocomplete="off"
                               required>
                        <p x-show="smsError" x-text="smsError" class="text-red-600 text-sm mt-1" x-transition></p>
                      </div>
                      
                      <!-- SMS Timer -->
                      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="text-center">
                          <div class="text-lg font-mono font-bold text-yellow-900" x-text="formatTime(smsTimer)">02:00</div>
                          <div class="text-xs text-yellow-700">Resend available in</div>
                        </div>
                      </div>
                      
                      <div class="space-y-3">
                        <button type="submit" 
                                :disabled="verifyingPhone || smsCode.length !== 6"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                          <span x-show="!verifyingPhone">Verify Phone Number</span>
                          <span x-show="verifyingPhone" class="flex items-center">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                          </span>
                        </button>
                        
                        <button type="button" 
                                @click="resendSMSCode()" 
                                :disabled="resendingSMS || smsTimer > 0"
                                class="w-full text-purple-600 hover:text-purple-700 py-2 text-sm font-medium transition disabled:opacity-50">
                          <span x-show="smsTimer > 0">Resend in <span x-text="formatTime(smsTimer)"></span></span>
                          <span x-show="smsTimer <= 0">Resend Code</span>
                        </button>
                        
                        <button type="button" @click="showPhoneVerification = false; skipPhoneVerification()" class="w-full text-gray-600 hover:text-gray-800 py-2 text-sm font-medium transition">
                          ← Change Phone Number
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
          </div>

          <!-- Phone Benefits -->
          <div class="space-y-6">
            <x-ui.card>
              <x-ui.card-header title="SMS Alert Benefits"></x-ui.card-header>
              <x-ui.card-content>
                <div class="space-y-4">
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Instant Alerts</p>
                      <p class="text-sm text-gray-600">Get notified seconds after tickets become available</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Price Drops</p>
                      <p class="text-sm text-gray-600">Never miss a great deal on your favorite events</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Event Changes</p>
                      <p class="text-sm text-gray-600">Schedule changes, cancellations, or venue updates</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Security</p>
                      <p class="text-sm text-gray-600">Additional layer for account verification</p>
                    </div>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
            
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">
                <strong>Privacy Note:</strong> We use your phone number only for important alerts and security. 
                We never share your information with third parties.
              </p>
            </div>
          </div>
        </div>
      @endif

      <!-- Step 3: Complete -->
      <div x-show="currentStep === 3" x-transition class="text-center py-12">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome to HD Tickets!</h2>
        <p class="text-lg text-gray-600 mb-8">Your account is now fully set up and ready to use</p>
        
        <!-- Account Summary -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 mb-8 max-w-md mx-auto">
          <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Email Verified:</span>
              <span class="text-green-600 font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ Auth::user()->email }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Phone Added:</span>
              <span class="font-medium flex items-center" :class="phoneVerified ? 'text-green-600' : 'text-gray-500'">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="phoneVerified">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span x-show="phoneVerified" x-text="countryCode + ' ' + phoneNumber"></span>
                <span x-show="!phoneVerified">Optional (can add later)</span>
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Account Role:</span>
              <x-ui.badge variant="primary">{{ ucfirst(Auth::user()->role) }}</x-ui.badge>
            </div>
          </div>
        </div>
        
        <!-- Free Access Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 max-w-lg mx-auto">
          <h3 class="font-medium text-blue-900 mb-2">🎉 7-Day Free Access</h3>
          <p class="text-sm text-blue-800">
            You have full access to HD Tickets for the next 7 days. After that, 
            you'll need an active subscription to continue purchasing tickets.
          </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('dashboard') }}" 
             class="bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Go to Dashboard
          </a>
          
          <a href="{{ route('tickets.browse') }}" 
             class="bg-green-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-green-700 transition flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            Browse Tickets
          </a>
          
          <a href="{{ route('profile.edit') }}" 
             class="text-gray-600 hover:text-gray-800 px-8 py-3 rounded-lg font-medium border border-gray-300 hover:border-gray-400 transition flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Complete Profile
          </a>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function accountVerification() {
        return {
          currentStep: {{ Auth::user()->hasVerifiedEmail() ? 2 : 1 }},
          emailVerified: {{ Auth::user()->hasVerifiedEmail() ? 'true' : 'false' }},
          phoneVerified: {{ Auth::user()->phone_verified_at ? 'true' : 'false' }},
          showPhoneVerification: false,
          
          // Form data
          phoneNumber: '{{ Auth::user()->phone ?? '' }}',
          countryCode: '+1',
          smsCode: '',
          
          // Timers
          emailTimer: 300, // 5 minutes
          smsTimer: 120, // 2 minutes
          emailTimerInterval: null,
          smsTimerInterval: null,
          
          // Loading states
          resendingEmail: false,
          sendingCode: false,
          resendingSMS: false,
          verifyingPhone: false,
          
          // Errors
          phoneError: '',
          smsError: '',

          init() {
            // Start email timer if needed
            if (!this.emailVerified) {
              this.startEmailTimer();
            }
            
            // Check email verification status periodically
            if (!this.emailVerified) {
              setInterval(() => {
                this.checkEmailVerification();
              }, 10000); // Check every 10 seconds
            }
            
            // Set initial step based on verification status
            if (this.emailVerified) {
              this.currentStep = this.phoneVerified ? 3 : 2;
            }
          },

          get completionPercentage() {
            let completed = 0;
            if (this.emailVerified) completed += 50;
            if (this.phoneVerified) completed += 25;
            if (this.currentStep === 3) completed += 25;
            return Math.min(100, completed);
          },

          async checkEmailVerification() {
            try {
              const response = await fetch('{{ route("verification.check") }}', {
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });
              
              const data = await response.json();
              
              if (data.verified) {
                this.emailVerified = true;
                this.currentStep = 2;
                clearInterval(this.emailTimerInterval);
                this.showNotification('Email Verified!', 'Your email address has been confirmed', 'success');
              }
            } catch (error) {
              console.error('Failed to check email verification:', error);
            }
          },

          async resendVerificationEmail() {
            this.resendingEmail = true;
            
            try {
              const response = await fetch('{{ route("verification.send") }}', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });
              
              if (response.ok) {
                this.showNotification('Email Sent!', 'Check your inbox for the verification email', 'success');
                this.emailTimer = 300; // Reset timer
                this.startEmailTimer();
              } else {
                this.showNotification('Error', 'Failed to send verification email', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Network error. Please try again.', 'error');
            } finally {
              this.resendingEmail = false;
            }
          },

          async sendPhoneVerification() {
            if (!this.phoneNumber.trim()) {
              this.phoneError = 'Please enter a valid phone number';
              return;
            }
            
            this.sendingCode = true;
            this.phoneError = '';
            
            try {
              const response = await fetch('{{ route("phone.verify.send") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  phone: this.countryCode + this.phoneNumber
                })
              });
              
              const data = await response.json();
              
              if (data.success) {
                this.showPhoneVerification = true;
                this.startSMSTimer();
                this.showNotification('Code Sent!', 'Check your phone for the verification code', 'success');
              } else {
                this.phoneError = data.message || 'Failed to send verification code';
              }
            } catch (error) {
              this.phoneError = 'Network error. Please try again.';
            } finally {
              this.sendingCode = false;
            }
          },

          async verifyPhone() {
            if (this.smsCode.length !== 6) {
              this.smsError = 'Please enter the 6-digit code';
              return;
            }
            
            this.verifyingPhone = true;
            this.smsError = '';
            
            try {
              const response = await fetch('{{ route("phone.verify.confirm") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  code: this.smsCode,
                  phone: this.countryCode + this.phoneNumber
                })
              });
              
              const data = await response.json();
              
              if (data.success) {
                this.phoneVerified = true;
                this.currentStep = 3;
                clearInterval(this.smsTimerInterval);
                this.showNotification('Phone Verified!', 'Your phone number has been confirmed', 'success');
              } else {
                this.smsError = data.message || 'Invalid verification code';
              }
            } catch (error) {
              this.smsError = 'Network error. Please try again.';
            } finally {
              this.verifyingPhone = false;
            }
          },

          async resendSMSCode() {
            this.resendingSMS = true;
            
            try {
              await this.sendPhoneVerification();
              this.smsTimer = 120; // Reset timer
              this.startSMSTimer();
            } catch (error) {
              this.showNotification('Error', 'Failed to resend code', 'error');
            } finally {
              this.resendingSMS = false;
            }
          },

          skipPhoneVerification() {
            this.currentStep = 3;
            this.showNotification('Setup Complete', 'You can add your phone number later in settings', 'info');
          },

          startEmailTimer() {
            this.emailTimerInterval = setInterval(() => {
              if (this.emailTimer > 0) {
                this.emailTimer--;
              } else {
                clearInterval(this.emailTimerInterval);
              }
            }, 1000);
          },

          startSMSTimer() {
            this.smsTimerInterval = setInterval(() => {
              if (this.smsTimer > 0) {
                this.smsTimer--;
              } else {
                clearInterval(this.smsTimerInterval);
              }
            }, 1000);
          },

          formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }
    </script>
  @endpush
</x-unified-layout>
