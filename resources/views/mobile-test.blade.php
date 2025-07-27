@extends('layouts.app')

@section('title', 'Mobile Responsiveness Test')

@section('content')
<div class="space-y-8">
    <!-- Header Test Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Header & Navigation Test</h2>
        <p class="text-gray-600 mb-4">Testing navigation visibility and mobile menu functionality.</p>
        <div class="bg-gray-50 p-4 rounded">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> On mobile, the navigation should collapse into a hamburger menu.
                On desktop, all navigation items should be visible.
            </p>
        </div>
    </div>

    <!-- Card Grid Test -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Card Grid Responsiveness</h2>
        <p class="text-gray-600 mb-4">Testing how cards adapt to different screen sizes.</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="dashboard-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Active Monitors</p>
                        <p class="stat-value text-lg">12</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="dashboard-card bg-green-500 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/90 text-sm">Alerts Today</p>
                        <p class="text-2xl font-bold">8</p>
                    </div>
                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="dashboard-card bg-yellow-500 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/90 text-sm">Price Drops</p>
                        <p class="text-2xl font-bold">3</p>
                    </div>
                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            
            <div class="dashboard-card bg-purple-500 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/90 text-sm">Available Now</p>
                        <p class="text-2xl font-bold">24</p>
                    </div>
                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> 
                Mobile: 1 column | Tablet: 2 columns | Desktop: 4 columns
            </p>
        </div>
    </div>

    <!-- Form Elements Test -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Form Elements Responsiveness</h2>
        <p class="text-gray-600 mb-4">Testing form inputs, buttons, and layout on mobile devices.</p>
        
        <form class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                    <input type="text" class="form-input" placeholder="Enter event name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
                    <input type="date" class="form-input">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select class="form-select">
                    <option>Sports</option>
                    <option>Music</option>
                    <option>Theater</option>
                    <option>Comedy</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea class="form-input" rows="3" placeholder="Enter event description"></textarea>
            </div>
            
            <!-- Button Group -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="btn-primary w-full sm:w-auto">Save Event</button>
                <button type="button" class="btn-secondary w-full sm:w-auto">Cancel</button>
                <button type="button" class="btn-danger w-full sm:w-auto">Delete</button>
            </div>
        </form>
        
        <div class="bg-gray-50 p-4 rounded mt-4">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> 
                Form inputs should be touch-friendly (min 44px height), buttons should stack on mobile.
            </p>
        </div>
    </div>

    <!-- Table Responsiveness Test -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Table Responsiveness</h2>
        <p class="text-gray-600 mb-4">Testing how tables behave on mobile devices.</p>
        
        <x-enhanced-table
            :headers="[
                ['label' => 'Event', 'key' => 'event'],
                ['label' => 'Date', 'key' => 'date'],
                ['label' => 'Venue', 'key' => 'venue'],
                ['label' => 'Price', 'key' => 'price'],
                ['label' => 'Status', 'key' => 'status'],
                ['label' => 'Actions', 'key' => 'actions']
            ]"
            :data="[
                [
                    'event' => 'Kansas City Chiefs vs Patriots',
                    'date' => '2024-01-15',
                    'venue' => 'Arrowhead Stadium',
                    'price' => '$89.00',
                    'status' => '<span class=\"badge badge-success\">Available</span>',
                    'actions' => '<button class=\"btn-primary btn-sm\">Buy Now</button>'
                ],
                [
                    'event' => 'Lakers vs Warriors',
                    'date' => '2024-01-20',
                    'venue' => 'Crypto.com Arena',
                    'price' => '$125.00',
                    'status' => '<span class=\"badge badge-warning\">Limited</span>',
                    'actions' => '<button class=\"btn-primary btn-sm\">Buy Now</button>'
                ],
                [
                    'event' => 'Taylor Swift Concert',
                    'date' => '2024-01-25',
                    'venue' => 'SoFi Stadium',
                    'price' => '$299.00',
                    'status' => '<span class=\"badge badge-danger\">Sold Out</span>',
                    'actions' => '<button class=\"btn-secondary btn-sm\" disabled>Notify Me</button>'
                ]
            ]"
            :sortable="true"
            :filterable="true"
        />
        
        <div class="bg-gray-50 p-4 rounded mt-4">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> 
                Table should scroll horizontally on mobile or transform into card view.
            </p>
        </div>
    </div>

    <!-- Modal Test -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Modal Responsiveness Test</h2>
        <p class="text-gray-600 mb-4">Testing modal behavior on different screen sizes.</p>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <button x-data="" @click="$dispatch('open-modal', 'standard-modal')" class="btn-primary">
                Standard Modal
            </button>
            <button x-data="" @click="$dispatch('open-modal', 'large-modal')" class="btn-secondary">
                Large Modal
            </button>
            <button x-data="" @click="$dispatch('open-modal', 'fullscreen-modal')" class="btn-danger">
                Mobile Fullscreen
            </button>
        </div>
        
        <!-- Standard Modal -->
        <x-modal name="standard-modal" maxWidth="md">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Standard Modal</h3>
                <p class="text-gray-600 mb-4">This is a standard modal that should adapt well to mobile screens.</p>
                <form class="space-y-4">
                    <input type="text" class="form-input" placeholder="Enter some text">
                    <select class="form-select">
                        <option>Option 1</option>
                        <option>Option 2</option>
                    </select>
                </form>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="$dispatch('close-modal', 'standard-modal')" class="btn-secondary">Cancel</button>
                    <button class="btn-primary">Save</button>
                </div>
            </div>
        </x-modal>
        
        <!-- Large Modal -->
        <x-modal name="large-modal" maxWidth="2xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Large Modal</h3>
                <p class="text-gray-600 mb-4">This larger modal should still be usable on mobile devices.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <input type="text" class="form-input" placeholder="Field 1">
                    <input type="text" class="form-input" placeholder="Field 2">
                    <input type="text" class="form-input" placeholder="Field 3">
                    <input type="text" class="form-input" placeholder="Field 4">
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="$dispatch('close-modal', 'large-modal')" class="btn-secondary">Cancel</button>
                    <button class="btn-primary">Save</button>
                </div>
            </div>
        </x-modal>
        
        <!-- Fullscreen Mobile Modal -->
        <x-modal name="fullscreen-modal" maxWidth="full" :fullscreenOnMobile="true">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Mobile Fullscreen Modal</h3>
                    <button @click="$dispatch('close-modal', 'fullscreen-modal')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">This modal takes up the full screen on mobile devices for better usability.</p>
                <div class="space-y-4">
                    <input type="text" class="form-input" placeholder="Full-width input">
                    <textarea class="form-input" rows="5" placeholder="Large textarea"></textarea>
                    <select class="form-select">
                        <option>Choose an option</option>
                        <option>Option A</option>
                        <option>Option B</option>
                        <option>Option C</option>
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 mt-6">
                    <button class="btn-primary w-full sm:w-auto">Save Changes</button>
                    <button @click="$dispatch('close-modal', 'fullscreen-modal')" class="btn-secondary w-full sm:w-auto">Cancel</button>
                </div>
            </div>
        </x-modal>
        
        <div class="bg-gray-50 p-4 rounded mt-4">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> 
                Modals should be appropriately sized for mobile, with touch-friendly controls.
            </p>
        </div>
    </div>

    <!-- Touch Target Test -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Touch Target Test</h2>
        <p class="text-gray-600 mb-4">Testing minimum touch target sizes (44px minimum).</p>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button class="touch-target bg-blue-500 text-white rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </button>
            <button class="touch-target bg-green-500 text-white rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                </svg>
            </button>
            <button class="touch-target bg-yellow-500 text-white rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </button>
            <button class="touch-target bg-red-500 text-white rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
        
        <div class="bg-gray-50 p-4 rounded mt-4">
            <p class="text-sm">
                <span class="font-medium">Expected:</span> 
                All buttons should be at least 44px x 44px for easy touch interaction.
            </p>
        </div>
    </div>

    <!-- Performance Indicators -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Performance Indicators</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-blue-600" id="viewport-width">-</div>
                <div class="text-sm text-gray-600">Current Width</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-green-600" id="device-type">-</div>
                <div class="text-sm text-gray-600">Device Type</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-2xl font-bold text-purple-600" id="touch-support">-</div>
                <div class="text-sm text-gray-600">Touch Support</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updatePerformanceIndicators() {
        const width = window.innerWidth;
        const isMobile = width < 768;
        const isTablet = width >= 768 && width < 1024;
        const isDesktop = width >= 1024;
        const hasTouch = 'ontouchstart' in window;
        
        document.getElementById('viewport-width').textContent = width + 'px';
        document.getElementById('device-type').textContent = 
            isMobile ? 'Mobile' : isTablet ? 'Tablet' : 'Desktop';
        document.getElementById('touch-support').textContent = hasTouch ? 'Yes' : 'No';
    }
    
    updatePerformanceIndicators();
    window.addEventListener('resize', updatePerformanceIndicators);
    
    // Test horizontal scrolling
    function checkHorizontalScroll() {
        const hasHorizontalScroll = document.body.scrollWidth > window.innerWidth;
        if (hasHorizontalScroll) {
            console.warn('⚠️ Horizontal scroll detected!', {
                bodyWidth: document.body.scrollWidth,
                windowWidth: window.innerWidth
            });
        } else {
            console.log('✅ No horizontal scroll detected');
        }
    }
    
    checkHorizontalScroll();
    window.addEventListener('resize', checkHorizontalScroll);
    
    console.log('📱 Mobile responsiveness test page loaded');
});
</script>
@endpush
@endsection
