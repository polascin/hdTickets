@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Please enter your authentication code to continue
            </p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                @foreach ($errors->all() as $error)
                    <span class="block sm:inline">{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('2fa.verify') }}" method="POST">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="relative">
                    <label for="code" class="sr-only">Authentication Code</label>
                    <input id="code" name="code" type="text" autocomplete="off" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm text-center text-xl tracking-widest font-mono" 
                           placeholder="000000" maxlength="8">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="recovery" name="recovery" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="recovery" class="ml-2 block text-sm text-gray-900">
                        Using recovery code
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Verify
                </button>
            </div>

            <div class="text-center space-y-2">
                <p class="text-sm text-gray-600">
                    Having trouble? Try these options:
                </p>
                <div class="flex space-x-4 justify-center">
                    <form action="{{ route('2fa.sms-code') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-500 text-sm">
                            Send SMS Code
                        </button>
                    </form>
                    <form action="{{ route('2fa.email-code') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-500 text-sm">
                            Send Email Code
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-500 mt-4">
                    Lost your device? <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">Contact support</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const recoveryCheckbox = document.getElementById('recovery');
    
    // Auto-format input
    codeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '');
        if (!recoveryCheckbox.checked && value.length === 6) {
            // TOTP code formatting
            e.target.value = value;
        } else if (recoveryCheckbox.checked) {
            // Recovery code formatting (XXXX-XXXX)
            value = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            if (value.length > 4) {
                value = value.substring(0, 4) + '-' + value.substring(4, 8);
            }
            e.target.value = value;
        }
    });
    
    // Change placeholder and max length based on code type
    recoveryCheckbox.addEventListener('change', function() {
        if (this.checked) {
            codeInput.placeholder = 'XXXX-XXXX';
            codeInput.maxLength = '9';
            codeInput.classList.add('tracking-wider');
        } else {
            codeInput.placeholder = '000000';
            codeInput.maxLength = '6';
            codeInput.classList.remove('tracking-wider');
        }
        codeInput.value = '';
        codeInput.focus();
    });
    
    // Focus on load
    codeInput.focus();
});
</script>
@endsection
