@extends('layouts.app-v2')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="max-w-2xl mx-auto">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            Enable Two-Factor Authentication
                        </h2>
                        <p class="text-gray-600">
                            Secure your account by enabling two-factor authentication using an authenticator app.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                            @foreach ($errors->all() as $error)
                                <span class="block sm:inline">{{ $error }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-6">
                        <!-- Step 1: Download App -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">
                                Step 1: Install an Authenticator App
                            </h3>
                            <p class="text-blue-800 mb-4">
                                Download and install one of these authenticator apps on your mobile device:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="bg-white p-3 rounded-lg shadow-sm">
                                        <div class="font-medium text-gray-900">Google Authenticator</div>
                                        <div class="text-sm text-gray-600">iOS & Android</div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="bg-white p-3 rounded-lg shadow-sm">
                                        <div class="font-medium text-gray-900">Microsoft Authenticator</div>
                                        <div class="text-sm text-gray-600">iOS & Android</div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="bg-white p-3 rounded-lg shadow-sm">
                                        <div class="font-medium text-gray-900">Authy</div>
                                        <div class="text-sm text-gray-600">iOS & Android</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Scan QR Code -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 mb-3">
                                Step 2: Scan the QR Code
                            </h3>
                            <div class="flex flex-col lg:flex-row items-center space-y-4 lg:space-y-0 lg:space-x-6">
                                <div class="flex-shrink-0">
                                    <div class="bg-white p-4 rounded-lg shadow-sm border">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-green-800 mb-4">
                                        Open your authenticator app and scan this QR code to add your account.
                                    </p>
                                    <div class="bg-white p-3 rounded border">
                                        <p class="text-sm text-gray-600 mb-2">
                                            <strong>Can't scan?</strong> Enter this key manually:
                                        </p>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono break-all">
                                            {{ $secret }}
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Verify -->
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-3">
                                Step 3: Enter Verification Code
                            </h3>
                            <p class="text-yellow-800 mb-4">
                                Enter the 6-digit code from your authenticator app to complete setup.
                            </p>
                            
                            <form action="{{ route('2fa.enable') }}" method="POST" class="max-w-sm">
                                @csrf
                                <div class="flex space-x-2">
                                    <input type="text" 
                                           name="code" 
                                           id="verification-code"
                                           class="flex-1 text-center text-xl font-mono tracking-widest border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" 
                                           placeholder="000000" 
                                           maxlength="6" 
                                           autocomplete="off"
                                           required>
                                    <button type="submit" 
                                            class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Verify & Enable
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                Important Security Notes
                            </h3>
                            <ul class="text-gray-700 space-y-2">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    After enabling 2FA, you'll receive recovery codes. Store them safely!
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Each code from your app can only be used once.
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Make sure your device's time is synchronized.
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Keep your recovery codes in a secure location.
                                </li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('profile.edit') }}" 
                               class="text-gray-600 hover:text-gray-800 underline">
                                ← Back to Profile Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('verification-code');
    
    codeInput.addEventListener('input', function(e) {
        // Only allow numbers
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
    
    codeInput.focus();
});
</script>
@endsection
