<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index, follow">

    <title>{{ __('Register') }} - HD Tickets</title>

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])

    <!-- SEO Meta -->
    <meta name="description"
      content="Register for HD Tickets - Professional sports event ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta name="keywords"
      content="HD Tickets registration, sports ticket monitoring signup, professional sports platform, subscription registration, ticket monitoring account">

    <!-- Open Graph -->
    <meta property="og:title" content="Register - HD Tickets Professional Sports Monitoring">
    <meta property="og:description"
      content="Register for HD Tickets professional sports ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Register - HD Tickets Professional Sports Monitoring">
    <meta name="twitter:description"
      content="Register for HD Tickets professional sports ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <style>
      .registration-bg {
        background: radial-gradient(1200px 600px at 50% -200px, rgba(59, 130, 246, 0.25), rgba(99, 102, 241, 0.15) 40%, rgba(16, 185, 129, 0.1) 70%, transparent),
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

        .registration-bg {
          background: linear-gradient(135deg, #0f172a 0%, #0b1733 50%, #052e16 100%);
        }
      }

      [x-cloak] {
        display: none !important;
      }
    </style>
  </head>

  <body class="font-sans antialiased registration-bg">
    <x-layout.auth-container :panel="false" center="true">
      <div class="w-full flex flex-col sm:justify-center items-center pt-6 sm:pt-8">
        <!-- Header -->
        <div class="w-full max-w-2xl mb-6 px-6">
          <div class="flex justify-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo"
                class="w-12 h-12 rounded-lg">
              <span class="text-2xl font-bold text-white">HD Tickets</span>
            </a>
          </div>
          <p class="text-center text-white/80 mt-2">Professional Sports Ticket Monitoring</p>
        </div>

        <div class="w-full sm:max-w-2xl mt-1 px-6 py-5 form-card shadow-2xl overflow-hidden sm:rounded-2xl">
          <!-- Registration Form Header -->
          <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create Your Account</h1>
            <p class="text-gray-600 dark:text-gray-300">Join HD Tickets and start monitoring sports events</p>
            <div class="flex justify-center space-x-4 mt-4 text-sm">
              <div class="flex items-center text-success-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                7-day free trial
              </div>
              <div class="flex items-center text-primary-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                    clip-rule="evenodd" />
                </svg>
                Secure & GDPR compliant
              </div>
            </div>
          </div>

          <!-- OAuth Registration Options -->
          <div class="mb-6">
            <div class="grid grid-cols-1 gap-3">
              @php
                  $oauthService = app('App\Services\OAuthUserService');
                  $providers = $oauthService->getSupportedProviders();
              @endphp
              
              @foreach($providers as $provider => $config)
                  @if($config['enabled'])
                      <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}"
                         class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 transform hover:scale-[1.01] min-h-[48px] touch-manipulation"
                         aria-label="Register with {{ $config['name'] }}">
                          
                          @if($provider === 'google')
                              <!-- Google Icon -->
                              <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                              </svg>
                          @else
                              <i class="{{ $config['icon'] }} text-lg mr-3"></i>
                          @endif
                          
                          <span>Continue with {{ $config['name'] }}</span>
                      </a>
                  @endif
              @endforeach
            </div>
            
            <div class="relative mt-6 mb-6">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-3 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 font-medium">Or register with email</span>
              </div>
            </div>
          </div>

          <!-- Stepper -->
          <x-ui.stepper :steps="[['title' => 'Account'], ['title' => 'Security'], ['title' => 'Legal']]" :current-step="(int) old('current_step', 1)" class="mb-6" />

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

          @if ($errors->any() && $errors->has('error'))
            <div class="mb-6 bg-error-50 border-l-4 border-error-500 p-4 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-error-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-error-700 font-medium">{{ $errors->first('error') }}</p>
              </div>
            </div>
          @endif

          <!-- Registration Form -->
          <form method="POST" action="{{ route('register.public') }}" id="registration-form" x-data="registrationForm()"
            x-init="init()" novalidate>
            @csrf

            <!-- Honeypot field for bot protection -->
            <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off" />
            <input type="hidden" name="current_step" x-model="currentStep" />

            <!-- Steps Container -->
            <div class="space-y-6">
              <!-- Step 1: Account Details -->
              <section x-show="currentStep === 1" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <x-form.input id="name" name="name" label="First Name" required autocomplete="given-name"
                    placeholder="John" :value="old('name')" :error="$errors->first('name')" :alpine-model="'fields.name'" :alpine-events="['blur' => 'touch(\'name\')', 'input' => 'validate()']" />

                  <x-form.input id="surname" name="surname" label="Last Name" autocomplete="family-name"
                    placeholder="Doe" :value="old('surname')" :error="$errors->first('surname')" :alpine-model="'fields.surname'" :alpine-events="['blur' => 'touch(\'surname\')', 'input' => 'validate()']" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                  <x-form.input id="email" name="email" type="email" label="Email Address" required
                    autocomplete="email" placeholder="you@example.com" :value="old('email')" :error="$errors->first('email')"
                    :alpine-model="'fields.email'" :alpine-events="['blur' => 'touch(\'email\')', 'input' => 'validate()']" />

                  <x-form.input id="phone" name="phone" type="tel" label="Phone Number"
                    hint="Optional - include country code (e.g., +1234567890)" autocomplete="tel"
                    placeholder="+1234567890" :value="old('phone')" :error="$errors->first('phone')" :alpine-model="'fields.phone'"
                    :alpine-events="['blur' => 'touch(\'phone\')', 'input' => 'validate()']" />
                </div>
              </section>

              <!-- Step 2: Security -->
              <section x-show="currentStep === 2" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <x-form.password id="password" name="password" label="Password" required :error="$errors->first('password')"
                    :alpine-validation="'validation.password ? \ ?>'"border-error-300 dark:border-error-600\" : \"\"'" :alpine-model="'fields.password'" />

                  <x-form.password id="password_confirmation" name="password_confirmation" label="Confirm Password"
                    required :error="$errors->first('password_confirmation')" :alpine-model="'fields.password_confirmation'" />
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/60 rounded-lg p-4 mt-4">
                  <h3 class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Security Options
                  </h3>

                  <label class="flex items-start space-x-3 cursor-pointer mt-3">
                    <input type="checkbox" name="enable_2fa" value="1" {{ old('enable_2fa') ? 'checked' : '' }}
                      class="mt-1 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                    <div class="flex-1">
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Two-Factor
                        Authentication (2FA)</span>
                      <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Add an extra layer of security to your account with Google Authenticator or similar apps.
                      </p>
                    </div>
                  </label>
                </div>
              </section>

              <!-- Step 3: Legal -->
              <section x-show="currentStep === 3" x-cloak>
                <x-legal.acceptance :documents="$legalDocuments" :errors="$errors->toArray()" />

                <div class="mt-4 bg-warning-50 border-l-4 border-warning-500 p-4 rounded-lg">
                  <div class="flex items-start">
                    <svg class="w-5 h-5 text-warning-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                      aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                    </svg>
                    <div>
                      <h4 class="text-sm font-medium text-warning-800">Important Notice</h4>
                      <p class="text-sm text-warning-700 mt-1">
                        This service is provided "as-is" with no warranty or money-back guarantee. You will receive a
                        7-day free trial, after which subscription fees apply.
                      </p>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <!-- Navigation Controls -->
            <div class="mt-6">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                  <span>Step <span x-text="currentStep"></span> of 3</span>
                </div>
                <div class="grid grid-cols-2 sm:flex sm:space-x-3 gap-3">
                  <button type="button" x-on:click="prev()" x-bind:disabled="currentStep === 1"
                    class="
                                inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed
                            ">
                    Back
                  </button>

                  <button type="button" x-show="currentStep < 3" x-on:click="next()"
                    x-bind:disabled="!canContinue()"
                    class="
                                inline-flex items-center justify-center px-4 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed
                            ">
                    Continue
                  </button>

                  <button type="submit" x-show="currentStep === 3" x-bind:disabled="!canSubmit() || submitting"
                    id="register-button"
                    class="
                                inline-flex items-center justify-center px-4 py-2 rounded-lg text-white bg-success-600 hover:bg-success-700 transition disabled:opacity-50 disabled:cursor-not-allowed
                            ">
                    <svg x-show="submitting" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                      aria-hidden="true">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                      </path>
                    </svg>
                    <span x-text="submitting ? 'Creating Account...' : 'Create My Account'"></span>
                  </button>
                </div>
              </div>

              <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-4">
                Already have an account?
                <a href="{{ route('login') }}"
                  class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                  Sign in here
                </a>
              </p>
            </div>
          </form>
        </div>

        <!-- Features Section -->
        <div class="w-full max-w-2xl mt-8 px-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div class="text-white">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 bg-primary-600 shadow-glow">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 class="font-semibold text-sm">Real-Time Monitoring</h3>
              <p class="text-xs text-white/80 mt-1">Track prices across 50+ platforms</p>
            </div>
            <div class="text-white">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 bg-secondary-500 shadow-glow">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5 5v-5zM9 15v-1.5A2.5 2.5 0 0111.5 11h1A2.5 2.5 0 0115 13.5V15" />
                </svg>
              </div>
              <h3 class="font-semibold text-sm">Automated Alerts</h3>
              <p class="text-xs text-white/80 mt-1">Get notified when prices drop</p>
            </div>
            <div class="text-white">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 bg-success-600 shadow-glow">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h3 class="font-semibold text-sm">Enterprise Security</h3>
              <p class="text-xs text-white/80 mt-1">GDPR compliant with 2FA support</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="w-full max-w-2xl mt-8 text-center text-white/60 text-xs px-6">
          <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
          <p class="mt-1">Professional Sports Event Ticket Monitoring Platform</p>
        </div>
      </div>
    </x-layout.auth-container>

    <script>
      function registrationForm() {
        return {
          currentStep: Number(@json((int) old('current_step', 1))),
          submitting: false,
          touched: {},
          fields: {
            name: @json(old('name')),
            surname: @json(old('surname')),
            email: @json(old('email')),
            phone: @json(old('phone')),
            password: '',
            password_confirmation: ''
          },
          init() {
            this.validate();
          },
          touch(field) {
            this.touched[field] = true;
          },
          next() {
            if (this.currentStep < 3 && this.canContinue()) this.currentStep++;
          },
          prev() {
            if (this.currentStep > 1) this.currentStep--;
          },
          canContinue() {
            if (this.currentStep === 1) {
              return !!(this.fields.name && this.fields.email && /.+@.+\..+/.test(this.fields.email));
            }
            if (this.currentStep === 2) {
              const p = this.fields.password || '';
              const c = this.fields.password_confirmation || '';
              const rulesMet = p.length >= 8 && /[a-z]/.test(p) && /[A-Z]/.test(p) && /[0-9]/.test(p) && /[^A-Za-z0-9]/
                .test(p);
              return rulesMet && p === c;
            }
            return true;
          },
          canSubmit() {
            // Ensure all required legal checkboxes are checked before enabling submit
            const required = document.querySelectorAll('input[type=checkbox][name^=accept_][required]');
            if (!required.length) return false;
            for (const cb of required) {
              if (!cb.checked) return false;
            }
            return true;
          },
          validate() {
            // Basic client-side guidance; server remains source of truth
          }
        }
      }

      // Prevent double-submit
      document.addEventListener('alpine:init', () => {
        const form = document.getElementById('registration-form');
        if (form) {
          form.addEventListener('submit', function() {
            const comp = Alpine.$data(form);
            if (comp) comp.submitting = true;
          });
        }
      });
    </script>
  </body>

</html>
