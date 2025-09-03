@extends('layouts.app')

@section('title', 'My Profile')

@section('header')
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <div>
      <h1 class="h3 mb-0 text-gray-900">My Profile</h1>
      <nav aria-label="breadcrumb" class="mt-1">
        <ol class="breadcrumb breadcrumb-sm mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
      </nav>
    </div>
    <div class="mt-3 mt-md-0">
      <div class="btn-group" role="group" aria-label="Profile actions">
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-user-edit me-1"></i> Edit Profile
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

@push('styles')
  <style>
    .sports-gradient {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 25%, #10b981 50%, #059669 75%, #065f46 100%);
    }

    .stats-card {
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
    }

    .stats-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      border-left-color: #3b82f6;
    }

    /* Skeleton Loading Animation */
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

    /* Lazy Loading States */
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

    /* Progressive Enhancement */
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
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
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
  <div class="container-fluid px-4">
    <!-- Enhanced Flash Messages -->
    @if (session('status') === 'profile-updated')
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-check-circle me-2"></i>
          <strong>Success!</strong> Profile updated successfully!
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if (session('status') === 'password-updated')
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-check-circle me-2"></i>
          <strong>Success!</strong> Password updated successfully!
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <!-- Profile Header Card -->
    <div class="card mb-4 border-0 shadow-sm overflow-hidden">
      <div class="card-header profile-header text-white border-0">
        <div class="p-3 p-md-4">
          <div class="row align-items-center g-3">
            <div class="col-auto">
              <!-- Profile Photo -->
              <div class="position-relative">
                @if ($user->profile_picture)
                  <img src="{{ $user->getProfileDisplay()['picture_url'] }}"
                    alt="{{ $user->getFullNameAttribute() ?: $user->name }}" class="profile-avatar">
                @else
                  <div class="profile-initials">
                    {{ $user->getProfileDisplay()['initials'] }}
                  </div>
                @endif
                <!-- Upload indicator -->
                <a href="{{ route('profile.edit') }}"
                  class="position-absolute bottom-0 end-0 btn btn-light btn-sm rounded-circle p-1" title="Change photo"
                  style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-camera text-primary" style="font-size: 12px;"></i>
                </a>
              </div>
            </div>
            <div class="col">
              <h1 class="h3 mb-1 text-white">{{ $user->getFullNameAttribute() ?: $user->name }}</h1>
              <p class="mb-2 text-white-75">{{ $user->email }}</p>
              <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark px-2 py-1">
                  <i class="fas fa-user-tag me-1"></i>
                  {{ ucfirst($user->role) }}
                </span>
                @if (isset($securityStatus['email_verified']) && $securityStatus['email_verified'])
                  <span class="badge bg-success px-2 py-1">
                    <i class="fas fa-check-circle me-1"></i>
                    Email Verified
                  </span>
                @endif
                @if (isset($securityStatus['two_factor_enabled']) && $securityStatus['two_factor_enabled'])
                  <span class="badge bg-primary px-2 py-1">
                    <i class="fas fa-shield-alt me-1"></i>
                    2FA Enabled
                  </span>
                @endif
                <span class="badge bg-info px-2 py-1">
                  <i class="fas fa-calendar me-1"></i>
                  Member since {{ $user->created_at->format('M Y') }}
                </span>
              </div>
            </div>
            <div class="col-12 col-md-auto">
              <div class="d-flex gap-2 justify-content-start justify-content-md-end">
                <a href="{{ route('tickets.redirect') }}" class="btn btn-outline-light btn-sm">
                  <i class="fas fa-ticket-alt me-1"></i> Sports Tickets
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Left Column -->
      <div class="col-lg-8">
        <!-- Profile Completion & Security Score -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card stats-card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title text-muted mb-3">
                  <i class="fas fa-user-check text-primary me-2"></i>
                  Profile Completion
                </h5>
                <div class="position-relative d-inline-block mb-3" id="profile-completion">
                  <svg class="progress-ring" width="100" height="100">
                    <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8" fill="transparent" />
                    <circle cx="50" cy="50" r="40" stroke="#3b82f6" stroke-width="8" fill="transparent"
                      stroke-dasharray="{{ 2 * pi() * 40 }}"
                      stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $profileCompletion['percentage'] / 100) }}"
                      stroke-linecap="round" transform="rotate(-90 50 50)" />
                  </svg>
                  <div class="position-absolute top-50 start-50 translate-middle">
                    <span class="h4 fw-bold text-primary progress-text">{{ $profileCompletion['percentage'] }}%</span>
                  </div>
                </div>
                <p class="text-muted mb-2">
                  {{ $profileCompletion['completed_count'] }}/{{ $profileCompletion['total_fields'] }}
                  fields complete</p>
                <span
                  class="badge bg-{{ $profileCompletion['status'] === 'excellent' ? 'success' : ($profileCompletion['status'] === 'good' ? 'primary' : 'warning') }}">
                  {{ ucfirst($profileCompletion['status']) }}
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card stats-card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title text-muted mb-3">
                  <i class="fas fa-shield-alt text-success me-2"></i>
                  Security Score
                </h5>
                @php
                  $securityScore = isset($profileInsights['security_score']) ? $profileInsights['security_score'] : 50;
                @endphp
                <div class="position-relative d-inline-block mb-3" id="security-score">
                  <svg class="progress-ring" width="100" height="100">
                    <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8"
                      fill="transparent" />
                    <circle cx="50" cy="50" r="40" stroke="#10b981" stroke-width="8" fill="transparent"
                      stroke-dasharray="{{ 2 * pi() * 40 }}"
                      stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $securityScore / 100) }}" stroke-linecap="round"
                      transform="rotate(-90 50 50)" />
                  </svg>
                  <div class="position-absolute top-50 start-50 translate-middle">
                    <span class="h4 fw-bold text-success progress-text">{{ $securityScore }}</span>
                  </div>
                </div>
                <p class="text-muted mb-2">Account Security Level</p>
                <span
                  class="badge bg-{{ $securityScore >= 80 ? 'success' : ($securityScore >= 60 ? 'warning' : 'danger') }}">
                  {{ $securityScore >= 80 ? 'Excellent' : ($securityScore >= 60 ? 'Good' : 'Needs Improvement') }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sports Events Monitoring Statistics -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-header bg-transparent border-bottom-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-ticket-alt text-primary me-2"></i>
                Sports Events & Tickets Monitoring
              </h5>
              <a href="{{ route('tickets.redirect') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-ticket-alt me-1"></i> View Tickets
              </a>
            </div>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-3 col-6 mb-3">
                <div class="stats-card bg-light rounded p-3">
                  <i class="fas fa-bell text-warning mb-2 fs-4"></i>
                  <h4 class="fw-bold text-primary mb-1" id="monitored-events">
                    {{ isset($userStats['monitored_events']) ? $userStats['monitored_events'] : 0 }}
                  </h4>
                  <small class="text-muted">Active Alerts</small>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="stats-card bg-light rounded p-3">
                  <i class="fas fa-chart-line text-success mb-2 fs-4"></i>
                  <h4 class="fw-bold text-primary mb-1" id="total-alerts">
                    {{ isset($userStats['total_alerts']) ? $userStats['total_alerts'] : 0 }}</h4>
                  <small class="text-muted">Total Alerts</small>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="stats-card bg-light rounded p-3">
                  <i class="fas fa-search text-info mb-2 fs-4"></i>
                  <h4 class="fw-bold text-primary mb-1" id="active-searches">
                    {{ isset($userStats['active_searches']) ? $userStats['active_searches'] : 0 }}</h4>
                  <small class="text-muted">Active Searches</small>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="stats-card bg-light rounded p-3">
                  <i class="fas fa-shopping-cart text-success mb-2 fs-4"></i>
                  <h4 class="fw-bold text-primary mb-1" id="recent-purchases">
                    {{ isset($userStats['recent_purchases']) ? $userStats['recent_purchases'] : 0 }}
                  </h4>
                  <small class="text-muted">Recent Purchases</small>
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
    </div>
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
  <script>
    // Profile photo upload handler
    function handleProfilePhotoChange(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('photo', file);

        // Show loading state
        const uploadOverlay = document.querySelector('.upload-overlay');
        if (uploadOverlay) {
          uploadOverlay.innerHTML = '<div class="spinner-border spinner-border-sm text-white"></div>';
        }

        // Upload photo
        fetch('{{ route('profile.photo.upload') }}', {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Reload page to show new photo
              location.reload();
            } else {
              alert('Error uploading photo: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error uploading photo');
          })
          .finally(() => {
            // Reset upload overlay
            if (uploadOverlay) {
              uploadOverlay.innerHTML = `
                    <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-white text-xs font-medium">Change Photo</span>
                `;
            }
          });
      }
    }

    // Animate progress rings on page load
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Profile page loading...');

      // Debug: Check page visibility and dimensions
      console.log('Document dimensions:', {
        width: document.documentElement.scrollWidth,
        height: document.documentElement.scrollHeight,
        viewportWidth: window.innerWidth,
        viewportHeight: window.innerHeight
      });

      // Ensure main container is properly sized
      const mainContainer = document.querySelector('.container-fluid');
      if (mainContainer) {
        mainContainer.style.minHeight = '100vh';
        mainContainer.style.overflow = 'visible';
        console.log('Main container sized properly');
      }

      // Enhanced statistics update functionality
      function updateProfileStats() {
        fetch('{{ route('profile.stats') }}', {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
          })
          .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
          })
          .then(data => {
            if (data.success) {
              console.log('Stats updated:', data.stats);

              // Update displayed statistics
              const stats = data.stats;
              updateStatElement('monitored-events', stats.monitored_events);
              updateStatElement('total-alerts', stats.total_alerts);
              updateStatElement('login-count', stats.login_count);
              updateStatElement('last-login', stats.last_login_display);

              // Update progress indicators if they exist
              if (stats.profile_completion) {
                updateProgressRing('profile-completion', stats.profile_completion);
              }
              if (stats.security_score) {
                updateProgressRing('security-score', stats.security_score);
              }

              // Update timestamp
              document.querySelectorAll('.stats-updated').forEach(el => {
                el.textContent = 'Updated ' + new Date().toLocaleTimeString();
              });
            }
          })
          .catch(error => {
            console.warn('Failed to update stats:', error);
          });
      }

      // Helper function to update stat elements
      function updateStatElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
          const currentValue = element.textContent;
          if (currentValue !== String(value)) {
            element.style.transform = 'scale(1.1)';
            element.textContent = value;
            setTimeout(() => {
              element.style.transform = 'scale(1)';
            }, 200);
          }
        }
      }

      // Helper function to update progress rings
      function updateProgressRing(ringId, percentage) {
        const ring = document.querySelector(`#${ringId} circle:last-child`);
        const text = document.querySelector(`#${ringId} .progress-text`);
        if (ring && text) {
          const circumference = 2 * Math.PI * 40;
          const offset = circumference * (1 - percentage / 100);
          ring.style.strokeDashoffset = offset;
          text.textContent = percentage + '%';
        }
      }

      // Check for any layout issues
      const cards = document.querySelectorAll('.card');
      console.log('Found', cards.length, 'cards on page');

      // Initialize progress rings with enhanced animation
      const progressRings = document.querySelectorAll('.progress-ring circle:last-child');
      progressRings.forEach((ring, index) => {
        try {
          const circumference = 2 * Math.PI * 40;
          ring.style.strokeDasharray = circumference;
          ring.style.strokeDashoffset = circumference;
          ring.style.transition = 'stroke-dashoffset 1s ease-in-out';

          // Animate with delay
          setTimeout(() => {
            const dashOffset = ring.getAttribute('stroke-dashoffset');
            ring.style.strokeDashoffset = dashOffset;
            console.log('Progress ring', index, 'animated');
          }, 500 + (index * 200));
        } catch (error) {
          console.warn('Error animating progress ring:', error);
        }
      });

      // Auto-update stats every 5 minutes
      setInterval(updateProfileStats, 5 * 60 * 1000);

      // Update on page visibility change (when user returns to tab)
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          setTimeout(updateProfileStats, 1000);
        }
      });

      // Force layout recalculation
      setTimeout(() => {
        document.body.style.display = 'block';
        window.dispatchEvent(new Event('resize'));
        console.log('Layout recalculated');
      }, 100);

      console.log('Enhanced profile page initialization complete');
    });

    // Profile photo upload enhancement
    function initializePhotoUpload() {
      const photoUpload = document.querySelector('input[type="file"][name="photo"]');
      if (photoUpload) {
        photoUpload.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (file) {
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
              const preview = document.querySelector('.profile-avatar, .profile-initials');
              if (preview) {
                if (preview.tagName === 'IMG') {
                  preview.src = e.target.result;
                } else {
                  // Replace initials div with image
                  const img = document.createElement('img');
                  img.src = e.target.result;
                  img.className = 'profile-avatar';
                  img.alt = 'Profile Picture';
                  preview.parentNode.replaceChild(img, preview);
                }
              }
            };
            reader.readAsDataURL(file);

            // Auto-upload if desired
            // uploadProfilePhoto(file);
          }
        });
      }
    }

    // Initialize enhanced features when DOM is ready
    document.addEventListener('DOMContentLoaded', initializePhotoUpload);
  </script>
@endpush
