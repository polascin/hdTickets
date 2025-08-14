<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Security Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Security Checkup Card --}}
            @if(isset($securityCheckup))
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Security Checkup') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Overall security score and recommendations.') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold {{ $securityCheckup['score'] >= 80 ? 'text-green-600' : ($securityCheckup['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $securityCheckup['score'] }}%
                        </div>
                        <div class="text-sm text-gray-500">Security Score</div>
                    </div>
                </div>
                
                @if($securityCheckup['critical_issues'] > 0)
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ $securityCheckup['critical_issues'] }} Critical Security {{ $securityCheckup['critical_issues'] == 1 ? 'Issue' : 'Issues' }}
                            </h3>
                            <p class="mt-1 text-sm text-red-700">
                                Please address these issues immediately to secure your account.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($securityCheckup['issues']))
                <div class="space-y-3">
                    @foreach($securityCheckup['issues'] as $issue)
                    <div class="flex items-start p-3 border rounded-lg {{ $issue['type'] === 'critical' ? 'border-red-200 bg-red-50' : ($issue['type'] === 'warning' ? 'border-yellow-200 bg-yellow-50' : 'border-blue-200 bg-blue-50') }}">
                        <div class="flex-shrink-0 mt-1">
                            @if($issue['type'] === 'critical')
                            <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            @elseif($issue['type'] === 'warning')
                            <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            @else
                            <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="font-medium {{ $issue['type'] === 'critical' ? 'text-red-800' : ($issue['type'] === 'warning' ? 'text-yellow-800' : 'text-blue-800') }}">
                                {{ $issue['title'] }}
                            </h4>
                            <p class="text-sm {{ $issue['type'] === 'critical' ? 'text-red-700' : ($issue['type'] === 'warning' ? 'text-yellow-700' : 'text-blue-700') }}">
                                {{ $issue['description'] }}
                            </p>
                        </div>
                        @if(isset($issue['url']))
                        <div class="ml-3">
                            <a href="{{ $issue['url'] }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md {{ $issue['type'] === 'critical' ? 'text-red-700 bg-red-100 hover:bg-red-200' : ($issue['type'] === 'warning' ? 'text-yellow-700 bg-yellow-100 hover:bg-yellow-200' : 'text-blue-700 bg-blue-100 hover:bg-blue-200') }}">
                                {{ $issue['action'] }}
                            </a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- Two-Factor Authentication --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-4xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Two-Factor Authentication') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Enhance your account security with two-factor authentication.') }}
                            </p>
                        </header>

                        <div class="mt-6 space-y-6">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ __('Two-Factor Authentication Status') }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        @if($twoFactorEnabled)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ __('Enabled') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ __('Disabled') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                
                                @if($twoFactorEnabled)
                                    <div class="text-sm text-gray-500">
                                        {{ __('Recovery codes remaining') }}: 
                                        <span class="font-medium {{ $remainingRecoveryCodes <= 2 ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $remainingRecoveryCodes }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- QR Code Setup --}}
                            @if(isset($qrCodeSvg) && $qrCodeSvg)
                            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                                <h3 class="font-medium text-blue-900 mb-4">{{ __('Scan QR Code') }}</h3>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="text-center">
                                        <div class="inline-block p-4 bg-white rounded-lg shadow">
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    </div>
                                    <div class="text-sm text-blue-800">
                                        <p class="mb-3"><strong>{{ __('Setup Instructions:') }}</strong></p>
                                        <ol class="space-y-2 list-decimal list-inside">
                                            <li>{{ __('Download a 2FA app like Google Authenticator or Authy') }}</li>
                                            <li>{{ __('Open the app and scan this QR code') }}</li>
                                            <li>{{ __('Enter the 6-digit code from your app to verify setup') }}</li>
                                        </ol>
                                        @if(isset($setupSecret))
                                        <div class="mt-4 p-3 bg-white rounded border">
                                            <p class="text-xs text-gray-600 mb-1">{{ __('Or enter this key manually:') }}</p>
                                            <code class="text-xs font-mono break-all">{{ $setupSecret }}</code>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="flex flex-wrap gap-3">
                                @if($twoFactorEnabled)
                                    <a href="{{ route('2fa.recovery-codes') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2h-6m6 0v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6zm-6 0V7a2 2 0 112-2 2 2 0 012 2v2H9z"></path>
                                        </svg>
                                        {{ __('View Recovery Codes') }}
                                    </a>
                                    
                                    <a href="{{ route('profile.security.download-backup-codes') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ __('Download Backup Codes') }}
                                    </a>
                                    
                                    <form method="POST" action="{{ route('2fa.disable') }}" class="inline" onsubmit="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            {{ __('Disable 2FA') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('2fa.setup') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        {{ __('Enable Two-Factor Authentication') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Login Statistics --}}
            @if(isset($loginStatistics))
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Login Statistics') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Overview of your account activity over the last :days days.', ['days' => $loginStatistics['period_days']]) }}
                    </p>
                </header>

                <div class="mt-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $loginStatistics['total_attempts'] }}</div>
                            <div class="text-sm text-blue-700">{{ __('Total Attempts') }}</div>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $loginStatistics['successful_logins'] }}</div>
                            <div class="text-sm text-green-700">{{ __('Successful') }}</div>
                        </div>
                        
                        @if($loginStatistics['failed_attempts'] > 0)
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-red-600">{{ $loginStatistics['failed_attempts'] }}</div>
                            <div class="text-sm text-red-700">{{ __('Failed') }}</div>
                        </div>
                        @else
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-gray-600">0</div>
                            <div class="text-sm text-gray-700">{{ __('Failed') }}</div>
                        </div>
                        @endif
                        
                        @if($loginStatistics['suspicious_attempts'] > 0)
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $loginStatistics['suspicious_attempts'] }}</div>
                            <div class="text-sm text-yellow-700">{{ __('Suspicious') }}</div>
                        </div>
                        @else
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-gray-600">0</div>
                            <div class="text-sm text-gray-700">{{ __('Suspicious') }}</div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-lg font-medium text-gray-900">{{ $loginStatistics['unique_locations'] }}</div>
                            <div class="text-sm text-gray-600">{{ __('Unique Locations') }}</div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-lg font-medium text-gray-900">{{ $loginStatistics['unique_devices'] }}</div>
                            <div class="text-sm text-gray-600">{{ __('Unique Devices') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Login History --}}
            @if(isset($recentLoginHistory) && $recentLoginHistory->count() > 0)
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Recent Login History') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Your recent login attempts and their details.') }}
                    </p>
                </header>

                <div class="mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Date & Time') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Location') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Device') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('IP Address') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentLoginHistory as $login)
                                <tr class="{{ $login->is_suspicious ? 'bg-yellow-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $login->attempted_at->format('M j, Y H:i') }}
                                        <div class="text-xs text-gray-500">
                                            {{ $login->attempted_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($login->success)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ __('Success') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ __('Failed') }}
                                            </span>
                                            @if($login->failure_reason)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ ucwords(str_replace('_', ' ', $login->failure_reason)) }}
                                            </div>
                                            @endif
                                        @endif
                                        
                                        @if($login->is_suspicious)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ __('Suspicious') }}
                                            </span>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $login->location_string }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($login->device_type === 'mobile')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z"></path>
                                                @elseif($login->device_type === 'tablet')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                @endif
                                            </svg>
                                            {{ $login->device_info }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                        {{ $login->ip_address }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Active Sessions --}}
            @if(isset($activeSessions) && $activeSessions->count() > 0)
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Active Sessions') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Manage your active sessions across different devices and browsers.') }}
                        </p>
                    </div>
                    @if($activeSessions->count() > 1)
                    <form method="POST" action="{{ route('profile.security.revoke-all-sessions') }}" class="inline" onsubmit="return confirm('Are you sure you want to log out all other sessions? You will remain logged in on this device.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Log Out All Other Sessions') }}
                        </button>
                    </form>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach($activeSessions as $session)
                    <div class="flex items-center justify-between p-4 border rounded-lg {{ $session->is_current ? 'border-green-200 bg-green-50' : 'border-gray-200' }}">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 {{ $session->is_current ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($session->device_type === 'mobile')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z"></path>
                                    @elseif($session->device_type === 'tablet')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    @endif
                                </svg>
                            </div>
                            
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-sm font-medium text-gray-900">
                                        {{ $session->device_info }}
                                    </h3>
                                    @if($session->is_current)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Current Session') }}
                                    </span>
                                    @endif
                                    @if($session->is_trusted)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ __('Trusted') }}
                                    </span>
                                    @endif
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ $session->location_string }}
                                </div>
                                <div class="mt-1 text-xs text-gray-400">
                                    {{ __('Last active') }}: {{ $session->time_since_last_activity }}
                                </div>
                                <div class="text-xs text-gray-400 font-mono">
                                    {{ $session->ip_address }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!$session->is_current)
                        <div class="flex space-x-2">
                            <form method="POST" action="{{ route('profile.security.revoke-session', $session->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to log out this session?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Trusted Devices --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Trusted Devices') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Devices that you trust and don\'t want to be prompted for additional verification.') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('profile.security.trust-device') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            {{ __('Trust This Device') }}
                        </button>
                    </form>
                </div>

                @if(empty($trustedDevices))
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No trusted devices') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('You haven\'t marked any devices as trusted yet.') }}</p>
                </div>
                @else
                <div class="space-y-4">
                    @foreach($trustedDevices as $index => $device)
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($device['device_type'] === 'mobile')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z"></path>
                                    @elseif($device['device_type'] === 'tablet')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    @endif
                                </svg>
                            </div>
                            
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    {{ $device['name'] ?? $device['browser'] . ' on ' . $device['os'] }}
                                </h3>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ ucfirst($device['device_type']) }} - {{ $device['browser'] }} on {{ $device['os'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-400">
                                    {{ __('Trusted on') }}: {{ \Carbon\Carbon::parse($device['trusted_at'])->format('M j, Y H:i') }}
                                </div>
                                <div class="text-xs text-gray-400 font-mono">
                                    {{ $device['ip_address'] }}
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <form method="POST" action="{{ route('profile.security.remove-trusted-device', $index) }}" class="inline" onsubmit="return confirm('Are you sure you want to remove this trusted device?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
