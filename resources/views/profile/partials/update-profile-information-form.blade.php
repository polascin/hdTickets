<div class="profile-information-form">
  <div class="mb-4">
    <h5 class="mb-2 text-primary">
      <i class="fas fa-user me-2"></i>
      {{ __('Profile Information') }}
    </h5>
    <p class="text-muted small">
      {{ __("Update your account's profile information and email address.") }}
    </p>
  </div>

  {{-- Email Verification Form (Hidden) --}}
  <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
    @csrf
  </form>

  {{-- Main Profile Update Form --}}
  <form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate data-ajax-submit>
    @csrf
    @method('patch')

    {{-- Success message container for AJAX responses --}}
    <div id="profile-success-message" class="alert alert-success d-none" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      <span id="profile-success-text">Profile updated successfully!</span>
    </div>

    {{-- Name Fields Row --}}
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="name" class="form-label">
          <i class="fas fa-user text-muted me-1"></i>
          {{ __('First Name') }} <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" 
               id="name" name="name" value="{{ old('name', $user->name) }}" 
               required autofocus autocomplete="given-name" 
               placeholder="Enter first name">
        @error('name')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
      </div>
      
      <div class="col-md-6 mb-3">
        <label for="surname" class="form-label">
          <i class="fas fa-user text-muted me-1"></i>
          {{ __('Last Name') }}
        </label>
        <input type="text" class="form-control @error('surname') is-invalid @enderror" 
               id="surname" name="surname" value="{{ old('surname', $user->surname) }}" 
               autocomplete="family-name" 
               placeholder="Enter last name">
        @error('surname')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
      </div>
    </div>

    {{-- Contact Information --}}
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="email" class="form-label">
          <i class="fas fa-envelope text-muted me-1"></i>
          {{ __('Email Address') }} <span class="text-danger">*</span>
        </label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" 
               id="email" name="email" value="{{ old('email', $user->email) }}" 
               required autocomplete="email" 
               placeholder="Enter email address">
        @error('email')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
      </div>
      
      <div class="col-md-6 mb-3">
        <label for="username" class="form-label">
          <i class="fas fa-at text-muted me-1"></i>
          {{ __('Username') }}
        </label>
        <input type="text" class="form-control @error('username') is-invalid @enderror" 
               id="username" name="username" value="{{ old('username', $user->username) }}" 
               autocomplete="username" 
               placeholder="Enter username">
        @error('username')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
        <div class="form-text">Used for public display and mentions</div>
      </div>
    </div>

    {{-- Phone Number --}}
    <div class="mb-3">
      <label for="phone" class="form-label">
        <i class="fas fa-phone text-muted me-1"></i>
        {{ __('Phone Number') }}
      </label>
      <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
             id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
             autocomplete="tel" 
             placeholder="Enter phone number">
      @error('phone')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
      <div class="form-text">Used for account security and important notifications</div>
    </div>

    {{-- Bio --}}
    <div class="mb-3">
      <label for="bio" class="form-label">
        <i class="fas fa-pen text-muted me-1"></i>
        {{ __('Biography') }}
      </label>
      <textarea class="form-control @error('bio') is-invalid @enderror" 
                id="bio" name="bio" rows="3" 
                placeholder="Tell us about yourself..." 
                maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
      @error('bio')
        <div class="invalid-feedback">
          {{ $message }}
        </div>
      @enderror
      <div class="form-text">Brief description about yourself (maximum 1000 characters)</div>
    </div>

    {{-- Localization Settings --}}
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="timezone" class="form-label">
          <i class="fas fa-clock text-muted me-1"></i>
          {{ __('Timezone') }}
        </label>
        <select class="form-select @error('timezone') is-invalid @enderror" 
                id="timezone" name="timezone">
          <option value="">Select your timezone...</option>
          @foreach (timezone_identifiers_list() as $timezone)
            <option value="{{ $timezone }}" {{ old('timezone', $user->timezone) === $timezone ? 'selected' : '' }}>
              {{ $timezone }}
            </option>
          @endforeach
        </select>
        @error('timezone')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
      </div>

      <div class="col-md-6 mb-3">
        <label for="language" class="form-label">
          <i class="fas fa-language text-muted me-1"></i>
          {{ __('Language') }}
        </label>
        <select class="form-select @error('language') is-invalid @enderror" 
                id="language" name="language">
          <option value="">Select language...</option>
          <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>
            English
          </option>
          <option value="es" {{ old('language', $user->language) === 'es' ? 'selected' : '' }}>
            Español (Spanish)
          </option>
          <option value="fr" {{ old('language', $user->language) === 'fr' ? 'selected' : '' }}>
            Français (French)
          </option>
          <option value="de" {{ old('language', $user->language) === 'de' ? 'selected' : '' }}>
            Deutsch (German)
          </option>
          <option value="it" {{ old('language', $user->language) === 'it' ? 'selected' : '' }}>
            Italiano (Italian)
          </option>
          <option value="pt" {{ old('language', $user->language) === 'pt' ? 'selected' : '' }}>
            Português (Portuguese)
          </option>
        </select>
        @error('language')
          <div class="invalid-feedback">
            {{ $message }}
          </div>
        @enderror
      </div>
    </div>

    {{-- Email Verification Notice --}}
    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
      <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Email Verification Required:</strong> Your email address is not yet verified.
        <button type="button" class="btn btn-link p-0 align-baseline" onclick="document.getElementById('send-verification').submit();">
          Click here to re-send the verification email.
        </button>
        
        @if (session('status') === 'verification-link-sent')
          <div class="mt-2 text-success small">
            <i class="fas fa-check me-1"></i>
            A new verification link has been sent to your email address.
          </div>
        @endif
      </div>
    @endif

    {{-- Form Actions --}}
    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
        <i class="fas fa-times me-1"></i>
        Cancel
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>
        {{ __('Save Changes') }}
      </button>
    </div>

    {{-- Session Status Message --}}
    @if (session('status') === 'profile-updated')
      <div class="mt-3 alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ __('Profile information updated successfully!') }}
      </div>
    @endif
  </form>
</div>
