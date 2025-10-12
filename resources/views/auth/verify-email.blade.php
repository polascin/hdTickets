<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Verify Your Email Address - {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="mx-auto h-10 w-auto">
                <svg class="mx-auto h-12 w-auto text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2M21 9V7L12 2L3 7V9C3 14.55 6.84 19.74 12 21C17.16 19.74 21 14.55 21 9M12 7C13.1 7 14 7.9 14 9S13.1 11 12 11 10 10.1 10 9 10.9 7 12 7M18 9C18 12.75 15.39 16.09 12 17.92C8.61 16.09 6 12.75 6 9H7.5C7.5 12.37 9.81 15.21 12 16C14.19 15.21 16.5 12.37 16.5 9H18M12 15L16 11H13V8L8 13H11V16L12 15Z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Verify Your Email Address
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                We've sent you a verification link to secure your sports events monitoring account
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
                
                <!-- Success messages -->
                @if (session('status'))
                    <div class="mb-6 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    @if (session('status') === 'verification-link-sent')
                                        A new verification link has been sent to your email address.
                                    @else
                                        {{ session('status') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (session('info'))
                    <div class="mb-6 rounded-md bg-blue-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Main content -->
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Check Your Email</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Thanks for signing up for sports events ticket monitoring! Before you can start tracking your favourite events, 
                        please verify your email address by clicking on the link we've just sent to:
                    </p>
                    <p class="text-sm font-medium text-purple-600 mb-4">{{ auth()->user()->email }}</p>
                    <p class="text-xs text-gray-500">
                        If you don't see the email in your inbox, please check your spam folder.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Resend verification email -->
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Resend Verification Email
                        </button>
                    </form>

                    <!-- Alternative actions -->
                    <div class="flex items-center justify-between text-sm">
                        <a href="{{ route('profile.edit') }}" class="font-medium text-purple-600 hover:text-purple-500">
                            Change Email Address
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="font-medium text-gray-600 hover:text-gray-500">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Help text -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Having trouble? <a href="mailto:support@hdtickets.com" class="font-medium text-purple-600 hover:text-purple-500">Contact support</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
