@extends('layouts.guest-v3')

@section('title', 'Email Verification')

@section('content')
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900">
          <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">Check your email</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          We've sent you a verification link to <strong>{{ $email ?? 'your email' }}</strong>
        </p>
      </div>

      <!-- Instructions -->
      <div class="card">
        <div class="text-center space-y-4">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Click the link in the email to verify your account. If you don't see the email, check your spam folder.
          </p>

          <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              Didn't receive the email?
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="inline">
              @csrf
              <button type="submit" class="btn btn-secondary">
                Resend Verification Email
              </button>
            </form>
          </div>
        </div>
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
