@extends('layouts.app')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Ticket Alerts') }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">Manage your sports ticket price and availability alerts</p>
    </div>
    <div class="flex items-center space-x-4">
        <!-- Quick Stats -->
        @php
            $activeAlerts = $alerts->where('is_active', true)->count();
            $totalMatches = $alerts->sum('matches_found');
        @endphp
        <div class="text-sm text-gray-600">
            {{ $activeAlerts }} Active • {{ $totalMatches }} Total Matches
        </div>
        <!-- Create Alert Button -->
        <button id="createAlertBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5m0 0V2m0 0V0m0 2h3a3 3 0 013 3v1M12 2V0"></path>
            </svg>
            Create Alert
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="py-6 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Alert Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Active Alerts -->
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12l2 2 4-4m5-7a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Active Alerts</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $alerts->where('is_active', true)->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Total Alerts -->
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5m4 6V4a1 1 0 00-1-1H7a1 1 0 00-1 1v3M9 9h1m4 0h1m-6 2h6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Alerts</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $alerts->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Total Matches -->
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Matches</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $alerts->sum('matches_found') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Matches -->
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Recent (24h)</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $alerts->where('updated_at', '>=', now()->subDay())->sum('matches_found') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts List -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Your Alerts</h3>
                    <div class="flex items-center space-x-3">
                        <button id="checkAlertsBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Check Now
                        </button>
                    </div>
                </div>

                @if($alerts->count() > 0)
                    <div class="space-y-4">
                        @foreach($alerts as $alert)
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $alert->name }}</h4>
                                            @if($alert->is_active)
                                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                                    Active
                                                </span>
                                            @else
                                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Paused
                                                </span>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Keywords</div>
                                                <div class="text-sm text-gray-900">{{ $alert->keywords }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Platform</div>
                                                <div class="text-sm text-gray-900">
                                                    @if($alert->platform)
                                                        {{ config('platforms.display_order.' . $alert->platform . '.display_name', ucfirst($alert->platform)) }}
                                                    @else
                                                        All Platforms
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Max Price</div>
                                                <div class="text-sm text-gray-900">
                                                    {{ $alert->max_price ? $alert->currency . ' ' . number_format($alert->max_price, 2) : 'No limit' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Matches Found</div>
                                                <div class="text-sm font-bold text-blue-600">{{ $alert->matches_found ?? 0 }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Created {{ $alert->created_at->diffForHumans() }}
                                            @if($alert->last_checked_at)
                                                • Last checked {{ $alert->last_checked_at->diffForHumans() }}
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        <button onclick="editAlert({{ $alert->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            Edit
                                        </button>
                                        <button onclick="toggleAlert({{ $alert->id }}, {{ $alert->is_active ? 'false' : 'true' }})" 
                                                class="text-yellow-600 hover:text-yellow-800 font-medium text-sm">
                                            {{ $alert->is_active ? 'Pause' : 'Activate' }}
                                        </button>
                                        <button onclick="deleteAlert({{ $alert->id }})" class="text-red-600 hover:text-red-800 font-medium text-sm">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $alerts->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12l2 2 4-4m5-7a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No alerts yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create your first ticket alert to get notified when tickets matching your criteria become available.</p>
                        <div class="mt-6">
                            <button id="createFirstAlertBtn" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                                </svg>
                                Create Your First Alert
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Alert Modal -->
<div id="alertModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="alertForm" method="POST">
                @csrf
                <input type="hidden" id="alertMethod" name="_method" value="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Create Alert</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Alert Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>

                        <div>
                            <label for="keywords" class="block text-sm font-medium text-gray-700">Keywords</label>
                            <input type="text" name="keywords" id="keywords" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                   placeholder="e.g., Manchester United, Liverpool, Arsenal" required>
                        </div>

                        <div>
                            <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                            <x-platform-select 
                                name="platform" 
                                id="platform" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            />
                        </div>

                        <div>
                            <label for="max_price" class="block text-sm font-medium text-gray-700">Max Price</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="max_price" id="max_price" step="0.01" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                        </div>

                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <input id="email_notifications" name="email_notifications" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                <label for="email_notifications" class="ml-2 block text-sm text-gray-900">Email notifications</label>
                            </div>
                            <div class="flex items-center">
                                <input id="sms_notifications" name="sms_notifications" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="sms_notifications" class="ml-2 block text-sm text-gray-900">SMS notifications</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Alert
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(title = 'Create Alert', alert = null) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('alertModal').classList.remove('hidden');
    
    if (alert) {
        // Edit mode
        document.getElementById('alertForm').action = `{{ url('tickets/alerts') }}/${alert.id}`;
        document.getElementById('alertMethod').value = 'PATCH';
        document.getElementById('name').value = alert.name;
        document.getElementById('keywords').value = alert.keywords;
        document.getElementById('platform').value = alert.platform || '';
        document.getElementById('max_price').value = alert.max_price || '';
        document.getElementById('email_notifications').checked = alert.email_notifications;
        document.getElementById('sms_notifications').checked = alert.sms_notifications;
    } else {
        // Create mode
        document.getElementById('alertForm').action = '{{ route("tickets.alerts.create") }}';
        document.getElementById('alertMethod').value = 'POST';
        document.getElementById('alertForm').reset();
        document.getElementById('email_notifications').checked = true;
    }
}

function closeModal() {
    document.getElementById('alertModal').classList.add('hidden');
}

function editAlert(alertId) {
    // In a real app, you'd fetch the alert data via AJAX
    fetch(`/api/alerts/${alertId}`)
        .then(response => response.json())
        .then(alert => openModal('Edit Alert', alert))
        .catch(() => {
            // Fallback: open empty modal
            openModal('Edit Alert');
        });
}

function toggleAlert(alertId, isActive) {
    fetch(`{{ url('tickets/alerts') }}/${alertId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ is_active: isActive === 'true' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating alert');
        }
    });
}

function deleteAlert(alertId) {
    if (confirm('Are you sure you want to delete this alert?')) {
        fetch(`{{ url('tickets/alerts') }}/${alertId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting alert');
            }
        });
    }
}

// Event listeners
document.getElementById('createAlertBtn').addEventListener('click', () => openModal());
document.getElementById('createFirstAlertBtn')?.addEventListener('click', () => openModal());

document.getElementById('checkAlertsBtn').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<svg class="w-4 h-4 inline mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Checking...';
    
    fetch('{{ route("tickets.alerts.check") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Checked ${data.alerts_checked} alerts successfully!`);
            location.reload();
        } else {
            alert('Error checking alerts');
        }
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Check Now';
    });
});

// Close modal when clicking outside
document.getElementById('alertModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
