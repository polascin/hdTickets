<x-unified-layout title="Advanced Security" subtitle="Manage your account security and device trust">
    <div class="space-y-6">
        
        <!-- Security Overview -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Security Overview</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Security Score -->
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $securityData['security_score'] }}/100</div>
                        <div class="text-sm text-gray-500">Security Score</div>
                    </div>
                    
                    <!-- 2FA Status -->
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $securityData['two_factor_enabled'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $securityData['two_factor_enabled'] ? 'ON' : 'OFF' }}
                        </div>
                        <div class="text-sm text-gray-500">Two-Factor Auth</div>
                    </div>
                    
                    <!-- Email Status -->
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $securityData['email_verified'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $securityData['email_verified'] ? 'VERIFIED' : 'PENDING' }}
                        </div>
                        <div class="text-sm text-gray-500">Email Status</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Active Sessions</h3>
                    <button onclick="revokeAllSessions()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Revoke All Other Sessions
                    </button>
                </div>
                
                @if($sessions->count() > 0)
                    <div class="space-y-3">
                        @foreach($sessions as $session)
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium">{{ $session->user_agent ?? 'Unknown Device' }}</div>
                                <div class="text-sm text-gray-500">
                                    IP: {{ $session->ip_address ?? 'Unknown' }} • 
                                    Last active: {{ $session->last_activity ? $session->last_activity->diffForHumans() : 'Unknown' }}
                                </div>
                            </div>
                            @if($session->id !== session()->getId())
                            <button onclick="revokeSession('{{ $session->id }}')"
                                    class="ml-4 text-red-600 hover:text-red-900 text-sm">
                                Revoke
                            </button>
                            @else
                            <span class="ml-4 text-green-600 text-sm font-medium">Current Session</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No active sessions found.</p>
                @endif
            </div>
        </div>

        <!-- Trusted Devices -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Trusted Devices</h3>
                    <button onclick="trustCurrentDevice()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Trust Current Device
                    </button>
                </div>
                
                @if(count($trustedDevices) > 0)
                    <div class="space-y-3">
                        @foreach($trustedDevices as $index => $device)
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium">{{ $device['name'] ?? 'Unnamed Device' }}</div>
                                <div class="text-sm text-gray-500">
                                    Added: {{ \Carbon\Carbon::parse($device['added_at'])->format('M j, Y') }} • 
                                    Last used: {{ \Carbon\Carbon::parse($device['last_used'] ?? $device['added_at'])->diffForHumans() }}
                                </div>
                            </div>
                            <button onclick="removeTrustedDevice({{ $index }})"
                                    class="ml-4 text-red-600 hover:text-red-900 text-sm">
                                Remove
                            </button>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No trusted devices configured.</p>
                @endif
            </div>
        </div>

        <!-- Recent Login Activity -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Login Activity</h3>
                
                @if($securityData['recent_logins']->count() > 0)
                    <div class="space-y-3">
                        @foreach($securityData['recent_logins'] as $login)
                        <div class="flex items-center justify-between p-3 border rounded">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    @if($login->status === 'success')
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                    <span class="font-medium capitalize">{{ $login->status }}</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $login->ip_address ?? 'Unknown IP' }} • {{ $login->user_agent ?? 'Unknown Device' }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $login->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No recent login activity found.</p>
                @endif
            </div>
        </div>

        <!-- Security Actions -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Security Actions</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('profile.security') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Security Settings
                    </a>
                    
                    @if($securityData['two_factor_enabled'])
                    <a href="{{ route('profile.security.download-backup-codes') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Backup Codes
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for AJAX functions -->
    <script>
        function revokeAllSessions() {
            if (confirm('Are you sure you want to revoke all other sessions? This will log you out of all other devices.')) {
                fetch('{{ route("profile.security.revoke-all-sessions") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('All other sessions have been revoked.');
                        window.location.reload();
                    } else {
                        alert('Failed to revoke sessions: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while revoking sessions.');
                });
            }
        }

        function revokeSession(sessionId) {
            if (confirm('Are you sure you want to revoke this session?')) {
                fetch(`{{ route("profile.security.revoke-session", ["sessionId" => ":sessionId"]) }}`.replace(':sessionId', sessionId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session revoked successfully.');
                        window.location.reload();
                    } else {
                        alert('Failed to revoke session: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while revoking the session.');
                });
            }
        }

        function trustCurrentDevice() {
            const deviceName = prompt('Enter a name for this device:', navigator.platform || 'Unknown Device');
            if (deviceName) {
                fetch('{{ route("profile.security.trust-device") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        device_fingerprint: generateDeviceFingerprint(),
                        device_name: deviceName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Device trusted successfully.');
                        window.location.reload();
                    } else {
                        alert('Failed to trust device: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while trusting the device.');
                });
            }
        }

        function removeTrustedDevice(deviceIndex) {
            if (confirm('Are you sure you want to remove this trusted device?')) {
                fetch(`{{ route("profile.security.remove-trusted-device", ["deviceIndex" => ":deviceIndex"]) }}`.replace(':deviceIndex', deviceIndex), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trusted device removed successfully.');
                        window.location.reload();
                    } else {
                        alert('Failed to remove trusted device: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the trusted device.');
                });
            }
        }

        function generateDeviceFingerprint() {
            // Simple device fingerprinting
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('Device fingerprint', 2, 2);
            
            const fingerprint = [
                navigator.userAgent,
                navigator.language,
                screen.width + 'x' + screen.height,
                new Date().getTimezoneOffset(),
                canvas.toDataURL()
            ].join('|');
            
            return btoa(fingerprint).substring(0, 32);
        }
    </script>
</x-unified-layout>
