@extends('layouts.app')

@section('title', 'Edit Profile')

@section('header')
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <div>
      <h1 class="h3 mb-0 text-gray-900">
        <i class="fas fa-user-edit text-primary me-2"></i>
        Edit Profile
      </h1>
      <nav aria-label="breadcrumb" class="mt-1">
        <ol class="breadcrumb breadcrumb-sm mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a>
          </li>
          <li class="breadcrumb-item">
            <a href="{{ route('profile.show') }}" class="text-decoration-none">Profile</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </nav>
    </div>
    <div class="mt-3 mt-md-0">
      <div class="btn-group" role="group" aria-label="Profile actions">
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-sm">
          <i class="fas fa-eye me-1"></i> View Profile
        </a>
        <a href="{{ route('profile.security') }}" class="btn btn-outline-success btn-sm">
          <i class="fas fa-shield-alt me-1"></i> Security
        </a>
        <a href="{{ route('profile.activity.dashboard') }}" class="btn btn-outline-info btn-sm">
          <i class="fas fa-chart-bar me-1"></i> Activity
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="container-fluid px-4">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-8">

        {{-- Success Messages --}}
        @if (session('status') === 'profile-updated')
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle text-success me-2"></i>
              <strong>Success!</strong> Your profile has been updated successfully.
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if (session('status') === 'password-updated')
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-shield-alt text-success me-2"></i>
              <strong>Password Updated!</strong> Your password has been changed successfully.
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle text-success me-2"></i>
              {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        {{-- Error Messages --}}
        @if (session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle text-danger me-2"></i>
              <strong>Error:</strong> {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        {{-- Validation Errors Summary --}}
        @if (isset($errors) && $errors->any())
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
              <i class="fas fa-exclamation-triangle text-warning me-2 mt-1"></i>
              <div>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 small">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        <ul class="mt-2 ml-6 list-disc">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  {{-- Quick Navigation Menu --}}
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-center">
        <div class="btn-group" role="group" aria-label="Profile sections navigation">
          <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="scrollToSection('profile-picture-section')">
            <i class="fas fa-camera me-1"></i> Picture
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="scrollToSection('personal-info-section')">
            <i class="fas fa-user me-1"></i> Personal
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToSection('security-section')">
            <i class="fas fa-lock me-1"></i> Security
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToSection('preferences-section')">
            <i class="fas fa-cog me-1"></i> Preferences
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToSection('user-info-section')">
            <i class="fas fa-info-circle me-1"></i> Account
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Profile Picture Section --}}
  <div class="card border-0 shadow-sm mb-4" id="profile-picture-section">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-camera text-primary me-2"></i>
        Profile Picture
      </h5>
    </div>
    <div class="card-body">
      @include('components.profile-picture-upload')
    </div>
  </div>

  {{-- Personal Information --}}
  <div class="card border-0 shadow-sm mb-4" id="personal-info-section">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-user text-primary me-2"></i>
        Personal Information
      </h5>
    </div>
    <div class="card-body">
      @include('profile.partials.update-profile-information-form')
    </div>
  </div>

  {{-- Password & Security --}}
  <div class="card border-0 shadow-sm mb-4" id="security-section">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-lock text-primary me-2"></i>
        Password & Security
      </h5>
    </div>
    <div class="card-body">
      @include('profile.partials.update-password-form')

      <div class="mt-4 pt-4 border-top">
        <div class="row">
          <div class="col-md-6">
            <a href="{{ route('profile.security') }}" class="btn btn-outline-success w-100 mb-2 mb-md-0">
              <i class="fas fa-shield-alt me-2"></i>
              Security Settings
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('profile.activity.dashboard') }}" class="btn btn-outline-info w-100">
              <i class="fas fa-history me-2"></i>
              Login History
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- User Preferences --}}
  <div class="card border-0 shadow-sm mb-4" id="preferences-section">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-cog text-primary me-2"></i>
        Preferences & Settings
      </h5>
    </div>
    <div class="card-body">
      <form id="preferences-form" method="POST" action="{{ route('profile.update') }}" class="needs-validation"
        novalidate>
        @csrf
        @method('patch')

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="timezone" class="form-label">
              <i class="fas fa-clock text-muted me-1"></i>
              Timezone
            </label>
            <select class="form-select" id="timezone" name="timezone">
              <option value="">Select timezone...</option>
              @foreach (timezone_identifiers_list() as $timezone)
                <option value="{{ $timezone }}"
                  {{ old('timezone', $user->timezone ?? '') === $timezone ? 'selected' : '' }}>
                  {{ $timezone }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="language" class="form-label">
              <i class="fas fa-language text-muted me-1"></i>
              Language
            </label>
            <select class="form-select" id="language" name="language">
              <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>
                English</option>
              <option value="es" {{ old('language', $user->language ?? 'en') === 'es' ? 'selected' : '' }}>
                Español</option>
              <option value="fr" {{ old('language', $user->language ?? 'en') === 'fr' ? 'selected' : '' }}>
                Français</option>
              <option value="de" {{ old('language', $user->language ?? 'en') === 'de' ? 'selected' : '' }}>
                Deutsch</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label for="bio" class="form-label">
            <i class="fas fa-pen text-muted me-1"></i>
            Bio
          </label>
          <textarea class="form-control" id="bio" name="bio" rows="3"
            placeholder="Tell us about yourself...">{{ old('bio', $user->bio ?? '') }}</textarea>
          <div class="form-text">Brief description about yourself (max 500 characters)</div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>
            Save Preferences
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Enhanced User Info --}}
  <div class="card border-0 shadow-sm mb-4" id="user-info-section">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-info-circle text-primary me-2"></i>
        Account Information
      </h5>
    </div>
    <div class="card-body">
      @include('profile.partials.enhanced-user-info')
    </div>
  </div>

  {{-- Danger Zone --}}
  <div class="card border-danger shadow-sm mb-4" id="danger-zone-section">
    <div class="card-header bg-danger bg-opacity-10 border-bottom-0">
      <h5 class="card-title mb-0 text-danger">
        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
        Danger Zone
      </h5>
    </div>
    <div class="card-body">
      <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Warning:</strong> Actions in this section are permanent and cannot be undone.
      </div>
      @include('profile.partials.delete-user-form')
    </div>
  </div>

  </div>
  </div>
  </div>
