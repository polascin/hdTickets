<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Enhanced Security Setup</h1>
                <p class="text-lg text-gray-600">Secure your HD Tickets account with two-factor authentication</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden" x-data="twoFactorSetup()">
                {{-- Progress Steps --}}
                <div class="bg-indigo-50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-700'">1</div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Install App</span>
                            </div>
                            <div class="w-8 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-700'">2</div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Scan QR</span>
                            </div>
                            <div class="w-8 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-700'">3</div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Verify</span>
                            </div>
                            <div class="w-8 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="currentStep >= 4 ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-700'">4</div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Complete</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    {{-- Step 1: Install Authenticator App --}}
                    <div x-show="currentStep === 1" class="space-y-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Install Authenticator App</h2>
                            <p class="text-gray-600 mb-8">First, you'll need an authenticator app on your mobile device</p>
                        </div>

                        <div class="grid md:grid-cols-3 gap-6">
                            {{-- Google Authenticator --}}
                            <div class="bg-gray-50 rounded-xl p-6 text-center">
                                <img src="/images/apps/google-authenticator.png" alt="Google Authenticator" class="w-16 h-16 mx-auto mb-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Google Authenticator</h3>
                                <p class="text-sm text-gray-600 mb-4">Free and reliable authenticator from Google</p>
                                <div class="space-y-2">
                                    <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" 
                                       class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                        Download for iOS
                                    </a>
                                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" 
                                       class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                        Download for Android
                                    </a>
                                </div>
                            </div>

                            {{-- Authy --}}
                            <div class="bg-gray-50 rounded-xl p-6 text-center">
                                <img src="/images/apps/authy.png" alt="Authy" class="w-16 h-16 mx-auto mb-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Authy</h3>
                                <p class="text-sm text-gray-600 mb-4">Multi-device authenticator with cloud backup</p>
                                <div class="space-y-2">
                                    <a href="https://apps.apple.com/app/authy/id494168017" target="_blank" 
                                       class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                        Download for iOS
                                    </a>
                                    <a href="https://play.google.com/store/apps/details?id=com.authy.authy" target="_blank" 
                                       class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                        Download for Android
                                    </a>
                                </div>
                            </div>

                            {{-- 1Password --}}
                            <div class="bg-gray-50 rounded-xl p-6 text-center">
                                <img src="/images/apps/1password.png" alt="1Password" class="w-16 h-16 mx-auto mb-4">
                                <h3 class="font-semibold text-gray-900 mb-2">1Password</h3>
                                <p class="text-sm text-gray-600 mb-4">Password manager with built-in authenticator</p>
                                <div class="space-y-2">
                                    <a href="https://apps.apple.com/app/1password-password-manager/id568903335" target="_blank" 
                                       class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                        Download for iOS
                                    </a>
                                    <a href="https://play.google.com/store/apps/details?id=com.onepassword.android" target="_blank" 
                                       class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                        Download for Android
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium">Already have an authenticator app?</p>
                                    <p>You can use any app that supports TOTP (Time-based One-Time Passwords)</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button @click="nextStep()" 
                                    class="px-8 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                I have an app ready
                            </button>
                        </div>
                    </div>

                    {{-- Step 2: Scan QR Code --}}
                    <div x-show="currentStep === 2" class="space-y-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Scan QR Code</h2>
                            <p class="text-gray-600 mb-8">Open your authenticator app and scan this QR code</p>
                        </div>

                        <div class="flex flex-col lg:flex-row items-center lg:items-start space-y-8 lg:space-y-0 lg:space-x-12">
                            {{-- QR Code --}}
                            <div class="flex-shrink-0">
                                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                    <div id="qr-code" class="w-48 h-48 flex items-center justify-center bg-gray-50 rounded-lg">
                                        {{-- QR Code will be generated here --}}
                                        <div x-show="!qrGenerated" class="text-center">
                                            <div class="animate-spin h-8 w-8 border-2 border-indigo-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                                            <p class="text-sm text-gray-500">Generating QR code...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Instructions --}}
                            <div class="flex-1 max-w-md">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">How to scan:</h3>
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-indigo-600">1</span>
                                        </div>
                                        <p class="text-sm text-gray-700">Open your authenticator app</p>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-indigo-600">2</span>
                                        </div>
                                        <p class="text-sm text-gray-700">Tap the "+" or "Add Account" button</p>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-indigo-600">3</span>
                                        </div>
                                        <p class="text-sm text-gray-700">Choose "Scan QR Code" or camera icon</p>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-indigo-600">4</span>
                                        </div>
                                        <p class="text-sm text-gray-700">Point your camera at the QR code</p>
                                    </div>
                                </div>

                                {{-- Manual Entry Option --}}
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <button @click="showManualEntry = !showManualEntry" 
                                            class="flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Can't scan? Enter manually
                                    </button>
                                    
                                    <div x-show="showManualEntry" x-collapse class="mt-4">
                                        <p class="text-sm text-gray-600 mb-2">Enter this secret key manually:</p>
                                        <div class="flex items-center space-x-2">
                                            <code class="flex-1 bg-white px-3 py-2 border border-gray-300 rounded text-sm font-mono" 
                                                  x-text="secretKey">{{ $secretKey ?? 'Loading...' }}</code>
                                            <button @click="copySecret()" 
                                                    class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition-colors">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button @click="previousStep()" 
                                    class="px-8 py-3 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Back
                            </button>
                            <button @click="nextStep()" 
                                    class="px-8 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                I've scanned the code
                            </button>
                        </div>
                    </div>

                    {{-- Step 3: Verify Code --}}
                    <div x-show="currentStep === 3" class="space-y-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Verify Setup</h2>
                            <p class="text-gray-600 mb-8">Enter the 6-digit code from your authenticator app to confirm setup</p>
                        </div>

                        <div class="max-w-md mx-auto">
                            <form @submit.prevent="verifyCode" class="space-y-6">
                                <div>
                                    <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Verification Code
                                    </label>
                                    <input 
                                        type="text" 
                                        id="verification_code"
                                        x-model="verificationCode"
                                        maxlength="6"
                                        pattern="[0-9]{6}"
                                        placeholder="000000"
                                        class="w-full px-4 py-3 text-center text-2xl font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                        @input="verificationCode = verificationCode.replace(/\D/g, '')"
                                        required
                                    >
                                    <p class="mt-1 text-sm text-gray-500 text-center">Enter the 6-digit code from your app</p>
                                    
                                    <div x-show="verificationError" class="mt-2 text-center">
                                        <p class="text-sm text-red-600" x-text="verificationError"></p>
                                    </div>
                                </div>

                                {{-- Recovery Codes Preview --}}
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-amber-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <h4 class="text-sm font-medium text-amber-800">Important: Recovery Codes</h4>
                                    </div>
                                    <p class="text-sm text-amber-700">
                                        After verification, you'll receive recovery codes that can be used to access your account if you lose your authenticator device. <strong>Save them securely!</strong>
                                    </p>
                                </div>

                                <button 
                                    type="submit"
                                    :disabled="verificationCode.length !== 6 || isVerifying"
                                    :class="verificationCode.length === 6 && !isVerifying ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                                    class="w-full py-3 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span x-show="!isVerifying">Verify & Complete Setup</span>
                                    <span x-show="isVerifying" class="flex items-center justify-center">
                                        <div class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></div>
                                        Verifying...
                                    </span>
                                </button>
                            </form>
                        </div>

                        <div class="flex justify-center">
                            <button @click="previousStep()" 
                                    class="px-8 py-3 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Back to QR Code
                            </button>
                        </div>
                    </div>

                    {{-- Step 4: Completion & Recovery Codes --}}
                    <div x-show="currentStep === 4" class="space-y-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Setup Complete!</h2>
                            <p class="text-gray-600 mb-8">Two-factor authentication is now enabled on your account</p>
                        </div>

                        {{-- Recovery Codes --}}
                        <div class="max-w-2xl mx-auto">
                            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6">
                                <div class="flex items-center mb-4">
                                    <svg class="w-6 h-6 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-red-800">Save Your Recovery Codes</h3>
                                </div>
                                <p class="text-sm text-red-700 mb-4">
                                    These codes can be used to access your account if you lose access to your authenticator app. 
                                    <strong>Save them in a secure location - they won't be shown again!</strong>
                                </p>
                                
                                <div class="bg-white rounded-lg p-4 mb-4">
                                    <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                        <template x-for="code in recoveryCodes" :key="code">
                                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                <span x-text="code"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex space-x-3">
                                    <button @click="downloadRecoveryCodes()" 
                                            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                                        Download Codes
                                    </button>
                                    <button @click="copyRecoveryCodes()" 
                                            class="flex-1 bg-white text-red-600 py-2 px-4 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">
                                        Copy to Clipboard
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Security Tips --}}
                        <div class="max-w-2xl mx-auto">
                            <div class="bg-indigo-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-indigo-900 mb-4">Security Tips</h3>
                                <div class="space-y-3 text-sm text-indigo-800">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Store recovery codes in a secure password manager</span>
                                    </div>
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Don't share your authenticator app or codes with anyone</span>
                                    </div>
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>If you change phones, remember to transfer your authenticator</span>
                                    </div>
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>You can disable 2FA anytime in your account settings</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button @click="completeSetup()" 
                                    class="px-8 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Continue to Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function twoFactorSetup() {
            return {
                currentStep: 1,
                qrGenerated: false,
                showManualEntry: false,
                secretKey: '',
                verificationCode: '',
                verificationError: '',
                isVerifying: false,
                recoveryCodes: [],

                init() {
                    this.generateQRCode();
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

                async generateQRCode() {
                    try {
                        const response = await fetch('/api/v1/auth/2fa/generate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.secretKey = data.secret;
                            this.renderQRCode(data.qr_code_url);
                            this.qrGenerated = true;
                        }
                    } catch (error) {
                        console.error('Failed to generate QR code:', error);
                    }
                },

                renderQRCode(qrCodeUrl) {
                    const qrContainer = document.getElementById('qr-code');
                    qrContainer.innerHTML = `<img src="${qrCodeUrl}" alt="QR Code" class="w-full h-full rounded-lg">`;
                },

                copySecret() {
                    navigator.clipboard.writeText(this.secretKey).then(() => {
                        // Show success message
                        this.showToast('Secret key copied to clipboard');
                    });
                },

                async verifyCode() {
                    if (this.verificationCode.length !== 6) return;

                    this.isVerifying = true;
                    this.verificationError = '';

                    try {
                        const response = await fetch('/api/v1/auth/2fa/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                code: this.verificationCode
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.recoveryCodes = data.recovery_codes;
                            this.currentStep = 4;
                        } else {
                            this.verificationError = data.message || 'Invalid verification code. Please try again.';
                        }
                    } catch (error) {
                        console.error('Verification failed:', error);
                        this.verificationError = 'Verification failed. Please try again.';
                    } finally {
                        this.isVerifying = false;
                    }
                },

                downloadRecoveryCodes() {
                    const content = this.recoveryCodes.join('\n');
                    const blob = new Blob([content], { type: 'text/plain' });
                    const url = window.URL.createObjectURL(blob);
                    
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'hd-tickets-recovery-codes.txt';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    
                    window.URL.revokeObjectURL(url);
                    this.showToast('Recovery codes downloaded');
                },

                copyRecoveryCodes() {
                    const content = this.recoveryCodes.join('\n');
                    navigator.clipboard.writeText(content).then(() => {
                        this.showToast('Recovery codes copied to clipboard');
                    });
                },

                completeSetup() {
                    window.location.href = '/dashboard';
                },

                showToast(message) {
                    // Simple toast implementation
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            }
        }
    </script>
</x-app-layout>
