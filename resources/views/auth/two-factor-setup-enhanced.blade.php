<x-unified-layout title="Two-Factor Authentication" subtitle="Secure your account with enhanced security">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Security Level Indicator -->
      <div class="flex items-center bg-{{ Auth::user()->two_factor_enabled ? 'green' : 'yellow' }}-100 text-{{ Auth::user()->two_factor_enabled ? 'green' : 'yellow' }}-800 px-3 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
        </svg>
        {{ Auth::user()->two_factor_enabled ? 'High Security' : 'Basic Security' }}
      </div>
      
      <a href="{{ route('profile.security') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
        ← Back to Security
      </a>
    </div>
  </x-slot>

  <div x-data="twoFactorSetup()" x-init="init()">
    <div class="max-w-4xl mx-auto">
      
      <!-- Security Status Banner -->
      <div class="mb-8 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-white rounded-lg p-6">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-2xl font-bold mb-2">Two-Factor Authentication Setup</h2>
            <p class="text-blue-100">Add an extra layer of security to your HD Tickets account</p>
            <div class="flex items-center mt-4 space-x-6 text-sm">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Enhanced Security
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Mobile App Required
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                30-Second Setup
              </div>
            </div>
          </div>
          
          <!-- Security Score -->
          <div class="text-right">
            <div class="bg-white/20 rounded-lg p-4 min-w-[120px]">
              <p class="text-white/80 text-sm mb-1">Security Score</p>
              <div class="relative w-16 h-16 mx-auto mb-2">
                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                  <path class="text-white/20" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                  <path class="text-white" stroke="currentColor" stroke-width="3" fill="none" :stroke-dasharray="`${currentSecurityScore}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-lg font-bold text-white" x-text="currentSecurityScore + '%'">65%</span>
                </div>
              </div>
              <p class="text-white/70 text-xs">{{ Auth::user()->two_factor_enabled ? 'High' : 'Upgrade to 95%' }}</p>
            </div>
          </div>
        </div>
      </div>

      @if (Auth::user()->two_factor_enabled)
        <!-- 2FA Already Enabled -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Current Status -->
          <x-ui.card>
            <x-ui.card-header title="2FA Status">
              <x-ui.badge variant="success" dot="true">Active</x-ui.badge>
            </x-ui.card-header>
            <x-ui.card-content>
              <div class="space-y-4">
                <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                  <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-900">2FA is Active</h3>
                    <p class="text-sm text-green-700">Your account is protected with two-factor authentication</p>
                    <p class="text-xs text-green-600 mt-1">
                      Enabled on {{ Auth::user()->two_factor_enabled_at?->format('M j, Y \a\t g:i A') ?? 'Unknown date' }}
                    </p>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-900">{{ Auth::user()->twoFactorBackupCodes()?->count() ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Backup Codes</div>
                  </div>
                  <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-900">{{ Auth::user()->trustedDevices()?->count() ?? 0 }}</div>
                    <div class="text-xs text-gray-500">Trusted Devices</div>
                  </div>
                </div>
              </div>
            </x-ui.card-content>
          </x-ui.card>

          <!-- Management Options -->
          <x-ui.card>
            <x-ui.card-header title="Management Options"></x-ui.card-header>
            <x-ui.card-content>
              <div class="space-y-3">
                <button @click="showBackupCodes = true" class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg text-left transition">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <div>
                      <p class="font-medium text-gray-900">View Backup Codes</p>
                      <p class="text-sm text-gray-600">Emergency access codes</p>
                    </div>
                  </div>
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>

                <button @click="showTrustedDevices = true" class="w-full flex items-center justify-between p-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg text-left transition">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                      <p class="font-medium text-gray-900">Manage Trusted Devices</p>
                      <p class="text-sm text-gray-600">Devices that skip 2FA</p>
                    </div>
                  </div>
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>

                <button @click="showDisable2FA = true" class="w-full flex items-center justify-between p-3 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg text-left transition">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                      <p class="font-medium text-gray-900">Disable 2FA</p>
                      <p class="text-sm text-gray-600">Reduce account security</p>
                    </div>
                  </div>
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>
            </x-ui.card-content>
          </x-ui.card>
        </div>
      @else
        <!-- 2FA Setup Process -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <!-- Setup Steps -->
          <div class="lg:col-span-2">
            <x-ui.card>
              <x-ui.card-header title="Setup Two-Factor Authentication">
                <div class="flex items-center space-x-2">
                  <div class="flex items-center" x-show="currentStep >= 1">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                      <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600">Step 1</span>
                  </div>
                  
                  <div class="flex items-center" x-show="currentStep >= 2" x-transition>
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                      <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600">Step 2</span>
                  </div>
                  
                  <div class="flex items-center" x-show="currentStep >= 3" x-transition>
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                      <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-green-600">Complete</span>
                  </div>
                </div>
              </x-ui.card-header>
              <x-ui.card-content>
                <!-- Step 1: Download App -->
                <div x-show="currentStep === 1" x-transition>
                  <div class="text-center py-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Step 1: Download Authenticator App</h3>
                    <p class="text-gray-600 mb-6">Download one of these recommended authenticator apps on your mobile device</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                      <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg border transition">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                          <span class="text-white font-bold text-sm">GA</span>
                        </div>
                        <div class="text-left">
                          <p class="font-medium text-gray-900">Google Authenticator</p>
                          <p class="text-sm text-gray-600">Free • iOS & Android</p>
                        </div>
                      </a>
                      
                      <a href="https://authy.com/download/" target="_blank" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg border transition">
                        <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                          <span class="text-white font-bold text-sm">AU</span>
                        </div>
                        <div class="text-left">
                          <p class="font-medium text-gray-900">Authy</p>
                          <p class="text-sm text-gray-600">Free • Cross-platform</p>
                        </div>
                      </a>
                    </div>
                    
                    <button @click="currentStep = 2" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                      I've Downloaded the App →
                    </button>
                  </div>
                </div>

                <!-- Step 2: Scan QR Code -->
                <div x-show="currentStep === 2" x-transition>
                  <div class="text-center py-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M9 16h2m2-6h2m0 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v1"></path>
                      </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Step 2: Scan QR Code</h3>
                    <p class="text-gray-600 mb-6">Use your authenticator app to scan this QR code</p>
                    
                    <!-- QR Code Display -->
                    <div class="bg-white p-6 rounded-lg border-2 border-dashed border-gray-300 mb-6 inline-block">
                      <div id="qr-code" class="w-48 h-48 bg-gray-100 rounded flex items-center justify-center">
                        <!-- QR Code will be generated here -->
                        <div class="text-center">
                          <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                          <p class="text-sm text-gray-500">Generating QR Code...</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Manual Entry Option -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                      <p class="text-sm text-gray-600 mb-2">Can't scan? Enter this code manually:</p>
                      <div class="bg-white p-3 rounded border font-mono text-sm break-all" id="manual-code">
                        {{ $twoFactorSecret ?? 'LOADING...' }}
                      </div>
                      <button onclick="copyToClipboard('manual-code')" class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium">
                        📋 Copy Code
                      </button>
                    </div>
                    
                    <button @click="currentStep = 3" class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 transition">
                      I've Added the Account →
                    </button>
                  </div>
                </div>

                <!-- Step 3: Verify -->
                <div x-show="currentStep === 3" x-transition>
                  <div class="text-center py-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Step 3: Verify Setup</h3>
                    <p class="text-gray-600 mb-6">Enter the 6-digit code from your authenticator app to confirm setup</p>
                    
                    <form @submit.prevent="verify2FA()" class="space-y-4">
                      <div>
                        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                          Verification Code
                        </label>
                        <input type="text" 
                               x-model="verificationCode"
                               id="verification_code"
                               name="code"
                               placeholder="123456"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               class="block w-full max-w-xs mx-auto px-4 py-3 text-center text-2xl font-mono tracking-wider border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               autocomplete="off"
                               required>
                        <p class="text-xs text-gray-500 mt-2">Enter the 6-digit code from your app</p>
                      </div>
                      
                      <div class="space-y-3">
                        <button type="submit" 
                                :disabled="verificationCode.length !== 6"
                                class="bg-purple-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                          <span x-show="!verifying">Verify & Enable 2FA</span>
                          <span x-show="verifying" class="flex items-center">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                          </span>
                        </button>
                        
                        <button type="button" @click="currentStep = 2" class="text-gray-600 hover:text-gray-700 text-sm">
                          ← Go Back to QR Code
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
          </div>

          <!-- Security Benefits -->
          <div class="space-y-6">
            <x-ui.card>
              <x-ui.card-header title="Security Benefits"></x-ui.card-header>
              <x-ui.card-content>
                <div class="space-y-4">
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Prevent Unauthorized Access</p>
                      <p class="text-sm text-gray-600">Even if someone knows your password, they can't access your account</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Protect Financial Data</p>
                      <p class="text-sm text-gray-600">Secure your subscription and payment information</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Industry Standard</p>
                      <p class="text-sm text-gray-600">Used by banks and major tech companies</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                      <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Works Offline</p>
                      <p class="text-sm text-gray-600">Codes work even without internet connection</p>
                    </div>
                  </div>
                </div>
              </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card>
              <x-ui.card-header title="Need Help?"></x-ui.card-header>
              <x-ui.card-content>
                <div class="space-y-3">
                  <a href="#" class="flex items-center text-blue-600 hover:text-blue-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    2FA Setup Guide
                  </a>
                  <a href="#" class="flex items-center text-blue-600 hover:text-blue-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Compatible Apps List
                  </a>
                  <a href="#" class="flex items-center text-blue-600 hover:text-blue-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Contact Support
                  </a>
                </div>
              </x-ui.card-content>
            </x-ui.card>
          </div>
        </div>
      @endif

      <!-- Backup Codes Modal -->
      <div x-show="showBackupCodes" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showBackupCodes = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Backup Codes</h3>
            <p class="text-sm text-gray-600">Save these codes in a safe place</p>
          </div>
          <div class="px-6 py-4">
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
              <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                @foreach(Auth::user()->twoFactorBackupCodes() ?? [] as $code)
                  <div class="bg-white p-2 rounded border text-center">{{ $code }}</div>
                @endforeach
              </div>
            </div>
            <p class="text-xs text-gray-600 mb-4">
              Each code can only be used once. Generate new codes if you run out.
            </p>
          </div>
          <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
            <button @click="generateNewBackupCodes()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
              Generate New Codes
            </button>
            <button @click="showBackupCodes = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
      function twoFactorSetup() {
        return {
          currentStep: 1,
          currentSecurityScore: {{ Auth::user()->two_factor_enabled ? 95 : 65 }},
          verificationCode: '',
          verifying: false,
          showBackupCodes: false,
          showTrustedDevices: false,
          showDisable2FA: false,

          init() {
            if (!@json(Auth::user()->two_factor_enabled)) {
              this.generateQRCode();
            }
            this.updateSecurityScore();
          },

          generateQRCode() {
            const secret = @json($twoFactorSecret ?? '');
            const email = @json(Auth::user()->email);
            const appName = 'HD Tickets';
            
            const otpauth = `otpauth://totp/${encodeURIComponent(appName)}:${encodeURIComponent(email)}?secret=${secret}&issuer=${encodeURIComponent(appName)}`;
            
            QRCode.toCanvas(document.createElement('canvas'), otpauth, {
              width: 192,
              margin: 1,
              color: {
                dark: '#1f2937',
                light: '#ffffff'
              }
            }, (error, canvas) => {
              if (error) {
                console.error('QR code generation failed:', error);
                document.getElementById('qr-code').innerHTML = '<p class="text-red-500 text-sm">Failed to generate QR code</p>';
              } else {
                document.getElementById('qr-code').innerHTML = '';
                document.getElementById('qr-code').appendChild(canvas);
              }
            });
          },

          async verify2FA() {
            this.verifying = true;
            
            try {
              const response = await fetch('{{ route("two-factor.enable") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  code: this.verificationCode
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('2FA Enabled!', 'Your account is now protected with two-factor authentication', 'success');
                this.currentSecurityScore = 95;
                setTimeout(() => {
                  window.location.reload();
                }, 2000);
              } else {
                this.showNotification('Verification Failed', data.message || 'Please check your code and try again', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Something went wrong. Please try again.', 'error');
            } finally {
              this.verifying = false;
            }
          },

          async generateNewBackupCodes() {
            try {
              const response = await fetch('{{ route("two-factor.backup-codes.regenerate") }}', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                this.showNotification('Success', 'New backup codes generated', 'success');
                window.location.reload();
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to generate new codes', 'error');
            }
          },

          updateSecurityScore() {
            // Animate security score update
            const targetScore = {{ Auth::user()->two_factor_enabled ? 95 : 65 }};
            let current = this.currentSecurityScore;
            
            const increment = targetScore > current ? 1 : -1;
            const interval = setInterval(() => {
              if (current === targetScore) {
                clearInterval(interval);
              } else {
                current += increment;
                this.currentSecurityScore = current;
              }
            }, 20);
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }

      function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
          if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.success('Copied!', 'Code copied to clipboard');
          }
        });
      }
    </script>
  @endpush
</x-unified-layout>
