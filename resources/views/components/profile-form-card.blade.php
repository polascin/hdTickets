@props([
    'title' => '',
    'subtitle' => '',
    'icon' => null,
    'method' => 'POST',
    'action' => '',
    'submitText' => 'Save Changes',
    'cancelUrl' => null,
    'showCancel' => true,
    'loading' => false,
    'success' => false,
    'errors' => null
])

<x-profile-card :title="$title" :subtitle="$subtitle" :icon="$icon" variant="default">
    @if($success)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">Changes saved successfully!</p>
            </div>
        </div>
    @endif

    @if($errors && $errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">There were some errors with your submission:</p>
                    <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="{{ $method }}" action="{{ $action }}" 
          class="profile-form {{ $loading ? 'opacity-50 pointer-events-none' : '' }}"
          x-data="{ submitting: false }"
          @submit="submitting = true">
        
        @csrf
        @if($method !== 'GET' && $method !== 'POST')
            @method($method)
        @endif

        <div class="space-y-6">
            {{ $slot }}
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200">
            <div class="flex items-center space-x-4">
                @if($showCancel && $cancelUrl)
                    <a href="{{ $cancelUrl }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancel
                    </a>
                @endif
            </div>

            <div class="flex items-center space-x-4">
                <!-- Loading Indicator -->
                <div x-show="submitting" class="flex items-center text-sm text-gray-500">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                </div>

                <button type="submit" 
                        :disabled="submitting"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg x-show="!submitting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $submitText }}
                </button>
            </div>
        </div>
    </form>

    <!-- Auto-hide success message -->
    @if($success)
        <script>
        setTimeout(function() {
            const successMessage = document.querySelector('.bg-green-50');
            if (successMessage) {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => successMessage.remove(), 500);
            }
        }, 5000);
        </script>
    @endif
</x-profile-card>

@push('styles')
<style>
.profile-form .form-group {
    margin-bottom: 1.5rem;
}

.profile-form label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.profile-form .required {
    color: #ef4444;
}

.profile-form input[type="text"],
.profile-form input[type="email"],
.profile-form input[type="password"],
.profile-form input[type="tel"],
.profile-form input[type="url"],
.profile-form input[type="date"],
.profile-form input[type="time"],
.profile-form select,
.profile-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.profile-form input:focus,
.profile-form select:focus,
.profile-form textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.profile-form .help-text {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.profile-form .error-text {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #ef4444;
}

.profile-form .form-group.has-error input,
.profile-form .form-group.has-error select,
.profile-form .form-group.has-error textarea {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .profile-form .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .profile-form .form-actions > div {
        width: 100%;
    }
    
    .profile-form button {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush
