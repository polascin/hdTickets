@extends('layouts.guest-v3')

@section('title', 'Create Account')

@section('content')
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create your account</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Join HD Tickets and get started</p>
      </div>

      <!-- Form -->
      <div class="card">
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
          @csrf

          <!-- Name -->
          <div class="hdt-form-group">
            <label for="name" class="form-label">Full name</label>
            <input id="name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required
              class="hdt-input" placeholder="Enter your full name">
            @error('name')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Email -->
          <div class="hdt-form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required
              class="hdt-input" placeholder="Enter your email">
            @error('email')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password -->
          <div class="hdt-form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required class="hdt-input"
              placeholder="Create a password">
            @error('password')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Confirm Password -->
          <div class="hdt-form-group">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
              required class="hdt-input" placeholder="Confirm your password">
            @error('password_confirmation')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Terms -->
          <div class="flex items-start">
            <input id="terms" name="terms" type="checkbox" required class="form-checkbox mt-1">
            <label for="terms" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              I agree to the
              <a href="#"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Terms of Service</a>
              and
              <a href="#"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Privacy Policy</a>
            </label>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary w-full">
            Create Account
          </button>
        </form>
      </div>

      <!-- Social Register -->
      <div class="text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">Or sign up with</p>
        <div class="mt-4 grid grid-cols-2 gap-3">
          <button class="btn btn-secondary w-full justify-center">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path fill="currentColor"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="currentColor"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="currentColor"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="currentColor"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Google
          </button>
          <button class="btn btn-secondary w-full justify-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
            </svg>
            Twitter
          </button>
        </div>
      </div>

      <!-- Login Link -->
      <div class="text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Already have an account?
          <a href="{{ route('login') }}"
            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
            Sign in
          </a>
        </p>
      </div>
    </div>
  </div>
@endsection
