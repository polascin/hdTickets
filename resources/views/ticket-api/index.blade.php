@extends('layouts.modern')

@section('title', 'Ticket API Integration')
@section('description', 'Integrate with external ticket platforms - Ticketmaster, StubHub, and more')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <div class="p-3 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                    Ticket API Integration
                </h1>
                <p class="text-gray-600 mt-1">Connect with external ticket platforms and import events</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <button onclick="testAllConnections()" class="dashboard-card hover:shadow-xl px-6 py-3 text-sm font-medium bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg transition-all duration-200 shadow-lg">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Test Connections
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6 space-y-6">
    
    <!-- Platform Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($availablePlatforms as $platform)
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
            <div class="p-6 relative">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-purple-600 rounded-bl-full opacity-10"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-lg font-bold text-gray-900 capitalize">{{ $platform }}</div>
                            <div class="text-sm text-gray-600">API Platform</div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs text-green-600 ml-2 font-medium">Connected</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Search Interface -->
    <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Search Events</h3>
            <p class="text-gray-600">Search for events across connected platforms and import them to your database</p>
        </div>

        <form id="searchForm" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Search Query -->
                <div>
                    <label for="query" class="block text-sm font-medium text-gray-700 mb-2">Search Query</label>
                    <input type="text" id="query" name="query" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="e.g., Manchester United, Concert, NBA">
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City (Optional)</label>
                    <input type="text" id="city" name="city"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="e.g., Manchester, London">
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From (Optional)</label>
                    <input type="date" id="date_from" name="date_from"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To (Optional)</label>
                    <input type="date" id="date_to" name="date_to"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Platforms -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Platforms</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($availablePlatforms as $platform)
                        <label class="flex items-center">
                            <input type="checkbox" name="platforms[]" value="{{ $platform }}" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700 capitalize">{{ $platform }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" data-action="search"
                        class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search Events
                </button>

                <button type="submit" data-action="import"
                        class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Search & Import
                </button>

                <label class="flex items-center">
                    <input type="checkbox" name="save_to_db" value="1"
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Save to Database</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden">
        <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100 text-center">
            <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500 hover:bg-blue-400 transition ease-in-out duration-150">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Searching...
            </div>
        </div>
    </div>

    <!-- Results Container -->
    <div id="resultsContainer" class="hidden">
        <div class="bg-white shadow-lg rounded-xl border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Search Results</h3>
            </div>
            <div id="resultsContent" class="p-6">
                <!-- Results will be populated here -->
            </div>
        </div>
    </div>

    <!-- Summary Container -->
    <div id="summaryContainer" class="hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Search Summary</h3>
            <div id="summaryContent" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Summary will be populated here -->
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultsContainer = document.getElementById('resultsContainer');
    const summaryContainer = document.getElementById('summaryContainer');

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(searchForm);
        const action = e.submitter.dataset.action;
        
        // Show loading indicator
        loadingIndicator.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        summaryContainer.classList.add('hidden');

        // Determine endpoint
        const endpoint = action === 'import' ? '{{ route("ticket-api.import") }}' : '{{ route("ticket-api.search") }}';
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loadingIndicator.classList.add('hidden');
            
            if (data.success) {
                displayResults(data.data);
                if (data.summary) {
                    displaySummary(data.summary);
                }
                if (action === 'import' && data.message) {
                    alert(data.message);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            loadingIndicator.classList.add('hidden');
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    });

    function displayResults(data) {
        const resultsContent = document.getElementById('resultsContent');
        let html = '';

        Object.keys(data).forEach(platform => {
            const events = data[platform];
            html += `<div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-3 capitalize">${platform} (${events.length} events)</h4>
                <div class="grid gap-4">`;
            
            events.forEach(event => {
                html += `<div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <h5 class="font-medium text-gray-900">${event.name || 'Unnamed Event'}</h5>
                            ${event.venue ? `<p class="text-sm text-gray-600">${event.venue}</p>` : ''}
                            ${event.date ? `<p class="text-sm text-gray-500">${event.date}</p>` : ''}
                        </div>
                        <div class="text-right">
                            ${event.price_min ? `<p class="text-sm font-medium text-green-600">From $${event.price_min}</p>` : ''}
                            ${event.price_max ? `<p class="text-xs text-gray-500">Up to $${event.price_max}</p>` : ''}
                        </div>
                    </div>
                </div>`;
            });
            
            html += '</div></div>';
        });

        resultsContent.innerHTML = html;
        resultsContainer.classList.remove('hidden');
    }

    function displaySummary(summary) {
        const summaryContent = document.getElementById('summaryContent');
        let html = `
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-900">${summary.total_events}</div>
                <div class="text-sm text-blue-700">Total Events</div>
            </div>
        `;

        if (summary.platforms) {
            Object.keys(summary.platforms).forEach(platform => {
                html += `
                    <div class="text-center">
                        <div class="text-xl font-semibold text-blue-800">${summary.platforms[platform]}</div>
                        <div class="text-sm text-blue-600 capitalize">${platform}</div>
                    </div>
                `;
            });
        }

        if (summary.price_range && (summary.price_range.min !== null || summary.price_range.max !== null)) {
            html += `
                <div class="text-center">
                    <div class="text-lg font-medium text-blue-800">
                        $${summary.price_range.min || 0} - $${summary.price_range.max || 0}
                    </div>
                    <div class="text-sm text-blue-600">Price Range</div>
                </div>
            `;
        }

        summaryContent.innerHTML = html;
        summaryContainer.classList.remove('hidden');
    }

    window.testAllConnections = function() {
        fetch('{{ route("ticket-api.test") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('All connections tested successfully!');
            } else {
                alert('Connection test failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while testing connections.');
        });
    };
});
</script>
@endsection
