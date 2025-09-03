@extends('layouts.app')

@section('title', 'My Profile')

@section('meta')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
@endsection

@section('header')
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <div>
      <h1 class="h3 mb-0 text-gray-900">My Profile</h1>
      <nav aria-label="breadcrumb" class="mt-1">
        <ol class="breadcrumb breadcrumb-sm mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none" aria-label="Go to Dashboard">Dashboard</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
      </nav>
    </div>
    <div class="mt-3 mt-md-0" x-data="{ showActionMenu: false }">
      <div class="btn-group" role="group" aria-label="Profile actions" x-show="!showActionMenu" x-transition>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm"
          aria-label="Edit your profile information">
          <i class="fas fa-user-edit me-1" aria-hidden="true"></i> Edit Profile
        </a>
        <a href="{{ route('profile.security') }}" class="btn btn-outline-success btn-sm"
          aria-label="Manage account security settings">
          <i class="fas fa-shield-alt me-1" aria-hidden="true"></i> Security
        </a>
        <a href="{{ route('profile.activity.dashboard') }}" class="btn btn-outline-info btn-sm"
          aria-label="View account activity and statistics">
          <i class="fas fa-chart-bar me-1" aria-hidden="true"></i> Activity
        </a>
      </div>
      <!-- Mobile Action Menu Toggle -->
      <button class="btn btn-primary btn-sm d-md-none" @click="showActionMenu = !showActionMenu"
        :aria-expanded="showActionMenu" aria-label="Toggle profile actions menu">
        <i class="fas fa-bars" aria-hidden="true"></i> Actions
      </button>
      <!-- Mobile Action Menu -->
      <div class="dropdown-menu show position-static mt-2 d-md-none" x-show="showActionMenu" x-transition role="menu">
        <a href="{{ route('profile.edit') }}" class="dropdown-item" role="menuitem">
          <i class="fas fa-user-edit me-2" aria-hidden="true"></i> Edit Profile
        </a>
        <a href="{{ route('profile.security') }}" class="dropdown-item" role="menuitem">
          <i class="fas fa-shield-alt me-2" aria-hidden="true"></i> Security
        </a>
        <a href="{{ route('profile.activity.dashboard') }}" class="dropdown-item" role="menuitem">
          <i class="fas fa-chart-bar me-2" aria-hidden="true"></i> Activity
        </a>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    /* Modern Sports Theme */
    .sports-gradient {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 25%, #10b981 50%, #059669 75%, #065f46 100%);
    }

    /* Enhanced Accessibility & High Contrast Support */
    @media (prefers-contrast: high) {
      .stats-card {
        border: 2px solid #000 !important;
      }

      .profile-avatar,
      .profile-initials {
        border: 3px solid #000 !important;
      }

      .text-muted {
        color: #333 !important;
      }
    }

    /* Reduced Motion Support */
    @media (prefers-reduced-motion: reduce) {

      .stats-card,
      .profile-avatar,
      .profile-initials,
      .progress-ring,
      .enhanced-feature {
        transition: none !important;
        animation: none !important;
      }
    }

    /* Enhanced Stats Cards */
    .stats-card {
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
      position: relative;
      overflow: hidden;
    }

    .stats-card:hover,
    .stats-card:focus-within {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      border-left-color: #3b82f6;
    }

    .stats-card:focus-within {
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    /* Touch-Friendly Interactive Elements */
    .btn,
    .card,
    .stats-card {
      min-height: 44px;
      touch-action: manipulation;
    }

    .profile-avatar,
    .profile-initials {
      min-width: 44px;
      min-height: 44px;
    }

    /* Enhanced Loading States */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
    }

    @keyframes loading {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    .skeleton-text {
      height: 1rem;
      border-radius: 4px;
    }

    .skeleton-title {
      height: 1.5rem;
      border-radius: 4px;
    }

    .skeleton-circle {
      border-radius: 50%;
    }

    .skeleton-card {
      height: 120px;
      border-radius: 8px;
    }

    /* Lazy Loading & Progressive Enhancement */
    .lazy-section {
      min-height: 200px;
      transition: opacity 0.3s ease;
    }

    .lazy-section.loading {
      opacity: 0.7;
    }

    .lazy-section.loaded {
      opacity: 1;
    }

    .enhanced-feature {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease;
    }

    .enhanced-feature.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Enhanced Progress Rings */
    .progress-ring {
      transition: stroke-dashoffset 1s ease-in-out;
      filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    }

    /* Recommendation Cards */
    .recommendation-card {
      border-left: 4px solid #fbbf24;
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      transition: all 0.3s ease;
      position: relative;
    }

    .recommendation-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, 0.15);
    }

    .recommendation-card:focus-within {
      outline: 2px solid #f59e0b;
      outline-offset: 2px;
    }

    /* Enhanced Profile Header */
    .profile-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
    }

    .profile-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
      pointer-events: none;
    }

    /* Touch-Friendly Avatar */
    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 4px solid rgba(255, 255, 255, 0.2);
      object-fit: cover;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
    }

    .profile-avatar:hover,
    .profile-avatar:focus {
      transform: scale(1.05);
      border-color: rgba(255, 255, 255, 0.4);
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    .profile-initials {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: bold;
      color: white;
      border: 4px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .profile-initials:hover,
    .profile-initials:focus {
      transform: scale(1.05);
      border-color: rgba(255, 255, 255, 0.4);
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    /* Enhanced Mobile Responsiveness */
    @media (max-width: 768px) {
      .card-header {
        padding: 1rem !important;
      }

      .profile-stats {
        margin-bottom: 1rem;
      }

      .btn-group {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem;
      }

      .btn-group .btn {
        width: 100%;
        margin-bottom: 0.25rem;
      }

      .profile-avatar,
      .profile-initials {
        width: 64px;
        height: 64px;
      }

      .profile-initials {
        font-size: 1.25rem;
      }

      .stats-card:hover {
        transform: none;
        /* Reduce hover effects on touch devices */
      }
    }

    /* Focus Management & Keyboard Navigation */
    .skip-link {
      position: absolute;
      top: -40px;
      left: 6px;
      background: #3b82f6;
      color: white;
      padding: 8px;
      text-decoration: none;
      z-index: 9999;
      border-radius: 4px;
    }

    .skip-link:focus {
      top: 6px;
    }

    /* Screen Reader Only Content */
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    .sr-only-focusable:focus {
      position: static;
      width: auto;
      height: auto;
      padding: inherit;
      margin: inherit;
      overflow: visible;
      clip: auto;
      white-space: normal;
    }

    /* Enhanced Dark Mode Support */
    @media (prefers-color-scheme: dark) {
      .skeleton {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
      }

      .recommendation-card {
        background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
        border-left-color: #f59e0b;
      }
    }

    /* Performance Optimizations */
    .card {
      will-change: transform;
      backface-visibility: hidden;
    }

    /* Error States */
    .error-state {
      text-align: center;
      padding: 2rem;
      color: #dc3545;
    }

    .retry-button {
      margin-top: 1rem;
    }

    .enhanced-feature {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease;
    }

    .enhanced-feature.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .progress-ring {
      transition: stroke-dashoffset 1s ease-in-out;
    }

    .recommendation-card {
      border-left: 4px solid #fbbf24;
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      transition: all 0.3s ease;
    }

    .recommendation-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, 0.15);
    }

    .profile-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
    }

    .profile-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
   <defs> <pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"> <circle cx="10" cy="10" r="1" fill="%23ffffff" opacity="0.1" /> </pattern> </defs> <rect width="100" height="100" fill="url(%23pattern)" /> </svg>');
   pointer-events: none;
      }

      .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.2);
        object-fit: cover;
        transition: all 0.3s ease;
        cursor: pointer;
      }

      .profile-avatar:hover {
        transform: scale(1.05);
        border-color: rgba(255, 255, 255, 0.4);
      }

      .profile-initials {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        border: 4px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        cursor: pointer;
      }

      .profile-initials:hover {
        transform: scale(1.05);
        border-color: rgba(255, 255, 255, 0.4);
      }

      /* Enhanced responsive design */
      @media (max-width: 768px) {
        .card-header {
          padding: 1rem !important;
        }

        .profile-stats {
          margin-bottom: 1rem;
        }

        .btn-group {
          flex-direction: column;
          width: 100%;
        }

        .btn-group .btn {
          margin-bottom: 0.25rem;
        }
      }
  </style>
