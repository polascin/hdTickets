@extends('layouts.modern')

@section('title', 'UX/UI Demo')
@section('description', 'Demonstration of enhanced UX/UI features')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">UX/UI Improvements Demo</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Modern card-based layout with dark mode and customizable features</p>
        </div>
        <div class="flex items-center space-x-4">
            <button data-theme-toggle class="btn-secondary">
                <i class="fas fa-moon mr-2"></i>
                <span class="theme-text">Dark Mode</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Modern Cards Demo -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Modern Card Components</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Basic Card -->
            <x-modern-card 
                title="Sports Tickets" 
                subtitle="Monitor and track ticket availability" 
                icon="fas fa-ticket-alt">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Available</span>
                        <span class="text-2xl font-bold text-green-600">1,247</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Monitored</span>
                        <span class="text-2xl font-bold text-blue-600">3,582</span>
                    </div>
                </div>
                
                <x-slot name="footer">
                    <button class="btn-primary w-full">View Details</button>
                </x-slot>
            </x-modern-card>

            <!-- Loading Card -->
            <x-modern-card 
                title="Loading Example" 
                subtitle="Shows loading state" 
                icon="fas fa-spinner"
                :loading="true">
            </x-modern-card>

            <!-- Gradient Card -->
            <x-modern-card 
                title="User Statistics" 
                subtitle="Active user metrics" 
                icon="fas fa-users"
                :gradient="true">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-xl font-bold text-purple-600">892</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Active</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-orange-600">124</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">New Today</div>
                    </div>
                </div>
            </x-modern-card>
        </div>
    </section>

    <!-- Enhanced Table Demo -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Enhanced Table Component</h2>
        
        @php
            $headers = [
                ['key' => 'id', 'label' => 'ID', 'sortable' => true, 'filterable' => true],
                ['key' => 'event', 'label' => 'Event', 'sortable' => true, 'filterable' => true],
                ['key' => 'venue', 'label' => 'Venue', 'sortable' => true, 'filterable' => true],
                ['key' => 'price', 'label' => 'Price', 'sortable' => true, 'filterable' => true, 'render' => function($value) {
                    return '<span class="font-bold text-green-600">$' . number_format($value, 2) . '</span>';
                }],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'filterable' => true, 'render' => function($value) {
                    $class = $value === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    return '<span class="badge ' . $class . '">' . $value . '</span>';
                }],
                ['key' => 'date', 'label' => 'Date', 'sortable' => true, 'filterable' => true]
            ];
            
            $data = [
                ['id' => 1, 'event' => 'Lakers vs Warriors', 'venue' => 'Staples Center', 'price' => 150.00, 'status' => 'Available', 'date' => '2024-12-25'],
                ['id' => 2, 'event' => 'Celtics vs Heat', 'venue' => 'TD Garden', 'price' => 89.50, 'status' => 'Available', 'date' => '2024-12-26'],
                ['id' => 3, 'event' => 'Super Bowl LVIII', 'venue' => 'Allegiant Stadium', 'price' => 2500.00, 'status' => 'Sold Out', 'date' => '2024-02-11'],
                ['id' => 4, 'event' => 'World Series Game 7', 'venue' => 'Yankee Stadium', 'price' => 750.00, 'status' => 'Available', 'date' => '2024-10-30'],
                ['id' => 5, 'event' => 'Champions League Final', 'venue' => 'Wembley Stadium', 'price' => 1200.00, 'status' => 'Available', 'date' => '2024-05-25']
            ];
        @endphp
        
        <x-enhanced-table 
            :headers="$headers"
            :data="$data"
            id="demo-table"
            :sortable="true"
            :filterable="true"
            :resizable="true"
            :exportable="true"
            :customizable="true"
            searchPlaceholder="Search events..."
            emptyMessage="No events found matching your criteria">
        </x-enhanced-table>
    </section>

    <!-- UI Feedback Demo -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">UI Feedback Components</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="showSuccessToast()" class="btn-primary">
                <i class="fas fa-check mr-2"></i>Success Toast
            </button>
            <button onclick="showErrorToast()" class="btn-danger">
                <i class="fas fa-times mr-2"></i>Error Toast
            </button>
            <button onclick="showWarningToast()" class="btn-secondary">
                <i class="fas fa-exclamation-triangle mr-2"></i>Warning Toast
            </button>
            <button onclick="showInfoToast()" class="btn-secondary">
                <i class="fas fa-info mr-2"></i>Info Toast
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button onclick="showLoadingDemo()" class="btn-secondary">
                <i class="fas fa-spinner mr-2"></i>Loading Demo
            </button>
            <button onclick="showFormValidationDemo()" class="btn-secondary">
                <i class="fas fa-check-circle mr-2"></i>Form Validation Demo
            </button>
        </div>
    </section>

    <!-- User Preferences Demo -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">User Preferences</h2>
        
        <x-modern-card title="Preference Settings" icon="fas fa-cog">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Notifications</label>
                    <input type="checkbox" id="notifications-toggle" class="toggle-switch" checked>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Compact Table Mode</label>
                    <input type="checkbox" id="compact-mode-toggle" class="toggle-switch">
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">High Contrast Mode</label>
                    <input type="checkbox" id="high-contrast-toggle" class="toggle-switch">
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reduce Motion</label>
                    <input type="checkbox" id="reduce-motion-toggle" class="toggle-switch">
                </div>
            </div>
            
            <x-slot name="footer">
                <div class="flex space-x-2">
                    <button onclick="exportPreferences()" class="btn-secondary flex-1">
                        <i class="fas fa-download mr-2"></i>Export Settings
                    </button>
                    <button onclick="resetPreferences()" class="btn-danger flex-1">
                        <i class="fas fa-undo mr-2"></i>Reset to Default
                    </button>
                </div>
            </x-slot>
        </x-modern-card>
    </section>

    <!-- Progress and Loading States -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Progress Indicators</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-modern-card title="Progress Bar Demo" icon="fas fa-tasks">
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Scraping Progress</span>
                            <span class="progress-percentage">65%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2" data-progress-bar="demo-progress">
                            <div class="progress-bar bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 65%"></div>
                        </div>
                    </div>
                    <button onclick="updateProgress()" class="btn-primary w-full">Update Progress</button>
                </div>
            </x-modern-card>
            
            <x-modern-card title="Skeleton Loading" icon="fas fa-eye">
                <div id="skeleton-demo" class="space-y-2">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded loading-shimmer"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6 loading-shimmer"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 loading-shimmer"></div>
                </div>
                
                <x-slot name="footer">
                    <button onclick="toggleSkeleton()" class="btn-secondary w-full">Toggle Skeleton</button>
                </x-slot>
            </x-modern-card>
        </div>
    </section>
