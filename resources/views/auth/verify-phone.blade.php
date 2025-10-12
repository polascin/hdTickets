<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ __('Verify Phone Number') }} - HD Tickets</title>

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])

    <!-- SEO Meta -->
    <meta name="description"
      content="Verify your phone number for HD Tickets - Professional sports event ticket monitoring platform. Complete your account security setup.">

    <style>
      .verification-bg {
        background: radial-gradient(1200px 600px at 50% -200px, rgba(59, 130, 246, 0.15), rgba(99, 102, 241, 0.1) 40%, rgba(16, 185, 129, 0.05) 70%, transparent),
          linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 35%, #22c55e 100%);
        min-height: 100vh;
      }

      .form-card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      @media (prefers-color-scheme: dark) {
        .form-card {
          background: rgba(17, 24, 39, 0.85);
          border-color: rgba(255, 255, 255, 0.05);
        }

        .verification-bg {
          background: linear-gradient(135deg, #0f172a 0%, #0b1733 50%, #052e16 100%);
        }
      }

      [x-cloak] {
        display: none !important;
      }
    </style>
  </head>

  <body class="font-sans antialiased verification-bg">
    <x-layout.auth-container :panel="false" center="true">
      <div class="w-full flex flex-col sm:justify-center items-center pt-6 sm:pt-8">
        <!-- Header -->
        <div class="w-full max-w-md mb-6 px-6">
          <div class="flex justify-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
              <x-application-logo size="large" class="rounded-lg" alt="HD Tickets Logo" />
              <span class="text-2xl font-bold text-white">HD Tickets</span>
            </a>
          </div>
          <p class="text-center text-white/80 mt-2">Professional Sports Ticket Monitoring</p>
        </div>

        <div class="w-full sm:max-w-md mt-1 px-6 py-8 form-card shadow-2xl overflow-hidden sm:rounded-2xl">
          <!-- Header -->
          <div class="text-center mb-8">
            <div
              class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-900/20 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Verify Your Phone</h1>
            <p class="text-gray-600 dark:text-gray-300">
              We sent a 6-digit verification code to
              <span
                class="font-medium text-gray-900 dark:text-gray-100">{{ substr($user->phone, 0, -4) . '****' }}</span>
            </p>
          </div>

          <!-- Success/Error Messages -->
          @if (session('success'))
            <div class="mb-6 bg-success-50 border-l-4 border-success-500 p-4 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-success-700 font-medium">{{ session('success') }}</p>
              </div>
            </div>
          @endif

          @if ($errors->any())
            <div class="mb-6 bg-error-50 border-l-4 border-error-500 p-4 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-error-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-error-700 font-medium">{{ $errors->first() }}</p>
                </div>
              </div>
            </div>
          @endif

          <!-- Verification Form -->
          <form method="POST" action="{{ route('phone.verify') }}" x-data="phoneVerification()" x-init="init()"
            @submit.prevent="submitCode">
            @csrf

            <div class="space-y-6">
              <!-- Code Input -->
              <x-form.code-input id="verification_code" name="verification_code" :length="6"
                label="Verification Code" hint="Enter the 6-digit code sent to your phone" required :error="$errors->first('verification_code')"
                :alpine-model="'verificationCode'" :alpine-events="['input' => 'onCodeInput()']" />

              <!-- Submit Button -->
              <button type="submit" :disabled="!canSubmit || submitting"
                class="
                            w-full inline-flex justify-center items-center px-4 py-3 rounded-lg text-white font-medium
                            transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                            disabled:opacity-50 disabled:cursor-not-allowed
                        "
                :class="canSubmit && !submitting ? 'bg-primary-600 hover:bg-primary-700 hover:shadow-lg' : 'bg-gray-300'">
                <svg x-show="submitting" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <span x-text="submitting ? 'Verifying...' : 'Verify Phone Number'"></span>
              </button>

              <!-- Resend Code -->
              <div class="text-center">
                <template x-if="timeRemaining > 0">
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    Didn't receive the code?
                    <span class="font-medium">Resend in <span x-text="formatTime(timeRemaining)"></span></span>
                  </p>
                </template>

                <template x-if="timeRemaining === 0">
                  <div class="space-y-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Didn't receive the code?</p>
                    <button type="button" @click="resendCode" :disabled="resending"
                      class="
                                        text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300
                                        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 rounded-md px-2 py-1
                                        transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                                    ">
                      <span x-show="!resending">Resend Code</span>
                      <span x-show="resending" class="flex items-center">
                        <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                          aria-hidden="true">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                          </path>
                        </svg>
                        Sending...
                      </span>
                    </button>
                  </div>
                </template>
              </div>

              <!-- Skip Option -->
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor"
                      viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                      Phone verification is optional
                    </h4>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                      <p class="mb-3">
                        While phone verification improves your account security and enables SMS notifications,
                        you can skip this step and complete it later in your account settings.
                      </p>
                      <a href="{{ route('dashboard') }}"
                        class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition-colors">
                        Skip for now and continue to dashboard →
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="w-full max-w-md mt-8 text-center text-white/60 text-xs px-6">
          <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
          <p class="mt-1">Professional Sports Event Ticket Monitoring Platform</p>
        </div>
      </div>
    </x-layout.auth-container>

    <script>
      function phoneVerification() {
        return {
          verificationCode: '',
          submitting: false,
          resending: false,
          timeRemaining: 60, // 60 seconds countdown
          countdown: null,

          init() {
            this.startCountdown();
          },

          get canSubmit() {
            return this.verificationCode.length === 6 && !this.submitting;
          },

          onCodeInput() {
            // Auto-submit when 6 digits are entered
            if (this.verificationCode.length === 6 && !this.submitting) {
              this.$nextTick(() => {
                this.submitCode();
              });
            }
          },

          async submitCode() {
            if (!this.canSubmit) return;

            this.submitting = true;

            try {
              // Submit the form
              this.$el.submit();
            } catch (error) {
              console.error('Verification failed:', error);
              this.submitting = false;
            }
          },

          async resendCode() {
            if (this.resending) return;

            this.resending = true;

            try {
              const response = await fetch('{{ route('phone.resend') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
              });

              const data = await response.json();

              if (response.ok) {
                this.timeRemaining = 60;
                this.startCountdown();
                this.showToast('Verification code sent successfully!', 'success');
              } else {
                this.showToast(data.message || 'Failed to resend code. Please try again.', 'error');
              }
            } catch (error) {
              console.error('Resend failed:', error);
              this.showToast('Failed to resend code. Please try again.', 'error');
            } finally {
              this.resending = false;
            }
          },

          startCountdown() {
            if (this.countdown) {
              clearInterval(this.countdown);
            }

            this.countdown = setInterval(() => {
              if (this.timeRemaining > 0) {
                this.timeRemaining--;
              } else {
                clearInterval(this.countdown);
                this.countdown = null;
              }
            }, 1000);
          },

          formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
          },

          showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-success-500' : type === 'error' ? 'bg-error-500' : 'bg-blue-500';
            toast.className =
              `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
              toast.style.transform = 'translateY(0)';
            });

            setTimeout(() => {
              toast.style.transform = 'translateY(100%)';
              setTimeout(() => toast.remove(), 300);
            }, 3000);
          },

          destroy() {
            if (this.countdown) {
              clearInterval(this.countdown);
            }
          }
        }
      }

      // Clean up interval on page unload
      window.addEventListener('beforeunload', () => {
        // Alpine will handle cleanup automatically, but this is a safety net
        const intervals = window.setInterval(() => {}, Number.MAX_SAFE_INTEGER);
        for (let i = 1; i < intervals; i++) {
          window.clearInterval(i);
        }
      });
    </script>
  </body>

</html>
