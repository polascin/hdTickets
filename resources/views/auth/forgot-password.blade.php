<x-guest-layout>
    <!-- Professional Header Section -->
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2M7 7a2 2 0 012-2h6m0 0V3a1 1 0 011-1h4a1 1 0 011 1v1M9 7h6m-7 8a2 2 0 002 2h6a2 2 0 002-2M9 15h6"></path>
            </svg>
        </div>
        <h2 class="mt-4 text-2xl font-bold text-gray-900">Reset Your Password</h2>
    </div>

    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>How it works:</strong> Enter your email address below and we'll send you a secure link to reset your password. 
                    The link will expire in 60 minutes for security purposes.
                </p>
            </div>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address with Enhanced Styling -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Email Address') }}
                <span class="text-red-500 ml-1">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                <input id="email" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus
                       placeholder="Enter your email address"
                       aria-describedby="email-help" />
            </div>
            <div id="email-help" class="mt-2 text-sm text-gray-600">
                We'll send password reset instructions to this email address.
            </div>
            @if ($errors->get('email'))
                <div class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $errors->first('email') }}
                </div>
            @endif
        </div>

        <!-- Security Information -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Security Notice</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Reset links expire after 60 minutes</li>
                            <li>Check your spam folder if you don't see the email</li>
                            <li>If you don't have an account, no email will be sent</li>
                            <li>For security, we won't confirm if the email exists</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col space-y-4">
            <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                {{ __('Send Password Reset Email') }}
            </button>
            
            <!-- Back to Login Link -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium transition duration-200">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </form>

    <!-- Additional Help Section -->
    <div class="mt-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Need Additional Help?
        </h3>
        <div class="space-y-4">
            <div class="border-l-4 border-blue-400 pl-4">
                <h4 class="font-medium text-gray-900">Can't access your email?</h4>
                <p class="text-sm text-gray-600">Contact your system administrator for assistance with password recovery.</p>
            </div>
            <div class="border-l-4 border-green-400 pl-4">
                <h4 class="font-medium text-gray-900">Technical Support</h4>
                <div class="text-sm text-gray-600">
                    <a href="mailto:support@hdtickets.local" class="text-blue-600 hover:text-blue-500 font-medium">
                        support@hdtickets.local
                    </a>
                    <span class="text-gray-400 mx-2">•</span>
                    <span>Response within 24 hours</span>
                </div>
            </div>
            <div class="border-l-4 border-yellow-400 pl-4">
                <h4 class="font-medium text-gray-900">Account Issues</h4>
                <p class="text-sm text-gray-600">If you believe your account has been compromised, contact support immediately.</p>
            </div>
        </div>
    </div>
</x-guest-layout>