</div>

<!-- Form Validation Demo Modal -->
<div id="form-demo-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 max-w-md mx-4 w-full">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Form Validation Demo</h3>
        <form id="demo-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" id="demo-email" class="form-input w-full" placeholder="Enter your email">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" id="demo-password" class="form-input w-full" placeholder="Enter password">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="btn-primary flex-1">Validate</button>
                <button type="button" onclick="closeDemoModal()" class="btn-secondary flex-1">Close</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Demo functions for UI feedback
function showSuccessToast() {
    if (window.hdTicketsFeedback) {
        window.hdTicketsFeedback.success('Operation completed successfully!', {
            actions: [{
                id: 'view',
                label: 'View Details',
                callback: () => alert('View details clicked!')
            }]
        });
    }
}

function showErrorToast() {
    if (window.hdTicketsFeedback) {
        window.hdTicketsFeedback.error('Something went wrong. Please try again.');
    }
}

function showWarningToast() {
    if (window.hdTicketsFeedback) {
        window.hdTicketsFeedback.warning('This action cannot be undone.');
    }
}

function showInfoToast() {
    if (window.hdTicketsFeedback) {
        window.hdTicketsFeedback.info('New features are now available!');
    }
}

function showLoadingDemo() {
    if (window.hdTicketsFeedback) {
        const loadingId = window.hdTicketsFeedback.loading('Processing your request...', {
            showProgress: true
        });
        
        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            window.hdTicketsFeedback.updateProgress('loading', progress);
            
            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    window.hdTicketsFeedback.stopLoading();
                    window.hdTicketsFeedback.success('Process completed!');
                }, 500);
            }
        }, 200);
    }
}

