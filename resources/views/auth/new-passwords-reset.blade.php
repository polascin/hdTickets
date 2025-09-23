@extends('layouts.guest-v3')

@section('title', 'Set New Password')

@section('content')
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Set new password</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Enter your new password below.
        </p>
      </div>

      <!-- Form -->
      <div class="card">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
          @csrf

          <!-- Password Reset Token -->
          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <!-- Email -->
          <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email"
              value="{{ old('email', $request->email) }}" required class="form-input" placeholder="Enter your email">
            @error('email')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password" class="form-label">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required class="form-input"
              placeholder="Enter new password">
            @error('password')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
              required class="form-input" placeholder="Confirm new password">
            @error('password_confirmation')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary w-full">
            Reset Password
          </button>
        </form>
      </div>

      <!-- Back to Login -->
      <div class="text-center">
        <a href="{{ route('login') }}"
          class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
          ← Back to sign in
        </a>
      </div>
    </div>
  </div>
@endsection
