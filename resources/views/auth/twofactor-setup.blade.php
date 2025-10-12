<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Two-Factor Authentication Setup - {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body class="h-full" x-data="twoFactorSetup()">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="mx-auto h-10 w-auto">
                <svg class="mx-auto h-12 w-auto text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1M10 17L6 13L7.41 11.59L10 14.17L16.59 7.58L18 9L10 17Z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Set up Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Add an extra layer of security to your sports events ticket monitoring account
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
                
                <!-- Step 1: Scan QR Code -->
                <div class="space-y-6" x-show="step === 1" x-transition>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900">1. Scan QR Code</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Use an authenticator app like Google Authenticator, Authy, or 1Password to scan this QR code:
                        </p>
                    </div>

                    <div class="flex justify-center">
                        <div class="rounded-lg border-2 border-gray-200 p-4">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>

                    <!-- Manual entry option -->
                    <div class="text-center">
                        <button 
                            @click="showManualEntry = !showManualEntry"
                            type="button" 
                            class="text-sm font-medium text-purple-600 hover:text-purple-500"
                        >
                            Can't scan? Enter code manually
                        </button>
                    </div>

                    <div x-show="showManualEntry" x-transition class="rounded-md bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-900 mb-2">Manual setup key:</p>
                        <div class="flex items-center space-x-2">
                            <code class="text-sm bg-white px-2 py-1 rounded border font-mono">{{ $secretKey }}</code>
                            <button 
                                @click="copyToClipboard('{{ $secretKey }}')"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                            >
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <form action="{{ route('register.twofactor.skip') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-500">
                                Skip for now
                            </button>
                        </form>
                        
                        <button 
                            @click="step = 2" 
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                        >
                            Next
                        </button>
                    </div>
                </div>

                <!-- Step 2: Verify Code -->
                <div class="space-y-6" x-show="step === 2" x-transition>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900">2. Verify Setup</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Enter the 6-digit code from your authenticator app to confirm setup:
                        </p>
                    </div>

                    <form action="{{ route('register.twofactor.enable') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">
                                Verification Code
                            </label>
                            <div class="mt-1">
                                <input 
                                    id="code" 
                                    name="code" 
                                    type="text" 
                                    autocomplete="off"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    required
                                    x-model="verificationCode"
                                    @input="verificationCode = $event.target.value.replace(/\D/g, '').slice(0, 6)"
                                    class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 text-center text-lg font-mono placeholder-gray-400 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500"
                                    placeholder="000000"
                                />
                            </div>
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-between">
                            <button 
                                @click="step = 1" 
                                type="button"
                                class="text-sm font-medium text-gray-600 hover:text-gray-500"
                            >
                                Back
                            </button>

                            <div class="flex space-x-3">
                                <form action="{{ route('register.twofactor.skip') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-500">
                                        Skip for now
                                    </button>
                                </form>
                                
                                <button 
                                    type="submit"
                                    :disabled="verificationCode.length !== 6"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Enable Two-Factor Authentication
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <!-- Progress indicator -->
            <div class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    <div 
                        class="h-2 w-2 rounded-full transition-colors"
                        :class="step === 1 ? 'bg-purple-600' : 'bg-gray-300'"
                    ></div>
                    <div 
                        class="h-2 w-2 rounded-full transition-colors"
                        :class="step === 2 ? 'bg-purple-600' : 'bg-gray-300'"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function twoFactorSetup() {
            return {
                step: 1,
                showManualEntry: false,
                verificationCode: '',
                
                copyToClipboard(text) {
                    navigator.clipboard.writeText(text).then(() => {
                        // You could show a toast notification here
                        console.log('Copied to clipboard');
                    });
                }
            }
        }
    </script>
</body>
</html>