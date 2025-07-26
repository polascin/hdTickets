<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Registration Restricted
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Access to HD Tickets registration is limited to authorized administrators
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10 border border-gray-100">
                <!-- Info Icon and Title -->
                <div class="flex items-center justify-center mb-6">
                    <div class="p-3 bg-blue-100 rounded-full">  
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Main Message -->
                <div class="text-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                        Administrator Approval Required
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        For security reasons, new user accounts can only be created by system administrators. 
                        This ensures proper access control and maintains system security.
                    </p>
                </div>

                <!-- Features List -->
                <div class="space-y-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Enhanced Security</p>
                            <p class="text-sm text-gray-500">Admin-only registration prevents unauthorized access</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Proper Role Assignment</p>
                            <p class="text-sm text-gray-500">Administrators assign appropriate access levels</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Account Verification</p>
                            <p class="text-sm text-gray-500">All new accounts are properly verified</p>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Need an Account?</h4>
                    <p class="text-sm text-gray-600 mb-3">
                        Contact your system administrator to request access to HD Tickets. 
                        They will create your account with the appropriate permissions.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Reach out via email or internal support channels
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('login') }}" 
                       class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Sign In to Your Account
                    </a>
                    
                    <a href="{{ url('/') }}" 
                       class="w-full flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