@endsection

@push('scripts')
  <script type="module" src="{{ asset('js/components/form-validation.js') }}"></script>
  <script type="module" src="{{ asset('js/components/loading-manager.js') }}"></script>

  <script>
    // Smooth scrolling to sections
    function scrollToSection(sectionId) {
      document.getElementById(sectionId).scrollIntoView({
        behavior: 'smooth',
        block: 'start',
        inline: 'nearest'
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Auto-save draft functionality
      const forms = document.querySelectorAll('form:not([data-no-autosave])');

      forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
          input.addEventListener('input', debounce(function() {
            saveDraft(form);
          }, 500));
        });

        // Load draft on page load
        loadDraft(form);
      });

      // Form validation
      const validationForms = document.querySelectorAll('.needs-validation');

      Array.from(validationForms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });

      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });

    // Debounce function for auto-save
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // Save form data as draft
    function saveDraft(form) {
      try {
        const formData = new FormData(form);
        const draftData = {};

        for (let [key, value] of formData.entries()) {
          if (key !== '_token' && key !== '_method') {
            draftData[key] = value;
          }
        }

        localStorage.setItem('profile_draft_' + form.id, JSON.stringify(draftData));
      } catch (e) {
        console.warn('Failed to save draft data:', e);
      }
    }

    // Load draft data
    function loadDraft(form) {
      try {
        const draftData = localStorage.getItem('profile_draft_' + form.id);
        if (draftData) {
          const data = JSON.parse(draftData);

          Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input && !input.value) {
              input.value = data[key];
            }
          });
        }
      } catch (e) {
        console.warn('Failed to load draft data:', e);
      }
    }

    // Clear draft data on successful form submission
    document.addEventListener('formSubmissionSuccess', (e) => {
      localStorage.removeItem('profile_draft_' + e.detail.form.id);
    });
  </script>
@endpush

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}?t={{ time() }}">
@endpush
