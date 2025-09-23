@extends('layouts.guest-v3')

@section('title', 'Reset Password')

@section('content')
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reset your password</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <!-- Success Message -->
      @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
      @endif

      <!-- Form -->
      <div class="card">
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
          @csrf

          <!-- Email -->
          <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required
              class="form-input" placeholder="Enter your email">
            @error('email')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary w-full">
            Send Password Reset Link
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
