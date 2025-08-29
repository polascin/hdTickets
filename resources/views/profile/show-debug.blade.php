@extends('layouts.app')

@section('title', 'My Profile - Debug')

@section('content')
  <div class="container-fluid px-4">
    <div class="row">
      <div class="col-12">
        <div class="alert alert-info">
          <h4>Profile Page Debug</h4>
          <p>If you can see this, the basic page structure is working.</p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h5>Basic Profile Information</h5>
          </div>
          <div class="card-body">
            <p><strong>Name:</strong> {{ $user->name ?? 'Not set' }}</p>
            <p><strong>Email:</strong> {{ $user->email ?? 'Not set' }}</p>
            <p><strong>Created:</strong> {{ $user->created_at ? $user->created_at->format('Y-m-d') : 'Unknown' }}</p>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h5>Profile Completion</h5>
          </div>
          <div class="card-body">
            @if (isset($profileCompletion))
              <p><strong>Completion:</strong> {{ $profileCompletion['percentage'] ?? 0 }}%</p>
              <p><strong>Status:</strong> {{ $profileCompletion['status'] ?? 'Unknown' }}</p>
            @else
              <p>Profile completion data not available</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5>User Statistics</h5>
          </div>
          <div class="card-body">
            @if (isset($userStats))
              <div class="row">
                <div class="col-md-3">
                  <p><strong>Login Count:</strong> {{ $userStats['login_count'] ?? 0 }}</p>
                </div>
                <div class="col-md-3">
                  <p><strong>Last Login:</strong> {{ $userStats['last_login_display'] ?? 'Never' }}</p>
                </div>
                <div class="col-md-3">
                  <p><strong>Account Age:</strong> {{ $userStats['joined_days_ago'] ?? 0 }} days</p>
                </div>
                <div class="col-md-3">
                  <p><strong>Monitored Events:</strong> {{ $userStats['monitored_events'] ?? 0 }}</p>
                </div>
              </div>
            @else
              <p>User statistics not available</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5>Quick Actions</h5>
          </div>
          <div class="card-body">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary me-2">
              <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="{{ route('profile.security') }}" class="btn btn-success me-2">
              <i class="fas fa-shield-alt"></i> Security
            </a>
            <a href="{{ route('profile.activity.dashboard') }}" class="btn btn-info">
              <i class="fas fa-chart-bar"></i> Activity
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
