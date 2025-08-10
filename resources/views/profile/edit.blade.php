<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- JavaScript disabled fallback --}}
            <x-no-js-fallback feature="advanced profile features">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-medium text-blue-900">Basic Profile Management</h4>
                    <p class="text-sm text-blue-700 mt-1">You can still update your profile information using the forms below. Some advanced features like real-time validation and drag-and-drop uploads require JavaScript.</p>
                </div>
            </x-no-js-fallback>

            {{-- Profile Picture Section --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="profile-picture-section">
                <div class="max-w-md mx-auto">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>
                    @include('components.profile-picture-upload')
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="personal-info-section">
                <div class="max-w-md mx-auto">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Password & Security --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="security-section">
                <div class="max-w-md mx-auto">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Password & Security</h3>
                    @include('profile.partials.update-password-form')
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('profile.security') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Advanced Security Settings
                        </a>
                    </div>
                </div>
            </div>

            {{-- Enhanced User Info --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="user-info-section">
                <div class="max-w-md mx-auto">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    @include('profile.partials.enhanced-user-info')
                </div>
            </div>

            {{-- Account Deletion --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-red-200" id="danger-zone-section">
                <div class="max-w-md mx-auto">
                    <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    {{-- Include enhanced JavaScript --}}
    @push('scripts')
    <script type="module">
        import { FormValidator } from '{{ asset('js/components/form-validation.js') }}';
        import { LoadingManager } from '{{ asset('js/components/loading-manager.js') }}';
        
        // Initialize form validation for all forms
        document.querySelectorAll('form[data-validate-form]').forEach(form => {
            new FormValidator(form, {
                errorContainer: form.querySelector('.form-errors'),
                customMessages: {
                    'name.required': 'Please enter your full name',
                    'email.required': 'Please enter your email address',
                    'email.email': 'Please enter a valid email address',
                    'current_password.required': 'Please enter your current password',
                    'password.required': 'Please enter a new password',
                    'password.password-strength': 'Password must be at least 8 characters with uppercase, lowercase, number, and special character',
                    'password_confirmation.confirm': 'Password confirmation does not match'
                }
            });
        });
        
        // Enhanced AJAX form submissions
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            if (!form.dataset.ajaxSubmit) return;
            
            e.preventDefault();
            
            try {
                const formData = new FormData(form);
                const response = await LoadingManager.wrapAjax(form, 
                    fetch(form.action, {
                        method: form.method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                );
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success mb-4';
                    alert.textContent = data.message || 'Changes saved successfully!';
                    form.insertBefore(alert, form.firstChild);
                    
                    // Auto-hide after 5 seconds
                    setTimeout(() => alert.remove(), 5000);
                }
                
            } catch (error) {
                console.error('Form submission error:', error);
            }
        });
        
        // Real-time profile picture feedback
        document.addEventListener('profilePictureUpdated', (e) => {
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success mt-3';
            successMessage.textContent = 'Profile picture updated successfully!';
            
            const section = document.getElementById('profile-picture-section');
            if (section) {
                section.appendChild(successMessage);
                setTimeout(() => successMessage.remove(), 5000);
            }
        });
        
        // Form change detection
        let formChanged = false;
        document.addEventListener('input', () => {
            formChanged = true;
        });
        
        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
        
        // Reset form changed flag on successful submission
        document.addEventListener('formSubmissionSuccess', () => {
            formChanged = false;
        });
    </script>
    @endpush

    {{-- Include enhanced styles --}}
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?t={{ time() }}">
    <style>
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .form-errors {
            margin-bottom: 1rem;
        }
        
        .field-error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .is-invalid {
            border-color: #dc2626;
            box-shadow: 0 0 0 1px #dc2626;
        }
        
        .is-valid {
            border-color: #059669;
            box-shadow: 0 0 0 1px #059669;
        }
        
        @media (max-width: 640px) {
            .max-w-4xl {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .p-4.sm\:p-8 {
                padding: 1rem;
            }
        }
    </style>
    @endpush
</x-app-layout>
