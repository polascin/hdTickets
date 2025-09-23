<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - HD Tickets</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <div x-data="passwordReset()" x-init="init()">
      <!-- Header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <div
                  class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                  <span class="text-white font-bold text-sm">HD</span>
                </div>
                <span class="text-xl font-bold text-gray-900">HD Tickets</span>
              </a>
              <div class="hidden md:block">
                <x-ui.badge variant="info" dot="true">Sports Events Platform</x-ui.badge>
              </div>
            </div>

            <div class="flex items-center space-x-4">
              <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">
                ← Back to Login
              </a>
              <a href="{{ route('help.contact') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Need Help?
              </a>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

          <!-- Progress Indicator -->
          <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center mr-3 transition"
                  :class="currentStep >= 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                  <svg x-show="currentStep > 1" class="w-4 h-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span x-show="currentStep === 1" class="text-sm font-medium">1</span>
                </div>
                <span class="text-sm font-medium">Request Reset</span>
              </div>

              <div class="flex-1 mx-4">
                <div class="h-1 bg-gray-200 rounded-full">
                  <div class="h-1 bg-blue-600 rounded-full transition-all duration-500"
                    :style="`width: ${Math.max(0, Math.min(100, (currentStep - 1) * 50))}%`"></div>
                </div>
              </div>

              <div class="flex items-center" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center mr-3 transition"
                  :class="currentStep >= 2 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                  <svg x-show="currentStep > 2" class="w-4 h-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span x-show="currentStep <= 2" class="text-sm font-medium">2</span>
                </div>
                <span class="text-sm font-medium">Check Email</span>
              </div>

              <div class="flex-1 mx-4">
                <div class="h-1 bg-gray-200 rounded-full">
                  <div class="h-1 bg-blue-600 rounded-full transition-all duration-500"
                    :style="`width: ${Math.max(0, Math.min(100, (currentStep - 2) * 100))}%`"></div>
                </div>
              </div>

              <div class="flex items-center" :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition"
                  :class="currentStep >= 3 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                  <span class="text-sm font-medium">3</span>
                </div>
                <span class="text-sm font-medium ml-3">New Password</span>
              </div>
            </div>
          </div>

          <!-- Step 1: Request Reset -->
          <div x-show="currentStep === 1" x-transition class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                  </path>
                </svg>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">Reset Your Password</h1>
              <p class="text-gray-600">Enter your email address and we'll send you a secure link to reset your password
              </p>
            </div>

            <form @submit.prevent="requestReset()" class="space-y-6">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                      </path>
                    </svg>
                  </div>
                  <input type="email" x-model="email" id="email" name="email"
                    placeholder="your.email@example.com"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    :class="emailError ? 'border-red-300 ring-red-500' : ''" autocomplete="email" required>
                </div>
                <p x-show="emailError" x-text="emailError" class="text-red-600 text-sm mt-1" x-transition></p>
                <p class="text-xs text-gray-500 mt-2">
                  We'll send password reset instructions to this email address
                </p>
              </div>

              <!-- Security Features -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">🔒 Security Features</h3>
                <div class="space-y-2">
                  <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    Secure encrypted email delivery
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    Link expires in 1 hour for safety
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    Single-use secure token system
                  </div>
                </div>
              </div>

              <button type="submit" :disabled="requesting || !email"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                <span x-show="!requesting">Send Reset Link</span>
                <span x-show="requesting" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                      stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Sending Reset Link...
                </span>
              </button>
            </form>

            <!-- Alternative Options -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <div class="text-center space-y-3">
                <p class="text-sm text-gray-600">Remember your password?</p>
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                  Sign in to your account →
                </a>
              </div>
            </div>
          </div>

          <!-- Step 2: Check Email -->
          <div x-show="currentStep === 2" x-transition class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                  </path>
                </svg>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">Check Your Email</h1>
              <p class="text-gray-600 mb-4">
                We've sent password reset instructions to:<br>
                <span class="font-medium text-gray-900" x-text="email"></span>
              </p>
            </div>

            <!-- Email Instructions -->
            <div class="space-y-6 mb-8">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">📧 Next Steps</h3>
                <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                  <li>Open your email inbox</li>
                  <li>Look for an email from "HD Tickets Security"</li>
                  <li>Click the "Reset Password" button in the email</li>
                  <li>Create your new secure password</li>
                </ol>
              </div>

              <!-- Timer Display -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="font-medium text-yellow-900">⏰ Link Expires In</h3>
                    <p class="text-sm text-yellow-800">For your security, this reset link is time-limited</p>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-mono font-bold text-yellow-900" x-text="formatTime(timeRemaining)">59:00
                    </div>
                    <div class="text-xs text-yellow-700">minutes</div>
                  </div>
                </div>
              </div>

              <!-- Troubleshooting -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-3">Don't see the email?</h3>
                <div class="space-y-2 text-sm text-gray-600">
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Check your spam or junk folder</span>
                  </div>
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Make sure you entered the correct email address</span>
                  </div>
                  <div class="flex items-start">
                    <span class="w-4 text-gray-400">•</span>
                    <span class="ml-2">Email delivery can take up to 5 minutes</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <button @click="resendEmail()" :disabled="resending || timeRemaining > 3540"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                <span x-show="!resending">
                  <span x-show="timeRemaining > 3540">Resend Available in <span
                      x-text="Math.ceil((3600 - timeRemaining) / 60)"></span>m</span>
                  <span x-show="timeRemaining <= 3540">Resend Reset Email</span>
                </span>
                <span x-show="resending" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                      stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Resending...
                </span>
              </button>

              <button @click="currentStep = 1"
                class="w-full text-gray-600 hover:text-gray-800 py-2 text-sm font-medium transition">
                ← Use Different Email Address
              </button>
            </div>
          </div>

          <!-- Step 3: Reset Password (via URL parameter) -->
          <div x-show="currentStep === 3" x-transition class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
              <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                  </path>
                </svg>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">Create New Password</h1>
              <p class="text-gray-600">Choose a strong, secure password for your HD Tickets account</p>
            </div>

            <form @submit.prevent="resetPassword()" class="space-y-6">
              <!-- New Password -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                  New Password
                </label>
                <div class="relative">
                  <input :type="showPassword ? 'text' : 'password'" x-model="password" id="password"
                    name="password" placeholder="Enter your new password"
                    class="block w-full pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    :class="passwordError ? 'border-red-300 ring-red-500' : ''" autocomplete="new-password" required>
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg x-show="!showPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none"
                      stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                      </path>
                    </svg>
                    <svg x-show="showPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none"
                      stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                      </path>
                    </svg>
                  </button>
                </div>
                <p x-show="passwordError" x-text="passwordError" class="text-red-600 text-sm mt-1" x-transition></p>

                <!-- Password Strength Indicator -->
                <div class="mt-3">
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">Password Strength</span>
                    <span :class="getStrengthColor(passwordStrength)"
                      x-text="getStrengthText(passwordStrength)"></span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300"
                      :class="getStrengthColor(passwordStrength)" :style="`width: ${passwordStrength * 25}%`"></div>
                  </div>
                </div>
              </div>

              <!-- Confirm Password -->
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirm New Password
                </label>
                <div class="relative">
                  <input :type="showConfirmPassword ? 'text' : 'password'" x-model="passwordConfirmation"
                    id="password_confirmation" name="password_confirmation" placeholder="Confirm your new password"
                    class="block w-full pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    :class="confirmError ? 'border-red-300 ring-red-500' : ''" autocomplete="new-password" required>
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg x-show="!showConfirmPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                      </path>
                    </svg>
                    <svg x-show="showConfirmPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                      </path>
                    </svg>
                  </button>
                </div>
                <p x-show="confirmError" x-text="confirmError" class="text-red-600 text-sm mt-1" x-transition></p>
              </div>

              <!-- Password Requirements -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Password Requirements</h3>
                <div class="space-y-2">
                  <div class="flex items-center text-sm"
                    :class="password.length >= 8 ? 'text-green-600' : 'text-gray-600'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    At least 8 characters
                  </div>
                  <div class="flex items-center text-sm"
                    :class="/[A-Z]/.test(password) ? 'text-green-600' : 'text-gray-600'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    One uppercase letter
                  </div>
                  <div class="flex items-center text-sm"
                    :class="/[a-z]/.test(password) ? 'text-green-600' : 'text-gray-600'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    One lowercase letter
                  </div>
                  <div class="flex items-center text-sm"
                    :class="/\d/.test(password) ? 'text-green-600' : 'text-gray-600'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    One number
                  </div>
                  <div class="flex items-center text-sm"
                    :class="/[^A-Za-z0-9]/.test(password) ? 'text-green-600' : 'text-gray-600'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                      </path>
                    </svg>
                    One special character (!@#$%^&*)
                  </div>
                </div>
              </div>

              <button type="submit" :disabled="resetting || passwordStrength < 3"
                class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center">
                <span x-show="!resetting">Reset Password</span>
                <span x-show="resetting" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                      stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Updating Password...
                </span>
              </button>
            </form>
          </div>

          <!-- Footer Links -->
          <div class="mt-8 text-center space-y-2">
            <div class="flex justify-center space-x-4 text-sm">
              <a href="{{ route('help.support') }}" class="text-gray-600 hover:text-gray-900">Support</a>
              <span class="text-gray-300">•</span>
              <a href="{{ route('help.security') }}" class="text-gray-600 hover:text-gray-900">Security</a>
              <span class="text-gray-300">•</span>
              <a href="{{ route('legal.privacy') }}" class="text-gray-600 hover:text-gray-900">Privacy</a>
            </div>
            <p class="text-xs text-gray-500">
              Secure password reset powered by HD Tickets
            </p>
          </div>
        </div>
      </main>
    </div>

    <script>
      function passwordReset() {
        return {
          currentStep: {{ request()->has('token') ? 3 : 1 }},
          email: '{{ old('email', request('email', '')) }}',
          password: '',
          passwordConfirmation: '',
          showPassword: false,
          showConfirmPassword: false,
          requesting: false,
          resending: false,
          resetting: false,
          timeRemaining: 3600, // 1 hour in seconds
          timer: null,
          emailError: '',
          passwordError: '',
          confirmError: '',

          init() {
            if (this.currentStep === 2) {
              this.startTimer();
            }

            // Watch password for strength calculation
            this.$watch('password', () => {
              this.calculatePasswordStrength();
              this.validatePasswordMatch();
            });

            this.$watch('passwordConfirmation', () => {
              this.validatePasswordMatch();
            });
          },

          get passwordStrength() {
            return this.calculatePasswordStrength();
          },

          calculatePasswordStrength() {
            if (!this.password) return 0;

            let strength = 0;

            // Length check
            if (this.password.length >= 8) strength++;

            // Character variety checks
            if (/[A-Z]/.test(this.password)) strength++;
            if (/[a-z]/.test(this.password)) strength++;
            if (/\d/.test(this.password)) strength++;
            if (/[^A-Za-z0-9]/.test(this.password)) strength++;

            // Bonus for longer passwords
            if (this.password.length >= 12) strength++;

            return Math.min(4, strength);
          },

          getStrengthColor(strength) {
            const colors = {
              0: 'bg-gray-300',
              1: 'bg-red-500',
              2: 'bg-orange-500',
              3: 'bg-yellow-500',
              4: 'bg-green-500'
            };
            return colors[strength] || colors[0];
          },

          getStrengthText(strength) {
            const texts = {
              0: 'Very Weak',
              1: 'Weak',
              2: 'Fair',
              3: 'Good',
              4: 'Strong'
            };
            return texts[strength] || texts[0];
          },

          validatePasswordMatch() {
            if (this.passwordConfirmation && this.password !== this.passwordConfirmation) {
              this.confirmError = 'Passwords do not match';
            } else {
              this.confirmError = '';
            }
          },

          async requestReset() {
            this.requesting = true;
            this.emailError = '';

            try {
              const response = await fetch('{{ route('password.email') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  email: this.email
                })
              });

              const data = await response.json();

              if (data.success || response.status === 200) {
                this.currentStep = 2;
                this.startTimer();
                this.showNotification('Reset Link Sent', 'Check your email for password reset instructions', 'success');
              } else {
                this.emailError = data.message || 'Unable to send reset link. Please try again.';
              }
            } catch (error) {
              this.emailError = 'Network error. Please check your connection and try again.';
            } finally {
              this.requesting = false;
            }
          },

          async resendEmail() {
            this.resending = true;

            try {
              await this.requestReset();
              this.timeRemaining = 3600; // Reset timer
            } catch (error) {
              this.showNotification('Error', 'Failed to resend email. Please try again.', 'error');
            } finally {
              this.resending = false;
            }
          },

          async resetPassword() {
            if (this.passwordStrength < 3) {
              this.passwordError = 'Please choose a stronger password';
              return;
            }

            if (this.password !== this.passwordConfirmation) {
              this.confirmError = 'Passwords do not match';
              return;
            }

            this.resetting = true;
            this.passwordError = '';
            this.confirmError = '';

            try {
              const response = await fetch('{{ route('password.update') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  token: '{{ request('token') }}',
                  email: '{{ request('email') }}',
                  password: this.password,
                  password_confirmation: this.passwordConfirmation
                })
              });

              const data = await response.json();

              if (data.success || response.status === 200) {
                this.showNotification('Password Updated!', 'Your password has been successfully reset', 'success');
                setTimeout(() => {
                  window.location.href = '{{ route('login') }}?reset=success';
                }, 2000);
              } else {
                this.passwordError = data.message || 'Unable to reset password. Please try again.';
              }
            } catch (error) {
              this.passwordError = 'Network error. Please try again.';
            } finally {
              this.resetting = false;
            }
          },

          startTimer() {
            this.timer = setInterval(() => {
              if (this.timeRemaining > 0) {
                this.timeRemaining--;
              } else {
                clearInterval(this.timer);
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
  </body>

</html>
