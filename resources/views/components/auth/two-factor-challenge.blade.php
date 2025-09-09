@props([
    'title' => 'Two-Factor Authentication',
    'subtitle' => 'Enter your authentication code to continue',
    'showBackupOptions' => true
])

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stadium-blue-50 via-white to-stadium-purple-50 py-12 px-4 sm:px-6 lg:px-8"
     x-data="twoFactorChallenge()">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('/images/stadium-pattern.svg')] opacity-5"></div>
    
    <!-- 2FA Container -->
    <div class="relative max-w-md w-full space-y-8">
        
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-stadium-blue-600 to-stadium-purple-600 rounded-full flex items-center justify-center mb-4 shadow-lg ring-4 ring-white/20">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h2>
            <p class="text-sm text-gray-600">{{ $subtitle }}</p>
        </div>

        <!-- Session Status Messages -->
        @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center" role="alert">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <div class="flex items-center mb-2">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">Verification Failed</span>
            </div>
            @foreach ($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <!-- 2FA Challenge Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8 transition-all duration-300 hover:shadow-2xl">
            
            <!-- 2FA Form -->
            <form method="POST" action="{{ route('2fa.verify') }}" 
                  class="space-y-6" 
                  id="twofa-form"
                  novalidate
                  x-ref="form"
                  @submit="handleSubmit">
                
                @csrf
                
                <!-- Code Type Toggle -->
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <label class="flex items-center group cursor-pointer">
                        <input type="radio" 
                               name="code_type" 
                               value="authenticator" 
                               x-model="codeType"
                               class="h-4 w-4 text-stadium-blue-600 focus:ring-stadium-blue-500 border-gray-300">
                        <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900">Authenticator</span>
                    </label>
                    <label class="flex items-center group cursor-pointer">
                        <input type="radio" 
                               name="code_type" 
                               value="recovery" 
                               x-model="codeType"
                               class="h-4 w-4 text-stadium-blue-600 focus:ring-stadium-blue-500 border-gray-300">
                        <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900">Recovery Code</span>
                    </label>
                </div>

                <!-- Code Input -->
                <div class="space-y-3">
                    <!-- Authenticator Code Input -->
                    <div x-show="codeType === 'authenticator'" x-transition>
                        <label for="auth-code" class="block text-sm font-semibold text-gray-700 text-center mb-3">
                            Enter 6-digit code from your authenticator app
                        </label>
                        <div class="flex justify-center">
                            <div class="flex space-x-2">
                                <template x-for="(digit, index) in authCode" :key="index">
                                    <input :id="'digit-' + index"
                                           type="text" 
                                           maxlength="1"
                                           x-model="authCode[index]"
                                           @input="handleDigitInput($event, index)"
                                           @keydown.backspace="handleBackspace($event, index)"
                                           @paste="handlePaste"
                                           class="w-12 h-12 text-center text-xl font-mono border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-stadium-blue-500 focus:border-transparent transition-all duration-200"
                                           :class="{'border-red-300': errors.code && codeType === 'authenticator'}"
                                           autocomplete="off">
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Recovery Code Input -->
                    <div x-show="codeType === 'recovery'" x-transition style="display: none;">
                        <label for="recovery-code" class="block text-sm font-semibold text-gray-700 text-center mb-3">
                            Enter recovery code (format: XXXX-XXXX)
                        </label>
                        <input type="text" 
                               id="recovery-code"
                               x-model="recoveryCode"
                               @input="formatRecoveryCode"
                               placeholder="XXXX-XXXX"
                               maxlength="9"
                               class="block w-full px-3 py-3 border border-gray-300 rounded-xl text-center text-lg font-mono tracking-wider text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-stadium-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 hover:bg-white"
                               :class="{'border-red-300': errors.code && codeType === 'recovery'}"
                               autocomplete="off">
                    </div>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="code" :value="codeType === 'authenticator' ? authCode.join('') : recoveryCode">
                <input type="hidden" name="recovery" :value="codeType === 'recovery' ? '1' : '0'">

                <!-- Submit Button -->
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-stadium-blue-600 to-stadium-purple-600 hover:from-stadium-blue-700 hover:to-stadium-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:transform-none"
                        :disabled="isSubmitting || !isCodeValid"
                        :class="{'animate-pulse': isSubmitting}">
                    
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <!-- Normal Icon -->
                        <svg x-show="!isSubmitting" 
                             class="h-5 w-5 transition-all duration-200" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        
                        <!-- Loading Spinner -->
                        <div x-show="isSubmitting" 
                             class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full">
                        </div>
                    </span>
                    
                    <span x-text="isSubmitting ? 'Verifying...' : 'Verify Code'"></span>
                </button>

                <!-- Backup Options -->
                @if($showBackupOptions)
                <div class="text-center space-y-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 font-medium">
                        Having trouble? Try these options:
                    </p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <!-- SMS Code -->
                        <form action="{{ route('2fa.sms-code') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-center py-2 px-3 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                                    :disabled="smsCodeSent"
                                    :class="{'opacity-50 cursor-not-allowed': smsCodeSent}">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="smsCodeSent ? 'SMS Sent' : 'Send SMS'"></span>
                            </button>
                        </form>

                        <!-- Email Code -->
                        <form action="{{ route('2fa.email-code') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-center py-2 px-3 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                                    :disabled="emailCodeSent"
                                    :class="{'opacity-50 cursor-not-allowed': emailCodeSent}">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="emailCodeSent ? 'Email Sent' : 'Send Email'"></span>
                            </button>
                        </form>
                    </div>
                    
                    <p class="text-xs text-gray-500">
                        Lost your device? 
                        <a href="{{ route('login') }}" class="text-stadium-blue-600 hover:text-stadium-blue-500 underline">
                            Contact support
                        </a>
                    </p>
                </div>
                @endif
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-500">
            <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
        </div>
    </div>
</div>

<script>
function twoFactorChallenge() {
    return {
        // Form state
        codeType: 'authenticator',
        authCode: ['', '', '', '', '', ''],
        recoveryCode: '',
        isSubmitting: false,
        errors: {},
        
        // UI state
        smsCodeSent: false,
        emailCodeSent: false,
        
        get isCodeValid() {
            if (this.codeType === 'authenticator') {
                return this.authCode.every(digit => digit.length === 1);
            } else {
                return this.recoveryCode.length === 9 && this.recoveryCode.includes('-');
            }
        },
        
        init() {
            // Focus first digit input
            this.$nextTick(() => {
                document.getElementById('digit-0')?.focus();
            });
            
            // Check for success messages that indicate code was sent
            if (document.querySelector('.bg-green-50')) {
                const message = document.querySelector('.bg-green-50').textContent.toLowerCase();
                if (message.includes('sms')) {
                    this.smsCodeSent = true;
                }
                if (message.includes('email')) {
                    this.emailCodeSent = true;
                }
            }
        },
        
        handleDigitInput(event, index) {
            const value = event.target.value;
            
            // Only allow digits
            if (!/^\d$/.test(value)) {
                event.target.value = '';
                this.authCode[index] = '';
                return;
            }
            
            this.authCode[index] = value;
            
            // Move to next input
            if (value && index < 5) {
                const nextInput = document.getElementById(`digit-${index + 1}`);
                nextInput?.focus();
            }
            
            // Auto-submit when all digits are entered
            if (index === 5 && this.isCodeValid) {
                this.$refs.form.submit();
            }
        },
        
        handleBackspace(event, index) {
            if (event.target.value === '' && index > 0) {
                const prevInput = document.getElementById(`digit-${index - 1}`);
                prevInput?.focus();
            }
        },
        
        handlePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            
            for (let i = 0; i < 6; i++) {
                this.authCode[i] = digits[i] || '';
                const input = document.getElementById(`digit-${i}`);
                if (input) input.value = this.authCode[i];
            }
            
            // Focus last filled digit or first empty one
            const lastFilledIndex = Math.min(digits.length - 1, 5);
            const nextIndex = digits.length < 6 ? digits.length : 5;
            document.getElementById(`digit-${nextIndex}`)?.focus();
        },
        
        formatRecoveryCode() {
            let value = this.recoveryCode.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            
            if (value.length > 4) {
                value = value.substring(0, 4) + '-' + value.substring(4, 8);
            }
            
            this.recoveryCode = value;
        },
        
        handleSubmit(event) {
            if (this.isSubmitting || !this.isCodeValid) {
                event.preventDefault();
                return;
            }
            
            this.isSubmitting = true;
            
            // Let form submit naturally, but prevent double submission
            setTimeout(() => {
                if (this.isSubmitting) {
                    this.isSubmitting = false;
                }
            }, 10000);
        }
    }
}
</script>