function showFormValidationDemo() {
    document.getElementById('form-demo-modal').classList.remove('hidden');
}

function closeDemoModal() {
    document.getElementById('form-demo-modal').classList.add('hidden');
}

function updateProgress() {
    const currentProgress = Math.random() * 100;
    document.querySelector('.progress-bar').style.width = currentProgress + '%';
    document.querySelector('.progress-percentage').textContent = Math.round(currentProgress) + '%';
}

let isSkeletonShowing = true;
function toggleSkeleton() {
    const container = document.getElementById('skeleton-demo');
    
    if (isSkeletonShowing) {
        container.innerHTML = `
            <p class="text-gray-600 dark:text-gray-400">This is the actual content that was loaded.</p>
            <p class="text-sm text-gray-500 dark:text-gray-500">The skeleton loading has been replaced with real data.</p>
        `;
        isSkeletonShowing = false;
    } else {
        container.innerHTML = `
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded loading-shimmer"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6 loading-shimmer"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 loading-shimmer"></div>
        `;
        isSkeletonShowing = true;
    }
}

// Preferences demo functions
function exportPreferences() {
    if (window.hdTicketsPrefs) {
        const data = window.hdTicketsPrefs.export();
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'hdtickets-preferences.json';
        a.click();
        URL.revokeObjectURL(url);
        
        if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.success('Preferences exported successfully!');
        }
    }
}

function resetPreferences() {
    if (window.hdTicketsPrefs) {
        window.hdTicketsPrefs.reset();
        
        if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.info('Preferences reset to default values.');
        }
        
        // Reset toggle states
        document.getElementById('notifications-toggle').checked = true;
        document.getElementById('compact-mode-toggle').checked = false;
        document.getElementById('high-contrast-toggle').checked = false;
        document.getElementById('reduce-motion-toggle').checked = false;
    }
}

// Initialize demo
document.addEventListener('DOMContentLoaded', function() {
    // Form validation demo
    document.getElementById('demo-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('demo-email').value;
        const password = document.getElementById('demo-password').value;
        
        // Clear previous errors
        if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.clearFieldError('#demo-email');
            window.hdTicketsFeedback.clearFieldError('#demo-password');
        }
        
        let hasErrors = false;
        
        if (!email || !email.includes('@')) {
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.showFieldError('#demo-email', 'Please enter a valid email address');
            }
            hasErrors = true;
        }
        
        if (password.length < 6) {
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.showFieldError('#demo-password', 'Password must be at least 6 characters');
            }
            hasErrors = true;
        }
        
        if (!hasErrors) {
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.showFieldSuccess('#demo-email');
                window.hdTicketsFeedback.showFieldSuccess('#demo-password');
                window.hdTicketsFeedback.success('Form validation passed!');
            }
            setTimeout(() => {
                closeDemoModal();
            }, 1500);
        }
    });
    
    // Preference toggles
    document.getElementById('high-contrast-toggle').addEventListener('change', function(e) {
        if (window.hdTicketsPrefs) {
            window.hdTicketsPrefs.setHighContrast(e.target.checked);
        }
    });
    
    document.getElementById('reduce-motion-toggle').addEventListener('change', function(e) {
        if (window.hdTicketsPrefs) {
            window.hdTicketsPrefs.setReduceMotion(e.target.checked);
        }
    });
    
    document.getElementById('compact-mode-toggle').addEventListener('change', function(e) {
        if (window.hdTicketsPrefs) {
            window.hdTicketsPrefs.setTableCompactMode(e.target.checked);
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.toggle-switch {
    @apply sr-only;
}

.toggle-switch + label {
    @apply relative inline-block w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors duration-200;
}

.toggle-switch + label:before {
    content: '';
    @apply absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform duration-200 shadow-sm;
}

.toggle-switch:checked + label {
    @apply bg-blue-600;
}

.toggle-switch:checked + label:before {
    @apply transform translate-x-6;
}

.dark-mode .toggle-switch + label {
    @apply bg-slate-600;
}

.dark-mode .toggle-switch + label:before {
    @apply bg-slate-300;
}

.dark-mode .toggle-switch:checked + label {
    @apply bg-blue-500;
}

.loading-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.dark-mode .loading-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    background-size: 200% 100%;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>
@endpush
