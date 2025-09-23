@extends('layouts.guest-v3')

@section('title', 'Email Verified')

@section('content')
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900">
          <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">Email verified!</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Your email has been successfully verified. You can now access your account.
        </p>
      </div>

      <!-- Actions -->
      <div class="card">
        <div class="text-center space-y-4">
          <a href="{{ route('dashboard') }}" class="btn btn-primary w-full">
            Go to Dashboard
          </a>

          <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <a href="{{ route('login') }}"
              class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
              ← Back to sign in
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
