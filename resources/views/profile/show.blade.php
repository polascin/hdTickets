@extends('layouts.app')

@section('title', 'My Profile')

@section('header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <div>
            <h1 class="h3 mb-0 text-gray-900">My Profile</h1>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb breadcrumb-sm mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
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
    .progress-ring {
        transition: stroke-dashoffset 0.5s ease;
    }
    .recommendation-card {
        border-left: 4px solid #fbbf24;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.2);
        object-fit: cover;
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
    }
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
<div class="container-xl">
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
                            @if($user->profile_picture)
                                <img src="{{ $user->getProfileDisplay()['picture_url'] }}" 
                                     alt="{{ $user->getFullNameAttribute() ?: $user->name }}" 
                                     class="profile-avatar">
                            @else
                                <div class="profile-initials">
                                    {{ $user->getProfileDisplay()['initials'] }}
                                </div>
                            @endif
                            <!-- Upload indicator -->
                            <a href="{{ route('profile.edit') }}" 
                               class="position-absolute bottom-0 end-0 btn btn-light btn-sm rounded-circle p-1"
                               title="Change photo"
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
                            @if($securityStatus['email_verified'])
                                <span class="badge bg-success px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Email Verified
                                </span>
                            @endif
                            @if($securityStatus['two_factor_enabled'])
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
                            <div class="position-relative d-inline-block mb-3">
                                <svg class="progress-ring" width="100" height="100">
                                    <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8" fill="transparent"/>
                                    <circle cx="50" cy="50" r="40" stroke="#3b82f6" stroke-width="8" fill="transparent"
                                            stroke-dasharray="{{ 2 * pi() * 40 }}"
                                            stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $profileCompletion['percentage'] / 100) }}"
                                            stroke-linecap="round" transform="rotate(-90 50 50)"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <span class="h4 fw-bold text-primary">{{ $profileCompletion['percentage'] }}%</span>
                                </div>
                            </div>
                            <p class="text-muted mb-2">{{ $profileCompletion['completed_count'] }}/{{ $profileCompletion['total_fields'] }} fields complete</p>
                            <span class="badge bg-{{ $profileCompletion['status'] === 'excellent' ? 'success' : ($profileCompletion['status'] === 'good' ? 'primary' : 'warning') }}">
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
                            <div class="position-relative d-inline-block mb-3">
                                <svg class="progress-ring" width="100" height="100">
                                    <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8" fill="transparent"/>
                                    <circle cx="50" cy="50" r="40" stroke="#10b981" stroke-width="8" fill="transparent"
                                            stroke-dasharray="{{ 2 * pi() * 40 }}"
                                            stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $profileInsights['security_score'] / 100) }}"
                                            stroke-linecap="round" transform="rotate(-90 50 50)"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <span class="h4 fw-bold text-success">{{ $profileInsights['security_score'] }}</span>
                                </div>
                            </div>
                            <p class="text-muted mb-2">Account Security Level</p>
                            <span class="badge bg-{{ $profileInsights['security_score'] >= 80 ? 'success' : ($profileInsights['security_score'] >= 60 ? 'warning' : 'danger') }}">
                                {{ $profileInsights['security_score'] >= 80 ? 'Excellent' : ($profileInsights['security_score'] >= 60 ? 'Good' : 'Needs Improvement') }}
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
                                <h4 class="fw-bold text-primary mb-1">{{ $userStats['monitored_events'] }}</h4>
                                <small class="text-muted">Active Alerts</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card bg-light rounded p-3">
                                <i class="fas fa-chart-line text-success mb-2 fs-4"></i>
                                <h4 class="fw-bold text-primary mb-1">{{ $userStats['total_alerts'] }}</h4>
                                <small class="text-muted">Total Alerts</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card bg-light rounded p-3">
                                <i class="fas fa-search text-info mb-2 fs-4"></i>
                                <h4 class="fw-bold text-primary mb-1">{{ $userStats['active_searches'] }}</h4>
                                <small class="text-muted">Active Searches</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card bg-light rounded p-3">
                                <i class="fas fa-shopping-cart text-success mb-2 fs-4"></i>
                                <h4 class="fw-bold text-primary mb-1">{{ $userStats['recent_purchases'] }}</h4>
                                <small class="text-muted">Recent Purchases</small>
                            </div>
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
                                    <h6 class="mb-0">{{ $userStats['login_count'] }}</h6>
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
                                    <h6 class="mb-0">{{ $userStats['last_login_display'] }}</h6>
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
                                    <h6 class="mb-0">{{ $userStats['joined_days_ago'] }} days</h6>
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
            @if(count($profileInsights['recommendations']) > 0)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Recommendations
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($profileInsights['recommendations'] as $recommendation)
                            <div class="recommendation-card rounded p-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-{{ $recommendation['icon'] }} text-{{ $recommendation['priority'] === 'high' ? 'danger' : 'warning' }} me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $recommendation['title'] }}</h6>
                                        <p class="small text-muted mb-2">{{ $recommendation['description'] }}</p>
                                        @if($recommendation['route'])
                                            <a href="{{ route($recommendation['route']) }}" class="btn btn-sm btn-outline-primary">
                                                {{ $recommendation['action'] }}
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
                                <i class="fas fa-envelope text-{{ $securityStatus['email_verified'] ? 'success' : 'danger' }} me-2"></i>
                                <small>Email Verification</small>
                            </div>
                            <span class="badge bg-{{ $securityStatus['email_verified'] ? 'success' : 'danger' }}">
                                {{ $securityStatus['email_verified'] ? 'Verified' : 'Pending' }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-mobile-alt text-{{ $securityStatus['two_factor_enabled'] ? 'success' : 'warning' }} me-2"></i>
                                <small>Two-Factor Auth</small>
                            </div>
                            <span class="badge bg-{{ $securityStatus['two_factor_enabled'] ? 'success' : 'warning' }}">
                                {{ $securityStatus['two_factor_enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-key text-info me-2"></i>
                                <small>Password Age</small>
                            </div>
                            <span class="badge bg-{{ $securityStatus['password_age_days'] <= 90 ? 'success' : ($securityStatus['password_age_days'] <= 180 ? 'warning' : 'danger') }}">
                                {{ $securityStatus['password_age_days'] }} days
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-devices text-primary me-2"></i>
                                <small>Trusted Devices</small>
                            </div>
                            <span class="badge bg-primary">{{ $securityStatus['trusted_devices_count'] }}</span>
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
        fetch('{{ route("profile.photo.upload") }}', {
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
    const progressRings = document.querySelectorAll('.progress-ring circle:last-child');
    progressRings.forEach(ring => {
        const circumference = 2 * Math.PI * 40;
        ring.style.strokeDasharray = circumference;
        ring.style.strokeDashoffset = circumference;
        
        // Animate
        setTimeout(() => {
            ring.style.strokeDashoffset = ring.getAttribute('stroke-dashoffset');
        }, 500);
    });
});
</script>
@endpush
