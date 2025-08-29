<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Profile Settings') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

      {{-- Success Messages --}}
      @if (session('status'))
        <div class="alert alert-success">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ session('status') }}
        </div>
      @endif

      @if (session('success'))
        <div class="alert alert-success">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ session('success') }}
        </div>
      @endif

      {{-- Error Messages --}}
      @if (session('error'))
        <div class="alert alert-danger">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ session('error') }}
        </div>
      @endif

      {{-- Validation Errors Summary --}}
      @if (isset($errors) && $errors->any())
        <div class="alert alert-warning">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z">
            </path>
          </svg>
          <div>
            <strong>Please correct the following errors:</strong>
            <ul class="mt-2 ml-6 list-disc">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      {{-- JavaScript disabled fallback --}}
      <x-no-js-fallback feature="advanced profile features">
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <h4 class="font-medium text-blue-900">Basic Profile Management</h4>
          <p class="text-sm text-blue-700 mt-1">You can still update your profile information using the forms
            below. Some advanced features like real-time validation and drag-and-drop uploads require
            JavaScript.</p>
        </div>
      </x-no-js-fallback>

      {{-- Profile Completion Indicator --}}
      <x-profile-completion-progress :user="$user" />

      {{-- Quick Navigation --}}
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
          <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
            </path>
          </svg>
          Quick Navigation
        </h3>
        <div class="flex flex-wrap gap-2">
          <a href="#profile-picture-section"
            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors">
            📷 Profile Picture
          </a>
          <a href="#personal-info-section"
            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors">
            👤 Personal Info
          </a>
          <a href="#security-section"
            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors">
            🔒 Password & Security
          </a>
          <a href="#user-info-section"
            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors">
            📊 Account Info
          </a>
          <a href="#danger-zone-section"
            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-red-100 hover:bg-red-200 text-red-700 rounded-full transition-colors">
            ⚠️ Danger Zone
          </a>
        </div>
      </div>

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
            <a href="{{ route('profile.security') }}"
              class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                </path>
              </svg>
              Security & Two-Factor Auth
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
      import {
        FormValidator
      } from '{{ asset('js/components/form-validation.js') }}';
      import {
        LoadingManager
      } from '{{ asset('js/components/loading-manager.js') }}';

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

          // Clear previous messages
          const successContainer = form.querySelector('#profile-success-message, .alert');
          if (successContainer && successContainer.id === 'profile-success-message') {
            successContainer.classList.add('hidden');
          }

          const response = await LoadingManager.wrapAjax(form,
            fetch(form.action, {
              method: form.method,
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': formData.get('_token')
              }
            })
          );

          if (response.ok) {
            const data = await response.json();

            // Show success message
            const successMsg = form.querySelector('#profile-success-message');
            const successText = form.querySelector('#profile-success-text');

            if (successMsg && successText) {
              successText.textContent = data.message || 'Changes saved successfully!';
              successMsg.classList.remove('hidden');

              // Auto-hide after 5 seconds
              setTimeout(() => successMsg.classList.add('hidden'), 5000);
            }

            // Trigger custom event for other components
            document.dispatchEvent(new CustomEvent('formSubmissionSuccess', {
              detail: {
                form,
                data
              }
            }));

            // Reset form changed flag
            formChanged = false;

          } else {
            // Handle HTTP errors
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to save changes');
          }

        } catch (error) {
          console.error('Form submission error:', error);

          // Show error message
          const errorAlert = document.createElement('div');
          errorAlert.className = 'alert alert-danger';
          errorAlert.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ${error.message || 'An error occurred while saving your changes.'}
                `;
          form.insertBefore(errorAlert, form.firstChild);

          // Auto-hide after 7 seconds
          setTimeout(() => errorAlert.remove(), 7000);
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

      // Smooth scrolling for navigation links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });

            // Add a subtle highlight animation
            target.style.transition = 'box-shadow 0.3s ease';
            target.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.3)';
            setTimeout(() => {
              target.style.boxShadow = '';
            }, 2000);
          }
        });
      });

      // Auto-save draft functionality (optional enhancement)
      let autoSaveTimer;
      document.addEventListener('input', (e) => {
        if (e.target.form && e.target.form.dataset.validateForm) {
          clearTimeout(autoSaveTimer);
          autoSaveTimer = setTimeout(() => {
            // Save form data to localStorage as draft
            const formData = new FormData(e.target.form);
            const draftData = Object.fromEntries(formData.entries());
            localStorage.setItem('profile_draft_' + e.target.form.id, JSON.stringify(draftData));
          }, 2000);
        }
      });

      // Load draft data on page load
      window.addEventListener('load', () => {
        document.querySelectorAll('form[data-validate-form]').forEach(form => {
          const draftData = localStorage.getItem('profile_draft_' + form.id);
          if (draftData) {
            try {
              const data = JSON.parse(draftData);
              Object.entries(data).forEach(([key, value]) => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && field.type !== 'password' && field.value === '') {
                  field.value = value;
                  // Show subtle indication that draft was loaded
                  field.style.backgroundColor = '#fef3c7';
                  setTimeout(() => {
                    field.style.backgroundColor = '';
                  }, 3000);
                }
              });
            } catch (e) {
              console.warn('Failed to load draft data:', e);
            }
          }
        });
      });

      // Clear draft data on successful form submission
      document.addEventListener('formSubmissionSuccess', (e) => {
        localStorage.removeItem('profile_draft_' + e.detail.form.id);
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
