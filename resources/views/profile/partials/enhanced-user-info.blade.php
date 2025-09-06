<div class="enhanced-user-info">
    <div class="mb-4">
        <h5 class="mb-2 text-primary">
            <i class="fas fa-user-cog me-2"></i>
            {{ __('Account Information') }}
        </h5>
        <p class="text-muted small">
            {{ __('Your account details and system information.') }}
        </p>
    </div>

    <div class="row g-4">
        {{-- Basic Account Information --}}
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title d-flex align-items-center mb-3">
                        <i class="fas fa-user text-primary me-2"></i>
                        Profile Information
                    </h6>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            @if($user->profile_picture)
                                <img src="{{ Storage::url($user->profile_picture) }}" 
                                     alt="{{ $user->name }}" 
                                     class="rounded-circle" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center rounded-circle text-white" 
                                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    {{ substr($user->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $user->name }} {{ $user->surname }}</h6>
                            <small class="text-muted">{{ '@' . ($user->username ?: 'no-username') }}</small><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Phone</small>
                            <span class="small">{{ $user->phone ?: 'Not provided' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">User ID</small>
                            <span class="small font-monospace">#{{ $user->id }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Language</small>
                            <span class="small">{{ strtoupper($user->language ?? 'EN') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Timezone</small>
                            <span class="small">{{ $user->timezone ?? 'Not set' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Account Status & Role --}}
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title d-flex align-items-center mb-3">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        Account Status
                    </h6>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Role:</span>
                            <span class="badge 
                                @if($user->role === 'admin') bg-danger
                                @elseif($user->role === 'agent') bg-primary 
                                @elseif($user->role === 'scraper') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($user->role ?? 'customer') }}
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Status:</span>
                            @if($user->is_active ?? true)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Email Verified:</span>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark">Unverified</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">2FA Enabled:</span>
                            @if($user->two_factor_secret)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Account Activity --}}
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title d-flex align-items-center mb-3">
                        <i class="fas fa-clock text-info me-2"></i>
                        Account Activity
                    </h6>
                    
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted d-block">Member Since</small>
                            <span class="small">{{ $user->created_at->format('M j, Y') }}</span>
                            <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Last Login</small>
                            <span class="small">
                                {{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}
                            </span>
                            @if($user->last_login_at)
                                <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                            @endif
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Profile Updated</small>
                            <span class="small">{{ $user->updated_at->format('M j, Y g:i A') }}</span>
                            <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- System Information --}}
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title d-flex align-items-center mb-3">
                        <i class="fas fa-cogs text-secondary me-2"></i>
                        System Information
                    </h6>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Login Count:</span>
                            <span>{{ $user->login_count ?? 0 }}</span>
                        </div>
                        
                        @if($user->last_login_ip)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Last IP:</span>
                                <code class="small">{{ $user->last_login_ip }}</code>
                            </div>
                        @endif
                        
                        @if($user->preferences)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-1">Preferences Set:</small>
                                <span class="badge bg-info">{{ count($user->preferences) }} items</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