@endpush

@section('content')
  <!-- Skip Link for Accessibility -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <div class="container-fluid px-4" x-data="profilePage()" x-init="init()">
    <!-- Enhanced Flash Messages with ARIA Live Region -->
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
      @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" x-data="{ show: true }"
          x-show="show" x-transition>
          <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
            <strong>Success!</strong> Profile updated successfully!
          </div>
          <button type="button" class="btn-close" @click="show = false" aria-label="Close notification"></button>
        </div>
      @endif

      @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" x-data="{ show: true }"
          x-show="show" x-transition>
          <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
            <strong>Success!</strong> Password updated successfully!
          </div>
          <button type="button" class="btn-close" @click="show = false" aria-label="Close notification"></button>
        </div>
      @endif
    </div>

    <main id="main-content" class="main-content">
      <!-- Enhanced Profile Header Card -->
      <section class="card mb-4 border-0 shadow-sm overflow-hidden" x-data="{ photoUploadLoading: false }"
        aria-labelledby="profile-header-title">
        <div class="card-header profile-header text-white border-0">
          <div class="p-3 p-md-4">
            <div class="row align-items-center g-3">
              <div class="col-auto">
                <!-- Enhanced Profile Photo with Upload -->
                <div class="position-relative">
                  <div class="profile-photo-container" @click="triggerPhotoUpload()" tabindex="0" role="button"
                    @keydown.enter="triggerPhotoUpload()" @keydown.space.prevent="triggerPhotoUpload()"
                    aria-label="Change profile photo">
                    @if ($user->profile_picture)
                      <img src="{{ $user->getProfileDisplay()['picture_url'] }}"
                        alt="Profile picture of {{ $user->getFullNameAttribute() ?: $user->name }}"
                        class="profile-avatar" x-show="!photoUploadLoading">
                    @else
                      <div class="profile-initials" x-show="!photoUploadLoading">
                        <span aria-hidden="true">{{ $user->getProfileDisplay()['initials'] }}</span>
                        <span class="sr-only">Profile initials for
                          {{ $user->getFullNameAttribute() ?: $user->name }}</span>
                      </div>
                    @endif

                    <!-- Loading State -->
                    <div class="profile-avatar d-flex align-items-center justify-content-center"
                      x-show="photoUploadLoading">
                      <div class="spinner-border spinner-border-sm text-white" role="status">
                        <span class="sr-only">Uploading photo...</span>
                      </div>
                    </div>
                  </div>

                  <!-- Upload Button -->
                  <button class="position-absolute bottom-0 end-0 btn btn-light btn-sm rounded-circle p-1"
                    @click="triggerPhotoUpload()" style="width: 32px; height: 32px;" title="Change profile photo"
                    aria-label="Change profile photo">
                    <i class="fas fa-camera text-primary" style="font-size: 12px;" aria-hidden="true"></i>
                  </button>

                  <!-- Hidden File Input -->
                  <input type="file" id="photo-upload" accept="image/*" style="display: none;"
                    @change="handlePhotoUpload($event)" aria-label="Select profile photo">
                </div>
              </div>

              <div class="col">
                <h1 id="profile-header-title" class="h3 mb-1 text-white">
                  {{ $user->getFullNameAttribute() ?: $user->name }}
                </h1>
                <p class="mb-2 text-white-75" role="text">{{ $user->email }}</p>
                <div class="d-flex flex-wrap gap-2" role="list">
                  <span class="badge bg-light text-dark px-2 py-1" role="listitem">
                    <i class="fas fa-user-tag me-1" aria-hidden="true"></i>
                    <span>{{ ucfirst($user->role) }}</span>
                    <span class="sr-only">User role</span>
                  </span>
                  @if (isset($securityStatus['email_verified']) && $securityStatus['email_verified'])
                    <span class="badge bg-success px-2 py-1" role="listitem">
                      <i class="fas fa-check-circle me-1" aria-hidden="true"></i>
                      <span>Email Verified</span>
                    </span>
                  @endif
                  @if (isset($securityStatus['two_factor_enabled']) && $securityStatus['two_factor_enabled'])
                    <span class="badge bg-primary px-2 py-1" role="listitem">
                      <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                      <span>2FA Enabled</span>
                    </span>
                  @endif
                  <span class="badge bg-info px-2 py-1" role="listitem">
                    <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                    <span>Member since {{ $user->created_at->format('M Y') }}</span>
                  </span>
                </div>
              </div>

              <div class="col-12 col-md-auto">
                <div class="d-flex gap-2 justify-content-start justify-content-md-end">
                  <a href="{{ route('tickets.redirect') }}" class="btn btn-outline-light btn-sm"
                    aria-label="View available sports tickets">
                    <i class="fas fa-ticket-alt me-1" aria-hidden="true"></i> Sports Tickets
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
          <!-- Profile Completion & Security Score -->
          <section class="row mb-4" aria-labelledby="completion-security-title">
            <h2 id="completion-security-title" class="sr-only">Profile Completion and Security Status</h2>

            <!-- Profile Completion Card -->
            <div class="col-md-6">
              <article class="card stats-card h-100 border-0 shadow-sm" x-data="{ animateCompletion: false }"
                x-intersect="animateCompletion = true">
                <div class="card-body text-center">
                  <h3 class="card-title text-muted mb-3">
                    <i class="fas fa-user-check text-primary me-2" aria-hidden="true"></i>
                    Profile Completion
                  </h3>

                  <!-- Enhanced Progress Ring with Animation -->
                  <div class="position-relative d-inline-block mb-3" id="profile-completion" role="progressbar"
                    :aria-valuenow="animateCompletion ? {{ $profileCompletion['percentage'] }} : 0" aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-label="`Profile completion: ${animateCompletion ? {{ $profileCompletion['percentage'] }} : 0} percent`">
                    <svg class="progress-ring" width="100" height="100" aria-hidden="true">
                      <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8"
                        fill="transparent" />
                      <circle cx="50" cy="50" r="40" stroke="#3b82f6" stroke-width="8"
                        fill="transparent" stroke-dasharray="{{ 2 * pi() * 40 }}"
                        :stroke-dashoffset="animateCompletion ? {{ 2 * pi() * 40 * (1 - $profileCompletion['percentage'] / 100) }} :
                            {{ 2 * pi() * 40 }}"
                        stroke-linecap="round" transform="rotate(-90 50 50)"
                        style="transition: stroke-dashoffset 2s ease-in-out" />
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle">
                      <span class="h4 fw-bold text-primary progress-text"
                        x-text="animateCompletion ? '{{ $profileCompletion['percentage'] }}%' : '0%'">
                        {{ $profileCompletion['percentage'] }}%
                      </span>
                    </div>
                  </div>

                  <p class="text-muted mb-2">
                    <span x-text="animateCompletion ? '{{ $profileCompletion['completed_count'] }}' : '0'">
                      {{ $profileCompletion['completed_count'] }}
                    </span>/<span>{{ $profileCompletion['total_fields'] }}</span>
                    fields complete
                  </p>

                  <span
                    class="badge bg-{{ $profileCompletion['status'] === 'excellent' ? 'success' : ($profileCompletion['status'] === 'good' ? 'primary' : 'warning') }}"
                    role="status">
                    {{ ucfirst($profileCompletion['status']) }}
                  </span>

                  <!-- Action Button -->
                  @if ($profileCompletion['percentage'] < 100)
                    <div class="mt-3">
                      <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary"
                        aria-label="Complete your profile">
                        <i class="fas fa-arrow-right me-1" aria-hidden="true"></i>
                        Complete Profile
                      </a>
                    </div>
                  @endif
                </div>
              </article>
            </div>

            <!-- Security Score Card -->
            <div class="col-md-6">
              <x-profile.security-score :score="$profileInsights['security_score'] ?? 50" improveRoute="profile.security" />
            </div>
          </section>

          <!-- Enhanced Sports Events Monitoring Statistics -->
          <section class="card mb-4 border-0 shadow-sm" x-data="{ statsLoading: false, lastUpdated: '{{ now()->format('g:i A') }}' }" aria-labelledby="sports-stats-title">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <h3 id="sports-stats-title" class="card-title mb-0">
                  <i class="fas fa-ticket-alt text-primary me-2" aria-hidden="true"></i>
                  Sports Events & Tickets Monitoring
                </h3>
                <div class="d-flex gap-2">
                  <button @click="refreshStats()" :disabled="statsLoading" class="btn btn-outline-primary btn-sm"
                    :aria-label="statsLoading ? 'Refreshing statistics...' : 'Refresh statistics'">
                    <i :class="statsLoading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" class="me-1"
                      aria-hidden="true"></i>
                    <span x-text="statsLoading ? 'Updating...' : 'Refresh'">Refresh</span>
                  </button>
                  <a href="{{ route('tickets.redirect') }}" class="btn btn-outline-primary btn-sm"
                    aria-label="View all available tickets">
                    <i class="fas fa-ticket-alt me-1" aria-hidden="true"></i> View Tickets
                  </a>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row text-center" role="group" aria-labelledby="stats-grid-title">
                <h4 id="stats-grid-title" class="sr-only">Statistics Overview</h4>

                <!-- Stat Cards -->
                <div class="col-md-3 col-6 mb-3">
                  <x-profile.stat-card id="monitored-events" icon="fa-bell" icon-color="text-warning"
                    label="Active Alerts" :value="isset($userStats['monitored_events']) ? $userStats['monitored_events'] : 0" dispatch="alerts" />
                </div>
                <div class="col-md-3 col-6 mb-3">
                  <x-profile.stat-card id="total-alerts" icon="fa-chart-line" icon-color="text-success"
                    label="Total Alerts" :value="isset($userStats['total_alerts']) ? $userStats['total_alerts'] : 0" dispatch="total-alerts" />
                </div>
                <div class="col-md-3 col-6 mb-3">
                  <x-profile.stat-card id="active-searches" icon="fa-search" icon-color="text-info"
                    label="Active Searches" :value="isset($userStats['active_searches']) ? $userStats['active_searches'] : 0" dispatch="searches" />
                </div>
                <div class="col-md-3 col-6 mb-3">
                  <x-profile.stat-card id="recent-purchases" icon="fa-shopping-cart" icon-color="text-success"
                    label="Recent Purchases" :value="isset($userStats['recent_purchases']) ? $userStats['recent_purchases'] : 0" dispatch="purchases" />
                </div>
              </div>

              <div class="row">
                <div class="col-12 text-center">
                  <small class="text-muted stats-updated" x-text="'Last updated: ' + lastUpdated" role="status"
                    aria-live="polite">
                    Last updated: {{ now()->format('g:i A') }}
                  </small>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
  </div>
  <div class="row">
    <div class="col-12 text-center">
      <small class="text-muted stats-updated">Last updated: {{ now()->format('g:i A') }}</small>
    </div>
  </div>
  </div>
  </div>

  <!-- Account Activity Overview -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom-0">
      <h5 class="card-title mb-0">
        <i class="fas fa-activity text-info me-2"></i>
        Account Activity
      </h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="d-flex align-items-center">
            <div class="stats-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
              <i class="fas fa-sign-in-alt text-primary"></i>
            </div>
            <div>
              <h6 class="mb-0">
                {{ isset($userStats['login_count']) ? $userStats['login_count'] : 0 }}</h6>
              <small class="text-muted">Total Logins</small>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="d-flex align-items-center">
            <div class="stats-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
              <i class="fas fa-clock text-success"></i>
            </div>
            <div>
              <h6 class="mb-0">
                {{ isset($userStats['last_login_display']) ? $userStats['last_login_display'] : 'Never' }}
              </h6>
              <small class="text-muted">Last Login</small>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="d-flex align-items-center">
            <div class="stats-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
              <i class="fas fa-calendar text-info"></i>
            </div>
            <div>
              <h6 class="mb-0">
                {{ isset($userStats['joined_days_ago']) ? $userStats['joined_days_ago'] : 0 }}
                days</h6>
              <small class="text-muted">Account Age</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

  <!-- Right Column -->
  <div class="col-lg-4">
    <!-- Profile Recommendations -->
    @if (isset($profileInsights['recommendations']) &&
            is_array($profileInsights['recommendations']) &&
            count($profileInsights['recommendations']) > 0)
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom-0">
          <h5 class="card-title mb-0">
            <i class="fas fa-lightbulb text-warning me-2"></i>
            Recommendations
          </h5>
        </div>
        <div class="card-body">
          @foreach ($profileInsights['recommendations'] as $recommendation)
            <div class="recommendation-card rounded p-3 mb-3">
              <div class="d-flex align-items-start">
                <i
                  class="fas fa-{{ $recommendation['icon'] ?? 'info-circle' }} text-{{ isset($recommendation['priority']) && $recommendation['priority'] === 'high' ? 'danger' : 'warning' }} me-2 mt-1"></i>
                <div class="flex-grow-1">
                  <h6 class="mb-1">{{ $recommendation['title'] ?? 'Recommendation' }}</h6>
                  <p class="small text-muted mb-2">
                    {{ $recommendation['description'] ?? 'No description available' }}</p>
                  @if (isset($recommendation['route']) && $recommendation['route'])
                    <a href="{{ route($recommendation['route']) }}" class="btn btn-sm btn-outline-primary">
                      {{ $recommendation['action'] ?? 'Take Action' }}
                    </a>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <!-- Security Status -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom-0">
        <h5 class="card-title mb-0">
          <i class="fas fa-shield-alt text-success me-2"></i>
          Security Status
        </h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
            <div class="d-flex align-items-center">
              <i
                class="fas fa-envelope text-{{ isset($securityStatus['email_verified']) && $securityStatus['email_verified'] ? 'success' : 'danger' }} me-2"></i>
              <small>Email Verification</small>
            </div>
            <span
              class="badge bg-{{ isset($securityStatus['email_verified']) && $securityStatus['email_verified'] ? 'success' : 'danger' }}">
              {{ isset($securityStatus['email_verified']) && $securityStatus['email_verified'] ? 'Verified' : 'Pending' }}
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
            <div class="d-flex align-items-center">
              @php
                $twoFactorEnabled = isset($securityStatus['two_factor_enabled'])
                    ? $securityStatus['two_factor_enabled']
                    : false;
              @endphp
              <i class="fas fa-mobile-alt text-{{ $twoFactorEnabled ? 'success' : 'warning' }} me-2"></i>
              <small>Two-Factor Auth</small>
            </div>
            <span class="badge bg-{{ $twoFactorEnabled ? 'success' : 'warning' }}">
              {{ $twoFactorEnabled ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
            <div class="d-flex align-items-center">
              <i class="fas fa-key text-info me-2"></i>
              <small>Password Age</small>
            </div>
            <span
              class="badge bg-{{ isset($securityStatus['password_age_days']) ? ($securityStatus['password_age_days'] <= 90 ? 'success' : ($securityStatus['password_age_days'] <= 180 ? 'warning' : 'danger')) : 'secondary' }}">
              {{ isset($securityStatus['password_age_days']) ? $securityStatus['password_age_days'] : 'Unknown' }}
              days
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
            <div class="d-flex align-items-center">
              <i class="fas fa-devices text-primary me-2"></i>
              <small>Trusted Devices</small>
            </div>
            <span
              class="badge bg-primary">{{ isset($securityStatus['trusted_devices_count']) ? $securityStatus['trusted_devices_count'] : 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom-0">
        <h5 class="card-title mb-0">
          <i class="fas fa-bolt text-primary me-2"></i>
          Quick Actions
        </h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-user-edit me-2"></i>
            Edit Profile
          </a>
          <a href="{{ route('profile.security') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-shield-alt me-2"></i>
            Security & Privacy
          </a>
          <a href="{{ route('profile.activity.dashboard') }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-chart-bar me-2"></i>
            My Activity
          </a>
          <a href="{{ route('tickets.redirect') }}" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-ticket-alt me-2"></i>
            Sports Tickets
          </a>
        </div>
      </div>
    </div>
  </div>
  </main>
  </div>
@endsection

@push('styles')
  <style>
    /* Ensure profile page is fully visible */
    html,
    body {
      height: 100%;
      overflow-x: hidden;
      overflow-y: auto;
    }

    .container-fluid {
      min-height: 100vh !important;
      padding-bottom: 2rem;
    }

    /* Fix any potential z-index issues */
    .main-content {
      position: relative;
      z-index: 1;
    }

    /* Ensure cards are visible */
    .card {
      opacity: 1 !important;
      visibility: visible !important;
      display: block !important;
    }

    /* Progress rings styling */
    .progress-ring {
      transform: rotate(-90deg);
      width: 100px;
      height: 100px;
    }

    .progress-ring__circle {
      transition: stroke-dashoffset 0.35s;
      transform: rotate(90deg);
      transform-origin: 50% 50%;
    }

    /* Responsive fixes */
    @media (max-width: 768px) {
      .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
      }

      .card {
        margin-bottom: 1rem;
      }
    }

    /* Debug styles for visibility testing */
    .debug-visible {
      border: 2px solid red !important;
      background: rgba(255, 0, 0, 0.1) !important;
    }
  </style>
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="{{ asset('js/profile-page.js') }}" defer></script>
@endpush
