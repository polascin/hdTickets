@extends('layouts.app')

@section('content')
  <div class="container-fluid px-4">
    <!-- User Profile Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-3">
          <i class="fas fa-arrow-left"></i> Back to Users
        </a>
        <div>
          <h1 class="h3 mb-0">User Profile</h1>
          <p class="text-muted mb-0">Comprehensive user management and security settings</p>
        </div>
      </div>
      <div class="d-flex gap-2">
        @if (auth()->user()->canManageUsers())
          <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
            <i class="fas fa-edit"></i> Edit User
          </button>
          <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-cog"></i> Actions
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="sendPasswordReset()">
                  <i class="fas fa-key"></i> Send Password Reset
                </a></li>
              <li><a class="dropdown-item" href="#" onclick="toggleUserStatus()">
                  <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                  {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                </a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-warning" href="#" onclick="resetTwoFactor()">
                  <i class="fas fa-shield-alt"></i> Reset 2FA
                </a></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser()">
                  <i class="fas fa-trash"></i> Delete User
                </a></li>
            </ul>
          </div>
        @endif
      </div>
    </div>

    <div class="row">
      <!-- Left Column - Basic Info & Security -->
      <div class="col-lg-4">
        <!-- User Overview Card -->
        <div class="card mb-4">
          <div class="card-body text-center">
            <div class="position-relative d-inline-block mb-3">
              @if ($user->profile_picture)
                <img src="{{ asset('storage/' . $user->profile_picture) }}" class="rounded-circle" width="120"
                  height="120" alt="Profile Picture">
              @else
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 120px; height: 120px; font-size: 48px; color: white;">
                  {{ strtoupper(substr($user->name, 0, 1) . substr($user->surname ?? '', 0, 1)) }}
                </div>
              @endif
              <span
                class="position-absolute bottom-0 end-0 bg-{{ $user->is_active ? 'success' : 'danger' }} 
                                     rounded-circle"
                style="width: 24px; height: 24px; border: 3px solid white;"></span>
            </div>

            <h4 class="mb-1">{{ $user->full_name }}</h4>
            <p class="text-muted mb-2">{{ '@' . $user->username }}</p>

            <div class="d-flex justify-content-center mb-3">
              <span
                class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'agent' ? 'primary' : ($user->role === 'scraper' ? 'secondary' : 'success')) }} me-2">
                <i
                  class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : ($user->role === 'agent' ? 'user-tie' : ($user->role === 'scraper' ? 'robot' : 'user')) }}"></i>
                {{ ucfirst($user->role) }}
              </span>
              @if ($user->email_verified_at)
                <span class="badge bg-success">
                  <i class="fas fa-check-circle"></i> Verified
                </span>
              @else
                <span class="badge bg-warning">
                  <i class="fas fa-exclamation-triangle"></i> Unverified
                </span>
              @endif
            </div>

            <div class="row text-center">
              <div class="col-4">
                <div class="fw-bold">{{ $user->login_count ?? 0 }}</div>
                <small class="text-muted">Logins</small>
              </div>
              <div class="col-4">
                <div class="fw-bold">{{ $user->activity_score ?? 0 }}</div>
                <small class="text-muted">Activity</small>
              </div>
              <div class="col-4">
                <div class="fw-bold">{{ $user->created_at->diffInDays() }}d</div>
                <small class="text-muted">Age</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication Card -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="fas fa-shield-alt text-primary"></i>
              Two-Factor Authentication
            </h5>
            <span class="badge bg-{{ $user->two_factor_enabled ? 'success' : 'secondary' }}">
              {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
          <div class="card-body">
            @if ($user->two_factor_enabled)
              <div class="d-flex align-items-center mb-3">
                <i class="fas fa-check-circle text-success me-2"></i>
                <div>
                  <div class="fw-bold">2FA is Active</div>
                  <small class="text-muted">
                    Enabled on {{ $user->two_factor_confirmed_at?->format('M j, Y') }}
                  </small>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Recovery Codes Status</label>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">
                    {{ count($user->two_factor_recovery_codes ?? []) }} codes available
                  </span>
                  <button class="btn btn-sm btn-outline-primary" onclick="regenerateRecoveryCodes()">
                    <i class="fas fa-refresh"></i> Regenerate
                  </button>
                </div>
              </div>

              @if (auth()->user()->canManageUsers())
                <div class="d-grid gap-2">
                  <button class="btn btn-outline-warning btn-sm" onclick="disable2FA()">
                    <i class="fas fa-times"></i> Disable 2FA
                  </button>
                  <button class="btn btn-outline-info btn-sm" onclick="show2FABackupCodes()">
                    <i class="fas fa-eye"></i> View Recovery Codes
                  </button>
                </div>
              @endif
            @else
              <div class="text-center py-3">
                <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 3rem;"></i>
                <p class="text-muted mb-3">Two-factor authentication is not enabled for this user.</p>
                @if (auth()->user()->canManageUsers())
                  <button class="btn btn-primary" onclick="setup2FA()">
                    <i class="fas fa-plus"></i> Enable 2FA
                  </button>
                @endif
              </div>
            @endif
          </div>
        </div>

        <!-- Security Settings Card -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-lock text-warning"></i>
              Security Settings
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Password Strength</label>
              <div class="progress mb-1" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: 85%"></div>
              </div>
              <small class="text-muted">Strong password</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Last Password Change</label>
              <div class="text-muted">{{ $user->updated_at->diffForHumans() }}</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Account Status</label>
              <div>
                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                  {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>

            @if (auth()->user()->canManageUsers())
              <div class="d-grid">
                <button class="btn btn-outline-primary btn-sm" onclick="sendPasswordReset()">
                  <i class="fas fa-key"></i> Force Password Reset
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Right Column - Detailed Information -->
      <div class="col-lg-8">
        <!-- Contact & Personal Information -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-user text-info"></i>
              Personal Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Full Name</label>
                  <div>{{ $user->full_name }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Username</label>
                  <div>{{ $user->username }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Email</label>
                  <div class="d-flex align-items-center">
                    {{ $user->email }}
                    @if ($user->email_verified_at)
                      <i class="fas fa-check-circle text-success ms-2" title="Verified"></i>
                    @else
                      <i class="fas fa-exclamation-triangle text-warning ms-2" title="Unverified"></i>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Phone</label>
                  <div>{{ $user->phone ?? 'Not provided' }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Timezone</label>
                  <div>{{ $user->timezone ?? 'UTC' }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Language</label>
                  <div>{{ $user->language ?? 'English' }}</div>
                </div>
              </div>
            </div>

            @if ($user->bio)
              <div class="mb-3">
                <label class="form-label fw-bold">Bio</label>
                <div class="p-3 bg-light rounded">{{ $user->bio }}</div>
              </div>
            @endif
          </div>
        </div>

        <!-- Login & Activity Information -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-chart-line text-success"></i>
              Activity & Login Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Last Login</label>
                  <div>
                    @if ($user->last_login_at)
                      {{ $user->last_login_at->format('M j, Y \a\t g:i A') }}
                      <small class="text-muted d-block">{{ $user->last_login_at->diffForHumans() }}</small>
                    @else
                      <span class="text-muted">Never logged in</span>
                    @endif
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Last Login IP</label>
                  <div>{{ $user->last_login_ip ?? 'Unknown' }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Total Logins</label>
                  <div>{{ $user->login_count ?? 0 }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Account Created</label>
                  <div>
                    {{ $user->created_at->format('M j, Y \a\t g:i A') }}
                    <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Registration Source</label>
                  <div>{{ ucfirst($user->registration_source ?? 'web') }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Activity Score</label>
                  <div class="d-flex align-items-center">
                    {{ $user->activity_score ?? 0 }}
                    <div class="progress ms-2 flex-grow-1" style="height: 8px;">
                      <div class="progress-bar"
                        style="width: {{ min((($user->activity_score ?? 0) / 100) * 100, 100) }}%"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Permissions & Role Information -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-key text-warning"></i>
              Permissions & Role
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <label class="form-label fw-bold">Current Role</label>
              <div>
                <span
                  class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'agent' ? 'primary' : ($user->role === 'scraper' ? 'secondary' : 'success')) }} fs-6 me-2">
                  <i
                    class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : ($user->role === 'agent' ? 'user-tie' : ($user->role === 'scraper' ? 'robot' : 'user')) }}"></i>
                  {{ ucfirst($user->role) }}
                </span>
                @if ($user->role === 'scraper')
                  <small class="text-muted">No system access - rotation user only</small>
                @endif
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Key Permissions</label>
              <div class="row">
                @php
                  $permissions = $user->getPermissions();
                  $keyPermissions = [
                      'can_access_system' => 'System Access',
                      'manage_users' => 'User Management',
                      'manage_system' => 'System Configuration',
                      'select_and_purchase_tickets' => 'Ticket Operations',
                      'manage_monitoring' => 'Monitoring Access',
                      'access_financials' => 'Financial Reports',
                  ];
                @endphp

                @foreach ($keyPermissions as $permission => $label)
                  <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                      <i
                        class="fas fa-{{ $permissions[$permission] ? 'check text-success' : 'times text-danger' }} me-2"></i>
                      <span class="{{ $permissions[$permission] ? '' : 'text-muted' }}">{{ $label }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            @if (auth()->user()->canManageUsers())
              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                  data-bs-target="#changeRoleModal">
                  <i class="fas fa-exchange-alt"></i> Change Role
                </button>
                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal"
                  data-bs-target="#customPermissionsModal">
                  <i class="fas fa-cog"></i> Custom Permissions
                </button>
              </div>
            @endif
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="fas fa-history text-primary"></i>
              Recent Activity
            </h5>
          </div>
          <div class="card-body">
            <div class="timeline">
              @forelse($activities ?? [] as $activity)
                <div class="timeline-item mb-3">
                  <div class="d-flex">
                    <div class="timeline-marker bg-primary rounded-circle me-3"
                      style="width: 10px; height: 10px; margin-top: 6px;"></div>
                    <div class="flex-grow-1">
                      <div class="fw-bold">{{ $activity->description }}</div>
                      <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <i class="fas fa-history text-muted mb-3" style="font-size: 3rem;"></i>
                  <p class="text-muted">No recent activity recorded</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals would go here -->
  <!-- Edit User Modal, Change Role Modal, 2FA Setup Modal, etc. -->

@endsection

@push('styles')
  <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
  <style>
    .timeline-item:not(:last-child) .timeline-marker::after {
      content: '';
      position: absolute;
      left: 50%;
      top: 15px;
      width: 2px;
      height: 40px;
      background: #dee2e6;
      transform: translateX(-50%);
    }
  </style>
@endpush

@push('scripts')
  <script>
    // Two-Factor Authentication Functions
    function setup2FA() {
      // Open 2FA setup modal or redirect to setup page
      showAlert('2FA setup initiated', 'info');
    }

    function disable2FA() {
      if (confirm('Are you sure you want to disable two-factor authentication for this user?')) {
        // AJAX call to disable 2FA
        showAlert('2FA has been disabled', 'warning');
      }
    }

    function regenerateRecoveryCodes() {
      if (confirm('Generate new recovery codes? This will invalidate existing codes.')) {
        // AJAX call to regenerate codes
        showAlert('Recovery codes regenerated', 'success');
      }
    }

    function show2FABackupCodes() {
      // Show modal with backup codes
      showAlert('Recovery codes displayed', 'info');
    }

    // User Management Functions
    function toggleUserStatus() {
      const isActive = {{ $user->is_active ? 'true' : 'false' }};
      const action = isActive ? 'deactivate' : 'activate';

      if (confirm(`Are you sure you want to ${action} this user?`)) {
        // AJAX call to toggle status
        showAlert(`User has been ${action}d`, isActive ? 'warning' : 'success');
      }
    }

    function sendPasswordReset() {
      if (confirm('Send password reset email to this user?')) {
        // AJAX call to send reset
        showAlert('Password reset email sent', 'success');
      }
    }

    function deleteUser() {
      if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // AJAX call to delete user
        showAlert('User deletion initiated', 'danger');
      }
    }

    function resetTwoFactor() {
      if (confirm('Reset two-factor authentication for this user? They will need to set it up again.')) {
        // AJAX call to reset 2FA
        showAlert('2FA has been reset', 'warning');
      }
    }

    // Utility function for notifications
    function showAlert(message, type = 'info') {
      // You can implement toast notifications or use your preferred alert system
      alert(message);
    }

    // Initialize tooltips and other Bootstrap components
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
@endpush
